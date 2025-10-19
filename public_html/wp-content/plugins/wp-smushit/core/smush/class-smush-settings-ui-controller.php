<?php
namespace Smush\Core\Smush;

use Smush\Core\Backups\Backups;
use Smush\Core\Controller;
use Smush\Core\Helper;
use Smush\Core\Settings;
use WP_Smush;

class Smush_Settings_UI_Controller extends Controller {
	const ADVANCED_FIELDS = array(
		'resize',
		'png_to_jpg',
		'background_email',
		'bulk_restore',
	);
	/**
	 * @var Settings
	 */
	private $settings;

	public function __construct() {
		$this->settings = Settings::get_instance();

		$this->register_action( 'smush_setting_column_right_inside', array( $this, 'settings_desc' ), 10, 2 );
		$this->register_action( 'smush_setting_column_right_inside', array( $this, 'auto_smush' ), 15, 2 );
		$this->register_action( 'smush_setting_column_right_outside', array( $this, 'image_sizes' ), 15, 2 );
		$this->register_action( 'smush_setting_column_right_additional', array( $this, 'resize_settings' ), 20 );
		$this->register_action( 'smush_setting_column_right_outside', array( $this, 'full_size_options' ), 20, 2 );
		$this->register_action( 'smush_setting_column_right_outside', array( $this, 'scale_options' ), 20, 2 );
		$this->register_action( 'wp_smush_render_setting_row', array( $this, 'set_background_email_setting_visibility' ) );

		$this->register_action( 'wp_smush_bulk_smush_settings', array( $this, 'render_basic_settings' ) );
		$this->register_action( 'wp_smush_bulk_smush_settings', array( $this, 'render_advanced_settings' ), 20 );
		$this->register_action( 'wp_smush_after_advanced_settings', array( $this, 'render_bulk_restore_field' ) );
	}

	/**
	 * Show additional descriptions for settings.
	 *
	 * @param string $setting_key Setting key.
	 */
	public function settings_desc( $setting_key = '' ) {
		if ( empty( $setting_key ) || ! in_array(
			$setting_key,
			array( 'resize', 'original', 'strip_exif', 'png_to_jpg', 'background_email' ),
			true
		) ) {
			return;
		}

		if ( 'png_to_jpg' === $setting_key ) {
			?>
			<div class="sui-toggle-content">
				<div class="sui-notice sui-notice-info" style="margin-top: 10px">
					<div class="sui-notice-content">
						<div class="sui-notice-message smush-png2jpg-setting-note">
							<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
							<p>
								<?php
									/* translators: 1: <strong> 2: </strong> */
									printf( esc_html__( 'Note: Any PNGs with transparency will be ignored. Smush will only convert PNGs if it results in a smaller file size. The original PNG file will be deleted, and the resulting file will have a new filename and extension (JPEG). %1$sAny hard-coded URLs on your site that contain the original PNG filename will need to be updated manually.%2$s', 'wp-smushit' ), '<strong>', '</strong>' );
								?>
								<br/>
								<span>
									<?php
										/* translators: 1: <strong> 2: </strong> */
										printf( esc_html__( '%1$sBackup original images%2$s must be enabled if you wish to retain the original PNG image as a backup.', 'wp-smushit' ), '<strong>', '</strong>' );
									?>
								</span>
							</p>
						</div>
					</div>
				</div>
			</div>
			<?php
			return;
		}

		global $wp_version;

		?>
		<span class="sui-description sui-toggle-description" id="<?php echo esc_attr( $setting_key . '-desc' ); ?>">
			<?php
			switch ( $setting_key ) {
				case 'resize':
					if ( version_compare( $wp_version, '5.2.999', '>' ) ) {
						esc_html_e( 'As of WordPress 5.3, large image uploads are resized down to a specified max width and height. If you require images larger than 2560px, you can override this setting here.', 'wp-smushit' );
					} else {
						esc_html_e( 'Save a ton of space by not storing over-sized images on your server. Set a maximum height and width for all images uploaded to your site so that any unnecessarily large images are automatically resized before they are added to the media gallery. This setting does not apply to images smushed using Directory Smush feature.', 'wp-smushit' );
					}
					break;
				case 'original':
					esc_html_e( 'By default, WordPress will only optimize the generated attachments when you upload images, not the original ones. Enable this feature to optimize the original images.', 'wp-smushit' );
					break;
				case 'strip_exif':
					esc_html_e(
						'Note: This data adds to the image file size. While it may be useful for photographers, it’s safe to remove for most users. This does not strip SEO metadata.',
						'wp-smushit'
					);
					break;
				case 'background_email':
					$bg_optimization = WP_Smush::get_instance()->core()->mod->bg_optimization;
					if ( $bg_optimization->can_use_background() ) {
						/* translators: %s: Email address. */
						$bg_email_desc = sprintf( __( 'You will receive an email at <strong>%s</strong> when the bulk smush has completed.', 'wp-smushit' ), $bg_optimization->get_mail_recipient() );
					} else {
						$bulk_upgrade_url = Helper::get_utm_link(
							array(
								'utm_campaign' => 'smush_bulk_smush_BO_email_toggle',
							)
						);
						$bg_email_desc    = sprintf(
							/* translators: 1: Open link tag <a>, 2: Close link tag </a> */
							esc_html__( 'Get the email notification as part of the Background Optimization feature. You don’t have to keep the bulk smush page open when it is in progress. Be notified when Background Optimization completes. %1$sUnlock now with Pro%2$s', 'wp-smushit' ),
							'<a href="' . esc_url( $bulk_upgrade_url ) . '" class="smush-upsell-link" target="_blank">',
							'</a>'
						);
					}
					echo wp_kses_post( $bg_email_desc );
					break;
				default:
					break;
			}
			?>
		</span>
		<?php
	}

