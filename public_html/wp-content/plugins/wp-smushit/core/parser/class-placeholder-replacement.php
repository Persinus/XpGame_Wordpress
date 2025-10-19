<?php

namespace Smush\Core\Parser;

class Placeholder_Replacement {
	private $placeholders = array();
	private $counts = array();
	private $prefix = 'smush-placeholder-';

	public function add_placeholders( $markup, $blocks ) {
		foreach ( $blocks as $block ) {
			$markup = $this->add_placeholder( $markup, $block );
		}

		return $markup;
	}

	public function add_placeholder( $markup, $block ) {
		$key                        = $this->make_key( $block );
		$this->placeholders[ $key ] = $block;
		$new_markup                 = str_replace( $block, $key, $markup, $count );

		if ( ! isset( $this->counts[ $key ] ) ) {
			$this->counts[ $key ] = 0;
		}
		$this->counts[ $key ] += $count;

		return $new_markup;
	}

	public function remove_placeholder( $markup, $key ) {
		if ( isset( $this->placeholders[ $key ] ) && strpos( $markup, $key ) !== false ) {
			$markup               = str_replace( $key, $this->placeholders[ $key ], $markup, $count );
			$this->counts[ $key ] -= $count;

			if ( empty( $this->counts[ $key ] ) ) {
				unset( $this->placeholders[ $key ] );
				unset( $this->counts[ $key ] );
			}
		}

		return $markup;
	}

	public function remove_placeholders( $markup ) {
		foreach ( $this->placeholders as $key => $original ) {
			$markup = $this->remove_placeholder( $markup, $key );
		}

		return $markup;
	}

	public function remove_placeholders_recursively( $markup ) {
		$markup = $this->remove_placeholders( $markup );
		if ( $this->has_some_key( $markup ) ) {
			return $this->remove_placeholders_recursively( $markup );
		}

		return $markup;
	}

	/**
	 * @param $block
	 *
	 * @return string
	 */
	private function make_key( $block ): string {
		return $this->prefix . md5( $block );
	}

	public function has_some_key( $markup ) {
		return strpos( $markup, $this->prefix ) !== false;
	}

	public function get_placeholders_from_markup( $markup ) {
		$matches = array();
		if ( preg_match_all( '/smush-placeholder-[a-f0-9]{32}/', $markup, $matches ) ) {
			return empty( $matches[0] )
				? array()
				: $matches[0];
		} else {
			return array();
		}
	}
}
