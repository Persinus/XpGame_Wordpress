<?php

namespace Smush\Core\Parser;

class Page {
	/**
	 * @var string
	 */
	private $page_url;
	/**
	 * @var string
	 */
	private $page_markup;
	/**
	 * @var Style[]
	 */
	private $styles;
	/**
	 * @var Element[]
	 */
	private $elements;
	/**
	 * @var Parser
	 */
	private $parser;
	/**
	 * @var Element[]
	 */
	private $iframe_elements;
	/**
	 * @var Composite_Element[]
	 */
	private $composite_elements;

	/**
	 * @var Composite_Element|Element|null
	 */
	private $lcp_element;

	/**
	 * @param $page_url string
	 * @param $page_markup string
	 * @param $styles Style[]
	 * @param $elements Element[]
	 */
	public function __construct( $page_url, $page_markup, $styles, $composite_elements, $elements, $iframe_elements ) {
		$this->page_url           = $page_url;
		$this->page_markup        = $page_markup;
		$this->styles             = $styles;
		$this->composite_elements = $composite_elements;
		$this->elements           = $elements;
		$this->iframe_elements    = $iframe_elements;
		$this->parser             = new Parser();
	}

	/**
	 * @return Style[]
	 */
	public function get_styles() {
		return $this->styles;
	}

	/**
	 * @return Composite_Element[]
	 */
	public function get_composite_elements() {
		return $this->composite_elements;
	}

	/**
	 * @return Element[]
	 */
	public function get_elements() {
		return $this->elements;
	}

	public function has_updates() {
		foreach ( $this->styles as $style ) {
			if ( $style->has_updates() ) {
				return true;
			}
		}

		foreach ( $this->composite_elements as $composite_element ) {
			if ( $composite_element->has_updates() ) {
				return true;
			}
		}

		foreach ( $this->elements as $element ) {
			if ( $element->has_updates() ) {
				return true;
			}
		}

		foreach ( $this->iframe_elements as $iframe_element ) {
			if ( $iframe_element->has_updates() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function get_page_markup() {
		return $this->page_markup;
	}

	/**
	 * ASSUMPTIONS:
	 * - All elements handled by this method have correct positions, not the default -1
	 * - The same element is not included in the elements array as well as a composite element
	 *
	 * @return string
	 */
	public function get_updated_markup() {
		$updated     = $this->page_markup;
		$replaceable = $this->get_sorted_items();

		foreach ( $replaceable as $replaceable_item ) {
			if ( $replaceable_item->has_updates() ) {
				$before  = substr( $updated, 0, $replaceable_item->get_position() );
				$after   = substr( $updated, $replaceable_item->get_position() + strlen( $replaceable_item->get_original() ) );
				$updated = $before . $replaceable_item->get_updated() . $after;
			}
		}

		return $updated;
	}

	public function get_iframe_elements() {
		return $this->iframe_elements;
	}

	public function get_lcp_element() {
		if ( is_null( $this->lcp_element ) ) {
			$this->lcp_element = $this->find_lcp_element();
		}

		return $this->lcp_element;
	}

	/**
	 * @return Composite_Element|Element|null
	 */
	private function find_lcp_element() {
		foreach ( $this->get_composite_elements() as $composite_element ) {
			if ( $composite_element->has_lcp() ) {
				return $composite_element;
			}
		}

		foreach ( $this->get_elements() as $element ) {
			if ( $element->is_lcp() ) {
				return $element;
			}
		}

		return null;
	}

	/**
	 * @return Replaceable[]
	 */
	private function get_sorted_items(): array {
		/**
		 * @var Replaceable[] $replaceable
		 */
		$replaceable = array_merge(
			$this->styles,
			$this->composite_elements,
			$this->elements,
			$this->iframe_elements
		);

		// Replace elements starting from the end of the markup so that positions don't change

		usort( $replaceable, function ( $a, $b ) {
			return $b->get_position() <=> $a->get_position();
		} );
		return $replaceable;
	}
}
