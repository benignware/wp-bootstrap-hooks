<?php

namespace benignware\bootstrap_hooks\util\colors {
  function mix($color_1 = array(0, 0, 0), $color_2 = array(0, 0, 0), $weight = 0.5) {
    $f = function ($x) use ($weight) {
      return $weight * $x;
    };

    $g = function ($x) use ($weight) {
      return (1 - $weight) * $x;
    };

    $h = function ($x, $y) {
      return round($x + $y);
    };

    return array_map($h, array_map($f, $color_1), array_map($g, $color_2));
  }

  function tint($color, $weight = 0.5) {
    $t = $color;

    if (is_string($color)) {
      $t = hex2rgb($color);
    }

    $u = mix($t, array(255, 255, 255), $weight);

    if (is_string($color)) {
      return rgb2hex($u);
    }

    return $u;
  }

  function tone($color, $weight = 0.5) {
    $t = $color;

    if (is_string($color)) {
      $t = hex2rgb($color);
    }

    $u = mix($t, array(128, 128, 128), $weight);

    if (is_string($color)) {
      return rgb2hex($u);
    }

    return $u;
  }

  function shade($color, $weight = 0.5) {
    $t = $color;

    if (is_string($color)) {
      $t = hex2rgb($color);
    }

    $u = mix($t, array(0, 0, 0), $weight);

    if (is_string($color)) {
      return rgb2hex($u);
    }

    return $u;
  }

  function hex2rgb($hex = '#000000') {
    $f = function ($x) {
      return hexdec($x);
    };

    return array_map($f, str_split(str_replace("#", "", $hex), 2));
  }

  function rgb2hex($rgb = array(0, 0, 0)) {
    $f = function ($x) {
      return str_pad(dechex($x), 2, "0", STR_PAD_LEFT);
    };

    return "#" . implode("", array_map($f, $rgb));
  }
}
