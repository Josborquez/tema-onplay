/**
 * M-OP-filtros — Extensión del módulo Filters de main.js para One Piece TCG.
 *
 * Aproximación A: NO duplica estado ni event loop. Engancha al `Filters`
 * existente extendiendo defaultState/read/write/applyUI/buildQuery con 3
 * claves nuevas (`op_color`, `op_type`, `op_alt`). Además agrega:
 *   - Manejo del click en botones aria-pressed para esas claves (el handler
 *     genérico ya cubre checkboxes via `change`, los botones requieren
 *     extender la lista de keys que se aceptan en bindSidebar click).
 *   - Mobile toggle "Filtros (N)" insertado dinámicamente cuando el sidebar
 *     es OP (CA-8).
 *
 * Activación: el archivo se concatena al final de main.js durante el build
 * (ver `package.json` build:js). Si ese script vuelve a ser un copyFileSync
 * simple, esta extensión queda fuera del bundle — habría que inlinear.
 */
(function () {
	"use strict";

	if (!window.onplay || !window.onplay.filters) {
		// Filters no inicializó o el bundle se cargó fuera de orden.
		return;
	}

	var F = window.onplay.filters;
	var OP_KEYS = ["op_color", "op_type", "op_alt"];

	// ── Extender defaultState ───────────────────────────────
	var origDefault = F.defaultState;
	F.defaultState = function () {
		var s = origDefault();
		OP_KEYS.forEach(function (k) { s[k] = []; });
		return s;
	};

	// ── Lectura desde URL (comma-separated) ─────────────────
	var origRead = F.readStateFromURL;
	F.readStateFromURL = function () {
		var s = origRead();
		var p = new URLSearchParams(window.location.search);
		OP_KEYS.forEach(function (k) {
			if (p.has(k)) {
				s[k] = (p.get(k) || "")
					.split(",")
					.map(function (v) { return v.trim(); })
					.filter(Boolean);
			} else {
				s[k] = [];
			}
		});
		return s;
	};

	// ── Escritura en URL (comma-separated) ──────────────────
	var origWrite = F.writeStateToURL;
	F.writeStateToURL = function (push) {
		origWrite(push); // escribe los params Magic
		// Re-leer y agregar los op_*. URLSearchParams permite re-set.
		var url = new URL(window.location.href);
		OP_KEYS.forEach(function (k) {
			var arr = F.state[k] || [];
			if (arr.length) {
				url.searchParams.set(k, arr.join(","));
			} else {
				url.searchParams.delete(k);
			}
		});
		var method = push ? "pushState" : "replaceState";
		window.history[method]({ onplayFilters: true }, "", url.toString());
	};

	// ── Sync de UI desde state (refleja checked/aria-pressed) ──
	var origApply = F.applyStateToUI;
	F.applyStateToUI = function () {
		origApply();
		if (!F.sidebar) return;
		OP_KEYS.forEach(function (k) {
			var nodes = F.sidebar.querySelectorAll("[data-filter='" + k + "']");
			nodes.forEach(function (n) {
				var v = n.getAttribute("data-value");
				var on = (F.state[k] || []).indexOf(v) >= 0;
				n.setAttribute("aria-pressed", on ? "true" : "false");
				n.classList.toggle("is-active", on);
			});
		});
		updateMobileCounter();
	};

	// ── Click handler para botones aria-pressed con data-filter=op_* ──
	// El handler se registra DESPUÉS de Filters.init() (dentro del ready cb
	// más abajo), porque F.sidebar se asigna en init y aquí en el IIFE
	// todavía es null.
	function handleOpClick(e) {
		var btn = e.target.closest && e.target.closest("[data-filter]");
		if (!btn) return;
		if (btn.matches("input")) return;
		var f = btn.getAttribute("data-filter");
		if (OP_KEYS.indexOf(f) < 0) return;
		var v = btn.getAttribute("data-value");
		if (!v) return;

		e.preventDefault();
		e.stopPropagation();

		var arr = F.state[f] || [];
		var idx = arr.indexOf(v);
		if (idx >= 0) arr.splice(idx, 1);
		else arr.push(v);
		F.state[f] = arr;
		F.state.page = 1;
		F.applyStateToUI();
		F.scheduleUpdate ? F.scheduleUpdate() : F.update();
	}

	// ── buildQuery: append op_* al payload AJAX ─────────────
	var origBuild = F.buildQuery;
	F.buildQuery = function () {
		var qs = origBuild();
		var p = new URLSearchParams(qs);
		OP_KEYS.forEach(function (k) {
			(F.state[k] || []).forEach(function (v) {
				p.append(k + "[]", v);
			});
		});
		return p.toString();
	};

	// ── Mobile toggle "Filtros (N)" — CA-8 ──────────────────
	var mobileBtn = null;
	var mobileCounter = null;

	function ensureMobileToggle() {
		var aside = document.querySelector("[data-op-filters]");
		if (!aside) return;
		if (aside.previousElementSibling && aside.previousElementSibling.classList.contains("op-filters-mobile-toggle")) {
			mobileBtn = aside.previousElementSibling;
			mobileCounter = mobileBtn.querySelector(".op-filters-mobile-toggle__count");
			return;
		}

		mobileBtn = document.createElement("button");
		mobileBtn.type = "button";
		mobileBtn.className = "op-filters-mobile-toggle";
		mobileBtn.setAttribute("aria-expanded", "false");
		mobileBtn.setAttribute("aria-controls", "op-filters-aside");
		aside.id = aside.id || "op-filters-aside";
		mobileBtn.innerHTML =
			'<span class="op-filters-mobile-toggle__label">Filtros<span class="op-filters-mobile-toggle__count" data-op-mobile-counter></span></span>' +
			'<span class="op-filters-mobile-toggle__chev" aria-hidden="true">▼</span>';

		// Estado inicial: en mobile el aside está cerrado; en desktop el CSS
		// fuerza visibilidad y oculta el botón. Defaulteamos a closed.
		aside.setAttribute("data-mobile-open", "false");

		aside.parentNode.insertBefore(mobileBtn, aside);
		mobileCounter = mobileBtn.querySelector(".op-filters-mobile-toggle__count");

		mobileBtn.addEventListener("click", function () {
			var open = aside.getAttribute("data-mobile-open") === "true";
			aside.setAttribute("data-mobile-open", open ? "false" : "true");
			mobileBtn.setAttribute("aria-expanded", open ? "false" : "true");
		});
	}

	function updateMobileCounter() {
		if (!mobileCounter) return;
		var total = 0;
		OP_KEYS.forEach(function (k) {
			total += (F.state[k] || []).length;
		});
		mobileCounter.textContent = total > 0 ? "(" + total + ")" : "";
	}

	// Init — corre DESPUÉS de Filters.init() porque registramos este ready
	// callback después en orden de declaración.
	window.onplay.ready(function () {
		// Sidebar OP detectado → registrar el listener click + montar toggle.
		var aside = document.querySelector("[data-op-filters]");
		if (!aside) return;
		if (F.sidebar) {
			F.sidebar.addEventListener("click", handleOpClick);
		}
		ensureMobileToggle();
		if (F.state) {
			F.applyStateToUI();
		}
	});
})();
