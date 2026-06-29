<?php
/**
 * Table of Contents — Server-side render callback.
 *
 * Renders the module shell (optional title + collapse toggle + empty list) and
 * a `data-toc` settings blob. The frontend script (frontend.js) scans the page
 * for headings and builds the nested list, then wires up smooth scroll,
 * scrollspy, sticky and collapse behaviour.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\TableOfContents\TableOfContentsTrait;

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
	 * Recursion guard so building the server list (which renders post content)
	 * never re-enters the TOC render.
	 *
	 * @var bool
	 */
	private static $is_building = false;

	/**
	 * Slugify heading text into a unique anchor id. Mirrors the frontend
	 * tocSlugify() so server-rendered anchors match the JS-assigned ids.
	 */
	private static function slugify( $text, array &$used ) {
		$slug = strtolower( trim( (string) $text ) );
		$slug = preg_replace( '/[^\p{L}\p{N}\s-]/u', '', $slug );
		$slug = preg_replace( '/[\s_]+/', '-', $slug );
		$slug = preg_replace( '/-+/', '-', $slug );
		$slug = trim( $slug, '-' );
		if ( '' === $slug ) {
			$slug = 'section';
		}
		$base = $slug;
		$n    = 2;
		while ( isset( $used[ $slug ] ) ) {
			$slug = $base . '-' . $n;
			$n++;
		}
		$used[ $slug ] = true;
		return $slug;
	}

	/**
	 * Build the nested <li> markup for the TOC list from a flat list of
	 * { level, text, id } heading items.
	 */
	private static function render_nested( array $items ) {
		if ( empty( $items ) ) {
			return '';
		}

		$levels = array();
		foreach ( $items as $it ) {
			$levels[] = (int) $it['level'];
		}

		$i = 0;
		return self::render_items( $items, $i, min( $levels ) );
	}

	/**
	 * Recursively render the <li> items at a given heading level. Always emits
	 * balanced <ul>/<li> markup regardless of the heading hierarchy (skipped or
	 * out-of-order levels). $i is advanced by reference as items are consumed.
	 */
	private static function render_items( array $items, &$i, $level ) {
		$html  = '';
		$count = count( $items );

		while ( $i < $count ) {
			$cur = (int) $items[ $i ]['level'];

			// Belongs to a shallower level — hand control back to the caller.
			if ( $cur < $level ) {
				break;
			}

			$item = $items[ $i ];
			$i++;

			$link = sprintf(
				'<a href="#%1$s">%2$s</a>',
				esc_attr( $item['id'] ),
				esc_html( $item['text'] )
			);

			// Nest any deeper following items inside this <li>.
			$children = '';
			if ( $i < $count && (int) $items[ $i ]['level'] > $cur ) {
				$children = '<ul>' . self::render_items( $items, $i, (int) $items[ $i ]['level'] ) . '</ul>';
			}

			$html .= '<li class="dtq-toc-item">' . $link . $children . '</li>';
		}

		return $html;
	}

	/**
	 * Server-render the TOC list by scanning the queried post's content. Used as
	 * the no-JS / AMP fallback so the list is populated without JavaScript. The
	 * frontend script re-enhances this when JS is available. Returns '' when not
	 * applicable so the JS path is used.
	 */
	/**
	 * Per-request cache of the rendered post HTML, so multiple TOC instances on
	 * one page don't each re-render the content.
	 *
	 * @var array
	 */
	private static $rendered_cache = array();

	private static function build_server_list( $start, $end, $min ) {
		if ( self::$is_building || is_admin() || ! is_singular() ) {
			return '';
		}

		$post = get_queried_object();
		if ( ! ( $post instanceof \WP_Post ) || empty( $post->post_content ) ) {
			return '';
		}

		if ( array_key_exists( $post->ID, self::$rendered_cache ) ) {
			$rendered = self::$rendered_cache[ $post->ID ];
		} else {
			self::$is_building = true;
			try {
				$rendered = $post->post_content;
				if ( function_exists( 'do_blocks' ) ) {
					$rendered = do_blocks( $rendered );
				}
				$rendered = do_shortcode( $rendered );
			} catch ( \Throwable $e ) {
				$rendered = '';
			}
			self::$is_building            = false;
			self::$rendered_cache[ $post->ID ] = $rendered;
		}

		if ( '' === $rendered ) {
			return '';
		}

		$pattern = '/<h([' . (int) $start . '-' . (int) $end . '])\b[^>]*>(.*?)<\/h\1>/is';
		if ( ! preg_match_all( $pattern, (string) $rendered, $matches, PREG_SET_ORDER ) ) {
			return '';
		}

		$used  = array();
		$items = array();
		foreach ( $matches as $h ) {
			$text = trim( wp_strip_all_tags( $h[2] ) );
			if ( '' === $text ) {
				continue;
			}
			$items[] = array(
				'level' => (int) $h[1],
				'text'  => $text,
				'id'    => self::slugify( $text, $used ),
			);
		}

		if ( count( $items ) < max( 1, (int) $min ) ) {
			return '';
		}

		return self::render_nested( $items );
	}

	/**
	 * Add slugified ids to content headings that lack one, so the server-rendered
	 * (no-JS / AMP) anchors resolve. Registered on `the_content`.
	 */
	public static function add_heading_ids( $content ) {
		if ( is_admin() || ! is_singular() || '' === trim( (string) $content ) ) {
			return $content;
		}

		$used = array();
		return preg_replace_callback(
			'/<h([2-6])\b([^>]*)>(.*?)<\/h\1>/is',
			function ( $m ) use ( &$used ) {
				if ( preg_match( '/\sid\s*=/i', $m[2] ) ) {
					return $m[0]; // Keep an existing id.
				}
				$text = trim( wp_strip_all_tags( $m[3] ) );
				if ( '' === $text ) {
					return $m[0];
				}
				$id = self::slugify( $text, $used );
				return '<h' . $m[1] . ' id="' . esc_attr( $id ) . '"' . $m[2] . '>' . $m[3] . '</h' . $m[1] . '>';
			},
			$content
		);
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

		// AMP / no-JS fallback: pre-render the list server-side from the queried
		// post so it works without JavaScript (AMP strips custom JS). The frontend
		// script enhances/rebuilds this when JS is available.
		$server_list = self::build_server_list( $start_level, $end_level, $min_headings );

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
		if ( '' !== $server_list ) {
			$wrapper_classes[] = 'dtq-toc-prerendered';
		}

		// Header.
		$header_html = '';
		if ( 'on' === $show_title || 'on' === $collapsible ) {
			$title_html  = 'on' === $show_title
				? sprintf( '<span class="dtq-toc-title">%s</span>', esc_html( $title ) )
				: '<span class="dtq-toc-title"></span>';
			$toggle_html = 'on' === $collapsible
				? '<button class="dtq-toc-toggle" type="button" aria-expanded="true" aria-label="' . esc_attr__( 'Toggle table of contents', 'divi-torque-lite' ) . '"><span class="dtq-toc-arrow"></span></button>'
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

		// Style components.
		$style_components = '';
		if ( is_callable( array( $elements, 'style_components' ) ) ) {
			ob_start();
			$elements->style_components( array( 'attrName' => 'module' ) );
			$style_components = ob_get_clean();
		}

		$children = sprintf(
			'%1$s<div class="%2$s" data-toc="%3$s"%4$s>%5$s<nav class="dtq-toc-body"><ul class="dtq-toc-list">%6$s</ul></nav></div>',
			$style_components,
			esc_attr( implode( ' ', $wrapper_classes ) ),
			esc_attr( $toc_data ),
			$sticky_style,
			$header_html,
			$server_list // Already escaped per-node in render_nested().
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
