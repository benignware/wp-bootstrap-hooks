<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_with_styles($content, $block) {
    if (!current_theme_supports('bootstrap') || empty(trim($content))) {
        return $content;
    }

    $supported_blocks = ['core/group'];

    if (!in_array($block['blockName'], $supported_blocks)) {
        return $content;
    }

    $attrs = $block['attrs'] ?? [];
    $doc = parse_html($content);

    // Determine the target element: try finding by block-specific class, fall back to the root element
    $block_class_name = str_replace('core/', 'wp-block-', $block['blockName'] ?? '');
    $element = find_by_class($doc, $block_class_name) ?? root_element($doc);

    if (!$element) {
        return $content;
    }
  
    // Handle background color
    $bg = $attrs['backgroundColor'] ?? null;
    $style_bg = $attrs['style']['color']['background'] ?? null;

    if (!$bg && !$style_bg) {
        return $content;
    }
    
    $bg_color = $bg ?: $style_bg;

    if ($bg_color) {
      $theme_color_def = get_palette_color($bg_color);
      
      if ($theme_color_def) {
        // Apply Bootstrap class for theme preset colors
        $theme_color = $theme_color_def['slug'];
        add_class($element, 'text-bg-' . $theme_color);
      } elseif (is_color($bg_color)) {
        // Apply inline style for custom colors
        add_style($element, 'background-color', $bg_color);
      }

      $bg_color_value = $theme_color_def ? $theme_color_def['color'] : $bg_color;
      $bg_brightness = intval(brightness($bg_color_value));

      $is_dark = $bg_brightness <= 80;

      if (!$is_dark) {
        // $element->setAttribute('data-bs-theme', 'light');
        return $content;
      } else {
        $element->setAttribute('data-bs-theme', 'dark');
      }
    }

    return serialize_html($doc);
}

// Register the filter
add_filter('render_block', __NAMESPACE__ . '\\render_block_with_styles', 10, 2);
