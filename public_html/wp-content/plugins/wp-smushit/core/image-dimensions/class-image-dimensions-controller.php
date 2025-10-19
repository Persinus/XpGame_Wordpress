<?php

namespace Smush\Core\Image_Dimensions;

use Smush\Core\Controller;
use Smush\Core\Settings;

class Image_Dimensions_Controller extends Controller {
	const IMAGE_DIMENSIONS_TRANSFORM_PRIORITY = 0;

	/**
	 * @var Settings
	 */
	private $settings;

	public function __construct() {
		$this->settings = Settings::get_instance();
		$this->register_action( 'wp_head', array( $this, 'add_inline_styles' ), 5 );
		$this->register_filter(
			'wp_smush_content_transforms',
			array(
				$this,
				'register_image_dimensions_transform',
			),
			self::IMAGE_DIMENSIONS_TRANSFORM_PRIORITY
		);
	}

	public function should_run() {
		return $this->settings->is_lazyload_active() && $this->settings->should_add_missing_dimensions();
	}

	public function register_image_dimensions_transform( $transforms ) {
		$transforms['image_dimensions'] = new Image_Dimensions_Transform();

		return $transforms;
	}

	public function add_inline_styles() {
		?>
		<style>
			.smush-dimensions {
				--smush-image-aspect-ratio: auto;
				aspect-ratio: var(--smush-image-aspect-ratio);
			}
		</style>
		<?php
	}
}
