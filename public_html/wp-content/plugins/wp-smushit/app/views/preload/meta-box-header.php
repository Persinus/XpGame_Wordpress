<?php
/**
 * Preload meta box.
 *
 * @package WP_Smush
 * 
 * @var bool $lcp_preload_enabled LCP preload status.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<h3 class="sui-box-title"><?php esc_html_e( 'Preload Critical Images', 'wp-smushit' ); ?></h3>
<?php if ( ! WP_Smush::is_pro() ) : ?>
<span style="margin-left:15px" class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'wp-smushit' ); ?></span>
<?php endif; ?>