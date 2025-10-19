<?php
/**
 * Lazy loading disabled meta box.
 *
 * @since 3.2.0
 * @package WP_Smush
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>


<img src="<?php echo esc_url( WP_SMUSH_URL . 'app/assets/images/graphic-smush-lazyload-default.png' ); ?>"
    srcset="<?php echo esc_url( WP_SMUSH_URL . 'app/assets/images/graphic-smush-lazyload-default@2x.png' ); ?> 2x"
    alt="<?php esc_html_e( 'Lazy Load', 'wp-smushit' ); ?>" class="sui-image">

<div class="sui-message-content">
	<p>
		<?php esc_html_e( 'This feature delays loading offscreen images until they\'re in view, helping your page load faster, use less bandwidth, and meet Google PageSpeed recommendations such as deferring offscreen images, properly sizing them, and setting explicit width and height.', 'wp-smushit' ); ?>
	</p>

	<button class="sui-button sui-button-blue" id="smush-enable-lazyload">
		<span class="sui-loading-text"><?php esc_html_e( 'Activate', 'wp-smushit' ); ?></span>
		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
	</button>
</div>

