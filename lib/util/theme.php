<?php
namespace benignware\bootstrap_hooks\util\theme;

use function benignware\bootstrap_hooks\util\object\query_object;

function get_theme_json() {
  $merged_data = \WP_Theme_JSON_Resolver::get_merged_data();
  if (!method_exists($merged_data, 'get_data')) {
    return null; // Return null if data cannot be retrieved
  }

  return $merged_data->get_data();
}

function get_theme_palette() {
  $theme_json = get_theme_json();
  $palette = query_object($theme_json, 'settings.color.palette');

  if (!$palette) {
    $palette_arr = get_theme_support('editor-color-palette');
	  $palette = $palette ? $palette[0] : null;
  }

  return $palette ? $palette : [];
}

function get_palette_color($color) {
  $color = parse_color_name($color);
  $palette = get_theme_palette();
  $matches = array_filter($palette, function($item) use ($color) {
    return $item['slug'] === $color || $item['color'] === $color;
  });

  return array_shift($matches);
}

function get_theme_css_var($input) {
  if (strpos($input, 'var:preset|') === 0) {
    // Remove the 'var:preset|' prefix
    $output = str_replace('var:preset|', '--wp--preset--', $input);
    
    // Replace any remaining '|' characters with double hyphens '--'
    $output = str_replace('|', '--', $output);
    
    // Wrap it in the CSS var() function
    return 'var(' . $output . ');';
  }

  return $input;
}

/**
 * Parse the color name from a WordPress CSS variable.
 *
 * @param string $css_variable The CSS variable string to parse.
 * @return string|null The extracted color name or null if not found.
 */
function parse_color_name($css_variable) {
  // Use a regular expression to match the color name.
  if (preg_match('/^var\(--wp--preset--color--([a-zA-Z0-9\-_]+)\)$/', $css_variable, $matches)) {
      return $matches[1]; // Return the captured color name.
  }
  
  return $css_variable; // Return the original input if the variable does not match.
}