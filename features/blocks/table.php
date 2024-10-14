<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_table($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/table') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  add_class($container, $options['table_container_class']);
  remove_class($container, 'figure', true);

  $table = $container->getElementsByTagName('table')->item(0);

  add_class($table, $options['table_class']);

  if (isset($attrs['className']) && in_array('is-style-stripes', explode(' ', $attrs['className']))) {
    add_class($table, $options['table_striped_class']);
  };

  remove_class($container, '~^wp-block-table~', true);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_table', 10, 2);
