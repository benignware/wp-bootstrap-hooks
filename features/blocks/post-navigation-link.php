<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_post_navigation_link($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/post-navigation-link') {
    return $content;
  }

  extract(wp_bootstrap_options());

  $attrs = $block['attrs'];
  $doc = parse_html($content);

  $type = $attrs['type'] ?? 'next';

  $elem = find_by_class($doc, "post-navigation-link-$type");

  if ($elem) {
    add_class($elem, $post_navigation_link_wrapper_class);
  
    $link = $elem->getElementsByTagName('a')->item(0);

    if (!$link) {
      return $content;
    }

    $arrow = find_by_class($elem, "wp-block-post-navigation-link__arrow-$type");

    if ($arrow && !contains_node($link, $arrow)) {
      if ($type === 'next') {
        $link->appendChild($arrow);
      } else {
        $link->insertBefore($arrow, $link->firstChild);
      }
    }

    add_class($link, $post_navigation_link_class);
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_post_navigation_link', 10, 2);