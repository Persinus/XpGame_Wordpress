<?php

namespace Smush\Core\Parser;


use Smush\Core\LCP\LCP_Data;
use Smush\Core\LCP\LCP_Locator;

class Page_Parser {
	/**
	 * @var string
	 */
	private $page_url;
	/**
	 * @var string
	 */
	private $page_markup;
	/**
	 * @var Parser
	 */
	private $parser;
	/**
	 * @var LCP_Data
	 */
	private $lcp_data;

	public function __construct( $page_url, $page_markup, $lcp_data = null ) {
		$this->page_url    = $page_url;
		$this->page_markup = $page_markup;
		$this->parser      = new Parser();
		$this->lcp_data    = $lcp_data;
	}

	/**
	 * TODO: make sure this method is called as few times as possible
	 *
	 * @return Page
	 */
	public function parse_page() {
		$page_markup  = $this->page_markup;
		$base_tag_url = $this->parser->get_base_url( $page_markup );
		$base_url     = $base_tag_url ?: $this->page_url;
		$styles       = $this->parser->get_inline_styles( $page_markup, $base_url );

		if ( empty( $this->lcp_data ) ) {
			$lcp_position = - 1;
		} else {
			$lcp_locator  = new LCP_Locator( $this->lcp_data, $page_markup, $this->page_url );
			$lcp_position = $lcp_locator->get_lcp_position();
		}

		$sub_element_positions = array();
		$script_elements       = $this->parser->get_composite_elements( $page_markup, $base_url, array( 'script', 'noscript' ), $lcp_position );
		$sub_element_positions = $this->get_composite_sub_element_positions( $script_elements, $sub_element_positions );

		$picture_elements      = $this->parser->get_composite_elements( $page_markup, $base_url, array( 'picture' ), $lcp_position );
		$sub_element_positions = $this->get_composite_sub_element_positions( $picture_elements, $sub_element_positions );

		$elements        = $this->parser->get_elements_with_image_attributes( $page_markup, $base_url, $lcp_position );
		$elements        = $this->remove_composite_sub_elements( $elements, $sub_element_positions );
		$iframe_elements = $this->parser->get_iframe_elements( $page_markup, $base_url );

		return new Page(
			$this->page_url,
			$this->page_markup,
			$styles,
			$picture_elements,
			$elements,
			$iframe_elements
		);
	}

	/**
	 * @param $markup
	 * @param $composite_elements Composite_Element[]
	 *
	 * @return string
	 */
	private function replace_composites_with_placeholders( $markup, $composite_elements ) {
		$placeholder_replacement = new Placeholder_Replacement();
		if ( empty( $composite_elements ) ) {
			return $markup;
		}

		$html_elements = array_map( function ( $composite_element ) {
			return $composite_element->get_markup();
		}, $composite_elements );
		return $placeholder_replacement->add_placeholders( $markup, $html_elements );
	}

	/**
	 * @param array $composite_elements Composite_Element[]
	 *
	 * @return int[]
	 */
	private function get_composite_sub_element_positions( array $composite_elements, $sub_element_positions ): array {
		foreach ( $composite_elements as $composite_element ) {
			foreach ( $composite_element->get_elements() as $sub_element ) {
				$sub_element_position = $sub_element->get_position();
				if ( ! in_array( $sub_element_position, $sub_element_positions ) ) {
					$sub_element_positions[] = $sub_element_position;
				}
			}
		}
		return $sub_element_positions;
	}

	/**
	 * @param Element[] $elements
	 * @param int[] $composite_sub_element_positions
	 *
	 * @return array
	 */
	private function remove_composite_sub_elements( $elements, $composite_sub_element_positions ): array {
		if ( empty( $composite_sub_element_positions ) || ! is_array( $composite_sub_element_positions ) || ! is_array( $elements ) ) {
			return $elements;
		}

		$filtered = array_filter( $elements, function ( $element ) use ( $composite_sub_element_positions ) {
			return ! in_array( $element->get_position(), $composite_sub_element_positions );
		} );
		return array_values( $filtered );
	}
}
