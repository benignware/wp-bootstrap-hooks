<?php

namespace benignware\bootstrap_hooks\util\object {
  function query_object($obj, $query) {
    $keys = explode('.', $query);

    foreach ($keys as $key) {
      if (is_array($obj) && array_key_exists($key, $obj)) {
        $obj = $obj[$key];
      } else if (is_object($obj) && property_exists($obj, $key)) {
        $obj = $obj->$key;
      } else {
        return null;
      }
    }

    return $obj;
  }
}