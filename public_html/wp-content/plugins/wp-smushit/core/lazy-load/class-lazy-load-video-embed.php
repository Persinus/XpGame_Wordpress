<?php

namespace Smush\Core\Lazy_Load;

use Smush\Core\Lazy_Load\Video_Embed\Video_Embed;
use Smush\Core\Lazy_Load\Video_Embed\Video_Embed_Helper;
use Smush\Core\Lazy_Load\Video_Embed\Video_Thumbnail;
use Smush\Core\Next_Gen\Next_Gen_Manager;
use Smush\Core\Parser\Element;
use Smush\Core\Parser\Element_Attribute;
use Smush\Core\Settings;
use Smush\Core\Url_Utils;

defined( 'WPINC' ) || exit;

class Lazy_Load_Video_Embed {
	const CLASS_SMUSH_VIDEO = 'smush-lazyload-video';

	/**
	 * @var Video_Embed
	 */
	private $embed_provider;

	/**
	 * @var string
	 */
	private $embed_url;

	/**
	 * @var Element
	 */
	private $iframe_element;

	/**
	 * @var Video_Embed_Helper
	 */
	protected $helper;

	private Url_Utils $url_utils;

	public function __construct( $embed_url, $iframe_element ) {
		$this->embed_url      = $embed_url;
		$this->iframe_element = $iframe_element;
		$this->helper         = Video_Embed_Helper::get_instance();
		$this->url_utils      = new Url_Utils();
	}

	private function get_embed_provider() {
		if ( ! $this->embed_provider ) {
			$this->embed_provider = $this->prepare_embed_provider();
		}

		return $this->embed_provider;
	}

	private function prepare_embed_provider() {
		return $this->helper->create_embed_object( $this->embed_url );
	}

	public function can_lazy_load() {
		$embed_provider = $this->get_embed_provider();
		$can_lazy_load  = ! empty( $embed_provider );

		return apply_filters( 'wp_smush_should_lazy_load_video', $can_lazy_load, $embed_provider, $this->iframe_element );
	}

	private function is_auto_play_enabled() {
		$query_vars = $this->url_utils->get_query_vars( $this->embed_url );

		return ! empty( $query_vars['autoplay'] );
	}

	public function transform() {
		if ( ! $this->get_embed_provider() ) {
			return;
		}

		$wrapper_markup_parts = $this->generate_video_wrapper_parts();
		if ( empty( $wrapper_markup_parts ) ) {
			return;
		}

		list( $wrapper_before, $wrapper_after ) = $wrapper_markup_parts;

		$this->iframe_element->set_wrapper_markup( $wrapper_before, $wrapper_after );
		$this->convert_src_to_data_src();
		$this->iframe_element->add_attribute( new Element_Attribute( 'src', Lazy_Load_Transform::TEMP_SRC ) );
	}

	private function generate_video_wrapper_parts(): ?array {
		// Try the cover attribute.
		$wrapper_markup = $this->generate_video_wrapper_parts_from_cover_attribute();
		if ( ! empty( $wrapper_markup ) ) {
			return $wrapper_markup;
		}

		// Use cached thumbnail data.
		$wrapper_markup = $this->generate_video_wrapper_parts_from_cached_video_thumbnail();
		if ( ! empty( $wrapper_markup ) ) {
			return $wrapper_markup;
		}

		// Generate a custom redirect URL.
		$wrapper_markup = $this->generate_video_wrapper_parts_with_custom_url();
		if ( ! empty( $wrapper_markup ) ) {
			return $wrapper_markup;
		}

		return null;
	}

	private function convert_src_to_data_src() {
		$src_attribute = $this->iframe_element->get_attribute( 'src' );
		if ( $src_attribute ) {
			$original_value = $src_attribute->get_value();
			$data_attribute = new Element_Attribute( 'data-src', $original_value );
			$this->iframe_element->replace_attribute( 'src', $data_attribute );
		}
	}

	private function generate_video_wrapper_parts_with_custom_url(): ?array {
		list( $video_width, $video_height ) = $this->get_video_dimensions( $this->iframe_element );
		if ( ! $video_width && ! $video_height ) {
			return null;
		}

		$video_thumbnail_url = $this->helper->make_video_thumbnail_url( $this->embed_url, $video_width, $video_height );
		$aspect_ratio        = $this->get_aspect_ratio( $video_width, $video_height );
		return $this->generate_video_wrapper_markup_parts( $aspect_ratio, $video_thumbnail_url );
	}

