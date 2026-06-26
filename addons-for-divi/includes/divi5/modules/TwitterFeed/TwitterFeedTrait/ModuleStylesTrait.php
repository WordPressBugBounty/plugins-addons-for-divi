<?php
/**
 * Twitter Feed: module styles trait. Ports the D4 layout/render_css.
 *
 * @package DiviTorqueLite\Modules\TwitterFeed
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\TwitterFeed\TwitterFeedTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    protected static function tf_val($a, $key, $fallback)
    {
        $v = $a[$key]['desktop']['value'] ?? null;
        return (null === $v || '' === $v) ? $fallback : $v;
    }

    protected static function tf_unit($value, $unit)
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

    protected static function tf_spacing($val)
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

    protected static function tf_bp($a, $key, $b)
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

    protected static function tf_bp_raw($a, $key, $b)
    {
        $v = $a[$key][$b]['value'] ?? null;
        return '' === $v ? null : $v;
    }

    public static function build_twitter_styles($oc, $a)
    {
        $styles = [];
        $a      = is_array($a) ? $a : [];
        $media  = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];
        $add = function ($sel, $decl, $at = false) use (&$styles) {
            if (!empty($decl)) {
                $styles[] = ['atRules' => $at, 'selector' => $sel, 'declaration' => $decl];
            }
        };

        $grid    = $oc . ' .dtq-twitter-grid';
        $cols    = self::tf_val($a, 'columnCount', '3');
        $gap_x   = self::tf_unit(self::tf_val($a, 'columnGapX', '20px'), 'px');
        $gap_y   = self::tf_unit(self::tf_val($a, 'columnGapY', '20px'), 'px');
        $add($grid, sprintf('display: grid; grid-template-columns: repeat(%1$s, 1fr); column-gap: %2$s; row-gap: %3$s;', $cols, $gap_x, $gap_y));
        foreach (['tablet', 'phone'] as $b) {
            $c  = self::tf_bp($a, 'columnCount', $b);
            $gx = self::tf_unit(self::tf_bp($a, 'columnGapX', $b), 'px');
            $gy = self::tf_unit(self::tf_bp($a, 'columnGapY', $b), 'px');
            if (null !== self::tf_bp_raw($a, 'columnCount', $b) || null !== self::tf_bp_raw($a, 'columnGapX', $b) || null !== self::tf_bp_raw($a, 'columnGapY', $b)) {
                $add($grid, sprintf('grid-template-columns: repeat(%1$s, 1fr); column-gap: %2$s; row-gap: %3$s;', $c, $gx, $gy), $media[$b]);
            }
        }

        // Twitter icon + avatar size.
        $add($oc . ' .dtq-twitter-grid-icon span', sprintf('width: %1$s; height: %1$s;', self::tf_unit(self::tf_val($a, 'twitterIconSize', '20px'), 'px')));
        $add($oc . ' .dtq-twitter-grid-avatar', sprintf('width: %1$s; height: %1$s;', self::tf_unit(self::tf_val($a, 'avatarSize', '50px'), 'px')));

        // Avatar position.
        if ('absolute' === self::tf_val($a, 'avatarPosition', 'normal')) {
            $placement = self::tf_val($a, 'avatarPlacement', 'left_top');
            $ox = self::tf_unit(self::tf_val($a, 'avatarOffsetX', '50%'), 'px');
            $oy = self::tf_unit(self::tf_val($a, 'avatarOffsetY', '0px'), 'px');
            $add($oc . ' .dtq-twitter-grid-item-inner', 'position: relative;');
            $decl = 'position: absolute; z-index: 2;';
            $decl .= (false !== strpos($placement, 'top')) ? sprintf(' top: %s;', $oy) : sprintf(' bottom: %s;', $oy);
            $decl .= (false !== strpos($placement, 'left')) ? sprintf(' left: %s;', $ox) : sprintf(' right: %s;', $ox);
            $add($oc . ' .dtq-twitter-grid-avatar-wrapper', $decl);
        } else {
            $add($oc . ' .dtq-twitter-grid-avatar-wrapper', sprintf('margin-right: %s;', self::tf_unit(self::tf_val($a, 'avatarSpacing', '15px'), 'px')));
        }

        // Alignment.
        $add($oc . ' .dtq-twitter-grid-item-inner', sprintf('text-align: %s;', self::tf_val($a, 'alignment', 'left')));

        // Content padding + description spacing.
        $cp = self::tf_spacing(self::tf_val($a, 'contentPadding', '50px|50px|50px|50px'));
        if ('' !== $cp) {
            $add($oc . ' .dtq-twitter-grid-content', sprintf('padding: %s;', $cp));
        }
        $add($oc . ' .dtq-twitter-grid-content p', sprintf('margin-bottom: %s;', self::tf_unit(self::tf_val($a, 'descriptionSpacing', '25px'), 'px')));

        // Footer.
        $add($oc . ' .dtq-twitter-grid-footer', sprintf('display: flex; justify-content: %s;', self::tf_val($a, 'footerAlignment', 'space-between')));
        $fp = self::tf_spacing(self::tf_val($a, 'footerPadding', '0px|50px|50px|50px'));
        if ('' !== $fp) {
            $add($oc . ' .dtq-twitter-grid-footer-wrapper', sprintf('padding: %s;', $fp));
        }

        // Favorite / retweet.
        $add($oc . ' .dtq-tweet-favorite', sprintf('color: %1$s !important; font-size: %2$s !important;', self::tf_val($a, 'favoriteColor', '#000000'), self::tf_unit(self::tf_val($a, 'favoriteFontSize', '14px'), 'px')));
        $add($oc . ' .dtq-tweet-favorite span', sprintf('color: %1$s !important; font-size: %2$s !important;', self::tf_val($a, 'favoriteIconColor', '#000000'), self::tf_unit(self::tf_val($a, 'favoriteIconSize', '14px'), 'px')));
        $add($oc . ' .dtq-tweet-retweet', sprintf('color: %1$s !important; font-size: %2$s !important;', self::tf_val($a, 'retweetColor', '#000000'), self::tf_unit(self::tf_val($a, 'retweetFontSize', '14px'), 'px')));
        $add($oc . ' .dtq-tweet-retweet span', sprintf('color: %1$s !important; font-size: %2$s !important;', self::tf_val($a, 'retweetIconColor', '#000000'), self::tf_unit(self::tf_val($a, 'retweetIconSize', '14px'), 'px')));

        return $styles;
    }

    public static function module_styles($args)
    {
        $attrs       = $args['attrs'] ?? [];
        $elements    = $args['elements'];
        $settings    = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';

        $advanced      = $attrs['module']['advanced'] ?? [];
        $custom_styles = self::build_twitter_styles($order_class, $advanced);

        $all_styles = [
            $elements->style(['attrName' => 'module', 'styleProps' => ['disabledOn' => ['disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null]]]),
            $elements->style(['attrName' => 'tweetsItem']),
            $elements->style(['attrName' => 'avatar']),
            $elements->style(['attrName' => 'nameFont']),
            $elements->style(['attrName' => 'usernameFont']),
            $elements->style(['attrName' => 'descriptionFont']),
            $elements->style(['attrName' => 'readmoreFont']),
            $elements->style(['attrName' => 'dateFont']),
        ];

        if (!empty($custom_styles)) {
            $all_styles[] = $custom_styles;
        }

        $all_styles[] = CssStyle::style(['selector' => $order_class, 'attr' => $attrs['css'] ?? [], 'cssFields' => self::custom_css_fields()]);

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
