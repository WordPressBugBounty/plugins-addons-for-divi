<?php
/**
 * Table of Contents: heading index.
 *
 * @package DiviTorqueLite\Modules\TableOfContents
 * @since   4.15.1
 */

namespace DiviTorqueLite\Modules\TableOfContents;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gives every heading in rendered HTML an anchor id and builds the Table of
 * Contents list from those same headings, in one pass.
 *
 * WHY ONE PASS
 *
 * The server-rendered (no-JS) list and the heading ids used to come from two
 * separate scans. The list rendered the whole post a second time inside the
 * module's own render (do_blocks() + do_shortcode(), re-running every other
 * module's render callback), and a `the_content` filter added the ids. The
 * two disagreed whenever their inputs did: a Theme Builder placement got a
 * list but no ids, startLevel h1 was never scanned, and the JS kept the
 * server's hrefs, so those links pointed nowhere.
 *
 * Now the module renders an empty slot (an HTML comment carrying its level
 * range and minimum) and fill() runs on the finished HTML: on `the_content`
 * for a TOC in post content, on `et_builder_render_layout` for one in a
 * Theme Builder body layout. It assigns the ids and renders every slot's
 * list from the same heading array, so a link can only point at an id this
 * pass wrote. Nothing is rendered twice and nothing here can recurse.
 *
 * The cost is the scope: only headings in the same HTML string as the slot
 * are listed server-side. A TOC in a header or footer layout, or one with
 * too few headings around it, keeps an empty list and the front-end script
 * builds it from the page instead, as it always did when there was no server
 * list.
 *
 * SLUG RULES (mirrored by tocSlugify() in src/divi5/frontend.js)
 *
 * Text is the heading's text content with entities decoded. Lowercase
 * (Unicode), trim, drop everything but letters, combining marks, digits,
 * whitespace, "_" and "-", turn runs of whitespace and "_" into "-",
 * collapse and trim "-". Empty becomes "section". A heading that already has
 * an id keeps it, and every id already in the HTML is reserved, so a
 * generated slug never duplicates one; repeats get "-2", "-3"... in document
 * order. All levels h1-h6 are walked, whatever the TOC's own range, so a
 * heading's anchor does not depend on the TOC settings.
 */
class HeadingIndex
{
    const HEADING_PATTERN = '/<h([1-6])\b([^>]*)>(.*?)<\/h\1\s*>/is';

    const ID_ATTR_PATTERN = '/(^|\s)id\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))/i';

    const SLOT_PREFIX = '<!--dtq-toc-list:';

    const SLOT_PATTERN = '/<!--dtq-toc-list:([1-6]):([1-6]):(\d+)-->/';

    /**
     * The placeholder a TOC renders where its list goes.
     *
     * @param int $start Shallowest heading level listed.
     * @param int $end   Deepest heading level listed.
     * @param int $min   Fewest headings worth a list.
     *
     * @return string
     */
    public static function slot($start, $end, $min)
    {
        return sprintf('%1$s%2$d:%3$d:%4$d-->', self::SLOT_PREFIX, (int) $start, (int) $end, max(1, (int) $min));
    }

    /**
     * Does this HTML hold a TOC slot?
     *
     * @param mixed $html Content.
     *
     * @return bool
     */
    public static function has_slot($html)
    {
        return is_string($html) && false !== strpos($html, self::SLOT_PREFIX);
    }

    /**
     * Index the headings and fill every TOC slot in the HTML.
     *
     * @param string $html Rendered content.
     *
     * @return string
     */
    public static function fill($html)
    {
        list($html, $items) = self::index($html);

        if (!self::has_slot($html)) {
            return $html;
        }

        $filled = preg_replace_callback(
            self::SLOT_PATTERN,
            function ($m) use ($items) {
                $start = min((int) $m[1], (int) $m[2]);
                $end   = max((int) $m[1], (int) $m[2]);
                return self::render_list($items, $start, $end, (int) $m[3]);
            },
            $html
        );

        return null === $filled ? $html : $filled;
    }

    /**
     * Give every non-empty heading an id and list them.
     *
     * @param string $html Rendered content.
     *
     * @return array{0: string, 1: array} The HTML with ids added, and the
     *                                    headings as [level, text, id] in order.
     */
    public static function index($html)
    {
        $html = (string) $html;
        if (false === stripos($html, '<h')) {
            return [$html, []];
        }

        $used  = self::reserved_ids($html);
        $items = [];

        $out = preg_replace_callback(
            self::HEADING_PATTERN,
            function ($m) use (&$used, &$items) {
                $text = self::heading_text($m[3]);
                if ('' === $text) {
                    return $m[0];
                }

                $level = (int) $m[1];
                $id    = self::id_attr($m[2]);
                $tag   = $m[0];

                if ('' === $id) {
                    $id  = self::slugify($text, $used);
                    $tag = sprintf(
                        '<h%1$d id="%2$s"%3$s>%4$s</h%1$d>',
                        $level,
                        esc_attr($id),
                        self::without_id_attr($m[2]),
                        $m[3]
                    );
                }

                $items[] = ['level' => $level, 'text' => $text, 'id' => $id];

                return $tag;
            },
            $html
        );

        if (null === $out) {
            // PCRE gave up (backtrack limit). Leave the content alone rather
            // than return ids for headings that did not get them.
            return [$html, []];
        }

        return [$out, $items];
    }

