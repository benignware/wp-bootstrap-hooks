<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_group_navbar($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/group') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  if (!has_class($container, 'navbar')) {
    return $content;
  }

  // Get nested navbars
  $nested_navbars = find_all_by_class($container, 'navbar');

  if (count($nested_navbars) === 0) {
    return $content;
  }

  // Determine the main navbar
  $nav = $nested_navbars[0];

  // Copy the navbar- classes over to the container
  $navbar_classes = array_values(array_filter(array_map('trim', explode(' ', $nav->getAttribute('class'))), function($class) {
    return strpos($class, 'navbar') === 0;
  }));

  add_class($container, $navbar_classes);

  // Get the toggler
  $toggler = find_by_class($nav, 'navbar-toggler');

  if (!$toggler) {
    return $content;
  }

  // Get the target modal
  $target = $toggler->getAttribute('data-bs-target');
  $modal_id = preg_replace('/^#/', '', $target);
  $modal = $doc->getElementById($modal_id);

  if (!$modal) {
    return $content;
  }

  // Get the inner element
  $inner_elem = $xpath->query("//*[@data-nav-content]")->item(0);

  if (!$inner_elem) {
    return $content;
  }

  // Get the modal entry
  $modal_entry = $inner_elem->parentNode;
  
  // Clone all nested navbars as a fragment
  $fragment = $doc->createDocumentFragment();

  foreach ($nested_navbars as $nested_navbar) {
    $copy = $nested_navbar->cloneNode(true);
    $nested_fragment = $doc->createDocumentFragment();

    remove_class($copy, '~^navbar~');

    // Remove the toggler
    $nested_toggler = find_by_class($copy, 'navbar-toggler');

    if ($nested_toggler) {
      $nested_modal_id = preg_replace('/^#/', '', $nested_toggler->getAttribute('data-bs-target'));

      if ($nested_modal_id) {
        $nested_modal = $xpath->query("//*[@id='$nested_modal_id']", $copy)->item(0);

        if ($nested_modal) {
          $nested_modal_content_items = $xpath->query(".//*[@data-nav-content]", $nested_modal);
  
          foreach ($nested_modal_content_items as $item) {
            $nested_fragment->appendChild($item->cloneNode(true));
          }
        }
      }
    }

    remove_all_children($copy);

    $copy->appendChild($nested_fragment);

    $fragment->appendChild($copy);
  }

  // Clear out the entry's content
  remove_all_children($modal_entry);

  // Append the fragment to the entry
  $modal_entry->appendChild($fragment);

  // Get the wrapper element
  $wrapper = find_by_class($container, 'container');

  if (!$wrapper) {
    $wrapper = $container;
  }

  // Add style to the wrapper
  add_style($wrapper, 'row-gap', '0px');
  
  // Insert modal before the main navbar
  $nav->parentNode->insertBefore($modal, $nav);

  // Insert the toggler before the modal
  $modal->parentNode->insertBefore($toggler, $modal);

  // Remove the original navbars
  foreach ($nested_navbars as $nested_navbar) {
    $nested_navbar->parentNode->removeChild($nested_navbar);
  }

  remove_class($container, 'is-layout-flex');
  remove_class($container, 'has-global-padding');

  return serialize_html($doc);

  add_class($collapse, 'collapse navbar-collapse');
  remove_class($collapse, 'is-layout-flex');

  foreach ($nested_navbars as $index => $nested_navbar) {
    $nested_navbar_classes = array_values(array_filter(array_map('trim', explode(' ', $nested_navbar->getAttribute('class'))), function($class) {
      return strpos($class, 'navbar') === 0;
    }));

    add_class($container, $nested_navbar_classes);

    foreach ($nested_navbar_classes as $class) {
      remove_class($nested_navbar, $class, true);
    }

    $nested_toggler = find_by_class($nested_navbar, 'navbar-toggler');

    if ($nested_toggler) {
      if (!$toggler) {
        $toggler = $nested_toggler;
        $target = $toggler->getAttribute('data-bs-target');
        $collapse->setAttribute('id', preg_replace('/^#/', '', $target));

        $collapse->parentNode->insertBefore($toggler, $collapse);
      } else {
        $nested_toggler->parentNode->removeChild($nested_toggler);
      }
    }

    $nested_collapse = find_by_class($nested_navbar, 'navbar-collapse');

    if ($nested_collapse) {
      remove_class($nested_collapse, 'navbar-collapse', true);
      remove_class($nested_collapse, 'collapse', true);
      $nested_collapse->removeAttribute('id');

      if (!$collapse->parentNode) {
        $nested_navbar->parentNode->insertBefore($collapse, $nested_navbar);
      }
    }

    remove_class($nested_navbar, 'is-layout-flex');
    
    $collapse->appendchild($nested_navbar);
  }

  try {
    $collapse->parentNode->insertBefore($toggler, $collapse);
  } catch (\Exception $e) {
    // ignore
  }
  
  

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_group_navbar', 10, 2);
