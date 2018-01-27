<?php

function wp_bootstrap_dom_get_classes($element) {
  $class = $element->getAttribute('class');
  return explode(' ', $class);
}

function wp_bootstrap_dom_has_class($element, $class) {
  return in_array($class, wp_bootstrap_dom_get_classes($element));
}

function wp_bootstrap_dom_set_class($element, $class) {
  $classes = wp_bootstrap_dom_get_classes($element);
  $classes[] = $class;
  $classes = array_unique($classes);
  $class = implode(' ', $classes);
  $element->setAttribute('class', $class);
}

function wp_bootstrap_dom_wrap($element, $tag_name) {
  $doc = $element->ownerDocument;
  $parent_node = $element->parentNode;
  $wrapper_element = $doc->createElement($tag_name);
  $parent_node->insertBefore($wrapper_element, $element);
  $wrapper_element->appendChild($element);
  return $wrapper_element;
}

?>
