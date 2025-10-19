<?php

namespace Smush\Core\Image_Dimensions;

use Smush\Core\Url_Utils;
use Smush\Core\Settings;
use Smush\Core\Parser\Element;
use Smush\Core\Transform\Transform;
use Smush\Core\Parser\Element_Attribute;
use Smush\Core\Transform\Transformation_Controller;

class Image_Dimensions_Transform implements Transform {
	const IMAGE_DIMENSIONS_CLASS = 'smush-dimensions';

	/**
	 * @var Url_Utils
	 */
	private $url_utils;

	/**
	 * @var array
	 */
	private $excluded_attributes;
	private $settings;

	public function __construct() {
		$this->url_utils = new Url_Utils();
		$this->settings  = Settings::get_instance();
	}

	public function should_transform() {
		return $this->settings->get( 'image_dimensions' );
	}

	public function transform_image_url( $url ) {
		return $url;
	}

	public function transform_page( $page ) {
		foreach ( $page->get_composite_elements() as $composite_element ) {
			$this->transform_elements( $composite_element->get_elements() );
		}

		$this->transform_elements( $page->get_elements() );
	}

	/**
	 * @param array $elements Image elements.
	 *
	 * @return void
	 */
	private function transform_elements( array $elements ) {
		foreach ( $elements as $element ) {
			$this->transform_element( $element );
		}
	}

	private function transform_element( $element ) {
		if ( $this->can_specify_dimensions( $element ) ) {
			$this->update_element_attributes( $element );
		}
	}

	/**
	 * Update image element attributes.
	 *
	 * @param Element $element Image element.
	 *
	 * @return void
	 */
	private function update_element_attributes( Element $image_element ) {
		$src_attribute = $this->get_src_attribute( $image_element );
		if ( ! $src_attribute ) {
			return;
		}

		$src_image_url = ! empty( $src_attribute->get_single_image_url() )
			? $src_attribute->get_single_image_url()->get_absolute_url()
			: $src_attribute->get_value();

		list( $width, $height ) = $this->url_utils->get_image_dimensions( $src_image_url );

		if (
			empty( $width ) ||
			empty( $height ) ||
			$width < Transformation_Controller::MIN_TRANSFORMABLE_IMAGE_DIMENSION ||
			$height < Transformation_Controller::MIN_TRANSFORMABLE_IMAGE_DIMENSION
		) {
			return;
		}

		$this->assign_image_dimensions( $image_element, $width, $height );
	}

	private function assign_image_dimensions( Element $image_element, $width, $height ) {
		$render_by_width = apply_filters( 'wp_smush_image_dimensions_use_width_attribute', true, $image_element );
		if ( $render_by_width ) {
			$image_element->remove_attribute( $image_element->get_attribute( 'height' ) );
			$image_element->add_or_update_attribute( new Element_Attribute( 'width', $width ) );
		} else {
			$image_element->remove_attribute( $image_element->get_attribute( 'width' ) );
			$image_element->add_or_update_attribute( new Element_Attribute( 'height', $height ) );
		}

		// Class attribute.
		$image_element->append_attribute_value( 'class', self::IMAGE_DIMENSIONS_CLASS );

		// Custom style attribute.
		$original_style = $image_element->get_attribute_value( 'style' );
		$new_style      = "--smush-image-width: {$width}px; --smush-image-aspect-ratio: $width/$height;$original_style";

		$image_element->add_or_update_attribute( new Element_Attribute( 'style', $new_style ) );
	}

	/**
	 * TODO: Check if dimensions need updating when only width or height is missing.
	 */
	private function can_specify_dimensions( Element $element ) {
		if (
			! $element->is_image_element() ||
			empty( $this->get_src_attribute( $element ) ) ||
			$this->element_has_excluded_attribute_values( $element )
		) {
			return false;
		}

		$has_width  = $element->has_attribute( 'width' );
		$has_height = $element->has_attribute( 'height' );

		$can_specify_dimensions = ! $has_width && ! $has_height;

		return apply_filters( 'wp_smush_can_specify_dimensions', $can_specify_dimensions, $element );
	}

	private function element_has_excluded_attribute_values( Element $element ) {
		$excluded_attributes = $this->get_excluded_attributes();
		if ( empty( $excluded_attributes ) ) {
			return false;
		}

		return $this->markup_has_excluded_attribute_values( $element->get_markup(), $excluded_attributes );
	}

	private function get_excluded_attributes() {
		if ( ! $this->excluded_attributes ) {
			$this->excluded_attributes = $this->prepare_excluded_attributes();
		}
		return $this->excluded_attributes;
	}

	private function prepare_excluded_attributes() {
		$excluded_attributes = array(
			'data-no-image-dimensions',
			'data-lazy-original', // Photon (Jetpack).
		);

		return apply_filters( 'wp_smush_image_dimensions_excluded_attributes', $excluded_attributes );
	}

	private function markup_has_excluded_attribute_values( $str, $excluded_values ) {
		if ( empty( $excluded_values ) ) {
			return false;
		}

		$excluded_values = (array) $excluded_values;

		foreach ( $excluded_values as $excluded_value ) {
			if ( empty( $excluded_value ) || ! is_string( $excluded_value ) ) {
				continue;
			}

			if ( strpos( $str, $excluded_value ) !== false ) {
				return true;
			}
		}

		return false;
	}

	private function get_src_attribute( $element ) {
		$source_attribute_names = apply_filters( 'wp_smush_image_dimensions_source_attribute_names', array( 'src' ), $element );
		if ( empty( $source_attribute_names ) ) {
			$source_attribute_names = array( 'src' );
		}
		$source_attribute_names = (array) $source_attribute_names;

		foreach ( $source_attribute_names as $source_attribute_name ) {
			$source_attribute = $element->get_attribute( $source_attribute_name );
			if ( $source_attribute ) {
				return $source_attribute;
			}
		}

		return $element->get_attribute( 'src' );
	}
}
