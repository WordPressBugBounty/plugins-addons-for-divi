<?php
/**
 * Table of Contents — Server-side render callback.
 *
 * Renders the module shell (optional title + collapse toggle + list) and a
 * `data-toc` settings blob. The list is a slot that HeadingIndex::fill() fills
 * from the finished page HTML (see add_heading_ids()); the frontend script
 * (frontend.js) keeps that list when every link resolves, or scans the page
 * and builds one, then wires up smooth scroll, scrollspy, sticky and collapse
 * behaviour.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\TableOfContents\TableOfContentsTrait;

use DiviTorqueLite\Modules\TableOfContents\HeadingIndex;
use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait {

	/**
	 * Extract a nested attribute value with device fallback. Empty strings are
	 * treated as unset so module.json defaults always win.
	 */
	private static function get_attr( $attrs, $path, $default = '', $device = 'desktop' ) {
		$keys  = explode( '.', $path );
		$value = $attrs;

		foreach ( $keys as $key ) {
			if ( ! isset( $value[ $key ] ) ) {
				return $default;
			}
			$value = $value[ $key ];
		}

		if ( is_array( $value ) ) {
			if ( isset( $value[ $device ]['value'] ) && '' !== $value[ $device ]['value'] ) {
				return $value[ $device ]['value'];
			}
			if ( isset( $value['desktop']['value'] ) && '' !== $value['desktop']['value'] ) {
				return $value['desktop']['value'];
			}
			return $default;
		}

		return '' !== $value && null !== $value ? $value : $default;
	}

	/**
	 * Convert an 'h2' style level to its numeric depth (2).
	 */
	private static function level_num( $level, $default ) {
		if ( preg_match( '/([1-6])/', (string) $level, $m ) ) {
			return (int) $m[1];
		}
		return $default;
	}

	/**
	 * Strip a CSS unit and return the integer pixel value.
	 */
	private static function px_int( $value, $default = 0 ) {
		if ( '' === $value || null === $value ) {
			return $default;
		}
		return (int) preg_replace( '/[^0-9.\-]/', '', (string) $value );
	}

	/**
	 * Give content headings their anchor ids and fill any Table of Contents
	 * list in the same HTML. Registered on `the_content` at priority 99, after
	 * Divi has rendered the blocks (see also add_heading_ids_to_layout()).
	 *
	 * Gated on the TOC slot: only HTML that holds a Table of Contents is
	 * touched, so no other post or page pays for the regex pass or gets ids
	 * injected. This replaced a has_block() test on the queried post, which
	 * also matched when the TOC sat in post content but the list was rendered
	 * elsewhere, and missed Theme Builder placements entirely.
	 *
	 * @param string $content Rendered content.
	 * @return string
	 */
	public static function add_heading_ids( $content ) {
		if ( ! is_string( $content ) || '' === $content || is_admin() ) {
			return $content;
		}

		/**
		 * Filters whether heading ids are injected into this HTML.
		 *
		 * @param bool   $has_toc Whether the HTML holds a Table of Contents list slot.
		 * @param string $content The HTML.
		 */
		if ( ! apply_filters( 'divitorque_toc_add_heading_ids', HeadingIndex::has_slot( $content ), $content ) ) {
			return $content;
		}

		return HeadingIndex::fill( $content );
	}

	/**
	 * The same for a Theme Builder body layout, on `et_builder_render_layout`
	 * at priority 99. By then the layout's Post Content module has rendered
	 * the post into the same HTML, so a TOC placed in the template lists the
	 * post's headings.
	 *
	 * Body layouts only. A header or footer layout does not hold the page's
	 * content, and the filter also runs for layouts rendered inside a post
	 * (a library shortcode), whose TOC slot must survive to the post's own
	 * `the_content` pass. An unfilled slot is an empty list, which the front
	 * end builds from the page.
	 *
	 * @param string $content Rendered layout.
	 * @return string
	 */
	public static function add_heading_ids_to_layout( $content ) {
		$is_body_layout = defined( 'ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE' )
			&& class_exists( 'ET_Theme_Builder_Layout' )
			&& method_exists( 'ET_Theme_Builder_Layout', 'get_theme_builder_layout_type' )
			&& ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE === \ET_Theme_Builder_Layout::get_theme_builder_layout_type();

		return $is_body_layout ? self::add_heading_ids( $content ) : $content;
	}

	/**
	 * Render the Table of Contents module.
	 *
	 * @param array     $attrs    Module attributes.
	 * @param string    $content  Inner content.
	 * @param \WP_Block $block    Block instance.
	 * @param object    $elements Elements helper.
	 * @return string
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {

		$style_preset        = self::get_attr( $attrs, 'module.advanced.stylePreset', 'default' );
		$active_color        = self::get_attr( $attrs, 'module.advanced.activeColor', '#2ea3f2' );
		$show_title          = self::get_attr( $attrs, 'module.advanced.showTitle', 'on' );
		$title               = self::get_attr( $attrs, 'module.advanced.title', 'Table of Contents' );
		$start_level         = self::level_num( self::get_attr( $attrs, 'module.advanced.startLevel', 'h2' ), 2 );
		$end_level           = self::level_num( self::get_attr( $attrs, 'module.advanced.endLevel', 'h4' ), 4 );
		$content_selector    = self::get_attr( $attrs, 'module.advanced.contentSelector', '' );
		$exclude_selector    = self::get_attr( $attrs, 'module.advanced.excludeSelector', '' );
		$min_headings        = (int) ( self::get_attr( $attrs, 'module.advanced.minHeadings', '1' ) ?: 1 );
		$collapsible         = self::get_attr( $attrs, 'module.advanced.collapsible', 'off' );
		$initially_collapsed = self::get_attr( $attrs, 'module.advanced.initiallyCollapsed', 'off' );
		$collapsible_subs    = self::get_attr( $attrs, 'module.advanced.collapsibleSubHeadings', 'off' );
		$subs_collapsed      = self::get_attr( $attrs, 'module.advanced.subHeadingsCollapsed', 'off' );
		$collapse_icon       = self::get_attr( $attrs, 'module.advanced.collapseIcon', 'chevron' );
		$smooth_scroll       = self::get_attr( $attrs, 'module.advanced.smoothScroll', 'on' );
		$scroll_offset       = self::px_int( self::get_attr( $attrs, 'module.advanced.scrollOffset', '0px' ), 0 );
		$highlight_active    = self::get_attr( $attrs, 'module.advanced.highlightActive', 'on' );
		$enable_view_more    = self::get_attr( $attrs, 'module.advanced.enableViewMore', 'off' );
		$view_more_count     = (int) ( self::get_attr( $attrs, 'module.advanced.viewMoreCount', '5' ) ?: 5 );
		$view_more_text      = self::get_attr( $attrs, 'module.advanced.viewMoreText', 'Show more' );
		$view_less_text      = self::get_attr( $attrs, 'module.advanced.viewLessText', 'Show less' );
		$sticky              = self::get_attr( $attrs, 'module.advanced.sticky', 'off' );
		$sticky_offset       = self::px_int( self::get_attr( $attrs, 'module.advanced.stickyOffset', '30px' ), 30 );
		$sticky_max_height   = self::px_int( self::get_attr( $attrs, 'module.advanced.stickyMaxHeight', '0px' ), 0 );
		$hide_on_mobile      = self::get_attr( $attrs, 'module.advanced.hideOnMobile', 'off' );

		// Keep start <= end.
		if ( $start_level > $end_level ) {
			$tmp         = $start_level;
			$start_level = $end_level;
			$end_level   = $tmp;
		}

		$toc_data = wp_json_encode(
			array(
				'startLevel'             => $start_level,
				'endLevel'               => $end_level,
				'contentSelector'        => (string) $content_selector,
				'excludeSelector'        => (string) $exclude_selector,
				'minHeadings'            => max( 1, $min_headings ),
				'collapsible'            => 'on' === $collapsible,
				'initiallyCollapsed'     => 'on' === $initially_collapsed,
				'collapsibleSubHeadings' => 'on' === $collapsible_subs,
				'subHeadingsCollapsed'   => 'on' === $subs_collapsed,
				'smoothScroll'           => 'on' === $smooth_scroll,
				'scrollOffset'           => $scroll_offset,
				'highlightActive'        => 'on' === $highlight_active,
				'enableViewMore'         => 'on' === $enable_view_more,
				'viewMoreCount'          => max( 1, $view_more_count ),
				'viewMoreText'           => (string) $view_more_text,
				'viewLessText'           => (string) $view_less_text,
				'sticky'                 => 'on' === $sticky,
				'stickyOffset'           => $sticky_offset,
				'stickyMaxHeight'        => $sticky_max_height,
				'hideOnMobile'           => 'on' === $hide_on_mobile,
			)
		);

		// AMP / no-JS fallback: a slot that add_heading_ids() fills with the list
		// once the page HTML exists (AMP strips custom JS). Not with a content or
		// exclude selector: PHP cannot evaluate CSS selectors, so a server list
		// would ignore them; the front end builds that list instead.
		$server_list = ( '' === trim( (string) $content_selector ) && '' === trim( (string) $exclude_selector ) )
			? HeadingIndex::slot( $start_level, $end_level, $min_headings )
			: '';

		// Module wrapper classes.
		$preset_slug     = preg_replace( '/[^a-z0-9_-]/', '', (string) $style_preset );
		$wrapper_classes = array( 'dtq-module', 'dtq-toc', 'dtq-toc-style-' . ( $preset_slug ?: 'default' ) );
		if ( 'on' === $sticky ) {
			$wrapper_classes[] = 'dtq-toc-sticky';
		}
		if ( 'on' === $collapsible && 'on' === $initially_collapsed ) {
			$wrapper_classes[] = 'dtq-toc-collapsed';
		}
		if ( 'on' === $hide_on_mobile ) {
			$wrapper_classes[] = 'dtq-toc-hide-mobile';
		}
		if ( 'on' === $collapsible_subs ) {
			$wrapper_classes[] = 'dtq-toc-collapse-subs';
		}
		$icon_slug         = preg_replace( '/[^a-z]/', '', (string) $collapse_icon );
		$wrapper_classes[] = 'dtq-toc-icon-' . ( $icon_slug ?: 'chevron' );

		// Header.
		$header_html = '';
		if ( 'on' === $show_title || 'on' === $collapsible ) {
			$title_html  = 'on' === $show_title
				? sprintf( '<span class="dtq-toc-title">%s</span>', esc_html( $title ) )
				: '<span class="dtq-toc-title"></span>';
			$toggle_html = 'on' === $collapsible
				? '<button class="dtq-toc-toggle" type="button" aria-expanded="true" aria-label="' . esc_attr__( 'Toggle table of contents', 'addons-for-divi' ) . '"><span class="dtq-toc-arrow"></span></button>'
				: '';
			$header_html = sprintf( '<div class="dtq-toc-header">%s%s</div>', $title_html, $toggle_html );
		}

		// Inline style: accent CSS variable (drives preset accents) + sticky offset.
		$inline_style  = '--dtq-toc-accent:' . esc_attr( $active_color ) . ';';
		if ( 'on' === $sticky ) {
			$inline_style .= 'top:' . (int) $sticky_offset . 'px;';
			if ( $sticky_max_height > 0 ) {
				$inline_style .= '--dtq-toc-sticky-max:' . (int) $sticky_max_height . 'px;';
			}
		}
		$sticky_style = ' style="' . $inline_style . '"';

		// Style components (background pattern/mask, box-shadow overlay). The
		// method returns its markup; it used to be wrapped in an output buffer as
		// if it echoed, so the return value was dropped and the options did
		// nothing on the front end.
		$style_components = is_callable( array( $elements, 'style_components' ) )
			? (string) $elements->style_components( array( 'attrName' => 'module' ) )
			: '';

		$children = sprintf(
			'%1$s<div class="%2$s" data-toc="%3$s"%4$s>%5$s<nav class="dtq-toc-body"><ul class="dtq-toc-list">%6$s</ul></nav></div>',
			$style_components,
			esc_attr( implode( ' ', $wrapper_classes ) ),
			esc_attr( $toc_data ),
			$sticky_style,
			$header_html,
			$server_list // A fixed-format HTML comment; filled by HeadingIndex::fill().
		);

		return Module::render(
			array(
				'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'] ?? '',
				'moduleClassName'     => 'dtq_table_of_contents',
				'name'                => $block->block_type->name ?? '',
				'classnamesFunction'  => array( self::class, 'module_classnames' ),
				'stylesComponent'     => array( self::class, 'module_styles' ),
				'scriptDataComponent' => array( self::class, 'module_script_data' ),
				'children'            => $children,
			)
		);
	}
}
