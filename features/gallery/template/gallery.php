<?php
$columns = min($columns, $wp_query->post_count);
$breakpoints = [
  'row-cols-1', // XS (default)
  'row-cols-sm-' . min($columns, 2), // SM: max 2
  'row-cols-md-' . min($columns, 3), // MD: max 3
  'row-cols-lg-' . min($columns, 4), // LG: max 4
  'row-cols-xl-' . $columns // XL: full column count
];

$column_class = implode(' ', $breakpoints);
?>
<div
  <?php foreach (array_merge($attrs, [
    'id' => $id,
  ]) as $name => $value): ?>
    <?= $name ?>="<?= $value ?>"
  <?php endforeach; ?>
>
  <div class="row row-cols-lg-<?= $columns ?> g-4">
    <?php while( have_posts()) : the_post() ?>
      <div class="col">
        <figure
          class="figure w-100 d-inline-flex flex-column m-0 position-relative <?= $fit ? ' h-100' : ''; ?>"
          <?php if ($lightbox): ?>
            data-bs-toggle="modal"
            data-bs-target="#<?= $id ?>-modal"
          <?php endif; ?>
        >
            <div class="figure-img position-relative h-100">
              <?= wp_get_attachment_image(get_the_ID(), $size, false, [
                'class' => 'w-100 img-fluid' . ($fit ? " h-100 object-fit-$fit" : ''),
                'style' => "border-radius: var(--bs-border-radius);" . ($lightbox ? 'cursor: pointer; ' : ''),
                'data-bs-target' => $lightbox ? "#$id-lightbox-carousel" : null,
                'data-bs-slide-to' => $lightbox ? $wp_query->current_post : null,
                'loading' => 'lazy'
              ]) ?>
              <?php if ($lightbox): ?>
                  <button
                    class="carousel-control-action position-absolute end-0 bottom-0 z-2 m-2 lh-1"
                    data-bs-toggle="modal"
                    data-bs-target="#<?= $id ?>-modal"
                  >
                    <?= apply_filters('bootstrap_icon', '<i>⌕</i>', 'search') ?>
                  </button>
              <?php endif ?>
            </div>
          <?php if ($caption = $captions[$wp_query->current_post] ?? (
              wp_get_attachment_caption() ?: get_the_title()
            )
          ): ?>
            <figcaption
              class="figure-caption"
            >
              <span
                style="
                    display: -webkit-box;
                    -webkit-box-orient: vertical;
                    -webkit-line-clamp: 3;
                    overflow: hidden;
                  "
              >
              <?= esc_html($caption) ?>
            </span>
            </figcaption>
          <?php endif; ?>
          
        </figure>
      </div>
    <?php endwhile; ?>
  </div>
  <?php if ($lightbox): ?>
    <?php include __DIR__ . '/gallery-lightbox.php' ?>
  <?php endif ?>
</div>