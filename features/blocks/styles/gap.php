<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_with_block_gap($content, $block) {
    if (!current_theme_supports('bootstrap') || empty(trim($content))) {
        return $content;
    }

    // $supported_blocks = ['core/group'];

    // if (!in_array($block['blockName'], $supported_blocks)) {
    //     return $content;
    // }

    $attrs = $block['attrs'] ?? [];
    $doc = parse_html($content);

    // Determine the target element: try finding by block-specific class, fall back to the root element
    $block_class_name = str_replace('core/', 'wp-block-', $block['blockName'] ?? '');
    $element = find_by_class($doc, $block_class_name) ?? root_element($doc);

    if (!$element) {
      return $content;
    }

    // Handle block gap
    $block_styles = $attrs['style'] ?? [];
    $block_spacings = $block_styles['spacing'] ?? [];
    $block_gap = $block_spacings['blockGap'] ?? null;

    $bg = $attrs['backgroundColor'] ?? null;
    $style_bg = $attrs['style']['color']['background'] ?? null;

    if (!isset($block_gap)) {
      // If no styles to apply, return the original content
      return $content;
    }

    $block_gap_value = get_theme_css_var($block_gap);
    // add_style($element, 'gap', $block_gap_value);
    add_style($element, '--bs-gap', $block_gap_value);

    return serialize_html($doc);
}

// Register the filter
add_filter('render_block', __NAMESPACE__ . '\\render_block_with_block_gap', 9, 2);
