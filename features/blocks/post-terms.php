<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_post_terms($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/post-terms') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);
  
  $tags = $xpath->query('//a[@rel="tag"]');

  if (!count($tags)) {
    return $content;
  }

  foreach ($tags as $tag) {
    add_class($tag, $options['post_tag_link_class']);
  }

  $separators = iterator_to_array($xpath->query('//span[@class="wp-block-post-terms__separator"]'));

  foreach ($separators as $separator) {
    $separator->parentNode->removeChild($separator);
  }

  return serialize_html($doc);
}

// add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_post_terms', 10, 2);