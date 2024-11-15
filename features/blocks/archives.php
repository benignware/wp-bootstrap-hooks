<?php

namespace benignware\wp\bootstrap_hooks;

/**
 * Generate a structured archive list based on post data.
 */
function generate_archives_structure($type = 'monthly') {
    global $wpdb;
    $items = [];

    if ($type === 'monthly') {
        $results = $wpdb->get_results("
            SELECT YEAR(post_date) AS year, MONTH(post_date) AS month, COUNT(ID) as post_count 
            FROM $wpdb->posts 
            WHERE post_type = 'post' AND post_status = 'publish' 
            GROUP BY year, month 
            ORDER BY year DESC, month DESC
        ");

        foreach ($results as $result) {
            $month_name = date_i18n('F', mktime(0, 0, 0, $result->month, 10));
            $url = get_month_link($result->year, $result->month);
            $items[] = (object) [
                'label'    => esc_html($month_name . ' ' . $result->year),
                'url'      => esc_url($url),
                'count'    => intval($result->post_count),
                'children' => [] 
            ];
        }
    } else {
        $results = $wpdb->get_results("
            SELECT YEAR(post_date) AS year, COUNT(ID) as post_count 
            FROM $wpdb->posts 
            WHERE post_type = 'post' AND post_status = 'publish' 
            GROUP BY year 
            ORDER BY year DESC
        ");

        foreach ($results as $result) {
            $url = get_year_link($result->year);
            $items[] = (object) [
                'label'    => esc_html($result->year),
                'url'      => esc_url($url),
                'count'    => intval($result->post_count),
                'children' => []
            ];
        }
    }

    return $items;
}

/**
 * Render archives block with structured data.
 */
function render_block_archives($content, $block) {
    if (!current_theme_supports('bootstrap') || $block['blockName'] !== 'core/archives') {
        return $content;
    }

    $attrs = isset($block['attrs']) ? $block['attrs'] : [];
    $doc = parse_html($content);
    $archives = generate_archives_structure('monthly');

    // Output buffering to handle rendering errors
    ob_start();
    try {
        echo render_block_with_structure($doc, 'wp-block-archives', $attrs, $archives);
    } catch (\Exception $e) {
        error_log('Error rendering archives block: ' . $e->getMessage());
        ob_end_clean();
        return $content;
    }

    return ob_get_clean();
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_archives', 10, 2);
