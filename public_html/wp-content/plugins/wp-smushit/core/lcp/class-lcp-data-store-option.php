<?php

namespace Smush\Core\LCP;

use Smush\Core\Array_Utils;

class LCP_Data_Store_Option extends LCP_Data_Store {
	private $location;
	/**
	 * @var Array_Utils
	 */
	private $array_utils;

	public function __construct( $location = '' ) {
		$this->location    = $location;
		$this->array_utils = new Array_Utils();

		parent::__construct();
	}

	public function save( $url, $is_mobile, LCP_Data $lcp_data ) {
		return update_option( $this->make_key( $url, $is_mobile ), $lcp_data->to_array(), false );
	}

	public function get( $url, $is_mobile ): LCP_Data {
		$data = get_option( $this->make_key( $url, $is_mobile ) );
		$data = ! empty( $data ) && is_array( $data )
			? $data
			: array();

		return LCP_Data::from_array( $data );
	}

	public function delete_all() {
		if ( $this->location ) {
			delete_option( $this->make_location_key( true ) );
			delete_option( $this->make_location_key( false ) );
		}
	}

	/**
	 * @param $url
	 * @param $is_mobile
	 *
	 * @return string
	 */
	protected function make_key( $url, $is_mobile ): string {
		if ( $this->location ) {
			$key = $this->make_location_key( $is_mobile );
		} else {
			$key = parent::make_key( $url, $is_mobile );
		}
		return $key;
	}

	/**
	 * @param $is_mobile
	 *
	 * @return string
	 */
	private function make_location_key( $is_mobile ): string {
		$key = LCP_Helper::KEY_PREFIX . $this->location;
		if ( $is_mobile ) {
			$key .= '-mobile';
		}
		return $key;
	}

	public function to_array() {
		return array(
			'location' => $this->location,
		);
	}

	public function from_array( $data ) {
		$this->location = $this->array_utils->get_array_value( $data, 'location' );
	}

	public function get_type() {
		return $this->location;
	}
}
