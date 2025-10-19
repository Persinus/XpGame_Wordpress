import { isSmushLazySizesInstance } from './helper/lazysizes';

export const LAZY_BEFORE_SIZES = 'lazybeforesizes';
export const SMUSH_BEFORE_SIZES = 'smush:beforeSizes';
const SMUSH_CDN_DOMAIN = 'smushcdn.com';
const ATTR_DATA_ORIGINAL_SIZES = 'data-original-sizes';
const ATTR_DATA_SRCSET = 'data-srcset';
const ATTR_DATA_SRC = 'data-src';
const SUPPORTED_EXTENSIONS = [ 'gif', 'jpg', 'jpeg', 'png', 'webp' ];
const SRCSET_WIDTH_DESCRIPTOR = 'w';
const SRCSET_DENSITY_DESCRIPTOR = 'x';
/**
 * Class representing lazy loading functionality with CDN support.
 */
export class AutoResizing {
	/**
	 * Create a SmushLazyload instance.
	 *
	 * @param {Object}  [options={}]                  - Auto resize options for the instance.
	 * @param {number}  [options.precision=0]         - Allowed width variation (in pixels) for determining if resizing is necessary.
	 * @param {boolean} [options.skipAutoWidth=false] - Whether to skip auto width resizing.
	 */
	constructor( { precision = 0, skipAutoWidth = false } = {} ) {
		this.precision = parseInt( precision, 10 );
		this.precision = isNaN( this.precision ) ? 0 : this.precision;
		this.skipAutoWidth = skipAutoWidth;

		this.initEventListeners();
	}

	/**
	 * Initialize event listeners.
	 */
	initEventListeners() {
		document.addEventListener( LAZY_BEFORE_SIZES, ( e ) => {
			if ( ! isSmushLazySizesInstance( e.detail?.instance ) ) {
				return;
			}

			this.maybeAutoResize( e );
		} );
	}

	/**
	 * Auto resize for CDN images.
	 *
	 * @param {Object} lazyEvent - Event object.
	 * @return {void}
	 */
	maybeAutoResize( lazyEvent ) {
		const element = lazyEvent.target;
		let resizeWidth = lazyEvent.detail?.width;
		const isImage = 'IMG' === element?.nodeName;

		// Exit early if the element is not an image or resizeWidth is missing.
		if ( ! isImage || ! resizeWidth ) {
			return;
		}

		const isInitialRender = lazyEvent.detail?.dataAttr;

		// Skip processing if it's not the initial render.
		if ( ! isInitialRender ) {
			return;
		}

		// Check if the element is eligible for resizing.
		if ( ! this.isElementEligibleForResizing( element ) ) {
			return;
		}

		// Handle reverting to original sizes if necessary.
		if ( this.shouldRevertToOriginalSizes( element, resizeWidth ) ) {
			if ( this.revertToOriginalSizesIfNeeded( element ) ) {
				// Prevent lazySizes from resizing the image.
				lazyEvent.preventDefault();
			}
			return;
		}

		const customEvent = this.triggerEvent( element, SMUSH_BEFORE_SIZES, {
			resizeWidth
		} );

		if ( customEvent.defaultPrevented ) {
			// If the event is prevented, do not proceed with resizing and revert the sizes.
			if ( this.revertToOriginalSizesIfNeeded( element ) ) {
				// Prevent lazySizes from resizing the image.
				lazyEvent.preventDefault();
			}
			return;
		}

		resizeWidth = customEvent.detail?.resizeWidth || resizeWidth;

		// Resize the image using CDN if applicable.
		const src = this.getDataSrc( element );
		if ( this.isFromSmushCDN( src ) ) {
			this.resizeImageWithCDN( element, resizeWidth );

			if ( this.isChildOfPicture( element ) ) {
				this.resizeSourceElements( element.parentNode.querySelectorAll( 'source' ), resizeWidth );
			}
		}
	}

