<?php

require('wp_bootstrap_navwalker.php');

function wpbsx_nav_menu_args($args) {
  
  $args = array_merge($args, array(
    //'container'         => 'div',
    //'container_class'   => 'collapse navbar-collapse',
    //'container_id'      => 'navbar-collapse',
    'menu_class'        => 'nav navbar-nav'
  ));
  
  if (empty($args['walker'])) {
    
    $args['fallback_cb'] = 'wp_bootstrap_navwalker::fallback';
    $args['walker'] = new wp_bootstrap_navwalker();
  }
  
  return $args;
}



?>