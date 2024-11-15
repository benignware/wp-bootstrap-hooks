<?php

namespace benignware\wp\bootstrap_hooks;

add_filter('render_block_core/calendar', __NAMESPACE__ . '\\render_bootstrap_calendar', 10, 2);

function render_bootstrap_calendar($block_content, $block) {
    // Load the content into a DOMDocument to manipulate
    $doc = new \DOMDocument();
    @$doc->loadHTML($block_content);

    // Find the calendar table element
    $table = $doc->getElementsByTagName('table')->item(0);
    if ($table) {
        // Add the Bootstrap 'table' class
        add_class($table, 'table');
    }

    // Return the modified HTML
    return serialize_html($doc);
}

