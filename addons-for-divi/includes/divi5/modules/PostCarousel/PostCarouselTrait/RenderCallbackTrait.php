<?php
/**
 * PostCarousel — Server-side render callback.
 *
 * Mirrors PostGrid/PostMasonry RenderCallbackTrait (single $els map + layout
 * dispatcher). Slick is initialized client-side in src/divi5/frontend.js by
 * reading the data-slick JSON attribute we emit on `.dtq-blog`.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\PostCarousel\PostCarouselTrait;

use ET\Builder\Packages\Module\Module;
use DiviTorqueLite\Modules\SharedCarousel\CarouselEngine;

trait RenderCallbackTrait {

	/**
	 * Render the PostCarousel module on the frontend.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {

		$get = function ( $path, $default = '', $device = 'desktop' ) use ( $attrs ) {
			$keys  = explode( '.', $path );
			$value = $attrs;
			foreach ( $keys as $key ) {
				if ( ! isset( $value[ $key ] ) ) {
					return $default;
				}
				$value = $value[ $key ];
			}
			if ( is_array( $value ) && isset( $value[ $device ]['value'] ) && '' !== $value[ $device ]['value'] ) {
				return $value[ $device ]['value'];
			}
			if ( 'desktop' !== $device && is_array( $value ) && isset( $value['desktop']['value'] ) ) {
				return $value['desktop']['value'];
			}
			return is_array( $value ) ? ( $value['desktop']['value'] ?? $default ) : ( $value ?: $default );
		};

		// --- Query settings ---
		$post_type       = $get( 'module.advanced.postType', 'post' );
		$categories      = $get( 'module.advanced.includeCategories', '' );
		$taxonomy        = $get( 'module.advanced.customTaxonomy', '' );
		$taxonomy_terms  = $get( 'module.advanced.customTaxonomyTerms', '' );
		$order_by        = $get( 'module.advanced.orderBy', 'date' );
		$order           = $get( 'module.advanced.order', 'ASC' );
		$post_count      = (int) ( $get( 'module.advanced.postCount', '6' ) ?: 6 );
		$offset_number   = (int) $get( 'module.advanced.offsetNumber', '0' );
		$include_posts   = $get( 'module.advanced.includePosts', '' );
		$exclude_posts   = $get( 'module.advanced.excludePosts', '' );
		$date_format     = $get( 'module.advanced.dateFormat', 'M d, Y' );
		$content_length  = (int) ( $get( 'module.advanced.contentLength', '150' ) ?: 150 );
		$thumb_size      = $get( 'module.advanced.thumbSize', 'full' );
		$pagination_type = $get( 'module.advanced.paginationType', '' );

		// No-thumbnail fallback settings.
		$no_thumb_behavior = $get( 'module.advanced.noThumbBehavior', 'placeholder' );
		$no_thumb_image    = $get( 'module.advanced.noThumbImage', '' );

		// --- Layout settings ---
		$layout             = $get( 'module.advanced.layout', 'layout1' ) ?: 'layout1';
		$is_equal_height    = $get( 'module.advanced.isEqualHeight', 'on' );
		$show_thumb         = $get( 'module.advanced.showThumb', 'on' );
		$show_title         = $get( 'module.advanced.showTitle', 'on' );
		$show_excerpt       = $get( 'module.advanced.showExcerpt', 'on' );
		$show_categories    = $get( 'module.advanced.showCategories', 'on' );
		$show_first_cat     = $get( 'module.advanced.showFirstCategory', 'off' );
		$show_author        = $get( 'module.advanced.showAuthor', 'on' );
		$show_date          = $get( 'module.advanced.showDate', 'on' );
		$show_btn           = $get( 'module.advanced.showBtn', 'on' );
		$show_avatar        = $get( 'module.advanced.showAvatar', 'on' );
		$button_text        = $get( 'module.advanced.buttonText', 'Read More' );
		$title_level        = $get( 'module.advanced.titleLevel', 'h4' );
		$img_hover_style    = $get( 'module.advanced.imgHoverStyle', 'none' );

		// --- Build WP_Query ---
		$current_page = max( 1, (int) ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' ) ) );
		$paged_offset = $offset_number + ( ( $current_page - 1 ) * $post_count );

		$query_args = array(
			'posts_per_page' => $post_count,
			'post_type'      => sanitize_text_field( $post_type ),
			'post_status'    => array( 'publish' ),
			'orderby'        => sanitize_text_field( $order_by ),
			'order'          => sanitize_text_field( $order ),
			'offset'         => $pagination_type ? $paged_offset : $offset_number,
			'paged'          => $current_page,
		);

		// Category filter — accepts comma-separated IDs or slugs.
		if ( ! empty( $categories ) ) {
			$terms = array_filter( array_map( 'trim', explode( ',', $categories ) ) );
			if ( ! empty( $terms ) ) {
				$field = ctype_digit( (string) $terms[0] ) ? 'term_id' : 'slug';
				$query_args['tax_query'][] = array(
					'taxonomy' => 'category',
					'field'    => $field,
					'terms'    => array_map( 'sanitize_text_field', $terms ),
				);
			}
		}

		// Custom taxonomy filter (for any post type).
		if ( ! empty( $taxonomy ) && ! empty( $taxonomy_terms ) ) {
			$terms = array_filter( array_map( 'trim', explode( ',', $taxonomy_terms ) ) );
			if ( ! empty( $terms ) ) {
				$field = ctype_digit( (string) $terms[0] ) ? 'term_id' : 'slug';
				$query_args['tax_query'][] = array(
					'taxonomy' => sanitize_text_field( $taxonomy ),
					'field'    => $field,
					'terms'    => array_map( 'sanitize_text_field', $terms ),
				);
			}
		}

		if ( ! empty( $include_posts ) ) {
			$query_args['post__in'] = array_map( 'absint', array_filter( explode( ',', $include_posts ) ) );
		}
		if ( ! empty( $exclude_posts ) ) {
			$query_args['post__not_in'] = array_map( 'absint', array_filter( explode( ',', $exclude_posts ) ) );
		}

		$query = new \WP_Query( $query_args );

		// --- Render posts ---
		$posts_html = '';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_id   = get_the_ID();
				$permalink = esc_url( get_the_permalink() );
				$title     = esc_html( get_the_title() );

				$cats_html = self::el_categories( $post_id, $show_categories, $show_first_cat );

				$thumb_args = array(
					'post_id'           => $post_id,
					'permalink'         => $permalink,
					'title'             => $title,
					'thumb_size'        => $thumb_size,
					'img_hover_style'   => $img_hover_style,
					'show_thumb'        => $show_thumb,
					'layout'            => $layout,
					'no_thumb_behavior' => $no_thumb_behavior,
					'no_thumb_image'    => $no_thumb_image,
				);

				$els = array(
					'cats_html'             => $cats_html,
					'standalone_categories' => ( 'on' !== $show_thumb ) ? $cats_html : '',
					'title'                 => self::el_title( $permalink, $title, $show_title, $title_level ),
					'excerpt'               => self::el_excerpt( $show_excerpt, $content_length ),
					'author'                => self::el_author( $show_author, $layout ),
					'date'                  => self::el_date( $show_date, $date_format, $layout ),
					'button'                => self::el_button( $show_btn, $button_text, $permalink, $layout ),
					'floating_avatar'       => self::el_floating_avatar( $show_avatar, $layout ),
					'thumbnail'             => self::el_thumbnail( array_merge( $thumb_args, array( 'cats_html' => $cats_html, 'with_categories' => true ) ) ),
					'thumbnail_no_cats'     => self::el_thumbnail( array_merge( $thumb_args, array( 'with_categories' => false ) ) ),
					'layout3_thumb'         => self::el_thumbnail( array_merge( $thumb_args, array( 'with_categories' => false ) ) ),
				);

				$card_html = self::render_layout_card( $layout, $els );

				$posts_html .= sprintf(
					'<div class="swiper-slide"><div class="dtq-blog-item dtq-blog-item-%s">%s</div></div>',
					esc_attr( $layout ),
					$card_html
				);
			}
		}

		// Pagination.
		$pagination_html = '';
		if ( $pagination_type && $query->max_num_pages > 1 ) {
			$pagination_html = self::render_pagination( $pagination_type, $current_page, $query->max_num_pages );
		}

		wp_reset_postdata();

		// Container classes.
		$classes = array(
			'dtq-module',
			'dtq-carousel dtq-blog-carousel dtq-carousel-frontend',
			'equal-height-' . esc_attr( $is_equal_height ),
		);

		// Style components.
		$style_components = '';
		if ( is_callable( array( $elements, 'style_components' ) ) ) {
			ob_start();
			$elements->style_components( array( 'attrName' => 'module' ) );
			$style_components = ob_get_clean();
		}

		// Build the Swiper config + chrome via the shared CarouselEngine. The
		// front end's initCarousels picks up `.dtq-swiper-carousel .swiper`, so
		// no module-specific carousel JS is needed.
		$advanced  = $attrs['module']['advanced'] ?? array();
		$built     = CarouselEngine::build_swiper_config( $advanced );
		$show_nav  = $built['show_nav'];
		$show_pagi = $built['show_pagi'];
		$wrap_cls  = CarouselEngine::base_wrapper_classes( $advanced, 'dtq-post-carousel' );
		$nav_html  = $show_nav ? ( CarouselEngine::render_arrow( $advanced, 'prev' ) . CarouselEngine::render_arrow( $advanced, 'next' ) ) : '';
		$pagi_html = $show_pagi ? '<div class="swiper-pagination"></div>' : '';

		$children = sprintf(
			'%s<div class="%s"><div class="%s"><div class="swiper" data-swiper-config="%s"><div class="swiper-wrapper">%s</div></div>%s%s</div>%s</div>',
			$style_components,
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( implode( ' ', $wrap_cls ) ),
			esc_attr( wp_json_encode( $built['config'] ) ),
			$posts_html,
			$nav_html,
			$pagi_html,
			$pagination_html
		);

		return Module::render(
			array(
				'orderIndex'         => $block->parsed_block['orderIndex'] ?? 0,
				'attrs'              => $attrs,
				'elements'           => $elements,
				'id'                 => $block->parsed_block['id'] ?? '',
				'moduleClassName'    => 'dtq_post_carousel',
				'name'               => $block->block_type->name ?? '',
				'classnamesFunction' => array( self::class, 'module_classnames' ),
				'stylesComponent'    => array( self::class, 'module_styles' ),
				'scriptDataComponent' => array( self::class, 'module_script_data' ),
				'children'           => $children,
			)
		);
	}

	/**
	 * Render pagination markup.
	 */
	private static function render_pagination( $type, $current_page, $max_num_pages ) {
		$inner = '';

		if ( 'prev_next' === $type ) {
			$older = get_next_posts_link( __( '&laquo; Older Entries', 'divi-torque-lite' ), $max_num_pages );
			$newer = get_previous_posts_link( __( 'Newer Entries &raquo;', 'divi-torque-lite' ) );
			$inner = (string) $older . (string) $newer;
		} elseif ( 'numbers' === $type ) {
			$paginate_args = array(
				'current'   => $current_page,
				'total'     => $max_num_pages,
				'prev_next' => false,
			);
			if ( is_singular() && ! is_front_page() ) {
				global $wp_rewrite;
				if ( $wp_rewrite && $wp_rewrite->using_permalinks() ) {
					$paginate_args['base']   = trailingslashit( get_permalink() ) . '%_%';
					$paginate_args['format'] = user_trailingslashit( '%#%', 'single_paged' );
				} else {
					$paginate_args['format'] = '?page=%#%';
				}
			}
			$inner = (string) paginate_links( $paginate_args );
		}

		if ( '' === $inner ) {
			return '';
		}

		return sprintf(
			'<nav class="dtq-pagination" role="navigation" aria-label="%s">%s</nav>',
			esc_attr__( 'Pagination', 'divi-torque-lite' ),
			wp_kses_post( $inner )
		);
	}
}
