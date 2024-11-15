<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_query_pagination($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/query-pagination') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  add_class($container, 'pagination gap-0');

  // Page numbers won't work with space-between layout
  $has_page_numbers = !!find_by_class($container, 'page-numbers');
  $layout = $attrs['layout'] ?? null;
  $justify_content = $layout['justifyContent'] ?? null;

  if ($has_page_numbers && $justify_content === 'space-between') {
    add_class($container, 'justify-content-center');
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_query_pagination', 10, 2);