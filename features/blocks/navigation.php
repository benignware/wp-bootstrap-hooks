<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_navigation($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/navigation') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $doc_xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  // Content class used to identify the navigation container
  $content_class = 'wp-block-navigation__responsive-container';
  // Close button class used to identify the close button
  $close_class = 'wp-block-navigation__responsive-container-close';
  // Toggler button class used to identify the toggler button
  $button_class = 'wp-block-navigation__responsive-container-open';

  $button = find_by_class($container, $button_class);
  $nav_content = find_by_class($container, $content_class);
  $menu = $doc_xpath->query(".//ul", $container)->item(0);

  // Remove the close button
  
  $close = find_by_class($container, $close_class);

  if ($close) {
    $close->parentNode->removeChild($close);
  }
  
  // Navbar
  add_class($container, 'navbar');
  // remove_class($container, 'is-layout-flex');
  add_style($container, 'gap', '0.5rem');

  // Process the menu
  if ($menu) {
    add_class($menu, 'nav navbar-nav');
    $walker = get_block_nav_walker($doc);
    $walker($menu);
  }

  if ($nav_content) {
    $collapse_id = $nav_content->getAttribute('id');

    // Class to apply to the navigation content
    $nav_content_class = 'collapse navbar-collapse';

    // Apply a filter to modify the content container class
    $nav_content_class = apply_filters('bootstrap_navbar_modal_class', $nav_content_class, $attrs);

    // add_class($nav_content, $nav_content_class);
    $children = iterator_to_array($nav_content->childNodes);

    foreach ($children as $child) {
      if ($child->nodeType !== 1) {
        continue;
      }
      
      $child->setAttribute('data-nav-content', '');
      add_style($child, 'display', 'contents');
      
      $modal_content = $doc_xpath->query(sprintf('//*[@id="%s"]', "$collapse_id-content"), $child)->item(0);

      if ($modal_content) {
        $content_fragment = $doc->createDocumentFragment();
        
        foreach ($modal_content->childNodes as $modal_child) {
          $content_fragment->appendChild($modal_child->cloneNode(true));
        }

        remove_all_children($child);
        $child->appendChild($content_fragment);
      }
    }

    // Extract the inner HTML of the nav content
    // $nav_content_html = '<div data-nav-content>' . get_inner_html($nav_content) . '</div>';
    $nav_content_html = get_inner_html($nav_content);

    $navbar_content_template = '<div id="%1$s" class="%2$s">%3$s</div>';

    // Apply a filter to allow modification of the entire content container HTML
    $navbar_content_template = apply_filters('bootstrap_navbar_modal_template', $navbar_content_template, $attrs);

    // Prepare a content container template with placeholders
    $navbar_content_html = sprintf(
      $navbar_content_template,
      esc_attr($collapse_id),
      esc_attr($nav_content_class),
      $nav_content_html // Preserving the inner HTML
    );

    // Apply a filter to allow modification of the entire content container HTML
    $navbar_content_html = apply_filters('bootstrap_navbar_modal_html', $navbar_content_html, $attrs);

    // Load the filtered content template as an HTML fragment
    $fragment = $doc->createDocumentFragment();
    $fragment->appendXML($navbar_content_html);

    // Replace the original content with the modified content
    $nav_content->parentNode->replaceChild($fragment, $nav_content);
  }

  $overlayMenu = isset($attrs['overlayMenu']) ? $attrs['overlayMenu'] : 'mobile';

  if ($overlayMenu !== 'always') {
    add_class($container,
      $overlayMenu === 'never'
        ? 'navbar-expand'
        : 'navbar-expand-md'
    );
  }

  // Determine if an icon should be shown based on the 'hasIcon' attribute in $attrs
  $has_icon = isset($attrs['hasIcon']) ? $attrs['hasIcon'] : true;

  if ($button) {
    // Default button content based on 'hasIcon'
    $button_content = $has_icon ? '<span class="navbar-toggler-icon"></span>' : __('Menu', 'text-domain');

    // Class to apply to the button (can be extended or customized)
    $button_class = 'navbar-toggler collapsed';

    // Apply a filter to allow modifying the button class
    $button_class = apply_filters('bootstrap_navbar_toggler_class', $button_class, $attrs);

    // Default toggle type
    $toggle_type = 'collapse';

    // Apply a filter to allow modifying the toggle type, either 'collapse' or 'offcanvas'
    $toggle_type = apply_filters('bootstrap_navbar_modal_type', $toggle_type, $attrs);

    // Prepare the entire button HTML as a template with indexed placeholders
    $button_template = '<button class="%1$s" data-bs-toggle="%2$s" data-bs-target="#%3$s">%4$s</button>';

    // Apply a filter to allow modifying the button content or markup
    $button_template = apply_filters('bootstrap_navbar_toggler_template', $button_template, $attrs);

    $button_html = sprintf(
      $button_template,
      esc_attr($button_class),
      esc_attr($toggle_type),
      esc_attr($collapse_id),
      $button_content
    );

    // Load the filtered template as an HTML fragment
    $fragment = $doc->createDocumentFragment();
    $fragment->appendXML($button_html);

    // Insert the new button before the first child in the container
    $button->parentNode->insertBefore($fragment, $button);

    // Remove the old button
    $button->parentNode->removeChild($button);
  }

  $navs = find_all_by_class($container, 'nav');
  
  foreach ($navs as $nav) {
    add_class($nav, 'navbar-nav');
  }

  // Remove wp-block-navigation class
  remove_class($container, '~^wp-block-navigation~', true);
  remove_class($container, 'is-responsive', true);

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_navigation', 10, 2);
