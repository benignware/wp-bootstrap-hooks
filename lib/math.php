<?php

namespace benignware\wp\bootstrap_hooks;

function gcd($a, $b) {
  if ($a == 0 || $b == 0) {
    return 0;
  }

  if ($a == $b) {
    return $a;
  }

  if ($a > $b) {
    return gcd($a - $b, $b);
  }

  return gcd($a, $b - $a);
}

function ratio($a, $b) {
  $gcd = gcd($a, $b);
  $ra = $a / $gcd;
  $rb = $b / $gcd;

  return $a > $b ? array($ra, $rb) : array($b, $ra);
}
