<?php
/**
 * ModalPopup: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\ModalPopup
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\ModalPopup\ModalPopupTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the overlay/close custom declarations (mirrors styles.jsx). Emitted
     * only when set, so they never override the SCSS defaults when left blank.
     *
     * @param string $order_class The module order class selector.
     * @param array  $advanced    The `module.advanced` attrs array.
     *
     * @return array
     */
    public static function build_custom_styles($order_class, $advanced)
    {
        if (!is_array($advanced)) {
            $advanced = [];
        }

        $styles = [];
        $push   = function ($selector, $declaration) use (&$styles) {
            $styles[] = ['selector' => $selector, 'declaration' => $declaration];
        };
        $val = function ($key) use ($advanced) {
            $v = $advanced[$key]['desktop']['value'] ?? null;
            return (null !== $v && '' !== $v) ? $v : null;
        };

        $overlay = $val('overlayColor');
        if ($overlay) {
            $push($order_class . ' .dtq-modal__overlay', sprintf('background-color: %1$s;', $overlay));
        }
        $blur = $val('overlayBlur');
        if ($blur) {
            $push($order_class . ' .dtq-modal__overlay', sprintf('-webkit-backdrop-filter: blur(%1$s); backdrop-filter: blur(%1$s);', $blur));
        }
        $close_color = $val('closeColor');
        if ($close_color) {
            $push($order_class . ' .dtq-modal__close', sprintf('color: %1$s;', $close_color));
        }
        $close_size = $val('closeSize');
        if ($close_size) {
            $push($order_class . ' .dtq-modal__close', sprintf('font-size: %1$s;', $close_size));
        }
        $close_bg = $val('closeBg');
        if ($close_bg) {
            $push($order_class . ' .dtq-modal__close', sprintf('background-color: %1$s;', $close_bg));
        }
        $max_width = $val('maxWidth');
        if ($max_width) {
            $push($order_class . ' .dtq-modal__box', sprintf('max-width: %1$s;', $max_width));
        }
        $dur = $val('animDuration');
        if ($dur) {
            $push($order_class, sprintf('--dtq-modal-dur: %1$s;', $dur));
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
        $custom_styles = self::build_custom_styles($order_class, $advanced);

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
            $elements->style(['attrName' => 'box']),
            $elements->style(['attrName' => 'title']),
            $elements->style(['attrName' => 'content']),
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
