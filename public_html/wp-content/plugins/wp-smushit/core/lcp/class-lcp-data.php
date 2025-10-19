<?php

namespace Smush\Core\LCP;

use Smush\Core\Array_Utils;

/**
 * Parses the
 */
class LCP_Data {
	/**
	 * @var array
	 */
	private $data;
	/**
	 * @var Array_Utils
	 */
	private $array_utils;

	private $image_url;

	private $selector;
	private $selector_xpath;
	private $lcp_markup_position;
	/**
	 * @var string
	 */
	private $selector_class;
	/**
	 * @var string
	 */
	private $selector_id;

	private $background_type;

	private $background_property;

	private $background_urls;
	/**
	 * @var int
	 */
	private $version;
	private $allowed_url_hostnames;

	public function __construct( array $data, int $version = LCP_Helper::DEFAULT_VERSION ) {
		$this->data        = $data;
		$this->version     = $version;
		$this->array_utils = new Array_Utils();
	}

	public function is_lcp_element_image() {
		return (bool) $this->get_image_url();
	}

	public function get_image_url() {
		if ( is_null( $this->image_url ) ) {
			$this->image_url = $this->prepare_image_url();
		}

		return $this->image_url;
	}

	private function prepare_image_url() {
		$image_url = $this->array_utils->get_array_value( $this->data, 'image_url' );
		if ( empty( $image_url ) ) {
			return '';
		}

		return esc_url_raw( $image_url );
	}

	public function get_selector_xpath() {
		if ( is_null( $this->selector_xpath ) ) {
			$this->selector_xpath = $this->prepare_selector_xpath();
		}

		return $this->selector_xpath;
	}

	private function prepare_selector_xpath() {
		$selector_xpath = $this->array_utils->get_array_value( $this->data, 'selector_xpath' );
		if ( empty( $selector_xpath ) ) {
			return '';
		}

		return sanitize_text_field( $selector_xpath );
	}

	public function get_selector() {
		if ( is_null( $this->selector ) ) {
			$this->selector = $this->prepare_selector();
		}

		return $this->selector;
	}

	private function prepare_selector() {
		$selector = $this->array_utils->get_array_value( $this->data, array( 'selector' ) );
		if ( empty( $selector ) ) {
			return '';
		}

		return sanitize_text_field( $selector );
	}

	public function get_selector_class() {
		if ( is_null( $this->selector_class ) ) {
			$this->selector_class = $this->prepare_selector_class();
		}

		return $this->selector_class;
	}

	private function prepare_selector_class() {
		$selector_class = $this->array_utils->get_array_value( $this->data, 'selector_class' );
		if ( empty( $selector_class ) ) {
			return '';
		}

		return sanitize_text_field( $selector_class );
	}

	public function get_selector_id() {
		if ( is_null( $this->selector_id ) ) {
			$this->selector_id = $this->prepare_selector_id();
		}

		return $this->selector_id;
	}

	public function get_background_type() {
		if ( is_null( $this->background_type ) ) {
			$this->background_type = $this->array_utils->get_array_value( $this->data, array( 'background_data', 'type' ) );
		}

		return $this->background_type;
	}

	public function get_background_property() {
		if ( is_null( $this->background_property ) ) {
			$this->background_property = $this->array_utils->get_array_value( $this->data, array( 'background_data', 'property' ) );
		}

		return $this->background_property;
	}

	public function get_background_urls() {
		if ( is_null( $this->background_urls ) ) {
			$background_urls       = $this->array_utils->get_array_value( $this->data, array( 'background_data', 'urls' ) );
			$this->background_urls = array_filter( $this->array_utils->ensure_array( $background_urls ) );
		}

		return $this->background_urls;
	}

	private function prepare_selector_id() {
		$selector_id = $this->array_utils->get_array_value( $this->data, 'selector_id' );
		if ( empty( $selector_id ) ) {
			return '';
		}

		return sanitize_text_field( $selector_id );
	}

	public static function from_array( $data ) {
		$version = ! empty( $data['version'] )
			? (int) $data['version']
			: LCP_Helper::DEFAULT_VERSION;

		unset( $data['version'] );

		return new self( $data, $version );
	}

	public function to_array() {
		if ( empty( $this->data ) || ! is_array( $this->data ) ) {
			return array();
		}

		return array_merge(
			$this->data,
			array(
				'version' => $this->version,
			)
		);
	}

	public function is_valid() {
		return ! empty( $this->data ) && is_array( $this->data );
	}

	public function get_version() {
		return (int) $this->version;
	}

	public function get_hash() {
		return md5( json_encode( $this->data ) );
	}
}
