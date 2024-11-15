<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_group_container($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  $is_alignfull = has_class($container, 'alignfull');
  $is_alignwide = has_class($container, 'alignwide');

  if (!$is_alignfull && !$is_alignwide) {
    return $content;
  }

  remove_class($container, 'container', true);
  remove_class($container, 'container-fluid', true);

  if ($is_alignfull) {
    add_class($container, 'container-fluid');
  } else if ($is_alignwide) {
    add_class($container, 'container');
  }

  remove_class($container, 'has-global-padding');

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_group_container', 10, 2);
