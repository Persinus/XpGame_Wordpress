import { isSmushLazySizesInstance } from './helper/lazysizes';

( () => {
	'use strict';
	// Lazyload for background images.
	const lazyloadBackground = ( element ) => {
		const backgroundValue = element.getAttribute( 'data-bg-image' ) || element.getAttribute( 'data-bg' );
		const cssProperty = element.hasAttribute( 'data-bg-image' ) ? 'background-image' : 'background';

		if ( backgroundValue ) {
			const currentStyle = element.getAttribute( 'style' ) || '';
			const newBackgroundCSS = `${ cssProperty }: ${ backgroundValue };`;
			const backgroundRegex = new RegExp( `${ cssProperty }\\s*:\\s*[^;]+;?` );

			let updatedStyle;
			if ( backgroundRegex.test( currentStyle ) ) {
				updatedStyle = currentStyle.replace( backgroundRegex, newBackgroundCSS );
			} else {
				updatedStyle = currentStyle.length > 0 ? currentStyle.replace( /;$/g, '' ) + ';' + newBackgroundCSS : newBackgroundCSS;
			}

			element.setAttribute( 'style', updatedStyle.trim() );
		}
	};

	document.addEventListener( 'lazybeforeunveil', function( e ) {
		if ( ! isSmushLazySizesInstance( e?.detail?.instance ) ) {
			return;
		}

		// Lazy background image.
		lazyloadBackground( e.target );
	} );
} )();
