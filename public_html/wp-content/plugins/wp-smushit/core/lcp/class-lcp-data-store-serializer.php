<?php

namespace Smush\Core\LCP;

use Smush\Core\Array_Utils;

class LCP_Data_Store_Serializer {
	/**
	 * @var Array_Utils
	 */
	private $array_utils;

	public function __construct() {
		$this->array_utils = new Array_Utils();
	}

	public function serialize( $object ) {
		$data = $object->to_array();

		if ( $object instanceof LCP_Data_Store_Post_Meta ) {
			return array(
				'type' => 'post-meta',
				'data' => $data,
			);
		} else {
			return array(
				'type' => 'option',
				'data' => $data,
			);
		}

	}

	public function deserialize( $serialized ) {
		$type = $this->array_utils->get_array_value( $serialized, 'type' );
		$data = $this->array_utils->get_array_value( $serialized, 'data' );

		if ( is_null( $type ) || is_null( $data ) ) {
			return null;
		}

		switch ( $type ) {
			case 'post-meta':
				$object = new LCP_Data_Store_Post_Meta();
				break;

			case 'option':
			default:
				$object = new LCP_Data_Store_Option();
		}

		$object->from_array( $data );
		return $object;
	}
}
