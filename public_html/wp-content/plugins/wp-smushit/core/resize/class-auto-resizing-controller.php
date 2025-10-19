<?php

namespace Smush\Core\Resize;

use Smush\Core\Controller;
use Smush\Core\Media\Attachment_Url_Cache;
use Smush\Core\Settings;
use Smush\Core\Srcset\Srcset_Helper;

class Auto_Resizing_Controller extends Controller {
	const AUTO_RESIZE_TRANSFORM_PRIORITY = 25;
	/**
	 * @var Settings
	 */
	private $settings;
	/**
	 * @var Srcset_Helper
	 */
	private $srcset_helper;

	public function __construct() {
		$this->settings      = Settings::get_instance();
		$this->srcset_helper = Srcset_Helper::get_instance();

		$this->register_filter( 'wp_smush_content_transforms', array(
			$this,
			'register_auto_resize_transform',
		), self::AUTO_RESIZE_TRANSFORM_PRIORITY );
		$this->register_filter( 'wp_calculate_image_sizes', array( $this, 'filter_image_sizes_for_content' ), 1, 2 );
		$this->register_filter( 'wp_smush_cdn_force_generate_srcset', array( $this, 'should_generate_srcset' ), 10, 2 );

		if ( $this->should_run() ) {
			// Auto resizing needs the URL cache to be primed
			Attachment_Url_Cache::get_instance()->set_fetch_in_advance( true );
		}
	}

	public function should_run() {
		return $this->settings->is_lazyload_active() && $this->settings->is_auto_resizing_active();
	}

	public function register_auto_resize_transform( $transforms ) {
		$transforms['auto_resize'] = new Auto_Resizing_Transform();

		return $transforms;
	}

	public function filter_image_sizes_for_content( $sizes, $size ) {
		if ( ! doing_filter( 'the_content' ) ) {
			return $sizes;
		}

		return $this->srcset_helper->update_image_sizes( $sizes, $size );
	}

	/**
	 * Filter to determine if srcset should be generated via CDN transform.
	 *
	 * @param bool $should_generate_srcset Whether to generate srcset.
	 * @param Element_Attribute|null $srcset_attribute Existing srcset attribute.
	 *
	 * @return bool
	 */
	public function should_generate_srcset( $should_generate_srcset, $srcset_attribute ) {
		if ( $should_generate_srcset || ! empty( $srcset_attribute ) ) {
			return $should_generate_srcset;
		}

		// Allow to add srcset if missing srcset when auto resizing is active and dynamic sizes is not.
		return $this->settings->is_auto_resizing_active() && $this->settings->is_lazyload_active();
	}
}
