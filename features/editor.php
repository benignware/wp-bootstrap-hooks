<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'enqueue_block_editor_assets', function() {
  wp_enqueue_style( 'bootstrap-editor-css', plugins_url( 'dist/bootstrap-editor.css', dirname( __FILE__ ) ), false, '1.0', 'all' );
});

add_filter( 'block_categories', function($categories, $post) {
	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'bootstrap',
				'title' => __( 'Bootstrap', 'bootstrap-hooks' ),
			),
		)
	);
}, 10, 2);

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */


// Hook: Block assets.
add_action( 'init', function() { // phpcs:ignore
	// Register block styles for both frontend + backend.
	wp_register_style(
		'bootstrap-hooks-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		array( 'wp-editor' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);

	// Register block editor script for backend.
	wp_register_script(
		'bootstrap-hooks-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
		null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
    'bootstrap-hooks-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	);

	/**
	 * Register Gutenberg block on server-side.
	 *
	 * Register the block on server-side to ensure that the block
	 * scripts and styles for both frontend and backend are
	 * enqueued when the editor loads.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @since 1.16.0
	 */
	register_block_type(
		'bootstrap-hooks/card', array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			'style'         => 'bootstrap-hooks-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'bootstrap-hooks-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'bootstrap-hooks-editor-css',
		)
	);

  register_block_type(
		'bootstrap-hooks/grid', array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			'style'         => 'bootstrap-hooks-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'bootstrap-hooks-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'bootstrap-hooks-editor-css',
		)
	);

	register_block_type(
		'bootstrap-hooks/button', array(
      // Enqueue blocks.style.build.css on both frontend & backend.
			'style'         => 'bootstrap-hooks-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'bootstrap-hooks-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'bootstrap-hooks-editor-css',
		)
	);

	wp_localize_script( 'bootstrap-hooks-js', 'BootstrapHooks',
    array(
			'data' => json_encode(array(
				'options' => wp_bootstrap_options()
			))
    )
  );
} );


add_action( 'after_setup_theme', function() {
	add_theme_support(
		'editor-color-palette', array(
			array(
				'name'  => esc_html__( 'Primary', '@@textdomain' ),
				'slug' => 'primary',
				'color' => 'var(--primary)',
			),
			array(
				'name'  => esc_html__( 'Secondary', '@@textdomain' ),
				'slug' => 'secondary',
				'color' => 'var(--secondary)',
			),
			array(
				'name'  => esc_html__( 'Success', '@@textdomain' ),
				'slug' => 'success',
				'color' => 'var(--success)',
			),
			array(
				'name'  => esc_html__( 'Danger', '@@textdomain' ),
				'slug' => 'danger',
				'color' => 'var(--danger)',
			),
			array(
				'name'  => esc_html__( 'Warning', '@@textdomain' ),
				'slug' => 'warning',
				'color' => 'var(--warning)',
			),
			array(
				'name'  => esc_html__( 'Info', '@@textdomain' ),
				'slug' => 'info',
				'color' => 'var(--info)',
			),
			array(
				'name'  => esc_html__( 'Light', '@@textdomain' ),
				'slug' => 'light',
				'color' => 'var(--light)',
			),
			array(
				'name'  => esc_html__( 'Dark', '@@textdomain' ),
				'slug' => 'dark',
				'color' => 'var(--dark)',
			)
		)
	);
});
