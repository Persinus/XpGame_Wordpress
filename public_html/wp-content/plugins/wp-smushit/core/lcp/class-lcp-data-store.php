<?php

namespace Smush\Core\LCP;

use Smush\Core\Url_Utils;

abstract class LCP_Data_Store {
	/**
	 * @var Url_Utils
	 */
	private $url_utils;

	public function __construct() {
		$this->url_utils = new Url_Utils();
	}

	abstract public function save( $url, $is_mobile, LCP_Data $lcp_data );

	abstract public function get( $url, $is_mobile ): LCP_Data;

	abstract public function delete_all();

	abstract public function get_type();

	public function get_object_id() {
		return false;
	}

	public function to_array() {
		return array();
	}

	public function from_array( $data ) {

	}

	protected function make_key( $url, $is_mobile ) {
		$url       = $this->url_utils->normalize_url( $url );
		$is_mobile = (int) $is_mobile;

		return LCP_Helper::KEY_PREFIX . md5( $url . $is_mobile );
	}
}
