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
	// Cart update — stepper (+/−/set) + remove (Módulo 7)
	// Endpoint propio admin-ajax.php?action=onplay_cart_update
	// Delegación global: cualquier [data-cart-action] dentro de un
	// [data-cart-row] con [data-cart-item-key] funciona, así que sirve
	// para el drawer y para la página /carrito/ sin bindings separados.
	// ──────────────────────────────────────────────────────────
	var CartUpdate = {
		init: function () {
			if (!window.onplayCart || !window.onplayCart.ajaxUrl) return;

			document.addEventListener("click", function (e) {
				var btn = e.target.closest && e.target.closest("[data-cart-action]");
				if (!btn) return;
				var row = btn.closest("[data-cart-row]");
				if (!row) return;
				var action = btn.getAttribute("data-cart-action");
				if (!action) return;
				e.preventDefault();

				if (action === "remove") {
					CartUpdate.send(row, "remove");
					return;
				}
				if (action === "increase" || action === "decrease") {
					CartUpdate.send(row, action);
					return;
				}
			});

			// Cambio directo en el input: action=set (blur o Enter).
			document.addEventListener(
				"change",
				function (e) {
					var input = e.target;
					if (!input || !input.matches || !input.matches("[data-cart-qty]")) return;
					var row = input.closest("[data-cart-row]");
					if (!row) return;
					var qty = parseInt(input.value, 10);
					if (isNaN(qty) || qty < 0) qty = 0;
					CartUpdate.send(row, "set", qty);
				},
				true
			);
		},

		/**
		 * @param {HTMLElement} row    elemento con data-cart-item-key
		 * @param {string}      action increase|decrease|set|remove
		 * @param {number}      [qty]  solo para 'set'
		 */
		send: function (row, action, qty) {
			var key = row.getAttribute("data-cart-item-key");
			if (!key) return;

			row.classList.add("is-updating");
			CartUpdate.disableRow(row, true);

			var body = new FormData();
			body.append("action", "onplay_cart_update");
			body.append("nonce", window.onplayCart.nonce);
			body.append("cart_action", action);
			body.append("cart_item_key", key);
			if (action === "set") body.append("qty", String(qty || 0));

			fetch(window.onplayCart.ajaxUrl, {
				method: "POST",
				credentials: "same-origin",
				body: body,
				headers: { "X-Requested-With": "XMLHttpRequest" },
			})
				.then(function (res) {
					return res.json().then(function (data) {
						return { ok: res.ok, data: data };
					});
				})
				.then(function (r) {
					if (!r.ok || !r.data || !r.data.success) {
						var msg =
							(r.data && r.data.data && r.data.data.message) ||
							window.onplayCart.i18n.updateError;
						CartUpdate.showRowError(row, msg);
						return;
					}
					var payload = r.data.data || {};
					if (payload.fragments) {
						Cart.applyFragments(payload.fragments);
					}
					if (payload.cart) {
						CartUpdate.applyCartPayload(payload.cart);
					}
					document.body.dispatchEvent(
						new CustomEvent("onplay_cart_updated", { detail: payload })
					);
				})
				.catch(function (err) {
					console.error("[onplay] cart update failed", err);
					CartUpdate.showRowError(row, window.onplayCart.i18n.updateError);
				})
				.finally(function () {
					row.classList.remove("is-updating");
					CartUpdate.disableRow(row, false);
				});
		},

		/**
		 * Aplica el payload plano a filas que NO fueron reemplazadas por fragments
		 * (ej. filas de la página /carrito/ que no matchean selectores del drawer).
		 */
		applyCartPayload: function (cart) {
			if (!cart || !cart.items) return;
			var rows = document.querySelectorAll("[data-cart-row]");
			rows.forEach(function (row) {
				var key = row.getAttribute("data-cart-item-key");
				if (!key) return;
				var entry = cart.items[key];
				if (!entry) {
					// Item removido → sacar la fila si sigue en DOM.
					if (row.parentNode) row.parentNode.removeChild(row);
					return;
				}
				var qtyInput = row.querySelector("[data-cart-qty]");
				if (qtyInput && parseInt(qtyInput.value, 10) !== entry.qty) {
					qtyInput.value = String(entry.qty);
				}
				var sub = row.querySelector("[data-cart-line-sub]");
				if (sub) sub.textContent = entry.subtotal;
			});

			// Subtotal global (header sticky en /carrito/).
			var globalSub = document.querySelector("[data-cart-global-sub]");
			if (globalSub) globalSub.textContent = cart.subtotal;
			var globalCount = document.querySelector("[data-cart-global-count]");
			if (globalCount) globalCount.textContent = String(cart.item_count);
		},

		disableRow: function (row, on) {
			var ctrls = row.querySelectorAll(
				"[data-cart-action],[data-cart-qty]"
			);
			ctrls.forEach(function (el) {
				el.disabled = !!on;
			});
		},

		showRowError: function (row, msg) {
			var existing = row.querySelector(".cart-row__error");
			if (existing) existing.remove();
			var note = document.createElement("div");
			note.className = "cart-row__error";
			note.textContent = msg;
			note.setAttribute("role", "alert");
			row.appendChild(note);
			setTimeout(function () {
				if (note.parentNode) note.parentNode.removeChild(note);
			}, 4000);
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

			// Submit: si hay un item activo del dropdown, ir a su permalink.
			// Si no, dejamos que el submit nativo llegue a /tienda/?q=... (action del form).
			Search.form.addEventListener("submit", function (e) {
				if (Search.activeIndex >= 0 && Search.items[Search.activeIndex]) {
					e.preventDefault();
					window.location.href = Search.items[Search.activeIndex].permalink;
				}
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

	// ──────────────────────────────────────────────────────────
	// Filters — Módulo 6: sidebar facetado del listado
	// ──────────────────────────────────────────────────────────
	var Filters = {
		// Estado en memoria. Se sincroniza con URLSearchParams en cada cambio.
		state: null,
		controller: null,
		debounceTimer: null,
		debounceMs: 250,

		root: null,       // .shop-results (host)
		sidebar: null,    // .shop-sidebar
		grid: null,       // [data-shop-grid]
		pagination: null, // [data-shop-pagination]
		chips: null,      // [data-shop-chips]
		count: null,      // [data-shop-count]
		loading: null,    // [data-shop-loading]
		sort: null,       // [data-shop-sort]

		init: function () {
			Filters.root = document.querySelector("[data-shop-results]");
			Filters.sidebar = document.querySelector("[data-filters-sidebar]");
			if (!Filters.root || !Filters.sidebar) return;
			if (!window.onplayFilters || !window.onplayFilters.ajaxUrl) return;

			Filters.grid = Filters.root.querySelector("[data-shop-grid]");
			Filters.pagination = Filters.root.querySelector("[data-shop-pagination]");
			Filters.chips = Filters.root.querySelector("[data-shop-chips]");
			Filters.count = document.querySelector("[data-shop-count]");
			Filters.loading = Filters.root.querySelector("[data-shop-loading]");
			Filters.sort = document.querySelector("[data-shop-sort]");

			Filters.state = Filters.readStateFromURL();
			Filters.applyStateToUI();

			Filters.bindSidebar();
			Filters.bindSort();
			Filters.bindPagination();
			Filters.bindChips();
			Filters.bindGroupHeaders();
			Filters.bindSetSearch();

			window.addEventListener("popstate", Filters.onPopState);
		},

		// ── Estado ↔ URL ──────────────────────────────
		defaultState: function () {
			return {
				set: [],
				color: [],
				rarity: [],
				condition: [],
				lang: [],
				foil: "all",
				price_min: 0,
				price_max: 500000,
				in_stock: true,
				sort: "price-desc",
				page: 1,
			};
		},

		readStateFromURL: function () {
			var s = Filters.defaultState();
			var p = new URLSearchParams(window.location.search);
			["set", "color", "rarity", "condition", "lang"].forEach(function (k) {
				if (p.has(k)) s[k] = p.getAll(k);
				else if (p.has(k + "[]")) s[k] = p.getAll(k + "[]");
			});
			if (p.has("foil")) s.foil = p.get("foil");
			if (p.has("price_min")) s.price_min = parseInt(p.get("price_min"), 10) || 0;
			if (p.has("price_max")) s.price_max = parseInt(p.get("price_max"), 10) || 0;
			if (p.has("in_stock")) s.in_stock = p.get("in_stock") === "1";
			if (p.has("sort")) s.sort = p.get("sort");
			if (p.has("page")) s.page = Math.max(1, parseInt(p.get("page"), 10) || 1);
			return s;
		},

		writeStateToURL: function (push) {
			var p = new URLSearchParams();
			["set", "color", "rarity", "condition", "lang"].forEach(function (k) {
				Filters.state[k].forEach(function (v) { p.append(k, v); });
			});
			if (Filters.state.foil !== "all") p.set("foil", Filters.state.foil);
			if (Filters.state.price_min > 0) p.set("price_min", String(Filters.state.price_min));
			if (Filters.state.price_max > 0 && Filters.state.price_max !== 500000) {
				p.set("price_max", String(Filters.state.price_max));
			}
			if (!Filters.state.in_stock) p.set("in_stock", "0");
			if (Filters.state.sort && Filters.state.sort !== "price-desc") p.set("sort", Filters.state.sort);
			if (Filters.state.page > 1) p.set("page", String(Filters.state.page));

			var qs = p.toString();
			var url = window.location.pathname + (qs ? "?" + qs : "");
			if (push) {
				window.history.pushState({ onplayFilters: true }, "", url);
			} else {
				window.history.replaceState({ onplayFilters: true }, "", url);
			}
		},

		applyStateToUI: function () {
			// Checkboxes y multi-buttons.
			var groups = [
				{ key: "set", attr: "input[data-filter='set']", type: "checkbox" },
				{ key: "rarity", attr: "input[data-filter='rarity']", type: "checkbox" },
				{ key: "color", attr: "[data-filter='color']", type: "aria" },
				{ key: "condition", attr: "[data-filter='condition']", type: "aria" },
				{ key: "lang", attr: "[data-filter='lang']", type: "aria" },
			];
			groups.forEach(function (g) {
				var nodes = Filters.sidebar.querySelectorAll(g.attr);
				nodes.forEach(function (n) {
					var v = n.value || n.getAttribute("data-value");
					var on = Filters.state[g.key].indexOf(v) >= 0;
					if (g.type === "checkbox") {
						n.checked = on;
					} else {
						n.setAttribute("aria-pressed", on ? "true" : "false");
					}
				});
			});

			// Foil tri-state.
			var foilBtns = Filters.sidebar.querySelectorAll("[data-filter='foil']");
			foilBtns.forEach(function (b) {
				var on = b.getAttribute("data-value") === Filters.state.foil;
				b.classList.toggle("is-active", on);
				b.setAttribute("aria-pressed", on ? "true" : "false");
				b.setAttribute("aria-checked", on ? "true" : "false");
			});

			// Price.
			var pmin = Filters.sidebar.querySelector("[data-filter='price_min']");
			var pmax = Filters.sidebar.querySelector("[data-filter='price_max']");
			var slider = Filters.sidebar.querySelector("[data-price-slider]");
			if (pmin) pmin.value = Filters.state.price_min || 0;
			if (pmax) pmax.value = Filters.state.price_max || 500000;
			if (slider) slider.value = Filters.state.price_max || 500000;

			// Stock toggle.
			var stockChk = Filters.sidebar.querySelector("[data-filter='in_stock']");
			if (stockChk) stockChk.checked = !!Filters.state.in_stock;

			// Sort.
			if (Filters.sort) Filters.sort.value = Filters.state.sort;
		},

		// ── Bindings ──────────────────────────────────
		bindSidebar: function () {
			Filters.sidebar.addEventListener("change", function (e) {
				var t = e.target;
				if (!t.matches) return;

				if (t.matches("input[type='checkbox'][data-filter]")) {
					var f = t.getAttribute("data-filter");
					var v = t.value;
					if (f === "in_stock") {
						Filters.state.in_stock = t.checked;
					} else {
						Filters.toggleArray(f, v, t.checked);
					}
					Filters.state.page = 1;
					Filters.scheduleUpdate();
				}

				if (t.matches("input[data-filter='price_min'], input[data-filter='price_max']")) {
					var key = t.getAttribute("data-filter");
					Filters.state[key] = Math.max(0, parseInt(t.value, 10) || 0);
					if (key === "price_max") {
						var slider = Filters.sidebar.querySelector("[data-price-slider]");
						if (slider) slider.value = Filters.state.price_max;
					}
					Filters.state.page = 1;
					Filters.scheduleUpdate();
				}
			});

			Filters.sidebar.addEventListener("click", function (e) {
				var btn = e.target.closest && e.target.closest("[data-filter]");
				if (!btn) return;
				if (btn.matches("input")) return;
				if (btn.disabled || btn.classList.contains("is-disabled")) return;

				var f = btn.getAttribute("data-filter");
				var v = btn.getAttribute("data-value");
				if (!f || !v) return;

				e.preventDefault();

				if (f === "foil") {
					Filters.state.foil = v;
				} else if (["color", "condition", "lang"].indexOf(f) >= 0) {
					var pressed = btn.getAttribute("aria-pressed") === "true";
					Filters.toggleArray(f, v, !pressed);
				} else {
					return;
				}
				Filters.state.page = 1;
				Filters.applyStateToUI();
				Filters.scheduleUpdate();
			});

			// Slider price_max — input handler para feedback inmediato.
			var slider = Filters.sidebar.querySelector("[data-price-slider]");
			if (slider) {
				slider.addEventListener("input", function () {
					Filters.state.price_max = parseInt(slider.value, 10) || 0;
					var pmax = Filters.sidebar.querySelector("[data-filter='price_max']");
					if (pmax) pmax.value = Filters.state.price_max;
				});
				slider.addEventListener("change", function () {
					Filters.state.page = 1;
					Filters.scheduleUpdate();
				});
			}
		},

		bindSort: function () {
			if (!Filters.sort) return;
			Filters.sort.addEventListener("change", function () {
				Filters.state.sort = Filters.sort.value;
				Filters.state.page = 1;
				Filters.update();
			});
		},

		bindPagination: function () {
			Filters.root.addEventListener("click", function (e) {
				var btn = e.target.closest && e.target.closest("[data-shop-pagination] [data-page]");
				if (!btn) return;
				e.preventDefault();
				var p = parseInt(btn.getAttribute("data-page"), 10);
				if (!p || p === Filters.state.page) return;
				Filters.state.page = p;
				Filters.update();
				// Scroll al top del grid.
				var top = Filters.grid.getBoundingClientRect().top + window.scrollY - 100;
				window.scrollTo({ top: top, behavior: "smooth" });
			});
		},

		bindChips: function () {
			Filters.root.addEventListener("click", function (e) {
				if (!e.target.closest) return;
				if (e.target.closest("[data-chip-clear]")) {
					Filters.clearAll();
					return;
				}
				var chip = e.target.closest("[data-chip-group]");
				if (!chip) return;
				e.preventDefault();
				var g = chip.getAttribute("data-chip-group");
				var v = chip.getAttribute("data-chip-value");
				if (g === "foil") {
					Filters.state.foil = "all";
				} else {
					Filters.toggleArray(g, v, false);
				}
				Filters.state.page = 1;
				Filters.applyStateToUI();
				Filters.update();
			});
		},

		bindGroupHeaders: function () {
			Filters.sidebar.addEventListener("click", function (e) {
				var head = e.target.closest && e.target.closest(".filter-group__head");
				if (!head) return;
				var open = head.getAttribute("aria-expanded") === "true";
				head.setAttribute("aria-expanded", open ? "false" : "true");
			});
		},

		bindSetSearch: function () {
			var input = Filters.sidebar.querySelector("[data-set-search]");
			var list = Filters.sidebar.querySelector("[data-set-list]");
			if (!input || !list) return;
			input.addEventListener("input", function () {
				var q = input.value.trim().toLowerCase();
				var items = list.querySelectorAll("[data-set-item]");
				items.forEach(function (item) {
					var name = item.getAttribute("data-set-name") || "";
					item.style.display = !q || name.indexOf(q) >= 0 ? "" : "none";
				});
			});
		},

		// ── Helpers ───────────────────────────────────
		toggleArray: function (key, value, on) {
			var arr = Filters.state[key];
			var idx = arr.indexOf(value);
			if (on && idx < 0) arr.push(value);
			if (!on && idx >= 0) arr.splice(idx, 1);
		},

		clearAll: function () {
			Filters.state = Filters.defaultState();
			Filters.applyStateToUI();
			Filters.update();
		},

		scheduleUpdate: function () {
			clearTimeout(Filters.debounceTimer);
			Filters.debounceTimer = setTimeout(Filters.update, Filters.debounceMs);
		},

		// ── Fetch ────────────────────────────────────
		buildQuery: function () {
			var p = new URLSearchParams();
			p.set("action", "onplay_filter");
			p.set("nonce", window.onplayFilters.nonce);
			["set", "color", "rarity", "condition", "lang"].forEach(function (k) {
				Filters.state[k].forEach(function (v) { p.append(k + "[]", v); });
			});
			p.set("foil", Filters.state.foil);
			p.set("price_min", String(Filters.state.price_min || 0));
			p.set("price_max", String(Filters.state.price_max || 0));
			p.set("in_stock", Filters.state.in_stock ? "1" : "0");
			p.set("sort", Filters.state.sort);
			p.set("page", String(Filters.state.page));
			return p.toString();
		},

		update: function () {
			Filters.writeStateToURL(true);
			Filters.fetch();
		},

		fetch: function () {
			if (Filters.controller) Filters.controller.abort();
			Filters.controller = new AbortController();

			Filters.setLoading(true);

			var url = window.onplayFilters.ajaxUrl + "?" + Filters.buildQuery();

			fetch(url, {
				method: "GET",
				credentials: "same-origin",
				headers: { "X-Requested-With": "XMLHttpRequest" },
				signal: Filters.controller.signal,
			})
				.then(function (res) {
					if (!res.ok) throw new Error("HTTP " + res.status);
					return res.json();
				})
				.then(function (data) {
					if (!data || !data.success) throw new Error("bad payload");
					var d = data.data;
					Filters.grid.innerHTML = d.html;
					Filters.pagination.innerHTML = d.pagination_html;
					Filters.chips.innerHTML = d.chips_html;
					if (Filters.count) Filters.count.textContent = String(d.total_groups);
					Filters.setLoading(false);
				})
				.catch(function (err) {
					if (err.name === "AbortError") return;
					console.error("[onplay] filter fetch failed", err);
					Filters.setLoading(false);
				});
		},

		setLoading: function (on) {
			if (Filters.loading) {
				if (on) Filters.loading.removeAttribute("hidden");
				else Filters.loading.setAttribute("hidden", "");
			}
			if (Filters.grid) Filters.grid.classList.toggle("is-loading", !!on);
		},

		onPopState: function (e) {
			Filters.state = Filters.readStateFromURL();
			Filters.applyStateToUI();
			Filters.fetch();
		},
	};

	window.onplay.cart = Cart;
	window.onplay.cartUpdate = CartUpdate;
	window.onplay.variantTable = VariantTable;
	window.onplay.search = Search;
	window.onplay.filters = Filters;

	window.onplay.ready(function () {
		document.documentElement.classList.add("onplay-ready");
		Cart.init();
		CartUpdate.init();
		VariantTable.init();
		Search.init();
		Filters.init();

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
