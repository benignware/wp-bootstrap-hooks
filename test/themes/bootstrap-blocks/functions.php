<?php


add_action( 'customize_register', '__return_true' );


add_action( 'wp_enqueue_scripts', function() {
	// wp_dequeue_style( 'wp-block-library' );
	// wp_dequeue_style( 'wp-block-library-theme' );
	// wp_dequeue_style( 'global-styles' );
}, 99999999 );

add_action( 'after_setup_theme', function() {   
	add_theme_support( 'bootstrap' );

	add_theme_support( 'editor-styles' );   
	
	add_editor_style( 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' );
} ); 

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'bootstrap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'bootstrap',
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	// Global
	// wp_enqueue_style(
	// 	'bootstrap-presets',
  //   add_query_arg('action', 'bootstrap_presets_css', admin_url( 'admin-ajax.php' )),
	// 	array(),
	// 	wp_get_theme()->get( 'Version' )
	// );
});
