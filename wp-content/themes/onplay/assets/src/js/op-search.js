/**
 * M-OP-buscador — Extensión del módulo Search del autocomplete del header.
 *
 * Aproximación A (consistente con M-OP-filtros): NO duplica fetch ni event
 * loop. Hace monkey-patch sobre `Search.fetch` y `Search.render` del módulo
 * existente en main.js para:
 *   1. Leer `data-tcg` del form y serializar `&tcg=op` cuando aplica.
 *   2. Renderizar items con context=op de forma diferenciada (set_code
 *      OP, badge alt art).
 *   3. Mostrar empty state OP con CTA "Buscar en todo el sitio" que
 *      re-fetchea sin tcg=op.
 *
 * Activación: archivo concatenado al final de main.js durante el build (ver
 * package.json build:js). Si Search no se inicializó (form ausente), el
 * patch es no-op gracias al guard inicial.
 */
(function () {
	"use strict";

	if (!window.onplay || !window.onplay.search) {
		return;
	}

	var S = window.onplay.search;

	// ── Helpers ─────────────────────────────────────────────
	function getCtx() {
		if (!S.form) return "global";
		return S.form.getAttribute("data-tcg") === "op" ? "op" : "global";
	}

	function escapeHTML(s) {
		return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
			return ({
				"&": "&amp;",
				"<": "&lt;",
				">": "&gt;",
				'"': "&quot;",
				"'": "&#39;",
			})[c];
		});
	}

	// ── Fetch: añadir &tcg=op cuando contexto OP ────────────
	var origFetch = S.fetch;
	S.fetch = function (q) {
		if (S.controller) S.controller.abort();
		S.controller = new AbortController();

		var url =
			window.onplaySearch.ajaxUrl +
			"?action=onplay_search&nonce=" +
			encodeURIComponent(window.onplaySearch.nonce) +
			"&q=" +
			encodeURIComponent(q);

		var ctx = getCtx();
		if (ctx === "op") {
			url += "&tcg=op";
		}

		// Trackeamos el contexto que usamos en este fetch para que render
		// pueda diferenciar al recibir la respuesta. Magic ignora esto.
		S._lastFetchCtx = ctx;
		// Para soportar el "Buscar en todo el sitio" del empty state OP:
		// guardamos la query original también.
		S._lastQuery = q;

		fetch(url, {
			method: "GET",
			credentials: "same-origin",
			headers: { "X-Requested-With": "XMLHttpRequest" },
			signal: S.controller.signal,
		})
			.then(function (res) {
				if (!res.ok) throw new Error("HTTP " + res.status);
				return res.json();
			})
			.then(function (data) {
				if (!data || !data.success) {
					S.items = [];
				} else {
					S.items = data.data.results || [];
					if (data.data.context) {
						S._lastResponseCtx = data.data.context;
					}
				}
				S.activeIndex = -1;
				S.render();
				S.show();
			})
			.catch(function (err) {
				if (err.name === "AbortError") return;
				console.error("[onplay] search failed", err);
				S.items = [];
				S.render();
			});
	};

	// ── Render: branch para items context=op + empty state OP ──
	var origRender = S.render;
	S.render = function () {
		var ctx = S._lastResponseCtx || S._lastFetchCtx || "global";

		// Empty state OP: solo si fetch fue tcg=op y no hay resultados.
		if (S.items.length === 0 && ctx === "op") {
			renderOpEmptyState();
			return;
		}

		// Si la respuesta fue OP y hay items, render diferenciado.
		if (ctx === "op" && S.items.length > 0) {
			renderOpResults();
			return;
		}

		// Magic / global / sin contexto: comportamiento original M5.
		origRender.call(S);
	};

	function renderOpEmptyState() {
		var i18n = (window.onplaySearch && window.onplaySearch.i18n) || {};
		var noResultsLabel = i18n.opNoResults || "No encontramos cartas en One Piece con";
		var fallbackLabel = i18n.opSearchAll || "Buscar en todo el sitio";
		var query = S._lastQuery || (S.input && S.input.value.trim()) || "";

		S.results.innerHTML =
			'<div class="search-empty search-empty--op">' +
			"<div>" +
			escapeHTML(noResultsLabel) +
			" <strong>“" +
			escapeHTML(query) +
			"”</strong></div>" +
			'<button type="button" class="search-empty--op__action" data-onplay-search-fallback>' +
			escapeHTML(fallbackLabel) +
			"</button>" +
			"</div>";

		var fallbackBtn = S.results.querySelector("[data-onplay-search-fallback]");
		if (fallbackBtn) {
			fallbackBtn.addEventListener("click", function () {
				// Re-fetch sin contexto OP — overrideamos getCtx temporalmente.
				S._forceGlobalFetch = true;
				doGlobalRefetch(query);
				S._forceGlobalFetch = false;
			});
		}
	}

	function doGlobalRefetch(q) {
		// Replica del cuerpo de S.fetch() pero sin agregar tcg=op aunque el
		// form lo declare. Útil solo para el CTA "Buscar en todo el sitio".
		if (S.controller) S.controller.abort();
		S.controller = new AbortController();
		var url =
			window.onplaySearch.ajaxUrl +
			"?action=onplay_search&nonce=" +
			encodeURIComponent(window.onplaySearch.nonce) +
			"&q=" +
			encodeURIComponent(q);
		S._lastFetchCtx = "global";
		S._lastQuery = q;
		fetch(url, {
			method: "GET",
			credentials: "same-origin",
			headers: { "X-Requested-With": "XMLHttpRequest" },
			signal: S.controller.signal,
		})
			.then(function (res) { return res.json(); })
			.then(function (data) {
				if (!data || !data.success) {
					S.items = [];
				} else {
					S.items = data.data.results || [];
					S._lastResponseCtx = data.data.context || "global";
				}
				S.activeIndex = -1;
				S.render();
				S.show();
			})
			.catch(function (err) {
				if (err.name === "AbortError") return;
				console.error("[onplay] search fallback failed", err);
			});
	}

	function renderOpResults() {
		var fromLabel = (window.onplaySearch && window.onplaySearch.i18n && window.onplaySearch.i18n.fromLabel) || "Desde";
		var altLabel = (window.onplaySearch && window.onplaySearch.i18n && window.onplaySearch.i18n.opAltArt) || "Alt Art";

		var html = S.items
			.map(function (r, i) {
				var thumb = r.thumb
					? '<img src="' + escapeHTML(r.thumb) + '" alt="" loading="lazy" />'
					: "";
				var altBadge = r.is_alt_art
					? '<span class="search-result__alt-badge">' + escapeHTML(altLabel) + "</span>"
					: "";
				var setLine = escapeHTML(r.set_code || r.set_name || "") + " · " + escapeHTML(r.card_number || r.print_key);
				return (
					'<a class="search-result" id="search-result-' +
					i +
					'" href="' +
					escapeHTML(r.permalink) +
					'" role="option">' +
					'<span class="search-result__thumb">' +
					thumb +
					"</span>" +
					'<span class="search-result__body">' +
					'<span class="search-result__name">' +
					escapeHTML(r.name) +
					altBadge +
					"</span>" +
					'<span class="search-result__set">' +
					setLine +
					"</span>" +
					"</span>" +
					'<span class="search-result__price">' +
					escapeHTML(fromLabel) +
					" " +
					escapeHTML(r.min_price_formatted) +
					"</span>" +
					"</a>"
				);
			})
			.join("");
		S.results.innerHTML = html;
	}
})();
