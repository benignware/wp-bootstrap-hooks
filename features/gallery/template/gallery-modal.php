<style>
  .carousel .btn-close, [data-bs-theme=light] .carousel .btn-close {
    filter: var(--bs-btn-close-white-filter);
  }
  [data-bs-theme=dark] .carousel .btn-close {
    filter: var(--bs-btn-close-black-filter);
  }
  .carousel-slide-backdrop {
    object-fit: cover;
    object-position: center;
    transform: scale(1.7);
    transform-origin: center;
    filter: blur(70px);
  }

  .carousel-caption p {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    -webkit-line-clamp: 3; /* Number of lines to show */
  }

</style>
<div class="modal fade" id="<?= $id ?>-modal" tabindex="-1" role="dialog" aria-hidden="true" data-controls="true">
  <div class="modal-dialog modal-fullscreen" role="document">
    <div class="modal-content">
      <?php if ($title): ?>
        <div class="modal-header">
          <h5 class="modal-title"><?= $title ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <div class="modal-body p-0">
        <div
          id="<?= $id ?>-fullscreen-carousel"
          class="carousel slide h-100"
          data-bs-ride="false"
          data-bs-interval="<?= !$autoplay ? 'false' : $interval; ?>"
        >
          <?php if (!$title): ?>
            <button type="button" class="btn-close position-absolute top-0 end-0 z-3 m-4" data-bs-dismiss="modal" aria-label="Close"></button>
          <?php endif; ?>
          <?php if ($wp_query->post_count > 1): ?>
            <div class="carousel-indicators">
              <?php while( have_posts()) : the_post() ?>
                <button
                  data-bs-target="#<?= $id ?>-fullscreen-carousel"
                  data-bs-slide-to="<?= $wp_query->current_post; ?>"
                  class="<?= $wp_query->current_post === 0 ? 'active' : '' ?>"
                ></button>
              <?php endwhile; ?>
            </div>
          <?php endif ?>
          <div class="carousel-inner h-100">
            <?php while( have_posts()) : the_post() ?>
              <div class="carousel-item h-100<?= $wp_query->current_post === 0 ? ' active' : '' ?>">
                <div class="position-relative w-100 h-100 overflow-hidden">
                  <?= wp_get_attachment_image(get_the_ID(), 'large', false, [
                    'class' => 'img-fluid m-0 w-100 h-100 position-relative',
                    'style' => "object-fit: cover; object-position: center",
                    'loading' => 'lazy',
                    'data-bs-dismiss' => "modal"
                  ]) ?>
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
            <button class="carousel-control-prev" type="button" data-bs-target="#<?= $id ?>-fullscreen-carousel" data-bs-slide="prev" data-control>
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?= $id ?>-fullscreen-carousel" data-bs-slide="next" data-control>
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>
</div>