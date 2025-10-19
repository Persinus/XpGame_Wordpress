<?php

namespace Smush\Core\LCP;

use Smush\Core\Array_Utils;
use Smush\Core\Parser\Composite_Element;
use Smush\Core\Parser\Element;
use Smush\Core\Parser\Page;
use Smush\Core\Url_Utils;

class LCP_Preload_Tag {
	/**
	 * @var Page
	 */
	private $page;
	/**
	 * @var Array_Utils
	 */
	private $array_utils;
	/**
	 * @var LCP_Data
	 */
	private $lcp_data;
	/**
	 * @var Url_Utils
	 */
	private $url_utils;
	/**
	 * @var array
	 */
	private $allowed_url_hostnames;

	public function __construct( Page $page, LCP_Data $lcp_data ) {
		$this->page        = $page;
		$this->array_utils = new Array_Utils();
		$this->lcp_data    = $lcp_data;
		$this->url_utils   = new Url_Utils();
	}

	private function start_tag() {
		return '<link rel="preload" data-smush-preload as="image" ';
	}

	private function end_tag() {
		return ' fetchpriority="high" />';
	}

	public function make_preload_tag() {
		$tag             = '';
		$background_urls = $this->get_validated_background_urls();
		if ( ! empty( $background_urls ) ) {
			if ( count( $background_urls ) === 1 ) {
				$single_background_url  = $background_urls[0];
				$escaped_background_url = $this->url_utils->is_relative( $single_background_url )
					? esc_attr( $single_background_url )
					: esc_url( $single_background_url );

				$tag = $this->start_tag() . 'href="' . $escaped_background_url . '"' . $this->end_tag();
			} else {
				$tag = $this->start_tag() . 'imagesrcset="' . esc_attr( join( ', ', $background_urls ) ) . '"' . $this->end_tag();
			}
		} else {
			$parsed_page = $this->page;
			$element     = $parsed_page->get_lcp_element();
			if ( is_a( $element, Composite_Element::class ) ) {
				if ( $element->get_tag() === 'picture' ) {
					$tag = $this->make_preload_tag_for_composite_element( $element );
				}
			} else if ( is_a( $element, Element::class ) ) {
				if ( $element->is_image_element() ) {
					$tag = $this->make_preload_tag_for_img_element( $element );
				}
			}
		}

		return $tag;
	}

	private function analyze_composite_element( Composite_Element $composite_element ) {
		$data                  = array(
			'src_url' => '',
			'sources' => array(),
		);
		$medias                = array();
		$multi_format          = false;
		$prev_type             = null;
		$prev_max_width        = false;
		$prev_min_width        = false;
		$ascending_max_widths  = false;
		$descending_min_widths = false;
		$srcset_count          = 0;

		foreach ( $composite_element->get_elements() as $element ) {
			if ( $element->get_tag() === 'img' ) {
				$data['src_url'] = $this->get_attribute_single_url( $element, 'src' );

				continue;
			}

			$source = array();
			$media  = $this->get_attribute_value( $element, 'media' );
			if ( ! empty( $media ) ) {
				$medias[] = $media;
			}
			$source['media'] = $media;

			$source['type'] = $this->get_attribute_value( $element, 'type' );

			$min_width           = preg_match( '/\(min-width\s*?:\s*?(\d+(\.\d+)?)\s*?px\)/', $media, $matches ) ? floatval( $matches[1] ) : 0;
			$source['min_width'] = $min_width;

			$max_width           = preg_match( '/\(max-width\s*?:\s*?(\d+(\.\d+)?)\s*?px\)/', $media, $matches ) ? floatval( $matches[1] ) : 0;
			$source['max_width'] = $max_width;

			$ascending_max_widths  = $prev_max_width === false || ( $prev_max_width !== null && ! empty( $max_width ) && $max_width > $prev_max_width && empty( $min_width ) );
			$descending_min_widths = $prev_min_width === false || ( $prev_min_width !== null && ! empty( $min_width ) && $min_width < $prev_min_width && empty( $max_width ) );

			$source['srcset'] = $this->get_attribute_value( $element, 'srcset' );
			if ( ! empty( $source['srcset'] ) ) {
				$srcset_count ++;
			}

			$source['srcset_url_count'] = count( explode( ',', $source['srcset'] ) );

			$source['sizes'] = $this->get_attribute_value( $element, 'sizes' );

			$data['sources'][] = $source;

			if ( ! empty( $source['type'] ) && $prev_type !== $source['type'] && null !== $prev_type ) {
				$multi_format = true;
				break;
			}

			$prev_type      = $source['type'];
			$prev_min_width = $min_width;
			$prev_max_width = $max_width;
		}

		if ( $multi_format ) {
			$data['type'] = 'multi_format';
		} else if ( empty( $medias ) && $srcset_count === 1 ) {
			$data['type'] = 'single_srcset';
		} else if ( $ascending_max_widths ) {
			$data['type'] = 'ascending_max_widths';
		} else if ( $descending_min_widths ) {
			$data['type'] = 'descending_min_widths';
		} else {
			$data['type'] = 'standard';
		}

		return $data;
	}

