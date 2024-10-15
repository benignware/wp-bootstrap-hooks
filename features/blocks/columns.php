<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_columns($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/columns') {
    return $content;
  }

  $name = $block['blockName'];
  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

  remove_class($container, 'is-layout-flex');
  remove_class($container, 'is-not-stacked-on-mobile');
  $columns = isset($block['innerBlocks']) ? $block['innerBlocks'] : [];
  $column_count = count($columns);

  $row = $doc->createElement('div');

  add_style($container, 'display', 'flow');

  $isStackedOnMobile = 1;
  $breakpoint = 'md';

  if (isset($attrs['isStackedOnMobile'])) {
    $isStackedOnMobile = $attrs['isStackedOnMobile'] ? 1 : 0;
  }

  $style = isset($column_attrs['style']) ? $attrs['style'] : [];
  $spacing = isset($column_attrs['spacing']) ? $attrs['spacing'] : [];
  $blockGap = isset($column_attrs['blockGap']) ? $attrs['blockGap'] : null;
  $blockGapValue = null;

  if ($blockGap !== null) {
    $blockGapValue = get_theme_css_var($blockGap);
    
    add_style($row, '--bs-gutter-y', $blockGapValue);
    add_style($row, '--bs-gutter-x', $blockGapValue);
  } else {
    add_class($row, 'g-4');
  }

  add_class($row, 'row');

  $i = 0;

  foreach ($container->childNodes as $child) {
    if ($child->nodeType === 1) {
      $column = isset($columns[$i]) ? $columns[$i] : null;
      $column_attrs = $column ? $column['attrs'] : [];

      $class = $child->getAttribute('class');

      if ($isStackedOnMobile) {
        remove_class($child, 'col');
        add_class($child, 'col-12');
      }

      $width = isset($column_attrs['width']) ? $column_attrs['width'] : '';

      if ($width) {
        [$value, $unit] = preg_split('/(?<=[0-9])(?=[a-z%])/', $width);

        if ($unit === '%') {
          $size = $width / 100 * 12;
          $grid_size = round($width / 100 * 12);

          if (abs($grid_size - $size)) {
            $class = sprintf($options['column_class'], $grid_size, $breakpoint);
            add_class($child, $class);
            remove_style($child, 'flex-basis');
          }
        } else {
          $abs_width = "calc($width + var(--bs-gutter-x))";

          add_style($child, 'flex-basis', $abs_width);
          add_class($child, 'flex-grow-0');
        }
      }

      if (!preg_match('/col-md/', $class)) {
        add_class($child, 'col-md');
      }
      
      $i++;
    }
  }

  if (isset($block['attrs']['verticalAlignment'])) {
    $classes[] = sprintf('align-items-%s', $block['attrs']['verticalAlignment']);
  }


  while ($container->hasChildNodes()) {
    $row->appendChild($container->firstChild);
  }

  $container->appendChild($row);

  return serialize_html($doc);
}