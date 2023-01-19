<?php

add_filter('wp_get_attachment_image', function($html) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  $options = wp_bootstrap_options();

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );
  $doc_xpath = new DOMXpath($doc);

  // Images
  $image_elements = $doc_xpath->query('//img|//video');

  foreach ($image_elements as $image_element) {
    $classes = explode(' ', $image_element->getAttribute('class'));
    $classes[]= $options['img_class'];
    $classes = array_values(array_unique($classes));

    $image_element->setAttribute('class', implode(' ', $classes));
  }

  $html = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $html;
}, 100);