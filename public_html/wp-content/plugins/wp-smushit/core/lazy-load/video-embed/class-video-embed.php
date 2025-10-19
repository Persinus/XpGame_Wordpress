<?php

namespace Smush\Core\Lazy_Load\Video_Embed;

interface Video_Embed {
	/**
	 * Get video provider name.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get video id.
	 *
	 * @return string.
	 */
	public function get_video_id();

	/**
	 * Get embed url.
	 *
	 * @return string
	 */
	public function get_embed_url();

	/**
	 * Check if can lazy load video.
	 *
	 * @return bool.
	 */
	public function can_lazy_load();

	/**
	 * Get video thumbnail.
	 *
	 * @param int $video_width Max width.
	 * @param int $video_height Max height.
	 *
	 * @return null|Video_Thumbnail
	 */
	public function fetch_video_thumbnail( $video_width, $video_height ): ?Video_Thumbnail;

	public function get_cached_video_thumbnail( $video_width, $video_height ): ?Video_Thumbnail;
}
