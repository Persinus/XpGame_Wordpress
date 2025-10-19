<?php

namespace Smush\Core\Parser;

class Composite_Element implements Replaceable {
	/**
	 * @var string
	 */
	private $markup;
	/**
	 * @var string
	 */
	private $tag;
	/**
	 * @var Element[]
	 */
	private $elements;
	/**
	 * @var int
	 */
	private $position;
	/**
	 * @var bool
	 */
	private $has_lcp;

	public function __construct( $markup, $tag, $elements, $position = - 1, $has_lcp = false ) {
		$this->markup   = $markup;
		$this->tag      = $tag;
		$this->elements = $elements;
		$this->position = $position;
		$this->has_lcp  = $has_lcp;
	}

	/**
	 * @return string
	 */
	public function get_markup(): string {
		return $this->markup;
	}

	/**
	 * @return string
	 */
	public function get_tag(): string {
		return $this->tag;
	}

	/**
	 * @return Element[]
	 */
	public function get_elements(): array {
		return $this->elements;
	}

	public function has_updates() {
		foreach ( $this->elements as $element ) {
			if ( $element->has_updates() ) {
				return true;
			}
		}
		return false;
	}

	public function get_updated() {
		$updated = $this->markup;
		foreach ( $this->elements as $element ) {
			if ( $element->has_updates() ) {
				$updated = str_replace(
					$element->get_markup(),
					$element->get_updated_markup(),
					$updated
				);
			}
		}

		return $updated;
	}

	public function has_lcp() {
		return $this->has_lcp;
	}

	public function set_has_lcp( $is_lcp ) {
		$this->has_lcp = $is_lcp;
	}

	public function get_position() {
		return $this->position;
	}

	public function set_position( $position ) {
		$this->position = $position;
	}

	public function get_original() {
		return $this->get_markup();
	}
}
