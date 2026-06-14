<?php
/**
 * Testimonial: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\Testimonial
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\Testimonial\TestimonialTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * The 5 built-in quote icon SVGs, copied verbatim from the D4 module
     * (includes/modules/divi-4/Testimonial/Testimonial.php::render_quote_icon()).
     *
     * IMPORTANT: keep byte-identical with the JS twin in
     * src/divi5/modules/testimonial/quote-icons.js so the VB and front end
     * render the same markup.
     *
     * @return array
     */
    public static function get_quote_icons()
    {
        return array(
            '1' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33.591 24.006"><g><path d="M232.2,72.969l-1.078,4.955c-7.76-.356-13.138-3.584-13.138-13.078V53.918h13.553V67.653H226.4C226.406,70.7,228.317,72.491,232.2,72.969Z" transform="translate(-198.609 -53.918)"/><path d="M14.216,72.969l-1.078,4.955C5.378,77.569,0,74.341,0,64.846V53.918H13.556V67.654H8.42C8.42,70.7,10.331,72.491,14.216,72.969Z" transform="translate(0 -53.918)"/></g> </svg>',

            '2' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24"><g transform="translate(0 -4)"><g><g><path d="M0,4V28L12,16V4Z"/><path d="M20,4V28L32,16V4Z"/></g></g></g></svg>',

            '3' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36.003 24.007"><path d="M17.66,18.228v1.582a7.789,7.789,0,0,1-.643,4.1A14.32,14.32,0,0,1,14.2,27.624a31.335,31.335,0,0,1-4.9,3.907,18.986,18.986,0,0,1-5.588,2.324.391.391,0,0,1-.593-.2.7.7,0,0,1,.1-.593,35.575,35.575,0,0,0,2.967-3.313,14.785,14.785,0,0,0,1.879-3.115h-.1a10.7,10.7,0,0,1-3.115-.692,5.451,5.451,0,0,1-1.929-1.385Q1.44,22.679,1.44,18.327v-.1q0-4.352,1.484-6.132a7.047,7.047,0,0,1,5.044-2.077,1.533,1.533,0,0,1,.643-.1H10.49a1.533,1.533,0,0,1,.643.1A7.047,7.047,0,0,1,16.177,12.1Q17.66,13.877,17.66,18.228Zm19.781,0v.1a8.193,8.193,0,0,1-.1,1.484,8.137,8.137,0,0,1-.593,4.1,12.666,12.666,0,0,1-2.868,3.709,28.055,28.055,0,0,1-4.8,3.907A18.986,18.986,0,0,1,23.5,33.855h-.1q-.3.2-.495-.2a.43.43,0,0,1,.1-.593,35.575,35.575,0,0,0,2.967-3.313,14.785,14.785,0,0,0,1.879-3.115h-.1a7.184,7.184,0,0,1-5.143-2.077q-1.385-1.78-1.385-6.231v-.1q0-4.451,1.385-6.132a7.184,7.184,0,0,1,5.143-2.077,1.533,1.533,0,0,1,.643-.1H30.27a1.533,1.533,0,0,1,.643.1A7.047,7.047,0,0,1,35.957,12.1q1.484,1.78,1.484,6.132Z" transform="translate(37.443 33.925) rotate(180)"/></svg>',

            '4' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33.453 24.005"><path id="Path_12" data-name="Path 12" d="M.98,24h12.6a.98.98,0,0,0,.981-.98v-12.6a.98.98,0,0,0-.981-.981H8.258V.98A.98.98,0,0,0,7.279,0H4.129A.98.98,0,0,0,3.2.67L.051,10.116A.973.973,0,0,0,0,10.428v12.6A.98.98,0,0,0,.98,24Zm0,0" transform="translate(18.895)"/><path id="Path_13" data-name="Path 13" d="M290.18,24h12.6a.98.98,0,0,0,.98-.98v-12.6a.98.98,0,0,0-.98-.981h-5.319V.98a.98.98,0,0,0-.98-.98h-3.149a.98.98,0,0,0-.93.67l-3.15,9.446a.982.982,0,0,0-.051.31v12.6a.979.979,0,0,0,.98.98Zm0,0" transform="translate(-289.199 0)"/></svg>',

            '5' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33.579 23.997"><g transform="translate(0 -53.918)"><g transform="translate(0 53.918)"><g><path id="Path_14" data-name="Path 14" d="M232.2,58.871l-1.077-4.953c-7.757.356-13.138,3.582-13.138,13.073V77.915h13.551V64.19H226.4C226.4,61.141,228.314,59.349,232.2,58.871Z" transform="translate(-198.616 -53.918)"/><path id="Path_15" data-name="Path 15" d="M14.211,58.871l-1.073-4.953C5.377,54.274,0,57.5,0,66.991V77.915H13.552V64.19H8.416C8.416,61.141,10.326,59.349,14.211,58.871Z" transform="translate(0 -53.918)"/></g></g></g></svg>',
        );
    }

    /**
     * Server-side render for the Testimonial module.
     *
     * Mirrors the D4 render() markup (and the VB edit component) exactly:
     * same class names, same SVG quote icons, same conditional layout matrix
     * (img_position x reviewer_position x ratings_position x review_design).
     * Keep byte-for-byte identical with src/divi5/modules/testimonial/edit.jsx.
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

        // D4 parity (dt_if_not_migrated()): legacy installs (the `ba_version`
        // option exists) shipped different D4 defaults for these fields.
        // module.json carries the NEW-USER default; when the attr is absent on
        // a legacy install we fall back to the legacy default so migrated
        // layouts render unchanged.
        $is_legacy = (bool) get_option('ba_version');

        $alignment         = $advanced['alignment']['desktop']['value'] ?? 'center';
        $img_position      = $advanced['imgPosition']['desktop']['value'] ?? 'relative';
        $reviewer_position = $advanced['reviewerPosition']['desktop']['value'] ?? ($is_legacy ? 'bottom' : 'top');
        $ratings_position  = $advanced['ratingsPosition']['desktop']['value'] ?? ($is_legacy ? '_default' : 'bottom');
        $use_rating        = $advanced['useRating']['desktop']['value'] ?? ($is_legacy ? 'off' : 'on');
        $hide_quote        = $advanced['hideQuote']['desktop']['value'] ?? ($is_legacy ? 'off' : 'on');
        $rating            = (int) ($advanced['rating']['desktop']['value'] ?? '5');
        $icon_placement    = $advanced['iconPlacement']['desktop']['value'] ?? 'background';
        $use_custom_icon   = $advanced['useCustomIcon']['desktop']['value'] ?? 'off';
        $selected_icon     = $advanced['selectedIcon']['desktop']['value'] ?? '5';
        $website_url       = $advanced['websiteUrl']['desktop']['value'] ?? '';
        $company_url       = $advanced['companyUrl']['desktop']['value'] ?? '';
        $link_target       = ('on' === ($advanced['linkNewWindow']['desktop']['value'] ?? 'off')) ? '_blank' : '_self';

        // The D5 upload field stores the image as an object (`{ src, ... }`);
        // accept both that and a plain URL string (the migrated D4 value).
        $image_val = $attrs['image']['innerContent']['desktop']['value'] ?? '';
        $image_src = is_array($image_val) ? ($image_val['src'] ?? '') : $image_val;
        $image_alt = $attrs['image']['advanced']['alt']['desktop']['value'] ?? '';

        $name_val        = $attrs['name']['innerContent']['desktop']['value'] ?? '';
        $title_val       = $attrs['title']['innerContent']['desktop']['value'] ?? '';
        $testimonial_val = $attrs['testimonial']['innerContent']['desktop']['value'] ?? '';

        // Quote icon (mirrors D4 render_quote_icon()): rendered only when the
        // quote is not hidden AND the chosen placement matches. With a custom
        // icon image the span stays empty (the image is a CSS background).
        $icons             = self::get_quote_icons();
        $render_quote_icon = function ($cls, $placement) use ($hide_quote, $icon_placement, $use_custom_icon, $selected_icon, $icons) {
            if ('off' !== $hide_quote || $icon_placement !== $placement) {
                return '';
            }
            $svg = ('off' === $use_custom_icon) ? ($icons[$selected_icon] ?? '') : '';
            return sprintf(
                '<div class="dtq-testimonial-icon %1$s"><span>%2$s</span></div>',
                esc_attr($cls),
                $svg
            );
        };

        // Star rating (mirrors D4 render_rating()).
        $render_rating = function ($pos) use ($use_rating, $ratings_position, $rating) {
            if ('on' !== $use_rating || $ratings_position !== $pos) {
                return '';
            }
            $stars = '';
            for ($i = 0; $i < $rating; $i++) {
                $stars .= '<span>★</span>';
            }
            return sprintf('<div class="dtq-testimonial-rating">%1$s</div>', $stars);
        };

        // Reviewer image (mirrors D4 _render_image()).
        $render_image = function ($positions) use ($image_src, $image_alt, $img_position) {
            if (empty($image_src) || !in_array($img_position, $positions, true)) {
                return '';
            }
            return sprintf(
                '<figure class="dtq-testimonial-img"><img class="dtq-img-cover" src="%1$s" alt="%2$s"/></figure>',
                esc_url($image_src),
                esc_attr($image_alt)
            );
        };

        // Reviewer name/title — rendered through elements->render (dynamic
        // content) and wrapped in the optional D4 link.
        $name_html = '';
        if ('' !== $name_val) {
            $name_inner = $elements->render(['attrName' => 'name']);
            $name_html  = !empty($website_url)
                ? sprintf('<a href="%1$s" target="%2$s">%3$s</a>', esc_url($website_url), esc_attr($link_target), $name_inner)
                : $name_inner;
        }

        $title_html = '';
        if ('' !== $title_val) {
            $title_inner = $elements->render(['attrName' => 'title']);
            $title_html  = !empty($company_url)
                ? sprintf('<a href="%1$s" target="%2$s">%3$s</a>', esc_url($company_url), esc_attr($link_target), $title_inner)
                : $title_inner;
        }

        // Reviewer block (mirrors D4 render_reviewer()).
        $render_reviewer = function ($pos) use ($reviewer_position, $render_image, $name_html, $title_html, $render_rating) {
            if ($reviewer_position !== $pos) {
                return '';
            }
            return sprintf(
                '<div class="dtq-testimonial-reviewer">%1$s<div class="dtq-testimonial-reviewer-text">%2$s%3$s%4$s</div></div>',
                $render_image(array('relative')),
                $name_html,
                $title_html,
                $render_rating('reviewer')
            );
        };

        // Review block (mirrors D4 render_review()): the default-placement
        // quote icon lives INSIDE the review box, before the testimonial text.
        $review_html = sprintf(
            '<div class="dtq-testimonial-review">%1$s%2$s</div>',
            $render_quote_icon('dtq-icon-default', '_default'),
            ('' !== $testimonial_val) ? sprintf('<p>%1$s</p>', $testimonial_val) : ''
        );

        $children = sprintf(
            '<div class="dtq-module dtq-testimonial dtq-align-%1$s">%2$s%3$s<div class="dtq-testimonial-inner dtq-bg-support img-pos-%4$s">%5$s%6$s<div class="dtq-testimonial-content">%7$s%8$s%9$s%10$s%11$s</div>%12$s</div></div>',
            esc_attr($alignment),
            $render_quote_icon('dtq-icon-absolute', 'absolute'),
            $render_image(array('absolute')),
            esc_attr($img_position),
            $render_quote_icon('dtq-icon-bg', 'background'),
            $render_image(array('top', 'left')),
            $render_reviewer('top'),
            $render_rating('_default'),
            $review_html,
            $render_reviewer('bottom'),
            $render_rating('bottom'),
            $render_image(array('right'))
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
