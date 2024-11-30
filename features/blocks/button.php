<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_button($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/button') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $doc_xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  $button = $doc_xpath->query("//a|//button")->item(0);

  if (!$button) {
    return $content;
  }

  if (isset($attrs['width'])) {
    add_class($container, sprintf('w-%s', $attrs['width']));
    add_class($button, 'd-block');
  }

  $is_outline = isset($attrs['className']) && in_array('is-style-outline', preg_split('/\s+/', $attrs['className']));

  $color = isset($attrs['textColor']) ? $attrs['textColor'] : '';
  $bg = isset($attrs['backgroundColor']) ? $attrs['backgroundColor'] : '';

  if (isset($attrs['style'])) {
    if (isset($attrs['style']['color'])) {
      if (!$color) {
        $color = isset($attrs['style']['color']['text']) ? $attrs['style']['color']['text'] : '';
      }

      if (!$bg) {
        $bg = isset($attrs['style']['color']['background']) ? $attrs['style']['color']['background'] : '';
      }
    }
  }

  $button_color = $is_outline ? $color : $bg;

  if (!$button_color) {
    $theme_json = get_theme_json();

    if ($is_outline) {
      $color = query_object($theme_json, 'styles.blocks.core/button.variations.outline.color.text');
      $bg = query_object($theme_json, 'styles.blocks.core/button.variations.outline.color.background');
    } else {
      $color = query_object($theme_json, 'styles.blocks.core/button.color.text');
      $bg = query_object($theme_json, 'styles.blocks.core/button.color.background');
    }

    $button_color = $is_outline ? $color : $bg;
  }

  $theme_color_def = get_palette_color($button_color);

  remove_class($button, 'has-link-color');
  remove_class($button, 'has-style-fill');
  remove_class($button, '~^wp-~');
  add_class($button, 'btn');

  if ($theme_color_def) {
    $theme_color = $theme_color_def['slug'];

    if ($is_outline) {
      remove_class($button, 'has-text-color');
      remove_class($button, "has-$theme_color-color");
    } else {
      remove_class($button, 'has-background');
      remove_class($button, 'has-background-color');
      remove_class($button, "has-$theme_color-background-color");
    }
    add_class($button, $is_outline ? 'btn-outline-' . $theme_color : 'btn-' . $theme_color);

  } else {
    if ($bg && is_color($bg)) {
      
      add_style($button, '--bs-btn-bg', $bg);
      remove_style($button, 'background-color');

      add_style($button, '--bs-btn-border-color', $is_outline ? ($color ?: 'initial') : $bg);
      remove_style($button, 'border-color');

      $hover_bg = $is_outline ? ($color ?: 'initial') : color_shade($bg, 0.9);

      add_style($button, '--bs-btn-hover-bg', $hover_bg);
      add_style($button, '--bs-btn-hover-border-color', $hover_bg);

      add_style($button, '--bs-btn-active-bg', $bg);
      add_style($button, '--bs-btn-active-border-color', $bg);
    }

    $color = $color ?: ($bg ? contrast_color($bg) : null);
    
    if ($color && is_color($color)) {
      add_style($button, '--bs-btn-color', $color);
      remove_style($button, 'color');

      $hover_color = color_shade($color, 0.9);
      $hover_color = $is_outline ? ($bg ?: 'initial') : color_shade($color, 0.9);

      add_style($button, '--bs-btn-hover-color', $hover_color);
      add_style($button, '--bs-btn-active-color', $color);
    }
  }

  if (isset($attrs['fontSize'])) {
    $class_size = $attrs['fontSize'] === 'small'
      ? 'btn-sm'
      : (
        $attrs['fontSize'] === 'large'
          ? 'btn-lg'
          : ''
      );

    add_class($button, $class_size);
  }

  if (isset($attrs['style'])) {
    if (isset($attrs['style']['typography'])) {
      if (isset($attrs['style']['typography']['fontSize'])) {
        $font_size = $attrs['style']['typography']['fontSize'];
        
        add_style($button, '--bs-btn-font-size', $font_size);
      }
    }

    if (isset($attrs['style']['border'])) {
      if (isset($attrs['style']['border']['radius'])) {
        $radius = $attrs['style']['border']['radius'];
        
        add_style($button, '--bs-btn-border-radius', $radius);
        remove_style($button, 'border-radius');
      }
    }
  }

  $button->setAttribute('role', 'button');

  if ($button->nodeName === 'a') {
    $button->setAttribute('href', $button->getAttribute('href') ?? '#');
  }

  remove_class($container, 'is-style-outline', true);
  remove_class($container, '~^wp-block~', true);

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_button', 10, 2);