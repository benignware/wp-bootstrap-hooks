<?php

use function benignware\bootstrap_hooks\util\colors\rgb;
use function benignware\bootstrap_hooks\util\object\query_object;
use function benignware\bootstrap_hooks\util\dom\find_by_class;

add_action( 'customize_register', '__return_true' );

//Remove Gutenberg Block Library CSS from loading on the frontend

add_action( 'wp_enqueue_scripts', function() {
	// wp_dequeue_style( 'wp-block-library' );
	// wp_dequeue_style( 'wp-block-library-theme' );
	// wp_dequeue_style( 'global-styles' );
}, 99999999 );

add_action( 'after_setup_theme', function() {   
	add_theme_support( 'bootstrap' );
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

	// Global
	wp_enqueue_style(
		'bootstrap-presets',
    add_query_arg('action', 'bootstrap_presets_css', admin_url( 'admin-ajax.php' )),
		array(),
		wp_get_theme()->get( 'Version' )
	);
});


 add_action( 'after_setup_theme', function() {   
	add_theme_support( 'editor-styles' );   
	add_editor_style( 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' );
	
	$url = add_query_arg('action', 'bootstrap_presets_css', admin_url( 'admin-ajax.php' ));
	add_editor_style($url);
 } ); 

 /**
* Add color styling from settings
* Inserted with an enqueued CSS file
*/

function _bootstrap_presets_css_action() {
	$is_editor = stripslashes($_GET['is_editor']) == 1;
	$body_selector = $is_editor ? '.editor-styles-wrapper' : 'body';
	$theme_json = WP_Theme_JSON_Resolver::get_merged_data()->get_data();
	$default_theme_json = json_decode(file_get_contents(ABSPATH . WPINC . '/theme.json'), true);
	
	$theme_json = array_merge(
		$default_theme_json,
		$theme_json,
		[
			'settings' => array_merge(
				$default_theme_json['settings'],
				query_object($theme_json, 'settings') ?: [],
				[
					'color' => array_merge(
						$default_theme_json['settings']['color'],
						query_object($theme_json, 'settings.color') ?: [],
						[
							'palette' => query_object($default_theme_json, 'settings.color.defaultPalette')
								? array_merge(
									query_object($default_theme_json, 'settings.color.palette') ?: [],
									query_object($theme_json, 'settings.color.palette') ?: []
								)
								: query_object($theme_json, 'settings.color.palette')
						]
					),
					'typography' => array_merge(
						query_object($default_theme_json, 'settings.typography') ?: [],
						query_object($theme_json, 'settings.typography') ?: [],
						[
							'fontSizes' => !empty(query_object($theme_json, 'settings.typography.fontSizes'))
								? query_object($theme_json, 'settings.typography.fontSizes')
								: query_object($default_theme_json, 'settings.typography.fontSizes')
						]
					)
				]
			)
		]
	);

	// echo '<pre>';
	// print_r($theme_json);
	// echo '</pre>';
	// exit;

	$resolve_preset = function($value) use ($theme_json) {
		if (preg_match('/var\(\s*--wp--preset--([\w+-]+)--([\w+-]+)\s*\)/', $value, $matches)) {
			$type = $matches[1];
			$slug = $matches[2];

			if ($type === 'font-family') {
				$font_families = query_object($theme_json, 'settings.typography.fontFamilies');

				if ($font_families) {
					$font_family = current(array_values(array_filter($font_families, function($obj) use ($slug) {
						return $obj['slug'] === $slug;
					})));

					if ($font_family) {
						return $font_family['fontFamily'];
					}
				}
			}

			if ($type === 'font-size') {
				$font_sizes = query_object($theme_json, 'settings.typography.fontSizes');

				if ($font_sizes) {
					$font_size = current(array_values(array_filter($font_sizes, function($obj) use ($slug) {
						return $obj['slug'] === $slug;
					})));

					if ($font_size) {
						return $font_size['size'];
					}
				}
			}

			if ($type === 'color') {
				$palette = query_object($theme_json, 'settings.color.palette');

				if ($palette) {
					$color = current(array_values(array_filter($palette, function($obj) use ($slug) {
						return $obj['slug'] === $slug;
					})));

					if ($color) {
						return $color['color'];
					}
				}
			}
		}

		return $value;
	};

	// $fontFamily = $resolve_preset(query_object($theme_json, 'styles.typography.fontFamily'));

	// echo $fontFamily;
	// echo '<br/>';

	$css = [
		$body_selector => [
			// '--bs-body-bg' => $resolve_preset(query_object($theme_json, 'styles.color.background')),
			// '--bs-body-color' => $resolve_preset(query_object($theme_json, 'styles.color.text')),
			// '--bs-body-font-family' => $resolve_preset(query_object($theme_json, 'styles.typography.fontFamily')),
			// '--bs-body-font-size' => $resolve_preset(query_object($theme_json, 'styles.typography.fontSize')),
			// '--bs-body-font-weight' => $resolve_preset(query_object($theme_json, 'styles.typography.fontWeight'))
			'--bs-body-bg' => query_object($theme_json, 'styles.color.background'),
			'--bs-body-color' => query_object($theme_json, 'styles.color.text'),
			'--bs-body-font-family' => query_object($theme_json, 'styles.typography.fontFamily'),
			'--bs-body-font-size' => query_object($theme_json, 'styles.typography.fontSize'),
			'--bs-body-font-weight' => query_object($theme_json, 'styles.typography.fontWeight')
		],
		"$body_selector a" => [
			'--bs-link-color-rgb' => implode(', ', rgb($resolve_preset(query_object($theme_json, 'styles.elements.link.color.text'))) ?: []),
			'--bs-link-hover-color-rgb' => implode(', ', rgb($resolve_preset(query_object($theme_json, 'styles.elements.link.:hover.color.text'))) ?: [])
		],
		"$body_selector .is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull))" => [
			'max-width' => $resolve_preset(query_object($theme_json, 'settings.layout.contentSize')),
			'margin-left' => 'auto',
			'margin-right' => 'auto'
		],
		// "container" => [
		// 	'padding-left' => query_object($theme_json, 'styles.spacing.padding.left'),
		// 	'padding-right' => query_object($theme_json, 'styles.spacing.padding.right')
		// ],
		"figure.alignfull,figure.alignwide" => [
			'display' => 'block',
			'max-width' => 'none'
		],
		// ".wp-block-image.alignfull img, .wp-block-image.alignwide img" => [
		// 	'width' => '100%',
		// 	'height' => 'auto'
		// ]
		".wp-site-blocks" => [
			'min-height' => '100vh',
			'display' => 'flex',
			'flex-direction' => 'column'
		],
		".card-img-top:not(img)" => [
			'overflow' => 'clip'
		]
	];


	$css = implode("\n", array_map(function($selector, $properties) {
		$properties = array_filter($properties);
		$prop_css = implode("\n", array_map(function($prop, $value) {
			return "\t$prop: $value;";
		}, array_keys($properties), array_values($properties)));

		return sprintf("%s {\n%s\n}\n", $selector, $prop_css);
	}, array_keys($css), array_values($css)));

	header('Content-Type: text/css');
	echo $css;
	exit;
}

add_action('wp_ajax_nopriv_bootstrap_presets_css', '_bootstrap_presets_css_action');
add_action('wp_ajax_bootstrap_presets_css', '_bootstrap_presets_css_action');

add_action( 'wp_enqueue_scripts', function() {
	

	// wp_register_style( 'bootstrap-blocks', false );
	// wp_enqueue_style( 'bootstrap-blocks' );
	// wp_add_inline_style( 'bootstrap-blocks', $custom_css );
} );

add_action( 'enqueue_block_editor_assets', function() {
	$url = add_query_arg('action', 'bootstrap_presets_css', admin_url( 'admin-ajax.php' ));
	$url = add_query_arg('is_editor', '1', $url);

  wp_register_style('bootstrap-presets', $url);
  wp_enqueue_style('bootstrap-presets');

	wp_enqueue_script('bootstrap-editor', get_template_directory_uri() . '/assets/editor.js');
});


// add_filter('the_content', function($content) {
// 	return 'CONTENT';
// });

// add_filter( 'template_include', function($template) {
// 	echo $template;
// 	exit;
// } );

function callback($buffer) {
	$doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $buffer);
  $doc_xpath = new DOMXpath($doc);

	$wrapper = find_by_class($doc->documentElement, 'wp-site-blocks');

	// echo 'TEST';
	// exit;

	if ($wrapper) {

		$wrapper->setAttribute('data-bs-theme', 'dark');
		

		$buffer = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

		return $buffer;
	}

  // modify buffer here, and then return the updated code
  return $buffer;
}

function buffer_start() { ob_start("callback"); }

function buffer_end() { ob_end_flush(); }

add_action('wp_head', 'buffer_start');
add_action('wp_footer', 'buffer_end');