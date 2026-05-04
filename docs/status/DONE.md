# DONE — Trabajo verificado

Módulos y entregables con criterios de aceptación cumplidos al 2026-04-29.

---

## Módulo 1 — Fundación del tema ✅

- Estructura de directorios según §4 de CLAUDE.md.
- `style.css` con header WP válido, slug `onplay`, versión inicial.
- `functions.php` con bootstrap de `/inc/`.
- `inc/setup.php` con theme supports (title-tag, post-thumbnails, woocommerce, custom-logo, html5, responsive-embeds) + `register_nav_menus` (primary, footer).
- `inc/enqueue.php` con `assets/dist/style.css` + `assets/dist/main.js` versionados por `filemtime`.
- `assets/src/scss/main.scss` con tokens, reset, typography, layout, componentes.
- `_tokens.scss` con tokens del design file Claude Design (`qsNZptfkkYAPh-YFwd9hQA`): `--carmesi #D62828`, `--black #0A0A0A`, `--carbon #141414`, `--bone #F5F5F5`, Bebas Neue + IBM Plex Sans + IBM Plex Mono.
- `assets/src/js/main.js` entry point con namespace `window.onplay`.
- `package.json` con `sass` + scripts `build` / `watch`.
- `header.php` con logo bulldog+diamante (JPG2), buscador placeholder, nav primary, placeholders de cuenta y carrito.
- `footer.php` con 4 columnas + copyright dinámico + redes placeholder.
- `index.php` fallback + `front-page.php` placeholder ("Home coming soon" hasta M9).
- `.gitignore` (Node + WP).
- `README.md` del tema con instalación, build, roadmap, contrato manager.

---

## Módulo 2 — Enriquecimiento Scryfall + Taxonomías ✅

- `inc/scryfall-enrich.php` con `onplay_enrich_from_scryfall($product_id)`.
- Hook async para enriquecer productos nuevos.
- Registro de taxonomías custom: `tcg_color`, `tcg_rarity`, `tcg_type`, `tcg_format_legal`, `tcg_foil`.
- Hook `save_post_product` que popula taxonomías desde meta `_onplay_*`.
- Comando WP-CLI `wp onplay:backfill-meta` con respeto al rate limit Scryfall (100ms).
- 410 productos enriquecidos con `_onplay_mana_cost`, `_onplay_type_line`, `_onplay_oracle_text`, `_onplay_colors`, `_onplay_legalities`.

---

## Módulo 3 — Templates de producto ✅

- Override `archive-product.php`, `content-product.php`, `single-product.php`.
- Ficha con imagen, mana cost con íconos, oracle renderizado, set, badges de formato.
- Add-to-cart default WC (la versión agrupada vino en M4).

---

## Módulo 4 — Tabla de variantes agrupada (`print_key`) ✅

- `onplay_get_variants_by_print_key($sku)` con cache transient.
- `template-parts/variant-table.php`.
- Reemplazo del add-to-cart con tabla AJAX → drawer.
- Cache invalidada al guardar producto del mismo `print_key`.

---

## Módulo 5 — Otras impresiones + Búsqueda ✅

- Sección "Otras impresiones de esta carta" (query por `post_title` normalizado).
- Input de búsqueda funcional en header.
- Endpoint AJAX autocomplete con debounce.
- Resultados agrupados por `print_key`.

---

## Módulo 6 — Listado con filtros facetados ✅

- Sidebar de filtros basado en taxonomías custom + `pa_estado` / `pa_idioma`.
- URLs amigables con query params.
- Sin recarga.
- Agrupación visual por `print_key` en el listado.

---

## Módulo 7 — Carrito y drawer ✅

- Drawer con operaciones full (agregar / modificar / eliminar).
- Página `/carrito/` customizada.
- Nombre del item incluye condición / idioma / foil.

---

## Módulo 9 — Home ✅

Commits: `9998259` → `4669b13` → `1036270` → `75e3ae9` → `735655d` → `ffcfd8d` → `366c5d3` → `7e5ed4b`.

