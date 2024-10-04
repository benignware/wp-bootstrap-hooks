<?php
namespace benignware\bootstrap_hooks\util\theme;

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