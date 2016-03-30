<?php

require('wp_bootstrap_commentwalker.php');
// http://www.codecheese.com/2013/11/wordpress-comment-form-with-twitter-bootstrap-3-supports/
// http://bassjobsen.weblogs.fm/wordpress-theming-comment_form-call-power-less/

function wpbsx_comment_form_default_fields( $fields ) {
    
  $commenter = wp_get_current_commenter();
  $req = get_option( 'require_name_email' );
  $aria_req = ( $req ? " aria-required='true'" : '' );
  $html5 = current_theme_supports( 'html5', 'comment-form' ) ? 1 : 0;
  
  $fields = array(
    'author' => 
      '<div class="form-group comment-form-author">' .
        '<label for="author">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<input class="form-control" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' />' .
      '</div>',
        
    'email' => 
      '<div class="form-group comment-form-email">' . 
        '<label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<input class="form-control" id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' />' .
      '</div>',
       
    'url' => 
      '<div class="form-group comment-form-url">' .
        '<label for="url">' . __( 'Website' ) . '</label> ' .
        '<input class="form-control" id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />' .
      '</div>',
      
  );
  return $fields;
}

function wpbsx_comment_form_defaults( $args ) {
  $args['comment_field'] = 
    '<div class="form-group comment-form-comment">
      <label for="comment">' . _x( 'Comment', 'noun' ) . '</label>
      <textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
    </div>';
    
  $args['comment_notes_after'] = $args['comment_notes_after'];
  return $args;
}

   
function wpbsx_comment_form_after() {
  echo 
    "<script>\n" .
    "  (function($) {\n" . 
    "    $('#commentform input#submit').addClass('btn btn-primary');\n" . 
    "  })(jQuery)\n" . 
    "</script>\n";
}

 

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

// add bootstrap classes to the comment reply link
if(!function_exists('wpbs_comment_reply_link_filter'))
{
  function wp_bootstrap_comment_reply_link($link, $args, $comment, $post) {
    return $link = '<div class="form-group">' . str_replace("class='comment-reply-", "class='comment-reply- btn btn-primary btn-xs ", $link) . '</div>';
  }
}

