<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_search($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/search') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $doc_xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  $form = $doc_xpath->query('//form')->item(0);
  $input = $doc_xpath->query('//input[@name="s"]')->item(0);
  $submit = $doc_xpath->query('//input[@type="submit"]|//button')->item(0);

  if (!$form || !$input || !$submit) {
    return '';
  }

  $common_ancestor = get_common_ancestor($input, $submit);

  if ($common_ancestor === $form) {
    $wrapper = $doc->createElement('div');
    $children = [];

    foreach ($common_ancestor->childNodes as $child) {
      if (
        $child === $submit
        || contains_node($child, $submit)
        || $child === $input
        || contains_node($child, $input)
      ) {
        $children[] = $child;
      }
    }

    if (count($children) > 0) {
      $common_ancestor->insertBefore($wrapper, $children[0]);

      foreach ($children as $child) {
        $wrapper->appendChild($child);
      }
    }
  } else {
    $wrapper = $common_ancestor;
  }

  $wrapper->setAttribute('class', $options['input_group_class']);

  $submit->setAttribute('class', $submit->getAttribute('class') . ' ' . $options['submit_class']);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_table', 10, 2);
