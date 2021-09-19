<?php

use function util\dom\add_class;
use function util\dom\remove_class;
use function util\dom\remove_style;

add_filter('render_block', function($content, $block)  {
  if (!trim($content)) {
    return $content;
  }

  $name = $block['blockName'];
  $options = wp_bootstrap_options();

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $content);
  $doc_xpath = new DOMXpath($doc);

  list($container) = $doc_xpath->query("//body/*[1]");

  // Images
  $imgs = $doc_xpath->query("//img");

  foreach ($imgs as $img) {
    add_class($img, $options['img_class']);
  }

  // Blockquotes
  $blockquotes = $doc_xpath->query("//blockquote");

  foreach ($blockquotes as $blockquote) {
    add_class($blockquote, $options['blockquote_class']);

    list($cite) = $doc_xpath->query("//cite", $blockquote);

    if ($cite) {
      list($figure) = $doc_xpath->query("..//figure", $blockquote);

      if (!$figure) {
        $figure = $doc->createElement('figure');
        $blockquote->parentNode->insertBefore($figure, $blockquote->nextSibling);
        $figure->appendChild($blockquote);
      }

      $figcaption = $doc->createElement('figcaption');
      $figcaption->setAttribute('class', 'blockquote-footer');

      $blockquote->parentNode->insertBefore($figcaption, $blockquote->nextSibling);
      $figcaption->appendChild($cite);
    }
  }

  // Figures
  $figures = $doc_xpath->query("//figure");

  foreach ($figures as $figure) {
    add_class($figure, $options['img_caption_class']);

    list($figcaption) = $doc_xpath->query("//figcaption", $figure);
    if ($figcaption) {
      add_class($figcaption, $options['img_caption_text_class']);
    }

    list($figimg) = $doc_xpath->query("//img", $figure);
    if ($figimg) {
      add_class($figimg, $options['img_caption_img_class']);
    }
  }

  // Inputs
  $inputs = $doc_xpath->query("//textarea|//select|//input[not(@type='checkbox') and not(@type='radio') and not(@type='submit')]");

  foreach ($inputs as $input) {
    add_class($input, $options['text_input_class']);
  }

  // Blocks
  if ($name === 'core/button') {
    $elements = $doc_xpath->query("//a|//button");

    foreach ($elements as $element) {
      add_class($element, $options['button_class']);
  
      $element->setAttribute('role', 'button');
      $element->setAttribute('href', $element->getAttribute('href') ?? '#');
    }

    remove_class($container, '~^wp-block~');
  }

  if ($name === 'core/columns') {
    remove_class($container, '~^wp-block~');
    add_class($container, $options['columns_class']);
  }

  if ($name === 'core/column') {
    $width = $block['attrs']['width'];
    $cell = intval(floatval($width) / 100 * 12);
    $breakpoint = 'lg';
    $class = sprintf($options['column_class'], $cell, $breakpoint);

    add_class($container, $class);
    remove_style($container, 'flex-basis');
    remove_class($container, '~^wp-block~');
  }

  if ($name === 'core/image') {
    remove_class($container, '~^wp-block~');
  }


  if ($name === 'core/pullquote' || $name === 'core/quote') {
    remove_class($container, '~^wp-block~');
  }
  
  $result = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $result;
}, 10, 2);
