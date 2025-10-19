<?php

namespace Smush\Core\LCP;

use DOMDocument;
use DOMXPath;
use Smush\Core\Parser\Element;
use Smush\Core\Parser\Parser;

class LCP_Locator {
	/**
	 * @var LCP_Data
	 */
	private $lcp_data;

	private $markup;

	private $lcp_position;
	/**
	 * @var Parser
	 */
	private $parser;
	/**
	 * @var DOMDocument
	 */
	private $dom;
	/**
	 * @var string
	 */
	private $base_url;
	private $default_position = '';

	/**
	 * @param $lcp_data LCP_Data
	 * @param $markup
	 * @param $base_url
	 */
	public function __construct( $lcp_data, $markup, $base_url ) {
		$this->lcp_data = $lcp_data;
		$this->markup   = $markup;
		$this->parser   = new Parser();
		$this->base_url = $base_url;
	}

	public function get_lcp_position() {
		if ( is_null( $this->lcp_position ) ) {
			$this->lcp_position = $this->calculate_lcp_position();
		}

		return $this->lcp_position;
	}

	private function calculate_lcp_position() {
		if ( ! $this->lcp_data || ! $this->lcp_data->is_lcp_element_image() ) {
			return $this->default_position;
		}

		$using_url = $this->locate_lcp_element_using_url();
		if ( ! empty( $using_url ) ) {
			return $using_url;
		}

		return $this->locate_lcp_element_using_xpath( $this->markup );
	}

	private function locate_lcp_element_using_url() {
		$element = $this->get_lcp_element_by_url();
		if ( ! $element ) {
			return $this->default_position;
		}

		return $element->get_position();
	}

	/**
	 * @param $elements Element[]
	 *
	 * @return mixed|null
	 */
	private function disambiguate_lcp_element( $elements ) {
		$matches = array();
		foreach ( $elements as $element ) {
			$id_attribute = $element->get_attribute( 'id' );
			$lcp_id       = $this->lcp_data->get_selector_id();
			$id_matched   = $id_attribute && $lcp_id && $id_attribute->get_value() === $lcp_id;

			$class_attribute = $element->get_attribute( 'class' );
			$lcp_class       = $this->lcp_data->get_selector_class();
			$class_matched   = $class_attribute && $lcp_class && $class_attribute->get_value() === $lcp_class;

			if ( $id_matched || $class_matched ) {
				$matches[] = $element;
			}
		}

		return count( $matches ) === 1 // More than one matches means we were not able to disambiguate
			? $matches[0]
			: null;
	}

	private function get_lcp_element_by_url() {
		$element   = null;
		$image_url = $this->lcp_data->get_image_url();
		if ( ! empty( $image_url ) ) {
			$elements = $this->parser->get_elements_with_image_url( $this->markup, $image_url, $this->base_url );
			if ( count( $elements ) > 1 ) {
				$element = $this->disambiguate_lcp_element( $elements );
			} else if ( count( $elements ) === 1 ) {
				$element = $elements[0];
			}
		}

		return $element;
	}

	private function locate_lcp_element_using_xpath( $markup ) {
		$lcp_path = $this->lcp_data->get_selector_xpath();
		$parts    = array_filter( explode( '/', $lcp_path ) );
		if ( empty( $parts ) ) {
			return $this->default_position;
		}

		return $this->walk_xpath_to_locate_lcp_element( $markup, $parts );
	}

	private function walk_xpath_to_locate_lcp_element( $markup, $path_parts, $position = 0 ) {
		$part = array_shift( $path_parts );
		list( $tag, $index ) = $this->get_tag_and_index( $part );

		$html_element_inner_markup   = null;
		$html_element_inner_position = null;
		if ( in_array( $tag, array( 'img', 'source' ) ) ) {
			list( $html_element_markup, $html_element_position ) = $this->parser->get_self_closing_element_and_position( $tag, $markup, $index );
		} else {
			list(
				$html_element_markup,
				$html_element_position,
				$html_element_inner_markup,
				$html_element_inner_position ) = $this->parser->get_top_level_element_and_position( $tag, $markup, $index );
		}

		if ( is_null( $html_element_markup ) || is_null( $html_element_position ) ) {
			return false;
		}

		if ( empty( $path_parts ) ) {
			return $position + $html_element_position;
		} else {
			if ( $html_element_inner_markup && $html_element_inner_position ) {
				$html_element_markup   = $html_element_inner_markup;
				$html_element_position = $html_element_inner_position;
			}

			return $this->walk_xpath_to_locate_lcp_element( $html_element_markup, $path_parts, $position + $html_element_position );
		}
	}

	/**
	 * @param $part
	 *
	 * @return array
	 */
	private function get_tag_and_index( $part ) {
		$index_start = strpos( $part, '[' );
		$index_end   = strpos( $part, ']' );
		$index       = 0;
		$tag         = $part;
		if ( $index_start !== false ) {
			$index = (int) substr( $part, $index_start + 1, $index_end - $index_start - 1 );
			$tag   = str_replace( "[$index]", '', $part );

			if ( $index ) {
				// Index in xpath starts at 1, so we have to adjust
				$index --;
			}
		}
		return array( $tag, $index );
	}
}
