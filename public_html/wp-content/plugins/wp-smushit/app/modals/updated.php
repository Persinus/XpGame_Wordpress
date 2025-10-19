<?php
/**
 * Show Updated Features modal.
 *
 * @package WP_Smush
 *
 * @since 3.7.0
 *
 * @var string $cta_url URL for the modal's CTA button.
 * @var bool $show_cta_button Indicates whether the CTA button should be displayed.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="smush-updated-dialog"
		class="sui-modal-content smush-updated-dialog wp-smush-modal-dark-background"
		aria-modal="true"
		data-esc-close="false"
		aria-labelledby="smush-title-updated-dialog"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-sides--20">
				<figure class="sui-box-banner" aria-hidden="true">
					<img src="<?php echo esc_url( WP_SMUSH_URL . 'app/assets/images/updated/updated.png' ); ?>"
						srcset="<?php echo esc_url( WP_SMUSH_URL . 'app/assets/images/updated/updated.png' ); ?> 1x, <?php echo esc_url( WP_SMUSH_URL . 'app/assets/images/updated/updated' ); ?>@2x.png 2x"
						alt="<?php esc_attr_e( 'Smush Updated Modal', 'wp-smushit' ); ?>" class="sui-image sui-image-center">
				</figure>

				<button class="sui-button-icon sui-button-float--right sui-button-grey" style="box-shadow:none!important" onclick="WP_Smush.onboarding.hideUpgradeModal(event, this)">
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
				</button>
			</div>

			<div class="sui-box-body sui-content-center sui-spacing-sides--30 sui-spacing-top--40 sui-spacing-bottom--30">
				<h3 class="sui-box-title sui-lg" id="smush-title-updated-dialog" style="white-space: normal">
					<?php esc_html_e( 'New: Advanced Image Sizing', 'wp-smushit' ); ?>
					<?php if ( ! WP_Smush::is_pro() ) : ?>
                        <span class="sui-tag sui-tag-pro" style="font-size: 10px; line-height: 12px;">
                            <?php esc_html_e( 'Pro', 'wp-smushit' ); ?>
                        </span>
                    <?php endif; ?>
				</h3>


				<p class="sui-description">
					<?php esc_html_e( 'We\'ve streamlined the image sizing tools for ease of use. All resizing and detection features are now in one place - The Lazy Loading page.', 'wp-smushit' ); ?>
				</p>
				<div class="sui-modal-list" style="text-align: left; background-color: #F8F8F8; padding: 15px; border-radius: 5px;">
					<h4>
						<?php esc_html_e( 'What\'s New?', 'wp-smushit' ); ?>
					</h4>
					<ul>
						<li>
							<h3>
								<span class="sui-icon-check-tick sui-sm sui-success" aria-hidden="true"></span>
								<?php esc_html_e( 'New Automatic Resizing', 'wp-smushit' ); ?>
							</h3>
						</li>
						<li>
							<h3>
								<span class="sui-icon-check-tick sui-sm sui-success" aria-hidden="true"></span>

								<?php esc_html_e( 'Add Missing Dimensions', 'wp-smushit' ); ?>
							</h3>
						</li>
					</ul>
				</div>
			</div>
			<?php

			$cta_config = array(
				'label'     => __( 'Go to Image sizing', 'wp-smushit' ),
				'target'    => '_self',
				'classes'   => array(
					'sui-button',
					'wp-smush-upgrade-modal-cta',
					'sui-button-grey',
				),
				'show_icon' => false,
			);

			if ( ! WP_Smush::is_pro() ) {
				$cta_config['target']    = '_blank';
				$cta_config['label']     = __( ' UNLOCK PRO – ON SALE ', 'wp-smushit' );
				$cta_config['classes'][] = 'sui-button-purple';
				$cta_config['show_icon'] = true;
			}

			$class_string = implode( ' ', $cta_config['classes'] );
			?>
			<?php if ( $show_cta_button ) : ?>
			<div class="sui-box-footer sui-flatten sui-content-center sui-spacing-bottom--50">
				<a href="<?php echo esc_url( $cta_url ); ?>"
					target="<?php echo esc_attr( $cta_config['target'] ); ?>"
					class="<?php echo esc_attr( $class_string ); ?>"
					onclick="WP_Smush.onboarding.hideUpgradeModal(event, this)">
					<?php echo esc_html( $cta_config['label'] ); ?>

					<?php if ( $cta_config['show_icon'] ) : ?>
						<span class="sui-icon-open-new-window" aria-hidden="true"></span>
					<?php endif; ?>
				</a>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