	private function generate_video_wrapper_parts_from_cached_video_thumbnail(): ?array {
		list( $video_width, $video_height ) = $this->get_video_dimensions( $this->iframe_element );
		if ( ! $video_width && ! $video_height ) {
			return null;
		}

		$video_embed            = $this->get_embed_provider();
		$cached_video_thumbnail = $video_embed->get_cached_video_thumbnail( $video_width, $video_height );
		if ( ! $cached_video_thumbnail ) {
			return null;
		}

		$video_thumbnail_url                 = $cached_video_thumbnail->get_url();
		$aspect_ratio                        = $this->get_aspect_ratio( $video_width, $video_height );
		$fallback_background_image_attribute = $this->get_next_gen_fallback_background_image_attribute( $cached_video_thumbnail );

		return $this->generate_video_wrapper_markup_parts( $aspect_ratio, $video_thumbnail_url, $fallback_background_image_attribute );
	}

	private function generate_video_wrapper_parts_from_cover_attribute(): ?array {
		$embed_provider = $this->get_embed_provider();
		list( $video_width, $video_height ) = $this->get_video_dimensions( $this->iframe_element );
		$video_thumbnail = $this->get_video_thumbnail_from_attribute( $this->iframe_element, $video_width, $video_height );

		if ( empty( $video_thumbnail ) || empty( $embed_provider ) ) {
			return null;
		}

		// Generate markup components.
		$aspect_ratio                        = $this->get_aspect_ratio( $video_width, $video_height, $video_thumbnail );
		$fallback_background_image_attribute = $this->get_next_gen_fallback_background_image_attribute( $video_thumbnail );

		return $this->generate_video_wrapper_markup_parts(
			$aspect_ratio,
			$video_thumbnail->get_url(),
			$fallback_background_image_attribute
		);
	}

	private function generate_video_wrapper_markup_parts( $aspect_ratio, $video_thumbnail_url, $fallback_background_image_attribute = '' ): ?array {
		$embed_provider             = $this->get_embed_provider();
		$wrapper_classes            = $this->generate_wrapper_classes();
		$background_image_attribute = $this->get_background_image_attribute( $video_thumbnail_url );
		// Build wrapper markup.
		$wrapper_markup_before = sprintf(
			'<div class="%1$s" style="--smush-video-aspect-ratio: %2$s" %3$s %4$s>',
			esc_attr( implode( ' ', $wrapper_classes ) ),
			esc_attr( $aspect_ratio ),
			$background_image_attribute,
			$fallback_background_image_attribute
		);
		$wrapper_markup_before = apply_filters(
			'wp_smush_lazy_load_video_wrapper_markup_before',
			$wrapper_markup_before,
			$embed_provider,
			$this->iframe_element
		);

		$wrapper_markup_after = $this->get_play_button( $this->iframe_element );
		$wrapper_markup_after .= '</div>';
		$wrapper_markup_after = apply_filters(
			'wp_smush_lazy_load_video_wrapper_markup_after',
			$wrapper_markup_after,
			$embed_provider,
			$this->iframe_element
		);

		if ( empty( $wrapper_markup_before ) || empty( $wrapper_markup_after ) ) {
			return null;
		}

		return array( $wrapper_markup_before, $wrapper_markup_after );
	}

	private function get_aspect_ratio( $video_width, $video_height, $video_thumbnail = null ) {
		$aspect_ratio = '16/9';
		if ( $video_width && $video_height ) {
			$aspect_ratio = "{$video_width}/{$video_height}";
		} else if ( $video_thumbnail ) {
			$aspect_ratio = $video_thumbnail->get_aspect_ratio();
		}

		return apply_filters(
			'wp_smush_lazy_load_video_aspect_ratio',
			$aspect_ratio,
			$this->get_embed_provider(),
			$this->iframe_element
		);
	}

	private function generate_wrapper_classes() {
		$embed_provider = $this->get_embed_provider();
		$classes        = array(
			Lazy_Load_Transform::LAZYLOAD_CLASS,
			self::CLASS_SMUSH_VIDEO,
			'smush-lazyload-' . $embed_provider->get_name(),
		);

		if ( $this->is_auto_play_enabled() ) {
			$classes[] = 'smush-lazyload-autoplay';
		}

		return $classes;
	}