- `front-page.php` + template-parts: `hero.php`, `trust-band.php`, `featured-sets.php`, `recent-cards.php`.
- Helpers `onplay_home_get_*` (featured sets + recent cards).
- Enqueue de Bebas + IBM Plex + Playfair + Keyrune + mana-font.
- **Iconografía oficial:** Keyrune para sets (`<i class="ss ss-...">`), mana-font para mana (`<i class="ss ms-... ms-cost ms-shadow">`). Híbridos y phyrexianos como símbolo combinado.
- **Recientes:** orden `price-desc` dentro del pool de 150 más nuevos en stock + carrusel horizontal scroll-snap + flechas wireadas por `RecentCarousel`.
- **Hero hybrid** fiel al diseño `home.jsx`: 2-col, stack flotante de 5 cartas animado, preview "Populares ahora" server-side, stats reales cacheados 1h.
- **PDP imagen capada a 460px** (evita upscale desde Scryfall `normal` 488px).

---

## Mini-módulo M-cuenta — Skin de `/mi-cuenta/` ✅ (código)

Overrides en `wp-content/themes/onplay/woocommerce/myaccount/`:

- `my-account.php` — wrapper 2-col (nav 240px + contenido) usando hooks `woocommerce_account_navigation` + `_content`.
- `navigation.php` — sidebar con SVG inline por endpoint (dashboard, orders, downloads, edit-address, payment-methods, edit-account, customer-logout).
- `dashboard.php` — saludo + tarjeta último pedido con status pill + empty state + 3 tiles.
- `form-login.php` — split login/registro o solo login según `woocommerce_enable_myaccount_registration`.
- `orders.php` — **lista como tarjetas, no tabla** (responsive sin data-title hacks).
- `my-address.php` — grid 2-col facturación|envío con estado `--filled` / `--empty`.
- `form-edit-address.php` — render via `woocommerce_form_field()` en grid 2-col.
- `form-edit-account.php` — nombre/apellido 2-col, email wide, fieldset password change.
- `form-lost-password.php` + `form-reset-password.php` — narrow forms centrados.
- `assets/src/scss/templates/_myaccount.scss` (~720 líneas) importado en `main.scss`.
- PHP lint limpio en los 10 templates, SCSS compila sin errores.

---

## Módulo 10 — Trabajo del tema ✅ (parcial)

Commit: `e42a371`.

- `inc/security.php` — cabeceras de seguridad: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `X-XSS-Protection: 0`, `Strict-Transport-Security` (solo con `is_ssl()`). Remueve `wp_generator`, `wlwmanifest`, `rsd`, `rest_output_link_wp_head`. Desactiva XML-RPC + pingback. Filtra `?ver=X` cuando coincide con `get_bloginfo('version')`. Backup de `DISALLOW_FILE_EDIT`. CSP **deliberadamente comentado** hasta auditar Webpay + Mercado Pago.
- `inc/seo.php` — JSON-LD desde el tema:
  - `Organization` (legalName: Comercializadora y Distribuidora BM Limitada, taxID 77.862.085-5, dirección Merced 832 Local 54).
  - `WebSite + SearchAction` (solo home, → `/tienda/?q={search_term_string}`).
  - `BreadcrumbList` (archive + single-product).
  - `Product` en single con `priceCurrency=CLP`, sku, availability, itemCondition=NewCondition, brand={Set name}, image full.
  - OG + Twitter Card `summary_large_image` con descripción del oracle cuando existe.
  - Canonical mínimo (home/shop/product_cat).
- `inc/enqueue.php` — performance: 3 CSS externos (Google Fonts, Keyrune, mana-font) con preload + onload + noscript fallback. Preconnect a `cdn.jsdelivr.net`. Preload de thumbnail full en PDP (LCP). `defer` en `onplay-main`.

---

## Design file (consumo del prompt 01) ✅

