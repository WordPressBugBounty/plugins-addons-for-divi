<?php
/**
 * CodeSnippet: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\CodeSnippet
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CodeSnippet\CodeSnippetTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Breakpoint media queries, matching Divi's own.
     */
    private static $dtq_cs_media = [
        'tablet' => '@media only screen and (max-width: 980px)',
        'phone'  => '@media only screen and (max-width: 767px)',
    ];

    /**
     * A breakpoint's own saved value, with no inheritance from a larger one.
     *
     * Telling "saved" apart from "inherited" is what stops a tablet rule being
     * emitted that is identical to the desktop one.
     *
     * @param array  $attr       Responsive attribute array.
     * @param string $breakpoint desktop|tablet|phone.
     *
     * @return string|null
     */
    private static function raw_value($attr, $breakpoint)
    {
        return $attr[$breakpoint]['value'] ?? null;
    }

    /**
     * A breakpoint's effective value, falling back up the chain.
     *
     * @param array  $attr       Responsive attribute array.
     * @param string $breakpoint desktop|tablet|phone.
     *
     * @return string|null
     */
    private static function effective_value($attr, $breakpoint)
    {
        if ('desktop' === $breakpoint) {
            return $attr['desktop']['value'] ?? null;
        }
        if ('tablet' === $breakpoint) {
            return $attr['tablet']['value'] ?? ($attr['desktop']['value'] ?? null);
        }

        return $attr['phone']['value']
            ?? ($attr['tablet']['value'] ?? ($attr['desktop']['value'] ?? null));
    }

    /**
     * Build the module's custom properties.
     *
     * PHP twin of buildCustomStyles() in src/divi5/modules/code-snippet/styles.jsx.
     * The two must emit the same properties from the same attributes: styles.jsx
     * drives the builder preview and this drives the published page, so a
     * property present in one and missing from the other is a setting that looks
     * applied while editing and disappears on save.
     *
     * Every value is interpolated into a stylesheet, so every value goes through
     * the sanitisers in includes/functions.php first.
     *
     * @param array  $advanced    The `module.advanced` attrs array.
     * @param string $order_class Module order class selector.
     *
     * @return array List of ['atRules' => string|false, 'selector' => string, 'declaration' => string].
     */
    public static function build_custom_styles($advanced, $order_class)
    {
        if (!is_array($advanced)) {
            $advanced = [];
        }

        $properties = [
            '--dtq-cs-maxh' => $advanced['maxHeight'] ?? [],
        ];

        $styles = [];

        $desktop = [];
        foreach ($properties as $property => $attr) {
            $value = self::effective_value($attr, 'desktop');
            if (null === $value || '' === $value) {
                continue;
            }
            $length = dtq_css_length($value, '');
            // maxHeight uses 0 as its "no limit" sentinel (styles.jsx skips
            // non-positive values) — emitting 0px collapses the pre.
            if ('' !== $length && (float) $length > 0) {
                $desktop[] = $property . ': ' . $length . ';';
            }
        }

        // Non-responsive values ride along with the desktop block.
        foreach ([
            'bgColor'     => '--dtq-cs-bg',
            'textColor'   => '--dtq-cs-fg',
            'headerBg'    => '--dtq-cs-hdr-bg',
            'headerColor' => '--dtq-cs-hdr-fg',
        ] as $key => $property) {
            $raw = $advanced[$key]['desktop']['value'] ?? '';
            if ('' === $raw) {
                continue;
            }
            $colour = dtq_css_color(dtq_resolve_css_value($raw), '');
            if ('' !== $colour) {
                $desktop[] = $property . ': ' . $colour . ';';
            }
        }

        $lengths = [
            'radius' => '--dtq-cs-radius',
        ];
        foreach ($lengths as $key => $property) {
            $raw = $advanced[$key]['desktop']['value'] ?? '';
            if ('' === $raw) {
                continue;
            }
            $length = dtq_css_length($raw, '');
            if ('' !== $length) {
                $desktop[] = $property . ': ' . $length . ';';
            }
        }


        if (!empty($desktop)) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class,
                'declaration' => implode(' ', $desktop),
            ];
        }

        foreach (['tablet', 'phone'] as $breakpoint) {
            $larger = 'tablet' === $breakpoint ? 'desktop' : 'tablet';

            $declarations = [];
            foreach ($properties as $property => $attr) {
                $value = self::raw_value($attr, $breakpoint);
                if (null === $value || '' === $value) {
                    continue;
                }
                if ((string) $value === (string) self::effective_value($attr, $larger)) {
                    continue;
                }
                $length = dtq_css_length($value, '');
                if ('' === $length) {
                    continue;
                }
                // 0 = "no limit": at a breakpoint that means lift the cap
                // inherited from the larger screen, not a 0px box.
                $declarations[] = $property . ': ' . ((float) $length > 0 ? $length : 'none') . ';';
            }

            if (!empty($declarations)) {
                $styles[] = [
                    'atRules'     => self::$dtq_cs_media[$breakpoint],
                    'selector'    => $order_class,
                    'declaration' => implode(' ', $declarations),
                ];
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
        $custom_styles = self::build_custom_styles($advanced, $order_class);

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

            $elements->style(['attrName' => 'codeText']),
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
