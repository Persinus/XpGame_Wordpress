<?php
/**
 * Bulk compress page.
 *
 * @since 2.9.0
 * @package Smush\App\Pages
 */

namespace Smush\App\Pages;

use Smush\App\Abstract_Summary_Page;
use Smush\App\Interface_Page;
use Smush\Core\Array_Utils;
use Smush\Core\Core;
use Smush\Core\Settings;
use Smush\Core\Stats\Global_Stats;
use Smush\Core\Media_Library\Background_Media_Library_Scanner;
use Smush\Core\Modules\Background\Background_Pre_Flight_Controller;
use WP_Smush;
use Smush\Core\Backups\Backups;
use Smush\Core\Smush\Smush_Settings_UI_Controller;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Bulk
 */
class Bulk extends Abstract_Summary_Page implements Interface_Page {
	/**
	 * Function triggered when the page is loaded before render any content.
	 */
	public function on_load() {
		parent::on_load();

		// If a free user, update the limits.
		if ( ! WP_Smush::is_pro() ) {
			// Reset transient.
			Core::check_bulk_limit( true );
			add_action( 'smush_setting_column_tag', array( $this, 'add_pro_tag' ) );
		}

		$smush_settings_ui_controller = new Smush_Settings_UI_Controller();
		$smush_settings_ui_controller->init();
	}

	public function enqueue_scripts( $hook ) {
		parent::enqueue_scripts( $hook );

		$this->enqueue_lib_scanner_scripts();
	}

	protected function enqueue_lib_scanner_scripts() {
		wp_enqueue_script(
			'smush-library-scanner',
			WP_SMUSH_URL . 'app/assets/js/smush-library-scanner.min.js',
			array( 'wp-i18n' ),
			WP_SMUSH_VERSION,
			true
		);

		wp_localize_script( 'smush-library-scanner', 'mediaLibraryScan', array(
			'nonce' => wp_create_nonce( 'wp_smush_media_library_scanner' ),
		) );
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		if ( ! is_network_admin() ) {
			$bg_optimization = WP_Smush::get_instance()->core()->mod->bg_optimization;

			if ( ! $bg_optimization->should_use_background() ) {
				$this->add_meta_box(
					'ajax-bulk-smush-in-progressing-notice',
					null,
					array( $this, 'ajax_bulk_smush_in_progressing_notice' ),
					null,
					null,
					'main',
					array(
						'box_class'         => 'sui-box ajax-bulk-smush-in-progressing-notice sui-hidden',
						'box_content_class' => false,
					)
				);
			}

			$background_health = Background_Pre_Flight_Controller::get_instance();
			if ( ! $background_health->is_cron_healthy() ) {
				$this->add_meta_box(
					'cron-disabled-notice',
					null,
					array( $this, 'cron_disabled_notice_meta_box' ),
					null,
					null,
					'main',
					array(
						'box_class'         => 'sui-box wp-smush-cron-disabled-notice-box',
						'box_content_class' => false,
					)
				);
			}

			$this->add_meta_box(
				'recheck-images-notice',
				null,
				array( $this, 'recheck_images_notice_meta_box' ),
				null,
				null,
				'main',
				array(
					'box_class'         => 'sui-box wp-smush-recheck-images-notice-box sui-hidden',
					'box_content_class' => false,
				)
			);

			$scan_background_process       = Background_Media_Library_Scanner::get_instance()->get_background_process();
			$is_scan_process_dead          = $scan_background_process->get_status()->is_dead();
			$show_bulk_smush_inline_notice = $bg_optimization->is_background_enabled() && $bg_optimization->is_dead();
			// Do not show failed bulk smush inline notice when required re-check images.
			$show_bulk_smush_inline_notice = $show_bulk_smush_inline_notice && ! $is_scan_process_dead;
			if ( $show_bulk_smush_inline_notice ) {
				$this->add_meta_box(
					'inline-retry-bulk-smush-notice',
					null,
					array( $this, 'inline_retry_bulk_smush_notice_box' ),
					null,
					null,
					'main',
					array(
						'box_class'         => 'sui-box wp-smush-inline-retry-bulk-smush-notice-box',
						'box_content_class' => false,
					)
				);
			}
		}
		parent::register_meta_boxes();

		if ( ! is_network_admin() ) {
			$this->add_meta_box(
				'bulk',
				__( 'Bulk Smush', 'wp-smushit' ),
				array( $this, 'bulk_smush_metabox' ),
				null,
				null,
				'main',
				array(
					'box_class' => 'sui-box bulk-smush-wrapper',
				)
			);
		}

		$class = WP_Smush::is_pro() ? 'wp-smush-pro' : '';
		$this->add_meta_box(
			'bulk-settings',
			__( 'Settings', 'wp-smushit' ),
			array( $this, 'bulk_settings_meta_box' ),
			null,
			array( $this, 'common_meta_box_footer' ),
			'main',
			array(
				'box_class' => "sui-box smush-settings-wrapper {$class}",
			)
		);

		$this->modals['restore-images'] = array();
	}

