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
  $container = find_by_class($doc, 'wp-block-navigation');

  if (!$container) {
    return $content;
  }

  // print_r($block);
  // echo '<br/>';
  // echo '<br/>';

  $menu_id = $attrs['ref'] ?? null;
  $block_menu = get_block_menu_by_id($menu_id);

  $content_class = 'wp-block-navigation__responsive-container';
  $close_class = 'wp-block-navigation__responsive-container-close';
  $button_class = 'wp-block-navigation__responsive-container-open';

  $button = find_by_class($container, $button_class);
  $nav_content = find_by_class($container, $content_class);

  $close = find_by_class($container, $close_class);

  if ($close) {
    $close->parentNode->removeChild($close);
  }
  
  add_class($container, 'navbar');
  add_style($container, 'gap', '0.5rem');
  
  $menus = $doc_xpath->query(".//ul[not(ancestor::ul)]", $container);

  foreach($menus as $menu) {
    $walker = get_block_nav_walker($doc, $block_menu);
    $walker($menu);

    if (!has_class($menu, 'dropdown-menu')) {
      add_class($menu, 'nav navbar-nav');
    }
  }

  $openSubmenusOnClick = isset($attrs['openSubmenusOnClick']) ? $attrs['openSubmenusOnClick'] : false;

  if (!$openSubmenusOnClick) {
    $submenus = find_all_by_class($container, 'dropdown');

    foreach ($submenus as $submenu) {
      add_class($submenu, 'dropdown-hover');
    }
  }

  $is_vertical = has_class($container, 'is-vertical');

  $overlayMenu = isset($attrs['overlayMenu']) ? $attrs['overlayMenu'] : 'mobile';
  $breakpoint = null;

  if ($overlayMenu !== 'always') {
    $breakpoint = $is_vertical ? 'md' : 'md';
    add_class($container,
      $overlayMenu === 'never'
        ? 'navbar-expand'
        : 'navbar-expand-' . $breakpoint
    );
  }

  $is_expand = preg_match('/\bnavbar-expand-(\w+)\b/', $container->getAttribute('class'));

  $attrs = array_merge($attrs, [
    'isVertical' => $is_vertical,
    'isExpand' => $is_expand,
    'breakpoint' => $breakpoint,
    'overlayMenu' => $overlayMenu,
  ]);

  // $nav_class = "d-flex gap-2";
  $nav_class = "";

  // if ($is_vertical || $is_expand) {
  //   $nav_class.= ' flex-column align-items-start';
  // }
  
  if ($is_expand && !$is_vertical) {
    $nav_class.= " flex-row align-items-$breakpoint-center"; // flex-$breakpoint-row 
  }

  if ($nav_content) {
    $collapse_id = $nav_content->getAttribute('id');
    $nav_modal_class = apply_filters(
      'bootstrap_navbar_modal_class',
      'collapse navbar-collapse',
      $attrs
    );

    $nav_content_class = apply_filters(
      'bootstrap_navbar_content_class',
      $nav_class,
      $attrs
    );

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

    $menu_title = $block_menu ? $block_menu['post_title'] : __('Navigation', 'bootstrap-hooks');

    $nav_content_attrs = array_merge($attrs, [
      'id' => $collapse_id,
      'class' => $nav_modal_class,
      'content_class' => $nav_content_class,
      'menu_title' => $menu_title
    ]);
    $nav_content_html = get_inner_html($nav_content);
    $navbar_content_template = '<div id="%1$s" class="%2$s %4$s">%3$s</div>';
    $navbar_content_template = apply_filters(
      'bootstrap_navbar_modal_template',
      $navbar_content_template,
      $nav_content_attrs
    );
    
    $navbar_content_html = sprintf(
      $navbar_content_template,
      esc_attr($collapse_id),
      esc_attr($nav_modal_class),
      $nav_content_html,
      esc_attr($nav_content_class),
      $menu_title
    );

    $navbar_content_html = apply_filters(
      'bootstrap_navbar_modal_html',
      $navbar_content_html,
      $nav_content_attrs
    );
    $fragment = get_html_fragment($doc, $navbar_content_html);
    $nav_content->parentNode->replaceChild($fragment, $nav_content);
  }

  // Determine if an icon should be shown based on the 'hasIcon' attribute in $attrs
  $has_icon = isset($attrs['hasIcon']) ? $attrs['hasIcon'] : true;

  if ($button) {
    $button_content = $has_icon ? '<span class="navbar-toggler-icon"></span>' : __('Menu', 'text-domain');
    $button_class = 'navbar-toggler collapsed';
    $button_class = apply_filters('bootstrap_navbar_toggler_class', $button_class, $attrs);
    $toggle_type = 'collapse';
    $toggle_type = apply_filters('bootstrap_navbar_modal_type', $toggle_type, $attrs);
    $button_template = '<button class="%1$s" data-bs-toggle="%2$s" data-bs-target="#%3$s">%4$s</button>';
    $button_template = apply_filters('bootstrap_navbar_toggler_template', $button_template, $attrs);

    $button_html = sprintf(
      $button_template,
      esc_attr($button_class),
      esc_attr($toggle_type),
      esc_attr($collapse_id),
      $button_content
    );

    $fragment = get_html_fragment($doc, $button_html);
    $button->parentNode->insertBefore($fragment, $button);
    $button->parentNode->removeChild($button);
  }

  $navs = find_all_by_class($container, 'nav');

  foreach ($navs as $nav) {
    add_class($nav, 'navbar-nav ' . $nav_class);
  }

  $flex = find_all_by_class($doc, 'is-layout-flex');

  foreach ($flex as $item) {
    add_class($item, $nav_class);
    remove_class($item, 'is-layout-flex');
  }

  remove_class($container, '~^wp-block-navigation~', true);
  remove_class($container, 'is-responsive', true);

  $output = serialize_html($doc);

  return $output;
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_navigation', 100, 2);


function navbar_modal_template_offcanvas($template, $attrs = []) {
  $options = wp_bootstrap_options();
  $modal_type = $options['navbar_modal_type'] ?? 'collapse';

  if ($modal_type !== 'offcanvas') {
    return $template;
  }

  $template = $options['navbar_modal_template_offcanvas'] ??
    '<div id="%1$s" class="%2$s" tabindex="-1" aria-labelledby="offcanvasNavbarLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Navigation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body %4$s">%3$s</div>
    </div>';

  return $template;
}

add_filter('bootstrap_navbar_modal_template', 'benignware\wp\bootstrap_hooks\navbar_modal_template_offcanvas', 10, 2);