	/**
	 * Decide whether Smush should apply auto-resize for this image.
	 *
	 * Rules:
	 * 1. If wrapper is inline/inline-block and wrapper/image already equal resizeWidth, skip (prevents Divi shrink).
	 * 2. Otherwise, allow.
	 *
	 * @param  imageElement
	 * @param  resizeWidth
	 */
	shouldAutoResize( imageElement, resizeWidth ) {
		const wrapper = imageElement.parentNode;
		if ( wrapper && this.isInlineElement( wrapper ) ) {
			const wrapperWidth = wrapper.offsetWidth;
			const imageWidth = imageElement.offsetWidth;
			const isWrapperAndImageSameWidth = resizeWidth === wrapperWidth && wrapperWidth === imageWidth;

			if ( isWrapperAndImageSameWidth ) {
				// BAIL: doing a resize here risks shrinking the inline wrapper
				return false;
			}
		}

		return true;
	}

	isInlineElement( el ) {
		if ( ! el || el.nodeType !== 1 ) {
			return false;
		}
		const display = window.getComputedStyle( el ).display;
		return display === 'inline' || display === 'inline-block';
	}

	isChildOfPicture( imageElement ) {
		return imageElement && 'PICTURE' === imageElement?.parentNode?.nodeName;
	}

	resizeSourceElements( sourceElements, resizeWidth ) {
		if ( ! sourceElements || ! sourceElements?.length ) {
			return;
		}

		sourceElements.forEach( ( sourceElement ) => this.resizeSourceElement( sourceElement, resizeWidth ) );
	}

	resizeSourceElement( sourceElement, resizeWidth ) {
		const srcset = sourceElement.getAttribute( ATTR_DATA_SRCSET );
		if ( ! srcset ) {
			return;
		}

		const sortedSources = this.parseSrcSet( srcset );
		if ( ! sortedSources || ! sortedSources.length ) {
			return;
		}

		const baseSourceSrc = this.getBaseSourceSrcForResize( sortedSources, resizeWidth );
		if ( ! this.isFromSmushCDN( baseSourceSrc ) ) {
			return;
		}

		this.updateSrcsetForResize( sourceElement, srcset, baseSourceSrc, resizeWidth, sortedSources );
	}

	getBaseSourceSrcForResize( sortedSources, resizeWidth ) {
		const largestSource = sortedSources[ 0 ];

		if ( SRCSET_WIDTH_DESCRIPTOR !== largestSource.unit ) {
			return null;
		}

		if (
			! this.isThumbnail( largestSource.src ) ||
			largestSource.value >= resizeWidth
		) {
			return largestSource.src;
		}

		return null;
	}

	isElementEligibleForResizing( element ) {
		const existOriginalSizes = this.getOriginalSizesAttr( element );
		const existSrc = this.getDataSrc( element );
		const existSrcSet = this.getDataSrcSet( element );
		/**
		 * lazybeforesizes only fires for images with data-sizes="auto",
		 * so skip checking it.
		 */
		return Boolean( existOriginalSizes && existSrc && existSrcSet );
	}

	shouldRevertToOriginalSizes( element, resizeWidth ) {
		const imageWidth = this.getElementWidth( element );

		// Skip resizing if width is 'auto' and skipping is enabled.
		if ( imageWidth === 'auto' ) {
			return this.shouldSkipAutoWidth();
		}

		const originalSizes = this.getOriginalSizesAttr( element );
		const maxWidthFromSizes = this.getMaxWidthFromSizes( originalSizes );
		if ( maxWidthFromSizes && resizeWidth > maxWidthFromSizes && ! this.isChildOfPicture( element ) ) {
			return true;
		}

		return ! this.shouldAutoResize( element, resizeWidth );
	}

	triggerEvent( elem, name, detail = {}, bubbles = true, cancelable = true ) {
		const event = new CustomEvent( name, {
			detail,
			bubbles,
			cancelable,
		} );

		elem.dispatchEvent( event );
		return event;
	}

