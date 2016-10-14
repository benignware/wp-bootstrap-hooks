<?php

/**
 * Get Bootstrap Gallery Options
 */
function wp_bootstrap_get_gallery_options() {
  return apply_filters( 'bootstrap_gallery_options', array(
    // Options to be defined
  ));
}

/**
 * Post Gallery
 */
function wp_bootstrap_post_gallery($output, $attr) {
  
    global $post;
    
    $options = wp_bootstrap_get_gallery_options();

    if (isset($attr['orderby'])) {
        $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
        if (!$attr['orderby'])
            unset($attr['orderby']);
    }

    extract(shortcode_atts(array(
        'order' => 'ASC',
        'orderby' => 'menu_order ID',
        'id' => $post->ID,
        'itemtag' => 'dl',
        'icontag' => 'dt',
        'captiontag' => 'dd',
        'columns' => 3,
        'size' => 'thumbnail',
        'include' => '',
        'exclude' => ''
    ), $attr));
    
    $id = intval($id);
    if ('RAND' == $order) $orderby = 'none';
    if (!empty($include)) {
        $include = preg_replace('/[^0-9,]+/', '', $include);
        $_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));

        $attachments = array();
        foreach ($_attachments as $key => $val) {
            $attachments[$val->ID] = $_attachments[$key];
        }
    }

    if (empty($attachments)) return '';
    
    $gallery_id = uniqid();
    
    $col_value = floor(12 / $columns);
    // Here's your actual output, you may customize it to your need
    $output = "<div class=\"gallery card card-block\" id=\"gallery-$gallery_id\">\n";
    $output.= "  <div class=\"row\">\n";
    // Now you loop through each attachment
    foreach ($attachments as $id => $attachment) {
      $img_thumb = wp_get_attachment_image_src($id, $size);
      $output.= "   <div class=\"col-md-$col_value col-sm-$col_value col-xs-$col_value\">";
      $output.= "     <a class=\"thumbnail thumbnail-gallery\" data-id=\"$id\">\n";
      // Fetch the thumbnail (or full image, it's up to you)
      $output.= "       <img src=\"{$img_thumb[0]}\" title=\"{$attachment->post_excerpt}\" alt=\"{$attachment->post_excerpt}\" />\n";
      $output.= "     </a>\n";
      $output.= "   </div>\n";
    }
    $output .= "  </div>\n";
    $output .= "</div>\n";
    
    $output.= "<div class=\"modal modal-carousel modal-gallery fade modal-fullscreen force-fullscreen\" id=\"modal-gallery-$gallery_id\" role=\"dialog\">";
    $output.= '  <div class="modal-dialog modal-lg">';
    $output.= '    <div class="modal-content">';
    $output.= '      <div class="modal-header">';
    $output.= '        <button class="close" type="button" data-dismiss="modal">×</button>';
    $output.= '        <h3 class="modal-title"></h3>';
    $output.= '      </div>';
    $output.= '      <div class="modal-body">';
    $output.= "        <div id=\"carousel-gallery-$gallery_id\" class=\"carousel slide carousel-fit\" data-ride=\"carousel\">";
    $output.= '          <ol class="carousel-indicators">';
    $image_index = 0;
    foreach ($attachments as $id => $attachment) {
      $output.= "          <li data-target=\"#carousel-gallery-$gallery_id\" data-slide-to=\"$image_index\" data-id=\"$id\"></li>";
      $image_index++;
    };
    $output.= '          </ol>';
    $output.= '          <div class="carousel-inner">';
    $image_index = 0;
    foreach ($attachments as $id => $attachment) {
      $img_large = wp_get_attachment_image_src($id, 'large');
      $active = $image_index === 0 ? 'active' : '';
      $output.= "          <div class=\"carousel-item $active\" data-id=\"$id\">";
      $output.= "            <img src=\"{$img_large[0]}\" title=\"{$attachment->post_excerpt}\" alt=\"{$attachment->post_excerpt}\" />\n";
      //$output.= '            <div class="carousel-caption">';
      //$output.= "              {$attachment->post_excerpt}";
      //$output.= '            </div>';
      $output.= '          </div>';
      $image_index++;
    }
    $output.= '          </div>';
    $output.= "          <a class=\"carousel-control left\" href=\"#carousel-gallery-$gallery_id\" data-slide=\"prev\"><i class=\"icon-left icon-prev\"></i></a>";
    $output.= "          <a class=\"carousel-control right\" href=\"#carousel-gallery-$gallery_id\" data-slide=\"next\"><i class=\"icon-right icon-next\"></i></a>";
    $output.= '        </div>';
    $output.= '      </div>';
    $output.= '      <div class="modal-footer">';
    $output.= '        <button class="btn btn-default" data-dismiss="modal">Close</button>';
    $output.= '      </div>';
    $output.= '    </div>';
    $output.= '  </div>';
    $output.= '</div>';
    // Script
    $output.= "<script>\n";
    $output.= "(function($) {\n";
    $output.= "  var\n";
    $output.= "    galleryId = '$gallery_id',\n";
    $output.= "    \$gallery = $('#gallery-' + galleryId),\n";
    $output.= "    \$modal = $('#modal-gallery-' + galleryId),\n";
    $output.= "    \$carousel = $('#carousel-gallery-' + galleryId);\n";
    $output.= "    \$gallery.find('a.thumbnail[data-id]').on('ontouchend' in window ? 'touchend' : 'click', function(event) {\n";
    $output.= "      \$carousel.find('.carousel-item').removeClass('active');\n";
    $output.= "      \$carousel.find('.carousel-item').removeClass('next');\n";
    $output.= "      \$carousel.find('.carousel-item').removeClass('left');\n";
    $output.= "      \$carousel.find('.carousel-indicators li').removeClass('active');\n";
    $output.= "      var\n";
    $output.= "        itemId = $(this).data('id');\n"; 
    $output.= "        \$carousel.find(\".carousel-item[data-id='\" + itemId + \"']\").addClass('active');\n";
    $output.= "        \$carousel.find(\".carousel-indicators li[data-id='\" + itemId + \"']\").addClass('active');\n";
    $output.= "      \$modal.find('.modal-title').html(\$carousel.find('.active img').attr('title'));";
    $output.= "      \$modal.modal('show');\n";
    $output.= "    });\n";
    $output.= "    \$carousel.on('slid.bs.carousel', function () {\n";
    $output.= "    \$modal.find('.modal-title').html(\$carousel.find('.active img').attr('title'));";
    $output.= "    });\n";
    $output.= "    console.log('script: ', \$gallery, \$modal, \$carousel);";
    $output.= "})(jQuery)";
    $output.= "</script>";
    return $output;
}
add_filter( 'post_gallery', 'wp_bootstrap_post_gallery', 10, 2 );
?>