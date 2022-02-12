<?php

if ( ! function_exists( 'twenty_twenty_one_post_thumbnail' ) ) {
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function twenty_twenty_one_post_thumbnail() {
		if ( ! twenty_twenty_one_can_show_post_thumbnail() ) {
			return;
		}
		?>

		<?php if ( is_singular() ) : ?>

			<figure class="post-thumbnail">
				<?php
				// Thumbnail is loaded eagerly because it's going to be in the viewport immediately.
				the_post_thumbnail( 'post-thumbnail', array( 'loading' => 'eager' ) );
				?>
				<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
					<figcaption class="wp-caption-text"><?php echo wp_kses_post( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
				<?php endif; ?>
			</figure><!-- .post-thumbnail -->

		<?php else : ?>

			<figure class="post-thumbnail">
				<a class="post-thumbnail-inner alignwide" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
					<?php the_post_thumbnail( 'post-thumbnail' ); ?>
				</a>
				<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
					<figcaption class="wp-caption-text"><?php echo wp_kses_post( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
				<?php endif; ?>
			</figure>

		<?php endif; ?>
		<?php
	}
}

if ( ! function_exists( 'twenty_twenty_one_the_posts_navigation' ) ) {
	/**
	 * Print the next and previous posts navigation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function twenty_twenty_one_the_posts_navigation() {
		$post_type      = get_post_type_object( get_post_type() );
		$post_type_name = '';
		if (
			is_object( $post_type ) &&
			property_exists( $post_type, 'labels' ) &&
			is_object( $post_type->labels ) &&
			property_exists( $post_type->labels, 'name' )
		) {
			$post_type_name = $post_type->labels->name;
		}

		the_posts_pagination(
			array(
				/* translators: There is a space after page. */
				'before_page_number' => esc_html__( 'Page ', 'twentytwentyone' ),
				'mid_size'           => 0,
				'prev_text'          => sprintf(
					'%s <span class="nav-prev-text">%s</span>',
					is_rtl() ? twenty_twenty_one_get_icon_svg( 'ui', 'arrow_right' ) : twenty_twenty_one_get_icon_svg( 'ui', 'arrow_left' ),
					sprintf(
						/* translators: %s: The post-type name. */
						esc_html__( 'Newer %s', 'twentytwentyone' ),
						'<span class="nav-short">' . esc_html( $post_type_name ) . '</span>'
					)
				),
				'next_text'          => sprintf(
					'<span class="nav-next-text">%s</span> %s',
					sprintf(
						/* translators: %s: The post-type name. */
						esc_html__( 'Older %s', 'twentytwentyone' ),
						'<span class="nav-short">' . esc_html( $post_type_name ) . '</span>'
					),
					is_rtl() ? twenty_twenty_one_get_icon_svg( 'ui', 'arrow_left' ) : twenty_twenty_one_get_icon_svg( 'ui', 'arrow_right' )
				),
			)
		);
	}
}