	/**
	 * Determines if auto width resizing should be skipped.
	 *
	 * @return {boolean} - True if auto width resizing should be skipped, false otherwise.
	 */
	shouldSkipAutoWidth() {
		return this.skipAutoWidth;
	}

	/**
	 * Resize the image using the CDN to generate appropriate sizes for the target width.
	 *
	 * @param {HTMLElement} element     - The image element to resize.
	 * @param {number}      resizeWidth - The target width for the image.
	 */
	resizeImageWithCDN( element, resizeWidth ) {
		const srcset = this.getDataSrcSet( element );
		const src = this.getDataSrc( element );

		// Exit early if the srcset or src is missing.
		if ( ! srcset || ! src ) {
			return;
		}

		// Parse the srcset once and reuse the parsed sources.
		const sortedSources = this.parseSrcSet( srcset );

		//the src attribute can be a thumbnail, so we need to get the larger image url to resize from it.
		const baseImageSrc = this.getBaseImageSrcForResize( src, sortedSources, resizeWidth );

		this.updateSrcsetForResize( element, srcset, baseImageSrc, resizeWidth, sortedSources );
	}

	updateSrcsetForResize( element, srcset, baseImageSrc, resizeWidth, sources ) {
		// Update the srcset with the target width.
		let newSrcset = this.updateSrcsetWithTargetWidth( srcset, baseImageSrc, resizeWidth, sources );

		// Update the srcset with retina-specific widths if applicable.
		newSrcset = this.updateSrcsetWithRetinaWidth( newSrcset, baseImageSrc, resizeWidth, sources );

		// Update the element's data-srcset attribute if the srcset has changed.
		this.updateElementSrcset( element, srcset, newSrcset );
	}

	getBaseImageSrcForResize( src, sortedSources, resizeWidth ) {
		if ( ! this.isThumbnail( src ) ) {
			return src;
		}

		// Find the largest source that is larger than resizing width.
		const largerSource = sortedSources.find( ( source ) => {
			return source.value >= resizeWidth;
		} );

		return largerSource ? largerSource.src : src;
	}

	isThumbnail( src ) {
		// Find the largest source that is larger than the current src.
		const regex = new RegExp( `(-\\d+x\\d+)\\.(${ SUPPORTED_EXTENSIONS.join( '|' ) })(?:\\?.+)?$`, 'i' );

		return regex.test( src );
	}

	/**
	 * Update the srcset with the target width if no similar source exists.
	 *
	 * @param {string} srcset      - The current srcset string.
	 * @param {string} src         - The original source URL of the image.
	 * @param {number} resizeWidth - The target width for the image.
	 * @param {Array}  sources     - The parsed sources from the srcset.
	 * @return {string} The updated srcset string.
	 */
	updateSrcsetWithTargetWidth( srcset, src, resizeWidth, sources ) {
		// Add a new source to the srcset if no similar source exists for the target width.
		if ( ! this.findSimilarSource( sources, resizeWidth ) ) {
			const resizedCDNURL = this.getResizedCDNURL( src, resizeWidth );
			return srcset + ', ' + resizedCDNURL + ' ' + resizeWidth + SRCSET_WIDTH_DESCRIPTOR;
		}

		return srcset;
	}

	/**
	 * Update the srcset with retina-specific widths if applicable.
	 *
	 * @param {string} srcset      - The current srcset string.
	 * @param {string} src         - The original source URL of the image.
	 * @param {number} resizeWidth - The target width for the image.
	 * @param {Array}  sources     - The parsed sources from the srcset.
	 * @return {string} The updated srcset string.
	 */
	updateSrcsetWithRetinaWidth( srcset, src, resizeWidth, sources ) {
		const scale = this.getPixelRatio();
		if ( scale <= 1 ) {
			return srcset;
		}

		const retinaWidth = Math.floor( resizeWidth * scale );
		const hasRetinaSource = this.findSimilarSource( sources, scale, SRCSET_DENSITY_DESCRIPTOR ) ||
								this.findSimilarSource( sources, retinaWidth, SRCSET_WIDTH_DESCRIPTOR );

		if ( hasRetinaSource ) {
			return srcset;
		}

		// Add a new retina source to the srcset if no similar source exists for the retina width.
		const retinaCDNURL = this.getResizedCDNURL( src, retinaWidth );
		const newRetinaSourceString = retinaCDNURL + ' ' + retinaWidth + SRCSET_WIDTH_DESCRIPTOR;

		return srcset + ', ' + newRetinaSourceString;
	}

