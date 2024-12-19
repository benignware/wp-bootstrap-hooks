<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_search($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/search') {
    return $content;
  }

  if (trim($content) === '') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  $form = $xpath->query('//form')->item(0);
  $input = $xpath->query('//input[@name="s"]')->item(0);
  $submit = $xpath->query('//input[@type="submit"]|//button')->item(0);

  if (!$form) {
    return $content;
  }

  $form->setAttribute('novalidate', 'novalidate');

  $show_label = $attrs['showLabel'] ?? true;
  $button_position = $attrs['buttonPosition'] ?? '';
  
  // Create input-group if we have a submit button
  if ($submit && $button_position !== 'no-button') {
    $wrapper = find_by_class($doc, 'wp-block-search__inside-wrapper');

    if (!$wrapper) {
      return $content;
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
    }

    add_class($wrapper, $options['input_group_class']);
    $wrapper->setAttribute('tabindex', '0');

    if ($button_position === 'button-only') {
      // Wrap the input and button directly in the input-group
      add_class($wrapper, $options['input_group_class']); // e.g., 'input-group'
  
      // Add the expandable class to the input
      add_class($wrapper, 'is-expandable');
  
      // Add necessary classes to the button
      // add_class($submit, $options['expandable_button_class']); // e.g., 'btn btn-outline-primary'
    }
  }

  add_class($submit, $options['submit_class']);
  remove_class($submit, '~^wp-~');
  add_class($submit, 'wp-element-button');

  remove_class($input, '~^wp-~');
  add_class($input, $options['text_input_class']);
  
  $icon = $xpath->query('//svg')->item(0);

  if ($icon) {
    add_style($icon, 'fill', 'currentColor');
  }

  return serialize_html($doc);
}
add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_search', 10, 2);
