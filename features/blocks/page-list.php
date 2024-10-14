<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_page_list($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/page-list') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  add_class($container, 'nav');
  $walker = get_block_nav_walker($doc);
  $walker($container);

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_page_list', 10, 2);