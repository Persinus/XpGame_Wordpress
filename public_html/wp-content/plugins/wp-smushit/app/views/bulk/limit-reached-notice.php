<?php
/**
 * Limit reached notice metabox on bulk smush page.
 *
 * @var bool $with_resume_button   With resume button or not.
 */
?>
<div id="smush-limit-reached-notice" class="sui-notice sui-notice-warning sui-hidden smush-limit-reached-notice">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
			<p>
				<?php
				$is_directory_smush = 'smush-directory' === $this->get_slug();
				$upgrade_url        = $this->get_utm_link(
					array(
						'utm_campaign' => $is_directory_smush ? 'smush_directory_smush_paused_50_limit' : 'smush_bulk_smush_paused_50_limit',
					)
				);
				printf(
					/* translators: %s1$d - bulk smush limit, %2$s - upgrade link, %3$s - <strong>, %4$s - </strong>, %5$s - Bulk Smush limit */
					esc_html__( 'The free version of Smush only allows you to compress %1$d images at a time. Skip limits, save time. Bulk Smush unlimited images with Pro — %2$sOn Sale Now!%3$s', 'wp-smushit' ),
					Smush\Core\Core::MAX_FREE_BULK,
					'<a class="smush-upsell-link" href="' . esc_url( $upgrade_url ) . '" target="_blank">',
					'</a>',
				);
				?>
			</p>
		</div>
	</div>
</div>
