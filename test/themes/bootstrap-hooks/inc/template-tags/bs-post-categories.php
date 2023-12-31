<?php

function bs_post_categories() {
  ?>
   <ul class="breadcrumb m-0">
    <li class="breadcrumb-item">
      <?php	the_category( '</li><li class="breadcrumb-item">', 'multiple', $post->ID); ?>
    </li>
    <li class="breadcrumb-item">
      <?php the_title(); ?>
    </li>
  </ul>
  <?php
}
