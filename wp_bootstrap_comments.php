<?php

require('wp_bootstrap_commentwalker.php');
/*
function wpbsx_comment_form_default_fields() { 

  $commenter = wp_get_current_commenter();
  $req = get_option( 'require_name_email' );

  return array (

    'author' =>
      '<div class="form-group">
        <label for="author">' . __("Name",'jamedo-bootstrap-start-theme')
       . ($req ? " (". __("required",'jamedo-bootstrap-start-theme') .")" : '')
       . ' </label>
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
          <input class="form-control" type="text" name="author" id="author" value="'
          . esc_attr($commenter['comment_author']). '" placeholder="'
          . __("Your Name",'jamedo-bootstrap-start-theme'). '"'
          . ($req ? ' required aria-required="true"' : ''). '/>'
        . '</div>'
        . '</div>',

    'email' =>
      '<div class="form-group">
        <label for="email">' . __("Email",'jamedo-bootstrap-start-theme')
       . ($req ? " (". __("required",'jamedo-bootstrap-start-theme') .")" : '')
       . ' </label>
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
          <input class="form-control" type="email" name="email" id="email" value="'
          . esc_attr($commenter['comment_author_email']). '" placeholder="'
          . __("Your Email",'jamedo-bootstrap-start-theme'). '"'
          . ($req ? ' required aria-required="true"' : ''). '/>'
        . '</div>'
        . '<span class="help-block">'. __("will not be published",'jamedo-bootstrap-start-theme'). '</span>'
        . '</div>',

    'url' => '<div class="form-group">
        <label for="author">' . __("Website",'jamedo-bootstrap-start-theme')
       . ' </label>
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
          <input class="form-control" type="url" name="url" id="url" value="'
          . esc_attr($commenter['comment_author_url']). '" placeholder="'
          . __("Your Website",'jamedo-bootstrap-start-theme'). '"'
          . '/>'
        . '</div>'
        . '</div>',
    
    
    'comment' =>  '
      <div class="comment-form-comment form-group">
        <label for="comment" class="control-label col-sm-2">Comment</label>
        <div class="col-sm-10">
          <textarea id="comment" name="comment" class="form-control" rows="5" aria-required="true"></textarea>
        </div>
      </div>',

    // bootstrap markup for the allowed tags notes
    'notes' => '
      <div class="comment-form-comment form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <p class="form-allowed-tags">' . sprintf(__('You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s'), allowed_tags()) . '</p>
        </div>
      </div>'
    );
}


add_filter( 'comment_form_default_fields', 'wpbsx_comment_form_default_fields');

*/


// http://www.codecheese.com/2013/11/wordpress-comment-form-with-twitter-bootstrap-3-supports/
// http://bassjobsen.weblogs.fm/wordpress-theming-comment_form-call-power-less/

//add_filter( 'comment_form_default_fields', 'bootstrap3_comment_form_fields' );

function wpbsx_comment_form_default_fields( $fields ) {
    
  $commenter = wp_get_current_commenter();
  $req = get_option( 'require_name_email' );
  $aria_req = ( $req ? " aria-required='true'" : '' );
  $html5 = current_theme_supports( 'html5', 'comment-form' ) ? 1 : 0;
  
  $fields = array(
    'author' => 
      '<div class="form-group comment-form-author">' .
        '<label for="author">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<div class="input-group">' .  
          '<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>' . 
          '<input class="form-control" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' />' .
        '</div>' .
      '</div>',
        
    'email' => 
      '<div class="form-group comment-form-email">' . 
        '<label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<div class="input-group">' .  
          '<span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>' . 
          '<input class="form-control" id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' />' .
        '</div>' . 
      '</div>',
       
    'url' => 
      '<div class="form-group comment-form-url">' .
        '<label for="url">' . __( 'Website' ) . '</label> ' .
        '<div class="input-group">' .
          '<span class="input-group-addon"><i class="glyphicon glyphicon-link"></i></span>' . 
          '<input class="form-control" id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />' .
         '</div>' .
      '</div>',
      
  );
  return $fields;
}


add_filter( 'comment_form_default_fields', 'wpbsx_comment_form_default_fields');


function wpbsx_comment_form_defaults( $args ) {
  $args['comment_field'] = 
    '<div class="form-group comment-form-comment">
      <label for="comment">' . _x( 'Comment', 'noun' ) . '</label>
      <textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
    </div>';
    
  $args['comment_notes_after'] = $args['comment_notes_after'];
  return $args;
}

    
    
add_filter( 'comment_form_defaults', 'wpbsx_comment_form_defaults' );


    
function wpbsx_comment_form_after() {
  echo 
    "<script>\n" .
    "  (function($) {\n" . 
    "    $('#commentform input#submit').addClass('btn btn-primary');\n" . 
    "  })(jQuery)\n" . 
    "</script>\n";
}

add_action('comment_form_after', 'wpbsx_comment_form_after' );



/* Comments */

function wp_bootstrap_list_comments_args($args) {
  
  $args = array_merge($args, array(
    'style' => 'div'
  ));
  
  if (empty($args['walker'])) {
    $args['walker'] = new wp_bootstrap_commentwalker();
  }
  return $args;
}

add_action('wp_list_comments_args', 'wp_bootstrap_list_comments_args' );


// add bootstrap classes to the comment reply link
if(!function_exists('wpbs_comment_reply_link_filter'))
{
  function wp_bootstrap_comment_reply_link($link, $args, $comment, $post) {
    return $link = '<div class="form-group">' . str_replace("class='comment-reply-", "class='comment-reply- btn btn-default btn-xs ", $link) . '</div>';
  }
}
add_filter('comment_reply_link', 'wp_bootstrap_comment_reply_link', 10, 4);
