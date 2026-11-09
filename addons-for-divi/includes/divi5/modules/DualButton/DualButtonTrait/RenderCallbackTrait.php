<?php
/**
 * DualButton: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\DualButton
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\DualButton\DualButtonTrait;

if (!defined('ABSPATH')) {
    exit;
}

use DiviTorqueLite\Modules\Shared\DynamicValue;
use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Build the data-icon attributes for a button.
     *
     * btnA/btnB carry Divi's own `divi/button` group, so Divi generates the icon
     * CSS for them — and that CSS is `content: attr(data-icon)`. The glyph lives
     * in the markup, not the stylesheet. This anchor is hand-built rather than
     * produced by Divi's ButtonComponent, so it never carried the attribute and
     * the icon had nowhere to come from: every button-icon setting looked dead
     * while the CSS for it was sitting right there in the page.
     *
     * Mirrors ButtonComponent's own resolution, including the tablet and phone
     * variants its responsive rules read.
     *
     * @param array $button_attr The btnA/btnB `decoration.button` attr array.
     *
     * @return string Ready-to-print attribute string, empty when there is no icon.
     */
    protected static function button_icon_attrs($button_attr)
    {
        if (!class_exists('\ET\Builder\Packages\IconLibrary\IconFont\Utils')) {
            return '';
        }

        $out = '';

        foreach (['desktop' => 'data-icon', 'tablet' => 'data-icon-tablet', 'phone' => 'data-icon-phone'] as $breakpoint => $attr_name) {
            $settings = $button_attr[$breakpoint]['value']['icon']['settings'] ?? null;

            // Only a well-formed icon value produces an attribute. Divi core
            // fatals on a malformed one (a responsive-wrapped scalar where it
            // expects a string) rather than ignoring it, so do not pass junk on.
            if (empty($settings) || !is_array($settings)) {
                continue;
            }

            $glyph = \ET\Builder\Packages\IconLibrary\IconFont\Utils::escape_font_icon(
                \ET\Builder\Packages\IconLibrary\IconFont\Utils::process_font_icon($settings)
            );

            if ('' === $glyph || null === $glyph) {
                continue;
            }

            $out .= sprintf(' %1$s="%2$s"', $attr_name, esc_attr($glyph));
        }

        return $out;
    }

    /**
     * Render a single button anchor.
     *
     * @param string $text        Button text.
     * @param string $link        Button URL.
     * @param string $target      Link target (`_self`|`_blank`).
     * @param string $modifier    Button modifier class (`primary`|`secondary`).
     * @param array  $button_attr The button's `decoration.button` attr array.
     *
     * @return string
     */
    public static function render_button($text, $link, $target, $modifier, $button_attr = [])
    {
        return sprintf(
            '<a class="et_pb_button btn-el btn-el--%1$s" href="%2$s" target="%3$s"%5$s>%4$s</a>',
            esc_attr($modifier),
            esc_url($link),
            esc_attr($target),
            et_core_esc_previously($text),
            self::button_icon_attrs($button_attr)
        );
    }

    /**
     * Render the optional connector between the two buttons.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return string
     */
    public static function render_connector($advanced)
    {
        $connector_type = $advanced['connectorType']['desktop']['value'] ?? 'empty';

        if ('empty' === $connector_type) {
            return '';
        }

        $inner = '';

        if ('text' === $connector_type) {
            $inner = esc_html($advanced['connectorText']['desktop']['value'] ?? '');
        } elseif ('icon' === $connector_type) {
            $icon_raw = $advanced['connectorIcon']['desktop']['value'] ?? '';

            if (is_array($icon_raw)) {
                $uni    = $icon_raw['unicode'] ?? '';
                $type   = $icon_raw['type'] ?? 'divi';
                $weight = $icon_raw['weight'] ?? '400';
            } else {
                $parts  = explode('||', (string) $icon_raw);
                $uni    = $parts[0] ?? '';
                $type   = $parts[1] ?? 'divi';
                $weight = $parts[2] ?? '400';
            }

            if ('' !== $uni) {
                $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';

                if (function_exists('dtq_inject_fa_icons')) {
                    // Reconstruct the legacy string form so the FA detector
                    // (which expects "unicode||type||weight") works for both shapes.
                    dtq_inject_fa_icons($uni . '||' . $type . '||' . $weight);
                }

                // dtq_resolve_icon_unicode() escapes its return value; entities
                // such as "&#xe0XX;" survive intact so the glyph still renders.
                $inner = sprintf(
                    '<i class="dtq-et-icon" style="font-family:\'%1$s\';font-weight:%2$s">%3$s</i>',
                    esc_attr($font),
                    esc_attr($weight),
                    dtq_resolve_icon_unicode($uni)
                );
            }
        }

        return sprintf(
            '<div class="dtq-btn__connector dtq-btn__connector--%1$s">%2$s</div>',
            esc_attr($connector_type),
            $inner
        );
    }

    /**
     * Emit gap, alignment, and connector CSS as an inline <style> block.
     *
     * Every interpolated value is author-controlled, so each one is run through
     * dtq_css_length()/dtq_css_color() first. Without that, a value such as
     * `#fff}</style><script>…</script><style>{` would close the style element and
     * execute; even a benign `}` or `;` would corrupt the whole rule set. Values
     * that do not parse as a length/colour fall back to the module default rather
     * than being emitted. Global colours are resolved to `var(--…)` beforehand so
     * they survive the colour check.
     *
     * Button Gap and Button Alignment are responsive fields. The builder has
     * always emitted their tablet and phone values (styles.jsx), but this method
     * printed the desktop rules only, so a gap or alignment set for tablet or
     * phone showed while editing and vanished on the published page. The media
     * queries below match styles.jsx, and a breakpoint is emitted only when its
     * value differs from the next larger one.
     *
     * @param array  $advanced    The `module.advanced` attrs array.
     * @param string $selector    The module order-class selector (e.g. ".dtq_dual_button_0").
     *
     * @return string
     */
    public static function render_inline_styles($advanced, $selector)
    {
        $button_gap    = dtq_css_length(dtq_resolve_css_value($advanced['buttonGap']['desktop']['value'] ?? ''), '40px');
        $justify       = self::dtq_alignment_to_justify($advanced['btnAlignment']['desktop']['value'] ?? 'left');
        $connector_type = $advanced['connectorType']['desktop']['value'] ?? 'empty';

        $rules = [
            "$selector .btn-el--primary{margin-right:calc($button_gap / 2)}",
            "$selector .btn-el--secondary{margin-left:calc($button_gap / 2)}",
            "$selector .dtq-dual-btn{justify-content:$justify}",
        ];

        if ('empty' !== $connector_type) {
            $size         = dtq_css_length(dtq_resolve_css_value($advanced['connectorSize']['desktop']['value'] ?? ''), '30px');
            $bg           = dtq_css_color(dtq_resolve_css_value($advanced['connectorBg']['desktop']['value'] ?? ''), 'transparent');
            $text_color   = dtq_css_color(dtq_resolve_css_value($advanced['connectorTextColor']['desktop']['value'] ?? ''), '#333');
            $text_size    = dtq_css_length(dtq_resolve_css_value($advanced['connectorTextSize']['desktop']['value'] ?? ''), '14px');
            $radius       = dtq_css_length(dtq_resolve_css_value($advanced['connectorRadius']['desktop']['value'] ?? ''), '0px');
            $border_width = dtq_css_length(dtq_resolve_css_value($advanced['connectorBorderWidth']['desktop']['value'] ?? ''), '0px');
            $border_color = dtq_css_color(dtq_resolve_css_value($advanced['connectorBorderColor']['desktop']['value'] ?? ''), 'transparent');

            $rules[] = "$selector .dtq-btn__connector{"
                . "width:$size;height:$size;background:$bg;color:$text_color;"
                . "font-size:$text_size;border-radius:$radius;"
                . "box-shadow:0 0 0 $border_width $border_color}";
        }

        $breakpoints = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        foreach ($breakpoints as $breakpoint => $at_rule) {
            $responsive = [];

            // An unparseable gap is skipped rather than replaced with the
            // default: the breakpoint then inherits the larger one, the same as
            // an unset value.
            $gap = self::dtq_responsive_value($advanced, 'buttonGap', $breakpoint);
            $gap = null !== $gap ? dtq_css_length(dtq_resolve_css_value($gap), '') : '';
            if ('' !== $gap) {
                $responsive[] = "$selector .btn-el--primary{margin-right:calc($gap / 2)}";
                $responsive[] = "$selector .btn-el--secondary{margin-left:calc($gap / 2)}";
            }

            // Mapped to a fixed keyword, so the raw value never reaches the CSS.
            $alignment = self::dtq_responsive_value($advanced, 'btnAlignment', $breakpoint);
            if (null !== $alignment && '' !== $alignment) {
                $responsive[] = "$selector .dtq-dual-btn{justify-content:" . self::dtq_alignment_to_justify($alignment) . '}';
            }

            if ($responsive) {
                $rules[] = $at_rule . '{' . implode('', $responsive) . '}';
            }
        }

        return '<style>' . implode('', $rules) . '</style>';
    }

    /**
     * The module's order class, exactly as Divi prints it on the wrapper.
     *
     * The inline <style> is scoped to this class, so it has to be the one on the
     * element. A class hand-built from `orderIndex` misses the Theme Builder
     * suffix (`_tb_header`, `_tb_body`, `_tb_footer`): a Dual Button in a
     * Theme Builder template lost its gap and alignment, and its rules landed on
     * the Dual Button with the same index in the post content instead. The
     * hand-built form stays as the fallback for when Divi's helper is missing.
     *
     * @param \WP_Block $block Parsed block.
     *
     * @return string Class name without the leading dot, or '' when unknown.
     */
    protected static function order_class_name($block)
    {
        $id    = $block->parsed_block['id'] ?? null;
        $utils = '\ET\Builder\Packages\ModuleUtils\ModuleUtils';

        if ($id && class_exists($utils) && method_exists($utils, 'get_module_order_class_name')) {
            $name = $utils::get_module_order_class_name($id, $block->parsed_block['storeInstance'] ?? null);

            if (is_string($name) && '' !== $name) {
                return $name;
            }
        }

        $order_index = $block->parsed_block['orderIndex'] ?? null;

        return null !== $order_index ? sprintf('dtq_dual_button_%d', $order_index) : '';
    }

    /**
     * Server-side render for the DualButton module.
     *
     * Mirrors the D4 markup:
     *     <div class="dtq-module dtq-dual-btn">
     *       <div class="dtq-btn-wrap">[primary] [connector]</div>
     *       <div class="dtq-btn-wrap">[secondary]</div>
     *     </div>
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused).
     * @param \WP_Block      $block    Parsed block.
     * @param object         $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $btn_a_text   = DynamicValue::resolve($advanced['btnAText']['desktop']['value'] ?? 'Button 1');
        $btn_a_link   = DynamicValue::resolve($advanced['btnALink']['desktop']['value'] ?? '#');
        $btn_a_target = $advanced['btnALinkTarget']['desktop']['value'] ?? '_self';

        $btn_b_text   = DynamicValue::resolve($advanced['btnBText']['desktop']['value'] ?? 'Button 2');
        $btn_b_link   = DynamicValue::resolve($advanced['btnBLink']['desktop']['value'] ?? '#');
        $btn_b_target = $advanced['btnBLinkTarget']['desktop']['value'] ?? '_self';

        // Gap, alignment and connector CSS is printed as an inline <style>, every
        // value sanitized, scoped to the order class Divi puts on the wrapper.
        $order_class = self::order_class_name($block);
        $custom_css  = '';
        if (!empty($order_class)) {
            $custom_css = self::render_inline_styles($advanced, '.' . $order_class);
        }

        $children = $custom_css . sprintf(
            '<div class="dtq-module dtq-dual-btn">' .
                '<div class="dtq-btn-wrap">%1$s%2$s</div>' .
                '<div class="dtq-btn-wrap">%3$s</div>' .
            '</div>',
            self::render_button($btn_a_text, $btn_a_link, $btn_a_target, 'primary', $attrs['btnA']['decoration']['button'] ?? []),
            self::render_connector($advanced),
            self::render_button($btn_b_text, $btn_b_link, $btn_b_target, 'secondary', $attrs['btnB']['decoration']['button'] ?? [])
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
                    // Background pattern/mask and the box-shadow overlay are DOM,
                    // not CSS. Without this call Divi renders them in the builder
                    // and nowhere else, so the options silently do nothing on the
                    // front end.
                    $elements->style_components(['attrName' => 'module']),
                    $children,
                ],
            ]
        );
    }
}