	/**************************
	 * META BOXES
	 */

	/**
	 * Common footer meta box.
	 *
	 * @since 3.2.0
	 */
	public function common_meta_box_footer() {
		$this->view( 'meta-box-footer', array(), 'common' );
	}

	/**
	 * Bulk smush meta box.
	 *
	 * Container box to handle bulk smush actions. Show progress bars,
	 * bulk smush action buttons etc. in this box.
	 */
	public function bulk_smush_metabox() {
		$core         = WP_Smush::get_instance()->core();
		$global_stats = $core->get_global_stats();
		$array_utils  = new Array_Utils();

		$bulk_upgrade_url       = $this->get_utm_link(
			array(
				'utm_campaign' => 'smush_bulk_smush_complete_global',
			)
		);
		$in_progress_upsell_url = $this->get_utm_link(
			array(
				'utm_campaign' => 'smush_bulk_smush_progress_BO',
			)
		);
		$upsell_cdn_url 		= $this->get_utm_link(
			array(
				'utm_campaign' => 'smush_bulksmush_cdn',
			)
		);

		$bg_optimization               = WP_Smush::get_instance()->core()->mod->bg_optimization;
		$background_processing_enabled = $bg_optimization->should_use_background();
		$background_in_processing      = $background_processing_enabled && $bg_optimization->is_in_processing();

		if ( $bg_optimization->can_use_background() ) {
			$upsell_text = '';
		} else {
			$upsell_text = sprintf(
				/* translators: 1: Open the link, 2: Close the link */
				__( 'Want to close this tab? Smush Pro lets you optimize in the background — %1$sOn sale now!%2$s', 'wp-smushit' ),
				'<a class="smush-upsell-link" target="_blank" href="' . esc_url( $in_progress_upsell_url ) . '">',
				'</a>'
			);
		}
		$in_processing_notice = sprintf(
			/* translators: %s: Upsell text */
			__( 'Bulk Smush is currently running. Please keep this page open until the process is complete. %s', 'wp-smushit' ),
			$upsell_text
		);

		$this->view(
			'bulk/meta-box',
			array(
				'core'                            => $core,
				'can_use_background'              => $bg_optimization->can_use_background(),
				'is_pro'                          => WP_Smush::is_pro(),
				'unsmushed_count'                 => (int) $array_utils->get_array_value( $global_stats, 'count_unsmushed' ),
				'resmush_count'                   => (int) $array_utils->get_array_value( $global_stats, 'count_resmush' ),
				'remaining_count'                 => (int) $array_utils->get_array_value( $global_stats, 'remaining_count' ),
				'total_count'                     => (int) $array_utils->get_array_value( $global_stats, 'count_total' ),
				'bulk_upgrade_url'                => $bulk_upgrade_url,
				'upsell_cdn_url'                  => $upsell_cdn_url,
				'background_processing_enabled'   => $background_processing_enabled,
				'background_in_processing'        => $background_in_processing,
				'background_in_processing_notice' => $bg_optimization->get_in_process_notice(),
				'in_processing_notice'            => $in_processing_notice,
			)
		);
	}

	/**
	 * Settings meta box.
	 *
	 * Free and pro version settings are shown in same section. For free users, pro settings won't be shown.
	 * To print full size smush, resize and backup in group, we hook at `smush_setting_column_right_end`.
	 */
	public function bulk_settings_meta_box() {
		$fields = $this->settings->get_bulk_fields();

		// Remove backups setting, as it's added separately.
		$key = array_search( 'backup', $fields, true );
		if ( false !== $key ) {
			unset( $fields[ $key ] );
		}

		// Remove no_scale setting, as it's added separately.
		$key = array_search( 'no_scale', $fields, true );
		if ( false !== $key ) {
			unset( $fields[ $key ] );
		}

		$this->view(
			'bulk-settings/meta-box',
			array(
				'basic_features'   => Settings::$basic_features,
				'cdn_enabled'      => $this->settings->get( 'cdn' ),
				'grouped_settings' => $fields,
				'settings'         => $this->settings->get(),
			)
		);
	}

	public function add_pro_tag( $name ) {
		$settings = Settings::get_instance();
		if ( ! $settings->is_pro_field( $name ) || $settings->can_access_pro_field( $name ) ) {
			return;
		}
		?>
		<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'wp-smushit' ); ?></span>
		<?php
	}

	public function recheck_images_notice_meta_box() {
		$this->view(
			'recheck-images-notice',
			array(),
			'common'
		);
	}


	public function inline_retry_bulk_smush_notice_box() {
		$this->view( 'bulk/inline-retry-bulk-smush-notice' );
	}

	public function ajax_bulk_smush_in_progressing_notice() {
		$this->view(
			'ajax-bulk-smush-in-progressing-notice',
			array(),
			'views/bulk'
		);
	}

	public function cron_disabled_notice_meta_box() {
		$this->view( 'bulk/cron-disabled-notice' );
	}
}
