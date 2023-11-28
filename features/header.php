<?php

add_filter('get_header_image_tag_attributes', function($attr) {
  if (!current_theme_supports('bootstrap')) {
    return $attr;
  }

  $options = wp_bootstrap_options();

  $classes = isset($attr['class']) ? explode(' ', $attr['class']) : [];
  $classes.= $options['img_class'];

  $attr['class'] = implode(' ', $classes);

  return $attr;
}, 11, 3);
