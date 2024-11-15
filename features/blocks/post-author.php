<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_ppost_author($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/post-author') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);

  $title = find_by_class($doc, 'wp-block-post-author__name');

  if ($title) {
    add_class($title, 'h5 mb-1');
  }

  $avatar = find_by_class($doc, 'wp-block-post-author__avatar');

  if ($avatar) {
    add_class($avatar, 'me-2');
  }

  $bio = find_by_class($doc, 'wp-block-post-author__bio');

  if ($bio) {
    add_class($bio, 'small');
  }
  

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_ppost_author', 10, 2);