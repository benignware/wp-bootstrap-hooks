<?php
namespace benignware\wp\bootstrap_hooks;

require "gallery-block.php";

function render_gallery_template($template, $data = array()) {
  if (!file_exists($template)) {
    return '';
  }

  foreach($data as $key => $value) {
    $$key = $data[$key];
  }

  ob_start();
  include $template;
  $output = ob_get_contents();
  ob_end_clean();

  return $output;
}

function bootstrap_gallery($params, $content = null) {
	global $wp, $wp_query, $post, $__bootstrap_gallery_instance;

  if (!isset($__bootstrap_gallery_instance)) {
    $__bootstrap_gallery_instance = 0;
  }

  $__bootstrap_gallery_instance++;

	$params = array_merge([
		'type' => '',
		'format' => '',
    'id' => 'bootstrap-gallery-' . $__bootstrap_gallery_instance,
    'title' => '',
    'class' => 'gallery my-4',
    'columns' => 3,
		// Query params
		'ids' => null,
		'order' => 'ASC',
    'orderby' => '',
    'post_status' => 'publish',
    'post_type' => 'any',
    'post_mime_type' => null,
    'include' => '',
    'exclude' => '',
    // Gallery
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'post_mime_type' => 'image',
    // 'ids' => is_array($params['ids']) ? implode(',', $params['ids']) : $params['ids'],
    'size' => 'medium',
    'fit' => 'cover',
    'autoplay' => false,
    'interval' => 3000,
    'captions' => null,
    'align' => '',
    'attrs' => [],
    'fullscreen' => true,
    'thumbnails' => true
  ], $params);

  $params = apply_filters('bootstrap_gallery_args', $params);

  $post_title = !$params['title'] ? get_the_title() : ''; // Alternatively show post title on gallery

  $template_locations = [
    get_template_directory() . '/bootstrap/gallery',
    get_stylesheet_directory() . '/bootstrap/gallery',
    __DIR__ . '/template'
  ];

  $type = $params['type'];
  $template_name = $type ? sprintf('gallery-%s.php', $type) : 'gallery.php';
  $template_file = array_reduce($template_locations, function($result, $dir) use ($template_name) {
    $file = $dir . '/' . $template_name;

    return $result || file_exists($file) ? $file : null;
  }, null);

  if (!$template_file) {
    return 'Gallery template not found: ' . $template_name;
  }

  $params['autoplay'] = $params['autoplay'] === 'false' ? false : $params['autoplay'];

  // Parse booleans
  $params = array_map(function($value) {
		return $value === 'false' ? false : ($value === 'true' ? true : $value);
	}, $params);

  if (empty($params['id'])) {
    $params['id'] = sanitize_title(
      sprintf(
        'sitekick-gallery-%s-%s',
        $post ? $post->ID : '0',
        base64_encode(str_replace(',', '-', $params['ids']))
      )
    );
  }

  $query_params = array_intersect_key($params, array_flip([
    'order',
    'orderby',
    'include',
    'exclude',
    'post_type',
    'post_mime_type',
    'post_status',
    'nopaging',
  ]));

  // TODO: Account for include and exclude params
  if ( ! empty( $params['ids'] ) ) {
    $ids = is_array($params['ids']) ? $params['ids'] : explode(',', $params['ids']);   
    $query_params['post__in'] = $ids;
    
    if ( empty( $params['orderby'] ) ) {
      $params['orderby'] = 'post__in';
    }
  }

  if (!isset($query_params['post__in'])) {
    $query_params['posts_per_page'] = 23;
    $query_params['nopaging'] = false;
  }

  $query_params['orderby'] = $params['order'] === 'RAND' ? 'none' : $params['orderby'];

	$wp_query = new \WP_QUERY($query_params);

  if (!count($wp_query->posts)) {
    return '';
  }

  $captions = isset($params['captions'])
    ? $params['captions']
    : (
      is_array($params['include']) ? array_fill('', count( $params['include'])) : []
    );
  $captions = is_string($captions) ? explode(',', $captions) : $captions;

  foreach ($wp_query->posts as $index => $post) {
    if (!isset($captions[$index]) || !$captions[$index]) {
      $captions[$index] = wp_get_attachment_caption($post->ID);
    }
  }

  $attrs = array_merge([
    'id' => $params['id'],
    'class' => $params['class'],
  ], $params['attrs']);

  $data = array_merge([
    'id' => $params['id'],
    'columns' => $params['columns'],
    'title' => $params['title'],
    'post_title' => $post_title,
    'size' => $params['size'],
    'autoplay' => $params['autoplay'],
    'interval' => $params['interval'],
    'captions' => $captions,
    'wp_query' => $wp_query,
    'fit' => $params['fit'],
    'align' => $params['align'],
    'attrs' => $attrs,
    'fullscreen' => $params['fullscreen'],
    'thumbnails' => $params['thumbnails']
  ]);

	$output = render_gallery_template($template_file, $data);

	wp_reset_query();

	return $output;
}

function post_gallery_filter($output, $attr = []) {
  $output = bootstrap_gallery($attr ?: [], $output);

  return $output;
}

add_filter( 'post_gallery', 'benignware\wp\bootstrap_hooks\post_gallery_filter', 0, 2);
