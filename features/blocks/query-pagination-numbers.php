<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_query_pagination_numbers($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/query-pagination-numbers') {
    return $content;
  }

  if (empty(trim($content))) {
    return $content;
  }

  extract(get_block_options($block));
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = find_by_class($doc, 'wp-block-query-pagination-numbers');

  if (!$container) {
    return $content;
  }

  $links = find_all_by_class($container, 'page-numbers');

  foreach ($links as $link) {
    if ($link->nodeType === 1) {
      add_class($link, $page_link_class);
      
      $item = $doc->createElement('div');
      $item->setAttribute('class', $page_item_class);
      
      $link->parentNode->insertBefore($item);
      $item->appendChild($link);
      
      $container->parentNode->insertBefore($item->cloneNode(true), $container);
    }
  }

  $container->parentNode->removeChild($container);

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_query_pagination_numbers', 10, 2);