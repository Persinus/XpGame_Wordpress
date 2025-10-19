import { isSmushLazySizesInstance } from './helper/lazysizes';

( () => {
	'use strict';
	// Constants
	const VIDEO_WRAPPER_CLASS = 'smush-lazyload-video';
	const LAZY_LOADED_CLASS = 'smush-lazyloaded-video';
	const AUTO_PLAY_CLASS = 'smush-lazyload-autoplay';
	const PLAY_BUTTON_CLASS = 'smush-play-btn';
	const DEFAULT_ALLOW_ATTR = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
	const USER_INTERACTION_EVENT = 'ontouchstart' in window ? 'touchstart' : 'pointerdown';
	const FALLBACK_VIDEO_RENDER_DELAY = window?.smush_video_render_delay || 0;

	/**
	 * LazyLoadVideo Class
	 * Handles lazy loading and autoplay functionality for videos.
	 */
	class LazyLoadVideo {
		constructor() {
			this.shouldDelayVideoRenderingForMobile = this.supportsIntersectionObserver();
			this.queuedVideoElements = [];
			this.isMobileOrSafari = null;

			this.init();
		}

		/**
		 * Initialize event listeners and fallback mechanisms.
		 */
		init() {
			document.addEventListener( 'lazybeforeunveil', ( e ) => this.handleVideoLazyLoad( e ) );
			document.addEventListener(
				USER_INTERACTION_EVENT,
				() => this.enableVideoRenderingForMobile(),
				{ once: true, passive: true }
			);

			// Unified fallback for delayed video rendering.
			const maybeTriggerVideoRenderingFallbackForMobile = () => {
				if ( ! FALLBACK_VIDEO_RENDER_DELAY || FALLBACK_VIDEO_RENDER_DELAY < 0 ) {
					return;
				}

				setTimeout( () => this.enableVideoRenderingForMobile(), FALLBACK_VIDEO_RENDER_DELAY );
			};

			document.addEventListener( 'DOMContentLoaded', maybeTriggerVideoRenderingFallbackForMobile );
		}

		/**
		 * Handles lazy loading of video elements.
		 *
		 * @param {Event} e - The lazybeforeunveil event.
		 */
		handleVideoLazyLoad( e ) {
			const videoWrapper = e.target;

			if (
				! isSmushLazySizesInstance( e?.detail?.instance ) ||
				! videoWrapper.classList.contains( VIDEO_WRAPPER_CLASS )
			) {
				return;
			}

			this.handleButtonPlay( videoWrapper );
			this.maybePrepareVideoForPlay( videoWrapper );
		}

		/**
		 * Handles the play button click event.
		 *
		 * @param {HTMLElement} videoWrapper - The video wrapper element.
		 */
		handleButtonPlay( videoWrapper ) {
			const playButton = videoWrapper.querySelector( `.${ PLAY_BUTTON_CLASS }` );

			if ( playButton ) {
				const playHandler = () => this.loadIframeVideoWithAutoPlay( videoWrapper );
				playButton.addEventListener( 'click', playHandler );
				playButton.addEventListener( 'keydown', ( e ) => {
					if ( e.key === 'Enter' || e.key === ' ' ) {
						e.preventDefault();
						playHandler();
					}
				} );
			} else {
				this.loadIframeVideo( videoWrapper );
				window.console?.warning( 'Missing play button [.smush-play-btn] for video element:', videoWrapper );
			}
		}

		/**
		 * Prepares the video for play based on autoplay and device conditions.
		 *
		 * @param {HTMLElement} videoWrapper - The video wrapper element.
		 */
		maybePrepareVideoForPlay( videoWrapper ) {
			const shouldAutoPlay = videoWrapper.classList.contains( AUTO_PLAY_CLASS );

			if ( this.shouldPrepareIframeForPlay() ) {
				this.maybePrepareVideoForMobileAndSafari( videoWrapper, shouldAutoPlay );
			} else if ( shouldAutoPlay ) {
				this.loadIframeVideoWithAutoPlay( videoWrapper );
			}
		}

		/**
		 * Enables video rendering for mobile devices.
		 */
		enableVideoRenderingForMobile() {
			if ( ! this.shouldDelayVideoRenderingForMobile ) {
				return;
			}

			this.shouldDelayVideoRenderingForMobile = false;
			this.maybeObserveQueuedVideoElements();
		}

		/**
		 * Checks if the browser supports IntersectionObserver.
		 *
		 * @return {boolean} True if supported, false otherwise.
		 */
		supportsIntersectionObserver() {
			return 'IntersectionObserver' in window;
		}

		/**
		 * Observes queued video elements for lazy loading.
		 */
		maybeObserveQueuedVideoElements() {
			if ( this.queuedVideoElements.length ) {
				this.observeQueuedVideoElements();
			}
		}

		/**
		 * Observes video elements using IntersectionObserver.
		 */
		observeQueuedVideoElements() {
			const observer = new IntersectionObserver(
				( entries ) => {
					entries.forEach( ( entry ) => {
						if ( entry.isIntersecting ) {
							const videoWrapper = entry.target;

							this.loadIframeVideo( videoWrapper );
							observer.unobserve( videoWrapper );
						}
					} );
				},
				{
					rootMargin: '0px 0px 200px 0px',
					threshold: 0.1,
				}
			);

			this.queuedVideoElements.forEach( ( videoWrapper ) => {
				observer.observe( videoWrapper );
			} );
		}

		/**
		 * Prepares video for mobile and Safari browsers.
		 *
		 * @param {HTMLElement} videoWrapper   - The video wrapper element.
		 * @param {boolean}     shouldAutoPlay - Whether the video should autoplay.
		 */
		maybePrepareVideoForMobileAndSafari( videoWrapper, shouldAutoPlay ) {
			if ( this.shouldDelayVideoRenderingForMobile ) {
				this.queuedVideoElements.push( videoWrapper );
				return;
			}

			this.loadIframeVideo( videoWrapper, shouldAutoPlay );
		}

		/**
		 * Checks if the iframe should be prepared for play.
		 *
		 * @return {boolean} True if preparation is needed, false otherwise.
		 */
		shouldPrepareIframeForPlay() {
			if ( this.isMobileOrSafari === null ) {
				this.isMobileOrSafari = this.checkIfMobileOrSafari();
			}

			return this.isMobileOrSafari;
		}

		/**
		 * Checks if the device is mobile or Safari browser.
		 *
		 * @return {boolean} True if mobile or Safari, false otherwise.
		 */
		checkIfMobileOrSafari() {
			const userAgent = navigator.userAgent;
			return userAgent.includes( 'Mobi' ) || ( userAgent.includes( 'Safari' ) && ! userAgent.includes( 'Chrome' ) );
		}

		/**
		 * Loads the iframe video.
		 *
		 * @param {HTMLElement} videoWrapper     - The video wrapper element.
		 * @param {boolean}     [autoPlay=false] - Whether to autoplay the video.
		 */
		loadIframeVideo( videoWrapper, autoPlay = false ) {
			if ( videoWrapper.classList.contains( LAZY_LOADED_CLASS ) ) {
				return;
			}

			videoWrapper.classList.add( LAZY_LOADED_CLASS, 'loading' );

			const iframe = videoWrapper.querySelector( 'iframe' );
			if ( ! iframe ) {
				window.console?.error( 'Missing iframe element in video wrapper:', videoWrapper );
				return;
			}

			let videoUrl = iframe.dataset?.src;
			if ( ! videoUrl ) {
				window.console?.error( 'Missing data-src attribute for iframe:', iframe );
				return;
			}

			if ( autoPlay ) {
				const url = new URL( videoUrl );
				url.searchParams.set( 'autoplay', '1' );
				url.searchParams.set( 'playsinline', '1' );
				videoUrl = url.toString();
			}

			let allowAttribute = iframe.getAttribute( 'allow' ) || DEFAULT_ALLOW_ATTR;
			if ( ! allowAttribute.includes( 'autoplay' ) ) {
				allowAttribute += '; autoplay';
			}

			iframe.setAttribute( 'allow', allowAttribute );
			iframe.setAttribute( 'allowFullscreen', 'true' );
			iframe.setAttribute( 'src', videoUrl );

			videoWrapper.classList.remove( 'loading' );
		}

		/**
		 * Loads the iframe video with autoplay enabled.
		 *
		 * @param {HTMLElement} videoWrapper - The video wrapper element.
		 */
		loadIframeVideoWithAutoPlay( videoWrapper ) {
			this.loadIframeVideo( videoWrapper, true );
		}
	}

	// Initialize LazyLoadVideo
	new LazyLoadVideo();
} )();
