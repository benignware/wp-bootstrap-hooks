<?php

namespace benignware\wp\bootstrap_hooks;

function adminbar_sticky_adjust() {
	if (!is_admin_bar_showing()) {
		return;
	}

	$custom_css = <<<EOT
	@media screen and (min-width: 601px) {
		.sticky-top {
			top: var(--wp-admin--admin-bar--height, 0);
		}
	}

	body {
		min-height: calc(100vh - var(--wp-admin--admin-bar--height, 0));
	}

	.modal {
		z-index: 10000;
	}
	EOT;

	wp_register_style( 'bootstrap-admin-bar', false,  );
	wp_enqueue_style( 'bootstrap-admin-bar' );
	wp_add_inline_style( 'bootstrap-admin-bar', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'benignware\wp\bootstrap_hooks\adminbar_sticky_adjust' );