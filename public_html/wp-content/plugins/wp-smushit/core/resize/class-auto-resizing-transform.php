<?php

namespace Smush\Core\Resize;

use Smush\Core\Keyword_Exclusions;
use Smush\Core\Lazy_Load\Lazy_Load_Helper;
use Smush\Core\Lazy_Load\Lazy_Load_Transform;
use Smush\Core\Parser\Composite_Element;
use Smush\Core\Parser\Element;
use Smush\Core\Parser\Element_Attribute;
use Smush\Core\Settings;
use Smush\Core\Srcset\Srcset_Helper;
use Smush\Core\Transform\Transform;
use Smush\Core\Url_Utils;

class Auto_Resizing_Transform implements Transform {
	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var Srcset_Helper
	 */
	private $srcset_helper;

	/**
	 * @var Url_Utils
	 */
	private $url_utils;

	/**
	 * @var Lazy_Load_Helper
	 */
	private $lazy_load_helper;

	/**
	 * Keyword Exclusions.
	 *
	 * @var Keyword_Exclusions
	 */
	private $keyword_exclusions;

	public function __construct() {
		$this->settings      = Settings::get_instance();
		$this->srcset_helper = Srcset_Helper::get_instance();
		$this->url_utils     = new Url_Utils();
		$this->lazy_load_helper = Lazy_Load_Helper::get_instance();
	}

	public function should_transform() {
		return $this->settings->is_lazyload_active() && $this->settings->is_auto_resizing_active();
	}

	public function transform_image_url( $url ) {
		return $url;
	}

	public function transform_page( $page ) {
		foreach ( $page->get_composite_elements() as $composite_element ) {
			if ( ! $this->is_composite_element_excluded( $composite_element ) ) {
				// TODO: (auto-resize) Handle <source> elements inside <picture> for proper srcset and sizes support with native lazy loading.
				// For now, we only transform the <img> elements inside the picture element.
				$this->transform_elements( $composite_element->get_elements() );
			}
		}

		$this->transform_elements( $page->get_elements() );
	}

	/**
	 * @param array $elements
	 *
	 * @return void
	 */
	private function transform_elements( array $elements ) {
		foreach ( $elements as $element ) {
			$this->transform_image_element( $element );
		}
	}

	private function transform_image_element( Element $element ) {
		if (
			! $element->is_image_element() ||
			$this->is_element_excluded( $element )
		) {
			return;
		}

		if ( $this->is_smush_lazy_loaded_element( $element ) ) {
			$this->transform_smush_lazy_loaded_image_element( $element );
		} else {
			$this->transform_standard_image_element( $element );
		}
	}

	private function transform_standard_image_element( Element $element ) {
		$src_url = $this->get_element_src_absolute_url( $element );
		if ( ! $src_url ) {
			return;
		}

		if ( $this->image_element_may_have_third_party_lazy_loading( $element ) ) {
			// This is a non-native lazy loaded image element.
			return;
		}

		$srcset = $element->get_attribute_value( 'srcset' );
		$sizes  = $element->get_attribute_value( 'sizes' );
		if ( $this->should_add_new_srcset_and_sizes( $element, 'srcset', $src_url ) ) {
			$generated_srcset_and_sizes = $this->generate_and_add_srcset_and_sizes( $src_url, $element, 'srcset', 'sizes' );
			if ( ! empty( $generated_srcset_and_sizes ) ) {
				list( $srcset, $sizes ) = $generated_srcset_and_sizes;
			}
		}

		if ( $this->should_add_sizes_auto_to_native_lazy_loaded_element( $element, $srcset, $sizes ) ) {
			// If the element has a srcset and sizes attributes, we can still add 'auto' to existing sizes attribute
			$this->add_sizes_auto_to_native_lazy_loaded_element( $element, $sizes );
		}
	}

