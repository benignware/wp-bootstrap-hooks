<?php

namespace benignware\wp\bootstrap_hooks;

function the_content_embeds($content) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  $options = wp_bootstrap_options();

  extract($options);

  // Parse DOM
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);

    // Embeds

  // Get ratio items and sort by their ratios
  $embed_preset_ratios_sorted = [];
  
  foreach ($embed_preset_ratios as $preset_ratio_string) {
    $preset_dimensions = explode(':', $preset_ratio_string);
    $preset_width = intval($preset_dimensions[0]);
    $preset_height = intval($preset_dimensions[1]);
    $preset_ratio = intval($preset_height / $preset_width * 100);
    $embed_preset_ratios_sorted[] = array(
      'string' => $preset_ratio_string,
      'width' => $preset_width,
      'height' => $preset_height,
      'ratio' => $preset_ratio,
      'orientation' => $preset_width < $preset_height ? 'portrait' : 'landscape'
    );
  }
  
  usort($embed_preset_ratios_sorted, function($a, $b) {
    return $a["ratio"] > $b["ratio"] ? 1 : ($a["ratio"] < $b["ratio"] ? -1 : 0);
  });

  $iframe_elements = $doc->getElementsByTagName( 'iframe' );
  
  foreach ($iframe_elements as $iframe_element) {
    // Adjust class
    add_class($iframe_element, $embed_class);

    // Setup container
    $iframe_parent = $iframe_element->parentNode;
    if (!has_class($iframe_element, $embed_container_class)) {

      // Resolve dimensions
      $width = $iframe_element->getAttribute('width');
      $height = $iframe_element->getAttribute('height');

      // Create wrapper
      $iframe_wrapper = wrap($iframe_element, 'div');

      // Set embed container class
      add_class($iframe_wrapper, $embed_container_class);

      $width = is_numeric($width) ? intval($width) : 525;
      $height = is_numeric($height) ? intval($height) : 295;
      $orientation = $width < $height ? 'portrait' : 'landscape';
      $ratio = intval($height / $width * 100);

      // Match against preset ratios
      $matched_preset_item = null;
      foreach($embed_preset_ratios_sorted as $embed_preset_item) {
        if ($orientation === $embed_preset_item['orientation'] && $ratio === $embed_preset_item['ratio']) {
          $matched_preset_item = $embed_preset_item;
          break;
        }
      }

      if ($matched_preset_item) {
        $matched_width = $matched_preset_item['width'];
        $matched_height = $matched_preset_item['height'];
      } else {
        $gcd_ratio = ratio($width, $height);
        $matched_width = $gcd_ratio[0];
        $matched_height = $gcd_ratio[1];
      }

      // Generate embed ratio class
      $embed_ratio_class = $embed_ratio_class_prefix
        . $matched_width
        . $embed_ratio_class_divider
        . $matched_height;

      if (!$matched_preset_item) {
        // For custom sizes, create an inline style element
        $style_element = $doc->createElement('style');
        $style_element->appendChild($doc->createTextNode(".$embed_ratio_class:before { padding: $ratio%; }"));
        $iframe_element->parentNode->insertBefore($style_element, $iframe_element);
      }

      // Apply embed ratio class
      add_class($iframe_wrapper, $embed_ratio_class);
    }
  }

  return serialize_html($doc);
}

add_filter('the_content', 'benignware\wp\bootstrap_hooks\the_content_embeds');