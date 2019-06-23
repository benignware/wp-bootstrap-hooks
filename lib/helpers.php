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
  $result_class = implode(' ', $classes);
  $element->setAttribute('class', $result_class);
}

function wp_bootstrap_dom_wrap($element, $tag_name) {
  $doc = $element->ownerDocument;
  $parent_node = $element->parentNode;
  $wrapper_element = $doc->createElement($tag_name);
  $parent_node->insertBefore($wrapper_element, $element);
  $wrapper_element->appendChild($element);
  return $wrapper_element;
}

function wp_bootstrap_gcd($a, $b) {
  // if ($q == 0) return $q == 0 ? $p : wp_bootstrap_gcd($q, $p % $q);
  // Everything divides 0
  if ($a == 0 || $b == 0) {
    return 0;
  }

  // base case
  if ($a == $b) {
    return $a;
  }

  // a is greater
  if ($a > $b) {
    return wp_bootstrap_gcd($a - $b, $b);
  }

  return wp_bootstrap_gcd($a, $b - $a);
}

function wp_bootstrap_ratio($a, $b) {
  $gcd = wp_bootstrap_gcd($a, $b);
  $ra = $a / $gcd;
  $rb = $b / $gcd;
  return $a > $b ? array($ra, $rb) : array($b, $ra);
}

function wp_bootstrap_tag_add_class($tag, $class, $html) {
  if (preg_match('/(<' . $tag . '[^>]*?)(class\s*=\s*"|\')(.*)("|\')(.*>)/', $html)) {
    $html = preg_replace('/(<' . $tag . '[^>]*?)(class\s*=\s*"|\')(.*)("|\')([^>]*>)/', '$1$2$3 ' . $class . '$4$5', $html);
  } elseif (preg_match('/(<' . $tag . '.*?)(>)/', $html)) {
    $html = preg_replace('/(<' . $tag . '.*?)(>)/', '$1 class ="' . $class . '">', $html);
  }

  return $html;
}

?>
