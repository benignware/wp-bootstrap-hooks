<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_pullquote($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/pullquote') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $doc_xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  $blockquote = $doc_xpath->query("//blockquote")->item(0);

  if ($blockquote) {
    // remove_class($container, '~^wp-block~');
    add_class($container, $options['blockquote_class']);
    $cite = $doc_xpath->query("//cite", $blockquote)->item(0);

    if ($cite) {
      $figcaption = $doc->createElement('figcaption');
      $figcaption->setAttribute('class', 'blockquote-footer');

      $cite->parentNode->insertBefore($figcaption, $cite);
      $figcaption->appendChild($cite);
    }
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_pullquote', 10, 2);
