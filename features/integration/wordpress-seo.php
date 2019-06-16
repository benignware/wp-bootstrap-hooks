<?php

// Yoast breadcrumbs
add_filter('wpseo_breadcrumb_output', function($output) {
	$breadcrumb_class = 'breadcrumb';
	$breadcrumb_item_class = 'breadcrumb-item';
	$breadcrumb_item_active_class = 'active';

	$doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $output );

	$doc_xpath = new DOMXpath($doc);

	$items = $doc_xpath->query('.//a');

	$last_item = $items->item($items->length - 1);

	if ($last_item) {
		$next_sibling = $last_item->nextSibling;
		$next_sibling_element = null;

		while ($next_sibling) {
			if ($next_sibling->nodeType === 1) {
				$next_sibling_element = $next_sibling;
				break;
			}
			$next_sibling = $next_sibling->nextSibling;
		}

		if ($next_sibling_element) {
			$current = $next_sibling_element;
		}
	} else {
		$current = $doc_xpath->query('.//text()')->item(0);
	}

	if (!$current) {
		return $output;
	}

	$fragment = $doc->createDocumentFragment();
	$child = $items[0] ?: $current;
	$parent_node =$child->parentNode->parentNode;

	foreach ($items as $item) {
		$li = $doc->createElement('li');
		$li->setAttribute('class', $breadcrumb_item_class);
		$li->appendChild($item);
		$fragment->appendChild($li);
	}

	if ($current) {
		$li = $doc->createElement('li');
		$li->setAttribute('class', $breadcrumb_item_class . ' ' . $breadcrumb_item_active_class);
		$li->appendChild($current);
		$fragment->appendChild($li);
	}

	$ul = $doc->createElement('ul');

	foreach($parent_node->attributes as $attr) {
		$ul->setAttribute($attr->nodeName, $attr->nodeValue);
	}

	$class = apply_filters('wpseo_breadcrumb_output_class', $breadcrumb_class);

	$ul->setAttribute('class', $class);

	$ul->appendChild($fragment);

	$container_node = $parent_node->parentNode;

	while ($container_node->firstChild) {
    $container_node->removeChild($container_node->firstChild);
  }

	$container_node->appendChild($ul);

	return preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());
});


add_filter('wpseo_breadcrumb_separator', function($separator = '') {
	return '';
});
?>
