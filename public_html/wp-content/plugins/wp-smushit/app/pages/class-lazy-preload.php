<?php
/**
 * Lazy load & Preload page.
 *
 * @package Smush\App\Pages
 */

namespace Smush\App\Pages;

use Smush\App\Abstract_Summary_Page;
use Smush\App\Interface_Page;
use WP_Smush;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Lazy_Preload
 */
class Lazy_Preload extends Abstract_Summary_Page implements Interface_Page {

	public function on_load() {
		parent::on_load();
		// Init the tabs.
		$this->tabs = array(
			'lazy_load' => __( 'Lazy Load', 'wp-smushit' ),
			'preload'   => __( 'Preload', 'wp-smushit' ),
		);

		add_action( 'wp_smush_admin_after_tab_smush-lazy-preload', array( $this, 'lazy_preload_add_new_tag' ) );
		add_action( 'wp_smush_admin_page_before_sidenav', array( $this, 'render_summary_meta_box' ) );
	}

	public function render_summary_meta_box() {
		$this->do_meta_boxes( 'main' );
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		parent::register_meta_boxes();

		$this->add_meta_box(
			'preload',
			__( 'Preload Critical Images', 'wp-smushit' ),
			array( $this, 'preload_meta_box' ),
			array( $this, 'preload_header_meta_box' ),
			array( $this, 'common_meta_box_footer' ),
			'preload',
			array(
				'box_class' => 'sui-box sui-no-padding',
			)
		);

		if ( ! $this->settings->get( 'lazy_load' ) ) {
			$this->add_meta_box(
				'lazyload/disabled',
				__( 'Lazy Load', 'wp-smushit' ),
				null,
				null,
				null,
				'lazy_load',
				array(
					'box_class' => 'sui-box sui-message sui-no-padding',
				)
			);

			return;
		}

		$this->add_meta_box(
			'lazyload',
			__( 'Lazy Load', 'wp-smushit' ),
			array( $this, 'lazy_load_meta_box' ),
			null,
			array( $this, 'common_meta_box_footer' ),
			'lazy_load'
		);
	}

	/**
	 * Common footer meta box.
	 *
	 * @since 3.2.0
	 */
	public function common_meta_box_footer() {
		$current_tab       = $this->get_current_tab();
		$is_submit_enabled = WP_Smush::is_pro() || 'lazy_load' === $current_tab;
			$this->view(
				'meta-box-footer',
				array(
					'is_submit_enabled' => $is_submit_enabled,
				),
				'common'
			);
	}

	/**
	 * Lazy loading meta box.
	 *
	 * @since 3.2.0
	 */
	public function lazy_load_meta_box() {
		$this->view(
			'lazyload/meta-box',
			array(
				'conflicts' => get_transient( 'wp-smush-conflict_check' ),
				'settings'  => $this->settings->get_setting( 'wp-smush-lazy_load' ),
				'cpts'      => get_post_types( // custom post types.
					array(
						'public'   => true,
						'_builtin' => false,
					),
					'objects'
				),
			)
		);
	}

	public function preload_meta_box() {
		$this->view(
			'preload/meta-box',
			array(
				'lcp_preload_enabled' => $this->settings->is_lcp_preload_enabled(),
				'preload_settings'    => $this->settings->get_setting( 'wp-smush-preload' ),
			)
		);
	}

	public function lazy_preload_add_new_tag( $tab_id ) {
		if ( 'preload' !== $tab_id ) {
			return $this->lazyload_add_new_tag();
		}

		if ( ! WP_Smush::is_pro() ) {
			echo '<span class="sui-tag sui-tag-pro" style="right:11px; top:11px">' . esc_html__( 'Pro', 'wp-smushit' ) . '</span>';
		}
	}

	public function lazyload_add_new_tag() {
		if ( ! self::should_show_new_feature_hotspot() ) {
			return;
		}
		echo '<span class="smush-new-feature-dot" style="transform: translate(-8px, -21px);"></span>';
	}

	public function preload_header_meta_box() {
		$this->view(
			'preload/meta-box-header'
		);
	}
}
