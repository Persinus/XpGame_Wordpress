<?php

namespace Smush\Core\Integrations;

use Hummingbird\Core\Utils;
use Smush\Core\CDN\CDN_Helper;
use Smush\Core\Controller;
use Smush\Core\LCP\LCP_Data;
use Smush\Core\LCP\LCP_Data_Store;
use Smush\Core\LCP\LCP_Data_Store_Home;
use Smush\Core\LCP\LCP_Data_Store_Post_Meta;
use Smush\Core\Settings;

class Hummingbird_Integration extends Controller {
	public function __construct() {
		$this->register_action( 'init', array( $this, 'ensure_hb_compatibility' ) );

		$this->register_filter( 'wphb_tracking_active_features', array( $this, 'get_smush_active_features' ) );

		$this->register_action( 'wp_smush_post_cache_flush_required', array( $this, 'clear_post_cache' ) );

		$this->register_action( 'wp_smush_home_cache_flush_required', array( $this, 'clear_url_cache' ) );
	}

	public function clear_post_cache( $post_id ) {
		if ( $this->is_hb_active() ) {
			// Clear HB page cache.
			do_action( 'wphb_clear_page_cache', $post_id );
		}
	}

	public function clear_url_cache( $url ) {
		if ( $this->is_hb_active() && class_exists( '\Hummingbird\Core\Utils' ) ) {
			$path              = str_replace( untrailingslashit( home_url() ), '', $url );
			$page_cache_module = Utils::get_module( 'page_cache' );
			if ( method_exists( $page_cache_module, 'clear_cache' ) ) {
				$page_cache_module->clear_cache( $path );
			}
		}
	}

	public function ensure_hb_compatibility() {
		// Doing this on init so the HB active check works
		if ( $this->is_hb_active() ) {
			add_action( 'wp_smush_clear_page_cache', array( $this, 'clear_cache' ) );
		}
	}

	private function is_hb_active() {
		return class_exists( '\\Hummingbird\\WP_Hummingbird' );
	}

	public function clear_cache() {
		// Clear HB page cache.
		do_action( 'wphb_clear_page_cache' );
	}

	public function get_smush_active_features( $active_features ) {
		$smush_settings        = Settings::get_instance();
		$lossy_level           = $smush_settings->get_lossy_level_setting();
		$cdn_module_activated  = CDN_Helper::get_instance()->is_cdn_active();
		$webp_module_activated = ! $cdn_module_activated && $smush_settings->is_webp_module_active();
		$webp_direct_activated = $webp_module_activated && $smush_settings->is_webp_direct_conversion_active();
		$webp_server_activated = $webp_module_activated && ! $webp_direct_activated;

		$smush_features = array(
			'smush_basic'       => Settings::LEVEL_LOSSLESS === $lossy_level,
			'smush_super'       => Settings::LEVEL_SUPER_LOSSY === $lossy_level,
			'smush_ultra'       => Settings::LEVEL_ULTRA_LOSSY === $lossy_level,
			'smush_lazy'        => $smush_settings->is_lazyload_active(),
			'smush_cdn'         => $cdn_module_activated,
			'smush_webp'        => $webp_module_activated,
			'smush_webp_direct' => $webp_direct_activated,
			'smush_webp_server' => $webp_server_activated,
		);

		$smush_active_features = array_keys( array_filter( $smush_features ) );
		$active_features       = array_merge( $active_features, $smush_active_features );

		return $active_features;
	}
}
