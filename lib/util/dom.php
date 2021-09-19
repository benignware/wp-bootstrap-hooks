<?php

namespace util\dom {
  function _parse_style($css) {
    return array_reduce(preg_split('~[;\s]+~', $css), function($result, $item) {
      list($key, $value) = preg_split('~[\s:]+~', $item);
  
      return array_merge($result, [
        $key => $value
      ]);
    }, []);
  }
  
  function _stringify_style($styles) {
    return implode('; ', array_map(function($key, $value) {
      return $key . ': ' . $value;
    }, array_keys($styles), array_values($styles)));
  }
  
  function add_style($element, $name, $value) {
    $styles = _parse_style($element->getAttribute('style'));
    $styles[$name] = $value;
    $element->setAttribute('style', _stringify_style($styles));
  }
  
  function remove_style($element, $name) {
    $styles = _parse_style($element->getAttribute('style'));
    unset($styles[$name]);
    $element->setAttribute('style', _stringify_style($styles));
  }
  
  function _parse_class($class) {
    return preg_split('/\s+/', $class);
  }
  
  function _stringify_class($classes) {
    return implode(' ', array_unique($classes));
  }
  
  function add_class($element, $class) {
    $classes = _parse_class($element->getAttribute('class'));
    $classes[] = $class;
    $element->setAttribute('class', _stringify_class($classes));
  }
  
  function remove_class($element, $pattern) {
    $classes = _parse_class($element->getAttribute('class'));
    $classes = array_filter($classes, function($class) use ($pattern) {
      return $class && !preg_match($pattern, $class);
    });

    $element->setAttribute('class', _stringify_class($classes));
  }
}


