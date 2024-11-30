<?php

namespace benignware\wp\bootstrap_hooks;

function the_content_images($content) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if (empty(trim($content))) {
    return $content;
  }

  $options = wp_bootstrap_options();
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);
  $images = $xpath->query('//img|//video');

  foreach ($images as $img) {
    add_class($img, $options['img_class']);
  }

  return serialize_html($doc);
}

add_filter('the_content', 'benignware\wp\bootstrap_hooks\the_content_images');