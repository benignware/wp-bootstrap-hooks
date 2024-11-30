<?php

namespace benignware\wp\bootstrap_hooks;

function the_content_buttons($content) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if (empty(trim($content))) {
    return $content;
  }

  $options = wp_bootstrap_options();

  // Parse DOM
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);

  // Buttons
  $buttons = $xpath->query("//form//button|//form//input[@type='submit']|//*[contains(concat(' ', normalize-space(@class), ' '), ' button ')]");
  // $buttons = $xpath->query("//button[not(@data-toggle)]|//input[@type='submit']");
  $buttons = iterator_to_array($buttons);
  $buttons = array_merge($buttons, find_all_by_class($doc, 'wp-element-button', 'button'));

  if (!count($buttons)) {
    return $content;
  }

  // echo 'BUTTONS: ' . count($buttons) . '<br/>';
  // echo '<textarea>';
  // echo $content;
  // echo '</textarea>';

  // echo '--->' . count(find_all_by_class($doc, 'wp-element-button'));

  foreach ($buttons as $button) {
    if (!has_class($button, '~^(?:carousel-control|btn-|nav-)~')) {
      // echo 'BUTTON: ' . $button->getAttribute('class') . '<br/>';

      add_class($button, sprintf($options['button_class'], 'primary'));
      remove_class($button, '~^wp-~');
    }
  }

  return serialize_html($doc);
}

add_filter('the_content', 'benignware\wp\bootstrap_hooks\the_content_buttons', 1000);