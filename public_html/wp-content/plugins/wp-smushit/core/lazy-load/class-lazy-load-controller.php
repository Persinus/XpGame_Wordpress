<?php
/**
 * Lazy load images class: Lazy
 *
 * @since 3.2.0
 * @package Smush\Core\Modules
 */

namespace Smush\Core\Lazy_Load;

use Smush\Core\Controller;
use Smush\Core\Parser\Page_Parser;
use Smush\Core\Server_Utils;
use Smush\Core\Settings;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Lazy
 */
class Lazy_Load_Controller extends Controller {
	const LAZY_LOAD_TRANSFORM_PRIORITY = 20;

	/**
	 * Module slug.
	 *
	 * @var string
	 */
	protected $slug = 'lazy_load';

	/**
	 * Lazy loading settings.
	 *
	 * @since 3.2.0
	 * @var array $settings
	 */
	private $options;

	/**
	 * Excluded classes list.
	 *
	 * @since 3.6.2
	 * @var array
	 */
	private $excluded_classes = array(
		'no-lazyload', // Internal class to skip images.
		'skip-lazy',
		'rev-slidebg', // Skip Revolution slider images.
		'soliloquy-preload', // Soliloquy slider.
	);

	/**
	 * Static instance
	 *
	 * @var self
	 */
	private static $instance;
	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var Lazy_Load_Helper
	 */
	private $helper;