	private function make_preload_tag_for_composite_element( Composite_Element $composite_element ) {
		$composite_data = $this->analyze_composite_element( $composite_element );
		switch ( $composite_data['type'] ) {
			case 'multi_format':
				$tag = $this->make_preload_tag_for_multi_format( $composite_data );
				break;
			case 'single_srcset':
				$tag = $this->make_preload_tag_for_single_srcset( $composite_data );
				break;
			case 'ascending_max_widths':
				$tag = $this->make_preload_tag_with_ascending_max_widths( $composite_data );
				break;
			case 'descending_min_widths':
				$tag = $this->make_preload_tag_with_descending_min_widths( $composite_data );
				break;
			default:
				$tag = $this->make_standard_preload_tag_for_composite_element( $composite_data );
		}

		return $tag;
	}

	private function make_preload_tag_with_ascending_max_widths( $composite_data ) {
		$tag            = '';
		$prev_max_width = null;

		// Iterate over the sources in the LCP object.
		foreach ( $composite_data['sources'] as $source ) {
			$media = $source['media'];
			// If a previous max-width is found, update the media query.
			if ( null !== $prev_max_width && empty( $source['min_width'] ) ) {
				$media = '(min-width: ' . ( $prev_max_width + 0.1 ) . 'px) and ' . $media;
			}

			// Add the media attribute to the media string.
			$media_attr = ! empty( $media ) ? ' media="' . $media . '"' : '';

			$srcset = $source['srcset'];

			$sizes = ! empty( $source['sizes'] ) ? ' imagesizes="' . esc_attr( $source['sizes'] ) . '"' : '';

			// Determine whether to use 'href' or 'imagesrcset' based on the srcset attribute.
			$link_attribute = $source['srcset_url_count'] > 1 ? 'imagesrcset' : 'href';

			// Append the source and media query to the tag string.
			if ( ! empty( $srcset ) ) {
				$tag .= $this->start_tag() . $link_attribute . '="' . esc_attr( $srcset ) . '"' . ( $media_attr ) . $sizes . $this->end_tag();
			}

			// If a max-width is found in the source's media attribute, update the previous max-width.
			if ( ! empty( $source['max_width'] ) ) {
				$prev_max_width = $source['max_width'];
			}
		}

		// If a previous max-width is found, update the media query and add the LCP source to the sources array and the tag string.
		if ( null !== $prev_max_width && ! empty( $composite_data['src_url'] ) ) {
			$media = ' media="(min-width: ' . ( $prev_max_width + 0.1 ) . 'px)"';
			$tag   .= $this->start_tag() . 'href="' . esc_url( $composite_data['src_url'] ) . '"' . $media . $this->end_tag();
		}

		return $tag;
	}

	private function make_preload_tag_with_descending_min_widths( $composite_data ) {
		$tag            = '';
		$prev_min_width = null;

		// Iterate over the sources in the LCP object.
		foreach ( $composite_data['sources'] as $source ) {
			$media = $source['media'];
			// If a previous max-width is found, update the media query.
			if ( null !== $prev_min_width && empty( $source['max_width'] ) ) {
				$media = $media . ' and (max-width: ' . ( $prev_min_width - 0.1 ) . 'px)';
			}

			// Add the media attribute to the media string.
			$media_attr = ! empty( $media ) ? ' media="' . $media . '"' : '';

			$srcset = $source['srcset'];

			$sizes = ! empty( $source['sizes'] ) ? ' imagesizes="' . esc_attr( $source['sizes'] ) . '"' : '';

			// Determine whether to use 'href' or 'imagesrcset' based on the srcset attribute.
			$link_attribute = $source['srcset_url_count'] > 1 ? 'imagesrcset' : 'href';

			// Append the source and media query to the tag string.
			if ( ! empty( $srcset ) ) {
				$tag .= $this->start_tag() . $link_attribute . '="' . esc_attr( $srcset ) . '"' . ( $media_attr ) . $sizes . $this->end_tag();
			}

			// If a max-width is found in the source's media attribute, update the previous max-width.
			if ( ! empty( $source['min_width'] ) ) {
				$prev_min_width = $source['min_width'];
			}
		}

		// If a previous max-width is found, update the media query and add the LCP source to the sources array and the tag string.
		if ( null !== $prev_min_width && ! empty( $composite_data['src_url'] ) ) {
			$media = ' media="(max-width: ' . ( $prev_min_width - 0.1 ) . 'px)"';
			$tag   .= $this->start_tag() . 'href="' . esc_url( $composite_data['src_url'] ) . '"' . $media . $this->end_tag();
		}

		return $tag;
	}

