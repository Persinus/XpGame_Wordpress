<?php

namespace Smush\Core\Cache;

class Cache_Helper {
	const CLEAR_CACHE_ACTION = 'wp_smush_clear_page_cache';
	const SHOW_CACHE_NOTICE_TRANSIENT = 'wp_smush_show_cache_notice';

	/**
	 * Static instance
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Static instance getter
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function clear_post_cache( $post_id ) {
		do_action( 'wp_smush_post_cache_flush_required', $post_id );
	}

	public function clear_home_cache( $url ) {
		do_action( 'wp_smush_home_cache_flush_required', $url );
	}

	public function clear_full_cache( $notice_key = 'generic' ) {
		if ( ! has_action( self::CLEAR_CACHE_ACTION ) && ! empty( $notice_key ) ) {
			// If no one is handling the cache clearing then show a notice
			set_transient( self::SHOW_CACHE_NOTICE_TRANSIENT, $notice_key );
		} else {
			do_action( self::CLEAR_CACHE_ACTION );
		}
	}

	public function delete_notice_key() {
		delete_transient( self::SHOW_CACHE_NOTICE_TRANSIENT );
	}

	public function get_notice_key() {
		return get_transient( self::SHOW_CACHE_NOTICE_TRANSIENT );
	}
}
