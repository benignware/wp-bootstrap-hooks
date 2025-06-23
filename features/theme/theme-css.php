<?php

namespace benignware\wp\bootstrap_hooks;

function get_theme_palette_style($theme_json = null, $options = []) {
	$prefix = $options['prefix'] ?? '--bs-';

  $theme_json = $theme_json ?: get_theme_json();

	// Extract the text and background colors from the theme.json
	$bg_color = query_object($theme_json, 'styles.color.background') ?: (
		get_background_color()
			? '#' . get_background_color()
			: ''
	);
	
	$text_color = query_object($theme_json, 'styles.color.text') ?: (
		get_theme_mod('text_color', '#000000')
	);

	$bg_brightness = $bg_color ? (rgb($bg_color) ? brightness($bg_color) : 100) : 100;
	$text_brightness = $text_color ? (rgb($text_color) ? brightness($text_color) : 0) : 0;

	$contrast_light = $bg_brightness > $text_brightness ? $bg_color : $text_color;
	$contrast_light = rgb2hex(rgb($contrast_light));
	$contrast_dark = $bg_brightness < $text_brightness ? $bg_color : $text_color;
	$contrast_dark = rgb2hex(rgb($contrast_dark));


	// echo "Background brightness: $bg_brightness\n";
	// echo "Text brightness: $text_brightness\n";
	// echo "Contrast light: $contrast_light\n";
	// echo "Contrast dark: $contrast_dark\n";


  $palette = query_object($theme_json, 'settings.color.palette');

	
	$palette_vars = array_reduce($palette, function($acc, $color) use ($prefix, $contrast_light, $contrast_dark) {
		$color_value = $color['color'] ?? '';

		if (!$color_value) {
			return $acc;
		}


		[$r, $g, $b] = rgb($color_value);
		$hex = rgb2hex([$r, $g, $b]);
		$rgb = implode(', ', [$r, $g, $b]);

		$identifier = $prefix . $color['slug'];

		$acc[$identifier] = $hex;
		$acc["{$identifier}-rgb"] = $rgb;
		$acc["{$identifier}-r"] = $r;
		$acc["{$identifier}-g"] = $g;
		$acc["{$identifier}-b"] = $b;
		$acc["{$identifier}-hex"] = $hex;

		
		// Calculate contrast color
		$contrast_color = contrast_color($color_value, $contrast_light, $contrast_dark);
		[$cr, $cg, $cb] = rgb($contrast_color);
		$contrast_hex = rgb2hex([$cr, $cg, $cb]);
		$contrast_rgb = implode(', ', [$cr, $cg, $cb]);

		$acc["{$identifier}-contrast"] = $contrast_hex;
		$acc["{$identifier}-contrast-rgb"] = $contrast_rgb;
		$acc["{$identifier}-contrast-r"] = $cr;
		$acc["{$identifier}-contrast-g"] = $cg;
		$acc["{$identifier}-contrast-b"] = $cb;

		// Calculate shift color based on brightness
		$weight = 0.25;

		$shift_color = color_shift($color_value, $bg_brightness > $text_brightness ? -$weight : $weight);
		[$sr, $sg, $sb] = $shift_color;
		$shift_hex = rgb2hex([$sr, $sg, $sb]);
		$shift_rgb = implode(', ', [$sr, $sg, $sb]);
		
		$acc["{$identifier}-shift-25"] = $shift_hex;
		$acc["{$identifier}-shift-25-rgb"] = $shift_rgb;
		$acc["{$identifier}-shift-25-r"] = $sr;
		$acc["{$identifier}-shift-25-g"] = $sg;
		$acc["{$identifier}-shift-25-b"] = $sb;
		
		return $acc;
	}, []);

	return $palette_vars;
}

function get_theme_shadow_vars($theme_json = null) {
  $shadow_presets = query_object($theme_json, 'settings.shadow.presets');
	$shadow_vars = [];

	if ($shadow_presets) {
		$shadow_vars = array_reduce($shadow_presets, function($acc, $preset) {
			$slug = $preset['slug'];
			$acc["--bs-shadow-{$slug}"] = "var(--wp--preset--shadow--{$slug}, {$preset['shadow']})";
			return $acc;
		}, []);
	}

  return $shadow_vars;
}

