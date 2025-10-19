import {onLCP} from 'web-vitals/attribution';
import unique from 'unique-selector';
import getXPath from 'get-xpath';

export class SmushLCPDetector {
	onLCP(data) {
		const element = data?.entries[0]?.element;
		const imageUrl = data?.attribution?.url;
		if (!element || !imageUrl) {
			return;
		}
		const attributionSelector = data?.attribution?.element || '';
		const selector = attributionSelector && document.querySelectorAll(attributionSelector).length === 1
			? attributionSelector
			: unique(element);
		const xpath = getXPath(element, {ignoreId: true});
		const body = {
			url: window.location.href,
			data: JSON.stringify({
				selector: selector,
				selector_xpath: xpath,
				selector_id: element?.id,
				selector_class: element?.className,
				image_url: imageUrl,
				background_data: this.getBackgroundDataForElement(element),
			}),
			nonce: smush_detector.nonce,
			is_mobile: smush_detector.is_mobile,
			data_store: JSON.stringify(smush_detector.data_store),
			previous_data_version: smush_detector.previous_data_version,
			previous_data_hash: smush_detector.previous_data_hash,
		};

		const xhr = new XMLHttpRequest();
		xhr.open('POST', smush_detector.ajax_url + '?action=smush_handle_lcp_data', true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		const urlEncodedData = Object.keys(body)
			.map(key => encodeURIComponent(key) + "=" + encodeURIComponent(body[key]))
			.join("&");
		xhr.send(urlEncodedData);
	}

	getBackgroundDataForElement(element) {
		const computedStyle = window.getComputedStyle(element, null);
		const backgroundProps = [
			computedStyle.getPropertyValue("background-image"),
			getComputedStyle(element, ":after").getPropertyValue("background-image"),
			getComputedStyle(element, ":before").getPropertyValue("background-image")
		].filter((prop) => prop !== "none");
		if (backgroundProps.length === 0) {
			return null;
		}
		return this.getBackgroundDataForPropertyValue(backgroundProps[0]);
	}

	getBackgroundDataForPropertyValue(fullBackgroundProp) {
		let type = "background-image";
		if (fullBackgroundProp.includes("image-set(")) {
			type = "background-image-set";
		}
		if (!fullBackgroundProp || fullBackgroundProp === "" || fullBackgroundProp.includes("data:image")) {
			return null;
		}
		// IMPORTANT: the following regex is a copy of the one in the PHP function Parser::get_image_urls. Remember to keep them synced.
		const cssBackgroundUrlRegex = /((?:https?:\/|\.+)?\/[^'",\s()]+\.(jpe?g|png|gif|webp|svg|avif)(?:\?[^\s'",?)]+)?)\b/ig;
		const matches = [...fullBackgroundProp.matchAll(cssBackgroundUrlRegex)];
		let backgroundSet = matches.map((match) => match[1].trim());
		if (backgroundSet.length <= 0) {
			return null;
		}
		if (backgroundSet.length > 0) {
			return {
				type: type,
				property: fullBackgroundProp,
				urls: backgroundSet,
			};
		} else {
			return null;
		}
	}
}

(function () {
	if (!document?.documentElement?.scrollTop) {
		onLCP(function (data) {
			const detector = new SmushLCPDetector();
			detector.onLCP(data);
		});
	}
})();
