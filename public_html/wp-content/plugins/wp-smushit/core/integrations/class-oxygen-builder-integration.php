<?php

/**
 * Integration with Oxygen Builder
 *
 * @since 3.19.0
 * @package Smush\Core\Integrations
 */

namespace Smush\Core\Integrations;

use Smush\Core\Controller;
use Smush\Core\LCP\LCP_Data_Store_Option;
use Smush\Core\LCP\LCP_Data_Store_Post_Meta;
use Smush\Core\Settings;



if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Oxygen_Builder_Integration
 */
class Oxygen_Builder_Integration extends Controller {

	/**
	 * Settings class instance for easier access.
	 *
	 * @since 3.0
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Oxygen_Builder_Integration constructor.
	 *
	 * @since 3.19.0
	 */
	public function __construct() {
		$this->settings = Settings::get_instance();
		$this->register_filter( 'updated_post_meta', array( $this, 'handle_oxygen_builder_update' ), 10, 4 );
	}

	public function should_run() {
		return defined( 'CT_VERSION' ) && $this->settings->is_lcp_preload_enabled();
	}

	public function handle_oxygen_builder_update( $meta_id, $post_id, $meta_key, $meta_value ) {
		if ( $meta_key !== 'ct_builder_json' || empty( $meta_value ) ) {
			return;
		}
		$this->clear_post_lcp_data( $post_id );
	}

	public function clear_post_lcp_data( $post_id ) {
		$data_store = new LCP_Data_Store_Post_Meta();
		$data_store->set_post_id( $post_id );
		$data_store->delete_all();

		$data_store_home = new LCP_Data_Store_Option( 'home' );
		$data_store_home->delete_all();
	}
}
