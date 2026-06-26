<?php
/**
 * Contact Form 7: module styles trait. Ports the D4 apply_css()/render_header_css().
 *
 * @package DiviTorqueLite\Modules\ContactForm7
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\ContactForm7\ContactForm7Trait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    protected static function cf_val($a, $key, $fallback)
    {
        $v = $a[$key]['desktop']['value'] ?? null;
        return (null === $v || '' === $v) ? $fallback : $v;
    }

    protected static function cf_unit($value, $unit)
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

    protected static function cf_spacing($val)
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

    protected static function cf_bp($a, $key, $b)
    {
        $node = $a[$key] ?? [];
        if (isset($node[$b]['value']) && '' !== $node[$b]['value']) {
            return $node[$b]['value'];
        }
        if ('phone' === $b && isset($node['tablet']['value']) && '' !== $node['tablet']['value']) {
            return $node['tablet']['value'];
        }
        return $node['desktop']['value'] ?? null;
    }

    protected static function cf_bp_raw($a, $key, $b)
    {
        $v = $a[$key][$b]['value'] ?? null;
        return '' === $v ? null : $v;
    }

    /**
     * Build the custom style array ported from the D4 CF7 styling.
     *
     * @param string $oc       Module order class.
     * @param array  $advanced The `module.advanced` attrs.
     *
     * @return array
     */
    public static function build_cf7_styles($oc, $advanced)
    {
        $styles = [];
        $a      = is_array($advanced) ? $advanced : [];
        $media  = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];
        $add = function ($selector, $declaration, $at = false) use (&$styles) {
            if (!empty($declaration)) {
                $styles[] = ['atRules' => $at, 'selector' => $selector, 'declaration' => $declaration];
            }
        };

        // ---- Header ----
        $add($oc . ' .dtq-form-header .dtq-form-header-title', sprintf('padding-bottom: %s!important;', self::cf_unit(self::cf_val($a, 'headerTitleSpacing', '0px'), 'px')));
        if (self::cf_val($a, 'formHeaderBg', '')) {
            $add($oc . ' .dtq-form-header-container', sprintf('background-color: %s!important;', self::cf_val($a, 'formHeaderBg', '')));
        }
        $add($oc . ' .dtq-form-header-container', sprintf('margin-bottom: %s!important;', self::cf_unit(self::cf_val($a, 'formHeaderBottom', '0px'), 'px')));
        if (self::cf_val($a, 'formHeaderImgBg', '')) {
            $add($oc . ' .dtq-form-header-icon, ' . $oc . ' .dtq-form-header-image', sprintf('background-color: %s!important;', self::cf_val($a, 'formHeaderImgBg', '')));
        }
        $hdr_icon_pad = self::cf_spacing(self::cf_val($a, 'formHeaderIconPadding', '15px|15px|15px|15px'));
        if ('' !== $hdr_icon_pad) {
            $add($oc . ' .dtq-form-header-icon, ' . $oc . ' .dtq-form-header-image', sprintf('padding: %s!important;', $hdr_icon_pad));
        }
        if (self::cf_val($a, 'formHeaderIconColor', '')) {
            $add($oc . ' .dtq-form-header-icon span', sprintf('color: %s!important;', self::cf_val($a, 'formHeaderIconColor', '')));
        }
        $add($oc . ' .dtq-form-header-icon span', sprintf('font-size: %s;', self::cf_unit(self::cf_val($a, 'formHeaderIconSize', '32px'), 'px')));
        foreach (['tablet', 'phone'] as $b) {
            if (null !== self::cf_bp_raw($a, 'formHeaderIconSize', $b)) {
                $add($oc . ' .dtq-form-header-icon span', sprintf('font-size: %s;', self::cf_unit(self::cf_bp($a, 'formHeaderIconSize', $b), 'px')), $media[$b]);
            }
        }
        if (self::cf_val($a, 'formBg', '')) {
            $add($oc . ' .dtq-cf7-styler', sprintf('background-color: %s!important;', self::cf_val($a, 'formBg', '')));
        }

        $hdr_pad = self::cf_spacing(self::cf_val($a, 'formHeaderPadding', '0px|0px|0px|0px'));
        if ('' !== $hdr_pad) {
            $add($oc . ' .dtq-form-header-container', sprintf('padding: %s!important;', $hdr_pad));
        }
        foreach (['tablet', 'phone'] as $b) {
            if (null !== self::cf_bp_raw($a, 'formHeaderPadding', $b)) {
                $v = self::cf_spacing(self::cf_bp($a, 'formHeaderPadding', $b));
                if ('' !== $v) {
                    $add($oc . ' .dtq-form-header-container', sprintf('padding: %s!important;', $v), $media[$b]);
                }
            }
        }

        $form_pad = self::cf_spacing(self::cf_val($a, 'formPadding', '0px|0px|0px|0px'));
        if ('' !== $form_pad) {
            $add($oc . ' .dtq-cf7-styler', sprintf('padding: %s;', $form_pad));
        }
        foreach (['tablet', 'phone'] as $b) {
            if (null !== self::cf_bp_raw($a, 'formPadding', $b)) {
                $v = self::cf_spacing(self::cf_bp($a, 'formPadding', $b));
                if ('' !== $v) {
                    $add($oc . ' .dtq-cf7-styler', sprintf('padding: %s;', $v), $media[$b]);
                }
            }
        }

        // ---- Fields ----
        $field_sel = $oc . ' .dtq-cf7-styler .wpcf7 input:not([type="submit"]):not([type="checkbox"]):not([type="radio"]), ' . $oc . ' .dtq-cf7-styler .wpcf7 select, ' . $oc . ' .dtq-cf7-styler .wpcf7 textarea';

        if ('on' === self::cf_val($a, 'useFormButtonFullwidth', 'off')) {
            $add($oc . ' .dtq-cf7 .wpcf7 input[type=submit], ' . $oc . ' .wpcf7-form button.wpcf7-submit', 'width: 100% !important;');
        }
        if (self::cf_val($a, 'formBackgroundColor', '')) {
            $add($oc . ' .dtq-cf7-styler input:not([type=submit]), ' . $oc . ' .dtq-cf7-styler select, ' . $oc . ' .dtq-cf7-styler textarea, ' . $oc . ' .dtq-cf7 .wpcf7-checkbox input[type="checkbox"] + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-radio input[type="radio"]:not(:checked) + span:before', sprintf('background-color: %s!important;', self::cf_val($a, 'formBackgroundColor', '')));
        }
        if (self::cf_val($a, 'formFieldHeight', '')) {
            $add($field_sel, sprintf('height: %s!important;', self::cf_unit(self::cf_val($a, 'formFieldHeight', ''), 'px')));
            foreach (['tablet', 'phone'] as $b) {
                if (null !== self::cf_bp_raw($a, 'formFieldHeight', $b)) {
                    $add($field_sel, sprintf('height: %s!important;', self::cf_unit(self::cf_bp($a, 'formFieldHeight', $b), 'px')), $media[$b]);
                }
            }
        }
        $field_pad = self::cf_spacing(self::cf_val($a, 'formFieldPadding', '0px|15px|0px|15px'));
        if ('' !== $field_pad) {
            $add($field_sel, sprintf('padding: %s!important;', $field_pad));
        }
        foreach (['tablet', 'phone'] as $b) {
            if (null !== self::cf_bp_raw($a, 'formFieldPadding', $b)) {
                $v = self::cf_spacing(self::cf_bp($a, 'formFieldPadding', $b));
                if ('' !== $v) {
                    $add($field_sel, sprintf('padding: %s!important;', $v), $media[$b]);
                }
            }
        }
        if (self::cf_val($a, 'formFieldActiveColor', '')) {
            $add($oc . ' .dtq-cf7 .wpcf7 input:not([type=submit]):focus, ' . $oc . ' .dtq-cf7 .wpcf7 select:focus, ' . $oc . ' .dtq-cf7 .wpcf7 textarea:focus', sprintf('border-color: %s!important;', self::cf_val($a, 'formFieldActiveColor', '')));
        }

        // ---- Checkbox / Radio ----
        if ('on' === self::cf_val($a, 'crCustomStyles', 'off')) {
            $cr_size   = self::cf_val($a, 'crSize', '20px');
            $cr_border = self::cf_val($a, 'crBorderSize', '1px');
            $add($oc . ' .dtq-cf7 .wpcf7-checkbox input[type="checkbox"] + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-radio input[type="radio"] + span:before', sprintf('width: %1$s!important; height: %1$s!important; border-width: %2$s!important;', self::cf_unit($cr_size, 'px'), self::cf_unit($cr_border, 'px')));
            if (is_numeric($cr_size)) {
                $add($oc . ' .dtq-cf7 .wpcf7-acceptance input[type=checkbox]:checked + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-checkbox input[type=checkbox]:checked + span:before', sprintf('font-size: %spx!important;', $cr_size / 1.2));
            }
            if (self::cf_val($a, 'crBackgroundColor', '')) {
                $cbg = self::cf_val($a, 'crBackgroundColor', '');
                $add($oc . ' .dtq-cf7 .wpcf7-checkbox input[type="checkbox"] + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-radio input[type="radio"]:not(:checked) + span:before', sprintf('background-color: %s!important;', $cbg));
                $add($oc . ' .dtq-cf7 .wpcf7-radio input[type="radio"]:checked + span:before', sprintf('box-shadow: inset 0px 0px 0px 4px %s!important;', $cbg));
            }
            if (self::cf_val($a, 'crSelectedColor', '')) {
                $csc = self::cf_val($a, 'crSelectedColor', '');
                $add($oc . ' .dtq-cf7 .wpcf7-checkbox input[type="checkbox"]:checked + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-acceptance input[type="checkbox"]:checked + span:before', sprintf('color: %s!important;', $csc));
                $add($oc . ' .dtq-cf7 .wpcf7-radio input[type="radio"]:checked + span:before', sprintf('background-color: %s!important;', $csc));
            }
            if (self::cf_val($a, 'crBorderColor', '')) {
                $add($oc . ' .dtq-cf7 .wpcf7-checkbox input[type=radio] + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-radio input[type=checkbox] + span:before, ' . $oc . ' .dtq-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before', sprintf('border-color: %s!important;', self::cf_val($a, 'crBorderColor', '')));
            }
            if (self::cf_val($a, 'crLabelColor', '')) {
                $add($oc . ' .dtq-cf7 .wpcf7-checkbox label, ' . $oc . ' .wpcf7-radio label', sprintf('color: %s!important;', self::cf_val($a, 'crLabelColor', '')));
            }
        }

        // ---- Messages ----
        $tip = $oc . ' .dtq-cf7-styler span.wpcf7-not-valid-tip';
        if (self::cf_val($a, 'cf7MessageColor', '')) { $add($tip, sprintf('color: %s!important;', self::cf_val($a, 'cf7MessageColor', ''))); }
        if (self::cf_val($a, 'cf7MessageBgColor', '')) { $add($tip, sprintf('background-color: %s!important;', self::cf_val($a, 'cf7MessageBgColor', ''))); }
        if (self::cf_val($a, 'cf7BorderHighlightColor', '')) { $add($tip, sprintf('border-color: %s!important;', self::cf_val($a, 'cf7BorderHighlightColor', ''))); }
        $ok = $oc . ' .dtq-cf7-styler .wpcf7-mail-sent-ok';
        if (self::cf_val($a, 'cf7SuccessMessageColor', '')) { $add($ok, sprintf('color: %s!important;', self::cf_val($a, 'cf7SuccessMessageColor', ''))); }
        if (self::cf_val($a, 'cf7SuccessMessageBgColor', '')) { $add($ok, sprintf('background-color: %s!important;', self::cf_val($a, 'cf7SuccessMessageBgColor', ''))); }
        if (self::cf_val($a, 'cf7SuccessBorderColor', '')) { $add($ok, sprintf('border-color: %s!important;', self::cf_val($a, 'cf7SuccessBorderColor', ''))); }
        $err = $oc . ' .wpcf7-validation-errors';
        if (self::cf_val($a, 'cf7ErrorMessageColor', '')) { $add($err, sprintf('color: %s!important;', self::cf_val($a, 'cf7ErrorMessageColor', ''))); }
        if (self::cf_val($a, 'cf7ErrorMessageBgColor', '')) { $add($err, sprintf('background-color: %s!important;', self::cf_val($a, 'cf7ErrorMessageBgColor', ''))); }
        if (self::cf_val($a, 'cf7ErrorBorderColor', '')) { $add($err, sprintf('border-color: %s!important;', self::cf_val($a, 'cf7ErrorBorderColor', ''))); }
        if (self::cf_val($a, 'cf7MessagePadding', '')) { $add($oc . ' span.wpcf7-not-valid-tip', sprintf('padding: %s!important;', self::cf_unit(self::cf_val($a, 'cf7MessagePadding', ''), 'px'))); }
        if (self::cf_val($a, 'cf7MessageMarginTop', '')) { $add($oc . ' span.wpcf7-not-valid-tip', sprintf('margin-top: %s!important;', self::cf_unit(self::cf_val($a, 'cf7MessageMarginTop', ''), 'px'))); }

        // ---- Label + field spacing (responsive) ----
        $ctrl = $oc . ' .dtq-cf7 .wpcf7-form-control:not(.wpcf7-submit)';
        $add($ctrl, sprintf('margin-top: %s;', self::cf_unit(self::cf_val($a, 'formLabelSpacing', '7px'), 'px')));
        $add($ctrl, sprintf('margin-bottom: %s;', self::cf_unit(self::cf_val($a, 'formFieldSpacing', '20px'), 'px')));
        foreach (['tablet', 'phone'] as $b) {
            if (null !== self::cf_bp_raw($a, 'formLabelSpacing', $b)) {
                $add($ctrl, sprintf('margin-top: %s;', self::cf_unit(self::cf_bp($a, 'formLabelSpacing', $b), 'px')), $media[$b]);
            }
            if (null !== self::cf_bp_raw($a, 'formFieldSpacing', $b)) {
                $add($ctrl, sprintf('margin-bottom: %s;', self::cf_unit(self::cf_bp($a, 'formFieldSpacing', $b), 'px')), $media[$b]);
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
        $custom_styles = self::build_cf7_styles($order_class, $advanced);

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
            $elements->style(['attrName' => 'field']),
            $elements->style(['attrName' => 'label']),
            $elements->style(['attrName' => 'placeholder']),
            $elements->style(['attrName' => 'headerTitle']),
            $elements->style(['attrName' => 'headerText']),
            $elements->style(['attrName' => 'submitButton']),
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
