<?php

namespace benignware\wp\bootstrap_hooks;

function get_theme_palette_style($theme_json = null) {
  $theme_json = $theme_json ?: get_theme_json();
  $palette = query_object($theme_json, 'settings.color.palette');

	$palette_vars = array_reduce($palette, function($acc, $color) {
		[$r, $g, $b] = rgb($color['color']);
		$hex = rgb2hex([$r, $g, $b]);
		$rgb = implode(', ', [$r, $g, $b]);


		$acc["--bs-{$color['slug']}"] = $hex;
		$acc["--bs-{$color['slug']}-rgb"] = $rgb;
		$acc["--bs-{$color['slug']}-r"] = $r;
		$acc["--bs-{$color['slug']}-g"] = $g;
		$acc["--bs-{$color['slug']}-b"] = $b;
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
  
  $rules = [
    $body_selector => $root_style,
    '.btn, .wp-element-button' => get_theme_style([
      '--bs-btn-border-radius' => 'styles.elements-button.border.radius',
      '--bs-btn-padding-x' => 'styles.elements.button.spacing.padding.left',
      '--bs-btn-padding-y' => 'styles.elements.button.spacing.padding.top',
      '--bs-btn-font-size' => 'styles.elements.button.typography.fontSize',
      '--bs-btn-font-weight' => 'styles.elements.button.typography.fontWeight',
      'text-transform' => 'styles.elements.button.typography.textTransform',
      'letter-spacing' => 'styles.elements.button.typography.letterSpacing',
    ]),
    '.btn.wp-block-button, .wp-block-buttons .btn' => get_theme_style([
      '--bs-btn-border-radius' => 'styles.blocks.core/button.border.radius',
      '--bs-btn-padding-x' => 'styles.blocks.core/button.spacing.padding.left',
      '--bs-btn-padding-y' => 'styles.blocks.core/button.spacing.padding.top',
      '--bs-btn-font-size' => 'styles.blocks.core/button.typography.fontSize',
      '--bs-btn-font-weight' => 'styles.blocks.core/button.typography.fontWeight',
      'text-transform' => 'styles.blocks.core/button.typography.textTransform',
      'letter-spacing' => 'styles.blocks.core/button.typography.letterSpacing',
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
  ];
  $css = get_theme_css_from_rules($rules);
  
  return $css;
}

function theme_css_action() {
  header('Content-Type: text/css');
  echo get_theme_css();
  die();
}
add_action('wp_ajax_nopriv_bootstrap_presets_css', 'benignware\wp\bootstrap_hooks\theme_css_action');
add_action('wp_ajax_bootstrap_presets_css', 'benignware\wp\bootstrap_hooks\theme_css_action');

function enqueue_theme_block_assets() {
	$url = add_query_arg('action', 'bootstrap_presets_css', admin_url( 'admin-ajax.php' ));
	
	if (is_block_editor()) {
		$url = add_query_arg('is_editor', '1', $url);

    wp_enqueue_script( 'bootstrap-editor',  plugin_dir_url( dirname(__FILE__) ) . '..//assets/editor.js' );
	}

	wp_register_style('bootstrap-global-styles', $url);
	wp_enqueue_style('bootstrap-global-styles');
}
add_action( 'enqueue_block_assets', 'benignware\wp\bootstrap_hooks\enqueue_theme_block_assets' );
