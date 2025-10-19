<?php

namespace Smush\Core\Lazy_Load\Video_Embed;

use Smush\Core\Next_Gen\Next_Gen_Manager;
use Smush\Core\Settings;
use Smush\Core\Url_Utils;


if ( ! defined( 'WPINC' ) ) {
	die;
}

class Youtube_Embed implements Video_Embed {
	const NAME = 'youtube';
	const VIDEO_ID_REGEX = '#youtube(?:-nocookie)?\.com\/embed\/(?<video_id>[a-zA-Z0-9_-]{11})#i';
	const THUMBNAIL_URL_FORMAT = 'https://i.ytimg.com/%1$s/%2$s/%3$s.%4$s';
	const THUMBNAIL_DEFAULT = 'default';
	const THUMBNAIL_MEDIUM = 'mqdefault';
	const THUMBNAIL_HIGH = 'hqdefault';
	const THUMBNAIL_SD = 'sddefault';
	const THUMBNAIL_MAX = 'maxresdefault';

	/**
	 * @var Settings
	 */
	private $settings;

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
	 * @var Video_Embed_Helper
	 */
	protected $video_helper;

	/**
	 * Thumbnail sizes.
	 *
	 * @var array
	 */
	private $thumb_sizes;
	private Video_Thumbnail_Cache $video_thumbnail_cache;
	private Url_Utils $url_utils;

	public function __construct( $embed_url ) {
		$this->embed_url             = $embed_url;
		$this->settings              = Settings::get_instance();
		$this->video_helper          = Video_Embed_Helper::get_instance();
		$this->video_thumbnail_cache = Video_Thumbnail_Cache::get_instance();
		$this->url_utils             = new Url_Utils();
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
		$video_id = $this->get_video_id();

		return ! empty( $video_id );
	}

	public function get_video_id() {
		if ( ! $this->video_id ) {
			$this->video_id = $this->prepare_video_id();
		}

		return $this->video_id;
	}

	private function prepare_video_id() {
		$video_id_regex = apply_filters( 'wp_smush_lazy_load_youtube_id_regex', self::VIDEO_ID_REGEX );
		if ( ! preg_match( $video_id_regex, $this->embed_url, $matches ) ) {
			return null;
		}

		$video_id = $matches['video_id'] ?? '';
		// TODO: check if we need to support data attribute for youtube playlist.
		$video_id    = apply_filters( 'wp_smush_lazy_load_youtube_video_id', $video_id, $this->embed_url );
		$is_playlist = 'videoseries' === $video_id;

		return $is_playlist ? null : $video_id;
	}

	public function fetch_video_thumbnail( $video_width, $video_height ): ?Video_Thumbnail {
		$video_id = $this->get_video_id();
		if ( ! $video_id ) {
			return null;
		}

		list( $thumb_size_name, $thumb_width, $thumb_height ) = $this->determine_best_thumbnail_size( $video_width, $video_height );

		$cached = $this->video_thumbnail_cache->get( $video_id, self::NAME, $thumb_width, $thumb_height );
		if ( $cached ) {
			return $cached;
		}

		$next_gen_thumbnail_url = $this->get_thumbnail_url( $thumb_size_name, true );
		$fallback_thumbnail_url = $this->get_thumbnail_url( $thumb_size_name, false );
		$video_ratio            = $video_width > 0 && $video_height > 0 ? "{$video_width}/{$video_height}" : "{$thumb_width}/{$thumb_height}";

		$video_thumbnail = new Video_Thumbnail();
		$video_thumbnail->from_array(
			array(
				'width'        => $thumb_width,
				'height'       => $thumb_height,
				'next_gen_url' => $next_gen_thumbnail_url,
				'fallback_url' => $fallback_thumbnail_url,
				'aspect_ratio' => $video_ratio,
			)
		);

		$this->video_thumbnail_cache->add( $video_id, self::NAME, $thumb_width, $thumb_height, $video_thumbnail );

		return $video_thumbnail;
	}