El prompt `Onplay.cl/uploads/01-PROMPT-CLAUDE-DESIGN.md` ya fue ejecutado en claude.ai/design y el design file resultante (`f03yAbQW7muzqRVdDJbvww` / `qsNZptfkkYAPh-YFwd9hQA`) está consumido por:

- Tokens CSS del tema (`_tokens.scss`) — paleta carmesí/black/carbon/bone, tipografía Bebas + IBM Plex.
- Layout de header / footer / cards / drawer / variant-table.
- Hero "hybrid" del home (M9) con stack flotante de cartas y stats.
- Skin de M-cuenta (overrides WC).

Iteraciones esperadas en el prompt (hero alternativo, selector de variantes, mobile pass) ya aplicadas en los módulos correspondientes.

---

## Infraestructura de páginas institucionales ✅

- **`inc/pages.php`** — catálogo canónico `onplay_pages_catalog()` con 13 páginas (legal, ayuda, empresa) y helper `onplay_page_url_by_slug()` con cache por request usado por el footer.
- **`inc/cli.php`** — comando WP-CLI `wp onplay:seed-pages` (con flags `--dry-run` y `--force`) que crea todas las Pages con contenido placeholder en bloques Gutenberg.
- Permite que el footer no rompa ni apunte a 404 desde el día 1; el contenido real se pega después en wp-admin.

---

## Contenido legal — convertido a Gutenberg ✅

- **Prompt 05 (T&C v2.1)** — redactado, datos legales reales integrados, 17 secciones. Pendiente convertir a Gutenberg como se hizo con el 06.
- **Prompt 06 (Política de Privacidad)** — ✅ contenido convertido a bloques Gutenberg en `docs/legal/politica-de-privacidad.gutenberg.html`. Listo para pegar en la Page que `seed-pages` ya creó (slug `politica-de-privacidad`). Email unificado a `contacto@onplay.cl` (el prompt 06 traía `onplay.cl@gmail.com` legacy, CLAUDE.md §1 manda).

---

## Contenido legal redactado (prompt 05) ✅

El documento de **Términos y Condiciones de Servicio v2.1** está redactado y listo para publicar (17 secciones, datos legales reales integrados, base normativa chilena: Ley 19.496, 19.628, 19.799, 20.575).

Datos legales ya integrados en el documento:
- Razón social, RUT, giro, ambas direcciones (tributaria L53 / física L54).
- Email `contacto@onplay.cl`, WhatsApp `+56 9 6682 6121`.
- §11 actualizada con condición de tienda oficial WPN / TPC / Bandai.

> ⚠️ El **contenido está listo** pero la **publicación NO** — ver `PENDING-CLAUDE.md` (template + maquetación) y `PENDING-OWNER.md` (validación legal + placeholders).

---

## Soporte multi-TCG en helpers del tema ✅

- **`onplay_tcg_from_sku()`** detecta `op` cuando el SKU empieza con `OP-`, `mtg` por defecto.
- **`onplay_print_key_from_sku()`** — para One Piece devuelve el SKU completo (cada SKU es su propia impresión, no hay variantes condición/idioma); para Magic mantiene `SET-COLLECTOR`.
- **`onplay_parse_sku()`** — rama OP usa `parts[1]` como `set_code` y `parts[2]` como `collector_number`; Magic mantiene comportamiento.
- **`onplay_normalize_card_name()`** — además de `(Foil)`, remueve sufijos `- SET-NUM` y `- SET-NUM (Alt Art P1)` típicos del binder OP.
- **`onplay_resolve_set_for_product()`** — toma el término `product_cat` más profundo (devuelve `[OP15-EB04]`, no el genérico `Booster Packs`).
- **Header / footer** — detectan `product_cat=one-piece-tcg` y reemplazan el badge "Pronto" por link real.

Verificado contra la DB de TestManager con 17 productos OP cargados por el Binder OP.

---

## Binder OP (sibling para One Piece) ✅ (construido por el dueño / equipo aparte)

