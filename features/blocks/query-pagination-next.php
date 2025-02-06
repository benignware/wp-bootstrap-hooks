<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_query_pagination_next($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/query-pagination-next') {
    return $content;
  }

  extract(get_block_options($block));
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = find_by_class($doc, 'wp-block-query-pagination-next');

  if (!$container) {
    return $content;
  }

  add_class($container, $page_link_class);

  $item = $doc->createElement('div');
  $item->setAttribute('class', $page_item_class);
  $item->appendChild($container->cloneNode(true));
  
  remove_class($container, 'has-small-font-size');

  $container->parentNode->appendChild($item);
  $container->parentNode->removeChild($container);

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_query_pagination_next', 10, 2);