	/**
	 * Static instance getter
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize module actions.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {
		$this->settings = Settings::get_instance();
		$this->helper   = Lazy_Load_Helper::get_instance();

		$this->register_action( 'wp_smush_content_transforms', array(
			$this,
			'register_lazy_load_transform',
		), self::LAZY_LOAD_TRANSFORM_PRIORITY );

		// Only run on front end and if lazy loading is enabled.
		if ( is_admin() || ! $this->settings->is_module_active( 'lazy_load' ) ) {
			return;
		}

		$this->options = $this->settings->get_setting( 'wp-smush-lazy_load' );

		// Enabled without settings? Don't think so... Exit.
		if ( ! $this->options ) {
			return;
		}

		// Disable WordPress native lazy load.
		$this->register_filter( 'wp_lazy_loading_enabled', array( $this, 'should_enable_wordpress_native_lazyload' ) );

		// Load js file that is required in public facing pages.
		$this->register_action( 'wp_head', array( $this, 'add_inline_styles' ) );
		$this->register_action( 'wp_head', array( $this, 'add_early_inline_styles' ), 5 );
		$this->register_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 99 );
		if ( defined( 'WP_SMUSH_ASYNC_LAZY' ) && WP_SMUSH_ASYNC_LAZY ) {
			$this->register_filter( 'script_loader_tag', array( $this, 'async_load' ), 10, 2 );
		}

		// Allow lazy load attributes in img tag.
		$this->register_filter( 'wp_kses_allowed_html', array( $this, 'add_lazy_load_attributes' ) );

		// Filter images.
		if ( ! isset( $this->options['output']['content'] ) || ! $this->options['output']['content'] ) {
			$this->register_filter( 'the_content', array( $this, 'exclude_from_lazy_loading' ), 100 );
		}
		if ( ! isset( $this->options['output']['thumbnails'] ) || ! $this->options['output']['thumbnails'] ) {
			$this->register_filter( 'post_thumbnail_html', array( $this, 'exclude_from_lazy_loading' ), 100 );
		}
		if ( ! isset( $this->options['output']['gravatars'] ) || ! $this->options['output']['gravatars'] ) {
			$this->register_filter( 'get_avatar', array( $this, 'exclude_from_lazy_loading' ), 100 );
		}
		if ( ! isset( $this->options['output']['widgets'] ) || ! $this->options['output']['widgets'] ) {
			$this->register_action( 'dynamic_sidebar_before', array( $this, 'filter_sidebar_content_start' ), 0 );
			$this->register_action( 'dynamic_sidebar_after', array( $this, 'filter_sidebar_content_end' ), 1000 );
		}
	}
	
	public function add_early_inline_styles() {
		if ( $this->helper->should_skip_lazyload() ) {
			return;
		}
		?>
		<style>
			.lazyload,
			.lazyloading {
				max-width: 100%;
			}
		</style>
		<?php
	}

	/**
	 * Add inline styles at the top of the page for pre-loaders and effects.
	 *
	 * @since 3.2.0
	 */
	public function add_inline_styles() {
		if ( $this->helper->should_skip_lazyload() ) {
			return;
		}
		// Lazy load embed video styles.
		$this->add_inline_embed_video_css();
		// Fix for poorly coded themes that do not remove the no-js in the HTML class.
		?>
		<script>
			document.documentElement.className = document.documentElement.className.replace('no-js', 'js');
		</script>
		<?php
		if ( empty( $this->options['animation']['selected'] ) || 'none' === $this->options['animation']['selected'] ) {
			return;
		}

		// Spinner.
		if ( 'spinner' === $this->options['animation']['selected'] ) {
			$loader = WP_SMUSH_URL . 'app/assets/images/smush-lazyloader-' . $this->options['animation']['spinner']['selected'] . '.gif';
			if ( isset( $this->options['animation']['spinner']['selected'] ) && 5 < (int) $this->options['animation']['spinner']['selected'] ) {
				$loader = wp_get_attachment_image_src( $this->options['animation']['spinner']['selected'], 'full' );
				$loader = $loader[0];
			}
			$background = 'rgba(255, 255, 255, 0)';
		} else {
			// Placeholder.
			$loader     = WP_SMUSH_URL . 'app/assets/images/smush-placeholder.png';
			$background = '#FAFAFA';
			if ( isset( $this->options['animation']['placeholder']['selected'] ) && 2 === (int) $this->options['animation']['placeholder']['selected'] ) {
				$background = '#333333';
			}
			if ( isset( $this->options['animation']['placeholder']['selected'] ) && 2 < (int) $this->options['animation']['placeholder']['selected'] ) {
				$loader = wp_get_attachment_image_src( (int) $this->options['animation']['placeholder']['selected'], 'full' );

				// Can't find a loader on multisite? Try main site.
				if ( ! $loader && is_multisite() ) {
					switch_to_blog( 1 );
					$loader = wp_get_attachment_image_src( (int) $this->options['animation']['placeholder']['selected'], 'full' );
					restore_current_blog();
				}

				$loader = $loader[0];
			}
			if ( isset( $this->options['animation']['placeholder']['color'] ) ) {
				$background = $this->options['animation']['placeholder']['color'];
			}
		}

		// Fade in.
		$fadein = isset( $this->options['animation']['fadein']['duration'] ) ? $this->options['animation']['fadein']['duration'] : 0;
		$delay  = isset( $this->options['animation']['fadein']['delay'] ) ? $this->options['animation']['fadein']['delay'] : 0;
		?>
		<style>
			.no-js img.lazyload {
				display: none;
			}

			figure.wp-block-image img.lazyloading {
				min-width: 150px;
			}

			.lazyload,
			.lazyloading {
				--smush-placeholder-width: 100px;
				--smush-placeholder-aspect-ratio: 1/1;
				width: var(--smush-image-width, var(--smush-placeholder-width)) !important;
				aspect-ratio: var(--smush-image-aspect-ratio, var(--smush-placeholder-aspect-ratio)) !important;
			}

			<?php if ( 'fadein' === $this->options['animation']['selected'] ) : ?>
			.lazyload, .lazyloading {
				opacity: 0;
			}

			.lazyloaded {
				opacity: 1;
				transition: opacity <?php echo esc_html( $fadein ); ?>ms;
				transition-delay: <?php echo esc_html( $delay ); ?>ms;
			}

			<?php else : ?>
			.lazyload {
				opacity: 0;
			}

			.lazyloading {
				border: 0 !important;
				opacity: 1;
				background: <?php echo esc_attr( $background ); ?> url('<?php echo esc_url( $loader ); ?>') no-repeat center !important;
				background-size: 16px auto !important;
				min-width: 16px;
			}

			<?php endif; ?>
		</style>
		<?php
	}

