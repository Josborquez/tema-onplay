/**
 * Onplay theme — JS entrypoint.
 *
 * Convención: todo lo público cuelga de window.onplay.
 * Los features (cart drawer, filters, search, etc.) se registran
 * como sub-namespaces en sus módulos en los Módulos 5+.
 */
(function () {
	"use strict";

	window.onplay = window.onplay || {};

	window.onplay.version = "0.1.0";

	window.onplay.ready = function (fn) {
		if (document.readyState !== "loading") {
			fn();
		} else {
			document.addEventListener("DOMContentLoaded", fn);
		}
	};

	window.onplay.ready(function () {
		document.documentElement.classList.add("onplay-ready");
	});
})();
