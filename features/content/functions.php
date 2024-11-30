<?php

namespace benignware\wp\bootstrap_hooks;

function the_content($content) {
  $content = the_content_alerts($content);
  $content = the_content_images($content);
  $content = the_content_tables($content);
  $content = the_content_forms($content);
  $content = the_content_buttons($content);
}
