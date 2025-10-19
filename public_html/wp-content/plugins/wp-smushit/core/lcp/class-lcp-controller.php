<?php

namespace Smush\Core\LCP;

use Smush\Core\Cache\Cache_Helper;
use Smush\Core\Controller;
use Smush\Core\Parser\Page;
use Smush\Core\Settings;
use WP_Error;

class LCP_Controller extends Controller {
	const LCP_TRANSFORM_PRIORITY = 30;
	/**
	 * @var Settings
	 */
	private $settings;
	/**
	 * @var LCP_Data_Store_Serializer
	 */
	private $data_store_serializer;
	/**
	 * @var LCP_Helper
	 */
	private $lcp_helper;
	private Cache_Helper $cache_helper;

	public function __construct() {
		$this->settings              = Settings::get_instance();
		$this->data_store_serializer = new LCP_Data_Store_Serializer();
		$this->lcp_helper            = new LCP_Helper();
		$this->cache_helper          = Cache_Helper::get_instance();

		$this->register_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_detector_script' ) );
		$this->register_action( 'wp_ajax_smush_handle_lcp_data', array( $this, 'ajax_handle_lcp_data' ) );
		$this->register_action( 'wp_ajax_nopriv_smush_handle_lcp_data', array( $this, 'ajax_handle_lcp_data' ) );
		$this->register_action( 'wp_smush_transformed_page_markup', array( $this, 'preload_lcp_images' ), 10, 2 );
		$this->register_action( 'edit_post', array( $this, 'clear_post_lcp_data' ) );
		$this->register_action( 'after_switch_theme', array( $this, 'mark_all_lcp_data_as_dirty' ) );
		$this->register_action( 'wp_ajax_clear_all_lcp_data', array( $this, 'ajax_mark_all_lcp_data_as_dirty' ) );
		$this->register_filter( 'wp_smush_content_transforms', array( $this, 'register_lcp_transform' ), self::LCP_TRANSFORM_PRIORITY );
	}

	public function register_lcp_transform( $transforms ) {
		$transforms['lcp'] = new LCP_Transform();

		return $transforms;
	}

	public function should_run() {
		return parent::should_run() &&
				$this->settings->is_lcp_preload_enabled() &&
				! $this->lcp_helper->should_skip_preload();
	}

	public function maybe_enqueue_detector_script() {
		$data_store = $this->lcp_helper->get_data_store();
		if ( ! $data_store ) {
			return;
		}

		$handle = 'smush-detector';
		wp_enqueue_script(
			$handle,
			WP_SMUSH_URL . 'app/assets/js/smush-detector.min.js',
			array(),
			WP_SMUSH_VERSION,
			array( 'in_footer' => true )
		);

		$previous_lcp_data = $this->lcp_helper->get_lcp_data_for_current_page();
		wp_localize_script( $handle, 'smush_detector', array(
			'ajax_url'              => admin_url( 'admin-ajax.php' ),
			'nonce'                 => wp_create_nonce( 'smush_handle_lcp_data' ),
			'is_mobile'             => wp_is_mobile(),
			'data_store'            => $this->data_store_serializer->serialize( $data_store ),
			'previous_data_version' => $previous_lcp_data ? $previous_lcp_data->get_version() : LCP_Helper::NO_DATA_VERSION,
			'previous_data_hash'    => $previous_lcp_data ? $previous_lcp_data->get_hash() : LCP_Helper::NO_DATA_HASH,
		) );
	}

	public function ajax_handle_lcp_data() {
		// Verify nonce.
		if ( ! check_ajax_referer( 'smush_handle_lcp_data', 'nonce', false ) ) {
			wp_send_json_error( array(
				'error_msg' => esc_html__( 'Error in processing LCP data, nonce verification failed.', 'wp-smushit' ),
			) );
		}

		$url                   = empty( $_POST['url'] ) ? '' : esc_url_raw( $_POST['url'] );
		$raw_data              = empty( $_POST['data'] ) ? array() : json_decode( stripslashes( $_POST['data'] ), true );
		$previous_data_version = ! isset( $_POST['previous_data_version'] ) ? LCP_Helper::NO_DATA_HASH : intval( $_POST['previous_data_version'] );
		$previous_data_hash    = ! isset( $_POST['previous_data_hash'] ) ? LCP_Helper::NO_DATA_HASH : sanitize_text_field( $_POST['previous_data_hash'] );
		$is_mobile             = ! empty( $_POST['is_mobile'] );
		$serialized_data_store = empty( $_POST['data_store'] ) ? array() : json_decode( stripslashes( $_POST['data_store'] ), true );
		$data_store            = $this->data_store_serializer->deserialize( $serialized_data_store );

		$handled = $this->handle_lcp_data( $url, $raw_data, $previous_data_version, $previous_data_hash, $is_mobile, $data_store );
		if ( is_wp_error( $handled ) ) {
			wp_send_json_error( array( 'error_msg' => $handled->get_error_message() ) );
		} else {
			wp_send_json_success();
		}
	}

