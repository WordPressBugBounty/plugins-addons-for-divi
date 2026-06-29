<?php
/**
 * Table of Contents — Server-side styles. Mirrors styles.jsx so the Visual
 * Builder preview and the frontend render identically.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\TableOfContents\TableOfContentsTrait;

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

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

	private static function with_unit( $value, $unit = 'px' ) {
		if ( '' === $value || null === $value ) {
			return '';
		}
		if ( is_numeric( $value ) ) {
			return $value . $unit;
		}
		return preg_match( '/[a-z%]$/i', $value ) ? $value : $value . $unit;
	}

	/**
	 * Convert a "top|right|bottom|left" string into a CSS `T R B L` value.
	 * Returns empty string when invalid.
	 */
	private static function parse_padding( $value ) {
		if ( empty( $value ) || ! is_string( $value ) ) {
			return '';
		}
		$parts = array_filter( array_map( 'trim', explode( '|', $value ) ), 'strlen' );
		if ( empty( $parts ) ) {
			return '';
		}
		$parts = array_values( $parts );
		while ( count( $parts ) < 4 ) {
			$parts[] = end( $parts );
		}
		return implode( ' ', array_slice( $parts, 0, 4 ) );
	}

	public static function module_styles( $args ) {
		$attrs       = $args['attrs'] ?? array();
		$elements    = $args['elements'];
		$order_class = $args['orderClass'] ?? '';

		$advanced = $attrs['module']['advanced'] ?? array();

		$title_spacing = self::with_unit( self::get_responsive_value( $advanced['titleSpacing'] ?? array(), 'desktop', '15px' ) );
		$item_spacing  = self::with_unit( self::get_responsive_value( $advanced['itemSpacing'] ?? array(), 'desktop', '8px' ) );
		$level_indent  = self::with_unit( self::get_responsive_value( $advanced['levelIndent'] ?? array(), 'desktop', '16px' ) );
		$link_color    = self::get_responsive_value( $advanced['linkColor'] ?? array(), 'desktop', '' );
		$active_color  = self::get_responsive_value( $advanced['activeColor'] ?? array(), 'desktop', '#2ea3f2' );
		$active_bg     = self::get_responsive_value( $advanced['activeBg'] ?? array(), 'desktop', '' );
		$hover_color   = self::get_responsive_value( $advanced['hoverColor'] ?? array(), 'desktop', '' );
		$marker_color  = self::get_responsive_value( $advanced['markerColor'] ?? array(), 'desktop', '' );
		$marker_type   = self::get_responsive_value( $advanced['markerType'] ?? array(), 'desktop', 'decimal' );
		$link_padding  = self::parse_padding( self::get_responsive_value( $advanced['linkPadding'] ?? array(), 'desktop', '2px|6px|2px|6px' ) );

		$list_style = 'none' === $marker_type ? 'none' : $marker_type;

		$custom_styles = array();

		// Accent variable feeds the style presets.
		$custom_styles[] = array(
			'atRules'     => false,
			'selector'    => $order_class,
			'declaration' => '--dtq-toc-accent:' . ( $active_color ? $active_color : '#2ea3f2' ) . ';',
		);

		$custom_styles[] = array(
			'atRules'     => false,
			'selector'    => "{$order_class} .dtq-toc-title",
			'declaration' => "margin-bottom:{$title_spacing};",
		);
		// !important on list-style + spacing so they beat theme ID selectors
		// (e.g. `#left-area ul`) that also style these properties.
		$custom_styles[] = array(
			'atRules'     => false,
			'selector'    => "{$order_class} .dtq-toc-list, {$order_class} .dtq-toc-list ul",
			'declaration' => "list-style-type:{$list_style} !important;",
		);
		// Spacing: adjacent-sibling margin-top only — no trailing margins to
		// accumulate where a nested sub-list returns to a top-level item.
		$custom_styles[] = array(
			'atRules'     => false,
			'selector'    => "{$order_class} .dtq-toc-list ul",
			'declaration' => "padding-left:{$level_indent} !important;margin-top:{$item_spacing} !important;",
		);
		$custom_styles[] = array(
			'atRules'     => false,
			'selector'    => "{$order_class} .dtq-toc-item + .dtq-toc-item",
			'declaration' => "margin-top:{$item_spacing} !important;",
		);

		if ( $link_padding ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-toc-list a",
				'declaration' => "padding:{$link_padding};",
			);
		}
		if ( $link_color ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-toc-list a",
				'declaration' => "color:{$link_color};",
			);
		}
		if ( $hover_color ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-toc-list a:hover",
				'declaration' => "color:{$hover_color};",
			);
		}
		if ( $active_color ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-toc-list .dtq-toc-active > a",
				'declaration' => "color:{$active_color};",
			);
		}
		if ( $active_bg ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-toc-list .dtq-toc-active > a",
				'declaration' => "background-color:{$active_bg};",
			);
		}
		if ( $marker_color ) {
			$custom_styles[] = array(
				'atRules'     => false,
				'selector'    => "{$order_class} .dtq-toc-list li::marker",
				'declaration' => "color:{$marker_color};",
			);
		}

		$all_styles = array(
			$elements->style( array( 'attrName' => 'module' ) ),
			$elements->style( array( 'attrName' => 'titleFont' ) ),
			$elements->style( array( 'attrName' => 'linkFont' ) ),
			$custom_styles,
		);

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
