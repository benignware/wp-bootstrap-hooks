<?php

namespace benignware\bootstrap_hooks\util\math {
  function gcd($a, $b) {
    // if ($q == 0) return $q == 0 ? $p : wp_bootstrap_gcd($q, $p % $q);
    // Everything divides 0
    if ($a == 0 || $b == 0) {
      return 0;
    }
  
    // base case
    if ($a == $b) {
      return $a;
    }
  
    // a is greater
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
}