	private function determine_best_thumbnail_size( $video_width, $video_height ) {
		$thumb_sizes = $this->get_thumbnail_sizes();

		$thumb_size  = array();
		$larger_size = null;
		foreach ( $thumb_sizes as $size ) {
			list( , $thumb_width, $thumb_height ) = $size;
			if ( $this->is_smaller_than_video( $thumb_width, $thumb_height, $video_width, $video_height ) ) {
				continue;
			}

			if ( empty( $video_width ) || empty( $video_height ) ) {
				return $size;
			}

			if ( $this->is_aspect_ratio_match( $video_width, $video_height, $thumb_width, $thumb_height ) ) {
				return $size;
			}

			if ( empty( $larger_size ) ) {
				$larger_size = $size;
			}
		}

		if ( empty( $thumb_size ) ) {
			$thumb_size = $larger_size ?? end( $thumb_sizes );
		}

		if (
			empty( $thumb_size ) ||
			! isset( $thumb_size[0] ) ||
			! $this->is_thumbnail_available( $thumb_size[0] )
		) {
			// Try standard size which should exist for all video
			// due to Youtube will automatically generate three thumbnail sizes (Default, Medium, High-Resolution).
			$thumb_size = array( self::THUMBNAIL_HIGH, 480, 360 );
		}

		return $thumb_size;
	}

	private function is_smaller_than_video( $thumb_width, $thumb_height, $video_width, $video_height ): bool {
		return $thumb_width < $video_width || ( empty( $video_width ) && $thumb_height < $video_height );
	}

	private function is_aspect_ratio_match( $video_width, $video_height, $thumb_width, $thumb_height ): bool {
		return wp_fuzzy_number_match( $video_width / $thumb_width, $video_height / $thumb_height, 0.1 );
	}

	private function is_thumbnail_available( $thumb_size_name ) {
		$standard_sizes = array(
			self::THUMBNAIL_DEFAULT,
			self::THUMBNAIL_MEDIUM,
			self::THUMBNAIL_HIGH,
		);

		if ( in_array( $thumb_size_name, $standard_sizes, true ) ) {
			return true;
		}

		$thumbnail_url = $this->get_thumbnail_url( $thumb_size_name );

		return $this->url_utils->url_has_200_response( $thumbnail_url );
	}

	private function get_thumbnail_sizes() {
		if ( ! $this->thumb_sizes ) {
			$this->thumb_sizes = $this->prepare_thumbnail_sizes();
		}

		return $this->thumb_sizes;
	}

	private function prepare_thumbnail_sizes() {
		// @see https://gist.github.com/a1ip/be4514c1fd392a8c13b05e082c4da363.
		$thumb_sizes = array(
			array( self::THUMBNAIL_DEFAULT, 120, 90 ), // - Default 4:3.
			array( self::THUMBNAIL_MEDIUM, 320, 180 ), // - Medium Quality 16:9.
			array( self::THUMBNAIL_HIGH, 480, 360 ), // - High Quality 4:3.
			array( self::THUMBNAIL_SD, 640, 480 ), // - Standard Definition 4:3.
			array( self::THUMBNAIL_MAX, 1280, 720 ), // - Maximum Resolution 16:9.
		);

		$thumb_sizes = apply_filters( 'wp_smush_lazy_load_youtube_thumbnail_sizes', $thumb_sizes, $this->get_video_id(), $this->embed_url );

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

	private function get_thumbnail_url( $thumb_size_name, $use_next_gen_format = false ) {
		if ( $use_next_gen_format ) {
			$extension     = 'webp';
			$extension_uri = 'vi_webp';
		} else {
			$extension     = 'jpg';
			$extension_uri = 'vi';
		}

		return sprintf( self::THUMBNAIL_URL_FORMAT, $extension_uri, $this->get_video_id(), $thumb_size_name, $extension );
	}

	public function get_cached_video_thumbnail( $video_width, $video_height ): ?Video_Thumbnail {
		list( , $thumb_width, $thumb_height ) = $this->determine_best_thumbnail_size( $video_width, $video_height );

		return $this->video_thumbnail_cache->get( $this->get_video_id(), $this->get_name(), $thumb_width, $thumb_height );
	}
}
