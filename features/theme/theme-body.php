<?php

namespace benignware\wp\bootstrap_hooks;

function theme_body($buffer) {
  $theme_json = _bootstrap_get_theme_json();
  $resolve_preset = _bootstrap_get_preset_resolver($theme_json);
  $background_color = query_object($theme_json, 'styles.color.background') ?: (
    get_background_color()
      ? '#' . get_background_color()
      : ''
  );
  $bg_color_value = $resolve_preset($background_color);

  $bs_theme = '';

  if ($bg_color_value) {
    $bg_brightness = intval(brightness($bg_color_value));
		
		$is_dark = $bg_brightness <= 80;

    if ($is_dark) {
      $bs_theme = 'dark';
    }
  }

  $bs_theme = apply_filters('bootstrap_theme', $bs_theme);

  if ($bs_theme) {
    $buffer = preg_replace('/<body([^>]+)/', sprintf('<body$1 data-bs-theme="%s"', $bs_theme), $buffer);
  }

  return $buffer;
}

function theme_shutdown() {
  $final = '';
  $levels = ob_get_level();

  for ($i = 0; $i < $levels; $i++) {
    $final .= ob_get_clean();
  }

  echo theme_body($final);
}

add_action('shutdown', 'benignware\wp\bootstrap_hooks\theme_shutdown', 0);

// Start capturing output
ob_start();