	private function get_background_image_attribute( $video_thumbnail_url ) {
		$thumb_url = apply_filters(
			'wp_smush_lazy_load_video_thumbnail_url',
			$video_thumbnail_url,
			$this->get_embed_provider(),
			$this->iframe_element
		);

		return sprintf( 'data-bg-image="url(%s)"', esc_url( $thumb_url ) );
	}

	/**
	 * @param Video_Thumbnail $video_thumbnail
	 *
	 * @return string
	 */
	private function get_next_gen_fallback_background_image_attribute( Video_Thumbnail $video_thumbnail ): string {
		$fallback_thumb_url = $video_thumbnail->get_fallback_url();
		$has_next_gen_url   = $video_thumbnail->has_next_gen_url();

		// Return early if fallback URL or next-gen URL is not available.
		if ( empty( $fallback_thumb_url ) || ! $has_next_gen_url ) {
			return '';
		}

		$next_gen_manager            = Next_Gen_Manager::get_instance();
		$is_next_gen_fallback_active = $next_gen_manager->is_active() && $next_gen_manager->is_fallback_activated();

		if ( ! $is_next_gen_fallback_active ) {
			return '';
		}

		$format_key    = Next_Gen_Manager::get_instance()->get_active_format_key();
		$fallback_data = wp_json_encode(
			array(
				'data-bg-image' => sprintf( 'url(%s)', esc_url( $fallback_thumb_url ) ),
			)
		);

		return sprintf( 'data-smush-%s-fallback=\'%s\'', esc_attr( $format_key ), esc_attr( $fallback_data ) );
	}

	private function get_video_thumbnail_from_attribute( $iframe_element, $video_width = false, $video_height = false ) {
		$poster = $iframe_element->get_attribute_value( 'data-poster' );
		if ( ! $poster ) {
			return null;
		}

		if ( ! empty( $video_width ) && ! empty( $video_height ) ) {
			$width  = $video_width;
			$height = $video_height;
		} else {
			list( $width, $height ) = $this->url_utils->get_image_dimensions( $poster );
		}

		if ( ! $width || ! $height ) {
			return null;
		}

		$extension          = $this->url_utils->get_extension( $poster );
		$is_next_gen_format = 'avif' === $extension || 'webp' === $extension;
		$next_gen_url       = $is_next_gen_format ? $poster : null;
		$fallback_url       = $is_next_gen_format ? null : $poster;

		$video_thumbnail = new Video_Thumbnail();
		$video_thumbnail->from_array(
			array(
				'width'        => $width,
				'height'       => $height,
				'next_gen_url' => $next_gen_url,
				'fallback_url' => $fallback_url,
			)
		);

		return $video_thumbnail;
	}

	private function get_play_button( $iframe_element ) {
		$play_label = $iframe_element->get_attribute_value( 'data-play-label' );
		if ( ! $play_label ) {
			$play_label = esc_html__( 'Play', 'wp-smushit' );
		}

		$player_button = sprintf(
			'<span class="smush-play-btn" role="button" aria-label="%1$s">
				<span tabindex="0" class="smush-play-btn-inner">
					<span>%2$s</span>
				</span>
			</span>',
			__( 'Play video', 'wp-smushit' ),
			$play_label,
		);

		return apply_filters( 'wp_smush_lazy_load_video_player_button', $player_button, $iframe_element );
	}

	private function get_video_dimensions( $iframe_element ) {
		$width  = $iframe_element->get_attribute_value( 'width' );
		$height = $iframe_element->get_attribute_value( 'height' );
		$width  = strpos( $width, '%' ) ? 0 : (int) $width;
		$height = strpos( $height, '%' ) ? 0 : (int) $height;

		if ( empty( $width ) && empty( $height ) ) {
			$width = $this->get_video_max_width();
		}

		$video_dimensions = array(
			$width,
			$height,
		);

		return (array) apply_filters( 'wp_smush_lazy_load_video_dimensions', $video_dimensions, $iframe_element );
	}

	private function get_video_max_width() {
		if ( defined( 'WP_SMUSH_LAZYLOAD_MAX_VIDEO_WIDTH' ) && WP_SMUSH_LAZYLOAD_MAX_VIDEO_WIDTH > 0 ) {
			return WP_SMUSH_LAZYLOAD_MAX_VIDEO_WIDTH;
		}

		return Settings::get_instance()->max_content_width();
	}
}
