import { onDocumentLoaded } from '../../helpers'
import ctEvents from 'ct-events'
import $ from 'jquery'

import './handle-events'

function isTouchDevice() {
	try {
		document.createEvent('TouchEvent')
		return true
	} catch (e) {
		return false
	}
}

export const wooEntryPoints = [
	{
		els: 'body.single-product .woocommerce-product-gallery:has(.ct-product-gallery-container)',
		load: () => import('./single-product-gallery'),
		trigger: [
			{
				id: 'hover-with-click',

				// We should never affect the pills because the click events on
				// the pills are very fragile and if we will do a preventDefault
				// on them, they will not work. This is especially tricky on
				// touch devices.
				ignoredEls: ['.flexy-pills'],
			},
		],
	},

	{
		els: 'form.variations_form',
		condition: () =>
			!!document.querySelector(
				'.woocommerce-product-gallery .ct-media-container'
			),
		load: () => import('./variable-products'),
		...(isTouchDevice() ||
		document.querySelectorAll(
			'form.variations_form[data-product_variations="false"]'
		)
			? {}
			: {
					trigger: ['hover'],
			  }),
	},

	{
		els: '.quantity .ct-increase, .quantity .ct-decrease',
		load: () => import('./quantity-input'),
		trigger: ['click'],
	},

	{
		els: '.ct-woocommerce-cart-form .quantity .qty',
		load: () => import('./quantity-input'),
		trigger: ['change'],
	},

	{
		els: () => [...document.querySelectorAll('.ct-ajax-add-to-cart .cart')],
		load: () => import('./add-to-cart-single'),
		trigger: [
			{
				id: 'submit',
				ignoreSubmitter: ['button[name]:not([name="add-to-cart"])'],
			},
		],
	},

	{
		els: '.ct-header-cart > .ct-cart-item, .ajax_add_to_cart, .ct-ajax-add-to-cart',
		load: () => import('./mini-cart'),
		events: ['ct:header:update'],
		trigger: ['hover-with-touch'],
	},

	{
		els: '#woo-cart-panel .qty',
		trigger: ['change'],
		load: () => import('./quantity-update'),
	},

	{
		els: '.product .ct-card-variation-swatches',
		load: () => import('./variation-stock'),
		condition: () =>
			!!document.querySelector('.product .ct-woo-card-stock'),
		trigger: ['hover'],
	},
]