	private function image_element_may_have_third_party_lazy_loading( Element $element ) {
		if ( $element->has_attribute( 'data-srcset' ) || $element->has_attribute( 'data-sizes' ) ) {
			return true;
		}

		if ( $element->has_attribute( 'data-src' ) && ! $element->has_attribute( 'srcset' ) ) {
			// This is a non-native lazy loaded image element.
			return true;
		}

		return apply_filters( 'wp_smush_element_has_third_party_lazy_loading', false, $element->get_markup() );
	}

	private function generate_and_add_srcset_and_sizes( $src_url, Element $element, $srcset_attr_name, $sizes_attr_name ) {
		$original_sizes_value    = (string) $element->get_attribute_value( $sizes_attr_name );
		$original_has_sizes_auto = $original_sizes_value && wp_sizes_attribute_includes_valid_auto( $original_sizes_value );
		$raw_width               = $element->get_attribute_value( 'width' );
		$width_from_attribute    = false === strpos( $raw_width, '%' ) ? (int) $raw_width : 0;
		$raw_height              = $element->get_attribute_value( 'height' );
		$height_from_attribute   = false === strpos( $raw_height, '%' ) ? (int) $raw_height : 0;
		list( $srcset, $sizes )  = $this->srcset_helper->generate_srcset_and_sizes( $src_url, 0, $width_from_attribute, $height_from_attribute );
		if ( empty( $srcset ) ) {
			return array();
		}

		$new_srcset_attribute = new Element_Attribute( $srcset_attr_name, $srcset );
		$element->add_attribute( $new_srcset_attribute );
		if ( $sizes ) {
			if ( $original_has_sizes_auto ) {
				$sizes = 'auto, ' . $sizes;
			}

			$new_sizes_attribute = new Element_Attribute( $sizes_attr_name, $sizes );
			$element->add_or_update_attribute( $new_sizes_attribute );
		}

		return array( $srcset, $sizes );
	}

	private function add_sizes_auto_to_native_lazy_loaded_element( Element $element, $sizes ) {
		$sizes               = 'auto, ' . $sizes;
		$new_sizes_attribute = new Element_Attribute( 'sizes', $sizes );
		$element->add_or_update_attribute( $new_sizes_attribute );
	}

	private function get_element_src_absolute_url( Element $element, $source_attribute_name = 'src' ) {
		$src_attribute = $element->get_attribute( $source_attribute_name );
		if ( ! $src_attribute ) {
			return null;
		}

		$src_image_url = $src_attribute->get_single_image_url();
		if ( empty( $src_image_url ) ) {
			return null;
		}

		return $src_image_url->get_absolute_url();
	}

	private function transform_smush_lazy_loaded_image_element( Element $element ) {
		$src_url = $this->get_element_src_absolute_url( $element, 'data-src' );
		if ( ! $src_url ) {
			return;
		}

		$srcset = $element->get_attribute_value( 'data-srcset' );
		$sizes  = $element->get_attribute_value( 'data-sizes' );
		if ( $this->should_add_new_srcset_and_sizes( $element, 'data-srcset', $src_url ) ) {
			$added_srcset_and_size = $this->generate_and_add_srcset_and_sizes( $src_url, $element, 'data-srcset', 'data-sizes' );
			if ( ! empty( $added_srcset_and_size ) ) {
				list( $srcset, $sizes ) = $added_srcset_and_size;
			}
		}

		if ( ! empty( $srcset ) && ! $this->skip_adding_auto_sizes( $element ) ) {
			$this->add_data_sizes_auto( $element, $sizes );
		}
	}

	private function add_data_sizes_auto( Element $element, $original_sizes ) {
		if ( $original_sizes ) {
			$backup_attribute = new Element_Attribute( 'data-original-sizes', $original_sizes );
			$element->add_attribute( $backup_attribute );
		}
		$new_sizes_attribute = new Element_Attribute( 'data-sizes', 'auto' );
		$element->add_or_update_attribute( $new_sizes_attribute );
	}