	/**
	 * Prints notice after auto compress settings.
	 *
	 * @since 3.2.1
	 *
	 * @param string $name  Setting key.
	 */
	public function auto_smush( $name = '' ) {
		// Add only to auto smush settings.
		if ( 'auto' !== $name ) {
			return;
		}
		?>
		<div class="sui-toggle-content">
			<div class="sui-notice <?php echo $this->settings->get( 'auto' ) ? '' : ' sui-hidden'; ?>" style="margin-top: 10px">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
						<p><?php esc_html_e( 'Note: We will only automatically compress the image sizes selected above.', 'wp-smushit' ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Prints all the registered image sizes, to be selected/unselected for smushing.
	 *
	 * @param string $name Setting key.
	 *
	 * @return void
	 */
	public function image_sizes( $name = '' ) {
		// Add only to bulk smush settings.
		if ( 'bulk' !== $name ) {
			return;
		}

		// Additional image sizes.
		$image_sizes  = $this->settings->get_setting( 'wp-smush-image_sizes' );
		$sizes        = WP_Smush::get_instance()->core()->image_dimensions();
		$all_selected = false === $image_sizes || count( $image_sizes ) === count( $sizes );
		?>
		<?php if ( ! empty( $sizes ) ) : ?>
			<div class="sui-side-tabs sui-tabs">
				<div data-tabs="">
					<label for="all-image-sizes" class="sui-tab-item <?php echo $all_selected ? 'active' : ''; ?>">
						<input type="radio" name="wp-smush-auto-image-sizes" value="all" id="all-image-sizes" <?php checked( $all_selected ); ?>>
						<?php esc_html_e( 'All', 'wp-smushit' ); ?>
					</label>
					<label for="custom-image-sizes" class="sui-tab-item <?php echo $all_selected ? '' : 'active'; ?>">
						<input type="radio" name="wp-smush-auto-image-sizes" value="custom" id="custom-image-sizes" <?php checked( $all_selected, false ); ?>>
						<?php esc_html_e( 'Custom', 'wp-smushit' ); ?>
					</label>
				</div><!-- end data-tabs -->
				<div data-panes>
					<div class="sui-tab-boxed <?php echo $all_selected ? 'active' : ''; ?>" style="display:none"></div>
					<div class="sui-tab-boxed <?php echo $all_selected ? '' : 'active'; ?>">
						<span class="sui-label"><?php esc_html_e( 'Included image sizes', 'wp-smushit' ); ?></span>
						<?php
						foreach ( $sizes as $size_k => $size ) {
							// If image sizes array isn't set, mark all checked ( Default Values ).
							if ( false === $image_sizes ) {
								$checked = true;
							} else {
								// WPMDUDEV hosting support: cast $size_k to string to properly work with object cache.
								$checked = is_array( $image_sizes ) && in_array( (string) $size_k, $image_sizes, true );
							}
							?>
							<label class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
								<input type="checkbox" <?php checked( $checked, true ); ?>
									id="wp-smush-size-<?php echo esc_attr( $size_k ); ?>"
									name="wp-smush-image_sizes[]"
									value="<?php echo esc_attr( $size_k ); ?>">
								<span aria-hidden="true">&nbsp;</span>
								<span>
									<?php if ( isset( $size['width'], $size['height'] ) ) : ?>
										<?php echo esc_html( $size_k . ' (' . $size['width'] . 'x' . $size['height'] . ') ' ); ?>
									<?php else : ?>
										<?php echo esc_attr( $size_k ); ?>
									<?php endif; ?>
								</span>
							</label>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<?php
		endif;
	}



	/**
	 * Prints Dimensions required for Resizing
	 *
	 * @param string $name Setting name.
	 */
	public function resize_settings( $name = '' ) {
		// Add only to full size settings.
		if ( 'resize' !== $name ) {
			return;
		}

		// Dimensions.
		$resize_sizes = $this->settings->get_setting(
			'wp-smush-resize_sizes',
			array(
				'width'  => '',
				'height' => '',
			)
		);

		// Get max dimensions.
		$max_sizes = WP_Smush::get_instance()->core()->get_max_image_dimensions();

		$setting_status = $this->settings->get( 'resize' );
		?>
		<div tabindex="0" class="sui-toggle-content">
			<div class="sui-border-frame<?php echo $setting_status ? '' : ' sui-hidden'; ?>" id="smush-resize-settings-wrap" style="margin-bottom: 10px;">
				<div class="sui-row">
					<div class="sui-col-md-6">
						<div class="sui-form-field">
							<label aria-labelledby="wp-smush-label-max-width" for="<?php echo 'wp-smush-' . esc_attr( $name ) . '_width'; ?>" class="sui-label">
								<?php esc_html_e( 'Max width', 'wp-smushit' ); ?>
							</label>
							<input aria-required="true" type="number" class="sui-form-control wp-smush-resize-input"
								aria-describedby="wp-smush-resize-note"
								id="<?php echo 'wp-smush-' . esc_attr( $name ) . '_width'; ?>"
								name="<?php echo 'wp-smush-' . esc_attr( $name ) . '_width'; ?>"
								min="0"
								value="<?php echo isset( $resize_sizes['width'] ) && ! empty( $resize_sizes['width'] ) ? absint( $resize_sizes['width'] ) : $this->settings->get_default_size_threshold(); ?>">
						</div>
					</div>
					<div class="sui-col-md-6">
						<div class="sui-form-field">
							<label aria-labelledby="wp-smush-label-max-height" for="<?php echo 'wp-smush-' . esc_attr( $name ) . '_height'; ?>" class="sui-label">
								<?php esc_html_e( 'Max height', 'wp-smushit' ); ?>
							</label>
							<input aria-required="true" type="number" class="sui-form-control wp-smush-resize-input"
								aria-describedby="wp-smush-resize-note"
								id="<?php echo 'wp-smush-' . esc_attr( $name ) . '_height'; ?>"
								name="<?php echo 'wp-smush-' . esc_attr( $name ) . '_height'; ?>"
								min="0"
								value="<?php echo isset( $resize_sizes['height'] ) && ! empty( $resize_sizes['height'] ) ? absint( $resize_sizes['height'] ) : $this->settings->get_default_size_threshold(); ?>">
						</div>
					</div>
				</div>
				<div class="sui-description" id="wp-smush-resize-note">
					<?php
					printf( /* translators: %1$s: strong tag, %2$d: max width size, %3$s: tag, %4$d: max height size, %5$s: closing strong tag  */
						esc_html__( 'Currently, your largest image size is set at %1$s%2$dpx wide %3$s %4$dpx high%5$s.', 'wp-smushit' ),
						'<strong>',
						esc_html( $max_sizes['width'] ),
						'&times;',
						esc_html( $max_sizes['height'] ),
						'</strong>'
					);
					?>
					<div class="sui-notice sui-notice-warning wp-smush-update-dimensions sui-no-margin-bottom sui-hidden" style="margin-top:5px">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
								<p><?php esc_html_e( "Just to let you know, the dimensions you've entered are less than your largest image and may result in pixelation.", 'wp-smushit' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<span class="sui-description">
				<?php
				printf( /* translators: %s: link to gifgifs.com */
					esc_html__(
						'Note: Image resizing happens automatically when you upload attachments. To support retina devices, we recommend using 2x the dimensions of your image size. Animated GIFs will not be resized as they will lose their animation, please use a tool such as %s to resize then re-upload.',
						'wp-smushit'
					),
					'<a href="https://gifgifs.com/resizer/" target="_blank">https://gifgifs.com/resizer/</a>'
				);
				?>
			</span>
		</div>
		<?php
	}

	/**
	 * Prints Resize, Smush Original, and Backup settings.
	 *
	 * @param string $name  Name of the current setting being processed.
	 */
	public function full_size_options( $name = '' ) {
		// Continue only if original image option.
		if ( 'original' !== $name ) {
			return;
		}

		$value = $this->settings->get( 'backup' );
		?>
		<div class="sui-form-field">
			<label for="backup" class="sui-toggle">
				<input
					type="checkbox"
					value="1"
					id="backup"
					name="backup"
					aria-labelledby="backup-label"
					aria-describedby="backup-desc"
					<?php checked( $value, 1 ); ?>
				/>
				<span class="sui-toggle-slider" aria-hidden="true"></span>
				<span id="backup-label" class="sui-toggle-label">
					<?php echo esc_html( Settings::get_setting_data( 'backup', 'label' ) ); ?>
				</span>
				<span class="sui-description sui-toggle-description" id="backup-desc">
					<?php echo esc_html( Settings::get_setting_data( 'backup', 'desc' ) ); ?>
				</span>
			</label>

			<div class="sui-toggle-content <?php echo $this->settings->get( 'original' ) ? 'sui-hidden' : ''; ?>" id="backup-notice">
				<div class="sui-notice" style="margin-top: 10px">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
							<p>
								<?php
								printf( /* translators: %1$s - <strong>, %2$s - </strong> */
									esc_html__( '%1$sOptimize original images%2$s is disabled, which means that enabling %1$sBackup original images%2$s won’t yield additional benefits and will use more storage space. We recommend enabling %1$sBackup original images%2$s only if %1$sOptimize original images%2$s is also enabled.', 'wp-smushit' ),
									'<strong>',
									'</strong>'
								);
								?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Add scale image settings.
	 *
	 * @since 3.9.1
	 *
	 * @param string $name  Name of the current setting being processed.
	 */
	public function scale_options( $name = '' ) {
		if ( 'resize' !== $name ) {
			return;
		}

		// Not available on WordPress before 5.3.
		global $wp_version;
		if ( version_compare( $wp_version, '5.3', '<' ) ) {
			return;
		}

		$value = $this->settings->get( 'no_scale' );
		?>
		<div class="sui-form-field">
			<label for="no_scale" class="sui-toggle">
				<input
					type="checkbox"
					value="1"
					id="no_scale"
					name="no_scale"
					aria-labelledby="no_scale-label"
					aria-describedby="no_scale-desc"
					<?php checked( $value, 1 ); ?>
				/>
				<span class="sui-toggle-slider" aria-hidden="true"></span>
				<span id="no_scale-label" class="sui-toggle-label">
					<?php echo esc_html( Settings::get_setting_data( 'no_scale', 'label' ) ); ?>
				</span>
				<span class="sui-description sui-toggle-description" id="no_scale-desc">
					<?php echo esc_html( Settings::get_setting_data( 'no_scale', 'desc' ) ); ?>
				</span>
			</label>
		</div>
		<?php
	}

	public function set_background_email_setting_visibility( $name ) {
		if ( 'background_email' !== $name ) {
			return;
		}

		$bg_optimization       = WP_Smush::get_instance()->core()->mod->bg_optimization;
		$is_background_enabled = $bg_optimization->should_use_background();

		if ( ! $is_background_enabled && $bg_optimization->can_use_background() ) {
			?>
			<style>
				.background_email-settings-row {
					display: none !important;
				}
			</style>
			<?php
		}
	}

	public function render_basic_settings( $bulk_settings ) {
		$basic_settings = array_diff( $bulk_settings, self::ADVANCED_FIELDS );
		?>
		<div class="smush-basic-settings" id="bulk-smush-basic-settings">
			<?php $this->render_bulk_settings( $basic_settings ); ?>
		</div>
		<?php
	}

	public function render_advanced_settings( $bulk_settings ) {
		$advanced_settings = array_intersect( $bulk_settings, self::ADVANCED_FIELDS );
		if ( empty( $advanced_settings ) ) {
			return;
		}

		?>
		<div class="sui-accordion sui-accordion-block smush-advanced-settings" id="bulk-smush-advanced-settings">
			<div class="sui-accordion-item">
				<div class="sui-accordion-item-header">
					<div class="sui-accordion-item-title sui-trim-title">Advanced Settings</div>
					<div class="sui-accordion-col-auto">
						<button type="button" class="sui-button-icon sui-accordion-open-indicator" aria-label="Open Item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
					</div>
				</div>
				<div class="sui-accordion-item-body">
					<?php
					$this->render_bulk_settings( $advanced_settings );
					do_action( 'wp_smush_after_advanced_settings' );
					?>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_bulk_settings( $bulk_settings ) {
		foreach ( $bulk_settings as $name ) {
			$can_access_pro        = $this->settings->can_access_pro_field( $name );
			$is_pro_field          = $this->settings->is_pro_field( $name );
			$is_upsell_field       = $this->settings->is_upsell_field( $name );
			$is_disabled_field     = ( $is_upsell_field || $is_pro_field ) && ! $can_access_pro;
			$is_pro_but_not_upsell = $is_pro_field && ! $is_upsell_field;
			// Only show pro upsell field on Bulk Smush page to avoid upselly UI.
			if ( $is_pro_but_not_upsell && ! $can_access_pro ) {
				continue;
			}

			$value = $this->settings->get( $name );
			$value = ( $is_disabled_field || empty( $value ) ) ? false : $value;

			// Show settings option.
			do_action( 'wp_smush_render_setting_row', $name, $value, $is_disabled_field, $is_upsell_field );
		}
	}

	public function render_bulk_restore_field() {
		$backups       = new Backups();
		$backup_exists = $backups->items_with_backup_exist();
		?>
		<div class="sui-box-settings-row" id="bulk-restore-settings-row">
			<div class="sui-box-settings-col-1">
				<span class="<?php echo WP_Smush::is_pro() ? 'sui-settings-label' : 'sui-settings-label-with-tag'; ?>">
					<?php esc_html_e( 'Bulk restore', 'wp-smushit' ); ?>
				</span>
				<span class="sui-description">
					<?php
					esc_html_e( 'Made a mistake? Use this feature to restore your image thumbnails to their original state.', 'wp-smushit' );
					?>
				</span>
			</div>

			<div class="sui-box-settings-col-2">
				<button type="button" class="sui-button sui-button-ghost wp-smush-restore" onclick="WP_Smush.restore.init()" <?php disabled( ! $backup_exists ); ?>>
					<i class="sui-icon-undo" aria-hidden="true"></i>
					<?php esc_html_e( 'Restore Thumbnails', 'wp-smushit' ); ?>
				</button>
				<span class="sui-description">
					<?php
					printf( /* translators: %1$s - strong tag, %2$s - closing strong tag */
						wp_kses( 'This feature regenerates thumbnails using your original uploaded images. If %1$sOptimize original images%2$s is enabled, your thumbnails can still be regenerated, but the quality will be impacted by the compression of your uploaded images.', 'wp-smushit' ),
						'<strong>',
						'</strong>'
					);
					?>
				</span>

				<div class="sui-notice" style="margin-top: 10px">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
							<p>
								<?php esc_html_e( 'Note: Backup original images must be enabled in order to bulk restore your images.', 'wp-smushit' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
