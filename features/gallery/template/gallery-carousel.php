<div
  <?php foreach (array_merge($attrs, [
    'id' => $id,
  ]) as $name => $value): ?>
    <?= $name ?>="<?= $value ?>"
  <?php endforeach; ?>
>
  <div class="position-relative d-inline-flex">
    <?php if (count($wp_query->posts) > 0 && $post = $wp_query->posts[0]): ?>
      <?= wp_get_attachment_image($post->ID, $size, false, [
        'class' => 'invisible img-fluid border border-danger bootstrap-gallery-sizer',
      ]) ?>
    <?php endif ?>
    <div
      id="<?= $id ?>-carousel"
      class="carousel slide h-100 rounded overflow-hidden position-absolute top-0 start-0 w-100"
      data-bs-ride="<?= $autoplay ? 'carousel' : 'false'; ?>"
      data-bs-interval="<?= $interval ?>"
    >
      <?php if ($wp_query->post_count > 1): ?>
        <div class="carousel-indicators">
          <?php while( have_posts()) : the_post() ?>
            <button
              data-bs-target="#<?= $id ?>-carousel"
              data-bs-slide-to="<?= $wp_query->current_post; ?>"
              class="<?= $wp_query->current_post === 0 ? 'active' : '' ?>"
            ></button>
          <?php endwhile; ?>
        </div>
      <?php endif ?>
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
      <?php if ($wp_query->post_count > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#<?= $id ?>-carousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#<?= $id ?>-carousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      <?php endif ?>
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
          class="btn carousel-control position-absolute end-0 bottom-0 p-1 z-2 m-2 lh-1"
          style="width: 1.5rem"
          data-bs-toggle="modal"
          data-bs-target="#<?= $id ?>-modal"
        >
          <?= apply_filters('bootstrap_icon', '<i>⌕</i>', 'search') ?>
        </button>
      <?php endif ?>
    </div>
  </div>
  
  <?php if ($thumbnails !== false && $wp_query->post_count > 1): ?>
    <?php include __DIR__ . '/gallery-thumbnails.php' ?>
  <?php endif ?>

  <?php if ($lightbox): ?>
    <?php include __DIR__ . '/gallery-lightbox.php' ?>
  <?php endif ?>
</div>