function get_theme_css() {
  $is_editor = isset($_GET['is_editor']) ? stripslashes($_GET['is_editor']) == 1 : false;
	$body_selector = $is_editor ? '.editor-styles-wrapper' : 'body';
  $theme_json = get_theme_json();
  $background_color = query_object($theme_json, 'styles.color.background') ?: (
    get_background_color()
      ? '#' . get_background_color()
      : ''
  );

  $root_style = array_merge(
    [
      '--bs-body-bg' => $background_color,
      '--bs-body-bg-rgb' => implode(', ', rgb($background_color) ?: []),
    ],
    get_theme_style([
      '--bs-body-color' => 'styles.color.text',
			'--bs-border-radius' => 'styles.border.radius',
      '--bs-body-font-family' => 'styles.typography.fontFamily',
      '--bs-body-font-size' => 'styles.typography.fontSize',
      '--bs-body-font-weight' => 'styles.typography.fontWeight',
      '--bs-gutter-x' => 'styles.spacing.gutter.x',
      '--bs-gutter-y' => 'styles.spacing.gutter.y',
      '--bs-link-color' => 'styles.elements.link.color.text',
    ]),
    get_theme_palette_style(),
    get_theme_shadow_vars()
  );

	// $root_selector = ':root, [data-bs-theme=light]';
	$root_selector = $body_selector;
  
  $rules = [
    $root_selector => $root_style,
		// ":root .btn:where(.wp-element-button, .wp-block-button__link)" => [
		// 	'background-color' => 'var(--bs-btn-bg)',
		// ],
		// ":root .btn:where(.wp-block-button__link)" => [
		// 	'color' => 'var(--bs-btn-color)',
		// ],
    // '.btn:not(.wp-element-button), .wp-element-button' => get_theme_style([
    //   '--bs-btn-border-radius' => 'styles.elements-button.border.radius',
    //   // '--bs-btn-padding-x' => 'styles.elements.button.spacing.padding.left',
    //   // '--bs-btn-padding-y' => 'styles.elements.button.spacing.padding.top',
    //   // '--bs-btn-font-size' => 'styles.elements.button.typography.fontSize',
    //   '--bs-btn-font-weight' => 'styles.elements.button.typography.fontWeight',
    //   'text-transform' => 'styles.elements.button.typography.textTransform',
    //   'letter-spacing' => 'styles.elements.button.typography.letterSpacing',
    // ]),
		':root .btn:not(.btn-link):where(.wp-block-button, .wp-block-button__link)' => array_merge(
			get_theme_style([
				'--bs-btn-border-radius' => 'styles.blocks.core/button.border.radius',
				'--bs-btn-font-weight' => 'styles.blocks.core/button.typography.fontWeight',
				'--bs-btn-font-family' => 'styles.blocks.core/button.typography.fontFamily',
				'text-transform' => 'styles.blocks.core/button.typography.textTransform',
				'letter-spacing' => 'styles.blocks.core/button.typography.letterSpacing',
				'--bs-btn-color' => 'styles.blocks.core/button.color.text',
				'--bs-btn-hover-color' => 'styles.blocks.core/button.color.text',
			]),
			[
				'background-color' => 'var(--bs-btn-bg)',
				'color' => 'var(--bs-btn-color)',
				'border-color' => 'var(--bs-btn-border-color)',
				'border-radius' => 'var(--bs-btn-border-radius)',
				'font-family' => 'var(--bs-btn-font-family)',
				'font-size' => 'var(--bs-btn-font-size)',
				'font-weight' => 'var(--bs-btn-font-weight)',
				'padding-left' => 'var(--bs-btn-padding-x)',
				'padding-right' => 'var(--bs-btn-padding-x)',
				'padding-top' => 'var(--bs-btn-padding-y)',
				'padding-bottom' => 'var(--bs-btn-padding-y)',
			]
		),
		':root .btn:not(.btn-sm):not(.btn-lg):where(.wp-block-button, .wp-block-button__link)'  => get_theme_style([
			'--bs-btn-padding-x' => 'styles.blocks.core/button.spacing.padding.left',
      '--bs-btn-padding-y' => 'styles.blocks.core/button.spacing.padding.top',
      '--bs-btn-font-size' => 'styles.blocks.core/button.typography.fontSize',
		]),
		'.btn.btn-primary:not(.wp-block-button)' => get_theme_style([
			'--bs-btn-color' => 'styles.elements.button.color.text',
			'--bs-btn-hover-color' => 'styles.elements.button.color.text',
			'--bs-btn-active-color' => 'styles.elements.button.color.text',
		]),
		'.btn.btn-outline-primary:not(.wp-block-button)' => get_theme_style([
			'--bs-btn-color' => 'styles.elements.button.color.background',
			'--bs-btn-hover-color' => 'styles.elements.button.color.text',
			'--bs-btn-active-color' => 'styles.elements.button.color.text',
		]),
    ".container" => [
			'max-width' => 'var(--wp--style--global--wide-size, 1200px)',
		],
		".container-fluid.alignfull" => [
			'margin-left' => 'auto',
			'margin-right' => 'auto',
			'padding-left' => 'var(--wp--style--root--padding-left)',
			'padding-right' => 'var(--wp--style--root--padding-right)',
			'width' => 'auto'
		],
		"figure.alignfull,figure.alignwide" => [
			'display' => 'block',
			'max-width' => 'none'
		],
		".wp-site-blocks" => [
			'display' => 'flex',
			'flex-direction' => 'column',
			'flex-grow' => '1'
		],
		".card-img-top:not(img)" => [
			'overflow' => 'clip'
		],
		".navbar-brand" => [
			'position' => 'relative'
		],
		".navbar-brand a" => [
			'text-decoration' => 'inherit',
      'color' => 'inherit'
		],
		".navbar-brand a:after" => [
			'position' => 'absolute',
			'top' =>  '0',
			'left' => '0',
			'bottom' => '0',
			'right' => '0',
			'content' => '\'\'',
			'z-index' => '3'
		],
		".navbar-brand a:hover,.navbar-brand a:focus" => [
			'color' => 'inherit'
		],
		":root h1:where(.wp-block-heading)" => get_theme_style([
			'font-family' => 'styles.elements.h1.typography.fontFamily',
			'font-weight' => 'styles.elements.h1.typography.fontWeight',
		]),
		":root h2:where(.wp-block-heading)" => get_theme_style([
			'font-family' => 'styles.elements.h2.typography.fontFamily',
			'font-weight' => 'styles.elements.h2.typography.fontWeight',
		]),
  ];

	$rules = apply_filters('bootstrap_theme_css_rules', $rules);

  $css = get_theme_css_from_rules($rules);
  
  return $css;
}

function theme_css_action() {
  header('Content-Type: text/css');
	// No cache
	header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
	header('Pragma: no-cache'); // HTTP 1.0.
	header('Expires: 0'); // Proxies.

  echo get_theme_css();
  die();
}
add_action('wp_ajax_nopriv_bootstrap_presets_css', 'benignware\wp\bootstrap_hooks\theme_css_action');
add_action('wp_ajax_bootstrap_presets_css', 'benignware\wp\bootstrap_hooks\theme_css_action');

function enqueue_theme_block_assets() {
	$url = add_query_arg('action', 'bootstrap_presets_css', admin_url( 'admin-ajax.php' ));
	$rnd = rand(0, 9999);
	$url = add_query_arg('ver', $rnd, $url);
	
	if (is_block_editor()) {
		$url = add_query_arg('is_editor', '1', $url);

    wp_enqueue_script( 'bootstrap-editor',  plugin_dir_url( dirname(__FILE__) ) . '..//assets/editor.js' );
	}

	wp_register_style('bootstrap-global-styles', $url);
	wp_enqueue_style('bootstrap-global-styles');
}
add_action( 'enqueue_block_assets', 'benignware\wp\bootstrap_hooks\enqueue_theme_block_assets', 10 );
