<?php
/**
 * InlineNotice: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\InlineNotice
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\InlineNotice\InlineNoticeTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Pre-made alert type color map. Mirrors the D4 `apply_css` $alerts_data.
     *
     * @return array
     */
    public static function alerts_data()
    {
        return [
            'danger'  => ['color' => '#721c24', 'background' => '#f8d7da', 'link' => '#491217'],
            'warning' => ['color' => '#856404', 'background' => '#fff3cd', 'link' => '#533f03'],
            'info'    => ['color' => '#0c5460', 'background' => '#d1ecf1', 'link' => '#062c33'],
            'ltdark'  => ['color' => '#1b1e21', 'background' => '#d6d8d9', 'link' => '#040505'],
            'dark'    => ['color' => '#ffffff', 'background' => '#626686', 'link' => '#ffffff'],
            'light'   => ['color' => '#818182', 'background' => '#fefefe', 'link' => '#686868'],
        ];
    }

    /**
     * Build the dynamic custom-style array. Mirrors the D4 `apply_css`.
     *
     * @param array  $attrs       Block attributes.
     * @param string $order_class Module order class selector.
     *
     * @return array
     */
    public static function build_dynamic_styles($attrs, $order_class)
    {
        $advanced   = $attrs['module']['advanced'] ?? [];
        $decoration = $attrs['module']['decoration'] ?? [];

        $alert_type   = $advanced['alertType']['desktop']['value'] ?? 'danger';
        $alerts       = self::alerts_data();
        $data         = $alerts[$alert_type] ?? $alerts['danger'];
        $use_icon     = ($advanced['useIcon']['desktop']['value'] ?? 'on') === 'on';
        $use_icon_box = ($advanced['useIconBox']['desktop']['value'] ?? 'off') === 'on';
        $align_items  = $advanced['alignItems']['desktop']['value'] ?? 'center';

        $has_background = !empty($decoration['background']);
        $has_padding    = !empty($decoration['spacing']['desktop']['value']['padding']);

        $icon_color      = $advanced['iconColor']['desktop']['value'] ?? '';
        $icon_bg_color   = $advanced['iconBackgroundColor']['desktop']['value'] ?? '';
        $dismiss_color   = $advanced['dismissColor']['desktop']['value'] ?? '';
        $icon_size       = $advanced['iconSize']['desktop']['value'] ?? '40px';
        $icon_spacing    = $advanced['iconSpacing']['desktop']['value'] ?? '20px';
        $icon_width      = $advanced['iconWidth']['desktop']['value'] ?? '80px';
        $icon_height     = $advanced['iconHeight']['desktop']['value'] ?? '80px';
        $dismiss_size    = $advanced['dismissSize']['desktop']['value'] ?? '22px';
        $dismiss_spacing = $advanced['dismissSpacing']['desktop']['value'] ?? '20px';
        $title_spacing   = $advanced['titleSpacing']['desktop']['value'] ?? '';

        $styles = [];

        $add = function ($selector, $declaration) use (&$styles) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        if (!$has_background) {
            $add($order_class, sprintf('background-color: %1$s;', $data['background']));
        }

        $add(
            $order_class . ', ' . $order_class . ' .dtq-alert-title',
            sprintf('color: %1$s;', $data['color'])
        );

        $add(
            $order_class . ' a, ' . $order_class . ' .dtq-alert-dismiss, ' . $order_class . ' strong, ' . $order_class . ' b',
            sprintf('color: %1$s;', $data['link'])
        );

        if (!$has_padding) {
            $add($order_class, 'padding: 20px;');
        }

        $add($order_class . ' .dtq-alert', sprintf('align-items: %1$s;', $align_items));

        // Icon size.
        if ($use_icon) {
            $add($order_class . ' .dtq-alert-icon', sprintf('font-size: %1$s;', $icon_size));
        } else {
            $add($order_class . ' .dtq-alert-icon img', sprintf('width: %1$s;', $icon_size));
        }

        // Icon color.
        if ($use_icon && $icon_color) {
            $add($order_class . ' .dtq-alert-icon', sprintf('color: %1$s;', $icon_color));
        }

        // Icon spacing.
        $add($order_class . ' .dtq-alert-icon', sprintf('margin-right: %1$s;', $icon_spacing));

        // Icon box.
        if ($use_icon_box) {
            if (!$icon_bg_color) {
                $add($order_class . ' .dtq-alert-icon', 'background-color: rgba(0,0,0,.1);');
            } else {
                $add($order_class . ' .dtq-alert-icon', sprintf('background-color: %1$s;', $icon_bg_color));
            }
            $add($order_class . ' .dtq-alert-icon', 'display: flex; align-items: center; justify-content: center;');
            $add($order_class . ' .dtq-alert-icon', sprintf('width: %1$s;', $icon_width));
            $add($order_class . ' .dtq-alert-icon', sprintf('height: %1$s;', $icon_height));
        } else {
            $add($order_class . ' .dtq-alert-icon', 'overflow: visible!important; border-radius: 0 0 0 0!important;');
        }

        // Dismiss.
        $add($order_class . ' .dtq-alert .dtq-alert-dismiss', sprintf('font-size: %1$s;', $dismiss_size));
        $add($order_class . ' .dtq-alert-dismiss', sprintf('margin-left: %1$s;', $dismiss_spacing));
        if ($dismiss_color) {
            $add($order_class . ' .dtq-alert-dismiss', sprintf('color: %1$s;', $dismiss_color));
        }

        // Title spacing.
        if ($title_spacing) {
            $add($order_class . ' .dtq-alert .dtq-alert-title', sprintf('padding-bottom: %1$s;', $title_spacing));
        }

        // --- Additive responsive (tablet/phone) output. ---
        // Desktop output above is unchanged. The following only appends @media
        // entries for responsive fields whose breakpoint value exists and
        // differs from the next-larger breakpoint.

        // Resolve a breakpoint value for an advanced field, falling back up the
        // chain (phone -> tablet -> desktop) so we can detect "differs".
        $bp_value = function ($key, $breakpoint) use ($advanced) {
            $field = $advanced[$key] ?? [];
            if ($breakpoint === 'desktop') {
                return $field['desktop']['value'] ?? null;
            }
            if ($breakpoint === 'tablet') {
                return $field['tablet']['value']
                    ?? ($field['desktop']['value'] ?? null);
            }
            // phone.
            return $field['phone']['value']
                ?? ($field['tablet']['value'] ?? ($field['desktop']['value'] ?? null));
        };

        // Raw (non-fallback) breakpoint value, used to know whether a value was
        // actually saved for that breakpoint.
        $bp_raw = function ($key, $breakpoint) use ($advanced) {
            return $advanced[$key][$breakpoint]['value'] ?? null;
        };

        $media = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        $add_media = function ($at_rule, $selector, $declaration) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        // Build the declaration for a responsive field given a resolved value.
        // Mirrors the desktop selector/property exactly; only value differs.
        $responsive_decls = function ($value) use (
            $order_class,
            $use_icon,
            $use_icon_box
        ) {
            return [
                // [key, selector, declaration].
                'iconSize' => $use_icon
                    ? [$order_class . ' .dtq-alert-icon', sprintf('font-size: %1$s;', $value)]
                    : [$order_class . ' .dtq-alert-icon img', sprintf('width: %1$s;', $value)],
                'iconSpacing'    => [$order_class . ' .dtq-alert-icon', sprintf('margin-right: %1$s;', $value)],
                'iconWidth'      => $use_icon_box
                    ? [$order_class . ' .dtq-alert-icon', sprintf('width: %1$s;', $value)]
                    : null,
                'iconHeight'     => $use_icon_box
                    ? [$order_class . ' .dtq-alert-icon', sprintf('height: %1$s;', $value)]
                    : null,
                'dismissSize'    => [$order_class . ' .dtq-alert .dtq-alert-dismiss', sprintf('font-size: %1$s;', $value)],
                'dismissSpacing' => [$order_class . ' .dtq-alert-dismiss', sprintf('margin-left: %1$s;', $value)],
                'titleSpacing'   => [$order_class . ' .dtq-alert .dtq-alert-title', sprintf('padding-bottom: %1$s;', $value)],
            ];
        };

        // titleSpacing only renders on desktop when truthy; keep the same gate
        // for responsive so we never introduce output the desktop path wouldn't.
        $responsive_fields = ['iconSize', 'iconSpacing', 'iconWidth', 'iconHeight', 'dismissSize', 'dismissSpacing', 'titleSpacing'];

        foreach (['tablet', 'phone'] as $breakpoint) {
            $larger = ($breakpoint === 'tablet') ? 'desktop' : 'tablet';

            foreach ($responsive_fields as $key) {
                $raw = $bp_raw($key, $breakpoint);
                if ($raw === null || $raw === '') {
                    continue; // No value saved for this breakpoint.
                }

                $larger_val = $bp_value($key, $larger);
                if ((string) $raw === (string) $larger_val) {
                    continue; // Same as next-larger breakpoint; nothing to add.
                }

                if ($key === 'titleSpacing') {
                    // Match desktop gating: skip empty/falsey title spacing.
                    if (!$raw) {
                        continue;
                    }
                }

                $decl = $responsive_decls($raw)[$key] ?? null;
                if ($decl === null) {
                    continue; // Field not applicable in current layout (e.g. icon box off).
                }

                $add_media($media[$breakpoint], $decl[0], $decl[1]);
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

        $dynamic_styles = self::build_dynamic_styles($attrs, $order_class);

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

            // Icon box border styles (mirrors D4 borders['icon']).
            $elements->style(['attrName' => 'icon']),

            // Title font / heading styles.
            $elements->style(['attrName' => 'title']),

            // Body / description font styles.
            $elements->style(['attrName' => 'content']),
        ];

        if (!empty($dynamic_styles)) {
            $all_styles[] = $dynamic_styles;
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
