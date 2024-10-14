<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_image($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/image') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $doc_xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  add_class($container, 'clearfix');

  $figure = $container->nodeName === 'figure' ? $container : $doc_xpath->query(".//figure", $container)->item(0);
  
  if ($figure) {
    add_class($figure, $options['img_caption_class']);

    $caption = $doc_xpath->query(".//figcaption", $figure)->item(0);
    
    if ($caption) {
      add_class($caption, $options['img_caption_text_class']);
      remove_class($container, 'wp-element-caption', true);
    }

    $img = $doc_xpath->query(".//img", $figure)->item(0);

    if ($img) {
      add_class($img, $options['img_caption_img_class']);

      if (!$caption) {
        add_class($img, 'mb-0');
      }
    }
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_image', 10, 2);