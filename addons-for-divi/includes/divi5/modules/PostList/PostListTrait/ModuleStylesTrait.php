<?php
/**
 * Post List: module styles trait. Ports the D4 render_css() output.
 *
 * @package DiviTorqueLite\Modules\PostList
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\PostList\PostListTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    protected static function pl_val($attr, $key, $fallback)
    {
        $value = $attr[$key]['desktop']['value'] ?? null;
        return (null === $value || '' === $value) ? $fallback : $value;
    }

    protected static function pl_with_unit($value, $unit)
    {
        if ('' === $value || null === $value) {
            return (string) $value;
        }
        $str = trim((string) $value);
        if (preg_match('/[a-z%]$/i', $str) || 0 === strpos($str, 'calc(') || 0 === strpos($str, 'var(')) {
            return $str;
        }
        return $str . $unit;
    }

    protected static function pl_spacing($val)
    {
        if (empty($val)) {
            return '';
        }
        if (is_array($val)) {
            return sprintf('%s %s %s %s', $val['top'] ?? '0px', $val['right'] ?? '0px', $val['bottom'] ?? '0px', $val['left'] ?? '0px');
        }
        $p = explode('|', (string) $val);
        return 4 === count($p) ? implode(' ', $p) : (string) $val;
    }

    /**
     * Resolve a breakpoint value with the desktop fallback chain.
     */
    protected static function pl_bp($a, $key, $breakpoint)
    {
        $node = $a[$key] ?? [];
        if (isset($node[$breakpoint]['value']) && '' !== $node[$breakpoint]['value']) {
            return $node[$breakpoint]['value'];
        }
        if ('phone' === $breakpoint && isset($node['tablet']['value']) && '' !== $node['tablet']['value']) {
            return $node['tablet']['value'];
        }
        return $node['desktop']['value'] ?? null;
    }

    protected static function pl_bp_raw($a, $key, $breakpoint)
    {
        $v = $a[$key][$breakpoint]['value'] ?? null;
        return '' === $v ? null : $v;
    }

    /**
     * Build the custom style array ported from the D4 render_css().
     *
     * @param string $order_class Module order class.
     * @param array  $advanced    The `module.advanced` attrs.
     *
     * @return array
     */
    public static function build_post_list_styles($order_class, $advanced)
    {
        $styles = [];
        $a      = is_array($advanced) ? $advanced : [];
        $add    = function ($selector, $declaration, $at_rules = false) use (&$styles) {
            if (!empty($declaration)) {
                $styles[] = ['atRules' => $at_rules, 'selector' => $selector, 'declaration' => $declaration];
            }
        };

        $media = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        $alignment    = self::pl_val($a, 'alignment', 'left');
        $list_type    = self::pl_val($a, 'listType', 'list');
        $show_thumb   = self::pl_val($a, 'showThumb', 'on');
        $show_icon    = self::pl_val($a, 'showIcon', 'on');
        $items        = self::pl_val($a, 'items', '4');
        $item_spacing = self::pl_with_unit(self::pl_val($a, 'itemSpacing', '15px'), 'px');
        $item_padding = self::pl_spacing(self::pl_val($a, 'itemPadding', '0px|0px|0px|0px'));
        $icon_color   = self::pl_val($a, 'iconColor', '#555555');

        $oc          = $order_class;
        $child_inner = $oc . ' .dtq-post-list-child-inner';
        $meta_sel    = $oc . ' .dtq-post-list-meta';
        $child_sel   = $oc . ' .dtq-post-list-child';
        $parent_sel  = $oc . ' .dtq-post-list-parent';
        $thumb_img   = $oc . ' .dtq-post-list-thumb img';
        $thumb_sel   = $oc . ' .dtq-post-list-thumb';
        $icon_sel    = $oc . ' .dtq-post-list-icon';
        $content_p   = $oc . ' .dtq-post-list-content p';

        // Alignment.
        $spacing_term = 'bottom';
        if ('left' === $alignment) {
            $spacing_term = 'right';
            $add($child_inner, 'align-items: flex-start;');
        } elseif ('right' === $alignment) {
            $spacing_term = 'left';
            $add($child_inner, 'flex-direction: row-reverse; align-items: flex-start;');
            $add($meta_sel, 'justify-content: flex-end;');
        } else {
            $add($child_inner, 'flex-direction: column; align-items: center;');
            $add($meta_sel, 'justify-content: center;');
        }
        $add($child_inner, sprintf('text-align: %1$s!important;', $alignment));

        // Item padding (responsive).
        if ('' !== $item_padding) {
            $add($child_inner, sprintf('padding: %1$s;', $item_padding));
        }
        foreach (['tablet', 'phone'] as $b) {
            if (null !== self::pl_bp_raw($a, 'itemPadding', $b)) {
                $v = self::pl_spacing(self::pl_bp($a, 'itemPadding', $b));
                if ('' !== $v) {
                    $add($child_inner, sprintf('padding: %1$s;', $v), $media[$b]);
                }
            }
        }

        // Grid vs list.
        if ('grid' === $list_type) {
            $add($child_sel, sprintf('flex: 0 0 calc(100%%/%1$s); max-width: calc(100%%/%1$s); padding: %2$s;', $items, $item_spacing));
            $add($parent_sel, sprintf('margin: -%1$s;', $item_spacing));
            foreach (['tablet', 'phone'] as $b) {
                $it = self::pl_bp($a, 'items', $b);
                $sp = self::pl_with_unit(self::pl_bp($a, 'itemSpacing', $b), 'px');
                $add($child_sel, sprintf('flex: 0 0 calc(100%%/%1$s); max-width: calc(100%%/%1$s); padding: %2$s;', $it, $sp), $media[$b]);
                $add($parent_sel, sprintf('margin: -%1$s;', $sp), $media[$b]);
            }
        } else {
            $add($child_sel, sprintf('padding-bottom: %1$s;', $item_spacing));
            foreach (['tablet', 'phone'] as $b) {
                if (null !== self::pl_bp_raw($a, 'itemSpacing', $b)) {
                    $add($child_sel, sprintf('padding-bottom: %1$s;', self::pl_with_unit(self::pl_bp($a, 'itemSpacing', $b), 'px')), $media[$b]);
                }
            }
        }

        // Meta + excerpt spacing.
        $add($meta_sel, sprintf('padding-top: %1$s;', self::pl_with_unit(self::pl_val($a, 'metaSpacing', '0px'), 'px')));
        $add($content_p, sprintf('padding-top: %1$s;', self::pl_with_unit(self::pl_val($a, 'excerptSpacing', '0px'), 'px')));
        foreach (['tablet', 'phone'] as $b) {
            if (null !== self::pl_bp_raw($a, 'metaSpacing', $b)) {
                $add($meta_sel, sprintf('padding-top: %1$s;', self::pl_with_unit(self::pl_bp($a, 'metaSpacing', $b), 'px')), $media[$b]);
            }
            if (null !== self::pl_bp_raw($a, 'excerptSpacing', $b)) {
                $add($content_p, sprintf('padding-top: %1$s;', self::pl_with_unit(self::pl_bp($a, 'excerptSpacing', $b), 'px')), $media[$b]);
            }
        }

        // Thumbnail.
        if ('on' === $show_thumb) {
            $add($thumb_img, sprintf('width: %1$s!important;', self::pl_with_unit(self::pl_val($a, 'imageWidth', '60px'), 'px')));
            $add($thumb_img, sprintf('height: %1$s!important;', self::pl_with_unit(self::pl_val($a, 'imageHeight', '60px'), 'px')));
            $add($thumb_sel, sprintf('margin-%1$s: %2$s;', $spacing_term, self::pl_with_unit(self::pl_val($a, 'imageSpacing', '12px'), 'px')));
            foreach (['tablet', 'phone'] as $b) {
                if (null !== self::pl_bp_raw($a, 'imageWidth', $b)) {
                    $add($thumb_img, sprintf('width: %1$s!important;', self::pl_with_unit(self::pl_bp($a, 'imageWidth', $b), 'px')), $media[$b]);
                }
                if (null !== self::pl_bp_raw($a, 'imageHeight', $b)) {
                    $add($thumb_img, sprintf('height: %1$s!important;', self::pl_with_unit(self::pl_bp($a, 'imageHeight', $b), 'px')), $media[$b]);
                }
                if (null !== self::pl_bp_raw($a, 'imageSpacing', $b)) {
                    $add($thumb_sel, sprintf('margin-%1$s: %2$s;', $spacing_term, self::pl_with_unit(self::pl_bp($a, 'imageSpacing', $b), 'px')), $media[$b]);
                }
            }
        }

        // Icon.
        if ('on' === $show_icon) {
            $add($icon_sel, sprintf('font-size: %1$s; color: %2$s;', self::pl_with_unit(self::pl_val($a, 'iconSize', '18px'), 'px'), $icon_color));
            $add($icon_sel, sprintf('margin-%1$s: %2$s;', $spacing_term, self::pl_with_unit(self::pl_val($a, 'iconSpacing', '20px'), 'px')));
            foreach (['tablet', 'phone'] as $b) {
                if (null !== self::pl_bp_raw($a, 'iconSize', $b)) {
                    $add($icon_sel, sprintf('font-size: %1$s;', self::pl_with_unit(self::pl_bp($a, 'iconSize', $b), 'px')), $media[$b]);
                }
                if (null !== self::pl_bp_raw($a, 'iconSpacing', $b)) {
                    $add($icon_sel, sprintf('margin-%1$s: %2$s;', $spacing_term, self::pl_with_unit(self::pl_bp($a, 'iconSpacing', $b), 'px')), $media[$b]);
                }
            }
        }

        return $styles;
    }

    /**
     * Generate the module styles.
     *
     * @param array $args Style args.
     *
     * @return void
     */
    public static function module_styles($args)
    {
        $attrs       = $args['attrs'] ?? [];
        $elements    = $args['elements'];
        $settings    = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';

        $advanced      = $attrs['module']['advanced'] ?? [];
        $custom_styles = self::build_post_list_styles($order_class, $advanced);

        $all_styles = [
            $elements->style(
                [
                    'attrName'   => 'module',
                    'styleProps' => [
                        'disabledOn' => [
                            'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                        ],
                    ],
                ]
            ),
            $elements->style(['attrName' => 'thumbImg']),
            $elements->style(['attrName' => 'childInner']),
            $elements->style(['attrName' => 'title']),
            $elements->style(['attrName' => 'excerpt']),
            $elements->style(['attrName' => 'meta']),
        ];

        if (!empty($custom_styles)) {
            $all_styles[] = $custom_styles;
        }

        $all_styles[] = CssStyle::style(
            [
                'selector'  => $order_class,
                'attr'      => $attrs['css'] ?? [],
                'cssFields' => self::custom_css_fields(),
            ]
        );

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => $all_styles,
            ]
        );
    }
}
