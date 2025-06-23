<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_navigation_link($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/navigation-link') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $doc_xpath = new \DOMXPath($doc);
  $block_element = find_by_class($doc, 'wp-block-navigation-link');

  if (!$block_element) {
    return $content;
  }

  // print_r($block_element);
  // echo '<br/>';
  // echo '<br/>';
  // echo '<br/>';

  if ($block_element->tagName === 'li') {
    // add_class($block_element, 'nav-item');
  }

  $link = $block_element->getElementsByTagName('a')->item(0);

  if ($link) {
    // add_class($link, 'nav-link');
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_navigation_link', 10, 2);
