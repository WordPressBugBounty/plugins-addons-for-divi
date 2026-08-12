<?php
/**
 * Tooltip: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\Tooltip
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\Tooltip\TooltipTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Placements tippy understands.
     *
     * The chosen value is emitted as configuration, so it is matched against
     * this list rather than passed through.
     */
    const PLACEMENTS = [
        'top', 'top-start', 'top-end',
        'right', 'right-start', 'right-end',
        'bottom', 'bottom-start', 'bottom-end',
        'left', 'left-start', 'left-end',
    ];

    /**
     * Only two animations ship. tippy's UMD injects its own core CSS, which
     * covers `fade`, and assets/libs/tippy/tippy.min.css is 394 bytes holding
     * exactly one animation — `scale`.
     */
    const ANIMATIONS = ['fade', 'scale'];

    const TRIGGER_TYPES = ['text', 'icon', 'image'];

    const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Turn a millisecond-ish attribute value into a number.
     *
     * @param mixed $value    Raw attribute value, e.g. '100ms'.
     * @param int   $fallback Used when the value is unusable.
     *
     * @return int
     */
    private static function to_ms($value, $fallback)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        $string = trim((string) $value);
        if ('' === $string || !preg_match('/^-?[\d.]+/', $string, $match)) {
            return $fallback;
        }

        $number = (float) $match[0];

        // "2s" means 2000ms; "200ms" means 200.
        if (preg_match('/[^m]s\s*$/', $string) || preg_match('/^[\d.]+s$/', $string)) {
            $number *= 1000;
        }

        return (int) round($number);
    }

    /**
     * Build the tippy options.
     *
     * PHP twin of buildTippyConfig() in
     * src/divi5/modules/tooltip/tippy-config.js. Emitted as JSON on
     * data-dtq-tip; src/divi5/modules/tooltip/frontend.js reads it back.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return array
     */
    public static function tippy_config($advanced)
    {
        $placement = $advanced['placement']['desktop']['value'] ?? 'top';
        $animation = $advanced['animation']['desktop']['value'] ?? 'fade';

        // Focus is always included, never hover alone: a tooltip a keyboard
        // user cannot open is a tooltip they cannot read.
        $trigger = ('click' === ($advanced['trigger']['desktop']['value'] ?? 'hover'))
            ? 'click'
            : 'mouseenter focus';

        return [
            'placement'   => in_array($placement, self::PLACEMENTS, true) ? $placement : 'top',
            'animation'   => in_array($animation, self::ANIMATIONS, true) ? $animation : 'fade',
            'trigger'     => $trigger,
            'arrow'       => 'off' !== ($advanced['arrow']['desktop']['value'] ?? 'on'),
            'interactive' => 'off' !== ($advanced['interactive']['desktop']['value'] ?? 'on'),
            // Gives the pointer somewhere to cross on the way to the tooltip;
            // without it an interactive tooltip closes before it is reached.
            'interactiveBorder' => 8,
            'delay'       => [
                self::to_ms($advanced['delayIn']['desktop']['value'] ?? '100ms', 100),
                self::to_ms($advanced['delayOut']['desktop']['value'] ?? '0ms', 0),
            ],
            'offset'      => [0, self::to_ms($advanced['offsetDistance']['desktop']['value'] ?? '10px', 10)],
            'maxWidth'    => self::to_ms($advanced['maxWidth']['desktop']['value'] ?? '300px', 300),
        ];
    }

    /**
     * Build the wrapper class list.
     *
     * PHP twin of src/divi5/modules/tooltip/wrapper-class.js.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return string
     */
    public static function wrapper_class($advanced)
    {
        $trigger_type = $advanced['triggerType']['desktop']['value'] ?? 'text';
        $alignment    = $advanced['alignment']['desktop']['value'] ?? 'center';

        if (!in_array($trigger_type, self::TRIGGER_TYPES, true)) {
            $trigger_type = 'text';
        }
        if (!in_array($alignment, self::ALIGNMENTS, true)) {
            $alignment = 'center';
        }

        return implode(' ', [
            'dtq-module',
            'dtq-tipbox',
            // Marks this as Divi 5 markup so the site-wide Divi 4 frontend.js
            // leaves it alone.
            'dtq-d5',
            'dtq-tipbox--trigger-' . $trigger_type,
            'dtq-tipbox--align-' . $alignment,
        ]);
    }

    /**
     * Server-side render for the Tooltip module.
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
        $advanced     = $attrs['module']['advanced'] ?? [];
        $trigger_type = $advanced['triggerType']['desktop']['value'] ?? 'text';

        if (!in_array($trigger_type, self::TRIGGER_TYPES, true)) {
            $trigger_type = 'text';
        }

        // The libraries load only on pages that actually use a tooltip. Handles
        // are registered in includes/assets.php; popper must come before tippy,
        // whose UMD build reads window.Popper at parse time.
        wp_enqueue_script('divi-torque-lite-popper');
        wp_enqueue_script('divi-torque-lite-tippy');
        wp_enqueue_style('divi-torque-lite-tippy');

        // Trigger contents.
        $trigger_inner = '';
        if ('icon' === $trigger_type) {
            // Divi 5 stores an icon as an object; Divi 4 stored it as
            // "unicode||type||weight". Normalise to the string form, because
            // that is what dtq_inject_fa_icons and Divi's own
            // et_pb_maybe_fa_font_icon expect — handing them the array is an
            // uncaught TypeError inside Divi's icon manager, which takes the
            // whole page down.
            $icon_raw = $advanced['triggerIcon']['desktop']['value'] ?? '';

            if (is_array($icon_raw)) {
                $unicode = $icon_raw['unicode'] ?? '';
                $type    = $icon_raw['type'] ?? 'divi';
                $weight  = $icon_raw['weight'] ?? '400';
            } else {
                $parts   = explode('||', (string) $icon_raw);
                $unicode = $parts[0] ?? '';
                $type    = $parts[1] ?? 'divi';
                $weight  = $parts[2] ?? '400';
            }

            $glyph = dtq_resolve_icon_unicode($unicode);

            if ('' !== $glyph) {
                if (function_exists('dtq_inject_fa_icons')) {
                    dtq_inject_fa_icons($unicode . '||' . $type . '||' . $weight);
                }
                $trigger_inner = sprintf(
                    '<i class="dtq-tipbox__icon" style="font-family:%1$s;font-weight:%2$s">%3$s</i>',
                    'fa' === $type ? 'FontAwesome' : 'ETmodules',
                    esc_attr($weight),
                    $glyph
                );
            }
        } elseif ('image' === $trigger_type) {
            $image = $advanced['triggerImage']['desktop']['value'] ?? '';
            $src   = is_array($image) ? ($image['src'] ?? '') : $image;
            if ('' !== $src) {
                $trigger_inner = sprintf(
                    '<img class="dtq-tipbox__image" src="%1$s" alt="%2$s" loading="lazy" decoding="async" />',
                    esc_url($src),
                    esc_attr($advanced['triggerImageAlt']['desktop']['value'] ?? '')
                );
            }
        } else {
            $trigger_inner = $elements->render(['attrName' => 'triggerText']);
        }

        $config = wp_json_encode(self::tippy_config($advanced));

        $children = sprintf(
            '<div class="%1$s">'
                . '<span class="dtq-tipbox__trigger" tabindex="0" data-dtq-tip="%2$s">%3$s</span>'
                . '<div class="dtq-tipbox__source" hidden>%4$s</div>'
                . '</div>',
            esc_attr(self::wrapper_class($advanced)),
            esc_attr($config),
            $trigger_inner,
            $elements->render(['attrName' => 'tipContent'])
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