	public function handle_lcp_data( $url, $raw_data, $previous_data_version, $previous_data_hash, $is_mobile, $data_store = null ) {
		$current_data_version = $this->lcp_helper->get_current_lcp_data_version();
		$data                 = $this->lcp_helper->sanitize_data( $raw_data );
		$lcp_data             = new LCP_Data( $data, $current_data_version );
		if ( ! $lcp_data->is_lcp_element_image() ) {
			return new WP_Error( 'not-an-image', esc_html__( 'LCP element is not an image.', 'wp-smushit' ) );
		}

		if ( empty( $url ) || empty( $raw_data ) || empty( $data_store ) || ! is_a( $data_store, LCP_Data_Store::class ) ) {
			return new WP_Error( 'error-in-processing', esc_html__( 'Error in processing LCP data, fields empty.', 'wp-smushit' ) );
		}

		$version_changed = $previous_data_version === LCP_Helper::NO_DATA_VERSION || $current_data_version !== $previous_data_version;
		$hash_changed    = $previous_data_hash === LCP_Helper::NO_DATA_HASH || $lcp_data->get_hash() !== $previous_data_hash;
		if ( ! $version_changed && ! $hash_changed ) {
			return new WP_Error( 'data-already-up-to-date', esc_html__( 'LCP data is already up to date', 'wp-smushit' ) );
		}

		$data_store->save( $url, $is_mobile, $lcp_data );

		$this->do_lcp_data_updated_action( $lcp_data, $data_store, $url );
		$this->clear_cache( $data_store, $url );

		return true;
	}

	/**
	 * @param $page_markup string
	 * @param $parsed_page Page
	 *
	 * @return mixed
	 */
	public function preload_lcp_images( $page_markup, $parsed_page ) {
		if ( ! preg_match( '#</title\s*>#', $page_markup, $matches ) ) {
			return $page_markup;
		}

		$lcp_data = $this->lcp_helper->get_lcp_data_for_current_page();
		if ( ! $lcp_data ) {
			return $page_markup;
		}

		$tag = ( new LCP_Preload_Tag( $parsed_page, $lcp_data ) )->make_preload_tag();
		if ( empty( $tag ) ) {
			return $page_markup;
		}

		$title   = $matches[0];
		$replace = preg_replace( '#' . $title . '#', '</title>' . $tag, $page_markup, 1 );
		if ( null === $replace ) {
			return $page_markup;
		}

		return $replace;
	}

	public function clear_post_lcp_data( $post_id ) {
		$data_store = new LCP_Data_Store_Post_Meta();
		$data_store->set_post_id( $post_id );
		$data_store->delete_all();

		$data_store_home = new LCP_Data_Store_Home();
		$data_store_home->delete_all();
	}

	public function mark_all_lcp_data_as_dirty() {
		$this->lcp_helper->increment_lcp_data_version();
	}

	public function ajax_mark_all_lcp_data_as_dirty() {
		if ( ! check_ajax_referer( 'wp-smush-ajax', '_ajax_nonce', false ) ) {
			wp_send_json_error( array(
				'error_msg' => esc_html__( 'Nonce verification failed.', 'wp-smushit' ),
			) );
		}

		$this->mark_all_lcp_data_as_dirty();
	}

	/**
	 * @param LCP_Data $lcp_data
	 * @param LCP_Data_Store $data_store
	 * @param $url
	 *
	 * @return void
	 */
	private function do_lcp_data_updated_action( LCP_Data $lcp_data, LCP_Data_Store $data_store, $url ) {
		do_action( 'wp_smush_lcp_data_updated', $lcp_data, $data_store, $url );
	}

	private function clear_cache( LCP_Data_Store $data_store, $url ): void {
		switch ( $data_store->get_type() ) {
			case LCP_Data_Store_Post_Meta::TYPE:
				$this->cache_helper->clear_post_cache( $data_store->get_object_id() );
				break;

			case LCP_Data_Store_Home::TYPE:
				$this->cache_helper->clear_home_cache( $url );
				break;
		}
	}
}
