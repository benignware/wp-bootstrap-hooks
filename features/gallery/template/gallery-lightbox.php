<?php $lightbox_in = true ?>
<div
  <?php
    $attrs = $lightbox['attrs'] ?? [];
    $class = $attrs['class'] ?? '';
    foreach (array_merge($attrs, [
    'id' => "$id-modal",
    'class' => 'modal fade ' .$class,
    'tabindex' => '-1',
    'role' => 'dialog',
    'aria-hidden' => 'true',
  ]) as $name => $value): ?>
    <?= $name ?>="<?= $value ?>"
  <?php endforeach; ?>
>
  <div class="modal-dialog modal-fullscreen" role="document">
    <div class="modal-content">
      <?php if ($lightbox['header']): ?>
        <div class="modal-header">
          <h5 class="modal-title"><?= $title ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <div class="modal-body p-0">
        <div
          id="<?= $id ?>-lightbox-carousel"
          class="carousel slide h-100"
          data-bs-ride="<?= $autoplay ? 'carousel' : 'false'; ?>"
          data-bs-interval="<?= !$autoplay ? 'false' : $interval; ?>"
        >
          <?php if (!$lightbox['header']): ?>
            <button
              type="button"
              class="carousel-control-action btn-close position-absolute top-0 end-0 z-3 m-3"
              data-bs-dismiss="modal"
              aria-label="Close"
            >
              <?= apply_filters('bootstrap_icon', '<i class="fst-normal font-monospace">x</i>', 'close') ?>
            </button>
          <?php endif; ?>
          <?php if (!$lightbox['footer']): ?>
            <?php if ($autoplay): ?>
              <button
                class="carousel-control-action carousel-control-play position-absolute start-0 bottom-0 z-2 m-3<?= !$autoplay ? ' is-paused' : '' ?>"
                data-bs-target="#<?= $id ?>-lightbox-carousel"
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
            <?php if ($wp_query->post_count > 1): ?>
              <div class="carousel-indicators">
                <?php while( have_posts()) : the_post() ?>
                  <button
                    data-bs-target="#<?= $id ?>-lightbox-carousel"
                    data-bs-slide-to="<?= $wp_query->current_post; ?>"
                    class="<?= $wp_query->current_post === 0 ? 'active' : '' ?>"
                  ></button>
                <?php endwhile; ?>
              </div>
            <?php endif ?>
          <?php endif; ?>
          <div class="carousel-inner h-100">
            <?php while( have_posts()) : the_post() ?>
              <div class="carousel-item h-100<?= $wp_query->current_post === 0 ? ' active' : '' ?>">
                <div
                  class="position-relative w-100 h-100 overflow-hidden"
                >
                  <?php if ($lightbox['backdrop']): ?>
                    <?= wp_get_attachment_image(get_the_ID(), 'large', false, [
                      'class' => 'img-fluid m-0 w-100 h-100 position-absolute opacity-75',
                      'style' => "object-fit: cover; object-position: center; transform: scale(1.7); transform-origin: center; filter: blur(70px);",
                      'loading' => 'lazy',
                      'data-bs-dismiss' => "modal"
                    ]) ?>
                  <?php endif; ?>
                  <?= wp_get_attachment_image(get_the_ID(), 'large', false, [
                    'class' => 'img-fluid m-0 w-100 h-100 position-relative',
                    'style' => "object-position: center; object-fit: " . ($lightbox['fit'] ?? 'contain'),
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
            <button class="carousel-control-prev" type="button" data-bs-target="#<?= $id ?>-lightbox-carousel" data-bs-slide="prev" data-control>
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?= $id ?>-lightbox-carousel" data-bs-slide="next" data-control>
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          <?php endif ?>
        </div>
      </div>
      <?php if ($lightbox['footer']): ?>
        <div class="modal-footer justify-content-center">
          <div class="col-2">
            <?php if ($autoplay): ?>
              <button
                class="btn btn-outline-secondary<?= !$autoplay ? ' is-paused' : '' ?>"
                style="width: 2.2em"
                data-bs-target="#<?= $id ?>-lightbox-carousel"
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
          </div>
          <div class="flex-grow-1">
            <?php if ($wp_query->post_count > 1): ?>
              <div class="flex-grow-1 carousel-indicators position-relative">
                <?php while( have_posts()) : the_post() ?>
                  <button
                    data-bs-target="#<?= $id ?>-lightbox-carousel"
                    data-bs-slide-to="<?= $wp_query->current_post; ?>"
                    class="<?= $wp_query->current_post === 0 ? 'active' : '' ?>"
                  ></button>
                <?php endwhile; ?>
              </div>
            <?php endif ?>
          </div>
          <div class="col-2 text-end">
            <?php if ($download): ?>
              <!-- Download All as ZIP -->
              <?php
                $accessible_ids = array_values(array_filter($attachment_ids, function($attachment_id) {
                    return apply_filters('bootstrap_gallery_is_accessible', true, $attachment_id);
                }));
              ?>
                <div class="mt-2">
                  <div class="text-end px-2">
                      <a 
                        class="btn btn-primary <?= count($accessible_ids) === 0 ? 'disabled' : '' ?>"
                        <?php if (count($accessible_ids) === 0): ?>
                          disabled
                          title="No files are accessible"
                        <?php else: ?>
                          href="<?= admin_url('admin-ajax.php') . '?action=generate_download_zip&attachment_ids=' . implode(',', $accessible_ids) ?>"
                        <?php endif; ?>
                        download
                      >
                      <span class="ms-1 d-inline-block"><?= __('Download All', 'WooCommerce') ?></span>
                    </a>
                </div>
              </div>
            <?php endif; ?>
            <!-- <button
              class="d-none d-md-inline-block btn btn-outline-secondary"
              data-toggle="zoom"
              data-target="#<?= $id ?>-lightbox-carousel"
            >
              <?= apply_filters('bootstrap_icon', '<i>⌕</i>', 'search') ?>
            </button> -->
          </div>
        </div>
      <?php endif ?>
    </div>
  </div>
</div>