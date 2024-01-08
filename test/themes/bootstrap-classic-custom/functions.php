<?php

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'bootstrap',
    get_stylesheet_directory_uri() . '/public/bootstrap.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'bootstrap',
		get_stylesheet_directory_uri() . '/public/bootstrap.js',
		array(),
		wp_get_theme()->get( 'Version' )
	);

  wp_enqueue_style(
		'bootstrap-classic-custom',
    get_stylesheet_directory_uri() . '/style.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
});

add_filter('bootstrap_theme', function() {
	return 'dark';
});
