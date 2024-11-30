<?php

namespace benignware\wp\bootstrap_hooks;

function the_content_alerts($content) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  $options = wp_bootstrap_options();

  // Parse DOM
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);

  // Alerts
  $alerts = find_all_by_class($doc->documentElement, 'mu_alert', 'alert');

  if (count($alerts) === 0) {
    return $content;
  }


  // echo 'alerts<br/>';
  // echo '<textarea>';
  // echo $content;
  // echo '</textarea>';


  foreach($alerts as $alert) {
    if (!has_class($alert, 'alert')) {
      add_class($alert, 'alert-secondary');
    }
    preg_match('~\balert-(\w+)\b~', $alert->getAttribute('class'), $matches);
    $context = count($matches) ? $matches[1] : '';

    $alert_links = $xpath->query('.//a', $alert);

    foreach($alert_links as $alert_link) {
      if (!has_class($alert_link, '~btn|button~')) {
        echo 'ALERT LINK: ' . $alert_link->getAttribute('class') . '<br/>';
        add_class($alert_link, 'alert-link');
      }
    }

    // if ($context) {
    //   $alert_buttons = find_all_by_class($alert, 'btn');

    //   foreach($alert_buttons as $alert_button) {
    //     remove_class($alert_button, '~^btn-~');
    //     add_class($alert_button, "btn-$context");
    //   }
    // }
  }

  return serialize_html($doc);
}

add_filter('the_content', 'benignware\wp\bootstrap_hooks\the_content_alerts', 100);