<?php

namespace Smush\Core\Lazy_Load\Video_Embed;

use Smush\Core\Controller;
use Smush\Core\Lazy_Load\Lazy_Load_Helper;
use Smush\Core\Server_Utils;
use Smush\Core\Settings;
use Smush\Core\Url_Utils;

class Video_Thumbnail_Controller extends Controller {
	private $video_helper;
	private Lazy_Load_Helper $lazy_helper;
	/**
	 * @var Settings
	 */
	private $settings;

	private Server_Utils $server_utils;
	private Url_Utils $url_utils;

	public function __construct() {
		$this->video_helper = Video_Embed_Helper::get_instance();
		$this->lazy_helper  = Lazy_Load_Helper::get_instance();
		$this->settings     = Settings::get_instance();
		$this->server_utils = new Server_Utils();
		$this->url_utils    = new Url_Utils();

		$this->register_action( 'wp_ajax_smush_video_thumbnail', array( $this, 'redirect_to_original_video_thumbnail' ) );
		$this->register_action( 'wp_ajax_nopriv_smush_video_thumbnail', array( $this, 'redirect_to_original_video_thumbnail' ) );
	}

	public function should_run() {
		return parent::should_run()
		       && $this->settings->is_lazyload_active()
		       && $this->lazy_helper->should_lazy_load_embed_video();
	}

	public function redirect_to_original_video_thumbnail() {
		$embed_url = empty( $_GET['url'] ) ? '' : $_GET['url'];
		$width     = empty( $_GET['video_width'] ) ? '' : $_GET['video_width'];
		$height    = empty( $_GET['video_height'] ) ? '' : $_GET['video_height'];
		if ( ! $embed_url || ( ! $width && ! $height ) ) {
			status_header( 404 );
			exit;
		}

		$embed           = $this->video_helper->create_embed_object( $embed_url );
		$video_thumbnail = $embed->fetch_video_thumbnail( $width, $height );
		if ( ! $video_thumbnail ) {
			status_header( 404 );
			exit;
		}

		$thumbnail_url = '';
		$nextgen_url   = $video_thumbnail->get_next_gen_url();
		if ( $nextgen_url ) {
			$nextgen_extension = $this->url_utils->get_extension( $nextgen_url );
			$nextgen_supported = $this->server_utils->browser_supports_nextgen_format( $nextgen_extension );
			if ( $nextgen_supported ) {
				$thumbnail_url = $nextgen_url;
			}
		}

		if ( empty( $thumbnail_url ) ) {
			$thumbnail_url = $video_thumbnail->get_fallback_url();
		}

		$expires = 31536000;
		header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + $expires ) . " GMT" );
		header( "Cache-Control: public, max-age={$expires}, immutable" );
		header( "Location: {$thumbnail_url}", true, 301 );
		exit();
	}
}
