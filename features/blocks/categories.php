<?php

namespace benignware\wp\bootstrap_hooks;

function parse_categories_hierarchy($items, $type = 'category') {
  $hierarchy = [];

  // Initialize the hierarchy with parent items
  foreach ($items as $item) {
      if (!isset($hierarchy[$item->parent])) {
          $hierarchy[$item->parent] = [];
      }

      // Get the post count for the category
      $term = get_term($item->term_id, $type);
      $count = $term->count; // Term count (post count)

      $hierarchy[$item->parent][] = (object) [
          'term_id'  => $item->term_id,
          'label'    => $item->name,
          'url'      => $item->url,
          'parent'   => $item->parent,
          'count'    => $count, // Include the post count
          'children' => []
      ];
  }

  // Populate children for each parent item
  foreach ($hierarchy as $parent_id => &$parent_items) {
      foreach ($parent_items as &$item) {
          if (isset($hierarchy[$item->term_id])) {
              $item->children = $hierarchy[$item->term_id];
          }
      }
  }

  // Ensure that top-level items are at the top
  return isset($hierarchy[0]) ? $hierarchy[0] : [];
}


function populate_children_recursively(&$item, $items) {
    foreach ($items as $possible_child) {
        if ($possible_child->parent === $item->term_id) {
            // Add child to the `children` property
            $item->children[] = (object) [
                'term_id'  => $possible_child->term_id,
                'label'    => $possible_child->name,
                'url'      => $possible_child->url,
                'parent'   => $possible_child->parent,
                'children' => []  // Recursively add children if needed
            ];

            // Recurse for this child
            populate_children_recursively(end($item->children), $items);
        }
    }
}

function render_block_categories($content, $block) {
    if (!current_theme_supports('bootstrap') || $block['blockName'] !== 'core/categories') {
        return $content;
    }

    $attrs = $block['attrs'];
    $doc = parse_html($content);
    $categories = get_categories(['hide_empty' => false]);
    $items = parse_categories_hierarchy($categories);

    $numberOfItems = $attrs['numberOfItems'] ?? 10;

    $items = array_slice($items, 0, $numberOfItems);
    
    return render_block_with_structure($doc, 'wp-block-categories', $attrs, $items);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_categories', 10, 2);
