<?php

namespace Smush\Core\Integrations;

use Smush\Core\Controller;

class WP_Rocket_Integration extends Controller {
	public function __construct() {
		$this->register_action( 'wp_smush_post_cache_flush_required', array( $this, 'clear_post_cache' ) );
		$this->register_action( 'wp_smush_home_cache_flush_required', array( $this, 'clear_home_cache' ) );
	}

	public function clear_post_cache( $post_id ) {
		if ( function_exists( 'rocket_clean_post' ) ) {
			rocket_clean_post( $post_id );
		}
	}

	public function clear_home_cache() {
		if ( function_exists( 'rocket_clean_home' ) ) {
			rocket_clean_home();
		}
	}
}
