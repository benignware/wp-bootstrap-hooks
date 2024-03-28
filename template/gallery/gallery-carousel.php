<div class="d-grid">
  <div class="position-relative d-inline-block">
    <?php if (count($wp_query->posts) > 0 && $post = $wp_query->posts[0]): ?>
      <?= wp_get_attachment_image($post->ID, $size, false, [
        'class' => 'invisible w-100 img-fluid border border-danger',
      ]) ?>
    <?php endif ?>
    <div
      id="<?= $id ?>-carousel"
      class="carousel slide h-100 rounded overflow-hidden position-absolute top-0 start-0 w-100"
      data-bs-ride="false"
      data-bs-interval="<?= !$autoplay ? 'false' : $interval; ?>"
    >
      <div class="carousel-indicators">
        <?php while( have_posts()) : the_post() ?>
          <button
            data-bs-target="#<?= $id ?>-carousel"
            data-bs-slide-to="<?= $wp_query->current_post; ?>"
            class="<?= $wp_query->current_post === 0 ? 'active' : '' ?>"
          ></button>
        <?php endwhile; ?>
      </div>
      <div class="carousel-inner h-100">
        <?php while( have_posts()) : the_post() ?>
          <div class="carousel-item h-100<?= $wp_query->current_post === 0 ? ' active' : '' ?>">
            <div
              class="position-relative w-100 h-100 overflow-hidden"
              <?php if ($fullscreen !== false): ?>
                data-bs-toggle="modal"
                data-bs-target="#<?= $id ?>-modal"
                style="cursor: pointer"
              <?php endif; ?>
            >
              <div class="position-relative w-100 h-100"
                <?php if ($fullscreen !== false): ?>
                  data-bs-target="#<?= $id ?>-fullscreen-carousel"
                  data-bs-slide-to="<?= $wp_query->current_post ?>"
                <?php endif; ?>
              >
                <?= wp_get_attachment_image(get_the_ID(), $size, false, [
                  'class' => 'img-fluid m-0 w-100 h-100 position-absolute opacity-75',
                  'style' => "object-fit: cover; object-position: center; transform: scale(1.7); transform-origin: center; filter: blur(70px);",
                  'loading' => 'lazy',
                  'data-bs-dismiss' => "modal"
                ]) ?>
                <?= wp_get_attachment_image(get_the_ID(), $size, false, [
                  'class' => 'img-fluid m-0 w-100 h-100 position-relative',
                  'style' => "object-fit: $fit; object-position: center",
                  'loading' => 'lazy',
                  'data-bs-dismiss' => "modal"
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
      <button class="carousel-control-prev" type="button" data-bs-target="#<?= $id ?>-carousel" data-bs-slide="prev" data-control>
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#<?= $id ?>-carousel" data-bs-slide="next" data-control>
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>
  
  <?php if ($thumbnails !== false): ?>
    <?php include __DIR__ . '/gallery-thumbnails.php' ?>
  <?php endif ?>

  <?php if ($fullscreen !== false): ?>
    <?php include __DIR__ . '/gallery-modal.php' ?>

    <script>
      (() => {
        const carouselEl = document.getElementById('<?= $id ?>-carousel');
        const fsCarouselEl = document.getElementById('<?= $id ?>-fullscreen-carousel');

        fsCarouselEl.addEventListener('slide.bs.carousel', event => {
          const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
          console.log('CAROUSEL: ', carousel);

          carousel.to(event.to);
        });
      })();
    </script>
  <?php endif ?>
</div>