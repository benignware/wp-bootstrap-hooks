<?php

include __DIR__ . '/navigation-walker.php' ;

/**
 * Nav Menu Args
 */
function wp_bootstrap_nav_menu_args($args) {
  if (!current_theme_supports('bootstrap')) {
    return $args;
  }

  $options = wp_bootstrap_options();
  $menu_class = isset($args['menu_class']) ? $args['menu_class'] : '';
  $is_default_menu_class = $menu_class === 'menu' ? true : false;

  $args['menu_class'].= ' nav';

  $navbar_locations = array(
    'primary', 'top'
  );

  // Apply .navbar-nav automatically to primary menu if no custom class has been set
  if ($is_default_menu_class && isset($args['theme_location']) && in_array($args['theme_location'], $navbar_locations)) {
    $args['menu_class'] = $args['menu_class'] . " navbar-nav";
  }

  if (empty($args['walker'])) {
    $args['fallback_cb'] = 'Bootstrap_Walker_Nav_Menu::fallback';
    $args['walker'] = new Bootstrap_Walker_Nav_Menu($options);
  }
  return $args;
}

add_filter( 'wp_nav_menu_args', 'wp_bootstrap_nav_menu_args', 10 );
add_action( 'wp_enqueue_scripts', function() {
  if (!current_theme_supports('bootstrap')) {
    return;
  }

  $options = wp_bootstrap_options();

  if (!isset($options['caret']) || !$options['caret']) {
    return;
  }

  wp_register_style( 'bootstrap-hooks-hide-pseudo-caret', '', [], '', true );
  wp_enqueue_style( 'bootstrap-hooks-hide-pseudo-caret' );
  wp_add_inline_style( 'bootstrap-hooks-hide-pseudo-caret', <<<EOT
<style>
  .menu-item.nav-item .dropdown-toggle::after {
    display: none
  }
</style>
EOT);
} );

add_action( 'wp_enqueue_scripts', function() {
  if (!current_theme_supports('bootstrap')) {
    return;
  }

  $options = wp_bootstrap_options();

  $caret_class = isset($options['caret_class']) ? $options['caret_class'] : '';

  wp_register_script( 'bootstrap-hooks-dropdown-links', '', [], '', true );
  wp_enqueue_script( 'bootstrap-hooks-dropdown-links' );
  wp_add_inline_script( 'bootstrap-hooks-dropdown-links', <<<EOT
  (() => {
    const caretSelector = '$caret_class';
    const handler = (e) => {
      console.log('HELLO CLICK');
      const target = event.target.closest('a[href].dropdown-toggle');
  
      if (!target) {
        return;
      }
  
      if (target.href.startsWith('#') || target.href.startsWith('javascript:')) {
        return;
      }
  
      const isOpen = !!target.classList.contains('show');
  
      if (isOpen) {
        return;
      }

      const isCaret = caretSelector && !!event.target.closest(caretSelector) || (() => {
        const after = getComputedStyle(target, ":after");
      
        if (after) {
          const w = Math.max(Number(after.getPropertyValue("width").slice(0, -2)), 16);
          const h = target.offsetHeight;
          const x = target.offsetWidth - w;
          const y = 0;
          const ex = e.layerX;
          const ey = e.layerY;
          
          if (ex > x && ex < x + w && ey > y && ey < y + h) {
            return true;
          }
        }
  
        return false;
      })();
  
      if (isCaret) {
        return false;
      }
  
      const hasText = [...target.childNodes].some(node => node.nodeType === 3);
  
      if (!hasText && event.target !== target || hasText && event.target === target) {
        window.location.href = target.href;
      }
    }
    window.addEventListener('click', handler);
  })();
EOT);
} );

// add_filter( 'nav_menu_item_title', function($title) {
//   return '<span>' . $title . '</span>';
// }, 2);
