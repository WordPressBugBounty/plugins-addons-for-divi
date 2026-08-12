<?php
/**
 * Shared button-element renderer.
 *
 * @package DiviTorqueLite\Modules\Shared
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\Shared;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Layout\Components\HTMLUtility\HTMLUtility;

/**
 * Renders a Divi button element as markup.
 *
 * WHY THIS EXISTS
 *
 * On Divi 5.9.0, $elements->render(['attrName' => 'button']) returns an empty
 * string for a button declared as a nested element. It is not our metadata:
 * Divi's own pricing-tables-item renders its button the same way and comes out
 * empty too. Divi's standalone divi/button module is unaffected only because it
 * never takes that path — ButtonModule::render_callback builds its children
 * from the attribute directly rather than calling render().
 *
 * Dual Button has always built its anchors by hand and was, before this, the
 * only module in the plugin whose buttons appeared on the front end at all.
 *
 * The affected attributes keep elementType "button" and their divi/button
 * decoration groups, so the Design tab still styles these anchors through the
 * selectors declared in module.json. Only the markup is ours.
 *
 * A static class rather than a trait, following SharedCarousel\CarouselEngine:
 * modules already `use` five traits, and a shared trait sharing a method name
 * with one of them is a fatal.
 *
 * src/divi5/shared/button-element.js is the JavaScript twin, used by the edit
 * components so the builder preview and the published page agree.
 */
class ButtonElement
{
    /**
     * Render one button anchor.
     *
     * @param array  $button_attr The button element's attrs (e.g. $attrs['button']).
     * @param string $classes     Classes for the anchor, on top of et_pb_button.
     * @param array  $args        Optional. 'children' replaces the label markup,
     *                            for a module that wraps its text in a span.
     *
     * @return string Empty string when there is no label to show.
     */
    public static function render($button_attr, $classes = '', $args = [])
    {
        $value = $button_attr['innerContent']['desktop']['value'] ?? [];

        if (!is_array($value)) {
            return '';
        }

        $text = (string) ($value['text'] ?? '');

        // An empty label is how an author turns a button off.
        if ('' === trim($text)) {
            return '';
        }

        // Divi reads the href from linkUrl, not url.
        $url = $value['linkUrl'] ?? '';
        if (class_exists(HTMLUtility::class) && method_exists(HTMLUtility::class, 'resolve_url_shortcodes')) {
            $url = HTMLUtility::resolve_url_shortcodes($url);
        }

        // linkTarget is stored as on/off, where "on" means a new window.
        $target = ('on' === ($value['linkTarget'] ?? 'off')) ? '_blank' : '_self';

        $rel_raw = $value['rel'] ?? '';
        $rel     = is_array($rel_raw) ? implode(' ', array_filter($rel_raw)) : (string) $rel_raw;

        // A new window without noopener hands the opened page a live reference
        // back to this one through window.opener. Divi's own path does not do
        // this; ours should.
        if ('_blank' === $target && false === strpos($rel, 'noopener')) {
            $rel = trim($rel . ' noopener');
        }

        $label = $args['children'] ?? et_core_esc_previously($text);

        return sprintf(
            '<a class="%1$s" href="%2$s" target="%3$s"%4$s>%5$s</a>',
            esc_attr(trim('et_pb_button ' . $classes)),
            esc_url($url),
            esc_attr($target),
            '' !== $rel ? sprintf(' rel="%s"', esc_attr($rel)) : '',
            // The label may carry dynamic-content markup, which Divi has
            // already escaped.
            $label
        );
    }

    /**
     * Whether a button has a label to show.
     *
     * For callers that need to decide on a wrapper before rendering.
     *
     * @param array $button_attr The button element's attrs.
     *
     * @return bool
     */
    public static function has_label($button_attr)
    {
        $value = $button_attr['innerContent']['desktop']['value'] ?? [];

        return is_array($value) && '' !== trim((string) ($value['text'] ?? ''));
    }
}
