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
	$level = $items->length;

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

	$breadcrumb_divider = WPSEO_Options::get( 'breadcrumbs-sep' );

	foreach ($items as $index => $item) {
		$li = $doc->createElement('li');
		$li->setAttribute('class', $breadcrumb_item_class);

		if ($index > 0) {
			$li->setAttribute('data-breadcrumb-divider', $breadcrumb_divider);
		}

		$li->appendChild($item);
		$fragment->appendChild($li);
	}

	if ($current) {
		$li = $doc->createElement('li');
		$li->setAttribute('class', $breadcrumb_item_class . ' ' . $breadcrumb_item_active_class);

		if ($level > 0) {
			$li->setAttribute('data-breadcrumb-divider', $breadcrumb_divider);
		}

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
	$options = wp_bootstrap_options();

	return $separator ?: $options['breadcrumb-divider'];
});

add_action('wp_enqueue_scripts', function() {
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );

	wp_add_inline_style( 'dummy-handle', '.breadcrumb-item[data-breadcrumb-divider]:before { color: blue !important; content: attr(data-breadcrumb-divider) " "; }' );
}, 100);


?>
