<?php

add_filter('get_header_image_tag', function($html, $header, $attr) {
  $html = wp_bootstrap_tag_add_class('img', 'img-fluid', $html);

  return $html;
}, 11, 3);
