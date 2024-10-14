<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_post_title($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/post-title') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  if (has_class($container, 'card-title')) {
    replace_tag($container, 'h5');
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_post_title', 10, 2);