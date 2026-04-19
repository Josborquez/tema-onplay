/**
 * Onplay theme — JS entrypoint.
 *
 * Convención: todo lo público cuelga de window.onplay.
 * Sub-namespaces:
 *   - onplay.cart        → drawer + add-to-cart AJAX (Módulo 4)
 *   - onplay.variantTable → handlers de la tabla de variantes (Módulo 4)
 */
(function () {
	"use strict";

	window.onplay = window.onplay || {};
	window.onplay.version = "0.2.0";

	window.onplay.ready = function (fn) {
		if (document.readyState !== "loading") {
			fn();
		} else {
			document.addEventListener("DOMContentLoaded", fn);
		}
	};

	// ──────────────────────────────────────────────────────────
	// Cart drawer + AJAX add-to-cart (Módulo 4 — versión mínima)
	// ──────────────────────────────────────────────────────────
	var Cart = {
		drawer: null,
		previousFocus: null,

		init: function () {
			this.drawer = document.getElementById("onplay-cart-drawer");
			if (!this.drawer) return;

			// Cerrar drawer (backdrop, botón close, ESC).
			document.addEventListener("click", function (e) {
				var target = e.target;
				if (target.closest && target.closest("[data-onplay-cart-close]")) {
					Cart.close();
				}
			});

			document.addEventListener("keydown", function (e) {
				if (e.key === "Escape" && Cart.isOpen()) {
					Cart.close();
				}
			});

			// Disparado por WC al refrescar fragments.
			document.body.addEventListener("wc_fragments_refreshed", function () {
				// no-op: fragments ya se aplicaron, contador actualizado.
			});
		},

		isOpen: function () {
			return this.drawer && this.drawer.getAttribute("aria-hidden") === "false";
		},

		open: function () {
			if (!this.drawer) return;
			this.previousFocus = document.activeElement;
			this.drawer.removeAttribute("hidden");
			this.drawer.setAttribute("aria-hidden", "false");
			document.documentElement.classList.add("is-cart-open");
			var closeBtn = this.drawer.querySelector(".cart-drawer__close");
			if (closeBtn) closeBtn.focus();
		},

		close: function () {
			if (!this.drawer) return;
			this.drawer.setAttribute("aria-hidden", "true");
			this.drawer.setAttribute("hidden", "");
			document.documentElement.classList.remove("is-cart-open");
			if (this.previousFocus && typeof this.previousFocus.focus === "function") {
				this.previousFocus.focus();
			}
		},

		/**
		 * Aplica fragments devueltos por wc-ajax=add_to_cart.
		 * @param {Object} fragments {selector: html}
		 */
		applyFragments: function (fragments) {
			if (!fragments) return;
			Object.keys(fragments).forEach(function (selector) {
				var nodes = document.querySelectorAll(selector);
				if (!nodes.length) return;
				var html = fragments[selector];
				nodes.forEach(function (node) {
					var temp = document.createElement("div");
					temp.innerHTML = html;
					var fresh = temp.firstElementChild;
					if (!fresh) {
						node.outerHTML = html;
					} else {
						node.replaceWith(fresh);
					}
				});
			});
		},

		/**
		 * Llama al endpoint wc-ajax=add_to_cart.
		 * @param {Object} opts {productId, quantity}
		 * @returns {Promise}
		 */
		add: function (opts) {
			if (!window.onplayWC || !window.onplayWC.ajaxUrl) {
				return Promise.reject(new Error("WC AJAX no configurado"));
			}
			var url = window.onplayWC.ajaxUrl.replace("%%endpoint%%", "add_to_cart");
			var body = new FormData();
			body.append("product_id", String(opts.productId));
			body.append("quantity", String(opts.quantity || 1));

			return fetch(url, {
				method: "POST",
				credentials: "same-origin",
				body: body,
				headers: { "X-Requested-With": "XMLHttpRequest" },
			})
				.then(function (res) {
					if (!res.ok) throw new Error("HTTP " + res.status);
					return res.json();
				})
				.then(function (data) {
					if (data && data.error) {
						throw new Error(data.error_message || "WC error");
					}
					if (data && data.fragments) {
						Cart.applyFragments(data.fragments);
						// Notificar a quien escuche (compat con listeners de WC).
						document.body.dispatchEvent(
							new CustomEvent("wc_fragments_refreshed")
						);
					}
					return data;
				});
		},
	};

	// ──────────────────────────────────────────────────────────
	// Variant table — handlers de "Agregar" + filtros chips
	// ──────────────────────────────────────────────────────────
	var VariantTable = {
		// Estado de filtros: condiciones es Set<string>; foil es 'all'|'regular'|'foil'.
		filters: { conditions: null, foil: "all" },

		init: function () {
			VariantTable.filters.conditions = new Set();

			document.addEventListener("click", function (e) {
				var addBtn = e.target.closest && e.target.closest(".variant-row__add");
				if (addBtn) {
					e.preventDefault();
					VariantTable.handleAdd(addBtn);
					return;
				}

				var chip = e.target.closest && e.target.closest(".variant-table__filters .chip");
				if (chip && !chip.disabled) {
					e.preventDefault();
					VariantTable.handleChipClick(chip);
				}
			});
		},

		handleChipClick: function (chip) {
			var cond = chip.getAttribute("data-filter-condition");
			var foil = chip.getAttribute("data-filter-foil");

			if (cond) {
				// Multi-select: toggle del chip.
				var pressed = chip.getAttribute("aria-pressed") === "true";
				chip.setAttribute("aria-pressed", pressed ? "false" : "true");
				chip.classList.toggle("is-active", !pressed);
				if (pressed) {
					VariantTable.filters.conditions.delete(cond);
				} else {
					VariantTable.filters.conditions.add(cond);
				}
			} else if (foil) {
				// Radio: solo uno activo en el grupo.
				var group = chip.parentNode;
				var siblings = group.querySelectorAll(".chip");
				siblings.forEach(function (c) {
					c.classList.remove("is-active");
					c.setAttribute("aria-pressed", "false");
				});
				chip.classList.add("is-active");
				chip.setAttribute("aria-pressed", "true");
				VariantTable.filters.foil = foil;
			}

			VariantTable.applyFilters();
		},

		applyFilters: function () {
			var tables = document.querySelectorAll("[data-variant-table]");
			tables.forEach(function (table) {
				var rows = table.querySelectorAll(".variant-row");
				var visible = 0;
				rows.forEach(function (row) {
					var rowCond = row.getAttribute("data-condition") || "";
					var rowFoil = row.getAttribute("data-foil") || "regular";

					var condOK =
						VariantTable.filters.conditions.size === 0 ||
						VariantTable.filters.conditions.has(rowCond);
					var foilOK =
						VariantTable.filters.foil === "all" ||
						VariantTable.filters.foil === rowFoil;

					var show = condOK && foilOK;
					row.style.display = show ? "" : "none";
					if (show) visible++;
				});

				var emptyMsg = table.parentNode.querySelector("[data-variant-table-empty]");
				if (emptyMsg) {
					if (visible === 0) {
						emptyMsg.removeAttribute("hidden");
					} else {
						emptyMsg.setAttribute("hidden", "");
					}
				}
			});
		},

		handleAdd: function (btn) {
			if (btn.disabled) return;

			var row = btn.closest(".variant-row");
			var qtyInput = row ? row.querySelector(".variant-row__qty") : null;
			var qty = qtyInput ? parseInt(qtyInput.value, 10) : 1;
			if (isNaN(qty) || qty < 1) qty = 1;

			var max = qtyInput ? parseInt(qtyInput.getAttribute("max"), 10) : 0;
			if (max > 0 && qty > max) qty = max;

			var productId = parseInt(btn.getAttribute("data-product-id"), 10);
			if (!productId) return;

			var originalHTML = btn.innerHTML;
			btn.disabled = true;
			btn.classList.add("is-loading");
			btn.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span>';

			Cart.add({ productId: productId, quantity: qty })
				.then(function () {
					btn.classList.remove("is-loading");
					btn.classList.add("is-ok");
					btn.innerHTML = "✓ " + (window.onplayWC ? window.onplayWC.i18n.added : "Agregado");
					Cart.open();
					setTimeout(function () {
						btn.classList.remove("is-ok");
						btn.disabled = false;
						btn.innerHTML = originalHTML;
					}, 1600);
				})
				.catch(function (err) {
					console.error("[onplay] add to cart failed", err);
					btn.classList.remove("is-loading");
					btn.classList.add("is-error");
					btn.innerHTML =
						(window.onplayWC ? window.onplayWC.i18n.addError : "Error");
					setTimeout(function () {
						btn.classList.remove("is-error");
						btn.disabled = false;
						btn.innerHTML = originalHTML;
					}, 2400);
				});
		},
	};

	// ──────────────────────────────────────────────────────────
	// Search — autocomplete del header
	// ──────────────────────────────────────────────────────────
	var Search = {
		form: null,
		input: null,
		results: null,
		controller: null,
		debounceTimer: null,
		activeIndex: -1,
		items: [],

		init: function () {
			Search.form = document.querySelector("[data-onplay-search]");
			if (!Search.form) return;
			Search.input = Search.form.querySelector("[data-onplay-search-input]");
			Search.results = Search.form.querySelector("[data-onplay-search-results]");
			if (!Search.input || !Search.results) return;
			if (!window.onplaySearch || !window.onplaySearch.ajaxUrl) return;

			Search.input.addEventListener("input", Search.onInput);
			Search.input.addEventListener("keydown", Search.onKeydown);
			Search.input.addEventListener("focus", function () {
				if (Search.items.length > 0) Search.show();
			});

			document.addEventListener("click", function (e) {
				if (!Search.form.contains(e.target)) Search.hide();
			});

			// Submit nativo: si hay un item activo, ir a su permalink.
			Search.form.addEventListener("submit", function (e) {
				if (Search.activeIndex >= 0 && Search.items[Search.activeIndex]) {
					e.preventDefault();
					window.location.href = Search.items[Search.activeIndex].permalink;
				}
				// si no, deja submit nativo a /?s=...
			});
		},

		onInput: function () {
			clearTimeout(Search.debounceTimer);
			var q = Search.input.value.trim();
			if (q.length < 2) {
				Search.items = [];
				Search.render();
				Search.hide();
				return;
			}
			Search.debounceTimer = setTimeout(function () {
				Search.fetch(q);
			}, 200);
		},

		onKeydown: function (e) {
			if (e.key === "Escape") {
				Search.hide();
				return;
			}
			if (Search.items.length === 0) return;
			if (e.key === "ArrowDown") {
				e.preventDefault();
				Search.move(1);
			} else if (e.key === "ArrowUp") {
				e.preventDefault();
				Search.move(-1);
			}
		},

		move: function (delta) {
			Search.activeIndex += delta;
			if (Search.activeIndex < 0) Search.activeIndex = Search.items.length - 1;
			if (Search.activeIndex >= Search.items.length) Search.activeIndex = 0;
			Search.highlight();
		},

		highlight: function () {
			var nodes = Search.results.querySelectorAll(".search-result");
			nodes.forEach(function (n, i) {
				var active = i === Search.activeIndex;
				n.classList.toggle("is-active", active);
				if (active) {
					Search.input.setAttribute("aria-activedescendant", n.id);
					n.scrollIntoView({ block: "nearest" });
				}
			});
		},

		fetch: function (q) {
			if (Search.controller) Search.controller.abort();
			Search.controller = new AbortController();

			var url =
				window.onplaySearch.ajaxUrl +
				"?action=onplay_search&nonce=" +
				encodeURIComponent(window.onplaySearch.nonce) +
				"&q=" +
				encodeURIComponent(q);

			fetch(url, {
				method: "GET",
				credentials: "same-origin",
				headers: { "X-Requested-With": "XMLHttpRequest" },
				signal: Search.controller.signal,
			})
				.then(function (res) {
					if (!res.ok) throw new Error("HTTP " + res.status);
					return res.json();
				})
				.then(function (data) {
					if (!data || !data.success) {
						Search.items = [];
					} else {
						Search.items = data.data.results || [];
					}
					Search.activeIndex = -1;
					Search.render();
					Search.show();
				})
				.catch(function (err) {
					if (err.name === "AbortError") return;
					console.error("[onplay] search failed", err);
					Search.items = [];
					Search.render();
				});
		},

		render: function () {
			if (Search.items.length === 0) {
				Search.results.innerHTML =
					'<div class="search-empty">' +
					(window.onplaySearch.i18n.noResults || "Sin resultados") +
					"</div>";
				return;
			}
			var fromLabel = window.onplaySearch.i18n.fromLabel || "Desde";
			var html = Search.items
				.map(function (r, i) {
					var thumb = r.thumb
						? '<img src="' + r.thumb + '" alt="" loading="lazy" />'
						: "";
					return (
						'<a class="search-result" id="search-result-' +
						i +
						'" href="' +
						r.permalink +
						'" role="option">' +
						'<span class="search-result__thumb">' +
						thumb +
						"</span>" +
						'<span class="search-result__body">' +
						'<span class="search-result__name">' +
						escapeHTML(r.name) +
						"</span>" +
						'<span class="search-result__set">' +
						escapeHTML(r.set_name || r.set_code || "") +
						" · " +
						escapeHTML(r.print_key) +
						"</span>" +
						"</span>" +
						'<span class="search-result__price">' +
						fromLabel +
						" " +
						escapeHTML(r.min_price_formatted) +
						"</span>" +
						"</a>"
					);
				})
				.join("");
			Search.results.innerHTML = html;
		},

		show: function () {
			Search.results.removeAttribute("hidden");
			Search.input.setAttribute("aria-expanded", "true");
		},

		hide: function () {
			Search.results.setAttribute("hidden", "");
			Search.input.setAttribute("aria-expanded", "false");
			Search.input.removeAttribute("aria-activedescendant");
			Search.activeIndex = -1;
		},
	};

	function escapeHTML(s) {
		return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
			return (
				{ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]
			);
		});
	}

	window.onplay.cart = Cart;
	window.onplay.variantTable = VariantTable;
	window.onplay.search = Search;

	window.onplay.ready(function () {
		document.documentElement.classList.add("onplay-ready");
		Cart.init();
		VariantTable.init();
		Search.init();

		// Click en el botón "Carrito" del header → abre drawer (sin navegar).
		document.addEventListener("click", function (e) {
			var link = e.target.closest && e.target.closest("[data-onplay-cart-trigger]");
			if (!link) return;
			// cmd/ctrl/shift-click o middle-click: dejar pasar.
			if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
			e.preventDefault();
			Cart.open();
		});
	});
})();
