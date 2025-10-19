<?php
/**
 * Lazy load meta box.
 *
 * @since 3.8.6
 * @package WP_Smush
 *
 * @var bool  $is_lazy_load_active  Is lazy load module active.
 * @var bool  $is_preload_active    Is preload module active.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<p>
	<?php esc_html_e( 'Boost your site’s speed and PageSpeed scores with smart image loading. Lazy Load delays offscreen images for faster initial loads, while Image Sizing helps you with properly sized images and adds missing dimensions. Preload Critical Images ensures key above-the-fold content loads instantly—improving LCP and perceived performance.', 'wp-smushit' ); ?>
</p>


<div class="sui-box-settings-row sui-flushed sui-no-padding">
	<table class="sui-table sui-table-flushed">
		<thead>
		<tr>
			<th><?php esc_html_e( 'Available Features', 'wp-smushit' ); ?></th>
			<th><?php esc_html_e( 'Status', 'wp-smushit' ); ?></th>
		</tr>
		</thead>

		<tbody>
		<tr class="smush-feature-lazy-load">
			<td class="sui-table-item-title">
				<?php esc_html_e( 'Lazy Load', 'wp-smushit' ); ?>
			</td>
			<td>
				<?php if ( $is_lazy_load_active ) : ?>
					<span class="sui-tag sui-tag-green"><?php esc_html_e( 'Active', 'wp-smushit' ); ?></span>
				<?php else : ?>
					<span class="sui-tag"><?php esc_html_e( 'Inactive', 'wp-smushit' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<tr class="<?php echo WP_Smush::is_pro() ? '' : 'smush-disabled-table-row'; ?>">
			<td class="sui-table-item-title">
				<?php esc_html_e( 'Preload Critical Images', 'wp-smushit' ); ?>
			</td>
			<td>
				<?php if ( $is_preload_active ) : ?>
					<span class="sui-tag sui-tag-green"><?php esc_html_e( 'Active', 'wp-smushit' ); ?></span>
				<?php elseif ( ! WP_Smush::is_pro() ) : ?>
					<span class="sui-tag sui-tag-purple sui-tag-sm"><?php esc_html_e( 'PRO', 'wp-smushit' ); ?></span>
				<?php else: ?>
					<span class="sui-tag"><?php esc_html_e( 'Inactive', 'wp-smushit' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		</tbody>
	</table>
</div>

<a href="<?php echo esc_url( $this->get_url( 'smush-lazy-preload' ) ); ?>" class="sui-button sui-button-ghost">
	<span class="sui-icon-wrench-tool" aria-hidden="true"></span>
	<?php esc_html_e( 'Configure', 'wp-smushit' ); ?>
</a>
