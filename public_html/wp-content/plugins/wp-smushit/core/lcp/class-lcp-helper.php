<?php

namespace Smush\Core\LCP;

use Smush\Core\Array_Utils;
use Smush\Core\Server_Utils;
use Smush\Core\Settings;
use Smush\Core\Threads\Thread_Safe_Options;
use Smush\Core\WP_Query_Utils;

class LCP_Helper {
	const KEY_PREFIX = 'wp-smush-lcp-data-';
	const OPTION_LCP_DETAILS = 'wp-smush-lcp-details';
	const NO_DATA_HASH = 'no-data';
	const NO_DATA_VERSION = - 1;
	const DEFAULT_VERSION = 0;

	/**
	 * @var Settings
	 */
	private $settings;
	/**
	 * @var WP_Query_Utils
	 */
	private $wp_query_utils;
	/**
	 * @var Server_Utils
	 */
	private $server_utils;
	/**
	 * @var Array_Utils
	 */
	private $array_utils;

	public function __construct() {
		$this->wp_query_utils = new WP_Query_Utils();
		$this->server_utils   = new Server_Utils();
		$this->array_utils    = new Array_Utils();
		$this->settings       = Settings::get_instance();
	}

	/**
	 * @return LCP_Data|null
	 */
	public function get_lcp_data_for_current_page() {
		if ( ! $this->settings->is_lcp_preload_enabled() ) {
			return null;
		}

		$page_url   = $this->server_utils->get_current_url();
		$data_store = $this->get_data_store();
		if ( ! $data_store ) {
			return null;
		}
		$lcp_data = $data_store->get( $page_url, wp_is_mobile() );
		if ( ! $lcp_data->is_valid() ) {
			return null;
		}

		$current_lcp_data_version = $this->get_current_lcp_data_version();
		if ( $lcp_data->get_version() !== $current_lcp_data_version ) {
			// The data is outdated
			return null;
		}

		return $lcp_data;
	}

	public function get_data_store() {
		$data_store = null;
		if ( $this->wp_query_utils->is_home() ) {
			$data_store = new LCP_Data_Store_Home();
		} elseif ( $this->wp_query_utils->is_singular() ) {
			$post_id    = $this->wp_query_utils->get_queried_object_id();
			$data_store = ( new LCP_Data_Store_Post_Meta() )->set_post_id( $post_id );
		}

		return $data_store;
	}

	public static function delete_all_lcp_data() {
		delete_option( self::OPTION_LCP_DETAILS );

		global $wpdb;

		$key_prefix   = $wpdb->esc_like( self::KEY_PREFIX ) . '%';
		$post_ids     = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $key_prefix ) );
		$meta_deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $key_prefix ) );
		if ( $meta_deleted ) {
			wp_cache_delete_multiple( (array) $post_ids, 'post_meta' );
		}

		$options_names   = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $key_prefix ) );
		$options_deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $key_prefix ) );
		if ( $options_deleted ) {
			wp_cache_delete_multiple( (array) $options_names, 'options' );
		}
	}

	public function get_current_lcp_data_version() {
		$thread_safe_options = new Thread_Safe_Options();
		return (int) $thread_safe_options->get_value( self::OPTION_LCP_DETAILS, 'version', self::DEFAULT_VERSION );
	}

	public function increment_lcp_data_version() {
		$thread_safe_options = new Thread_Safe_Options();
		$thread_safe_options->increment_values( self::OPTION_LCP_DETAILS, array( 'version' ) );
	}

	public function set_server_utils( $server_utils ) {
		$this->server_utils = $server_utils;
	}

	public function set_wp_query_utils( $wp_query_utils ) {
		$this->wp_query_utils = $wp_query_utils;
	}

	public function sanitize_data( $data ) {
		$allowed_keys = array(
			'selector',
			'selector_xpath',
			'selector_id',
			'selector_class',
			'image_url',
			array( 'background_data', 'type' ),
			array( 'background_data', 'property' ),
			array( 'background_data', 'urls' ),
		);

		return $this->_sanitize_data( $data, $allowed_keys );
	}

	private function _sanitize_data( $data, $allowed_keys ) {
		$sanitized_data = array();
		foreach ( $allowed_keys as $key ) {
			$original_value = $this->array_utils->get_array_value( $data, $key );
			if ( is_null( $original_value ) ) {
				continue;
			}

			if ( is_int( $original_value ) || is_float( $original_value ) ) {
				$sanitized_value = $original_value;
			} else {
				$sanitized_value = is_array( $original_value )
					? array_map( array( $this, 'sanitize_string_value' ), $original_value )
					: $this->sanitize_string_value( $original_value );
			}

			if ( ! is_null( $sanitized_value ) ) {
				$this->array_utils->put_array_value( $sanitized_data, $sanitized_value, $key );
			}
		}

		return $sanitized_data;
	}

	private function sanitize_string_value( $value ) {
		$max_length = apply_filters( 'wp_smush_lcp_data_value_max_length', 512 );
		if ( strlen( $value ) > $max_length ) {
			return null;
		}

		return sanitize_text_field( $value );
	}

	/**
	 * Retrieves the list of pages to be excluded from preload.
	 *
	 * @return array List of excluded page URLs.
	 */
	private function get_excluded_pages() {
		$exclude_pages = $this->array_utils->get_array_value( $this->get_preload_options(), 'exclude-pages' );
		return array_filter( $this->array_utils->ensure_array( $exclude_pages ) );
	}

	/**
	 * Checks if the current request URI matches any of the excluded pages.
	 *
	 * @return bool True if the current request URI is excluded, false otherwise.
	 */
	public function is_excluded_uri() {
		$pattern = $this->get_excluded_uri_pattern();
		if ( empty( $pattern ) ) {
			return false;
		}

		$request_uri = $this->server_utils->get_request_uri();
		$request_uri = wp_parse_url( $request_uri, PHP_URL_PATH );
		return (bool) preg_match( "#{$pattern}#i", $request_uri );
	}

	/**
	 * Generate a regex pattern from excluded page URLs.
	 *
	 * @return string Regex pattern without delimiters or flags.
	 */
	private function get_excluded_uri_pattern() {
		$excluded_page_urls = $this->get_excluded_pages();

		if ( empty( $excluded_page_urls ) ) {
			return '';
		}

		$patterns = array_map( array( $this, 'build_url_pattern' ), $excluded_page_urls );

		return implode( '|', array_filter( $patterns ) );
	}

	/**
	 * Build regex pattern for a single URL.
	 *
	 * @param string $url The URL to convert to regex pattern.
	 * @return string Regex pattern for the URL.
	 */
	private function build_url_pattern( $url ) {
		$url = trim( $url );

		if ( empty( $url ) ) {
			return '';
		}

		if ( '/' === $url ) {
			return $this->is_subsite()
				? '^' . preg_quote( get_blog_details( get_current_blog_id() )->path, '#' ) . '$'
				: '^/$';
		}

		return $url;
	}

	/**
	 * Checks if the current site is a subsite in a multisite network.
	 *
	 * @return bool True if the current site is a subsite, false otherwise.
	 */
	public function is_subsite() {
		return is_multisite() && ! is_main_site();
	}

	/**
	 * Retrieves the preload options.
	 *
	 * @return array Preload options.
	 */
	private function get_preload_options() {
		$setting = $this->settings->get_setting( 'wp-smush-preload' );
		return $this->array_utils->ensure_array( $setting );
	}

	/**
	 * Checks if the preload feature should be skipped for the current request.
	 *
	 * @return bool True if preload should be skipped, false otherwise.
	 */
	public function should_skip_preload() {
		return $this->is_excluded_uri();
	}
}
