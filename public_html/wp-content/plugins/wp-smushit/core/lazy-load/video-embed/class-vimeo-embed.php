<?php

namespace Smush\Core\Lazy_Load\Video_Embed;

use Smush\Core\Helper;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Vimeo_Embed implements Video_Embed {
	const NAME = 'vimeo';
	const VIDEO_ID_REGEX = '#player\.vimeo\.com\/video\/(?<video_id>[\d]+)#i';
	const OEMBED_API_URL = 'https://vimeo.com/api/oembed.json?url=%s';

	/**
	 *
	 * @var string
	 */
	private $embed_url;

	/**
	 * Video id.
	 *
	 * @var string
	 */
	private $video_id;

	/**
	 * Thumbnail sizes.
	 *
	 * @var array
	 */
	private $thumb_sizes;

	private Video_Thumbnail_Cache $video_thumbnail_cache;

	public function __construct( $embed_url ) {
		$this->embed_url             = $embed_url;
		$this->video_thumbnail_cache = Video_Thumbnail_Cache::get_instance();
	}

	public function get_name() {
		return self::NAME;
	}

	public function get_embed_url() {
		return $this->embed_url;
	}

	public function can_lazy_load() {
		return $this->is_valid_embed_url();
	}

	private function is_valid_embed_url() {
		return ! empty( $this->get_video_id() );
	}

	public function get_video_id() {
		if ( ! $this->video_id ) {
			$this->video_id = $this->prepare_video_id();
		}

		return $this->video_id;
	}

	private function prepare_video_id() {
		$video_id_regex = apply_filters( 'wp_smush_lazy_load_vimeo_id_regex', self::VIDEO_ID_REGEX );
		if ( preg_match( $video_id_regex, $this->embed_url, $matches ) ) {
			return $matches['video_id'] ?? '';
		}

		return '';
	}

	public function fetch_video_thumbnail( $video_width, $video_height ): ?Video_Thumbnail {
		$video_id = $this->get_video_id();
		if ( ! $video_id ) {
			return null;
		}

		list( $requested_thumb_width, $requested_thumb_height ) = $this->determine_best_thumbnail_size( $video_width, $video_height );

		$cached = $this->video_thumbnail_cache->get( $video_id, self::NAME, $requested_thumb_width, $requested_thumb_height );
		if ( $cached ) {
			return $cached;
		}

		$thumbnail_info = $this->fetch_thumbnail_info( $requested_thumb_width, $requested_thumb_height );
		if ( empty( $thumbnail_info ) ) {
			return null;
		}

		list(
			$thumb_url,
			$actual_thumb_width,
			$actual_thumb_height ) = $thumbnail_info;
		$video_thumbnail = new Video_Thumbnail();
		$video_thumbnail->from_array(
			array(
				'fallback_url' => $thumb_url,
				'width'        => $actual_thumb_width,
				'height'       => $actual_thumb_height,
			)
		);

		$this->video_thumbnail_cache->add( $video_id, self::NAME, $requested_thumb_width, $requested_thumb_height, $video_thumbnail );

		return $video_thumbnail;
	}

	/**
	 * @param $video_width
	 * @param $video_height
	 *
	 * @return array|int[]|mixed
	 */
	private function determine_best_non_retina_thumbnail_size( $video_width, $video_height ) {
		if ( $video_width > 0 && $video_height > 0 ) {
			return array( $video_width, $video_height );
		}

		$thumb_sizes = $this->get_thumbnail_sizes();
		$thumb_size  = array();
		foreach ( $thumb_sizes as $size ) {
			list( $thumb_width, $thumb_height ) = $size;

			if ( $this->is_smaller_than_video( $thumb_width, $thumb_height, $video_width, $video_height ) ) {
				continue;
			}

			$thumb_size = $size;
			break;
		}

		if ( empty( $thumb_size ) ) {
			$thumb_size = array( 640, 360 );// Standard Definition.
		}

		return $thumb_size;
	}

	private function determine_best_thumbnail_size( $video_width, $video_height ) {
		list( $non_retina_width, $non_retina_height ) = $this->determine_best_non_retina_thumbnail_size( $video_width, $video_height );

		$retina_ratio  = $this->get_retina_ratio( $non_retina_width, $non_retina_height );
		$retina_width  = $non_retina_width * $retina_ratio;
		$retina_height = $non_retina_height * $retina_ratio;

		return array( (int) ceil( $retina_width ), (int) ceil( $retina_height ) );
	}

	private function is_smaller_than_video( $thumb_width, $thumb_height, $video_width, $video_height ): bool {
		return $thumb_width < $video_width || ( empty( $video_width ) && $thumb_height < $video_height );
	}

	private function get_thumbnail_sizes() {
		if ( ! $this->thumb_sizes ) {
			$this->thumb_sizes = $this->prepare_thumbnail_sizes();
		}

		return $this->thumb_sizes;
	}

	private function prepare_thumbnail_sizes() {
		$thumb_sizes = array(
			array( 295, 166 ), // Small 16:9.
			array( 640, 360 ), // Standard Definition SD) 16:9.
			array( 640, 480 ), // Standard Definition SD) 4:3.
			array( 1280, 720 ), // High Definition HD) 16:9.
			array( 1280, 960 ), // High Definition HD) 4:3.
			array( 1920, 1080 ), // Full High Definition Full HD.
		);

		$thumb_sizes = apply_filters( 'wp_smush_lazy_load_vimeo_thumbnail_sizes', $thumb_sizes, $this->get_video_id(), $this->embed_url );

		// Sort by width.
		usort(
			$thumb_sizes,
			function ( $a, $b ) {
				if ( isset( $a[1], $b[1] ) ) {
					return $a[1] - $b[1];
				} else {
					return 0;
				}
			}
		);

		return $thumb_sizes;
	}

	private function fetch_thumbnail_info( $width, $height ) {
		$embed_api_url = $this->get_oembed_api_url( $width, $height );
		$timeout       = apply_filters( 'wp_smush_lazy_load_oembed_api_timeout', 15, $this->get_video_id(), $this->embed_url );
		$response      = wp_remote_get(
			$embed_api_url,
			array(
				'timeout' => $timeout,
			)
		);
		$video_info    = wp_remote_retrieve_body( $response );
		$thumb_info    = array();
		if ( ! empty( $video_info ) ) {
			$video_info = json_decode( $video_info, true );
			if (
				! empty( $video_info['thumbnail_url'] ) &&
				! empty( $video_info['thumbnail_width'] ) &&
				! empty( $video_info['thumbnail_height'] )
			) {
				$thumb_info = array(
					$video_info['thumbnail_url'],
					$video_info['thumbnail_width'],
					$video_info['thumbnail_height'],
				);
			}
		} elseif ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$error_code    = $response->get_error_code();
			$error_code    = $error_code ? $error_code : 'unknown_error';

			$this->logger()->error(
				sprintf(
					'Vimeo: Error fetching thumbnail info: %s (%s) | oEmbed API URL: %s',
					$error_message,
					$error_code,
					$embed_api_url
				)
			);
		}

		return $thumb_info;
	}

	private function get_retina_ratio( $width, $height ) {
		$retina_ratio = (int) apply_filters( 'wp_smush_lazy_load_vimeo_retina_ratio', null, $width, $height );
		if ( $retina_ratio > 0 ) {
			return $retina_ratio;
		}

		if ( $this->thumbnail_size_exists( $width, $height ) ) {
			return 1;
		}

		// Increase the video dimensions because video thumbnails are often smaller than the original video dimensions,
		// typically by a factor of 1.25 to 1.69.
		$retina_ratio = 1.5;

		return $retina_ratio;
	}

	private function thumbnail_size_exists( $width, $height ) {
		$common_thumbnail_sizes = $this->get_thumbnail_sizes();
		foreach ( $common_thumbnail_sizes as $size ) {
			if ( isset( $size[0], $size[1] ) && $size[0] === $width && $size[1] === $height ) {
				return true;
			}
		}

		return false;
	}

	private function get_oembed_api_url( $width, $height ) {
		// @see https://developer.vimeo.com/api/oembed/videos.
		$embed_url = add_query_arg(
			array(
				'width'  => $width,
				'height' => $height,
			),
			$this->embed_url
		);

		$oembed_api_url = sprintf( self::OEMBED_API_URL, $embed_url );

		return apply_filters( 'wp_smush_lazyload_vimeo_oembed_api_url', $oembed_api_url, $this->get_video_id(), $this->embed_url );
	}

	private function logger() {
		// Logger is a dynamic object, we will switch to another log file when point to another module,
		// so keep it as a function instead of a fixed variable to log into correct log file.
		return Helper::logger()->lazy();
	}

	public function get_cached_video_thumbnail( $video_width, $video_height ): ?Video_Thumbnail {
		list( $thumb_width, $thumb_height ) = $this->determine_best_thumbnail_size( $video_width, $video_height );

		return $this->video_thumbnail_cache->get( $this->get_video_id(), self::NAME, $thumb_width, $thumb_height );
	}
}
