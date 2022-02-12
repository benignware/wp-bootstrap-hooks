<?php

add_filter('the_category', function($thelist, $separator) {
  $options = wp_bootstrap_options();

  // echo '<textarea>' . $thelist . '</textarea>';
  // exit;

  // echo $separator;
  // echo '<br/>';

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $thelist );
  $doc_xpath = new DOMXpath($doc);

  // Wrapper
  $root = $doc_xpath->query("//body/*")->item(0);

  if (!in_array(strtolower($root->tagName), ['nav', 'div'])) {
    $nav = $doc->createElement('nav');
    $parent_node = $root->parentNode;

    foreach ($parent_node->childNodes as $child) {
      $nav->appendChild($child->cloneNode(true));
    }

    while ($parent_node->hasChildNodes()) {
      $parent_node->removeChild($parent_node->firstChild);
    }
    
    $parent_node->appendChild($nav);

    $root = $nav;
  }

  // Remove separators from markup
  $text_nodes = $doc_xpath->query("//text()");

  foreach ($text_nodes as $text_node) {
    if (strpos(trim($text_node->nodeValue), trim($separator)) === 0) {
      $text_node->nodeValue = preg_replace('~^\s*' . preg_quote($separator,'~') . '~', '', $text_node->nodeValue);
    }
  }

  // Wrap links in list items
  $link_elements = $doc->getElementsByTagName( 'a' );
  $items = [];

  foreach ($link_elements as $link_element) {
    if (strtolower($link_element->parentNode->nodeName) !== 'li') {
      $li = $doc->createElement('li');
      $li->setAttribute('class', $options['category_list_item_class']);

      $link_element->parentNode->insertBefore($li, $link_element);
      $li->appendChild($link_element);
    } else {
      $li = $link_element->parentNode;
      add_class($li, $options['category_list_item_class']);
    }

    $items[] = $li;
  }

  // Find or create list element and append items
  $ul = $doc->getElementsByTagName('ul')->item(0);

  if (!$ul) {
    $ul = $doc->createElement('ul');
    $root->appendChild($ul);
  }

  add_class($ul, $options['category_list_class']);

  foreach ($items as $item) {
    $ul->appendChild($item);
  }

  $thelist = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());
  // $thelist = '';

  return $thelist;
}, 10, 2);