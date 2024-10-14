<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_group_navbar_brand($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/group') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  if (!has_class($container, 'navbar-brand')) {
    return $content;
  }

  $links = $container->getElementsByTagName('a');

  foreach ($links as $link) {
    add_style($link, 'text-decoration', 'inherit');
  }

  $logo_block = find_by_class($container, 'wp-block-site-logo');

  remove_class($logo_block, '~^wp-block~', true);
  add_class($logo_block, 'd-flex');

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_group_navbar_brand', 10, 2);
