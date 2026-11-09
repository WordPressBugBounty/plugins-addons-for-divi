<?php
/**
 * Tell an empty text attribute from one a visitor can see.
 *
 * Several modules print a title or description wrapper around a text
 * attribute. When the attribute was empty the wrapper still printed: a
 * zero-height element that kept its margin and padding, so a module gained
 * space nobody could see or trace in the builder (#112). Divi's own
 * $elements->render() already skips an empty string, but not a value that is
 * only markup or whitespace, such as "<p></p>" or "<p>&nbsp;</p>", which is
 * what a cleared rich text field can leave behind.
 *
 * JS twin: src/divi5/shared/text-value.js. The builder and the published page
 * must agree on what counts as empty, so change both together.
 *
 * @package DiviTorqueLite\Modules\Shared
 * @since   4.15.1
 */

namespace DiviTorqueLite\Modules\Shared;

if (!defined('ABSPATH')) {
    exit;
}

class TextValue
{
    /**
     * Elements that show something with no text inside them.
     */
    const VISIBLE_TAGS = 'img|svg|picture|video|audio|iframe|object|embed|canvas|input|select|textarea|button|hr';

    /**
     * Whether one attribute value shows anything.
     *
     * A dynamic-content value (an array, or the legacy `$variable(...)$`
     * token) counts as visible: what it resolves to is not known here.
     *
     * @param mixed $value Raw attribute value.
     *
     * @return bool
     */
    public static function visible($value)
    {
        if (is_array($value)) {
            return !empty($value);
        }

        if (!is_scalar($value)) {
            return false;
        }

        $value = (string) $value;

        if (false !== strpos($value, '$variable(')) {
            return true;
        }

        if (preg_match('/<(?:' . self::VISIBLE_TAGS . ')\b/i', $value)) {
            return true;
        }

        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\s\x{00A0}\x{2000}-\x{200B}\x{FEFF}]+/u', '', $text);

        return '' !== (string) $text;
    }

    /**
     * Whether a responsive attribute shows anything at any breakpoint or state.
     *
     * Matches how Divi decides to render an element: a value set only for
     * tablet still renders on tablet, so it still needs its wrapper.
     *
     * @param mixed $attr Responsive attribute, e.g. `['desktop' => ['value' => '…']]`.
     *
     * @return bool
     */
    public static function any_visible($attr)
    {
        if (!is_array($attr)) {
            return self::visible($attr);
        }

        foreach ($attr as $states) {
            if (!is_array($states)) {
                continue;
            }
            foreach ($states as $value) {
                if (self::visible($value)) {
                    return true;
                }
            }
        }

        return false;
    }
}
