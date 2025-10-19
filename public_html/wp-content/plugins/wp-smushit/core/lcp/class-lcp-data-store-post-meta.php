<?php

namespace Smush\Core\LCP;

use Smush\Core\Array_Utils;

class LCP_Data_Store_Post_Meta extends LCP_Data_Store {
	const TYPE = 'post-meta';

	private $post_id;
	/**
	 * @var Array_Utils
	 */
	private $array_utils;

	public function __construct() {
		$this->array_utils = new Array_Utils();

		parent::__construct();
	}

	public function save( $url, $is_mobile, LCP_Data $lcp_data ) {
		$post_meta = $lcp_data->to_array();
		$post_meta = ! empty( $post_meta ) && is_array( $post_meta )
			? $post_meta
			: array();

		if ( empty( $post_meta ) ) {
			return false;
		}

		return update_post_meta(
			$this->post_id,
			$this->make_key( $url, $is_mobile ),
			$post_meta
		);
	}

	public function get( $url, $is_mobile ): LCP_Data {
		$post_meta = get_post_meta(
			$this->post_id,
			$this->make_key( $url, $is_mobile ),
			true
		);
		$post_meta = ! empty( $post_meta ) && is_array( $post_meta )
			? $post_meta
			: array();

		return LCP_Data::from_array( $post_meta );
	}

	public function set_post_id( $post_id ) {
		$this->post_id = $post_id;

		return $this;
	}

	public function get_post_id() {
		return $this->post_id;
	}

	public function to_array() {
		return array(
			'post_id' => $this->post_id,
		);
	}

	public function from_array( $data ) {
		$this->post_id = $this->array_utils->get_array_value( $data, 'post_id' );
	}

	public function delete_all() {
		global $wpdb;

		$like      = $wpdb->esc_like( LCP_Helper::KEY_PREFIX ) . '%';
		$query     = $wpdb->prepare(
			"SELECT meta_key FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
			$this->post_id,
			$like
		);
		$meta_keys = $wpdb->get_col( $query );

		if ( empty( $meta_keys ) ) {
			return false;
		}

		foreach ( $meta_keys as $meta_key ) {
			delete_post_meta( $this->post_id, $meta_key );
		}
		return true;
	}

	public function get_type() {
		return self::TYPE;
	}

	public function get_object_id() {
		return $this->post_id;
	}
}
