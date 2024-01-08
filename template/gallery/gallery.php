<div
  <?php foreach (array_merge($attrs, [
    'id' => $id,
  ]) as $name => $value): ?>
    <?= $name ?>="<?= $value ?>"
  <?php endforeach; ?>
>
  <div class="row g-4">
    <?php while( have_posts()) : the_post() ?>
      <div class="col-md-<?= intval(12 / $columns) ?> flex-grow-1">
        <figure
          class="figure m-0 position-relative w-100 <?= $fit ? ' h-100' : ''; ?>"
          <?php if ($fullscreen !== false): ?>
            data-bs-toggle="modal"
            data-bs-target="#<?= $id ?>-modal"
          <?php endif; ?>
        >
          <?= wp_get_attachment_image(get_the_ID(), $size, false, [
            'class' => 'figure-img img-fluid w-100 m-0' . ($fit ? " object-fit-$fit h-100" : ''),
            'style' => "border-radius: var(--bs-border-radius);" . ($fullscreen ? 'cursor: pointer; ' : ''),
            'data-bs-target' => "#$id-fullscreen-carousel",
            'data-bs-slide-to' => $wp_query->current_post,
            'loading' => 'lazy'
          ]) ?>
          <?php if ($caption = wp_get_attachment_caption()): ?>
            <figcaption class="figure-caption position-absolute bottom-0 mb-2 w-100 text-center">
              <?= $caption ?>
            </figcaption>
          <?php endif; ?>
        </figure>
      </div>
    <?php endwhile; ?>
  </div>
  <?php include __DIR__ . '/gallery-modal.php' ?>
</div>
