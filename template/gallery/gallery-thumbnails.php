<?php $column_width = 72 ?>
<div class="position-relative" style="height: 100px; ">
  <div
    <?php foreach (array_merge($attrs, [
      'id' => $id . '-thumbnails',
      'class' => 'mt-2 d-grid column-gap-2 overflow-hidden position-absolute',
      'style' => "left:0; right: 0; width: 100%; grid-template-columns: repeat(auto-fill, minmax({$column_width}px, 1fr)); grid-template-rows: 1fr; grid-auto-rows: 0;"
    ]) as $name => $value): ?>
      <?= $name ?>="<?= $value ?>"
    <?php endforeach; ?>
  >
  <?php while( have_posts()) : the_post() ?>
    <?= wp_get_attachment_image(get_the_ID(), 'small', false, [
      'loading' => 'eager',
      'decoding' => 'sync',
      'class' => "figure-img img-fluid m-0 h-100 object-fit-cover" . ($wp_query->current_post === 0 ? ' active' : ''),
      'style' => "aspect-ratio: 1/1; border-radius: var(--bs-border-radius); cursor: pointer; height: 100%; max-height: 90px; width: auto",
      'data-bs-target' => "#$id-carousel",
      'data-bs-slide-to' => $wp_query->current_post
    ]) ?>
  <?php endwhile; ?>
  </div>
</div>

<style>
  #<?= $id ?>-thumbnails > * {
    opacity: 0.6;
    transition: opacity 0.25s ease;
  }

  #<?= $id ?>-thumbnails > .active {
    opacity: 1;
  }
</style>
<script>
  (() => {
    const COLUMN_WIDTH = <?= $column_width ?>;
    const thumbnails = document.getElementById('<?= $id ?>-thumbnails');

    const resize = () => {
      const style = window.getComputedStyle(thumbnails);
      const g = parseFloat(style.getPropertyValue('column-gap'));
      const w = thumbnails.parentNode.clientWidth + g;
      const a = Math.floor(w / (COLUMN_WIDTH + g));
      const c = thumbnails.childElementCount;
      const x = w / a - g;
  
      thumbnails.style.gridTemplateColumns = `repeat(${Math.max(c, a)}, minmax(${x}px, 1fr))`;
    };

    window.addEventListener('resize', resize);

    resize();

    const carousel = document.getElementById('<?= $id ?>-carousel');

    carousel.addEventListener('slide.bs.carousel', event => {
      const items = [...thumbnails.children];
      const slideIndex = event.to;
      const itemIndex = Math.max(0, event.to - 2);
      const item = items[itemIndex];

      const bi = item.getBoundingClientRect();
      const bp = thumbnails.getBoundingClientRect();
      const x = thumbnails.scrollLeft + bi.left - bp.left;

      items.forEach((item, index) => {
        item.classList.toggle('active', slideIndex === index);
      });

      thumbnails.scrollTo({
        left: x,
        behavior: 'smooth'
      });
    });
    
  })();
</script>