<?php



if ( ! function_exists( 'emptytheme_support' ) ) :
	function emptytheme_support()  {

		// Adding support for core block visual styles.
		add_theme_support( 'wp-block-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style.css' );
	}
	add_action( 'after_setup_theme', 'emptytheme_support' );
endif;

/**
 * Enqueue scripts and styles.
 */
function emptytheme_scripts() {
	// Enqueue theme stylesheet.
	wp_enqueue_style( 'emptytheme-style', get_template_directory_uri() . '/style.css', array(), wp_get_theme()->get( 'Version' ) );
}

add_action( 'wp_enqueue_scripts', 'emptytheme_scripts' );



/**
 * Get Bootstrap from cdn
*/

add_action('wp_enqueue_scripts', function() {
	wp_register_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' );
	wp_enqueue_style( 'bootstrap' );
	
	// wp_register_script( 'bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', array( 'jquery' ), null, true );
	// wp_enqueue_script( 'bootstrap' );
	
	// wp_register_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ), null, true );
	// wp_enqueue_script( 'popper' );
}, 9999);

/**
 * Init Bootstrap Hooks
 */

if (function_exists( 'wp_bootstrap_hooks' )) {
	wp_bootstrap_hooks();
}


// Add block patterns
require get_template_directory() . '/inc/block-patterns.php';


// Drop the below in theme functions.php.

add_action( 'after_setup_theme', function() {
	remove_theme_support( 'block-templates' );
} );