Repo separado, no parte de `tema-onplay`. Operativo, sincronizando con WC vía REST API. Escribe meta sin prefijo (`_color`, `_card_type`, `_is_alt_art`, `_set_full_code`, `_card_number`, `_rarity_code`, `_rarity`, `_image_filename`). 17 productos OP cargados a 2026-04-30 (verificado en TestManager local).

Categorías jerárquicas creadas: `One Piece TCG > Booster Packs > [OP15-EB04]` y `[OP-07]`.

> El contrato real lo declara `docs/PROMPT_tema-onplay-cardlist-filtros.md`. La sección "Catálogo / buscador autónomo One Piece" en `PENDING-CLAUDE.md` lista el trabajo del tema para consumir esos meta.

---

## M-OP-filtros — Panel facetado One Piece TCG ✅ (código + verificación server-side)

Sidebar de filtros propio para `/shop/?set=one-piece-tcg` (y descendientes). Filtra por **Color** con soporte de duales (`Red/Yellow`, `Red/Purple`), **Tipo de carta** (Leader/Character/Event/Stage), **Tipo de ilustración** (Normal/Alternate Art).

**Archivos**:
- `inc/op-filters.php` (bootstrap) + `inc/op-filters/{query,panel,enqueue}.php`.
- `template-parts/op-filters/group-{color,card-type,illustration}.php`.
- `assets/src/scss/components/_op-filters.scss` (importado en `main.scss`).
- `assets/src/js/op-filters.js` (concatenado al bundle vía `package.json` build:js).
- Modificaciones: `template-parts/filters-sidebar.php` (4 líneas, fork temprano), `inc/filters-ajax.php` (2 hooks de extensión), `package.json` (build:js concat), `functions.php` (1 require_once), `README.md` (sección add-on).

**Estrategia (Aproximación A)**: extiende el pipeline existente `inc/filters-ajax.php` mediante `apply_filters('onplay_filters_state_after_parse')` y `apply_filters('onplay_filters_meta_query')`. NO crea custom taxonomies — opera vía `meta_query` con `_color LIKE` (duales), `_card_type IN`, `_is_alt_art =`. Deuda técnica documentada en CLAUDE.md §3.7.

**Verificación server-side (T040 parcial)**: counts vía curl con la categoría OP de 25 productos:
- `?op_color=Red` → 15 (13 Red + 2 Red/Purple, duales capturados con LIKE) ✓
- `?op_color=Purple` → 5 (3 Purple + 2 Red/Purple) ✓
- `?op_type=LEADER` → 7 ✓
- `?op_alt=alt` → 6 ✓
- `?op_color=Red&op_type=LEADER` → 5 (AND) ✓
- `/shop/` Magic → 226 sin afectación (no-regresión) ✓
- SSR `aria-pressed`/`is-active` server-side desde URL ✓
- `body_class` `onplay-op-archive` solo en archive OP ✓

**Pendiente owner**: QA visual con capturas (panel desktop, panel mobile colapsado con contador, grid filtrado, URL compartida entre browsers), Lighthouse mobile (target ≥80, CA-9 perf <500ms), back/forward del navegador, smoke en mobile ≤768px.

**Documentación**: spec/plan/tasks en `docs/specs/M-OP-filtros/`, índices SQL recomendados en `docs/op-filters-sql-indexes.md`, decisiones de diseño en `docs/design-onplay-cl/` (bundle Claude Design integrado durante implementación).

---

## Datos legales confirmados (no es módulo, es referencia)

- **Razón social:** Comercializadora y Distribuidora BM Limitada
- **RUT:** 77.862.085-5
- **Domicilio tributario:** Merced 832, Local 53, Santiago Centro
- **Dirección física:** Merced 832, Local 54, Galería Casa Colorada, Santiago Centro
- **Email:** contacto@onplay.cl · **WhatsApp:** +56 9 6682 6121
- **Condición:** tienda oficial WPN + The Pokémon Company + Bandai.

Aplicado en schemas (M10) y debe aplicarse en términos / privacidad / facturación.
