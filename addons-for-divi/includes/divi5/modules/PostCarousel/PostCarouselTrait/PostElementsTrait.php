<?php
/**
 * PostCarousel — Per-element renderers (server-side mirror of post-elements.jsx).
 *
 * Each render_* helper produces the HTML for one logical piece of a post card.
 * Layout assemblers in RenderCallbackTrait compose these — single source of
 * truth, no duplication across layouts.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\PostCarousel\PostCarouselTrait;

trait PostElementsTrait {

	/**
	 * Render category badges for a post.
	 */
	protected static function el_categories( $post_id, $show_categories, $show_first_cat ) {
		if ( 'on' !== $show_categories ) {
			return '';
		}
		$terms = get_the_terms( $post_id, 'category' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}
		if ( 'on' === $show_first_cat ) {
			$terms = array( $terms[0] );
		}
		$html = '<div class="dtq-post-categories">';
		foreach ( $terms as $term ) {
			$html .= sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_term_link( $term ) ),
				esc_html( $term->name )
			);
		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * Render the post title (heading with permalink).
	 */
	protected static function el_title( $permalink, $title, $show_title, $title_level ) {
		if ( 'on' !== $show_title ) {
			return '';
		}
		$tag = in_array( $title_level, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ? $title_level : 'h4';
		return sprintf(
			'<%1$s class="dtq-post-title"><a href="%2$s">%3$s</a></%1$s>',
			$tag,
			$permalink,
			$title
		);
	}

	/**
	 * Render the trimmed post excerpt.
	 */
	protected static function el_excerpt( $show_excerpt, $content_length ) {
		if ( 'on' !== $show_excerpt ) {
			return '';
		}
		$excerpt = get_the_excerpt();
		if ( empty( $excerpt ) ) {
			$excerpt = get_the_content();
		}
		$excerpt = wp_strip_all_tags( strip_shortcodes( $excerpt ) );
		if ( $content_length > 0 && mb_strlen( $excerpt ) > $content_length ) {
			$excerpt = mb_substr( $excerpt, 0, $content_length ) . '&hellip;';
		}
		return sprintf( '<div class="dtq-post-excerpt">%s</div>', esc_html( $excerpt ) );
	}

	/**
	 * Render the author element. Layouts 1 and 5 render an avatar image inline;
	 * layouts 2/3/4 render an SVG user icon.
	 */
	protected static function el_author( $show_author, $layout ) {
		if ( 'on' !== $show_author ) {
			return '';
		}
		$author_name = esc_html( get_the_author() );
		$author_link = esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );
		$use_avatar  = in_array( $layout, array( 'layout1', 'layout5' ), true );

		if ( $use_avatar ) {
			$avatar = get_avatar_url( get_the_author_meta( 'ID' ), array( 'size' => 52 ) );
			$img    = $avatar
				? sprintf( '<img src="%s" alt="%s" class="dtq-author-img" />', esc_url( $avatar ), $author_name )
				: '';
			return sprintf(
				'<div class="dtq-post-author">%s<a href="%s">%s</a></div>',
				$img,
				$author_link,
				$author_name
			);
		}

		$svg = function_exists( 'dtq_get_svg_user_icon' ) ? dtq_get_svg_user_icon() : '';
		$svg = str_replace( '<svg ', '<svg width="14" height="14" ', $svg );
		return sprintf(
			'<div class="dtq-post-author">%s<a href="%s">%s</a></div>',
			$svg,
			$author_link,
			$author_name
		);
	}

	/**
	 * Render the post date. Layouts 1 and 5 omit the SVG icon.
	 */
	protected static function el_date( $show_date, $date_format, $layout ) {
		if ( 'on' !== $show_date ) {
			return '';
		}
		$date_text = esc_html( get_the_date( $date_format ) );
		$omit_icon = in_array( $layout, array( 'layout1', 'layout5' ), true );

		if ( $omit_icon ) {
			return sprintf( '<div class="dtq-post-date dtq-flex-base">%s</div>', $date_text );
		}
		$svg = function_exists( 'dtq_get_svg_clock_icon' ) ? dtq_get_svg_clock_icon() : '';
		$svg = str_replace( '<svg ', '<svg width="14" height="14" ', $svg );
		return sprintf( '<div class="dtq-post-date">%s%s</div>', $svg, $date_text );
	}

	/**
	 * Render the read more button. Hidden for layout 4 (mirrors D4 show_btn show_if).
	 * Layout 5 uses an arrow style.
	 */
	protected static function el_button( $show_btn, $button_text, $permalink, $layout ) {
		if ( 'on' !== $show_btn || 'layout4' === $layout ) {
			return '';
		}
		if ( 'layout5' === $layout ) {
			return sprintf(
				'<div class="dtq-post-btn-wrap"><a class="dtq-post-btn dtq-post-btn-arrow" href="%s">%s <span class="dtq-btn-arrow">&rarr;</span></a></div>',
				$permalink,
				esc_html( $button_text )
			);
		}
		return sprintf(
			'<div class="dtq-post-btn-wrap"><a class="dtq-post-btn dtq-flex-base" href="%s">%s</a></div>',
			$permalink,
			esc_html( $button_text )
		);
	}

	/**
	 * Render the placeholder body inside an empty-thumb figure.
	 * Mirrors src/divi5/shared/post-elements.jsx <PlaceholderThumb />.
	 */
	protected static function el_placeholder_thumb() {
		$svg = function_exists( 'dtq_get_svg_placeholder_image_icon' ) ? dtq_get_svg_placeholder_image_icon() : '';
		return '<div class="dtq-thumb-placeholder" aria-hidden="true">' . $svg . '</div>';
	}

	/**
	 * Render the floating avatar block (layout 4 only — sits between thumb and content).
	 */
	protected static function el_floating_avatar( $show_avatar, $layout ) {
		if ( 'layout4' !== $layout || 'on' !== $show_avatar ) {
			return '';
		}
		$avatar_url = get_avatar_url( get_the_author_meta( 'ID' ), array( 'size' => 80 ) );
		if ( ! $avatar_url ) {
			return '';
		}
		return sprintf(
			'<div class="dtq-author-avatar"><img src="%s" alt="%s" /></div>',
			esc_url( $avatar_url ),
			esc_html( get_the_author() )
		);
	}

	/**
	 * Render the thumbnail figure (with overlay + categories inside, mirroring D4).
	 *
	 * Decision tree (mirrors JSX `thumbnail()` in post-elements.jsx):
	 *   show_thumb=off                       → ''
	 *   has post thumbnail                   → figure with featured image
	 *   no thumb + behavior=hide             → ''
	 *   no thumb + behavior=image + url      → figure with custom image
	 *   no thumb + behavior=image + no url   → fall through to placeholder
	 *   no thumb + behavior=placeholder      → figure.dtq-empty-thumb with placeholder body
	 *
	 * @param array $args {
	 *   @type int    $post_id
	 *   @type string $permalink
	 *   @type string $title
	 *   @type string $thumb_size
	 *   @type string $img_hover_style
	 *   @type string $show_thumb
	 *   @type string $layout              Unused for branching now (kept for callers).
	 *   @type string $cats_html
	 *   @type bool   $with_categories
	 *   @type string $no_thumb_behavior   'placeholder' | 'image' | 'hide'.
	 *   @type string $no_thumb_image      Custom fallback image URL.
	 * }
	 */
	protected static function el_thumbnail( array $args ) {
		$show_thumb        = $args['show_thumb'] ?? 'on';
		$post_id           = $args['post_id'];
		$permalink         = $args['permalink'];
		$title             = $args['title'];
		$thumb_size        = $args['thumb_size'] ?? 'full';
		$img_hover_style   = $args['img_hover_style'] ?? 'none';
		$with_categories   = ! empty( $args['with_categories'] );
		$cats_html         = $with_categories ? ( $args['cats_html'] ?? '' ) : '';
		$no_thumb_behavior = $args['no_thumb_behavior'] ?? 'placeholder';
		$no_thumb_image    = $args['no_thumb_image'] ?? '';

		if ( 'on' !== $show_thumb ) {
			return '';
		}
		$hover_class = 'none' !== $img_hover_style ? ' dtq-img-hover-' . esc_attr( $img_hover_style ) : '';

		// Real featured image present.
		if ( has_post_thumbnail( $post_id ) ) {
			return sprintf(
				'<figure class="dtq-post-thumb%s"><a href="%s"><div class="dtq-overlay"></div>%s</a>%s</figure>',
				esc_attr( $hover_class ),
				$permalink,
				get_the_post_thumbnail( $post_id, $thumb_size, array( 'alt' => $title ) ),
				$cats_html
			);
		}

		// No featured image — branch on user-selected fallback behavior.
		if ( 'hide' === $no_thumb_behavior ) {
			return '';
		}

		if ( 'image' === $no_thumb_behavior && '' !== $no_thumb_image ) {
			return sprintf(
				'<figure class="dtq-post-thumb%s"><a href="%s"><div class="dtq-overlay"></div><img src="%s" alt="%s" /></a>%s</figure>',
				esc_attr( $hover_class ),
				$permalink,
				esc_url( $no_thumb_image ),
				esc_attr( $title ),
				$cats_html
			);
		}

		// Default: placeholder (also fallback when behavior=image but URL is empty).
		return sprintf(
			'<figure class="dtq-post-thumb dtq-empty-thumb%s">%s%s</figure>',
			esc_attr( $hover_class ),
			self::el_placeholder_thumb(),
			$cats_html
		);
	}
}
