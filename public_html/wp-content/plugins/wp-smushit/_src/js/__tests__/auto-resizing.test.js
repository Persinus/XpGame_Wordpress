import { SMUSH_BEFORE_SIZES, LAZY_BEFORE_SIZES, AutoResizing } from '../frontend/lazy-load/auto-resizing';
import { isSmushLazySizesInstance } from '../frontend/lazy-load/helper/lazysizes';

jest.mock( '../frontend/lazy-load/helper/lazysizes', () => ( {
	isSmushLazySizesInstance: jest.fn(),
} ) );

describe( 'AutoResizing', () => {
	let instance;

	beforeEach( () => {
		document.body.innerHTML = ''; // Clear DOM
		isSmushLazySizesInstance.mockReset();
		instance = new AutoResizing( { precision: 5, skipAutoWidth: true } );
	} );

	test( 'initializes with default values', () => {
		const auto = new AutoResizing();
		expect( auto.precision ).toBe( 0 );
		expect( auto.skipAutoWidth ).toBe( false );
	} );

	test.each([
		[
			'Smush LazySizes instance is valid',
			true,
			'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=500x0 500w'
		],
		[
			'Smush LazySizes instance is invalid',
			false,
			'https://smushcdn.com/img-300x300.jpg 300w'
		]
	])( 'Should handle LAZY_BEFORE_SIZES event based on Smush LazySizes instance: %s', ( description, isValidLazyInstance, expectedSrcset ) => {
		isSmushLazySizesInstance.mockReturnValue( isValidLazyInstance );

		const imageMarkup = `<img data-src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px">`;
		const container = document.createElement( 'div' );
		container.innerHTML = imageMarkup;
		const img = container.firstElementChild;
		// Appending the image into document to ensures the event can propagate through the DOM tree,
		// allowing listeners on document to catch it.
		document.body.appendChild( img );

		const event = new CustomEvent( LAZY_BEFORE_SIZES, {
			detail: { instance: {}, width: 500, dataAttr: true },
			bubbles: true,
		} );
		Object.defineProperty( event, 'target', { value: img } );
		document.dispatchEvent( event );

		const srcset = img.getAttribute( 'data-srcset' );
		expect( srcset ).toBe( expectedSrcset );
	} );

	test.each( [
		[
			'skips resizing when width is invalid',
			'img', null, true, 'https://smushcdn.com/img-300x300.jpg 300w'
		],
		[
			'skips resizing when not initial render',
			'img', 500, false, 'https://smushcdn.com/img-300x300.jpg 300w'
		],
		[
			'performs resizing for valid image and initial render',
			'img', 500, true, 'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=500x0 500w'
		],
		[
			'skips resizing when element is not an image',
			'div', 500, true, 'https://smushcdn.com/img-300x300.jpg 300w'
		],
	] )( 'skips processing when element is not image or missing width, dataAttr: %s', ( description, tagName, resizeWidth, isInitialRender, expectedSrcset ) => {
		const element = document.createElement( tagName );
		element.setAttribute( 'data-src', 'https://smushcdn.com/img.jpg' );
		element.setAttribute( 'data-srcset', 'https://smushcdn.com/img-300x300.jpg 300w' );
		element.setAttribute( 'data-original-sizes', '(max-width: 1024px) 100vw, 1024px' );
		document.body.appendChild( element );

		const event = new CustomEvent( LAZY_BEFORE_SIZES, {
			detail: { instance: {}, width: resizeWidth, dataAttr: isInitialRender },
			bubbles: true,
		} );
		Object.defineProperty( event, 'target', { value: element } );

		isSmushLazySizesInstance.mockReturnValue( true );
		document.dispatchEvent( event );

		const srcset = element.getAttribute( 'data-srcset' );
		expect( srcset ).toBe( expectedSrcset );
	} );

	test.each( [
		[
			'Image element eligible for resizing',
			`<img data-src="https://example.com/img.jpg"
				data-srcset="https://example.com/img.jpg 300w"
				data-sizes="auto"
				data-original-sizes="(max-width: 400px) 100vw, 400px"
				width="350" >`,
			true
		],
		[
			'Image element NOT eligible for resizing due to missing data-src',
			`<img src="https://example.com/img.jpg"
				data-srcset="https://example.com/img.jpg 300w"
				data-sizes="auto"
				data-original-sizes="(max-width: 400px) 100vw, 400px"
				width="350" >`,
			false
		],
		[
			'Image element NOT eligible for resizing due to missing data-srcset',
			`<img data-src="https://example.com/img.jpg"
				srcset="https://example.com/img.jpg 300w"
				data-sizes="auto"
				data-original-sizes="(max-width: 400px) 100vw, 400px"
				width="350" >`,
			false
		],
		[
			'Image element NOT eligible for resizing due to missing data-original-sizes',
			`<img data-src="https://example.com/img.jpg"
				srcset="https://example.com/img.jpg 300w"
				data-sizes="auto"
				width="350" >`,
			false
		],
	] )( 'test isElementEligibleForResizing: %s', ( description, imageMarkup, expectedResult ) => {
		const container = document.createElement( 'div' );
		container.innerHTML = imageMarkup;
		const img = container.firstElementChild;

		expect( instance.isElementEligibleForResizing( img ) ).toBe( expectedResult );
	} );

	test.each( [
		[
			'Image has max width smaller than resizing width, revert to original sizes.',
			`<img data-src="https://example.com/img.jpg"
				data-srcset="https://example.com/img.jpg 300w"
				data-sizes="auto"
				data-original-sizes="(max-width: 300px) 100vw, 300px">`,
			'(max-width: 300px) 100vw, 300px'
		],
		[
			'Image has max width equal resizing width does not revert sizes.',
			`<img data-src="https://example.com/img.jpg"
				data-srcset="https://example.com/img.jpg 900w"
				data-sizes="auto"
				data-original-sizes="(max-width: 350px) 100vw, 350px">`,
			null
		],
		[
			'Image has width auto, revert to original sizes.',
			`<img data-src="https://example.com/img.jpg"
				data-srcset="https://example.com/img.jpg 400w"
				data-sizes="auto"
				data-original-sizes="(max-width: 400px) 100vw, 400px"
				style="width:auto" >`,
			'(max-width: 400px) 100vw, 400px'
		],
		[
			'Valid resizing image, keep using data-size="auto".',
			`<img data-src="https://example.com/img.jpg"
				data-srcset="https://example.com/img.jpg 400w"
				data-sizes="auto"
				data-original-sizes="(max-width: 400px) 100vw, 400px" >`,
			null
		],
		[
			'Image with no data-original-sizes does not revert sizes.',
			`<img data-src="https://example.com/img.jpg"
				data-srcset="https://example.com/img.jpg 300w"
				data-sizes="auto" style="width:auto">`,
			null
		],
	] )( 'reverts to original sizes if applicable: %s', ( description, imageMarkup, expectedSizes ) => {
		const container = document.createElement( 'div' );
		container.innerHTML = imageMarkup;
		const img = container.firstElementChild;
		const isRevertedToOriginalSizes = Boolean( expectedSizes );

		const preventDefault = jest.fn();
		const event = {
			detail: { instance: {}, width: 350, dataAttr: true },
			target: img,
			preventDefault,
		};

		instance.maybeAutoResize( event );

		expect( img.getAttribute( 'sizes' ) ).toBe( expectedSizes );
		expect( preventDefault ).toHaveBeenCalledTimes( isRevertedToOriginalSizes ? 1 : 0 );
	} );

	test( 'Revert original sizes via custom event', () => {
		isSmushLazySizesInstance.mockReturnValue( true );

		const img = document.createElement( 'img' );
		img.setAttribute( 'data-original-sizes', '(max-width: 400px) 100vw, 400px' );
		img.setAttribute( 'data-src', 'https://example.com/img.jpg?size=400x0' );
		img.setAttribute( 'data-srcset', 'https://example.com/img.jpg 400w' );
		img.setAttribute( 'data-sizes', 'auto' );
		// Appending the image into document to ensures the event can propagate through the DOM tree,
		// allowing listeners on document to catch it.
		document.body.appendChild( img );

		// Listen and prevent default on the custom event
		const handler = function( e ) {
			e.preventDefault();
		};
		document.addEventListener( SMUSH_BEFORE_SIZES, handler );

		// Create a real CustomEvent
		const event = new CustomEvent( LAZY_BEFORE_SIZES, {
			detail: { instance: {}, width: 350, dataAttr: true },
			bubbles: true,
			cancelable: true,
		} );
		Object.defineProperty( event, 'target', { value: img } );

		// Call the method that triggers the event internally
		document.dispatchEvent( event );

		expect( img.getAttribute( 'sizes' ) ).toBe( '(max-width: 400px) 100vw, 400px' );
		expect( img.getAttribute( 'data-srcset' ) ).toBe( 'https://example.com/img.jpg 400w' );

		document.removeEventListener( SMUSH_BEFORE_SIZES, handler );
	} );

	const skippedImages = [
		[
			'Image missing data-src',
			`<img src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px" >`,
			'https://smushcdn.com/img-300x300.jpg 300w'
		],
		[
			'Image missing data-srcset',
			`<img data-src="https://smushcdn.com/img.jpg"
				srcset="https://smushcdn.com/img-300x300.jpg 300w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px" >`,
			''
		],
		[
			'Image skipped due to source is not from Smush CDN',
			`<img src="https://example.com/img.jpg"
				data-srcset="https://example.com/img-300x300.jpg 300w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px" >`,
			'https://example.com/img-300x300.jpg 300w'
		],
		[
			'Image skipped due to has similar source in srcset',
			`<img data-src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=605x0 605w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px" >`,
			'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=605x0 605w'
		],
	];

	const autoResizedImages = [
		[
			'Resizes CDN image and appends new srcset entry for requested width',
			`<img data-src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px" >`,
			'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=600x0 600w'
		],
		[
			'Adds new CDN srcset entry for 600w since it is not within precision (5) of existing 594w',
			`<img data-src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=594x0 594w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px" >`,
			'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=594x0 594w, https://smushcdn.com/img.jpg?size=600x0 600w'
		],
		[
			'Adds new CDN srcset entry for 600w since similar source in srcset smaller than requested width',
			`<img data-src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px" >`,
			'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w, https://smushcdn.com/img.jpg?size=600x0 600w'
		],
	];

	test.each( [
		...skippedImages,
		...autoResizedImages,
	] )( 'test resizeImageWithCDN: %s', ( description, imageMarkup, expectedSrcset ) => {
		const container = document.createElement( 'div' );
		container.innerHTML = imageMarkup;
		const img = container.firstElementChild;

		const event = {
			detail: { width: 600, instance: {}, dataAttr: true },
			target: img,
			preventDefault: jest.fn(),
		};

		instance.maybeAutoResize( event );

		const newSrcset = img.getAttribute( 'data-srcset' ) || '';
		expect( newSrcset ).toBe( expectedSrcset );
	} );

	const autoResizedImagesWithRetina = [
		[
			'Resizes CDN image and appends new srcset entry for requested width',
			`<img data-src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px" >`,
			'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=600x0 600w, https://smushcdn.com/img.jpg?size=1200x0 1200w'
		],
		[
			'Appends new CDN srcset entry for requested width, preserving existing retina source',
			`<img data-src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=1202x0 1202w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px" >`,
			'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=1202x0 1202w, https://smushcdn.com/img.jpg?size=600x0 600w'
		],
		[
			'Adds both standard and retina srcset entries when original sizes match requested width',
			`<img data-src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
				data-original-sizes="(max-width: 600px) 100vw, 600px" >`,
			'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=600x0 600w, https://smushcdn.com/img.jpg?size=1200x0 1200w'
		],
	];

	test.each(
		autoResizedImagesWithRetina
	)( 'test resizeImageWithCDN with retina: %s', ( description, imageMarkup, expectedSrcset ) => {
		const originalDevicePixelRatio = window.devicePixelRatio;
		window.devicePixelRatio = 2; // Simulate retina display

		const container = document.createElement( 'div' );
		container.innerHTML = imageMarkup;
		const img = container.firstElementChild;

		const event = {
			detail: { width: 600, instance: {}, dataAttr: true },
			target: img,
			preventDefault: jest.fn(),
		};

		instance.maybeAutoResize( event );

		const newSrcset = img.getAttribute( 'data-srcset' ) || '';
		expect( newSrcset ).toBe( expectedSrcset );

		// Revert the devicePixelRatio to its original value.
		window.devicePixelRatio = originalDevicePixelRatio;
	} );

	test( 'resizeImageWithCDN with custom resizing width', () => {
		const imageMarkup = `<img data-src="https://smushcdn.com/img.jpg"
				data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
				data-original-sizes="(max-width: 1024px) 100vw, 1024px">`;
		const container = document.createElement( 'div' );
		container.innerHTML = imageMarkup;
		const img = container.firstElementChild;
		// Appending the image into document to ensures the event can propagate through the DOM tree,
		// allowing listeners on document to catch it.
		document.body.appendChild( img );

		// Listen and prevent default on the custom event.
		const handler = function( e ) {
			e.detail.resizeWidth = 400;
		};
		document.addEventListener( SMUSH_BEFORE_SIZES, handler );

		// Create a real CustomEvent
		const event = new CustomEvent( LAZY_BEFORE_SIZES, {
			detail: { instance: {}, width: 600, dataAttr: true },
			bubbles: true,
			cancelable: true,
		} );

		Object.defineProperty( event, 'target', { value: img } );

		isSmushLazySizesInstance.mockReturnValue( true );
		document.dispatchEvent( event );

		const newSrcset = img.getAttribute( 'data-srcset' );
		const expectedSrcset = 'https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=400x0 400w';
		expect( newSrcset ).toBe( expectedSrcset );

		document.removeEventListener( SMUSH_BEFORE_SIZES, handler );
	} );

	const autoResizedPictures = [
		[
			'New srcset entry appended to source with webp type',
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.webp 300w, https://smushcdn.com/img.webp 1024w" type="image/webp">
				<source data-srcset="https://smushcdn.com/img.jpg?size=300x300 300w">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px" >
			</picture>`,
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.webp 300w, https://smushcdn.com/img.webp 1024w, https://smushcdn.com/img.webp?size=600x0 600w" type="image/webp">
				<source data-srcset="https://smushcdn.com/img.jpg?size=300x300 300w, https://smushcdn.com/img.jpg?size=600x0 600w">
				<img data-src="https://smushcdn.com/img.jpg" data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=600x0 600w" data-original-sizes="(max-width: 1024px) 100vw, 1024px">
			</picture>`
		],
		[
			'Adds new CDN srcset entry for 600w in <picture> since it is not within precision (5) of existing 594w',
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=594x0 594w">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=594x0 594w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px" >
			</picture>`,
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=594x0 594w, https://smushcdn.com/img.jpg?size=600x0 600w">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=594x0 594w, https://smushcdn.com/img.jpg?size=600x0 600w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px" >
			</picture>`
		],
		[
			'Adds new CDN srcset entry for 600w in <picture> since similar source in srcset smaller than requested width',
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px" >
			</picture>`,
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w, https://smushcdn.com/img.jpg?size=600x0 600w">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w, https://smushcdn.com/img.jpg?size=600x0 600w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px" >
			</picture>`
		],
		[
			'Adds new CDN srcset entry for 600w in <picture> since similar source in srcset smaller than requested width',
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px" >
			</picture>`,
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w, https://smushcdn.com/img.jpg?size=600x0 600w">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=595x0 595w, https://smushcdn.com/img.jpg?size=600x0 600w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px" >
			</picture>`
		],
		[
			'Multi-Breakpoint <Picture> template using srcset and sizes',
			`<picture>
				<source media="(min-width: 800px)"
					data-srcset="https://smushcdn.com/medium-large.jpg 800w, https://smushcdn.com/large.jpg 1024w"
					data-sizes="(min-width: 800px) 100vw">
				<source 
					media="(min-width: 500px)" 
					data-srcset="https://smushcdn.com/medium.jpg 500w, https://smushcdn.com/medium-smaller.jpg 400w"
					data-sizes="(min-width: 500px) 100vw">
				<img data-src="https://smushcdn.com/img.jpg"
						data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
						data-original-sizes="(max-width: 300px) 100vw, 300px" >
			</picture>`,
			`<picture>
				<source media="(min-width: 800px)"
					data-srcset="https://smushcdn.com/medium-large.jpg 800w, https://smushcdn.com/large.jpg 1024w, https://smushcdn.com/large.jpg?size=600x0 600w"
					data-sizes="(min-width: 800px) 100vw">
				<source 
					media="(min-width: 500px)" 
					data-srcset="https://smushcdn.com/medium.jpg 500w, https://smushcdn.com/medium-smaller.jpg 400w, https://smushcdn.com/medium.jpg?size=600x0 600w"
					data-sizes="(min-width: 500px) 100vw">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=600x0 600w"
					data-original-sizes="(max-width: 300px) 100vw, 300px">
			</picture>`
		],
		[
			'Responsive <source> with type and media attributes in <picture> element',
			`<picture>
				<source media="(min-width: 800px)"
					type="image/webp"
					data-srcset="https://smushcdn.com/medium-large.webp 800w, https://smushcdn.com/large.webp 1024w"
					data-sizes="(min-width: 800px) 100vw">
				<source media="(min-width: 800px)"
					type="image/jpeg"
					data-srcset="https://smushcdn.com/medium-large.jpg 800w, https://smushcdn.com/large.jpg 1024w"
					data-sizes="(min-width: 800px) 100vw">
				<source 
					media="(max-width: 799px)"
					type="image/webp"
					data-srcset="https://smushcdn.com/medium.webp 500w, https://smushcdn.com/medium-smaller.webp 400w"
					data-sizes="100vw">
				<source 
					media="(max-width: 799px)"
					type="image/jpeg"
					data-srcset="https://smushcdn.com/medium.jpg 500w, https://smushcdn.com/medium-smaller.jpg 400w"
					data-sizes="100vw">
				<img data-src="https://smushcdn.com/img.jpg"
						data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
						data-original-sizes="(max-width: 300px) 100vw, 300px" >
			</picture>`,
			`<picture>
				<source media="(min-width: 800px)"
					type="image/webp"
					data-srcset="https://smushcdn.com/medium-large.webp 800w, https://smushcdn.com/large.webp 1024w, https://smushcdn.com/large.webp?size=600x0 600w"
					data-sizes="(min-width: 800px) 100vw">
				<source media="(min-width: 800px)"
					type="image/jpeg"
					data-srcset="https://smushcdn.com/medium-large.jpg 800w, https://smushcdn.com/large.jpg 1024w, https://smushcdn.com/large.jpg?size=600x0 600w"
					data-sizes="(min-width: 800px) 100vw">
				<source 
					media="(max-width: 799px)"
					type="image/webp"
					data-srcset="https://smushcdn.com/medium.webp 500w, https://smushcdn.com/medium-smaller.webp 400w, https://smushcdn.com/medium.webp?size=600x0 600w"
					data-sizes="100vw">
				<source 
					media="(max-width: 799px)"
					type="image/jpeg"
					data-srcset="https://smushcdn.com/medium.jpg 500w, https://smushcdn.com/medium-smaller.jpg 400w, https://smushcdn.com/medium.jpg?size=600x0 600w"
					data-sizes="100vw">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=600x0 600w"
					data-original-sizes="(max-width: 300px) 100vw, 300px">
			</picture>`
		],
	];

	const skippedPictures = [
		[
			'Skipped appends new srcset entry for source elements in <picture> due to using x descriptor',
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.webp 1x, https://smushcdn.com/img.webp 2x" type="image/webp">
				<source data-srcset="https://smushcdn.com/img.jpg?size=300x300 1x">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px" >
			</picture>`,
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.webp 1x, https://smushcdn.com/img.webp 2x" type="image/webp">
				<source data-srcset="https://smushcdn.com/img.jpg?size=300x300 1x">
				<img data-src="https://smushcdn.com/img.jpg" data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=600x0 600w" data-original-sizes="(max-width: 1024px) 100vw, 1024px">
			</picture>`
		],
		[
			'Skipped not responsive <source> elements in the <picture> element',
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.webp" type="image/webp">
				<source data-srcset="https://smushcdn.com/img.jpg?size=300x300" type="image/jpeg">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px" >
			</picture>`,
			`<picture>
				<source data-srcset="https://smushcdn.com/img-300x300.webp" type="image/webp">
				<source data-srcset="https://smushcdn.com/img.jpg?size=300x300" type="image/jpeg">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=600x0 600w"
					data-original-sizes="(max-width: 1024px) 100vw, 1024px">
			</picture>`,
		],
		[
			'Not supported <picture> element due to missing w descriptor',
			`<picture>
			<source media="(min-width: 800px)" data-srcset="https://smushcdn.com/large.jpg">
			<source media="(min-width: 500px)" data-srcset="https://smushcdn.com/medium.jpg">
				<img data-src="https://smushcdn.com/img.jpg"
						data-srcset="https://smushcdn.com/img-300x300.jpg 300w"
						data-original-sizes="(max-width: 300px) 100vw, 300px" >
			</picture>`,
			`<picture>
				<source media="(min-width: 800px)" data-srcset="https://smushcdn.com/large.jpg">
				<source media="(min-width: 500px)" data-srcset="https://smushcdn.com/medium.jpg">
				<img data-src="https://smushcdn.com/img.jpg"
					data-srcset="https://smushcdn.com/img-300x300.jpg 300w, https://smushcdn.com/img.jpg?size=600x0 600w"
					data-original-sizes="(max-width: 300px) 100vw, 300px">
			</picture>`
		]
	];

	test.each([
		...skippedPictures,
		... autoResizedPictures
	] )( 'test auto resize picture element: %s', ( description, pictureMarkup, expectedMarkup ) => {
		const container = document.createElement( 'div' );
		container.innerHTML = pictureMarkup;
		const picture = container.firstElementChild;
		document.body.appendChild( picture );

		const event = {
			detail: { width: 600, instance: {}, dataAttr: true },
			target: document.body.querySelector('img'),
			preventDefault: jest.fn(),
		};

		instance.maybeAutoResize( event );
		const updatedMarkup = document.body.querySelector('picture').outerHTML;

		expect( normalizeHtml( updatedMarkup ) ).toBe( normalizeHtml( expectedMarkup ) );
	} );

	function normalizeHtml(html) {
		return html
			.replace(/\s+/g, ' ')   // collapse whitespace
			.replace(/\s+>/g, '>')  // remove space(s) before '>'
			.trim();
	}

	test.each(
		[
			[
				// 1
				'No match: all sources are smaller than required width',
				[
					{ value: 300, unit: 'w' },
					{ value: 600, unit: 'w' },
				],
				602,
				undefined,
			],
			[
				// 2
				'No match: units do not match',
				[
					{ value: 300, unit: 'w' },
					{ value: 604, unit: 'h' },
				],
				602,
				undefined,
			],
			[
				// 3
				'Match found: source within precision range of required width',
				[
					{ value: 300, unit: 'w' },
					{ value: 604, unit: 'w' },
				],
				602,
				{ value: 604 },
			],
		]
	)( 'findSimilarSource returns matching source within precision: %s', ( description, sources, resizeWidth, expectedMatch ) => {
		const unit = 'w';
		const precision = 5;
		const found = instance.findSimilarSource( sources, resizeWidth, unit, precision );
		const expectedResult = expectedMatch ? expect.objectContaining( expectedMatch ) : undefined;
		expect( found ).toEqual( expectedResult );
	} );

	test( 'getElementWidth returns correctly.', () => {
		const img = document.createElement( 'img' );
		// Mock getComputedStyle to return a non-numeric width
		window.getComputedStyle = () => ( { width: 'auto' } );
		expect( instance.getElementWidth( img ) ).toBe( 'auto' );
		window.getComputedStyle = () => ( { width: '100px' } );
		expect( instance.getElementWidth( img ) ).toBe( 100 );
	} );

	test( 'parseSrcSet sorts sources in descending order', () => {
		const srcset = 'img-200.jpg 200w, img-400.jpg 400w, img-300.jpg 300w';
		const sources = instance.parseSrcSet( srcset );
		expect( sources[ 0 ].value ).toBe( 400 );
		expect( sources[ 1 ].value ).toBe( 300 );
		expect( sources[ 2 ].value ).toBe( 200 );
	} );

	test( 'parseSrcSet parses retina descriptors correctly', () => {
		const srcset = 'img-1x.jpg 1x, img-2x.jpg 2x, img-400.jpg 400w';
		const sources = instance.parseSrcSet( srcset );

		// Should parse the 'x' descriptors as floats and unit as 'x'
		expect( sources.find( ( s ) => s.unit === 'x' && s.value === 2 ).src ).toBe( 'img-2x.jpg' );
		expect( sources.find( ( s ) => s.unit === 'x' && s.value === 1 ).src ).toBe( 'img-1x.jpg' );
		// Should also parse the 'w' descriptor
		expect( sources.find( ( s ) => s.unit === 'w' && s.value === 400 ).src ).toBe( 'img-400.jpg' );
	} );

	test.each( [
		[
			'Not a thumbnail',
			'https://example.com/image.jpg',
			[
				{ value: 500, src: 'https://example.com/image-500x500.jpg' },
				{ value: 300, src: 'https://example.com/image-300x300.jpg' },
				{ value: 200, src: 'https://example.com/image-200x200.jpg' },
			],
			400,
			'https://example.com/image.jpg',
		],
		[
			'Is a thumbnail but no larger source',
			'https://example.com/image.jpg',
			[
				{ value: 300, src: 'https://example.com/image-300x300.jpg' },
				{ value: 200, src: 'https://example.com/image-200x200.jpg' },
			],
			400,
			'https://example.com/image.jpg',
		],
		[
			'Is a thumbnail and larger source exists',
			'https://example.com/image-400x400.jpg',
			[
				{ value: 500, src: 'https://example.com/image-500x500.jpg' },
				{ value: 300, src: 'https://example.com/image-300x300.jpg' },
				{ value: 200, src: 'https://example.com/image-200x200.jpg' },
			],
			400,
			'https://example.com/image-500x500.jpg',
		]
	] )(
		'getBaseImageSrcForResize %s',
		( desc, src, sortedSources, resizeWidth, expected ) => {
			expect( instance.getBaseImageSrcForResize( src, sortedSources, resizeWidth ) ).toBe( expected );
		}
	);

	test( 'updateElementSrcset sets attribute only if changed', () => {
		const img = document.createElement( 'img' );
		const originalSrcset = 'img-300.jpg 300w, img-600.jpg 600w';
		const newSrcset = 'img-300.jpg 300w, img-600.jpg 600w, img-900.jpg 900w';

		// Should set attribute because newSrcset !== originalSrcset
		instance.updateElementSrcset( img, originalSrcset, newSrcset );
		expect( img.getAttribute( 'data-srcset' ) ).toBe( newSrcset );

		// Should NOT set attribute because newSrcset === originalSrcset
		img.removeAttribute( 'data-srcset' );
		instance.updateElementSrcset( img, originalSrcset, originalSrcset );
		expect( img.getAttribute( 'data-srcset' ) ).toBe( null );
	} );

	test( 'parseSrcSet handles sources with equal values and sorts descending', () => {
		const srcset = 'img-200.jpg 200w, img-400.jpg 400w, img-400b.jpg 400w, img-300.jpg 300w';
		const sources = instance.parseSrcSet( srcset );

		// Should be sorted descending, and equal values retain their relative order
		expect( sources[ 0 ].value ).toBe( 400 );
		expect( sources[ 1 ].value ).toBe( 400 );
		expect( sources[ 2 ].value ).toBe( 300 );
		expect( sources[ 3 ].value ).toBe( 200 );

		// The two 400w sources should both be present and in the order they appeared in the srcset
		expect( sources[ 0 ].src ).toBe( 'img-400.jpg' );
		expect( sources[ 1 ].src ).toBe( 'img-400b.jpg' );
	} );
} );