	private function add_inline_embed_video_css() {
		if ( ! $this->helper->should_lazy_load_embed_video() ) {
			return;
		}
		?>
		<style>
			/* Thanks to https://github.com/paulirish/lite-youtube-embed and https://css-tricks.com/responsive-iframes/ */
			.smush-lazyload-video {
				--smush-video-aspect-ratio: 16/9;background-color: #000;position: relative;display: block;contain: content;background-position: center center;background-size: cover;cursor: pointer;
			}
			.smush-lazyload-video.loading{cursor:progress}
			.smush-lazyload-video::before{content:'';display:block;position:absolute;top:0;background-image:linear-gradient(rgba(0,0,0,0.6),transparent);background-position:top;background-repeat:repeat-x;height:60px;width:100%;transition:all .2s cubic-bezier(0,0,0.2,1)}
			.smush-lazyload-video::after{content:"";display:block;padding-bottom:calc(100% / (var(--smush-video-aspect-ratio)))}
			.smush-lazyload-video > iframe{width:100%;height:100%;position:absolute;top:0;left:0;border:0;opacity:0;transition:opacity .5s ease-in}
			.smush-lazyload-video.smush-lazyloaded-video > iframe{opacity:1}
			.smush-lazyload-video > .smush-play-btn{z-index:10;position: absolute;top:0;left:0;bottom:0;right:0;}
			.smush-lazyload-video > .smush-play-btn > .smush-play-btn-inner{opacity:0.75;display:flex;align-items: center;width:68px;height:48px;position:absolute;cursor:pointer;transform:translate3d(-50%,-50%,0);top:50%;left:50%;z-index:1;background-repeat:no-repeat;background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 68 48"><path d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55c-2.93.78-4.63 3.26-5.42 6.19C.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z" fill="red"/><path d="M45 24 27 14v20" fill="white"/></svg>');filter:grayscale(100%);transition:filter .5s cubic-bezier(0,0,0.2,1), opacity .5s cubic-bezier(0,0,0.2,1);border:none}
			.smush-lazyload-video:hover .smush-play-btn-inner,.smush-lazyload-video .smush-play-btn-inner:focus{filter:none;opacity:1}
			.smush-lazyload-video > .smush-play-btn > .smush-play-btn-inner span{display:none;width:100%;text-align:center;}
			.smush-lazyload-video.smush-lazyloaded-video{cursor:unset}
			.smush-lazyload-video.video-loaded::before,.smush-lazyload-video.smush-lazyloaded-video > .smush-play-btn,.smush-lazyload-video.loading > .smush-play-btn{display:none;opacity:0;pointer-events:none}
			.smush-lazyload-video.smush-lazyload-vimeo > .smush-play-btn > .smush-play-btn-inner{background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 203 120' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m0.25116 9.0474c0-4.9968 4.0507-9.0474 9.0474-9.0474h184.4c4.997 0 9.048 4.0507 9.048 9.0474v101.91c0 4.996-4.051 9.047-9.048 9.047h-184.4c-4.9968 0-9.0474-4.051-9.0474-9.047v-101.91z' fill='%2317d5ff' fill-opacity='.7'/%3E%3Cpath d='m131.1 59.05c0.731 0.4223 0.731 1.4783 0 1.9006l-45.206 26.099c-0.7316 0.4223-1.646-0.1056-1.646-0.9504v-52.199c0-0.8448 0.9144-1.3727 1.646-0.9504l45.206 26.099z' fill='%23fff'/%3E%3C/svg%3E%0A");width:81px}
			<?php if ( get_theme_support( 'responsive-embeds' ) ) : ?>
				.wp-embed-responsive .wp-has-aspect-ratio .smush-lazyload-video{position:absolute;width:100%;height:100%;top:0;left:0}.wp-embed-responsive .wp-has-aspect-ratio .smush-lazyload-video::after{padding-bottom:0}
			<?php endif; ?>
		</style>
		<?php
	}

	/**
	 * Enqueue JS files required in public pages.
	 *
	 * @since 3.2.0
	 */
	public function enqueue_assets() {
		if (
			$this->helper->should_skip_lazyload()
			|| ( $this->helper->is_native_lazy_loading_enabled() && ! $this->helper->should_lazy_load_embed_video() )
		) {
			return;
		}

		$script = WP_SMUSH_URL . 'app/assets/js/smush-lazy-load.min.js';

		$in_footer = isset( $this->options['footer'] ) ? $this->options['footer'] : true;

		wp_enqueue_script(
			'smush-lazy-load',
			$script,
			array(),
			WP_SMUSH_VERSION,
			$in_footer
		);

		$lazy_load_script_options = apply_filters(
			'smush_lazy_load_script_options',
			array(
				'autoResizingEnabled' => $this->settings->is_auto_resizing_active(),
				'autoResizeOptions'   => array(
					'precision' => 5, //5px.
					'skipAutoWidth' => true, // Whether to skip the image has 'auto' width.
				),
			)
		);

		wp_add_inline_script(
			'smush-lazy-load',
			'var smushLazyLoadOptions = ' . wp_json_encode( $lazy_load_script_options ) . ';',
			'before'
		);

		$this->add_masonry_support();
		if ( defined( 'WP_SMUSH_LAZY_LOAD_AVADA' ) && WP_SMUSH_LAZY_LOAD_AVADA ) {
			$this->add_avada_support();
		}
		$this->add_divi_support();
		$this->add_soliloquy_support();
	}

	/**
	 * Async load the lazy load scripts.
	 *
	 * @param string $tag The <script> tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 *
	 * @return string
	 * @since 3.7.0
	 *
	 */
	public function async_load( $tag, $handle ) {
		if ( 'smush-lazy-load' === $handle ) {
			return str_replace( ' src', ' async="async" src', $tag );
		}

		return $tag;
	}

	/**
	 * Add support for plugins that use the masonry grid system (Block Gallery and CoBlocks plugins).
	 *
	 * @since 3.5.0
	 *
	 * @see https://wordpress.org/plugins/coblocks/
	 * @see https://github.com/godaddy/block-gallery
	 * @see https://masonry.desandro.com/methods.html#layout-masonry
	 */
	private function add_masonry_support() {
		if ( ! function_exists( 'has_block' ) ) {
			return;
		}

		// None of the supported blocks are active - exit.
		if ( ! has_block( 'blockgallery/masonry' ) && ! has_block( 'coblocks/gallery-masonry' ) ) {
			return;
		}

		$js = "var e = jQuery( '.wp-block-coblocks-gallery-masonry ul' );";
		if ( has_block( 'blockgallery/masonry' ) ) {
			$js = "var e = jQuery( '.wp-block-blockgallery-masonry ul' );";
		}

		$block_gallery_compat = "jQuery(document).on('lazyloaded', function(){{$js} if ('function' === typeof e.masonry) e.masonry();});";

		wp_add_inline_script( 'smush-lazy-load', $block_gallery_compat );
	}

	/**
	 * Add fusion gallery support in Avada theme.
	 *
	 * @since 3.7.0
	 */
	private function add_avada_support() {
		if ( ! defined( 'FUSION_BUILDER_VERSION' ) ) {
			return;
		}

		$js = "var e = jQuery( '.fusion-gallery' );";

		$block_gallery_compat = "jQuery(document).on('lazyloaded', function(){{$js} if ('function' === typeof e.isotope) e.isotope();});";

		wp_add_inline_script( 'smush-lazy-load', $block_gallery_compat );
	}

	/**
	 * Adds lazyload support to Divi & it's Waypoint library.
	 *
	 * @since 3.9.0
	 */
	private function add_divi_support() {
		if ( ! defined( 'ET_BUILDER_THEME' ) || ! ET_BUILDER_THEME ) {
			return;
		}

		$script = "function rw() { Waypoint.refreshAll(); } window.addEventListener( 'lazybeforeunveil', rw, false); window.addEventListener( 'lazyloaded', rw, false);";

		wp_add_inline_script( 'smush-lazy-load', $script );
	}

	/**
	 * Prevents the navigation from being missaligned in Soliloquy when lazy loading.
	 *
	 * @since 3.7.0
	 */
	private function add_soliloquy_support() {
		if ( ! function_exists( 'soliloquy' ) ) {
			return;
		}

		$js = "var e = jQuery( '.soliloquy-image:not(.lazyloaded)' );";

		$soliloquy = "jQuery(document).on('lazybeforeunveil', function(){{$js}e.each(function(){lazySizes.loader.unveil(this);});});";

		wp_add_inline_script( 'smush-lazy-load', $soliloquy );
	}

	/**
	 * Make sure WordPress does not filter out img elements with lazy load attributes.
	 *
	 * @param array $allowedposttags Allowed post tags.
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	public function add_lazy_load_attributes( $allowedposttags ) {
		if ( ! isset( $allowedposttags['img'] ) ) {
			return $allowedposttags;
		}

		$smush_attributes = array(
			'data-src'    => true,
			'data-srcset' => true,
			'data-sizes'  => true,
		);

		$img_attributes = array_merge( $allowedposttags['img'], $smush_attributes );

		$allowedposttags['img'] = $img_attributes;

		return $allowedposttags;
	}

	/**
	 * Get images from content and add exclusion class.
	 *
	 * @param string $content Page/block content.
	 *
	 * @return string
	 * @since 3.2.2
	 *
	 */
	public function exclude_from_lazy_loading( $content ) {
		$server_utils       = new Server_Utils();
		$page               = new Page_Parser( $server_utils->get_request_uri(), $content );
		$parsed_page        = $page->parse_page();
		$images             = $parsed_page->get_elements();
		$composite_images   = $parsed_page->get_composite_elements();
		$all_image_elements = ! empty( $composite_images )
			? array_merge( $images, $this->extract_images_from_composite_elements( $composite_images ) )
			: $images;

		if ( empty( $all_image_elements ) ) {
			return $content;
		}

		// Process all images.
		foreach ( $all_image_elements as $image ) {
			// Add .no-lazyload class.
			$image->append_attribute_value( 'class', 'no-lazyload' );

			/**
			 * Filters the no-lazyload image.
			 *
			 * @param string $text The image that can be filtered.
			 *
			 * @since 3.8.5
			 *
			 */
			$new_markup = apply_filters( 'wp_smush_filter_no_lazyload_image', $image->get_updated_markup() );

			$content = str_replace( $image->get_markup(), $new_markup, $content );
		}

		return $content;
	}

	/**
	 * Extracts individual image elements from composite elements.
	 *
	 * @param array $composite_elements Array of composite elements.
	 *
	 * @return array Array of individual image elements.
     * @since 3.18.0
     *
	 */
	private function extract_images_from_composite_elements( $composite_elements ) {
		$individual_images = [];

		foreach ( $composite_elements as $composite_element ) {
			$element_images = $composite_element->get_elements();
			if ( ! empty( $element_images ) ) {
				$individual_images = array_merge( $individual_images, $element_images );
			}
		}

		return $individual_images;
	}

	/**
	 * Buffer sidebar content.
	 *
	 * @since 3.2.0
	 */
	public function filter_sidebar_content_start() {
		ob_start();
	}

	/**
	 * Process buffered content.
	 *
	 * @since 3.2.0
	 */
	public function filter_sidebar_content_end() {
		$content = ob_get_clean();

		echo $this->exclude_from_lazy_loading( $content );

		unset( $content );
	}

	public function register_lazy_load_transform( $transforms ) {
		$transforms['lazy_load'] = new Lazy_Load_Transform();

		return $transforms;
	}

	public function should_enable_wordpress_native_lazyload() {
		return $this->helper->is_native_lazy_loading_enabled();
	}
}
