<?php

namespace benignware\wp\bootstrap_hooks;

function get_icon_class($name, $attrs = []) {
  $icon_class = sprintf('bi bi-%s', esc_html($name));
  $icon_class = apply_filters('bootstrap_icon_class', $icon_class, $name, $attrs);

  return $icon_class;
}

function get_icon($name, $attrs = [], $context = []) {
  $icon_class = get_icon_class($name, $attrs);
  
  // $class = trim(sprintf('%s %s', $icon_class, $attrs['class'] ?? ''));
  // $classes = explode(' ', $class);
  // $classes = array_values(array_unique($classes)); 
  // $class = implode(' ', $classes);
  
  // $attrs['class'] = $class;
  $attrs['aria-hidden'] = 'true';
  $attrs['role'] = 'img'; // Ensure the icon is treated as an image for accessibility.
  
  // Allow the tag name to be specified, defaulting to 'i'.
  $tag_name = isset($attrs['tag']) ? $attrs['tag'] : 'i';
  // Remove the 'tag' attribute from the attributes array.
  $attrs = array_diff_key($attrs, ['tag' => '']);

  $attrs_str = html_attributes($attrs);
  $icon = sprintf('<%s %s></%s>', $tag_name, $attrs_str, $tag_name);
  $icon = apply_filters('bootstrap_icon', $icon, $name, $attrs, $context);

  return $icon;
}