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

	/**
	 * Build a single declaration, or nothing when the value is empty.
	 *
	 * Mirrors the `decl()` helper in styles.jsx so both halves omit the same
	 * declarations for unset options rather than emitting `color:;`.
	 */
	private static function decl( $prop, $value ) {
		return ( null === $value || '' === $value ) ? '' : "{$prop}:{$value};";
	}

	/**
	 * Read a colour option, resolve global colours, and sanitize it.
	 *
	 * Returns '' for unset or unparseable values so self::decl() drops the
	 * declaration. Sanitizing matters because these land in a stylesheet: an
	 * unchecked value could terminate the rule and inject arbitrary CSS.
	 */
	private static function color_value( $advanced, $key, $default = '' ) {
		$raw = self::get_responsive_value( $advanced[ $key ] ?? array(), 'desktop', $default );
		return dtq_css_color( dtq_resolve_css_value( $raw ), '' );
	}

	/**
	 * Read a length option, append the default unit, and sanitize it.
	 */
	private static function length_value( $advanced, $key, $default = '', $unit = 'px' ) {
		$raw = self::with_unit( self::get_responsive_value( $advanced[ $key ] ?? array(), 'desktop', $default ), $unit );
		return dtq_css_length( dtq_resolve_css_value( $raw ), '' );
	}

	public static function module_styles( $args ) {
		$attrs       = $args['attrs'] ?? array();
		$elements    = $args['elements'];
		$settings    = $args['settings'] ?? array();
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

		// Placeholder background when a post has no featured image.
		$no_thumb_bg = self::color_value( $advanced, 'noThumbBgColor' );
		if ( '' !== $no_thumb_bg ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-empty-thumb",
				'declaration' => "background:{$no_thumb_bg};",
			);
		}

		// Title.
		$title_color           = self::color_value( $advanced, 'titleColor' );
		$title_font_size       = self::length_value( $advanced, 'titleFontSize' );
		$title_spacing_top     = self::length_value( $advanced, 'titleSpacingTop' );
		$title_spacing_bottom  = self::length_value( $advanced, 'titleSpacingBottom' );

		$title_decl = self::decl( 'padding-top', $title_spacing_top )
			. self::decl( 'padding-bottom', $title_spacing_bottom )
			. self::decl( 'font-size', $title_font_size );
		if ( '' !== $title_decl ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-title",
				'declaration' => $title_decl,
			);
		}
		if ( '' !== $title_color ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-title, {$order_class} .dtq-post-title a",
				'declaration' => "color:{$title_color};",
			);
		}

		// Excerpt.
		$excerpt_decl = self::decl( 'color', self::color_value( $advanced, 'excerptColor' ) )
			. self::decl( 'font-size', self::length_value( $advanced, 'excerptFontSize' ) )
			. self::decl( 'padding-top', self::length_value( $advanced, 'excerptSpacingTop' ) )
			. self::decl( 'padding-bottom', self::length_value( $advanced, 'excerptSpacingBottom' ) );
		if ( '' !== $excerpt_decl ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-excerpt",
				'declaration' => $excerpt_decl,
			);
		}

		// Category badge. background-color is always emitted (it has a default);
		// colour and font-size only when set, matching styles.jsx.
		$category_decl = '';
		if ( $category_bg ) {
			$category_bg_safe = dtq_css_color( dtq_resolve_css_value( $category_bg ), 'transparent' );
			$category_decl   .= "background-color:{$category_bg_safe};";
		}
		$category_decl .= self::decl( 'color', self::color_value( $advanced, 'categoryColor' ) )
			. self::decl( 'font-size', self::length_value( $advanced, 'categoryFontSize' ) );
		if ( '' !== $category_decl ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-categories a",
				'declaration' => $category_decl,
			);
		}

		// Author / date meta.
		$author_decl = self::decl( 'color', self::color_value( $advanced, 'authorColor' ) )
			. self::decl( 'font-size', self::length_value( $advanced, 'authorFontSize' ) );
		if ( '' !== $author_decl ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-author, {$order_class} .dtq-post-author a",
				'declaration' => $author_decl,
			);
		}
		$date_decl = self::decl( 'color', self::color_value( $advanced, 'dateColor' ) )
			. self::decl( 'font-size', self::length_value( $advanced, 'dateFontSize' ) );
		if ( '' !== $date_decl ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-date",
				'declaration' => $date_decl,
			);
		}

		// Read More button.
		$btn_spacing_top = self::length_value( $advanced, 'btnSpacingTop', '15' );
		if ( '' !== $btn_spacing_top ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-btn-wrap",
				'declaration' => "padding-top:{$btn_spacing_top};",
			);
		}

		$btn_padding_y = self::length_value( $advanced, 'btnPaddingY', '8' );
		$btn_padding_x = self::length_value( $advanced, 'btnPaddingX', '20' );
		$btn_decl      = self::decl( 'font-size', self::length_value( $advanced, 'btnFontSize', '14' ) );
		if ( '' !== $btn_padding_y && '' !== $btn_padding_x ) {
			$btn_decl .= "padding:{$btn_padding_y} {$btn_padding_x};";
		}
		$btn_decl .= self::decl( 'border-radius', self::length_value( $advanced, 'btnBorderRadius', '3' ) )
			. self::decl( 'color', self::color_value( $advanced, 'btnTextColor' ) )
			. self::decl( 'background-color', self::color_value( $advanced, 'btnBgColor' ) );
		if ( '' !== $btn_decl ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-btn",
				'declaration' => $btn_decl,
			);
		}

		$btn_hover_decl = self::decl( 'color', self::color_value( $advanced, 'btnHoverTextColor' ) )
			. self::decl( 'background-color', self::color_value( $advanced, 'btnHoverBgColor' ) );
		if ( '' !== $btn_hover_decl ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-post-btn:hover",
				'declaration' => $btn_hover_decl,
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
			// styleProps.disabledOn must match styles.jsx, or "Disable On" is
			// honoured in the builder and ignored on the front end.
			$elements->style(
				array(
					'attrName'   => 'module',
					'styleProps' => array(
						'disabledOn' => array(
							'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
						),
					),
				)
			),
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
