<?php

namespace benignware\wp\bootstrap_hooks;

$columns = min($columns, $wp_query->post_count);
$breakpoints = [
  'row-cols-1', // XS (default)
  'row-cols-sm-' . min($columns, 2), // SM: max 2
  'row-cols-md-' . min($columns, 3), // MD: max 3
  'row-cols-lg-' . min($columns, 4), // LG: max 4
  'row-cols-xl-' . $columns // XL: full column count
];

$column_class = implode(' ', $breakpoints);

// Distribute the items evenly across the columns in a rows array
$rows = [];
for ($i = 0; $i < $wp_query->post_count; $i++) {
  $rows[$i % ($columns - 1)][] = $wp_query->posts[$i];
}


?>
<div
  <?php foreach (array_merge($attrs, [
    'id' => $id,
  ]) as $name => $value): ?>
    <?= $name ?>="<?= $value ?>"
  <?php endforeach; ?>
>
  <style>
 

  </style>
<div class=" carousel-snapper snapper-outer gallery-inner g-4">
  <div class="snapper-frame">
    <div class="snapper-wrapper">
      <div 
        id="<?= $id ?>-snapper"
        class="snapper-container"
        data-bs-interval="<?= $autoplay ? $interval : 'false' ?>"
      >
        <div class="snapper-inner">
          <div class="row grid <?= $column_class ?> snapper-row">
              <?php while (have_posts()): the_post(); ?>
                <div class="snapper-col col-auto col-md">
                  <figure
                    class="snapper-slide figure mb-0 position-relative w-100 <?= $fit ? ' h-100' : ''; ?>"
                    <?php if ($lightbox): ?>
                      data-bs-toggle="modal"
                      data-bs-target="#<?= $id ?>-modal"
                    <?php endif; ?>
                  >
                    <div class="figure-img position-relative w-100 h-100 m-0">
                      <?= wp_get_attachment_image(get_the_ID(), $size, false, [
                        'class' => 'img-fluid' . ($fit ? " w-100 h-100 object-fit-$fit" : ''),
                        'style' => "border-radius: var(--bs-border-radius);" . ($lightbox ? 'cursor: pointer; ' : ''),
                        'data-bs-target' => $lightbox ? "#$id-lightbox-carousel" : null,
                        'data-bs-slide-to' => $wp_query->current_post,
                        'loading' => 'lazy'
                      ]) ?>
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
                    <?php if ($caption = $captions[$wp_query->current_post] ?? (
                        wp_get_attachment_caption() ?: get_the_title()
                      )
                    ): ?>
                      <figcaption class="figure-caption">
                        <span style="display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden;">
                          <?= esc_html($caption) ?>
                        </span>
                      </figcaption>
                    <?php endif; ?>
                  </figure>
                </div>
              <?php endwhile; ?>
              <?php wp_reset_postdata(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

    <?php if ($wp_query->post_count > 1): ?>
      <!-- Carousel Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#<?= $id ?>-snapper" data-bs-slide="prev">
        <!-- <span class="carousel-control-prev-icon" aria-hidden="true"></span> -->
         <?php echo get_icon('chevron-left', [
          'tag' => 'span',
          'class' => 'carousel-control-prev-icon',
        ]) ?>
        <span class="visually-hidden">Previous</span>
      </button>
      

        <!-- Carousel Indicators -->
        <div class="carousel-indicators">
          <?php for ($i = 0; $i < $wp_query->post_count; $i++): ?>
            <button
              type="button"
              data-bs-target="#<?= $id ?>-snapper"
              data-bs-slide-to="<?= $i ?>"
              class="<?= $i === 0 ? 'active' : '' ?>"
              aria-current="<?= $i === 0 ? 'true' : 'false' ?>"
              aria-label="Slide <?= $i + 1 ?>"
            ></button>
          <?php endfor; ?>
        </div>

        <button class="carousel-control-next" type="button" data-bs-target="#<?= $id ?>-snapper" data-bs-slide="next">
        <!-- <span class="carousel-control-next-icon" aria-hidden="true"></span> -->
        <?php echo get_icon('chevron-right', [
          'tag' => 'span',
          'class' => 'carousel-control-next-icon',
        ]) ?>

        <span class="visually-hidden">Next</span>
      </button>
    
      <?php endif; ?>

    
  </div>
<?php if ($lightbox): ?>
      <?php include __DIR__ . '/gallery-lightbox.php' ?>
    <?php endif ?>
<script>
  (() => {
    const id = '<?= esc_js($id) ?>-snapper';

    if (!window.bootstrapHooks || !window.bootstrapHooks.Snapper) {
      console.error('Bootstrap Hooks is not loaded.');
      return;
    }
    
    const snapperEl = document.getElementById(id);
    if (!snapperEl) {
      console.error('Snapper element with ID ' + id + ' not found.');
      return;
    }

    const snapper = new bootstrapHooks.Snapper(snapperEl);
  })();
</script>