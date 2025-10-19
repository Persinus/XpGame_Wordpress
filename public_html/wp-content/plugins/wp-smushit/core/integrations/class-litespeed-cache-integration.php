<?php

namespace Smush\Core\Integrations;

use Smush\Core\Controller;

class Litespeed_Cache_Integration extends Controller {
	public function __construct() {
		$this->register_action( 'wp_smush_post_cache_flush_required', array( $this, 'clear_post_cache' ) );
		$this->register_action( 'wp_smush_home_cache_flush_required', array( $this, 'clear_url_cache' ) );
	}

	public function should_run() {
		return parent::should_run() && class_exists( '\LiteSpeed\Core' );
	}

	public function clear_post_cache( $post_id ) {
		do_action( 'litespeed_purge_post', $post_id );
	}

	public function clear_url_cache( $url ) {
		do_action( 'litespeed_purge_url', $url );
	}
}
