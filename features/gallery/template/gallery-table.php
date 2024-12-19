<?php
// Define table classes for styling
$table_classes = [
    'table',
    // 'table-bordered',  // Optional for adding borders
    'table-hover',     // Adds hover effects
    'align-middle',    // Ensures vertical alignment is centered
];
$attachment_ids = [];
?>
<div
    <?php foreach (array_merge($attrs, ['id' => $id]) as $name => $value): ?>
        <?= $name ?>="<?= $value ?>"
    <?php endforeach; ?>
>
    <table class="<?= implode(' ', $table_classes) ?>" style="cursor: pointer;">
        <thead>
            <tr>
                <th scope="col" style="width: 120px;">Thumbnail</th>
                <th scope="col" class="text-start" style="width: 100%;">Title</th>
                <th scope="col" class="text-end" style="width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while (have_posts()): the_post(); ?>
                <?php
                $attachment_id = get_the_ID();
                $attachment_ids[] = $attachment_id;
                $is_accessible = apply_filters('bootstrap_gallery_is_accessible', true, $attachment_id);
                ?>
                <tr
                  <?php if ($is_accessible): ?>
                    data-bs-toggle="modal"
                    data-bs-target="#<?= $id ?>-modal"
                  <?php endif; ?>
                >
                    <!-- Thumbnail -->
                    <td
                        data-bs-target="#<?= $id ?>-lightbox-carousel"
                        data-bs-slide-to="<?= $wp_query->current_post ?>"
                        class="align-middle"
                    >
                        <figure class="m-0">
                            <?= wp_get_attachment_image($attachment_id, $size, false, [
                                'class' => 'img-thumbnail',
                                'alt' => get_the_title(),
                                'loading' => 'lazy',
                                'style' => 'max-width: 100px; height: auto;', // Constrain thumbnail size
                            ]) ?>
                        </figure>
                    </td>

                    <!-- Title -->
                    <td class="align-middle" style="white-space: nowrap;">
                        <div><?= esc_html(get_the_title()) ?></div>
                    </td>

                    <!-- Actions -->
                    <td class="text-end align-middle" style="width: 120px;">
                        <div class="d-flex justify-content-end">
                            <!-- View Button -->
                            <button 
                                class="btn btn-primary btn-sm me-1"
                                data-bs-target="#<?= $id ?>-lightbox-carousel"
                                data-bs-slide-to="<?= $wp_query->current_post ?>"
                                <?php if (!$is_accessible): ?>
                                    disabled
                                    title="This file is not accessible"
                                <?php endif; ?>
                            >
                                <?= apply_filters('bootstrap_icon', '<i class="fst-normal font-monospace">⌕</i>', 'eye search') ?>
                            </button>

                            <!-- Download Button -->
                            <a 
                                
                                class="btn btn-secondary btn-sm <?= !$is_accessible ? 'disabled' : '' ?>"
                                <?php if (!$is_accessible): ?>
                                    disabled
                                    title="This file is not accessible"
                                <?php else: ?>
                                    href="<?= admin_url('admin-ajax.php') . '?action=generate_download_zip&attachment_ids=' . $attachment_id ?>" 
                                <?php endif; ?>
                                download
                            >
                              <?= apply_filters('bootstrap_icon', '<i class="fst-normal font-monospace">⇩</i>', 'download') ?>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
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
            <?= apply_filters('bootstrap_icon', '<i class="fst-normal font-monospace">⇩</i>', 'download zip') ?>
            <span class="ms-1 d-inline-block"><?= __('Download All', 'WooCommerce') ?></span>
            </a>
        </div>
      </div>
    

    <?php if (!$lightbox_in && $lightbox): ?>
        <?php include __DIR__ . '/gallery-lightbox.php' ?>
    <?php endif; ?>
</div>
