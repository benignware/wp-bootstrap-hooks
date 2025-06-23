<?php
namespace benignware\wp\bootstrap_hooks;

require_once "gallery-block.php";

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
  
  $defaults = [
		'type' => '',
		'format' => '',
    'id' => 'bootstrap-gallery-' . $__bootstrap_gallery_instance,
    'title' => '',
    // 'class' => '',
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
    // 'post_mime_type' => 'image',
    // 'ids' => is_array($params['ids']) ? implode(',', $params['ids']) : $params['ids'],
    'size' => 'medium',
    'fit' => 'cover',
    'autoplay' => false,
    'interval' => 5000,
    'captions' => null,
    'align' => '',
    'attrs' => [],
    'lightbox' => true,
    'thumbnails' => true,
    'download' => false,
    'style' => [],
  ];

  // $params['type'] = 'snapper'; 

  if (is_string($params['style'])) {
    $css_text = $params['style'];
    $style = array_reduce(explode(';', trim($css_text)), function($result, $item) {
      $parts = explode(':', $item);
      $key = trim($parts[0]);
      $value = isset($parts[1]) ? trim($parts[1]) : null;
      if ($key && $value) {
        $result[$key] = $value;
      }
      return $result;
    }, []);
    
    $params['style'] = $style;
  }


  $params = apply_filters('bootstrap_gallery_args', $params);
	$params = array_merge($defaults, $params);
  $classes = $params['class'] ? explode(' ', $params['class']) : [];
  $classes = array_merge(
    ['gallery', 'mb-4'],
    $classes
  );
  $classes = array_diff($classes, ['is-layout-flex']);
  $params['class'] = implode(' ', $classes);

  $params['title'] = $params['title'] ?: get_the_title();
  $params['lightbox'] = $params['lightbox'] === 'false' ? false : $params['lightbox'];
  $params['lightbox'] = $params['lightbox'] ? array_merge([
    'fit' => 'contain',
    'backdrop' => true,
    'header' => true,
    'footer' => true,
  ], is_array($params['lightbox']) ? $params['lightbox'] : []) : null;

  $params['thumbnails'] = $params['thumbnails'] === 'false' ? false : $params['thumbnails'];

  $template_locations = [
    get_template_directory() . '/bootstrap/gallery',
    get_stylesheet_directory() . '/bootstrap/gallery',
    __DIR__ . '/template'
  ];

  $type = $params['type'];
  $template_file = null;

  if (file_exists($type)) {
    $template_file = $type;
  } else {
    $template_name = $type ? sprintf('gallery-%s.php', $type) : 'gallery.php';
    $template_file = array_reduce($template_locations, function($result, $dir) use ($template_name) {
      $file = $dir . '/' . $template_name;
  
      return $result || file_exists($file) ? $file : null;
    }, null);
  }

  if (!$template_file || !file_exists($template_file)) {
    $template_file = __DIR__ . '/template/gallery.php';
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

  // Clear empty query params
  $query_params = array_filter($query_params);

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
  
  $css_text = array_reduce(array_keys($params['style']), function($result, $key) use ($params) {
    $value = $params['style'][$key] ?? null;

    if (!$key || $value === null) {
      return $result;
    }

    return $result . sprintf('%s: %s;', $key, $value);
  }, '');

  $attrs = array_merge([
    'id' => $params['id'],
    'class' => $params['class'],
    'style' => $css_text,
  ], $params['attrs']);

  $data = array_merge([
    'id' => $params['id'],
    'columns' => $params['columns'],
    'title' => $params['title'],
    'size' => $params['size'],
    'autoplay' => $params['autoplay'],
    'interval' => $params['interval'],
    'captions' => $captions,
    'wp_query' => $wp_query,
    'fit' => $params['fit'],
    'align' => $params['align'],
    'attrs' => $attrs,
    'lightbox' => $params['lightbox'],
    'thumbnails' => $params['thumbnails'],
    'download' => $params['download'],
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

function enqueue_gallery_scripts() {
  wp_enqueue_script(
    'bootstrap-gallery',
    plugins_url('gallery.js', __FILE__),
    [],
    null,
    true
  );
  wp_enqueue_script(
    'bootstrap-gallery-snapper',
    plugins_url('gallery-snapper.js', __FILE__),
    [],
    null,
    false
  );

  wp_enqueue_style(
    'bootstrap-gallery',
    plugins_url('gallery.css', __FILE__)
  );

  wp_enqueue_style(
    'bootstrap-gallery-snapper',
    plugins_url('gallery-snapper.css', __FILE__)
  );

  // Zoom, not yet fully implemented:
  // wp_enqueue_script(
  //   'bootstrap-zoom',
  //   plugins_url('zoom.js', __FILE__),
  //   [],
  //   null,
  //   true
  // );
  // wp_enqueue_style(
  //   'bootstrap-zoom',
  //   plugins_url('zoom.css', __FILE__)
  // );
}
add_action('wp_enqueue_scripts', 'benignware\wp\bootstrap_hooks\enqueue_gallery_scripts');