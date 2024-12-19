<?php

namespace benignware\wp\bootstrap_hooks;


function get_theme_style($mappings, $theme_json = null) {
  $theme_json = $theme_json ?: get_theme_json();
  $style = array_reduce(array_keys($mappings), function($acc, $prop) use ($theme_json, $mappings) {
		$query = $mappings[$prop];
		$queries = is_array($query) ? $query : [$query];

		$value = array_reduce($queries, function($acc, $query) use ($theme_json) {
			$value = query_object($theme_json, $query);

			if (!$acc && $value) {
				return $value;
			}

			return $acc;
		}, '');

    // echo "prop: $prop, value: $value\n";

    if ($value) {
      $acc[$prop] = $value;
    }

		return $acc;
	}, []);

  return $style;
}

function get_theme_css_rules($theme_json = null) {
  $theme_json = $theme_json ?: get_theme_json();
  $rules = $theme_json['rules'] ?? [];
  
  return $rules;
}

function get_theme_css_from_rules($rules = null) {
  $css = implode("\n", array_map(function($selector, $properties) {
    $prop_css = implode("\n", array_map(function($prop, $value) {
      return $value !== null && $value !== '' ? "\t$prop: $value;" : '';
    }, array_keys($properties), array_values($properties)));
  
    return sprintf("%s {\n%s\n}\n", $selector, $prop_css);
  }, array_keys($rules), array_values($rules)));

  return $css;
}
