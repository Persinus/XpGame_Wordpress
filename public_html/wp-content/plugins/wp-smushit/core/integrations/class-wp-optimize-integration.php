<?php

namespace Smush\Core\Integrations;

use Smush\Core\Controller;
use WPO_Page_Cache;

class WP_Optimize_Integration extends Controller {
	public function __construct() {
		$this->register_action( 'wp_smush_post_cache_flush_required', array( $this, 'clear_post_cache' ) );
		$this->register_action( 'wp_smush_home_cache_flush_required', array( $this, 'clear_url_cache' ) );
	}

	public function should_run() {
		return parent::should_run() && class_exists( 'WPO_Page_Cache' );
	}

	public function clear_post_cache( $post_id ) {
		if ( method_exists( 'WPO_Page_Cache', 'delete_single_post_cache' ) ) {
			WPO_Page_Cache::delete_single_post_cache( $post_id );
		}
	}

	public function clear_url_cache( $url ) {
		if ( method_exists( 'WPO_Page_Cache', 'delete_cache_by_url' ) ) {
			WPO_Page_Cache::delete_cache_by_url( $url );
		}
	}
}
