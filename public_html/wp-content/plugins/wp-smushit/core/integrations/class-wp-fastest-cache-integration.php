<?php

namespace Smush\Core\Integrations;

use Smush\Core\Controller;

class WP_Fastest_Cache_Integration extends Controller {
	public function __construct() {
		$this->register_action( 'wp_smush_post_cache_flush_required', array( $this, 'clear_post_lcp_changes' ) );
	}

	public function should_run() {
		return parent::should_run() && function_exists( 'wpfc_clear_post_cache_by_id' );
	}

	public function clear_post_lcp_changes( $post_id ) {
		if ( function_exists( 'wpfc_clear_post_cache_by_id' ) ) {
			wpfc_clear_post_cache_by_id( $post_id );
		}
	}
}
