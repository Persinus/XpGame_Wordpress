<?php
/**
 * Render Smush pages.
 *
 * @package WP_Smush
 *
 * @var Abstract_Page $this
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Some pages don't need to have wrapped in a form.
$page_without_forms = array();
$page_has_form      = ! in_array( $this->get_slug(), $page_without_forms, true );

$this->do_meta_boxes( 'summary' );

?>

<?php if ( ! $this->get_current_tab() ) : ?>
	<?php if ( $page_has_form ) : ?>
		<form id="<?php echo esc_attr( $this->get_slug() ); ?>-form" class="wp-smush-settings-form" method="post">
	<?php endif; ?>
		<?php $this->do_meta_boxes(); ?>

		<?php if ( 'smush-next-gen' === $this->get_slug() && $this->is_wizard() ) : ?>
			<div id="smush-box-webp-wizard" class="sui-webp-wizard sui-box"></div>
		<?php endif; ?>
	<?php if ( $page_has_form ) : ?>
		</form>
	<?php endif; ?>
<?php else : ?>
	<?php if ( 'configs' !== $this->get_current_tab() ) : ?>
		<form id="<?php echo esc_attr( $this->get_slug() ); ?>-form" method="post">
	<?php endif; ?>
		<?php do_action( 'wp_smush_admin_page_before_sidenav', $this->get_slug(), $this->get_current_tab() ); ?>
		<div class="sui-row-with-sidenav">
			<?php $this->show_tabs(); ?>
			<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>

			<?php if ( 'configs' === $this->get_current_tab() ) : ?>
				<div id="smush-box-configs"></div>
			<?php endif; ?>
		</div>
		<input type="hidden" name="tab" value="<?php echo esc_attr( $this->get_current_tab() ); ?>">
	<?php if ( 'configs' !== $this->get_current_tab() ) : ?>
		</form>
	<?php endif; ?>
<?php endif; ?>

<?php if ( $this->has_meta_boxes( 'box-dashboard-left' ) || $this->has_meta_boxes( 'box-dashboard-right' ) ) : ?>
	<div class="sui-row">
		<div class="sui-col-lg-6">
			<?php $this->do_meta_boxes( 'box-dashboard-left' ); ?>
		</div>
		<div class="sui-col-lg-6">
			<?php $this->do_meta_boxes( 'box-dashboard-right' ); ?>
			<?php if ( ! is_multisite() || is_network_admin() ) : ?>
				<div id="smush-widget-configs"></div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php
$this->view( 'footer-links', array(), 'common' );
