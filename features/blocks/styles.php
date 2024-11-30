<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_with_styles($content, $block) {
    if (!current_theme_supports('bootstrap') || empty(trim($content))) {
        return $content;
    }

    $is_button = $block['blockName'] === 'core/button';

    if ($is_button) {
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

    if (has_class('.btn', $element)) {
        return $content;
    }

    // Handle background color
    $bg = $attrs['backgroundColor'] ?? null;
    $style_bg = $attrs['style']['color']['background'] ?? null;
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
    }

    return serialize_html($doc);
}

// Register the filter
add_filter('render_block', __NAMESPACE__ . '\\render_block_with_styles', 10, 2);