	private function is_smush_lazy_loaded_element( Element $element ) {
		if ( ! $this->settings->is_lazyload_active() ) {
			return false;
		}

		$src      = $element->get_attribute_value( 'src' );
		$data_src = $element->get_attribute_value( 'data-src' );
		if ( Lazy_Load_Transform::TEMP_SRC !== $src || empty( $data_src ) ) {
			return false;
		}

		$class = $element->get_attribute_value( 'class' );
		if (
			empty( $class ) ||
			! is_string( $class ) ||
			! in_array( Lazy_Load_Transform::LAZYLOAD_CLASS, explode( ' ', $class ), true )
		) {
			return false;
		}

		return true;
	}

	private function should_add_new_srcset_and_sizes( Element $element, $attribute_name, $src_url ) {
		if ( $element->has_attribute( $attribute_name ) ) {
			// Already present
			return false;
		}

		if ( $this->url_utils->is_external_url( $src_url ) ) {
			return false;
		}

		return ! $this->srcset_helper->skip_adding_srcset( $src_url, $element->get_markup() );
	}

	public function element_has_native_lazy_loading( $element ) {
		return 'lazy' === $element->get_attribute_value( 'loading' );
	}

	public function should_add_sizes_auto_to_native_lazy_loaded_element( Element $element, $srcset, $sizes ) {
		if ( ! $this->element_has_native_lazy_loading( $element ) ) {
			return false;
		}

		if ( empty( $srcset ) ) {
			return false;
		}

		// @see https://github.com/WordPress/wordpress-develop/pull/7812
		$width = $element->get_attribute_value( 'width' );
		if ( ! is_string( $width ) || empty( trim( $width ) ) ) {
			return false;
		}

		if ( empty( $sizes ) || wp_sizes_attribute_includes_valid_auto( $sizes ) ) {
			return false;
		}

		$wp_add_auto_sizes = apply_filters( 'wp_img_tag_add_auto_sizes', true );
		if ( ! $wp_add_auto_sizes ) {
			return false;
		}

		return ! $this->skip_adding_auto_sizes( $element );
	}

	private function skip_adding_auto_sizes( Element $element ) {
		return apply_filters( 'wp_smush_skip_adding_auto_sizes', false, $element->get_markup() );
	}

	/**
	 * @param Composite_Element $composite_element
	 *
	 * @return bool
	 */
	private function is_composite_element_excluded( Composite_Element $composite_element ): bool {
		foreach ( $composite_element->get_elements() as $sub_element ) {
			if ( $this->is_element_excluded( $sub_element ) ) {
				return true;
			}
		}
		return false;
	}

	private function is_element_excluded( Element $element ) {
		return $this->element_has_excluded_keywords( $element ) || $this->is_image_element_skipped_through_filter( $element );
	}

	private function element_has_excluded_keywords( Element $element ) {
		$keyword_exclusions = $this->keyword_exclusions();
		if ( ! $keyword_exclusions->has_excluded_keywords() ) {
			return false;
		}

		return $keyword_exclusions->is_markup_excluded( $element->get_markup() );
	}

	private function is_image_element_skipped_through_filter( Element $element ) {
		$image_attributes = $element->get_image_attributes();
		if ( empty( $image_attributes ) ) {
			return true;// This should not occur.
		}

		foreach ( $image_attributes as $attribute ) {
			$single_image_url = $attribute->get_single_image_url();
			if ( $single_image_url ) {
				return $this->srcset_helper->skip_adding_srcset( $single_image_url, $element->get_markup() );
			}
		}

		return false;
	}

	/**
	 * Get Keyword Exclusions.
	 *
	 * @return Keyword_Exclusions
	 */
	private function keyword_exclusions() {
		if ( ! $this->keyword_exclusions ) {
			$this->keyword_exclusions = new Keyword_Exclusions( $this->get_excluded_keywords() );
		}

		return $this->keyword_exclusions;
	}

	private function get_excluded_keywords() {
		$default_exclude_keywords = array(
			'data-no-auto-resize',
		);

		return apply_filters( 'wp_smush_auto_resizing_excluded_keywords', array_unique( $default_exclude_keywords ) );
	}
}
