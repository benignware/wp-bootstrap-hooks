<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_comments($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/comments') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  $input = $doc_xpath->query('//input[@type="submit"]')->item(0);

  if ($input) {
    add_class($input, $options['submit_class']);
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_comments', 10, 2);