<?php
/**
 * Twitter Carousel: module styles trait. Combines the shared CarouselEngine
 * (nav/pagination/cursor) styles with the per-tweet styling.
 *
 * @package DiviTorqueLite\Modules\TwitterFeedCarousel
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\TwitterFeedCarousel\TwitterFeedCarouselTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use DiviTorqueLite\Modules\SharedCarousel\CarouselEngine;

trait ModuleStylesTrait
{
    protected static function tc_val($a, $key, $fallback)
    {
        $v = $a[$key]['desktop']['value'] ?? null;
        return (null === $v || '' === $v) ? $fallback : $v;
    }

    protected static function tc_unit($value, $unit)
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

    protected static function tc_spacing($val)
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

    /**
     * Per-tweet styling (icon/avatar/alignment/content/footer/favorite/retweet),
     * shared with the Twitter Feed grid minus the grid-column layout (Swiper
     * handles slide layout here).
     */
    protected static function build_tweet_styles($oc, $a)
    {
        $styles = [];
        $a      = is_array($a) ? $a : [];
        $add    = function ($sel, $decl) use (&$styles) {
            if (!empty($decl)) {
                $styles[] = ['atRules' => false, 'selector' => $sel, 'declaration' => $decl];
            }
        };

        $add($oc . ' .dtq-twitter-grid-icon span', sprintf('width: %1$s; height: %1$s;', self::tc_unit(self::tc_val($a, 'twitterIconSize', '20px'), 'px')));
        $add($oc . ' .dtq-twitter-grid-avatar', sprintf('width: %1$s; height: %1$s;', self::tc_unit(self::tc_val($a, 'avatarSize', '50px'), 'px')));

        if ('absolute' === self::tc_val($a, 'avatarPosition', 'normal')) {
            $placement = self::tc_val($a, 'avatarPlacement', 'left_top');
            $ox = self::tc_unit(self::tc_val($a, 'avatarOffsetX', '50%'), 'px');
            $oy = self::tc_unit(self::tc_val($a, 'avatarOffsetY', '0px'), 'px');
            $add($oc . ' .dtq-twitter-grid-item-inner', 'position: relative;');
            $decl = 'position: absolute; z-index: 2;';
            $decl .= (false !== strpos($placement, 'top')) ? sprintf(' top: %s;', $oy) : sprintf(' bottom: %s;', $oy);
            $decl .= (false !== strpos($placement, 'left')) ? sprintf(' left: %s;', $ox) : sprintf(' right: %s;', $ox);
            $add($oc . ' .dtq-twitter-grid-avatar-wrapper', $decl);
        } else {
            $add($oc . ' .dtq-twitter-grid-avatar-wrapper', sprintf('margin-right: %s;', self::tc_unit(self::tc_val($a, 'avatarSpacing', '15px'), 'px')));
        }

        $add($oc . ' .dtq-twitter-grid-item-inner', sprintf('text-align: %s;', self::tc_val($a, 'alignment', 'left')));

        $cp = self::tc_spacing(self::tc_val($a, 'contentPadding', '50px|50px|50px|50px'));
        if ('' !== $cp) {
            $add($oc . ' .dtq-twitter-grid-content', sprintf('padding: %s;', $cp));
        }
        $add($oc . ' .dtq-twitter-grid-content p', sprintf('margin-bottom: %s;', self::tc_unit(self::tc_val($a, 'descriptionSpacing', '25px'), 'px')));

        $add($oc . ' .dtq-twitter-grid-footer', sprintf('display: flex; justify-content: %s;', self::tc_val($a, 'footerAlignment', 'space-between')));
        $fp = self::tc_spacing(self::tc_val($a, 'footerPadding', '0px|50px|50px|50px'));
        if ('' !== $fp) {
            $add($oc . ' .dtq-twitter-grid-footer-wrapper', sprintf('padding: %s;', $fp));
        }

        $add($oc . ' .dtq-tweet-favorite', sprintf('color: %1$s !important; font-size: %2$s !important;', self::tc_val($a, 'favoriteColor', '#000000'), self::tc_unit(self::tc_val($a, 'favoriteFontSize', '14px'), 'px')));
        $add($oc . ' .dtq-tweet-favorite span', sprintf('color: %1$s !important; font-size: %2$s !important;', self::tc_val($a, 'favoriteIconColor', '#000000'), self::tc_unit(self::tc_val($a, 'favoriteIconSize', '14px'), 'px')));
        $add($oc . ' .dtq-tweet-retweet', sprintf('color: %1$s !important; font-size: %2$s !important;', self::tc_val($a, 'retweetColor', '#000000'), self::tc_unit(self::tc_val($a, 'retweetFontSize', '14px'), 'px')));
        $add($oc . ' .dtq-tweet-retweet span', sprintf('color: %1$s !important; font-size: %2$s !important;', self::tc_val($a, 'retweetIconColor', '#000000'), self::tc_unit(self::tc_val($a, 'retweetIconSize', '14px'), 'px')));

        return $styles;
    }

    public static function module_styles($args)
    {
        $attrs       = $args['attrs'] ?? [];
        $elements    = $args['elements'];
        $settings    = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';

        $advanced = $attrs['module']['advanced'] ?? [];

        $carousel_styles = CarouselEngine::build_carousel_styles($order_class, $advanced);
        $tweet_styles    = self::build_tweet_styles($order_class, $advanced);
        $custom_styles   = array_merge(is_array($carousel_styles) ? $carousel_styles : [], $tweet_styles);

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
