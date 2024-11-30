<?php

namespace benignware\wp\bootstrap_hooks;

function the_content_tables($content) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  $options = wp_bootstrap_options();

  $table_container_tag = $options['table_container_tag'] ?? 'div';
  $table_container_class = $options['table_container_class'] ?? 'table-responsive';

  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);
  $tables = $doc->getElementsByTagName( 'table' );

  foreach ($tables as $table) {
    if (has_class($table, $options['table_class'])) {
      continue;
    }

    add_class($table, $options['table_class']);

    if ($table_container_tag && !has_class($table->parentNode, $table_container_class)) {
      $table_container_element = $doc->createElement($table_container_tag);
      $table_container_element->setAttribute("class", $table_container_class);
      $table->parentNode->insertBefore($table_container_element, $table);
      $table_container_element->appendChild($table);
    }
  }

  return serialize_html($doc);
}

add_filter('the_content', 'benignware\wp\bootstrap_hooks\the_content_tables');