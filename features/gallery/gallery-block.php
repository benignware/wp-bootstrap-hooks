<?php
namespace benignware\wp\bootstrap_hooks;
 
function render_block_gallery($html, $block = null) {
  if ($block['blockName'] === 'core/gallery') {
    $attrs = $block['attrs'];
    $captions = [];

    foreach ($block['innerBlocks'] as $index => $inner_block) {
      if (isset($inner_block['innerHTML'])) {
        $inner_html = $inner_block['innerHTML'];
        $doc = new \DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $inner_html);
        $doc_xpath = new \DOMXpath($doc);
        $fig_caption = $doc_xpath->query("//figcaption")->item(0);
        $captions[$index] = $fig_caption ? $fig_caption->textContent : '';
      } else {
        $captions[$index] = '';
      }
    }
  
    if (isset($attrs['ids'])) {
      $ids = is_array($attrs['ids']) ? $attrs['ids'] : implode(',', $attrs['ids']);
    } else if (isset($block['innerBlocks'])) {
      $ids = [];
      $inner_blocks = $block['innerBlocks'];

      foreach ($inner_blocks as $index => $item) {
        if (isset($item['attrs']) and isset($item['attrs']['id'])) {
          $id = $item['attrs']['id'];
          array_push($ids, $id);
        }
      }
    }

    $ids = is_array($ids) ? implode(',', $ids) : $ids;

    $doc = new \DOMDocument();
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $doc_xpath = new \DOMXpath($doc);
    
    $block_element = find_by_class($doc, 'wp-block-gallery');
    $container = $block_element ?? root_element($doc);

    if (!$container) {
      return $html;
    }

    $id = $container->getAttribute('id');

    remove_class($container, 'wp-block-gallery');
    remove_class($container, 'is-layout-flex');

    $gallery_caption_class = 'blocks-gallery-caption';
    $gallery_caption = $doc_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $gallery_caption_class ')]")->item(0);

    $title = '';

    if ($gallery_caption) {
      $title = $gallery_caption->textContent;
    }

    $columns = isset($attrs['columns']) && !empty($attrs['columns']) ? $attrs['columns'] : 3;
    $size = isset($attrs['sizeSlug']) ? $attrs['sizeSlug'] : 'large';
    $fit = !isset($attrs['imageCrop']) || $attrs['imageCrop'] ? 'cover' : false;

    $align = isset($attrs['align']) ? $attrs['align'] : '';
    $class = isset($attrs['className']) ? $attrs['className'] : $container->getAttribute('class');

    $attrs = get_attributes($container);

    unset($attrs['id']);
    unset($attrs['class']);

    $html = bootstrap_gallery([
      'id' => $id,
      'ids' => $ids,
      'class' => $class,
      'columns' => $columns,
      'title' => $title,
      'size' => $size,
      'captions' => $captions,
      'fit' => $fit,
      'align' => $align,
      'attrs' => $attrs
    ]);
  }

  return $html;
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_gallery', 10, 2);
