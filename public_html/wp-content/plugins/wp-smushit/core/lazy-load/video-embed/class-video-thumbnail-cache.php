<?php

namespace Smush\Core\Lazy_Load\Video_Embed;
class Video_Thumbnail_Cache {
	private static $instance;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function add( $video_id, $provider, $thumb_width, $thumb_height, Video_Thumbnail $video_thumbnail ) {
		$transient_key = $this->get_transient_key( $video_id, $provider, $thumb_width, $thumb_height );
		set_transient( $transient_key, $video_thumbnail->to_array() );
	}

	public function get( $video_id, $provider, $thumb_width, $thumb_height ): ?Video_Thumbnail {
		$transient_key        = $this->get_transient_key( $video_id, $provider, $thumb_width, $thumb_height );
		$video_thumbnail_data = get_transient( $transient_key );
		if ( ! $video_thumbnail_data ) {
			return null;
		}

		$video_thumbnail = new Video_Thumbnail();
		$video_thumbnail->from_array( $video_thumbnail_data );

		return $video_thumbnail;
	}

	private function get_transient_key( $video_id, $provider, $thumb_width, $thumb_height ) {
		return sprintf( 'wp-smush-video-thumbnail-%s-%s-%d-%d', $provider, $video_id, (int) $thumb_width, (int) $thumb_height );
	}
}
