<?php

if ( ! function_exists( 'wp_sizes_attribute_includes_valid_auto' ) ) {
	/**
	 * Polyfill for wp_sizes_attribute_includes_valid_auto().
	 *
	 * Checks if the first value in a 'sizes' attribute is 'auto'.
	 *
	 * @param string $sizes_attr The value of the 'sizes' attribute.
	 * @return bool True if the first value is 'auto', false otherwise.
	 */
	function wp_sizes_attribute_includes_valid_auto( string $sizes_attr ): bool {
		list( $first_size ) = explode( ',', $sizes_attr, 2 );
		return 'auto' === strtolower( trim( $first_size, " \t\f\r\n" ) );
	}
}


if ( ! function_exists( 'wp_print_auto_sizes_contain_css_fix' ) ) {
	/**
	 * Polyfill for wp_print_auto_sizes_contain_css_fix().
	 *
	 * Prints a CSS rule to fix potential visual issues with images using `sizes=auto`.
	 *
	 * This rule overrides the similar rule in the default user agent stylesheet, to avoid images that use e.g.
	 * `width: auto` or `width: fit-content` to appear smaller.
	 *
	 * @see https://html.spec.whatwg.org/multipage/rendering.html#img-contain-size
	 * @see https://core.trac.wordpress.org/ticket/62413
	 */
	function wp_print_auto_sizes_contain_css_fix() {
		/** This filter is documented in wp-includes/media.php */
		$add_auto_sizes = apply_filters( 'wp_img_tag_add_auto_sizes', true );
		if ( ! $add_auto_sizes ) {
			return;
		}

		?>
		<style>img:is([sizes="auto" i], [sizes^="auto," i]) { contain-intrinsic-size: 3000px 1500px }</style>
		<?php
	}

	// @see https://github.com/WordPress/wordpress-develop/pull/7813
	add_action( 'wp_head', 'wp_print_auto_sizes_contain_css_fix', 1 );
}