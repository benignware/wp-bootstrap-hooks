<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_group_container($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);

  $body = $doc->getElementsByTagName('body')->item(0);
  
  if (!$body) {
    return $content;
  }

  foreach ($body->childNodes as $node) {
    if ($node->nodeType !== XML_ELEMENT_NODE || $node->nodeName !== 'div') {
      continue;
    }

    $is_alignfull = has_class($node, 'alignfull');
    $is_alignwide = has_class($node, 'alignwide');

    if (!$is_alignfull && !$is_alignwide) {
      continue;
    }

    // echo 'Processing group container alignment: ' . ($is_alignfull ? 'alignfull' : 'alignwide') . "<br>";
    
    $container = $node;
  }

  if (!$container) {
    return $content;
  }
  // remove_class($container, 'container', true);
  // remove_class($container, 'container-fluid', true);
  remove_class($container, 'has-global-padding', true);

  if ($is_alignfull) {
    // remove_class($container, 'container-fluid', true);
    // add_class($container, 'container-fluid');
  } else if ($is_alignwide) {
    remove_class($container, 'container-fluid', true);
    add_class($container, 'container-fluid');
    // remove_class($container, 'container', true);
    // add_class($container, 'container');
  }


  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_group_container', 1000, 2);