	private function make_standard_preload_tag_for_composite_element( $composite_data ) {
		$tag = '';

		foreach ( $composite_data['sources'] as $source ) {
			$parts          = array();
			$link_attribute = $source['srcset_url_count'] > 1 ? 'imagesrcset' : 'href';
			if ( ! empty( $source['srcset'] ) ) {
				$parts[] = $link_attribute . '="' . esc_attr( $source['srcset'] ) . '"';
			}

			if ( ! empty( $source['sizes'] ) ) {
				$parts[] = 'imagesizes="' . esc_attr( $source['sizes'] ) . '"';
			}

			if ( ! empty( $source['media'] ) ) {
				$parts[] = 'media="' . $source['media'] . '"';
			}

			if ( ! empty( $parts ) ) {
				$tag .= $this->start_tag() . implode( ' ', $parts ) . $this->end_tag();
			}
		}

		return $tag;
	}

	/**
	 * @param Element $element
	 * @param string $attribute
	 *
	 * @return mixed|string
	 */
	private function get_attribute_value( Element $element, string $attribute ) {
		return $element->has_attribute( $attribute )
			? $element->get_attribute( $attribute )->get_value()
			: '';
	}

	/**
	 * @param Element $element
	 * @param string $attribute_name
	 *
	 * @return string
	 */
	private function get_attribute_single_url( Element $element, string $attribute_name ): string {
		$url = $element->has_attribute( $attribute_name )
			? $element->get_attribute( $attribute_name )->get_single_image_url()
			: null;

		return $url
			? $url->get_absolute_url()
			: '';
	}

	private function make_preload_tag_for_multi_format( $composite_data ) {
		$tag = '';
		foreach ( $composite_data['sources'] as $source ) {
			if ( ! empty( $source['type'] ) ) {
				$link_attribute = $source['srcset_url_count'] > 1 ? 'imagesrcset' : 'href';
				$tag            = $this->start_tag() . $link_attribute . '="' . $source['srcset'] . '"' . $this->end_tag();
				break;
			}
		}

		return $tag;
	}

	private function make_preload_tag_for_single_srcset( $composite_data ) {
		$parts   = array();
		$src_url = $this->array_utils->get_array_value( $composite_data, 'src_url' );
		if ( ! $src_url ) {
			return '';
		}

		$parts[] = 'href="' . $src_url . '"';
		if ( ! empty( $composite_data['sources'][0]['srcset'] ) ) {
			$parts[] = 'imagesrcset="' . $composite_data['sources'][0]['srcset'] . '"';
		}
		if ( ! empty( $composite_data['sources'][0]['sizes'] ) ) {
			$parts[] = 'imagesizes="' . $composite_data['sources'][0]['sizes'] . '"';
		}
		return $this->start_tag() . implode( ' ', $parts ) . $this->end_tag();
	}

	/**
	 * @param $element Element
	 *
	 * @return string
	 */
	private function make_preload_tag_for_img_element( $element ) {
		$src_attribute = $element->get_attribute( 'src' );
		if ( $src_attribute && ! empty( $src_attribute->get_single_image_url() ) ) {
			$parts[] = 'href="' . esc_url( $src_attribute->get_single_image_url()->get_absolute_url() ) . '"';
		}

		$srcset_attribute = $element->get_attribute( 'srcset' );
		if ( $srcset_attribute && ! empty( $srcset_attribute->get_image_urls() ) ) {
			$parts[] = 'imagesrcset="' . esc_attr( $srcset_attribute->get_value() ) . '"';
		}

		$sizes_attribute = $element->get_attribute( 'sizes' );
		if ( $sizes_attribute && ! empty( $sizes_attribute->get_value() ) ) {
			$parts[] = 'imagesizes="' . esc_attr( $sizes_attribute->get_value() ) . '"';
		}

		if ( empty( $parts ) ) {
			return '';
		}

		return $this->start_tag() . implode( ' ', $parts ) . $this->end_tag();
	}

	public function get_validated_background_urls() {
		$all_background_urls = $this->lcp_data->get_background_urls();
		$validated           = array();
		foreach ( $all_background_urls as $background_url ) {
			if ( $this->is_valid_url( $background_url ) ) {
				$validated[] = $background_url;
			}
		}

		return $validated;
	}

	private function is_valid_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		if ( strpos( $this->page->get_page_markup(), $url ) ) {
			return true;
		}

		$host = parse_url( $url, PHP_URL_HOST );
		if ( empty( $host ) ) {
			return false;
		}

		$allowed_hostnames = $this->get_allowed_url_hostnames();
		if ( ! in_array( $host, $allowed_hostnames ) ) {
			return false;
		}

		return true;
	}

	private function get_allowed_url_hostnames() {
		if ( is_null( $this->allowed_url_hostnames ) ) {
			$this->allowed_url_hostnames = $this->prepare_allowed_url_hostnames();
		}

		return $this->allowed_url_hostnames;
	}

	private function prepare_allowed_url_hostnames() {
		$site_url_host     = parse_url( site_url(), PHP_URL_HOST );
		$allowed_hostnames = array( $site_url_host );

		return apply_filters( 'wp_smush_lcp_allowed_url_hostnames', $allowed_hostnames );
	}
}
