<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_separator($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/separator') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  remove_class($container, '~^wp-block~', true);

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_separator', 10, 2);