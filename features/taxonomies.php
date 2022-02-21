<?php
use function util\dom\add_class;

add_action('init', function() {
  $taxonomies = get_taxonomies();

  // print_r($taxonomies);

  add_filter('term_links-topic-tag', function($links) {
    $result = [];

    foreach($links as $link) {
      // Parse DOM
      $doc = new DOMDocument();
      @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $link );
      $doc_xpath = new DOMXpath($doc);
      $link = $doc_xpath->query('//a')->item(0);

      if ($link) {
        add_class($link, 'badge rounded-pill bg-primary');
      }

      $html = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

      $result[] = $html;
    }

    return $result;
  });
});

