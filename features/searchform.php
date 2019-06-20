<?php

/**
 * Get Search Form
 * TODO: Template
 */

add_filter( 'get_search_form', function( $form ) {
  extract(wp_bootstrap_options());

  $form = '<form role="search" method="get" id="searchform" class="searchform ' . $search_form_class . '" action="' . home_url( '/' ) . '" >
  <label class="screen-reader-text" for="s">' . __( 'Search for:' ) . '</label>
  <div class="' . $input_group_class . '">
    <input class="' . $text_input_class . '" type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="'. esc_attr__( 'Search' ) .'..."/>
    <span class="' . $input_group_append_class . '">
      <button class="' . $submit_button_class . '" type="submit" id="searchsubmit" title="' . esc_attr_x( 'Search', 'submit button' ) . '">' . $search_submit_label . '</button>
    </span>
  </div>
</form>';
  return $form;
});
