<?php

namespace benignware\wp\bootstrap_hooks;

function calculate_column_classes(
  $column_count,
  $stacked_on_mobile = 1,
  $prefix = 'col',
  $base = 12
) {
  $breakpoints = [
      'xs' => min($column_count, $stacked_on_mobile ? 1 : 2),
      'sm' => min($column_count, $stacked_on_mobile ? 1 : 2),
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
  return $content;
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

  if (!$container) {
    return $content;
  }

  remove_class($container, 'is-layout-flex');
  remove_class($container, 'is-not-stacked-on-mobile');
  $columns = isset($block['innerBlocks']) ? $block['innerBlocks'] : [];
  $column_count = count($columns);

  $row = $doc->createElement('div');

  add_style($container, 'display', 'flow-root');

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

  // add_style($row, 'margin-top', 'calc(-2 * var(--bs-gutter-y))');
  
  add_class($row, 'row');
  remove_class($row, '~^is-layout-');

  $has_custom_widths = count(array_values(array_filter($columns, function ($column) {
    return isset($column['attrs']['width']);
  }))) > 0;

  $column_elems = array_values(array_filter(iterator_to_array($container->childNodes), function ($child) {
    return $child->nodeType === 1;
  }));

  $column_classes = calculate_column_classes($column_count);

  if ($isStackedOnMobile) {
    add_class($row, 'd-block d-md-flex');
  }

  foreach ($column_elems as $i => $child) {
    $column = isset($columns[$i]) ? $columns[$i] : null;

    $has_content = is_empty($child) === false;

    if (!$has_content) {
      remove_all_children($child);
    }
    
    $column_attrs = $column ? $column['attrs'] : [];

    $class = $child->getAttribute('class');

    if (!$has_custom_widths) {
      $column_classes = calculate_column_classes($column_count, $isStackedOnMobile);
      add_class($child, $column_classes);
    } else {
      $width = $column_attrs['width'] ?? null;
      [$value, $unit] = $width ? preg_split('/(?<=[0-9])(?=[a-z%])/', $width) : [null, null];

      if ($value && is_numeric($value)) {
        $is_grid_size = false;

        if ($unit === '%') {
          $size = $value / 100 * 12;
          $grid_size = round($value / 100 * 12);
          $is_grid_size = abs($size - $grid_size) <= 0.005;
        }
        
        if ($is_grid_size) {
          $class = sprintf($options['column_class'], $grid_size, $breakpoint);
          add_class($child, $class);
          remove_style($child, 'flex-basis');
        } else {
          // $abs_width = "calc($width + var(--bs-gutter-x))";
          $abs_width = "calc($width)";

          // add_style($child, 'flex-basis', $abs_width);
          add_style($child, 'flex-basis', $abs_width);
          // add_style($child, 'min-width', 'fit-content');
          add_style($child, 'max-width', '100%');
          add_style($child, 'width', 'auto');
          add_class($child, 'flex-grow-0');
        }
      } else if ($isStackedOnMobile) {
        add_class($child, 'col-12 col-md');
      } else {
        add_class($child, 'col');
      }
    }

    if ($isStackedOnMobile) {
      add_class($child, 'is-stacked-on-mobile');
    }

    if (has_class($child, '~^is-vertically-aligned-~')) {
      $vertical_alignment_class = find_class($child, '~^is-vertically-aligned-~');
      $vertical_alignment = preg_replace('/^is-vertically-aligned-/', '', $vertical_alignment_class);
      $vertical_flex = null;

      switch ($vertical_alignment) {
        case 'top':
          $vertical_flex = 'start';
          break;
        case 'middle':
          $vertical_flex = 'center';
          break;
        case 'bottom':
          $vertical_flex = 'end';
          break;
        case 'stretch':
          $vertical_flex = 'stretch';
          break;
        case 'between':
          $vertical_flex = 'between';
          break;
        case 'around':
          $vertical_flex = 'around';
          break;
      }

      if ($vertical_flex) {
        add_style($child, 'align-self', $vertical_flex);
        add_class($child, 'd-flex flex-column');
      }

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
      // add_class($child, 'vert-test');
    }

    remove_class($child, 'has-global-padding');
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