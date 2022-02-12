<?php

/**
 * Determines if post thumbnail can be displayed.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function bs_can_show_post_thumbnail() {
	return apply_filters(
		'bs_can_show_post_thumbnail',
		! post_password_required() && ! is_attachment() && has_post_thumbnail()
	);
}
