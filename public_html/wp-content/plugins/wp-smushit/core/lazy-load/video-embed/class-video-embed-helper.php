<?php

namespace Smush\Core\Lazy_Load\Video_Embed;

defined( 'ABSPATH' ) || exit;

class Video_Embed_Helper {
	/**
	 * @var self
	 */
	private static $instance;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function make_video_thumbnail_url( $embed_url, $video_width, $video_height ) {
		return admin_url( sprintf( 'admin-ajax.php?action=smush_video_thumbnail&url=%s&video_width=%d&video_height=%d', urlencode( $embed_url ), (int) $video_width, (int) $video_height ) );
	}

	public function create_embed_object( $embed_url ): ?Video_Embed {
		$provider_classes = self::get_embed_provider_classes();

		if ( empty( $provider_classes ) || ! is_array( $provider_classes ) ) {
			return null;
		}

		foreach ( $provider_classes as $provider_class ) {
			$provider_instance = new $provider_class( $embed_url );
			if ( $provider_instance->can_lazy_load() ) {
				return $provider_instance;
			}
		}

		return null;
	}

	private function get_embed_provider_classes() {
		$embed_provider_classes = array(
			Youtube_Embed::class,
			Vimeo_Embed::class,
		);

		return apply_filters( 'wp_smush_lazy_load_embed_provider_classes', $embed_provider_classes );
	}
}
