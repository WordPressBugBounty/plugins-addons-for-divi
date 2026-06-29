<?php
/**
 * PostCarousel — Server-side styles.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\PostCarousel\PostCarouselTrait;

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use DiviTorqueLite\Modules\SharedCarousel\CarouselEngine;

trait ModuleStylesTrait {

	private static function get_responsive_value( $attr, $device, $default ) {
		if ( ! is_array( $attr ) ) {
			return $default;
		}
		if ( isset( $attr[ $device ]['value'] ) && '' !== $attr[ $device ]['value'] ) {
			return $attr[ $device ]['value'];
		}
		if ( 'desktop' !== $device && isset( $attr['desktop']['value'] ) && '' !== $attr['desktop']['value'] ) {
			return $attr['desktop']['value'];
		}
		return $default;
	}

	/**
	 * Append a CSS unit when the value is a bare number. Divi's range field
	 * stores "10" rather than "10px" even when defaultUnit is set.
	 */
	private static function with_unit( $value, $unit = 'px' ) {
		if ( null === $value || '' === $value ) {
			return $value;
		}
		$str = trim( (string) $value );
		if ( '' === $str ) {
			return $str;
		}
		if ( in_array( $str, array( 'auto', 'inherit', 'initial', 'unset' ), true ) ) {
			return $str;
		}
		if ( preg_match( '/[a-zA-Z%]$/', $str ) || 0 === strpos( $str, 'calc(' ) || 0 === strpos( $str, 'var(' ) ) {
			return $str;
		}
		return $str . $unit;
	}

	public static function module_styles( $args ) {
		$attrs       = $args['attrs'] ?? array();
		$elements    = $args['elements'];
		$order_class = $args['orderClass'] ?? '';

		$advanced = $attrs['module']['advanced'] ?? array();

		$image_height        = self::get_responsive_value( $advanced['imageHeight'] ?? array(), 'desktop', 'auto' );
		$image_height_tablet = self::get_responsive_value( $advanced['imageHeight'] ?? array(), 'tablet', $image_height );
		$image_height_phone  = self::get_responsive_value( $advanced['imageHeight'] ?? array(), 'phone', $image_height_tablet );
		$image_width         = self::get_responsive_value( $advanced['imageWidth'] ?? array(), 'desktop', '' );
		$image_size          = self::get_responsive_value( $advanced['imageSize'] ?? array(), 'desktop', 'cover' );

		$object_fit_map = array( 'cover' => 'cover', 'contain' => 'contain', 'actual' => 'none' );
		$object_fit     = $object_fit_map[ $image_size ] ?? 'cover';

		$content_alignment = self::get_responsive_value( $advanced['contentAlignment'] ?? array(), 'desktop', 'left' );
		$is_equal_height   = self::get_responsive_value( $advanced['isEqualHeight'] ?? array(), 'desktop', 'on' );

		$category_bg        = self::get_responsive_value( $advanced['categoryBg'] ?? array(), 'desktop', '#FF2851' );
		$category_offset    = self::get_responsive_value( $advanced['categoryOffset'] ?? array(), 'desktop', '15px' );
		$category_placement = self::get_responsive_value( $advanced['categoryPlacement'] ?? array(), 'desktop', 'top_left' );

		$custom_styles = array();

		// Equal-height rules are emitted later in this method, scoped to Swiper's
		// `.swiper-slide` (see CarouselEngine + the equal-height block below).

		// Image styles.
		$img_width_val = $image_width ? $image_width : '100%';
		$custom_styles[] = array(
			'atRules'     => false,
			'selector'    => "{$order_class} .dtq-post-thumb img",
			'declaration' => "object-fit:{$object_fit};width:{$img_width_val};height:{$image_height};",
		);
		$custom_styles[] = array(
			'atRules'     => 'max-width: 980px',
			'selector'    => "{$order_class} .dtq-post-thumb img",
			'declaration' => "height:{$image_height_tablet};",
		);
		$custom_styles[] = array(
			'atRules'     => 'max-width: 767px',
			'selector'    => "{$order_class} .dtq-post-thumb img",
			'declaration' => "height:{$image_height_phone};",
		);

		// Content alignment.
		$custom_styles[] = array(
			'atRules'     => false,
			'selector'    => "{$order_class} .dtq-blog-content",
			'declaration' => "text-align:{$content_alignment};",
		);

		// Category badge.
		if ( $category_bg ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-categories a",
				'declaration' => "background-color:{$category_bg};",
			);
		}

		// Category position.
		$cat_pos = '';
		if ( false !== strpos( $category_placement, 'top' ) ) {
			$cat_pos .= "top:{$category_offset};";
		}
		if ( false !== strpos( $category_placement, 'bottom' ) ) {
			$cat_pos .= "bottom:{$category_offset};";
		}
		if ( false !== strpos( $category_placement, 'left' ) ) {
			$cat_pos .= "left:{$category_offset};";
		}
		if ( false !== strpos( $category_placement, 'right' ) ) {
			$cat_pos .= "right:{$category_offset};";
		}
		if ( $cat_pos ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-categories",
				'declaration' => $cat_pos,
			);
		}

		// Equal-height slides (Swiper) — stretch slides + let cards fill height.
		$is_equal = self::get_responsive_value( $advanced['isEqualHeight'] ?? array(), 'desktop', 'on' );
		if ( 'on' === $is_equal ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .swiper-slide",
				'declaration' => 'height:auto;',
			);
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .swiper-slide > .dtq-blog-item",
				'declaration' => 'display:flex;flex-direction:column;height:100%;width:100%;',
			);
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .swiper-slide .dtq-post-card",
				'declaration' => 'display:flex;flex-direction:column;flex:1 1 auto;height:100%;',
			);
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .swiper-slide .dtq-blog-content",
				'declaration' => 'flex:1 1 auto;',
			);
		}

		// Navigation + pagination styling via the shared CarouselEngine.
		$custom_styles = array_merge( $custom_styles, CarouselEngine::build_carousel_styles( $order_class, $advanced ) );

		// Only the module wrapper has element decoration now (per-element decorations
		// were removed to stop duplicate "Module Text"/"Background"/"Border" sections
		// in the auto Design panel). Per-element styling is in $custom_styles below.
		$all_styles = array(
			$elements->style( array( 'attrName' => 'module' ) ),
		);

		if ( ! empty( $custom_styles ) ) {
			$all_styles[] = $custom_styles;
		}

		$all_styles[] = CssStyle::style(
			array(
				'selector'  => $order_class,
				'attr'      => $attrs['css'] ?? array(),
				'cssFields' => self::custom_css_fields(),
			)
		);

		Style::add(
			array(
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => $all_styles,
			)
		);
	}
}
