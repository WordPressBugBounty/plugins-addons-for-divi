<?php
/**
 * PostCarousel — Per-layout card assemblers (server-side mirror of layouts.jsx).
 *
 * Each render_layout_N() method composes pre-rendered element strings into
 * the final card HTML for one layout.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\PostCarousel\PostCarouselTrait;

trait PostLayoutsTrait {

	/**
	 * Render a single post card for the given layout.
	 *
	 * @param string $layout Layout key (layout1..layout5).
	 * @param array  $els    Pre-rendered element HTML keyed by element name.
	 * @return string
	 */
	protected static function render_layout_card( $layout, array $els ) {
		switch ( $layout ) {
			case 'layout1':
				return self::render_layout_1( $els );
			case 'layout2':
				return self::render_layout_2( $els );
			case 'layout3':
				return self::render_layout_3( $els );
			case 'layout5':
				return self::render_layout_5( $els );
			case 'layout6':
				return self::render_layout_6( $els );
			case 'layout4':
			default:
				return self::render_layout_4( $els );
		}
	}

	/** Layout 6 — Horizontal (image left, content right). */
	protected static function render_layout_6( array $els ) {
		$separator = ( '' !== $els['author'] && '' !== $els['date'] )
			? '<span class="dtq-meta-sep" aria-hidden="true">&middot;</span>'
			: '';

		return sprintf(
			'<article class="dtq-post-card dtq-post-card-6">%s<div class="dtq-blog-content">%s%s<div class="dtq-blog-meta">%s%s%s</div>%s%s</div></article>',
			$els['thumbnail_no_cats'],
			$els['cats_html'],
			$els['title'],
			$els['author'],
			$separator,
			$els['date'],
			$els['excerpt'],
			$els['button']
		);
	}

	/** Layout 1 — Classic. */
	protected static function render_layout_1( array $els ) {
		return sprintf(
			'<article class="dtq-post-card dtq-post-card-1">%s%s<div class="dtq-blog-content">%s%s%s%s%s</div></article>',
			$els['thumbnail'],
			$els['standalone_categories'],
			$els['author'],
			$els['title'],
			$els['excerpt'],
			$els['button'],
			$els['date']
		);
	}

	/** Layout 2 — Stacked Meta. */
	protected static function render_layout_2( array $els ) {
		return sprintf(
			'<article class="dtq-post-card dtq-post-card-2">%s%s<div class="dtq-blog-content">%s<div class="dtq-blog-meta">%s%s</div>%s%s</div></article>',
			$els['thumbnail'],
			$els['standalone_categories'],
			$els['title'],
			$els['author'],
			$els['date'],
			$els['excerpt'],
			$els['button']
		);
	}

	/** Layout 3 — Overlay (categories inside content; thumb is its own simpler figure). */
	protected static function render_layout_3( array $els ) {
		return sprintf(
			'<article class="dtq-post-card dtq-post-card-3">%s<div class="dtq-blog-content">%s%s<div class="dtq-blog-meta">%s%s</div>%s%s</div></article>',
			$els['layout3_thumb'],
			$els['cats_html'],
			$els['title'],
			$els['author'],
			$els['date'],
			$els['excerpt'],
			$els['button']
		);
	}

	/** Layout 4 — Avatar Above (separate avatar block between thumb and content). */
	protected static function render_layout_4( array $els ) {
		return sprintf(
			'<article class="dtq-post-card dtq-post-card-4">%s%s%s<div class="dtq-blog-content">%s<div class="dtq-blog-meta">%s%s</div>%s</div></article>',
			$els['thumbnail'],
			$els['standalone_categories'],
			$els['floating_avatar'],
			$els['title'],
			$els['author'],
			$els['date'],
			$els['excerpt']
		);
	}

	/** Layout 5 — Spotlight (Modern). */
	protected static function render_layout_5( array $els ) {
		// Bullet separator only when both author and date are present.
		$separator = ( '' !== $els['author'] && '' !== $els['date'] )
			? '<span class="dtq-meta-sep" aria-hidden="true">&middot;</span>'
			: '';

		return sprintf(
			'<article class="dtq-post-card dtq-post-card-5">%s<div class="dtq-blog-content">%s%s%s<div class="dtq-blog-meta">%s%s%s</div>%s</div></article>',
			$els['thumbnail_no_cats'],
			$els['cats_html'],
			$els['title'],
			$els['excerpt'],
			$els['author'],
			$separator,
			$els['date'],
			$els['button']
		);
	}
}
