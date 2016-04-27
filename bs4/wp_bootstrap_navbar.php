<?php

require('wp_bootstrap_navwalker.php');

function wp_bootstrap_nav_menu_args($args) {
  // Navbar walker does only apply to primary menu
  
  $menu_class = isset($args['menu_class']) ? $args['menu_class'] . ' ' : '';
  
  $args = array_merge($args, array(
    'menu_class'        => $menu_class . 'nav'
  ));
  
  if (isset($args['theme_location']) && trim($args['theme_location']) === 'primary') {
    $args['menu_class'] = $args['menu_class'] . " navbar-nav"; 
  }
  
  if (empty($args['walker'])) {
    $args['fallback_cb'] = 'wp_bootstrap_navwalker::fallback';
    $args['walker'] = new wp_bootstrap_navwalker();
  }
  
  return $args;
}
?>