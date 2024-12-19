<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_details($content, $block) {
    if (!current_theme_supports('bootstrap')) {
        return $content;
    }

    // Check if the block is `core/details`
    if ($block['blockName'] !== 'core/details') {
        return $content;
    }

    $block_type_slug = str_replace('core/', '', $block['blockName']);
    $block_class = 'wp-block-' . $block_type_slug;

    $doc = parse_html($content);

    // The block element is actually the <details> element itself
    $block_element = find_by_class($doc, $block_class);
    if (!$block_element) {
        return $content; // Block wrapper not found
    }

    // Ensure the block element is a <details> tag
    if ($block_element->nodeName !== 'details') {
        return $content; // Not a <details> element
    }

    // Check if the `details` element has already been processed
    if ($block_element->hasAttribute('data-processed')) {
        return $content;
    }
    $block_element->setAttribute('data-processed', 'true');

    // Extract the <summary> element (to use as the collapse toggle)
    $summary = $block_element->getElementsByTagName('summary')->item(0);
    if (!$summary) {
        return $content; // No <summary> element found
    }

    remove_class($block_element, 'is-layout-flow');

    // Generate unique IDs for the switch and collapsible content
    $switch_id = uniqid('switch-');
    $collapse_id = uniqid('collapse-');

    // Create the switch container
    $switch = $doc->createElement('div');
    add_class($switch, 'form-check form-switch mb-0 d-flex gap-2 align-items-baseline');

    // Create the input element (checkbox)
    $input = $doc->createElement('input');
    $input->setAttribute('type', 'checkbox');
    add_class($input, 'form-check-input');
    $input->setAttribute('id', $switch_id);
    $input->setAttribute('aria-controls', $collapse_id);
    $input->setAttribute('data-bs-toggle', 'collapse');
    $input->setAttribute('data-bs-target', '#' . $collapse_id);

    // Create the label for the switch
    $label = $doc->createElement('label', $summary->textContent); // Use summary text as the label
    add_class($label, 'form-check-label');
    $label->setAttribute('for', $switch_id);

    // Append the input and label to the switch container
    $switch->appendChild($input);
    $switch->appendChild($label);

    // Replace the <details> tag with a <div> while keeping classes
    $block_element = replace_tag($block_element, 'div');
    add_class($block_element, 'details-wrapper');

    // Create a new div for the collapsible content
    $collapse = $doc->createElement('div');
    $collapse->setAttribute('id', $collapse_id);
    add_class($collapse, 'collapse is-layout-flow my-0');

    // Move all non-summary children of <details> into the collapsible content
    foreach ($block_element->childNodes as $child) {
        if ($child->nodeName !== 'summary') {
            $collapse->appendChild($child->cloneNode(true)); // Clone to avoid modifying the original DOM
        }
    }

    // Clear all existing children of the block wrapper
    while ($block_element->firstChild) {
        $block_element->removeChild($block_element->firstChild);
    }

    // Add the switch and collapse content as children of the block wrapper
    $block_element->appendChild($switch);
    $block_element->appendChild($collapse);


    // SVG
    // $svg = $doc->createElementNS('http://www.w3.org/2000/svg', 'svg');
    // $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    // $svg->setAttribute('fill', 'var(--bs-primary)');
    // $svg->setAttribute('class', 'bi bi-chevron-down');
    // $svg->setAttribute('viewBox', '-4 -4 8 8');
    // $circle = $doc->createElementNS('http://www.w3.org/2000/svg', 'circle');
    // $circle->setAttribute('r', '3');
    // $svg->appendChild($circle);

    // $switch->insertBefore($svg, $input->nextSibling);

    return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_details', 10, 2);
