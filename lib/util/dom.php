<?php

namespace benignware\bootstrap_hooks\util\dom {
  use \DOMXPath;

  function _parse_style($css) {
    return array_reduce(preg_split('~[;]+\s*~', $css), function($result, $item) {
      $split_index = strpos($item, ':');

      if ($split_index >= 0) {
        $key = trim(substr($item, 0, $split_index));
        $value = trim(substr($item, $split_index + 1));
      }
  
      return array_merge($result, [
        $key => $value
      ]);
    }, []);
  }
  
  function _stringify_style($styles) {
    $styles = array_filter($styles);

    return implode(';', array_map(function($key, $value) {
      return trim($key) . ': ' . trim($value);
    }, array_keys($styles), array_values($styles)));
  }
  
  function add_style($element, $name, $value) {
    $styles = _parse_style($element->getAttribute('style'));
    $styles[$name] = $value;
    $element->setAttribute('style', _stringify_style($styles));
  }

  function get_style($element, $name) {
    $styles = _parse_style($element->getAttribute('style'));
    return isset($styles[$name]) ? $styles[$name] : null;
  }
  
  function remove_style($element, $name) {
    $styles = _parse_style($element->getAttribute('style'));
    unset($styles[$name]);
    $element->setAttribute('style', _stringify_style($styles));
  }

  function remove_all_styles($element) {
    $element->removeAttribute('style');
  }
  
  function _parse_class($class) {
    return preg_split('/\s+/', $class);
  }
  
  function _stringify_class($classes) {
    return implode(' ', array_unique($classes));
  }

  function has_class($element, $pattern) {
    $classes = _parse_class($element->getAttribute('class'));
    $classes = array_filter($classes, function($class) use ($pattern) {
      return $class === $pattern || @preg_match($pattern, $class);
    });

    return count($classes) > 0;
  }
  
  function add_class($element, $class) {
    $classes = _parse_class($element->getAttribute('class'));
    $classes[] = $class;
    $element->setAttribute('class', _stringify_class($classes));
  }
  
  function remove_class($element, $pattern, $recursive = false) {
    if (is_array($element) or $element instanceof \DOMNodeList) {
      foreach ($element as $node) {
        remove_class($node, $pattern);
      }
      return;
    }

    if (!$element || $element->nodeType !== 1) {
      return;
    }

    $classes = _parse_class($element->getAttribute('class'));
    $classes = array_filter($classes, function($class) use ($pattern) {
      return $class && $class !== $pattern && !@preg_match($pattern, $class);
    });

    if (count($classes) > 0) {
      $element->setAttribute('class', _stringify_class($classes));
    } else {
      $element->removeAttribute('class');
    }

    if ($recursive) {
      foreach ($element->childNodes as $node) {
        if ($node->nodeType === 1) {
          remove_class($node, $pattern);
        }
      }
    }
  }

  function find_all_by_class($element, ...$classes) {
    $result = [];
    $container = $element instanceof DOMDocument ? $element : $element->ownerDocument;

    if (!$container) {
      return [];
    }

    $xpath = new DOMXPath($container);

    foreach ($classes as $class) {
      $items = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' " . $class . " ')]", $element);
      $result = array_merge($result, iterator_to_array($items));
    }
    
    return $result;
  }

  function find_by_class($element, $class) {
    return current(find_all_by_class($element, $class));
  }

  function remove_all($parentNode) {
    while ($parentNode->hasChildNodes()) {
      $parentNode->removeChild($parentNode->firstChild);
    }
  }

  function trim_nodes($node_list) {
    $i = 0;
    $node = $node_list->item($i++);
    $remove = [];

    while ($node->nodeType === 3 && strlen(trim($node->nodeValue)) === 0) {
      $remove[] = $node;
      $node = $node_list->item($i++);
    }

    $i = $node_list->length - 1;
    $node = $node_list->item($i--);

    while ($node->nodeType === 3 && strlen(trim($node->nodeValue)) === 0) {
      $remove[] = $node;
      $node = $node_list->item($i--);
    }

    foreach ($remove as $node) {
      $node->parentNode->removeChild($node);
    }
  }

  function nested_root($element) {
    $result = null;

    foreach ($element->childNodes as $child) {
      if ($child->nodeType === 3 && strlen(trim($child->nodeValue)) > 0) {
        return $element;
      }

      if ($result) {
        return $element;
      }

      $result = $child;
    }

    if ($result) {
      return nested_root($result);
    }

    return $element;
  }

  function replace_tag($element, $name) {
    $new_element = $element->ownerDocument->createElement($name);
  
    foreach ($element->childNodes as $child) {
      $new_element->appendChild($child->cloneNode(true));
    }

    foreach ($element->attributes as $attrName => $attrNode) {
      $new_element->setAttribute($attrName, $attrNode->value);
    }

    if ($element->parentNode) {
      $element->parentNode->insertBefore($new_element, $element);
      $element->parentNode->removeChild($element);
    }

    return $new_element;
  }

  function contains_node($parent, $node) {
    $xpath = new DOMXpath($parent->ownerDocument);
    $elements = $xpath->query('.//*', $parent);
  
    foreach ($elements as $element) {
      if ($element === $node) {
        return true;
      }
    }
  
    return false;
  }

  function get_common_ancestor($node_a, $node_b) {
    while ($node_a = $node_a->parentNode) {
      if (contains_node($node_a, $node_b)) {
        return $node_a;
      }
    }
  
    return null;
  }

  function inner_root($root) {
    $doc = $root->ownerDocument;
    $xpath = new \DOMXpath($doc);
    $inner_roots = $xpath->query('//div[1][count(following-sibling::*[not(local-name() = "script")]) = 0 and count(preceding-sibling::*[not(local-name() = "script")]) = 0]', $root);
    $inner_root = $inner_roots->item($inner_roots->length - 1);
    $p = null;
  
    foreach ( $inner_roots as $element) {
      if ($p === null or $element->parentNode === $p) {
        if ($p !== null) {
          $inner_root = $element;
        }
        
        $p = $element;
      } else {
        break;
      }
     
    }

    return $inner_root;
  }

  function append_html($parent, $source) {
    $tmpDoc = new \DOMDocument();
    $tmpDoc->loadHTML($source);
    
    foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
      $node = $parent->ownerDocument->importNode($node, true);
      $parent->appendChild($node);
    }
}
}
