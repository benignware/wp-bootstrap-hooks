<?php


namespace benignware\wp\bootstrap_hooks;

function render_block_post_featured_image($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/post-featured-image') {
    return $content;
  }

  $attrs = $block['attrs'];

  if (!isset($attrs['aspectRatio'])) {
    return $content;
  }

  $doc = parse_html($content);
  $doc_xpath = new \DOMXPath($doc);

  $block_element = find_by_class($doc, 'wp-block-post-featured-image');

  if (!$block_element) {
    return $content;
  }

  add_style($block_element, 'height', 'max-content');

  // $aspectRatio = $attrs['aspectRatio'];

  // $images = $doc_xpath->query(".//img", $container);

  // foreach ($images as $image) {
  //   // add_style($image, 'aspect-ratio', $aspectRatio);
  // }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_post_featured_image', 10, 2);