    /**
     * Slugify heading text into an id no other element in the HTML uses.
     *
     * @param string $text Heading text, entities decoded.
     * @param array  $used Ids taken so far; the new one is added.
     *
     * @return string
     */
    public static function slugify($text, array &$used)
    {
        $slug = function_exists('mb_strtolower') ? mb_strtolower((string) $text, 'UTF-8') : strtolower((string) $text);
        $slug = (string) preg_replace('/[^\p{L}\p{M}\p{N}\s_-]/u', '', $slug);
        $slug = (string) preg_replace('/[\s_]+/u', '-', $slug);
        $slug = (string) preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        if ('' === $slug) {
            $slug = 'section';
        }

        $base = $slug;
        $n    = 2;
        while (isset($used[$slug])) {
            $slug = $base . '-' . $n;
            $n++;
        }
        $used[$slug] = true;

        return $slug;
    }

    /**
     * A heading's text, as the browser's textContent gives it.
     *
     * @param string $inner Heading inner HTML.
     *
     * @return string
     */
    public static function heading_text($inner)
    {
        $text = html_entity_decode(wp_strip_all_tags((string) $inner), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Unicode-aware trim, like String.prototype.trim(): a heading that
        // holds only &nbsp; is empty on both sides.
        return (string) preg_replace('/^\s+|\s+$/u', '', $text);
    }

    /**
     * The id a heading already has, or '' when it has none (or an empty one).
     *
     * @param string $attrs The heading's attribute string.
     *
     * @return string
     */
    private static function id_attr($attrs)
    {
        if (!preg_match(self::ID_ATTR_PATTERN, (string) $attrs, $m)) {
            return '';
        }

        $raw = ($m[2] ?? '') . ($m[3] ?? '') . ($m[4] ?? '');

        return trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Drop an empty id attribute before ours is added, so the tag never ends
     * up with two.
     *
     * @param string $attrs The heading's attribute string.
     *
     * @return string
     */
    private static function without_id_attr($attrs)
    {
        return (string) preg_replace(self::ID_ATTR_PATTERN, '', (string) $attrs, 1);
    }

    /**
     * Every id already in the HTML, so a generated slug avoids them all.
     *
     * @param string $html Rendered content.
     *
     * @return array<string, true>
     */
    private static function reserved_ids($html)
    {
        $used = [];
        if (preg_match_all(self::ID_ATTR_PATTERN, $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $id = trim(html_entity_decode(($m[2] ?? '') . ($m[3] ?? '') . ($m[4] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ('' !== $id) {
                    $used[$id] = true;
                }
            }
        }

        return $used;
    }

    /**
     * The nested <li> markup for one TOC.
     *
     * @param array $items Indexed headings.
     * @param int   $start Shallowest level listed.
     * @param int   $end   Deepest level listed.
     * @param int   $min   Fewest headings worth a list.
     *
     * @return string '' when there are too few, so the front end decides.
     */
    public static function render_list(array $items, $start, $end, $min)
    {
        $items = array_values(
            array_filter(
                $items,
                function ($item) use ($start, $end) {
                    return $item['level'] >= $start && $item['level'] <= $end;
                }
            )
        );

        if (count($items) < max(1, (int) $min)) {
            return '';
        }

        $i = 0;
        return self::render_items($items, $i, min(array_column($items, 'level')));
    }

    /**
     * Recursively render the <li> items at a given heading level. Always emits
     * balanced <ul>/<li> markup regardless of the heading hierarchy (skipped
     * or out-of-order levels). $i is advanced by reference as items are
     * consumed.
     *
     * @param array $items Headings in range.
     * @param int   $i     Cursor.
     * @param int   $level Level rendered by this call.
     *
     * @return string
     */
    private static function render_items(array $items, &$i, $level)
    {
        $html  = '';
        $count = count($items);

        while ($i < $count) {
            $cur = (int) $items[$i]['level'];

            // Belongs to a shallower level: hand control back to the caller.
            if ($cur < $level) {
                break;
            }

            $item = $items[$i];
            $i++;

            $link = sprintf(
                '<a href="#%1$s">%2$s</a>',
                esc_attr($item['id']),
                esc_html($item['text'])
            );

            // Nest any deeper following items inside this <li>.
            $children = '';
            if ($i < $count && (int) $items[$i]['level'] > $cur) {
                $children = '<ul>' . self::render_items($items, $i, (int) $items[$i]['level']) . '</ul>';
            }

            $html .= '<li class="dtq-toc-item">' . $link . $children . '</li>';
        }

        return $html;
    }
}
