<?php

namespace Smush\Core\Integrations;

use Smush\Core\Controller;

class WP_Super_Cache_Integration extends Controller {
	public function __construct() {
		$this->register_action( 'wp_smush_post_cache_flush_required', array( $this, 'clear_post_cache' ) );
		$this->register_action( 'wp_smush_home_cache_flush_required', array( $this, 'clear_url_cache' ) );
	}

	public function should_run() {
		return parent::should_run() && function_exists( 'wpsc_delete_post_cache' );
	}

	public function clear_post_cache( $post_id ) {
		if ( function_exists( 'wpsc_delete_post_cache' ) ) {
			wpsc_delete_post_cache( $post_id );
		}
	}

	public function clear_url_cache( $url ) {
		if ( function_exists( 'wpsc_delete_url_cache' ) ) {
			wpsc_delete_url_cache( $url );
		}
	}
}
