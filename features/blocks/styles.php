<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_with_styles($content, $block) {
    if (!current_theme_supports('bootstrap') || empty(trim($content))) {
        return $content;
    }

    // $supported_blocks = ['core/group'];

    // if (!in_array($block['blockName'], $supported_blocks)) {
    //     return $content;
    // }

    $attrs = $block['attrs'] ?? [];
    $doc = parse_html($content);

    // Determine the target element: try finding by block-specific class, fall back to the root element
    $block_class_name = str_replace('core/', 'wp-block-', $block['blockName'] ?? '');
    $element = find_by_class($doc, $block_class_name) ?? root_element($doc);

    if (!$element) {
      return $content;
    }

    // Handle block gap
    $block_styles = $attrs['style'] ?? [];
    $block_spacings = $block_styles['spacing'] ?? [];
    $block_gap = $block_spacings['blockGap'] ?? null;

    $bg = $attrs['backgroundColor'] ?? null;
    $style_bg = $attrs['style']['color']['background'] ?? null;

    if (!isset($block_gap) && !isset($bg) && !isset($style_bg)) {
      // If no styles to apply, return the original content
      return $content;
    }

    if (isset($block_gap)) {
      $block_gap_value = get_theme_css_var($block_gap);
      // add_style($element, 'gap', $block_gap_value);
      add_style($element, '--bs-gap', $block_gap_value);
    }

    // Handle background color
    if ($bg || $style_bg) {
      if ($style_bg) {
        $theme_color_def = get_palette_color($style_bg);

        if ($theme_color_def) {
          // Apply Bootstrap class for theme preset colors
          $theme_color = $theme_color_def['slug'] ?? null;

          if ($theme_color) {
            $is_bs_theme_color = in_array($theme_color, [
              'primary',
              'secondary',
              'success',
              'danger',
              'warning',
              'info',
              'light',
              'dark',
              'body-bg',
              'body-secondary-bg',
              'body-tertiary-bg',
            ]);

            if ($is_bs_theme_color) {
              add_class($element, 'text-bg-' . $theme_color);
            } else {
              $color_value = $theme_color_def['color'];
              $rgba = 'rgba(from var(--bs-' . $theme_color . ', ' . $color_value . ') r g b / var(--bs-bg-opacity, 1))';

              remove_class($element, '~-background-color$~');
              remove_class($element, 'has-background');
              
              add_style(
                $element,
                'background-color',
                $rgba
              );
            }
          }
          
        } elseif (is_color($bg_color)) {
          // Apply inline style for custom colors
          add_style($element, 'background-color', $bg_color);
        }


        // Automatic theme detection based on brightness
        // $brightness_threshold = 50; // Define a threshold for brightness

        // $bg_color_value = $theme_color_def ? $theme_color_def['color'] : $bg_color;
        // $bg_brightness = brightness($bg_color_value);

        // $is_dark = $bg_brightness >= 0 && $bg_brightness <= $brightness_threshold;

        // if ($is_dark) {
        //   $element->setAttribute('data-bs-theme', 'dark');
        // }
      }
    }

    return serialize_html($doc);
}

// Register the filter
add_filter('render_block', __NAMESPACE__ . '\\render_block_with_styles', 9, 2);
