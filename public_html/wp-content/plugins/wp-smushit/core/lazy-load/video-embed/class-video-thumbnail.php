<?php

namespace Smush\Core\Lazy_Load\Video_Embed;

use Smush\Core\CDN\CDN_Helper;
use Smush\Core\Next_Gen\Next_Gen_Manager;
use Smush\Core\Settings;

class Video_Thumbnail {

	/**
	 * @var int
	 */
	private $width;

	/**
	 * @var int
	 */
	private $height;

	/**
	 * @var string
	 */

	private $next_gen_url;

	/**
	 * @var string
	 */

	private $cdn_url;

	/**
	 * @var string
	 */
	private $fallback_url;

	/**
	 * @var string
	 */
	private $aspect_ratio;

	public function __construct() {
	}

	public function get_width() {
		return $this->width;
	}

	private function set_width( $width ) {
		$this->width = (int) $width;
	}

	public function get_height() {
		return $this->height;
	}

	private function set_height( $height ) {
		$this->height = (int) $height;
	}

	public function get_next_gen_url() {
		return $this->next_gen_url;
	}

	private function set_next_gen_url( $next_gen_url ) {
		$this->next_gen_url = $next_gen_url;
	}

	private function get_cdn_url() {
		if ( is_null( $this->cdn_url ) ) {
			$this->cdn_url = $this->prepare_cdn_url();
		}

		return $this->cdn_url;
	}

	private function prepare_cdn_url() {
		$cdn_helper    = CDN_Helper::get_instance();
		$thumbnail_url = $this->get_fallback_url();
		if (
			! $cdn_helper->is_cdn_active()
			|| ! $cdn_helper->is_supported_url( $thumbnail_url )
			|| $cdn_helper->skip_image_url( $thumbnail_url )
		) {
			return false;
		}

		return $cdn_helper->generate_cdn_url( $thumbnail_url );
	}

	private function set_cdn_url( $cdn_url ) {
		$this->cdn_url = $cdn_url;
	}

	public function get_fallback_url() {
		return $this->fallback_url;
	}

	private function set_fallback_url( $fallback_url ) {
		$this->fallback_url = $fallback_url;
	}

	public function get_url() {
		if ( $this->has_next_gen_url() ) {
			return $this->get_next_gen_url();
		}

		$cdn_url = $this->get_cdn_url();
		if ( $cdn_url ) {
			return $cdn_url;
		}

		return $this->get_fallback_url();
	}

	public function has_next_gen_url() {
		if ( ! $this->should_use_next_gen_format() ) {
			return false;
		}

		return ! empty( $this->next_gen_url );
	}

	private function should_use_next_gen_format() {
		return Next_Gen_Manager::get_instance()->is_active()
								|| Settings::get_instance()->is_cdn_next_gen_conversion_active();
	}

	public function get_aspect_ratio() {
		if ( ! $this->aspect_ratio ) {
			$this->aspect_ratio = $this->width && $this->height ? "{$this->width}/{$this->height}" : 'auto';
		}

		return $this->aspect_ratio;
	}

	public function to_array() {
		return array(
			'width'        => $this->get_width(),
			'height'       => $this->get_height(),
			'next_gen_url' => $this->get_next_gen_url(),
			'cdn_url'      => $this->get_cdn_url(),
			'fallback_url' => $this->get_fallback_url(),
		);
	}

	public function from_array( $array_values ) {
		$this->set_width( $this->get_array_value( $array_values, 'width' ) );
		$this->set_height( $this->get_array_value( $array_values, 'height' ) );
		$this->set_next_gen_url( $this->get_array_value( $array_values, 'next_gen_url' ) );
		$this->set_cdn_url( $this->get_array_value( $array_values, 'cdn_url' ) );
		$this->set_fallback_url( $this->get_array_value( $array_values, 'fallback_url' ) );
	}

	private function get_array_value( $array_values, $key ) {
		return isset( $array_values[ $key ] ) ? $array_values[ $key ] : null;
	}
}
