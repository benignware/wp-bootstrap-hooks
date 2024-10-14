<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_post_comments($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/post-comments') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $doc_xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  $list = $doc_xpath->query('.//ol|.//ul', $container)->item(0);

  if ($list) {
    $list_items = $doc_xpath->query('./li', $list);

    if (!count($list_items)) {
      replace_tag($list, 'div');
    }
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_post_comments', 10, 2);