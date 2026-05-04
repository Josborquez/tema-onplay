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
	// Checkout — RUT chileno (Módulo 8)
	// Valida módulo 11 en blur y bloquea submit con RUT inválido.
	// Formatea a "12.345.678-K" al salir del campo.
	// Mirror del validador PHP en inc/checkout-rut.php.
	// ──────────────────────────────────────────────────────────
	var CheckoutRut = {
		init: function () {
			document.addEventListener("blur", CheckoutRut.onBlur, true);
			document.addEventListener("input", CheckoutRut.onInput, true);
			document.addEventListener("submit", CheckoutRut.onSubmit, true);
		},

		getInput: function (root) {
			var scope = root || document;
			return scope.querySelector("[data-onplay-rut]");
		},

		normalize: function (raw) {
			return String(raw || "")
				.toUpperCase()
				.replace(/[^0-9K]/g, "");
		},

		format: function (raw) {
			var clean = CheckoutRut.normalize(raw);
			if (clean.length < 2) return clean;
			var dv = clean.slice(-1);
			var digits = clean.slice(0, -1);
			digits = digits.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
			return digits + "-" + dv;
		},

		validate: function (raw) {
			var clean = CheckoutRut.normalize(raw);
			if (clean.length < 8 || clean.length > 9) return false;
			var dv = clean.slice(-1);
			var digits = clean.slice(0, -1);
			if (!/^\d+$/.test(digits)) return false;

			var sum = 0;
			var mult = 2;
			for (var i = digits.length - 1; i >= 0; i--) {
				sum += parseInt(digits[i], 10) * mult;
				mult = mult === 7 ? 2 : mult + 1;
			}
			var mod = 11 - (sum % 11);
			var expected =
				mod === 11 ? "0" : mod === 10 ? "K" : String(mod);
			return expected === dv;
		},

		onInput: function (e) {
			var input = e.target;
			if (!input || !input.matches || !input.matches("[data-onplay-rut]")) return;
			// Limpiar error al editar.
			CheckoutRut.setFieldState(input, "neutral");
		},

		onBlur: function (e) {
			var input = e.target;
			if (!input || !input.matches || !input.matches("[data-onplay-rut]")) return;
			var raw = input.value.trim();
			if (raw === "") {
				CheckoutRut.setFieldState(input, "neutral");
				return;
			}
			input.value = CheckoutRut.format(raw);
			var ok = CheckoutRut.validate(raw);
			CheckoutRut.setFieldState(input, ok ? "valid" : "invalid");
		},

		onSubmit: function (e) {
			var form = e.target;
			if (!form || !form.matches || !form.matches("form.checkout, form.woocommerce-checkout")) return;
			var input = CheckoutRut.getInput(form);
			if (!input) return;
			var raw = input.value.trim();
			if (raw !== "" && !CheckoutRut.validate(raw)) {
				e.preventDefault();
				e.stopPropagation();
				CheckoutRut.setFieldState(input, "invalid");
				input.focus();
				input.scrollIntoView({ behavior: "smooth", block: "center" });
			}
		},

		setFieldState: function (input, state) {
			var row = input.closest(".form-row, .onplay-rut-field");
			if (!row) return;
			row.classList.remove("is-rut-valid", "is-rut-invalid");
			if (state === "valid") row.classList.add("is-rut-valid");
			if (state === "invalid") row.classList.add("is-rut-invalid");
			var note = row.querySelector(".onplay-rut-msg");
			if (state === "invalid") {
				if (!note) {
					note = document.createElement("div");
					note.className = "onplay-rut-msg";
					note.setAttribute("role", "alert");
					row.appendChild(note);
				}
				note.textContent = "El RUT ingresado no es válido.";
			} else if (note) {
				note.remove();
			}
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
	// HeroSearch — autocomplete reactivo del hero (panel inline)
	// Comparte endpoint y nonce con Search (window.onplaySearch).
	// Cuando el input está vacío, restaura las filas "Populares ahora" renderizadas
	// server-side. Cuando hay >= 2 chars, reemplaza con el resultado del endpoint
	// y swapea el label del header.
	// ──────────────────────────────────────────────────────────
	var HeroSearch = {
		form: null,
		input: null,
		panel: null,
		head: null,
		body: null,
		labelPopular: "",
		labelSuggestions: "",
		originalHTML: "",
		controller: null,
		debounceTimer: null,

		init: function () {
			HeroSearch.form = document.querySelector("[data-onplay-hero-search]");
			if (!HeroSearch.form) return;
			HeroSearch.input = HeroSearch.form.querySelector("[data-onplay-hero-input]");
			HeroSearch.panel = document.querySelector("[data-onplay-hero-panel]");
			if (!HeroSearch.input || !HeroSearch.panel) return;
			HeroSearch.head = HeroSearch.panel.querySelector("[data-onplay-hero-head]");
			HeroSearch.body = HeroSearch.panel.querySelector("[data-onplay-hero-body]");
			if (!HeroSearch.head || !HeroSearch.body) return;
			if (!window.onplaySearch || !window.onplaySearch.ajaxUrl) return;

			HeroSearch.labelPopular = HeroSearch.head.getAttribute("data-label-popular") || "Populares ahora";
			HeroSearch.labelSuggestions = HeroSearch.head.getAttribute("data-label-suggestions") || "Sugerencias";
			HeroSearch.originalHTML = HeroSearch.body.innerHTML;

			HeroSearch.input.addEventListener("input", HeroSearch.onInput);
		},

		onInput: function () {
			clearTimeout(HeroSearch.debounceTimer);
			var q = HeroSearch.input.value.trim();

			if (q.length === 0) {
				HeroSearch.restore();
				return;
			}
			if (q.length < 2) {
				return; // respeta el mínimo del endpoint sin flashear estado.
			}

			HeroSearch.debounceTimer = setTimeout(function () {
				HeroSearch.fetch(q);
			}, 200);
		},

		restore: function () {
			HeroSearch.head.textContent = HeroSearch.labelPopular;
			HeroSearch.body.innerHTML = HeroSearch.originalHTML;
			if (HeroSearch.controller) HeroSearch.controller.abort();
		},

		fetch: function (q) {
			if (HeroSearch.controller) HeroSearch.controller.abort();
			HeroSearch.controller = new AbortController();

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
				signal: HeroSearch.controller.signal,
			})
				.then(function (res) {
					if (!res.ok) throw new Error("HTTP " + res.status);
					return res.json();
				})
				.then(function (data) {
					var items = data && data.success && data.data ? data.data.results || [] : [];
					HeroSearch.render(items);
				})
				.catch(function (err) {
					if (err.name === "AbortError") return;
					console.error("[onplay] hero search failed", err);
					HeroSearch.render([]);
				});
		},

		render: function (items) {
			HeroSearch.head.textContent = HeroSearch.labelSuggestions;

			if (!items || items.length === 0) {
				var empty = (window.onplaySearch.i18n && window.onplaySearch.i18n.noResults) || "Sin resultados locales";
				HeroSearch.body.innerHTML = '<div class="home-hero__sugg-empty">' + escapeHTML(empty) + "</div>";
				return;
			}

			var html = items
				.slice(0, 4)
				.map(function (r) {
					var setCode = (r.set_code || "").toLowerCase();
					var setIcon = setCode
						? '<i class="ss ss-' + escapeHTML(setCode) + ' ss-fw set-icon" style="font-size:11px" aria-hidden="true"></i>'
						: "";
					var thumb = r.thumb
						? '<img class="home-hero__sugg-img" src="' + escapeHTML(r.thumb) + '" alt="" loading="lazy" />'
						: '<span class="home-hero__sugg-img home-hero__sugg-img--ph" aria-hidden="true"></span>';
					return (
						'<a class="home-hero__sugg" href="' +
						escapeHTML(r.permalink) +
						'">' +
						thumb +
						'<span class="home-hero__sugg-body">' +
						'<span class="home-hero__sugg-name">' +
						escapeHTML(r.name) +
						"</span>" +
						'<span class="home-hero__sugg-meta">' +
						setIcon +
						"<span>" +
						escapeHTML(r.set_name || r.set_code || "") +
						"</span>" +
						"</span>" +
						"</span>" +
						'<span class="home-hero__sugg-price">' +
						escapeHTML(r.min_price_formatted) +
						"</span>" +
						"</a>"
					);
				})
				.join("");

			HeroSearch.body.innerHTML = html;
		},
	};

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

	// ---------- Recent cards carousel (home) ---------- //
	var RecentCarousel = {
		init: function () {
			var scroller = document.querySelector("[data-onplay-recent-scroller]");
			if (!scroller) return;
			var prev = document.querySelector("[data-onplay-recent-prev]");
			var next = document.querySelector("[data-onplay-recent-next]");
			var step = function () {
				// Un step ≈ ancho visible menos un pelito → ~2 cartas a la vez
				return Math.max(240, Math.round(scroller.clientWidth * 0.6));
			};
			if (prev) {
				prev.addEventListener("click", function () {
					scroller.scrollBy({ left: -step(), behavior: "smooth" });
				});
			}
			if (next) {
				next.addEventListener("click", function () {
					scroller.scrollBy({ left: step(), behavior: "smooth" });
				});
			}
		},
	};

	window.onplay.cart = Cart;
	window.onplay.cartUpdate = CartUpdate;
	window.onplay.checkoutRut = CheckoutRut;
	window.onplay.variantTable = VariantTable;
	window.onplay.search = Search;
	window.onplay.heroSearch = HeroSearch;
	window.onplay.filters = Filters;
	window.onplay.recentCarousel = RecentCarousel;

	window.onplay.ready(function () {
		document.documentElement.classList.add("onplay-ready");
		Cart.init();
		CartUpdate.init();
		CheckoutRut.init();
		VariantTable.init();
		Search.init();
		HeroSearch.init();
		Filters.init();
		RecentCarousel.init();

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

/* op-filters bundle */
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

/* op-search bundle */
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
