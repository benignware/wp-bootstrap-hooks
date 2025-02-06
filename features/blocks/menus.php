<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_with_structure($doc, $block_class, $attrs, $hierarchy) {
    $block_element = find_by_class($doc, $block_class);
    $show_posts_count = !empty($attrs['showPostCounts']);
    $display_as_dropdown = !empty($attrs['displayAsDropdown']);
    $hierarchical = false;

    if ($display_as_dropdown) {
        $dropdown = render_dropdown($doc, $hierarchy, $show_posts_count, $hierarchical);
        $block_element->parentNode->replaceChild($dropdown, $block_element);
    } else {
        $list_group = render_list_group($doc, $hierarchy, $show_posts_count, $hierarchical);
        $block_element->parentNode->replaceChild($list_group, $block_element);
    }

    return serialize_html($doc);
}

function render_dropdown($doc, $hierarchy, $show_posts_count, $hierarchical = false) {
    $dropdown = $doc->createElement('div');
    add_class($dropdown, 'dropdown');

    $unique_id = 'navbarDropdownMenuLink_' . uniqid();
    $toggle = $doc->createElement('a', __('Select', 'textdomain'));
    add_class($toggle, 'btn btn-secondary dropdown-toggle');
    $toggle->setAttribute('href', '#');
    $toggle->setAttribute('id', $unique_id);
    $toggle->setAttribute('role', 'button');
    $toggle->setAttribute('data-bs-toggle', 'dropdown');
    $toggle->setAttribute('aria-expanded', 'false');
    $dropdown->appendChild($toggle);

    $menu = $doc->createElement('ul');
    add_class($menu, 'dropdown-menu' . ($hierarchical ? ' is-hierarchical' : ''));
    $menu->setAttribute('aria-labelledby', $unique_id);

    // Render dropdown items based on hierarchy setting
    $menu->appendChild(render_dropdown_items($doc, $hierarchy, $show_posts_count, $hierarchical));
    $dropdown->appendChild($menu);

    return $dropdown;
}

function render_dropdown_items($doc, $hierarchy, $show_posts_count, $hierarchical = false, $level = 0) {
    $fragment = $doc->createDocumentFragment();

    foreach ($hierarchy as $item) {
        $list_item = $doc->createElement('li');
        $link = $doc->createElement('a');
        $link->setAttribute('href', $item->url);
        add_class($link, 'dropdown-item');

        // Apply padding for non-hierarchical (flat) view
        if (!$hierarchical && $level > 0) {
            add_style($link, 'padding-left', "calc(1rem + {$level}rem)");
        }

        $label = $doc->createElement('span', $item->label);
        add_class($label, 'flex-grow-1');
        $link->appendChild($label);

        if ($show_posts_count && isset($item->count)) {
            $badge = $doc->createElement('span', $item->count);
            add_class($badge, 'badge text-bg-primary rounded-pill ms-2');
            $link->appendChild($badge);
        }

        $list_item->appendChild($link);

        // Handle children based on hierarchical setting
        if ($hierarchical && !empty($item->children)) {
            add_class($list_item, 'dropend');
            add_class($link, 'dropdown-toggle');
            $link->setAttribute('data-bs-toggle', 'dropdown');
            $link->setAttribute('aria-expanded', 'false');

            $submenu = $doc->createElement('ul');
            add_class($submenu, 'dropdown-menu dropdown-submenu');
            $submenu->appendChild(render_dropdown_items($doc, $item->children, $show_posts_count, $hierarchical, $level + 1));
            $list_item->appendChild($submenu);
        } elseif (!$hierarchical && !empty($item->children)) {
            // Flat view: render children in the main menu with indentation
            foreach ($item->children as $child) {
                $fragment->appendChild(render_dropdown_items($doc, [$child], $show_posts_count, $hierarchical, $level + 1));
            }
        }

        $fragment->appendChild($list_item);
    }

    return $fragment;
}


function render_list_group($doc, $hierarchy, $show_posts_count, $hierarchical) {
    // Create the list group wrapper
    $list_group_wrapper = $doc->createElement('div');
    add_class($list_group_wrapper, 'list-group');
    add_style($list_group_wrapper, 'margin-block-start', '0');

    // Render each item in the hierarchy
    foreach ($hierarchy as $parent_item) {
        $list_group_wrapper->appendChild(render_list_group_item($doc, $parent_item, $show_posts_count, 0, $hierarchical));
    }

    return $list_group_wrapper;
}

function render_list_group_item($doc, $parent_item, $show_posts_count, $level = 0, $hierarchical = false) {
    $fragment = $doc->createDocumentFragment();

    $url = $parent_item->url ?? '#';

    // Render the current item (category)
    $link = $doc->createElement('a');
    $link_text = $doc->createTextNode($parent_item->label);
    $link->appendChild($link_text);
    add_class($link, 'list-group-item list-group-item-action d-flex justify-content-between align-items-center');
    
    $link->setAttribute('href', $url);

    // Indentation for nested categories
    if ($level > 0) {
        add_style($link, 'padding-left', "calc(var(--bs-list-group-item-padding-x) + {$level}rem)");
    }

    // Badge for post count if applicable
    if ($show_posts_count && isset($parent_item->count)) {
        $badge = $doc->createElement('span', $parent_item->count);
        add_class($badge, 'badge text-bg-primary rounded-pill');
        $link->appendChild($badge);
    }

    $fragment->appendChild($link);

    // Render children (subcategories) if hierarchical
    if ($hierarchical && !empty($parent_item->children)) {
        foreach ($parent_item->children as $child_item) {
            $fragment->appendChild(render_list_group_item($doc, $child_item, $show_posts_count, $level + 1, $hierarchical));
        }
    }

    return $fragment;
}
