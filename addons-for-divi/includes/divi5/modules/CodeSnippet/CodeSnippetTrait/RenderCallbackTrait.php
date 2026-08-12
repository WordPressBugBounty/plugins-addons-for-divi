<?php
/**
 * CodeSnippet: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\CodeSnippet
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CodeSnippet\CodeSnippetTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Languages bundled in assets/libs/prism.
     *
     * The value lands in a `language-*` class Prism reads, so it is matched
     * against this list rather than passed through — and this is also exactly
     * what is bundled, since there is no autoloader to fetch anything else.
     */
    const LANGUAGES = [
        'none', 'markup', 'css', 'javascript', 'typescript', 'jsx', 'php',
        'python', 'bash', 'json', 'sql', 'yaml', 'markdown',
    ];

    const THEMES = ['dark', 'light', 'midnight', 'paper'];

    /**
     * Build the wrapper class list.
     *
     * PHP twin of src/divi5/modules/code-snippet/wrapper-class.js.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return string
     */
    public static function wrapper_class($advanced)
    {
        $theme = $advanced['theme']['desktop']['value'] ?? 'dark';
        if (!in_array($theme, self::THEMES, true)) {
            $theme = 'dark';
        }

        $classes = [
            'dtq-module',
            'dtq-code-snippet',
            // Marks this as Divi 5 markup so the site-wide Divi 4 frontend.js
            // leaves it alone.
            'dtq-d5',
            'dtq-code-snippet--theme-' . $theme,
        ];

        if (($advanced['showLineNumbers']['desktop']['value'] ?? 'on') !== 'off') {
            $classes[] = 'dtq-code-snippet--numbered';
        }
        if (($advanced['wrapLines']['desktop']['value'] ?? 'off') === 'on') {
            $classes[] = 'dtq-code-snippet--wrap';
        }

        return implode(' ', $classes);
    }

    /**
     * Escape code for display.
     *
     * esc_html handles the markup. The bracket replacement handles something
     * esc_html does not: WordPress runs do_shortcode on the_content at priority
     * 11, and do_blocks — which is what renders this module — runs at 9. So a
     * snippet containing [gallery] would be *executed* by WordPress after we
     * had faithfully rendered it, and a snippet of shortcode documentation
     * would run the shortcodes it was documenting.
     *
     * @param string $code Raw author input.
     *
     * @return string
     */
    public static function escape_code($code)
    {
        return str_replace(
            ['[', ']'],
            ['&#91;', '&#93;'],
            esc_html((string) $code)
        );
    }

    /**
     * Validate a line-highlight expression.
     *
     * Prism's line-highlight plugin reads `data-line`, so only its own grammar
     * of numbers, commas and ranges is allowed through — anything else is
     * dropped rather than passed on.
     *
     * @param string $value Raw author input.
     *
     * @return string Empty when unusable.
     */
    public static function sanitize_highlight_lines($value)
    {
        $value = trim((string) $value);

        if ('' === $value || !preg_match('/^[\d,\s-]+$/', $value)) {
            return '';
        }

        return $value;
    }

    /**
     * Server-side render for the Code Snippet module.
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

        $code = (string) ($advanced['code']['desktop']['value'] ?? '');

        $language = $advanced['language']['desktop']['value'] ?? 'php';
        if (!in_array($language, self::LANGUAGES, true)) {
            $language = 'none';
        }

        // Prism loads only on pages that actually show a snippet. Registered in
        // includes/divi5/Modules.php, enqueued here — the same on-demand shape
        // Swiper uses.
        if ('none' !== $language) {
            wp_enqueue_script('divi-torque-lite-prism');
        }

        $show_header   = ($advanced['showHeader']['desktop']['value'] ?? 'on') !== 'off';
        $show_copy     = ($advanced['showCopy']['desktop']['value'] ?? 'on') !== 'off';
        $show_language = ($advanced['showLanguageLabel']['desktop']['value'] ?? 'on') !== 'off';
        $file_name     = trim((string) ($advanced['fileName']['desktop']['value'] ?? ''));
        $copy_label    = (string) ($advanced['copyLabel']['desktop']['value'] ?? __('Copy', 'addons-for-divi'));
        $copied_label  = (string) ($advanced['copiedLabel']['desktop']['value'] ?? __('Copied', 'addons-for-divi'));

        $header_html = '';
        if ($show_header) {
            $parts = '';

            if ('' !== $file_name) {
                $parts .= sprintf(
                    '<span class="dtq-code-snippet__file">%s</span>',
                    esc_html($file_name)
                );
            }

            $parts .= '<span class="dtq-code-snippet__spacer"></span>';

            if ($show_language && 'none' !== $language) {
                $parts .= sprintf(
                    '<span class="dtq-code-snippet__lang">%s</span>',
                    esc_html($language)
                );
            }

            if ($show_copy) {
                $parts .= sprintf(
                    '<button type="button" class="dtq-code-snippet__copy" data-dtq-copy'
                        . ' data-copy-label="%1$s" data-copied-label="%2$s">%1$s</button>',
                    esc_attr($copy_label),
                    esc_attr($copied_label)
                );
            }

            $header_html = sprintf('<div class="dtq-code-snippet__header">%s</div>', $parts);
        }

        $start_line = (int) ($advanced['startLine']['desktop']['value'] ?? 1);
        if ($start_line < 1) {
            $start_line = 1;
        }

        $highlight = self::sanitize_highlight_lines($advanced['highlightLines']['desktop']['value'] ?? '');

        // The block scrolls, so it must be focusable; role="region" plus a name
        // means a screen-reader user is told what they have landed in.
        $label = '' !== $file_name
            /* translators: %s: the snippet's file name. */
            ? sprintf(__('Code sample: %s', 'addons-for-divi'), $file_name)
            : __('Code sample', 'addons-for-divi');

        // Prism's line-numbers plugin activates on `pre.line-numbers` — the
        // wrapper modifier alone does nothing, so the gutter has to be asked
        // for on the element the plugin actually looks at.
        $show_numbers = ($advanced['showLineNumbers']['desktop']['value'] ?? 'on') !== 'off';

        $pre_html = sprintf(
            '<pre class="dtq-code-snippet__pre%1$s" tabindex="0" role="region" aria-label="%2$s" data-start="%3$d"%4$s>'
                . '<code class="language-%5$s">%6$s</code></pre>',
            $show_numbers ? ' line-numbers' : '',
            esc_attr($label),
            $start_line,
            '' !== $highlight ? sprintf(' data-line="%s"', esc_attr($highlight)) : '',
            esc_attr($language),
            self::escape_code($code)
        );

        // The copy confirmation is announced rather than only shown.
        $status_html = $show_copy && $show_header
            ? '<span class="dtq-code-snippet__status" role="status" aria-live="polite"></span>'
            : '';

        $children = sprintf(
            '<div class="%1$s">%2$s%3$s%4$s</div>',
            esc_attr(self::wrapper_class($advanced)),
            $header_html,
            $pre_html,
            $status_html
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
