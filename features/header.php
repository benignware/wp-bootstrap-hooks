<?php

add_filter('get_header_image_tag', function($html, $header, $attr) {
  $options = wp_bootstrap_options();
  $html = wp_bootstrap_tag_add_class('img', $options['img_class'], $html);

  return $html;
}, 11, 3);
