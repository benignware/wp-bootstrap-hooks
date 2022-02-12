<?php

/**
 * Prints HTML with meta information about theme author.
 *
 * @since 1.0.0
 *
 * @return void
 */

function bs_posted_by() {
	if ( ! get_the_author_meta( 'description' ) && post_type_supports( get_post_type(), 'author' ) ) {
		echo '<span class="byline">';
		printf(
			/* translators: %s author name. */
			esc_html__( 'By %s', 'twentytwentyone' ),
			'<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" rel="author">' . esc_html( get_the_author() ) . '</a>'
		);
		echo '</span>';
	}
}