	/**
	 * Update the element's data-srcset attribute if the srcset has changed.
	 *
	 * @param {HTMLElement} element        - The image element to update.
	 * @param {string}      originalSrcset - The original srcset string.
	 * @param {string}      newSrcset      - The updated srcset string.
	 */
	updateElementSrcset( element, originalSrcset, newSrcset ) {
		if ( newSrcset !== originalSrcset ) {
			element.setAttribute( 'data-srcset', newSrcset );
		}
	}

	/**
	 * Get the device pixel ratio.
	 *
	 * @return {number} The device pixel ratio. Default is 1 if the property is not available.
	 */
	getPixelRatio() {
		return window.devicePixelRatio || 1;
	}

	/**
	 * Finds and returns the first source object that has a similar width to the target width.
	 *
	 * @param {Array}  sources                    - An array of source objects to search through.
	 * @param {number} resizeWidth                - The target width to match against the source widths.
	 * @param {string} [unit='w']                 - The unit of measurement for the width (default is 'w').
	 * @param {number} [precision=this.precision] - The allowed width variation (in pixels) used to determine if a source width matches the target width during resizing.
	 * @return {Object|null} - The first source object that matches the criteria, or null if no match is found.
	 */
	findSimilarSource( sources, resizeWidth, unit = SRCSET_WIDTH_DESCRIPTOR, precision = this.precision ) {
		return sources.find( ( source ) => {
			return unit === source.unit && source.value >= resizeWidth &&
			this.isFuzzyMatch( source.value, resizeWidth, precision );
		} );
	}

	/**
	 * Get the resized image CDN URL.
	 *
	 * @param {string} src         - The original source URL of the image.
	 * @param {number} resizeWidth - The target width for the resized image.
	 * @return {string|undefined} The resized image CDN URL, or undefined if resizing is not applicable.
	 */
	getResizedCDNURL( src, resizeWidth ) {
		const url = this.parseURL( src );
		if ( ! url ) {
			return;
		}

		const searchParams = new URLSearchParams( url.search );
		searchParams.set( 'size', `${ resizeWidth }x0` );
		// Get the base URL (without search parameters).
		const baseUrl = url.origin + url.pathname;

		return `${ baseUrl }?${ searchParams.toString() }`;
	}

	/**
	 * Parse the URL from the source string.
	 *
	 * @param {string} src - The source URL string.
	 * @return {URL|null} The parsed URL object, or null if parsing fails.
	 */
	parseURL( src ) {
		try {
			return new URL( src );
		} catch ( error ) {
			return null;
		}
	}

	/**
	 * Extract width, unit, and src from srcset.
	 *
	 * @param {string} srcset - The srcset string.
	 * @return {Array} An array of objects source info.
	 */
	parseSrcSet( srcset ) {
		const sources = this.extractSourcesFromSrcSet( srcset );
		return this.sortSources( sources );
	}

	extractSourcesFromSrcSet( srcset ) {
		return srcset.split( ',' ).map( ( item ) => {
			const [ src, descriptor ] = item.trim().split( /\s+/ );
			let value = 0;
			let unit = '';

			if ( descriptor ) {
				if ( descriptor.endsWith( SRCSET_WIDTH_DESCRIPTOR ) ) {
					value = parseInt( descriptor, 10 );
					unit = SRCSET_WIDTH_DESCRIPTOR;
				} else if ( descriptor.endsWith( SRCSET_DENSITY_DESCRIPTOR ) ) {
					value = parseFloat( descriptor );
					unit = SRCSET_DENSITY_DESCRIPTOR;
				}
			}

			return {
				markup: item,
				src,
				value,
				unit,
			};
		} );
	}

