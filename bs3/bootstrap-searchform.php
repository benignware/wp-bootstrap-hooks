<?php
/**
 * Search Form
 */
function wp_bootstrap_get_search_form( $form ) {
  $args = array(
    'submit_label' => '<i class="glyphicon glyphicon-search"></i>'
  );
  $args = apply_filters( 'wp_bootstrap_get_search_form_args', $args );
  
  $form = '<form role="search" method="get" id="searchform" class="searchform" action="' . home_url( '/' ) . '" >
  <label class="screen-reader-text" for="s">' . __( 'Search for:' ) . '</label>
  <div class="form-group">
    <div class="input-group">
    <input class="form-control" type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="'. esc_attr__( 'Search' ) .'..."/>
    <span class="input-group-btn">
      <button class="btn btn-default" type="submit" id="searchsubmit" title="' . esc_attr_x( 'Search', 'submit button' ) . '">' . $args['submit_label'] . '</button>
    </span>
    </div>
  </div>
  </form>';
  return $form;
}
add_filter( 'get_search_form', 'wp_bootstrap_get_search_form' );
?>