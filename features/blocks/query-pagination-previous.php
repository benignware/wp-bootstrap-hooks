<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_query_pagination_previous($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/query-pagination-previous') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  add_class($container, 'page-link');
  remove_class($container, 'has-small-font-size');

  $item = $doc->createElement('div');
  $item->setAttribute('class', 'page-item');
  $item->appendChild($container->cloneNode(true));
  remove_class($container, 'has-small-font-size');

  $container->parentNode->appendChild($item);
  $container->parentNode->removeChild($container);

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_query_pagination_previous', 10, 2);