	sortSources( sources ) {
		sources.sort( ( a, b ) => {
			if ( a.value === b.value ) {
				return 0;
			}
			return a.value > b.value ? -1 : 1;
		} );
		return sources;
	}

	/**
	 * Revert to the original sizes attribute.
	 *
	 * @param {Object} element - Image element.
	 * @return {boolean} True if the original sizes were reverted, false otherwise.
	 */
	revertToOriginalSizesIfNeeded( element ) {
		const originalSizes = this.getOriginalSizesAttr( element );
		if ( originalSizes ) {
			element.setAttribute( 'sizes', originalSizes );
			element.removeAttribute( ATTR_DATA_ORIGINAL_SIZES );

			return true;
		}

		return false;
	}

	/**
	 * Get the image width.
	 *
	 * @param {Object} element - Image element.
	 * @return {string|number} The image width.
	 */
	getElementWidth( element ) {
		/**
		 * Check if the element has an inline width set to 'auto'.
		 * Note: For external CSS, we couldn't cover it due to getComputedStyle just returning the parsed value.
		 */
		const inlineWidth = element.style?.width;
		if ( inlineWidth && 'auto' === inlineWidth.trim() ) {
			return 'auto';
		}

		const widthStr = window.getComputedStyle( element ).width;
		const width = parseInt( widthStr, 10 );

		return isNaN( width ) ? widthStr : width;
	}

	/**
	 * Get the content width from the original sizes attribute.
	 *
	 * @param {string} originalSizes - The original sizes attribute.
	 * @return {number} The content width.
	 */
	getMaxWidthFromSizes( originalSizes ) {
		const regex = /\(max-width:\s*(\d+)px\)\s*100vw,\s*\1px/;
		const match = originalSizes.match( regex );

		return match ? parseInt( match[ 1 ], 10 ) : 0;
	}

	/**
	 * Get the original sizes attribute.
	 *
	 * @param {Object} element - Image element.
	 * @return {string} The original sizes attribute.
	 */
	getOriginalSizesAttr( element ) {
		return element.getAttribute( ATTR_DATA_ORIGINAL_SIZES );
	}

	/**
	 * Get the srcset attribute.
	 *
	 * @param {Object} element - Image element.
	 * @return {string} The srcset attribute.
	 */
	getDataSrcSet( element ) {
		return element.getAttribute( ATTR_DATA_SRCSET );
	}

	/**
	 * Get the src attribute.
	 *
	 * @param {Object} element - Image element.
	 * @return {string} The src attribute.
	 */
	getDataSrc( element ) {
		return element.getAttribute( ATTR_DATA_SRC );
	}

	/**
	 * Check if the source is from the CDN.
	 *
	 * @param {string} src - The source URL.
	 * @return {boolean} True if the source is from the CDN, false otherwise.
	 */
	isFromSmushCDN( src ) {
		return src && src.includes( SMUSH_CDN_DOMAIN );
	}

	/**
	 * Perform a fuzzy match between two numbers.
	 *
	 * @param {number} number1       - The first number.
	 * @param {number} number2       - The second number.
	 * @param {number} [precision=1] - The allowed variation. Default is 1.
	 * @return {boolean} True if the numbers are close enough, false otherwise.
	 */
	isFuzzyMatch( number1, number2, precision = 1 ) {
		return Math.abs( number1 - number2 ) <= precision;
	}
}
( () => {
	'use strict';
	const isAutoResizingEnabled = window.smushLazyLoadOptions?.autoResizingEnabled;
	if ( ! isAutoResizingEnabled ) {
		return;
	}

	let autoResizeOptions = window.smushLazyLoadOptions?.autoResizeOptions || {};
	autoResizeOptions = Object.assign(
		{
			precision: 5, //5px.
			skipAutoWidth: true, // Whether to skip the image has 'auto' width.
		},
		autoResizeOptions
	);
	new AutoResizing( autoResizeOptions );
} )();
