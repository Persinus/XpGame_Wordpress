<?php
/**
 * Settings meta box.
 *
 * @package WP_Smush
 *
 * @var array $basic_features    Basic features list.
 * @var bool  $cdn_enabled       CDN status.
 * @var array $settings          Settings values.
 * @var bool  $backup_exists     Number of attachments with backups.
 */

use Smush\Core\Settings;

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<?php if ( WP_Smush::is_pro() && $cdn_enabled && Settings::can_access( 'bulk' ) ) : ?>
	<div class="sui-notice sui-notice-info">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
				<p>
					<?php
					echo esc_html(
						$this->whitelabel->whitelabel_string(
							__( 'Your images are currently being served via the WPMU DEV CDN. Bulk smush will continue to operate as per your settings below and is treated completely separately in case you ever want to disable the CDN.', 'wp-smushit' )
						)
					);
					?>
				</p>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php
do_action( 'wp_smush_bulk_smush_settings', $grouped_settings );

do_action_deprecated(
	'wp_smush_after_basic_settings',
	array(),
	'3.21.0',
	'wp_smush_after_advanced_settings',
	__( 'The wp_smush_after_basic_settings hook is deprecated. Use wp_smush_after_advanced_settings instead.', 'wp-smushit' )
);