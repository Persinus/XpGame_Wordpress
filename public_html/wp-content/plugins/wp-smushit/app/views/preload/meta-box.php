<?php
/**
 * Preload meta box.
 *
 * @package WP_Smush
 * 
 * @var bool $lcp_preload_enabled LCP preload status.
 * @var bool $preload_settings LCP preload settings.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<p>
	<?php
	esc_html_e( "Preload helps to improve the Largest Contentful Paint (LCP) metric by optimizing images that often form the main viewport content. Since LCP measures the rendering time of the largest visible element, Smush helps to achieve Google's recommended 2.5-second benchmark for good user experience.", 'wp-smushit' );
	?>
</p>

<div class="sui-box-settings-row" id="preload-images-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label">
			<?php esc_html_e( 'Preload Critical Images', 'wp-smushit' ); ?>
		</span>
		<span class="sui-description">
			<?php esc_html_e( 'Get a faster initial load and optimize your LCP audit by automatically preloading your most critical image.', 'wp-smushit' ); ?>
		</span>
	</div>

	<div class="sui-box-settings-col-2">
		<div class="sui-form-field">
			<label for="preload-images" class="sui-toggle">
				<input
					type="checkbox"
					id="preload-images"
					name="preload_images"
					aria-labelledby="preload-images-label"
					aria-describedby="preload-images-description"
					<?php echo WP_Smush::is_pro() ? '' : 'disabled'; ?>
					<?php checked( $lcp_preload_enabled ); ?>
				/>
				<span class="sui-toggle-slider" aria-hidden="true"></span>
				<span id="noscript-label" class="sui-toggle-label">
					<?php esc_html_e( 'Enable Preloading Critical Images', 'wp-smushit' ); ?>
				</span>
			</label>
			<?php
			if ( ! WP_Smush::is_pro() ) :
				$upgrade_url = $this->get_utm_link(
					array(
						'utm_campaign' => 'smush_lazyload-preload_preload-critical-images',
					)
				);
			?>
			<span id="preload-images-description" class="sui-description">
				<a style="margin-left:44px;" class="smush-upsell-link" href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank">
				<strong>
					<?php
						esc_html_e( 'Go Pro for Faster Load Times & Optimized LCP – On Sale Now!', 'wp-smushit' );
					?>
				</strong>
				<span class="sui-icon-open-new-window" aria-hidden="true"></span>
			</a>
			</span>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="sui-box-settings-row" id="preload-exclude-settings-row" style="display: <?php echo $lcp_preload_enabled ? 'flex' : 'none'; ?>">
    <div class="sui-box-settings-col-1">
		<span class="sui-settings-label">
			<?php esc_html_e( 'Exclude', 'wp-smushit' ); ?>
		</span>
        <span class="sui-description">
			<?php esc_html_e( 'Disable preload for specific pages or posts that you wish to prevent preload on', 'wp-smushit' ); ?>
		</span>
    </div>

    <div class="sui-box-settings-col-2">
        <div class="sui-form-field">
            <strong><?php esc_html_e( 'Post, Pages & URLs', 'wp-smushit' ); ?></strong>
            <div class="sui-description">
			    <?php esc_html_e( 'Specify URLs of the pages or posts you want to disable preload on.', 'wp-smushit' ); ?>
            </div>
		    <?php
		    $strings = '';
		    if ( isset( $preload_settings['exclude-pages'] ) && is_array( $preload_settings['exclude-pages'] ) ) {
			    $strings = join( PHP_EOL, $preload_settings['exclude-pages'] );
		    }
		    ?>
            <textarea class="sui-form-control" name="exclude-pages" placeholder="<?php esc_attr_e( 'E.g. /page', 'wp-smushit' ); ?>"><?php echo esc_attr( $strings ); ?></textarea>
            <div class="sui-description">
			    <?php
			    printf(
			    /* translators: %1$s - opening strong tag, %2$s - closing strong tag */
				    esc_html__( 'Add one page or post URL per line in relative format. I.e. %1$s/example-page%2$s or %1$s/example-page/sub-page/%2$s.', 'wp-smushit' ),
				    '<strong>',
				    '</strong>'
			    );
			    ?>
            </div>
        </div>
    </div>
</div>
<?php if ( WP_Smush::is_pro() ) :  ?>
<div class="sui-box-settings-row" id="preload-clear-lcp-settings">
    <div class="sui-box-settings-col-1">
		<span class="sui-settings-label">
			<?php esc_html_e( 'Clear LCP Data', 'wp-smushit' ); ?>
		</span>
        <span class="sui-description">
			<?php
			esc_html_e(
				'Remove all LCP data from the database. Use this option if you\'ve made image optimisations and want to ensure Smush identifies new critical images.',
				'wp-smushit'
			);
			?>
		</span>
    </div>
    <div class="sui-box-settings-col-2">
        <button class="sui-button sui-button-ghost" id="smush-clear-lcp-data">
			<span class="sui-loading-text">
				<?php esc_html_e( 'Clear LCP Data', 'wp-smushit' ); ?>
			</span>
            <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
        </button>
        <span class="sui-description sui-toggle-description">
            <?php
            esc_html_e(
	            'Note: This will not affect your images, but it will clear the stored LCP data across your entire site.',
	            'wp-smushit'
            );
            ?>
        </span>
    </div>
</div>
<?php endif; ?>
