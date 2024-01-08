<?php

add_action( 'after_setup_theme', function() {   
  add_theme_support( 'bootstrap' );

	add_theme_support( 'custom-background' );

	// Editor Theme Palette
	add_theme_support( 'editor-color-palette', array(
		array(
			'name'  => esc_attr__( 'Primary', 'bootstrap-classic' ),
			'slug'  => 'primary',
			'color' => get_theme_mod('primary', '#0d6efd')
		),
		array(
			'name'  => esc_attr__( 'Secondary', 'bootstrap-classic' ),
			'slug'  => 'secondary',
			'color' => get_theme_mod('secondary', '#6c757d'),
		),
		array(
			'name'  => esc_attr__( 'Success', 'bootstrap-classic' ),
			'slug'  => 'success',
			'color' => get_theme_mod('success', '#198754'),
		),
		array(
			'name'  => esc_attr__( 'Info', 'bootstrap-classic' ),
			'slug'  => 'info',
			'color' => get_theme_mod('info', '#0dcaf0'),
		),
		array(
			'name'  => esc_attr__( 'Warning', 'bootstrap-classic' ),
			'slug'  => 'warning',
			'color' => get_theme_mod('warning', '#ffc107'),
		),
		array(
			'name'  => esc_attr__( 'Danger', 'bootstrap-classic' ),
			'slug'  => 'danger',
			'color' => get_theme_mod('danger', '#dc3545'),
		),
		array(
			'name'  => esc_attr__( 'Light', 'bootstrap-classic' ),
			'slug'  => 'light',
			'color' => get_theme_mod('light', '#f8f9fa'),
		),
		array(
			'name'  => esc_attr__( 'Dark', 'bootstrap-classic' ),
			'slug'  => 'dark',
			'color' => get_theme_mod('dark', '#212529'),
		),
	));

  register_nav_menus(
    array(
      'primary' => __( 'Primary', 'twentynineteen' ),
      'footer' => __( 'Footer Menu', 'twentynineteen' ),
      'social' => __( 'Social Links Menu', 'twentynineteen' ),
    )
  );
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'bootstrap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'bootstrap',
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
		array(),
		wp_get_theme()->get( 'Version' )
	);

  wp_enqueue_style(
		'bootstrap-classic',
    get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
});


// excerpt_more should be set the empty.
add_filter( 'excerpt_more', '__return_empty_string', 21 );


add_filter( 'the_excerpt', function ( $excerpt ) {
	$excerpt .= sprintf( 
					'<a class="btn btn-primary" href="%s">%s</a>',
					esc_url( get_permalink() ),
					__( 'Read more' )
	);
	return $excerpt;
}, 21 );