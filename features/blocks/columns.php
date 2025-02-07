<?php

namespace benignware\wp\bootstrap_hooks;

function calculate_column_classes($column_count, $prefix = 'col', $base = 12) {
  $breakpoints = [
      'xs' => min($column_count, 2),
      'sm' => min($column_count, 2),
      'md' => min($column_count, 2),
      'lg' => min($column_count, 4),
      'xl' => $column_count
  ];

  $breakpoint_values = array_map(function ($value) use ($base) {
      return $base < 0 ? $value : round($base / $value);
  }, $breakpoints);

  $breakpoint_classes = array_map(function ($value, $key) use ($prefix) {
      return "$prefix-$key-$value";
  }, $breakpoint_values, array_keys($breakpoint_values));
  
  $class = implode(' ', $breakpoint_classes);
  
  return $class;
}


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
  
  $container = find_by_class($doc, 'wp-block-columns');

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
  remove_class($row, '~^is-layout-');

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

        if ($value && $unit === '%') {
          $value = floatval($value);
          $size = $value / 100 * 12;
          $grid_size = round($value / 100 * 12);

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
        $column_classes = calculate_column_classes($column_count);
        add_class($child, $column_classes);
      }

      if (has_class($child, '~^is-vertically-aligned-~')) {
        $vertical_alignment_class = find_class($child, '~^is-vertically-aligned-~');
        
        $vertical_alignment = preg_replace('/^is-vertically-aligned-/', '', $vertical_alignment_class);

        if (has_class($child, 'is-layout-flow') || has_class($child, 'is-layout-constrained')) {
          if ($vertical_alignment === 'stretch') {
            $vertical_alignment = 'between';
          }

          add_class($child, sprintf('justify-content-%s', $vertical_alignment));
          add_class($child, 'd-flex flex-column');
        } else {
          add_class($child, 'd-flex');
          add_class($child, sprintf('align-items-%s', $vertical_alignment));
        }

        remove_class($child, '~^is-vertically-aligned-~');
      }

      remove_class($child, 'has-global-padding');
      
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

  remove_class($container, 'is-layout-flex');

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_columns', 100000, 2);