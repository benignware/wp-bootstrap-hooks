<?php

add_filter('get_header_image_tag', function($html, $header, $attr) {
  if (!current_theme_supports('bootstrap')) {
    return $html;
  }

  $options = wp_bootstrap_options();
  $html = wp_bootstrap_tag_add_class('img', $options['img_class'], $html);

  return $html;
}, 11, 3);
