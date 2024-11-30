<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_group_card($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/group') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  if (!has_class($container, 'card')) {
    return $content;
  }

  $card = $container;

  remove_class($card, 'is-layout-flex');
  remove_class($card, "~^wp-container-core-group-is-layout~");
  
  $card_bodies = find_all_by_class($container, 'card-body');

  foreach ($card_bodies as $card_body) {
    remove_class($card_body, 'is-layout-flex');
    remove_class($card_body, "~^wp-container-core-group-is-layout~");
  }

  // Find read-more block and add stretched-link class
  $read_more = find_by_class($container, 'wp-block-read-more');
  
  if ($read_more) {
    $href = $read_more->getAttribute('href');
    $links = $xpath->query('.//a[@href="' . $href . '"]', $container);

    foreach ($links as $link) {
      add_class($link, 'stretched-link');
    }
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_group_card', 10, 2);
