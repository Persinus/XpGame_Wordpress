<?php
if ( class_exists( 'WP_Customize_Control' ) ) {
	class NewsBlogger_Img_Radio_Control extends WP_Customize_Control {
		protected function get_newsblogger_resource_url() {
			if( strpos( wp_normalize_path( __DIR__ ), wp_normalize_path( WP_PLUGIN_DIR ) ) === 0 ) {
				// We're in a plugin directory and need to determine the url accordingly.
				return plugin_dir_url( __DIR__ );
			}

			return trailingslashit( get_template_directory_uri() );
		}
	}

	class NewsBlogger_Image_Radio_Button_Custom_Control extends NewsBlogger_Img_Radio_Control {
		/**
		 * The type of control being rendered
		 */
		public $type = 'image_radio_button';
		/**
		 * Enqueue our scripts and styles
		 */
		public function enqueue() {
			wp_enqueue_style( 'newsblogger-custom-img-radio-controls-css', NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI . '/inc/customizer/custom-controls/customizer-image-radio/css/customizer.css', array(), '1.0', 'all' );
		}
		/**
		 * Render the control in the customizer
		 */
		public function render_content() {
		?>
			<div class="image_radio_button_control">
				<?php if( !empty( $this->label ) ) { ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php } ?>
				<?php if( !empty( $this->description ) ) { ?>
					<span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php } ?>

				<?php $i=1; 
					foreach ( $this->choices as $key => $value ) { ?>
					<label class="radio-button-label">
						<input id="input_<?php echo esc_attr( $i ); ?>" type="radio" name="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php $this->link(); ?> <?php checked( esc_attr( $key ), $this->value() ); ?>/>
						<img id="customizer_img_<?php echo esc_attr( $i ); ?>" src="<?php echo esc_attr( $value['image'] ); ?>" alt="<?php echo esc_attr( isset($value['name']) ); ?>" />
					</label>
				<?php	$i++; } ?>
			</div>
		<?php
		}
	}
}