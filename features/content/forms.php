<?php

namespace benignware\wp\bootstrap_hooks;

function the_content_forms($content) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  $options = wp_bootstrap_options();

  $text_input_class = $options['text_input_class'] ?? 'form-control';

  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);
  
  // Form inputs
  $input_elements = $xpath->query("//textarea|//input[not(@type='checkbox') and not(@type='radio') and not(@type='submit')]");
  
  foreach ($input_elements as $input_element) {
    add_class($input_element, $options['text_input_class']);
  }

  // Form selects
  $select_elements = $xpath->query("//select");
  
  foreach ($select_elements as $select_element) {
    add_class($select_element, $options['form_select_class']);
  }

  // Labels
  $label_elements = $xpath->query("//label");
  foreach ($label_elements as $label_element) {
    if (has_class($label_element, 'input-group-text')) {
      continue;
    }
    add_class($label_element, $options['label_class']);
  }

  // Handle label-wrapped inputs and checkboxes
  $forms = $xpath->query("//form");

  foreach ($forms as $form) {
    $form_id = $form->getAttribute('id');
    $labels = iterator_to_array($xpath->query(".//label", $form));

    foreach ($labels as $label) {
      $for = $label->getAttribute('for');
      $input = null;

      if ($for) {
        $input = $xpath->query(sprintf('.//input[@id="%s"]', $for))->item(0);
      } else {
        $input = $xpath->query(".//input[not(@type='submit' or @type='button' or @type='hidden')]", $label)->item(0);
        
        if ($input) {
          $input_type = $input->getAttribute('type');
          $input_name = $input->getAttribute('name');
          $input_id = $input->hasAttribute('id')
            ? $input->getAttribute('id')
            : (
              $form_id && $input->hasAttribute('name')
                ? $form_id . '-' . $input->getAttribute('name')
                : null
            );

          // Make sure to hide inputs if their wrapper was (honeypot)
          if ($label->getAttribute('style') && preg_match('~display:\s*none~', $label->getAttribute('style'))) {
            $style = $input->getAttribute('style') ?: '';
            $input->setAttribute('style', $style . '; display: none !important');
          }

          if ($input_id) {
            $input->setAttribute('id', $input_id);
            $label->setAttribute('for', $input_id);

            if ($label->nextSibling) {
              $label->parentNode->insertBefore($input, $label->nextSibling);
            } else {
              $label->parentNode->appendChild($input);
            }
          }
        }
      }

      if (!$input) {
        continue;
      }

      if (in_array($input->getAttribute('type'), ['checkbox', 'radio'])) {
        if (!has_class($input, $options['checkbox_input_class']) && !has_class($label->parentNode, $options['checkbox_container_class'])) {
          $input->setAttribute('class', $options['checkbox_input_class']);
          $label->setAttribute('class', $options['checkbox_label_class']);
          $wrapper = $doc->createElement('span');
          $wrapper->setAttribute('style', 'display: block');
          $wrapper->setAttribute('class', $options['checkbox_container_class']);
          $wrapper->appendChild($input);
          $label->parentNode->insertBefore($wrapper, $label);
          $wrapper->appendChild($label);
        }
      }
    }
  }

  return serialize_html($doc);
}

add_filter('the_content', 'benignware\wp\bootstrap_hooks\the_content_forms');