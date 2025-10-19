<?php

namespace Smush\Core\Srcset;

use Smush\Core\Media\Attachment_Url_Cache;
use Smush\Core\Settings;
use Smush\Core\Transform\Transformation_Controller;
use Smush\Core\Url_Utils;

class Srcset_Helper {
	/**
	 * @var self
	 */
	private static $instance;

	/**
	 * @var Attachment_Url_Cache
	 */
	private $attachment_url_cache;

	/**
	 * @var Url_Utils
	 */
	private $url_utils;
	/**
	 * @var Settings
	 */
	private $settings;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->url_utils            = new Url_Utils();
		$this->attachment_url_cache = Attachment_Url_Cache::get_instance();
		$this->settings             = Settings::get_instance();
	}

	/**
	 * @return array( $srcset, $sizes )
	 */
	public function generate_srcset_and_sizes( $src, $attachment_id = 0, $width = 0, $height = 0 ) {
		add_filter( 'wp_calculate_image_sizes', array( $this, 'update_image_sizes' ), 10, 2 );
		list( $srcset, $sizes ) = $this->_generate_srcset_and_sizes( $src, $attachment_id, $width, $height );
		remove_filter( 'wp_calculate_image_sizes', array( $this, 'update_image_sizes' ), 10 );

		return array( $srcset, $sizes );
	}

	private function _generate_srcset_and_sizes( $src, $attachment_id = 0, $width = 0, $height = 0 ) {
		/**
		 * Try to get the attachment URL.
		 */
		if ( empty( $attachment_id ) ) {
			$attachment_id = $this->attachment_url_cache->get_id_for_url( $src );
		}

		$width  = (int) $width;
		$height = (int) $height;

		if ( ! $width || ! $height ) {
			list( $width, $height ) = $this->find_image_dimensions( $src, $attachment_id, $width, $height );
		}

		if ( empty( $width ) || empty( $height ) ) {
			return array( false, false );
		}

		// This is an image placeholder - do not generate srcset.
		if ( $width === $height && $width < Transformation_Controller::MIN_TRANSFORMABLE_IMAGE_DIMENSION ) {
			return array( false, false );
		}

		$image_metadata = $attachment_id > 0 ? wp_get_attachment_metadata( $attachment_id ) : array();
		$size_array     = array( absint( $width ), absint( $height ) );

		if ( $this->is_image_metadata_invalid( $image_metadata ) ) {
			$image_metadata = array(
				'width'  => $width,
				'height' => $height,
			);
			// Generate srcset via filter if metadata is invalid.
			$srcset = $this->generate_image_srcset_through_filter( $size_array, $src, $image_metadata, $attachment_id );
		} else {
			$srcset = wp_calculate_image_srcset( $size_array, $src, $image_metadata, $attachment_id );
		}

		$sizes = wp_calculate_image_sizes( $size_array, $src, $image_metadata, $attachment_id );

		return array( $srcset, $sizes );
	}

	private function generate_image_srcset_through_filter( $size_array, $image_src, $image_meta, $attachment_id ) {
		$sources = apply_filters( 'wp_calculate_image_srcset', array(), $size_array, $image_src, $image_meta, $attachment_id );

		// Only return a 'srcset' value if there is more than one source.
		if ( ! is_array( $sources ) || count( $sources ) < 2 ) {
			return false;
		}

		$srcset = '';

		foreach ( $sources as $source ) {
			$srcset .= str_replace( ' ', '%20', $source['url'] ) . ' ' . $source['value'] . $source['descriptor'] . ', ';
		}

		return rtrim( $srcset, ', ' );
	}

	private function is_image_metadata_invalid( $image_metadata ) {
		// Check if required metadata fields are missing or invalid.
		$is_missing_sizes      = empty( $image_metadata['sizes'] );
		$is_missing_dimensions = empty( $image_metadata['width'] ) || empty( $image_metadata['height'] );
		$is_missing_file       = ! isset( $image_metadata['file'] ) || strlen( $image_metadata['file'] ) < 4;

		// Return true if any of the conditions are met.
		return $is_missing_sizes || $is_missing_dimensions || $is_missing_file;
	}

	private function find_image_dimensions( $src_url, $attachment_id, $width_from_attribute, $height_from_attribute ) {
		list( $src_width, $src_height ) = $this->get_dimensions_from_url_or_attachment( $src_url, $attachment_id );

		// If still missing, return zeros.
		if ( $src_width <= 0 || $src_height <= 0 ) {
			return array( $width_from_attribute, $height_from_attribute );
		}

		$image_ratio = $src_width / $src_height;

		if ( $width_from_attribute > 0 ) {
			return array( $width_from_attribute, $width_from_attribute / $image_ratio );
		}

		if ( $height_from_attribute > 0 ) {
			return array( $height_from_attribute * $image_ratio, $height_from_attribute );
		}

		return array( $src_width, $src_height );
	}

	private function get_dimensions_from_url_or_attachment( $src_url, $attachment_id ) {
		list( $src_width, $src_height ) = $this->url_utils->get_image_dimensions( $src_url );

		if ( empty( $src_width ) || empty( $src_height ) ) {
			$image_data = wp_get_attachment_image_src( $attachment_id, 'full' );
			if ( is_array( $image_data ) && count( $image_data ) >= 3 ) {
				list( , $src_width, $src_height ) = $image_data;
			}
		}

		return array( (int) $src_width, (int) $src_height );
	}

	private function get_image_metadata( $attachment_id, $image_width, $image_height ) {
		$image_metadata = array();
		if ( $attachment_id ) {
			$image_metadata = wp_get_attachment_metadata( $attachment_id );
		}

		if ( empty( $image_metadata ) || ! is_array( $image_metadata ) ) {
			$image_metadata = array(
				'width'  => $image_width,
				'height' => $image_height,
			);
		}

		return $image_metadata;
	}

	public function skip_adding_srcset( $src_url, $image_markup ) {
		return apply_filters( 'smush_skip_adding_srcset', false, $src_url, $image_markup );
	}

	public function update_image_sizes( $sizes, $size ) {
		$content_width            = $this->settings->max_content_width();
		$filtered_max_image_width = (int) apply_filters( 'wp_smush_max_image_width', 0, $content_width );
		$original_sizes           = $sizes;
		$image_width              = ! empty( $size[0] ) ? $size[0] : 0;

		if ( ! empty( $sizes ) && 0 === $filtered_max_image_width ) {
			$final_max_width = $content_width;
			$final_sizes     = $sizes;
		} else {
			$options         = array_filter( array_map( 'absint', array( $image_width, $filtered_max_image_width ) ) );
			$final_max_width = ! empty( $options ) ? min( $options ) : $content_width;
			$final_sizes     = sprintf( '(max-width: %1$dpx) 100vw, %1$dpx', $final_max_width );
		}

		return apply_filters( 'wp_smush_image_sizes', $final_sizes, $size, $final_max_width, $original_sizes );
	}
}
