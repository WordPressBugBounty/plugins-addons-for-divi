<?php
/**
 * SvgDraw: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\SvgDraw
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\SvgDraw\SvgDrawTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    const TRIGGERS   = ['viewport', 'load', 'hover'];
    const FILL_MODES = ['none', 'after'];
    const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Elements that can be stroke-drawn.
     *
     * Circles, ellipses and rects are opt-in: pathLength on them is unreliable
     * in older Safari, where they would simply appear instead of drawing.
     */
    const DRAWABLE       = ['path', 'line', 'polyline', 'polygon'];
    const DRAWABLE_SHAPES = ['circle', 'ellipse', 'rect'];

    /**
     * Read an SVG out of the media library.
     *
     * Deliberately not a URL fetch. Divi Torque Pro's Inline SVG resolves an
     * author-supplied URL by string-replacing the site URL for ABSPATH and
     * reading whatever that lands on, then falls back to wp_remote_get for
     * anything else — which is a server-side request to an arbitrary address,
     * i.e. an SSRF primitive, and not something a free plugin on wordpress.org
     * should ship.
     *
     * Here the URL has to resolve to an attachment in this site's own media
     * library, and the bytes come from that attachment's real path. A URL
     * pointing anywhere else returns nothing.
     *
     * @param string $url Attachment URL.
     *
     * @return string Raw file contents, or '' when it cannot be resolved.
     */
    public static function read_library_svg($url)
    {
        $url = trim((string) $url);

        if ('' === $url) {
            return '';
        }

        // Cached on the attachment id rather than the URL, so a resized or
        // renamed URL for the same file still hits. Prefixed dtq_ — Pro uses
        // dtp_ and the two must not collide on a site running both.
        $attachment_id = attachment_url_to_postid($url);

        if (!$attachment_id) {
            return '';
        }

        $cache_key = 'dtq_svg_' . $attachment_id;
        $cached    = get_transient($cache_key);

        if (false !== $cached) {
            return (string) $cached;
        }

        $path = get_attached_file($attachment_id);

        if (!$path || !is_readable($path)) {
            return '';
        }

        // The media library can hold anything; only take a file that is
        // actually an SVG.
        if ('svg' !== strtolower((string) pathinfo($path, PATHINFO_EXTENSION))) {
            return '';
        }

        $contents = file_get_contents($path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

        if (false === $contents) {
            return '';
        }

        set_transient($cache_key, $contents, DAY_IN_SECONDS);

        return $contents;
    }

    /**
     * Sanitise SVG markup.
     *
     * Divi ships a first-party sanitiser whose allowlist already covers what
     * this module needs — class, stroke, stroke-width, stroke-dasharray,
     * stroke-dashoffset, transform — while dropping script elements, on*
     * handlers and external <use> references. Preferred over rolling our own.
     *
     * The wp_kses fallback exists for the case where Divi's class is not
     * loadable; it is deliberately narrower rather than more permissive.
     *
     * @param string $markup Untrusted SVG markup.
     *
     * @return string
     */
    public static function sanitize_svg($markup)
    {
        $markup = trim((string) $markup);

        if ('' === $markup || false === stripos($markup, '<svg')) {
            return '';
        }

        $sanitizer = '\\ET\\Builder\\Packages\\ModuleLibrary\\Svg\\SvgSanitizer';

        if (class_exists($sanitizer) && method_exists($sanitizer, 'sanitize_markup')) {
            return $sanitizer::sanitize_markup($markup);
        }

        $common = [
            'class'             => true,
            'fill'              => true,
            'fill-opacity'      => true,
            'fill-rule'         => true,
            'stroke'            => true,
            'stroke-width'      => true,
            'stroke-linecap'    => true,
            'stroke-linejoin'   => true,
            'stroke-dasharray'  => true,
            'stroke-dashoffset' => true,
            'stroke-opacity'    => true,
            'transform'         => true,
            'opacity'           => true,
        ];

        $allowed = [
            'svg' => array_merge($common, [
                'viewbox'             => true,
                'xmlns'               => true,
                'width'               => true,
                'height'              => true,
                'preserveaspectratio' => true,
                'role'                => true,
                'aria-label'          => true,
                'aria-hidden'         => true,
                'focusable'           => true,
            ]),
            'g'        => $common,
            'path'     => array_merge($common, ['d' => true]),
            'line'     => array_merge($common, ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true]),
            'polyline' => array_merge($common, ['points' => true]),
            'polygon'  => array_merge($common, ['points' => true]),
            'circle'   => array_merge($common, ['cx' => true, 'cy' => true, 'r' => true]),
            'ellipse'  => array_merge($common, ['cx' => true, 'cy' => true, 'rx' => true, 'ry' => true]),
            'rect'     => array_merge($common, ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true]),
            'title'    => ['id' => true],
        ];

        return wp_kses($markup, $allowed);
    }

    /**
     * Prepare sanitised SVG markup for drawing.
     *
     * Adds pathLength="1" to every drawable element, plus a --dtq-sd-i index so
     * the strokes can be staggered from one CSS expression.
     *
     * pathLength="1" is what makes the whole module CSS-only: it normalises the
     * path's length to 1, so stroke-dasharray:1 / stroke-dashoffset:1 is
     * "undrawn" and dashoffset:0 is "drawn", for any path, with no
     * getTotalLength() call.
     *
     * Runs AFTER sanitisation, deliberately: sanitising untrusted input before
     * adding our own trusted attributes is the right order, and pathLength is
     * not on Divi's allowlist, so injecting it first would simply have it
     * stripped.
     *
     * That also means an author's own pathLength never survives to be seen
     * here — the sanitiser removes it — so it is always normalised to 1. For
     * this module that is what is wanted anyway: 1 is the value the drawing
     * maths depends on.
     *
     * @param string $markup         Sanitised SVG markup.
     * @param bool   $include_shapes Also prepare circle/ellipse/rect.
     *
     * @return string
     */
    public static function prepare_svg($markup, $include_shapes = false)
    {
        if ('' === $markup) {
            return '';
        }

        $tags = self::DRAWABLE;
        if ($include_shapes) {
            $tags = array_merge($tags, self::DRAWABLE_SHAPES);
        }

        $index   = 0;
        $pattern = '/<(' . implode('|', $tags) . ')(\s[^>]*?)?\s*(\/?)>/i';

        return (string) preg_replace_callback(
            $pattern,
            function ($matches) use (&$index) {
                $tag        = $matches[1];
                $attributes = $matches[2] ?? '';
                $close      = $matches[3] ?? '';

                // A greedy attribute match would swallow the trailing slash of a
                // self-closing tag, and appending after it produces
                // `<path d="…"/ pathLength="1">` — malformed. The pattern is
                // non-greedy, and this catches anything that still slips past.
                $attributes = rtrim($attributes);
                if ('' !== $attributes && '/' === substr($attributes, -1)) {
                    $attributes = rtrim(substr($attributes, 0, -1));
                    $close      = '/';
                }
                if ('' !== $attributes) {
                    $attributes = ' ' . ltrim($attributes);
                }

                $path_length = ' pathLength="1"';

                // The index rides in a custom property. A style attribute is on
                // the allowlist, and merging into an existing one keeps whatever
                // the author had.
                if (preg_match('/\sstyle\s*=\s*"([^"]*)"/i', $attributes, $style_match)) {
                    $merged     = rtrim(trim($style_match[1]), ';');
                    $merged     = ('' !== $merged ? $merged . ';' : '') . '--dtq-sd-i:' . $index;
                    $attributes = str_replace($style_match[0], ' style="' . esc_attr($merged) . '"', $attributes);
                    $style      = '';
                } else {
                    $style = ' style="--dtq-sd-i:' . (int) $index . '"';
                }

                $index++;

                return '<' . $tag . $attributes . $path_length . $style . $close . '>';
            },
            $markup
        );
    }

    /**
     * Build the wrapper class list.
     *
     * PHP twin of src/divi5/modules/svg-draw/wrapper-class.js.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return string
     */
    public static function wrapper_class($advanced)
    {
        $pick = function ($key, array $allowed, $fallback) use ($advanced) {
            $value = $advanced[$key]['desktop']['value'] ?? $fallback;
            return in_array($value, $allowed, true) ? $value : $fallback;
        };

        $classes = [
            'dtq-module',
            'dtq-svg-draw',
            // Marks this as Divi 5 markup so the site-wide Divi 4 frontend.js
            // leaves it alone.
            'dtq-d5',
            'dtq-svg-draw--trigger-' . $pick('trigger', self::TRIGGERS, 'viewport'),
            'dtq-svg-draw--fill-' . $pick('fillMode', self::FILL_MODES, 'none'),
            'dtq-svg-draw--align-' . $pick('align', self::ALIGNMENTS, 'center'),
        ];

        if (($advanced['includeShapes']['desktop']['value'] ?? 'off') === 'on') {
            $classes[] = 'dtq-svg-draw--shapes';
        }

        return implode(' ', $classes);
    }

    /**
     * Server-side render for the SVG Draw module.
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused; no children).
     * @param WP_Block       $block    Parsed block.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];
        $source   = $advanced['source']['desktop']['value'] ?? 'code';

        if ('file' === $source) {
            $file_raw = $advanced['svgFile']['desktop']['value'] ?? '';
            $url      = is_array($file_raw) ? ($file_raw['src'] ?? '') : (string) $file_raw;
            $markup   = self::read_library_svg($url);
        } else {
            $markup = (string) ($advanced['svgCode']['desktop']['value'] ?? '');
        }

        $markup = self::sanitize_svg($markup);

        if ('' !== $markup) {
            $include_shapes = ($advanced['includeShapes']['desktop']['value'] ?? 'off') === 'on';
            $markup         = self::prepare_svg($markup, $include_shapes);

            // Ornament by default — most SVG on a page is decoration, and
            // announcing it adds noise. Off means the author has said it carries
            // meaning, so it gets a role and a name.
            $decorative = ($advanced['decorative']['desktop']['value'] ?? 'on') === 'on';
            $label      = trim((string) ($advanced['svgLabel']['desktop']['value'] ?? ''));

            $svg_attrs = $decorative || '' === $label
                ? ' aria-hidden="true" focusable="false"'
                : sprintf(' role="img" aria-label="%s"', esc_attr($label));

            // Applied to the outermost <svg> only.
            $markup = preg_replace('/<svg\b/i', '<svg' . $svg_attrs, $markup, 1);
        }

        $canvas = '' !== $markup
            ? $markup
            : '<p class="dtq-svg-draw__placeholder">'
                . esc_html__('Paste an SVG or choose one from the media library.', 'addons-for-divi')
                . '</p>';

        // Only the viewport trigger needs JavaScript; load and hover are pure
        // CSS. The offset and repeat flag ride along for the observer.
        $data = '';
        if (($advanced['trigger']['desktop']['value'] ?? 'viewport') === 'viewport') {
            $offset = dtq_css_length($advanced['viewportOffset']['desktop']['value'] ?? '15%', '15%');
            $data   = sprintf(
                ' data-dtq-sd-offset="%1$s" data-dtq-sd-repeat="%2$s"',
                esc_attr($offset),
                ($advanced['repeat']['desktop']['value'] ?? 'off') === 'on' ? '1' : '0'
            );
        }

        $children = sprintf(
            '<div class="%1$s"%2$s><div class="dtq-svg-draw__canvas">%3$s</div></div>',
            esc_attr(self::wrapper_class($advanced)),
            $data,
            // Already sanitised above, and re-escaping would render the markup
            // as text.
            et_core_esc_previously($canvas)
        );

        return Module::render(
            [
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'],
                'name'                => $block->block_type->name,
                'moduleCategory'      => $block->block_type->category,
                'classnamesFunction'  => [self::class, 'module_classnames'],
                'stylesComponent'     => [self::class, 'module_styles'],
                'scriptDataComponent' => [self::class, 'module_script_data'],
                'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
                'storeInstance'       => $block->parsed_block['storeInstance'] ?? null,
                'children'            => [
                    $elements->style_components(['attrName' => 'module']),
                    $children,
                ],
            ]
        );
    }
}
