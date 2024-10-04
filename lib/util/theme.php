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
