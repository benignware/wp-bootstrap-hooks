<?php
namespace benignware\wp\bootstrap_hooks;
?>
<div
  <?php foreach (array_merge($attrs, [
    'id' => $id,
  ]) as $name => $value): ?>
    <?= $name ?>="<?= $value ?>"
  <?php endforeach; ?>
>
<div class="gallery-inner position-relative h-100">
    
    <div class="position-relative d-inline-flex">
      <?php if (count($wp_query->posts) > 0 && $post = $wp_query->posts[0]): ?>
        <?= wp_get_attachment_image($post->ID, $size, false, [
          'class' => 'invisible img-fluid bootstrap-gallery-sizer',
        ]) ?>
      <?php endif ?>
      <div
        id="<?= $id ?>-carousel"
        class="carousel slide h-100 rounded overflow-hidden position-absolute top-0 start-0 w-100"
        data-bs-ride="<?= $autoplay ? 'carousel' : 'false'; ?>"
        data-bs-interval="<?= $interval ?>"
      >
        
        <div class="carousel-inner h-100">
          <?php while( have_posts()) : the_post() ?>
            <div class="carousel-item h-100<?= $wp_query->current_post === 0 ? ' active' : '' ?>">
              <div
                class="position-relative w-100 h-100 overflow-hidden"
                <?php if ($lightbox): ?>
                  data-bs-toggle="modal"
                  data-bs-target="#<?= $id ?>-modal"
                  style="cursor: pointer"
                <?php endif; ?>
              >
                <div class="position-relative w-100 h-100"
                  <?php if ($lightbox): ?>
                    data-bs-target="#<?= $id ?>-lightbox-carousel"
                    data-bs-slide-to="<?= $wp_query->current_post ?>"
                  <?php endif; ?>
                >
                  <?= wp_get_attachment_image(get_the_ID(), $size, false, [
                    'class' => 'img-fluid m-0 w-100 h-100 position-relative',
                    'style' => "object-fit: $fit; object-position: center",
                    'loading' => 'lazy',
                  ]) ?>
                </div>
              </div>
              <?php if ($caption = wp_get_attachment_caption()): ?>
                <div class="carousel-caption d-none d-md-block text-light">
                  <div class="container">
                    <p><?= $caption ?></p>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        </div>
        
        <?php if ($autoplay): ?>
          <button
            class="carousel-control-play position-absolute z-2 start-0 bottom-0 p-1 m-2 lh-1<?= !$autoplay ? ' is-paused' : '' ?>"
            style="width: 1.5rem"
            data-bs-target="#<?= $id ?>-carousel"
            data-bs-toggle="play"
          >
            <span class="carousel-icon-play">
              <?= apply_filters('bootstrap_icon', '<i class="fst-normal font-monospace">▶</i>', 'play') ?>
            </span>
            <span class="carousel-icon-pause">
              <?= apply_filters('bootstrap_icon', '<i class="fst-normal font-monospace">⏸</i>', 'pause') ?>
            </span>
          </button>
        <?php endif ?>
        <?php if ($lightbox): ?>
          <button
            class="carousel-control-action position-absolute end-0 bottom-0 z-3 m-2 lh-1"
            data-bs-toggle="modal"
            data-bs-target="#<?= $id ?>-modal"
          >
            <?= get_icon('search', [
              'class' => 'carousel-control-action-icon',
            ]) ?>
          </button>
        <?php endif ?>
      </div>
    </div>
    

    <?php if ($wp_query->post_count > 1): ?>
      <button class="carousel-control-prev" type="button" data-bs-target="#<?= $id ?>-carousel" data-bs-slide="prev">
        <!-- <span class="carousel-control-prev-icon" aria-hidden="true"></span> -->
          <?php echo get_icon('chevron-left', [
            'tag' => 'span',
            'class' => 'carousel-control-prev-icon',
          ]) ?>
        <span class="visually-hidden">Previous</span>
      </button>
       <div class="carousel-indicators">
        <?php while( have_posts()) : the_post() ?>
          <button
            data-bs-target="#<?= $id ?>-carousel"
            data-bs-slide-to="<?= $wp_query->current_post; ?>"
            class="<?= $wp_query->current_post === 0 ? 'active' : '' ?>"
          ></button>
        <?php endwhile; ?>
      </div>
      <button class="carousel-control-next" type="button" data-bs-target="#<?= $id ?>-carousel" data-bs-slide="next">
        <!-- <span class="carousel-control-next-icon" aria-hidden="true"></span> -->
        <?php echo get_icon('chevron-right', [
            'tag' => 'span',
            'class' => 'carousel-control-next-icon',
          ]) ?>
        <span class="visually-hidden">Next</span>
      </button>
    <?php endif ?>
  </div>
  
  <?php if ($thumbnails !== false && $wp_query->post_count > 1): ?>
    <?php include __DIR__ . '/gallery-thumbnails.php' ?>
  <?php endif ?>


  <?php if ($lightbox): ?>
    <?php include __DIR__ . '/gallery-lightbox.php' ?>
  <?php endif ?>
</div>