<?php
/**
 * ScrollImage: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\ScrollImage
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\ScrollImage\ScrollImageTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the custom Scroll Image declarations from the flat
     * `module.advanced` attrs.
     *
     * Ports the D4 `render_css()` set_style calls into the custom-style array
     * shape: `['atRules' => false, 'selector' => ..., 'declaration' => ...]`.
     * Must stay byte-identical to `buildScrollImageStyles()` in styles.jsx so
     * the front end matches the VB.
     *
     * @param array  $advanced    The `module.advanced` attrs array.
     * @param string $order_class The module order class.
     * @return array
     */
    public static function build_scroll_image_styles($advanced, $order_class)
    {
        if (!is_array($advanced)) {
            return [];
        }

        $styles = [];

        $show_icon         = ($advanced['showIcon']['desktop']['value'] ?? 'off') === 'on';
        $use_image         = ($advanced['useImage']['desktop']['value'] ?? 'off') === 'on';
        $use_icon_anim     = ($advanced['useIconAnim']['desktop']['value'] ?? 'off') === 'on';
        $icon_color        = $advanced['iconColor']['desktop']['value'] ?? '#2EA3F2';
        $icon_size         = $advanced['iconSize']['desktop']['value'] ?? '48px';
        $scroll_type       = $advanced['scrollType']['desktop']['value'] ?? 'on_hover';
        $scroll_dir_scroll = $advanced['scrollDirScroll']['desktop']['value'] ?? 'vertical';
        $scroll_dir_hover  = $advanced['scrollDirHover']['desktop']['value'] ?? 'Y_btt';
        $scroll_speed      = $advanced['scrollSpeed']['desktop']['value'] ?? '500ms';
        $img_height        = $advanced['imgHeight']['desktop']['value'] ?? '200px';

        // Icon animation (direction derived exactly like D4).
        if ($use_icon_anim) {
            if ('on_scroll' === $scroll_type) {
                $anim_dir = 'vertical' === $scroll_dir_scroll ? 'Y' : 'X';
            } else {
                $anim_dir = substr((string) $scroll_dir_hover, 0, 1);
            }
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-scroll-image-icon-el',
                'declaration' => sprintf('animation-name: dtq-scroll-%1$s; animation-duration: .5s; animation-iteration-count: infinite; animation-direction: alternate; animation-timing-function: ease-in-out;', $anim_dir),
            ];
        }

        // Icon color/size.
        if ($show_icon) {
            if (!$use_image) {
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .dtq-scroll-image-icon-el',
                    'declaration' => sprintf('color: %1$s; font-size: %2$s;', $icon_color, $icon_size),
                ];
            } else {
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .dtq-scroll-image-icon img',
                    'declaration' => sprintf('width: %1$s;', $icon_size),
                ];
            }
        }

        // Scroll behavior styling.
        if ('on_scroll' === $scroll_type) {
            if ('vertical' === $scroll_dir_scroll) {
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .dtq-scroll-image',
                    'declaration' => 'overflow-y: auto;overflow-x:hidden;',
                ];
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .dtq-scroll-image .dtq-scroll-image-el',
                    'declaration' => 'max-width: 100%;width: 100%;',
                ];
            } else {
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .dtq-scroll-image',
                    'declaration' => 'overflow-y:hidden;overflow-x: auto;',
                ];
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .dtq-scroll-image .dtq-scroll-image-el',
                    'declaration' => 'height: 100%; max-width: none;width: auto;',
                ];
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .scroll-figure-wrap',
                    'declaration' => 'height: 100%;width: 100%;',
                ];
            }
        } elseif ('on_hover' === $scroll_type) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .scroll-figure-wrap',
                'declaration' => 'height:100%;width:100%;',
            ];
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-scroll-image',
                'declaration' => 'overflow: hidden;',
            ];
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-scroll-image .dtq-scroll-image-el',
                'declaration' => sprintf('position:absolute;transition: %1$s;', $scroll_speed),
            ];

            if ('X_ltr' === $scroll_dir_hover || 'X_rtl' === $scroll_dir_hover) {
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .dtq-scroll-image .dtq-scroll-image-el',
                    'declaration' => 'height: 100%; max-width: none;width: auto;top:0;',
                ];
                if ('X_ltr' === $scroll_dir_hover) {
                    $styles[] = [
                        'atRules'     => false,
                        'selector'    => $order_class . ' .dtq-scroll-image .dtq-scroll-image-el',
                        'declaration' => 'right:0;',
                    ];
                } else {
                    $styles[] = [
                        'atRules'     => false,
                        'selector'    => $order_class . ' .dtq-scroll-image .dtq-scroll-image-el',
                        'declaration' => 'left:0;',
                    ];
                }
            } else {
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .dtq-scroll-image .dtq-scroll-image-el',
                    'declaration' => 'max-width: 100%;width: 100%; left:0;',
                ];
                if ('Y_ttb' === $scroll_dir_hover) {
                    $styles[] = [
                        'atRules'     => false,
                        'selector'    => $order_class . ' .dtq-scroll-image .dtq-scroll-image-el',
                        'declaration' => 'bottom:0;',
                    ];
                } elseif ('Y_btt' === $scroll_dir_hover) {
                    $styles[] = [
                        'atRules'     => false,
                        'selector'    => $order_class . ' .dtq-scroll-image .dtq-scroll-image-el',
                        'declaration' => 'top:0;',
                    ];
                }
            }
        }

        // Image height (desktop).
        $styles[] = [
            'atRules'     => false,
            'selector'    => $order_class . ' .dtq-scroll-image',
            'declaration' => sprintf('height: %1$s;', $img_height),
        ];

        // Additive responsive (tablet/phone) image height.
        $breakpoints = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        foreach ($breakpoints as $breakpoint => $at_rule) {
            $larger = 'phone' === $breakpoint ? 'tablet' : 'desktop';

            $val = $advanced['imgHeight'][$breakpoint]['value'] ?? null;
            if (null === $val) {
                continue;
            }
            $larger_val = $advanced['imgHeight'][$larger]['value']
                ?? ($advanced['imgHeight']['desktop']['value'] ?? null);
            if ($val === $larger_val) {
                continue;
            }
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $order_class . ' .dtq-scroll-image',
                'declaration' => sprintf('height: %1$s;', $val),
            ];
        }

        return $styles;
    }

    /**
     * Generate the module styles.
     *
     * @param array $args Style args.
     * @return void
     */
    public static function module_styles($args)
    {
        $attrs       = $args['attrs'] ?? [];
        $elements    = $args['elements'];
        $settings    = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';

        $advanced      = $attrs['module']['advanced'] ?? [];
        $custom_styles = self::build_scroll_image_styles($advanced, $order_class);

        $all_styles = [
            // Module wrapper styles (background, spacing, border, etc.).
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

            // Overlay background styles.
            $elements->style(['attrName' => 'overlay']),

            // Icon border styles.
            $elements->style(['attrName' => 'icon']),
        ];

        if (!empty($custom_styles)) {
            $all_styles[] = $custom_styles;
        }

        // Custom CSS (Advanced tab).
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
