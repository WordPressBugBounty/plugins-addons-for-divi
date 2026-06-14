<?php
/**
 * TeamBox: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\TeamBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\TeamBox\TeamBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Return the SVG network icon markup keyed by icon slug.
     *
     * Mirrors the D4 `network_icons.php` map.
     *
     * @return array
     */
    public static function network_icons()
    {
        static $icons = null;

        if (null === $icons) {
            $icons = include __DIR__ . '/network-icons.php';
        }

        return is_array($icons) ? $icons : [];
    }

    /**
     * Build the social links list markup.
     *
     * @param array  $advanced          The `module.advanced` attrs array.
     * @param string $content_alignment Content alignment value.
     *
     * @return string
     */
    public static function render_links($advanced, $content_alignment)
    {
        $icons = self::network_icons();

        $links = [
            ['type' => 'website', 'icon' => 'website'],
            ['type' => 'email', 'icon' => 'email'],
            ['type' => 'facebook', 'icon' => 'facebook'],
            ['type' => 'twitter', 'icon' => 'twitter'],
            ['type' => 'linkedin', 'icon' => 'linkedin'],
            ['type' => 'instagram', 'icon' => 'instagram'],
            ['type' => 'github', 'icon' => 'github'],
            ['type' => 'behance', 'icon' => 'behance'],
            ['type' => 'dribbble', 'icon' => 'dribbble'],
        ];

        $html     = '';
        $is_empty = true;

        foreach ($links as $item) {
            $value = $advanced[$item['type']]['desktop']['value'] ?? '';

            if (empty($value)) {
                continue;
            }

            $is_empty    = false;
            $icon        = $icons[$item['icon']] ?? '';
            $href_prefix = ('email' === $item['type']) ? 'mailto:' : '';
            $href        = ('email' === $item['type']) ? sanitize_email($value) : esc_url($value);

            $html .= sprintf(
                '<li><a class="dtq-icon" href="%3$s%2$s"><span>%1$s</span></a></li>',
                $icon,
                $href,
                esc_html($href_prefix)
            );
        }

        if ($is_empty) {
            return '';
        }

        return sprintf(
            '<ul class="dtq-team-social item-%1$s">%2$s</ul>',
            esc_attr($content_alignment),
            $html
        );
    }

    /**
     * Server-side render for the TeamBox module.
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused).
     * @param WP_Block       $block    Parsed block.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $content_on_hover    = $advanced['contentOnHover']['desktop']['value'] ?? 'off';
        $hover_style         = $advanced['hoverStyle']['desktop']['value'] ?? 'hover_1';
        $links_position      = $advanced['linksPosition']['desktop']['value'] ?? 'content';
        $photo_animation     = $advanced['photoHoverAnimation']['desktop']['value'] ?? 'none';
        $content_alignment   = $advanced['contentAlignment']['desktop']['value'] ?? 'left';
        $overlay_on_hover    = $advanced['overlayOnHover']['desktop']['value'] ?? 'off';

        // Photo. The D5 upload field stores the image as an object
        // (`{ src, ... }`); accept both that and a plain URL string.
        $photo_val = $attrs['photo']['innerContent']['desktop']['value'] ?? '';
        $photo_src = is_array($photo_val) ? ($photo_val['src'] ?? '') : $photo_val;
        $photo_alt = $attrs['photo']['advanced']['alt']['desktop']['value'] ?? '';
        $photo     = '';
        if (!empty($photo_src)) {
            $photo = sprintf(
                '<img class="dtq-swapped-img" src="%1$s" alt="%2$s"/>',
                esc_url($photo_src),
                esc_attr($photo_alt)
            );
        }

        // Texts.
        $member_name = $elements->render(['attrName' => 'memberName']);
        $job_title   = $elements->render(['attrName' => 'jobTitle']);
        $short_bio   = $elements->render(['attrName' => 'shortBio']);

        // Classes.
        $classes = ['dtq-module', 'dtq-team', 'dtq-bg-support', 'dtq-swapped-img-selector'];
        if ('on' === $content_on_hover) {
            $classes[] = $hover_style;
        }
        $classes[] = 'dtq-hover--' . $photo_animation;

        // Overlay markup.
        $overlay = '';
        if ('on' === $overlay_on_hover) {
            $overlay_icon = $advanced['overlayIcon']['desktop']['value'] ?? '';

            $overlay_glyph = '';
            if (!empty($overlay_icon)) {
                if (is_array($overlay_icon)) {
                    $uni  = $overlay_icon['unicode'] ?? '';
                    $type = $overlay_icon['type'] ?? 'divi';
                    $wt   = $overlay_icon['weight'] ?? '400';
                } else {
                    $parts = explode('||', (string) $overlay_icon);
                    $uni   = $parts[0] ?? '';
                    $type  = $parts[1] ?? 'divi';
                    $wt    = $parts[2] ?? '400';
                }

                if ('' !== $uni) {
                    $font          = 'fa' === $type ? 'FontAwesome' : 'ETmodules';
                    dtq_inject_fa_icons($uni . '||' . $type . '||' . $wt);
                    $overlay_glyph = sprintf(
                        '<i class="dtq-overlay-icon dtq-et-icon" style="font-family:\'%1$s\';font-weight:%2$s">%3$s</i>',
                        esc_attr($font),
                        esc_attr($wt),
                        dtq_resolve_icon_unicode($uni)
                    );
                }
            }

            $overlay = sprintf(
                '<div class="dtq-overlay">%1$s</div>',
                $overlay_glyph
            );
        }

        // Social links — placed near photo or content based on settings.
        $links_for_photo   = ('off' === $content_on_hover && 'photo' === $links_position)
            ? self::render_links($advanced, $content_alignment)
            : '';
        $links_for_content = ('on' === $content_on_hover || 'content' === $links_position)
            ? self::render_links($advanced, $content_alignment)
            : '';

        $children = sprintf(
            '<div class="%1$s">
                %2$s
                <figure class="dtq-figure dtq-team-figure">
                    %3$s
                    %4$s
                </figure>
                <div class="dtq-team-content content-%5$s">
                    <div class="flex-top">
                        %6$s %7$s %8$s
                    </div>
                    %9$s
                </div>
            </div>',
            esc_attr(implode(' ', $classes)),
            $links_for_photo,
            $overlay,
            $photo,
            esc_attr($content_alignment),
            $member_name,
            $job_title,
            $short_bio,
            $links_for_content
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
                'children'            => $children,
            ]
        );
    }
}
