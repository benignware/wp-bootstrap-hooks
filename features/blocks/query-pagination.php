<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_query_pagination($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/query-pagination') {
    return $content;
  }

  extract(get_block_options($block));

  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = array_pop(find_all_by_class($doc, 'wp-block-query-pagination'));

  if (!$container) {
    return $content;
  }

  $pagination_class = $pagination_class ?: 'd-flex';

  add_class($container, $pagination_class);
  
  // Pagination prev/next
  foreach (['previous', 'next'] as $key) {
    $element = find_by_class($doc, "wp-block-query-pagination-$key");

    if ($element) {
      add_class($element, $page_link_class);

      $item = $doc->createElement('div');
      remove_class($element, 'has-small-font-size');

      $element->parentNode->insertBefore($item, $element);
      $item->setAttribute('class', $page_item_class);
      $item->appendChild($element);
    }
  }

  // Pagination numbers
  $page_numbers = find_by_class($container, 'wp-block-query-pagination-numbers');

  if ($page_numbers) {
    $links = find_all_by_class($page_numbers, 'page-numbers');

    foreach ($links as $link) {
      if ($link->nodeType === 1) {
        add_class($link, $page_link_class);
        
        $item = $doc->createElement('div');
        $item->setAttribute('class', $page_item_class);
        
        $link->parentNode->insertBefore($item);
        $item->appendChild($link);
        
        $page_numbers->parentNode->insertBefore($item->cloneNode(true), $page_numbers);
      }
    }
  
    $page_numbers->parentNode->removeChild($page_numbers);

    // Page numbers won't work with space-between layout
    $layout = $attrs['layout'] ?? null;
    $justify_content = $layout['justifyContent'] ?? null;

    if ($has_page_numbers && $justify_content === 'space-between') {
      add_class($container, 'justify-content-center');
    }
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_query_pagination', 10, 2);