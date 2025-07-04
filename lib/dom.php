<?php

namespace benignware\wp\bootstrap_hooks;

use DOMDocument;
use DOMDocumentFragment;
use DOMXPath;

function parse_html($html) {
  $doc = new \DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);

  return $doc;
}

function serialize_html($doc, $partial = true) {
  $html = $doc->saveHTML();

  if ($partial) {
    $html = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $html);
  }

  return $html;
}

function get_xpath($doc) {
  return new \DOMXPath($doc);
}

function dom_query($doc, $query, $context = null) {
  $xpath = get_xpath($doc);
  return $xpath->query($query, $context);
}

function root_element($doc) {
  $doc_xpath = get_xpath($doc);
  return $doc_xpath->query("//body/*[1]")->item(0);
}

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
  $styles = array_filter($styles, function($value, $key) {
    return $key;
  }, ARRAY_FILTER_USE_BOTH);
  
  return implode(';', array_map(function($key, $value) {
    return trim($key) . ': ' . trim($value);
  }, array_keys($styles), array_values($styles)));
}

function add_style($element, $name, $value) {
  if (!$element) {
    return;
  }
  $styles = _parse_style($element->getAttribute('style'));
  $styles[$name] = $value;
  $element->setAttribute('style', _stringify_style($styles));
}

function get_style($element, $name) {
  $styles = _parse_style($element->getAttribute('style'));
  return isset($styles[$name]) ? $styles[$name] : null;
}

function get_attributes($element) {
  $attributes = [];

  foreach ($element->attributes as $attrName => $attrNode) {
    $attributes[$attrName] = $attrNode->value;
  }

  return $attributes;
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

function has_class($element, ...$patterns) {
  if (!$element || !is_object($element) || $element->nodeType !== 1) {
    return;
  }

  $classes = _parse_class($element->getAttribute('class'));
  $classes = array_filter($classes, function($class) use ($patterns) {
    return array_reduce($patterns, function($result, $pattern) use ($class) {
      return $result || $class === $pattern || @preg_match($pattern, $class);
    }, false);
  });

  return count($classes) > 0;
}

function add_class($element, $class) {
  if ($element->nodeType !== 1) {
    return;
  }

  $classes = _parse_class($element->getAttribute('class'));

  if (is_array($class)) {
    $classes = array_merge($classes, $class);
  } else {
    $classes[] = $class;
  }
  
  $element->setAttribute('class', _stringify_class($classes));
}

function get_class($element) {
  return _parse_class($element->getAttribute('class'));
}

function find_class($element, $pattern) {
  $classes = _parse_class($element->getAttribute('class'));
  
  $result = array_values(array_filter($classes, function($class) use ($pattern) {
    return $class === $pattern || preg_match($pattern, $class);
  }));

  return $result[0] ?? null;
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
        remove_class($node, $pattern, $recursive);
      }
    }
  }
}

function find_all_by_class($element, ...$classes) {
  $result = [];
  $container = $element && $element instanceof \DOMDocument
    ? $element
    : (
      $element && property_exists($element, 'ownerDocument')
        ? $element->ownerDocument
        : null
    );

  if (!$container) {
    return [];
  }

  $xpath = new \DOMXPath($container);

  foreach ($classes as $class) {
    $items = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' " . $class . " ')]", $element);
    $result = array_merge($result, iterator_to_array($items));
  }
  
  return $result;
}

function find_by_class($element, ...$class) {
  return current(find_all_by_class($element, ...$class));
}

function remove_all_children($parentNode) {
  while ($parentNode->hasChildNodes()) {
    $parentNode->removeChild($parentNode->firstChild);
  }
}

// Deprecated: Use remove_all_children instead
function remove_all($parentNode) {
  remove_all_children($parentNode);
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
  if (strtolower($element->nodeName) === strtolower($name)) {
    return $element;
  }

  if ($element->ownerDocument === null) {
    return $element;
  }

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
  $xpath = new \DOMXpath($parent->ownerDocument);
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
  $doc = $root->ownerDocument ?? $root;
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

function wrap_element($element, $tag_name) {
  $doc = $element->ownerDocument;
  $parent_node = $element->parentNode;
  $wrapper_element = $doc->createElement($tag_name);
  $parent_node->insertBefore($wrapper_element, $element);
  $wrapper_element->appendChild($element);

  return $wrapper_element;
}

function inner_html($element) {
  $innerHTML = '';
  foreach ($element->childNodes as $child) {
    $innerHTML .= $element->ownerDocument->saveHTML($child);
  }
  return $innerHTML;
}

function get_inner_html($element) {
  return inner_html($element);
}

function outer_html($element) {
  return $element->ownerDocument->saveHTML($element);
}

function get_outer_html($element) {
  return outer_html($element);
}

function remove_text_nodes($element) {
  // Get all child nodes as an array (static snapshot)
  $childNodes = iterator_to_array($element->childNodes);

  // Iterate over the child nodes
  foreach ($childNodes as $node) {

      // Only remove text nodes (nodeType 3)
      if ($node->nodeType === 3) {
          $element->removeChild($node);  // Remove the text node
      }
  }
}

/**
 * Safely create a DOMDocumentFragment from raw HTML.
 *
 * @param DOMDocument $doc The DOMDocument instance.
 * @param string $html The raw HTML string to append.
 * @return DOMDocumentFragment The created DOMDocumentFragment with the parsed nodes.
 */
function get_html_fragment(DOMDocument $doc, string $html): DOMDocumentFragment
{
    // Suppress warnings from invalid or non-well-formed HTML
    libxml_use_internal_errors(true);

    // Create a temporary DOMDocument for parsing the HTML
    $tempDoc = new DOMDocument();

    // Wrap the HTML in a container if it's not a full document
    // This ensures single or multiple elements are handled correctly
    $wrappedHtml = "<div>$html</div>";

    // Load the wrapped HTML into the temporary DOMDocument
    $tempDoc->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // Reset libxml errors to avoid side effects
    libxml_clear_errors();

    // Create a new DocumentFragment
    $fragment = $doc->createDocumentFragment();

    // Import each child of the temporary document's body into the main document
    foreach ($tempDoc->documentElement->childNodes as $node) {
        $fragment->appendChild($doc->importNode($node, true));
    }

    return $fragment;
}

function is_empty($element) {
  if ($element->nodeType !== XML_ELEMENT_NODE) {
    return false;
  }

  foreach ($element->childNodes as $child) {
    if ($child->nodeType !== XML_TEXT_NODE || trim($child->nodeValue) !== '') {
      return false; // Found a non-text node or non-whitespace text
    }
  }

  return true; // Only whitespace text nodes or no children at all
}
