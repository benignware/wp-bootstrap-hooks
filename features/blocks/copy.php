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
  $container = root_element($doc);

  if (!has_class($container, 'navbar')) {
    return $content;
  }

  // Determine wrapper element
  $wrapper = find_by_class($container, 'container');

  if (!$wrapper) {
    $wrapper = $container;
  }

  add_style($wrapper, 'row-gap', '0px');
  
  // Create a new collapse element add append it to the wrapper
  $collapse = $doc->createElement('div');
  $wrapper->appendChild($collapse);

  $toggler = find_by_class($container, 'navbar-toggler');

  $collapse_target = $toggler->getAttribute('data-bs-target');

  $collapse->setAttribute('id', preg_replace('/^#/', '', $target));

  $collapse->parentNode->insertBefore($toggler, $collapse);

  $navbar_collapse = 

  $nested_navbars = find_all_by_class($container, 'navbar');
  
  $toggler = null;
  
  $nav = $nested_navbars[0];

  

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
