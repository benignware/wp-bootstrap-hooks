<?php

namespace benignware\wp\bootstrap_hooks;

add_filter('do_shortcode_tag', 'benignware\wp\bootstrap_hooks\the_content_images');