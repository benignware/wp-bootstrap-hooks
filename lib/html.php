<?php

namespace benignware\wp\bootstrap_hooks;

function html_attributes( $attrs ) {
	if ( ! is_array( $attrs ) ) {
		return '';
	}

	$parts = [];

	foreach ( $attrs as $key => $value ) {
		if ( is_bool( $value ) ) {
			if ( $value ) {
				$parts[] = esc_html( $key );
			}
			// If false, omit attribute entirely.
		} elseif ( is_scalar( $value ) ) {
			$parts[] = sprintf(
				'%s="%s"',
				esc_html( $key ),
				esc_attr( $value )
			);
		}
		// Skip non-scalar and non-bool values.
	}

	return implode( ' ', $parts );
}
