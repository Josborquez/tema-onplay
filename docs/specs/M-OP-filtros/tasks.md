# Tasks — M-OP-filtros

**Spec**: `specs/M-OP-filtros/spec.md`
**Plan**: `specs/M-OP-filtros/plan.md`

Leyenda: `[ ]` pendiente · `[X]` hecho · `[P]` paralelizable

> **Antes de empezar**: ejecutar Paso 0 del `PROMPT-CLAUDE-CODE.md` (verificar contrato Binder OP, slug de categoría, productos en stock). No tocar código sin completar este paso.

---

## Fase 0 — Verificación de pre-requisitos

### T000 — Auditoría del estado real
- **Acción**: Ejecutar en local con DB copia:
  ```bash
  wp post list --post_type=product --tax_query='[{"taxonomy":"product_cat","terms":"one-piece-tcg"}]' --format=count
  wp post meta list <ID_OP> --format=table
  wp term get product_cat one-piece-tcg --field=name
  ```
- **Criterio de hecho**: documentar en la bitácora de este `tasks.md`:
  - Cantidad real de productos OP.
  - Lista de meta encontrados en el primer producto (debe incluir `_color`, `_card_type`, `_is_alt_art`, etc.).
  - Slug verificado de la categoría raíz.
- **Bloqueante**: si `_color` o `_card_type` no aparecen → detenerse y avisar al dueño antes de seguir.

---

## Fase 1 — Bootstrap del módulo

### T001 — Crear `inc/op-filters.php` (bootstrap)
- **Archivo**: `wp-content/themes/onplay/inc/op-filters.php`
- **Descripción**: archivo de entrada del módulo. Solo `require_once` de los demás archivos en orden:
  ```php
  <?php
  /**
   * M-OP-filtros — Panel facetado de filtros para One Piece TCG
   * Spec: docs/specs/M-OP-filtros/
   */
  defined( 'ABSPATH' ) || exit;

  require_once __DIR__ . '/op-filters/query.php';
  require_once __DIR__ . '/op-filters/panel.php';
  require_once __DIR__ . '/op-filters/partial.php';
  require_once __DIR__ . '/op-filters/enqueue.php';
  ```
- **Criterio de hecho**: archivo existe, sintaxis PHP válida (`php -l`).

### T002 — Registrar bootstrap en `functions.php`
- **Archivo**: `wp-content/themes/onplay/functions.php`
- **Descripción**: agregar **una línea** al final de la sección de includes (mantener orden alfabético si existe):
  ```php
  require_once get_stylesheet_directory() . '/inc/op-filters.php';
  ```
- **Criterio de hecho**: el sitio carga sin fatal error. `wp shell` muestra `function_exists('onplay_op_is_archive')` retornando `true` cuando se cree.

---

## Fase 2 — Lógica server-side

### T010 — Implementar `inc/op-filters/query.php` con helpers + pre_get_posts
- **Archivo**: `wp-content/themes/onplay/inc/op-filters/query.php`
- **Descripción**: contiene:
  - Constantes `ONPLAY_OP_COLORS`, `ONPLAY_OP_CARD_TYPES`, `ONPLAY_OP_ILLUSTRATION` (ver `plan.md` §4.4).
  - Helpers: `onplay_op_get_param()`, `onplay_op_is_archive()`, `onplay_op_term_descends_from()`.
  - Hook `pre_get_posts` → `onplay_op_apply_filters()` (ver `plan.md` §4.2).
- **Criterio de hecho**:
  - `php -l` limpio.
  - Visitar `/categoria-producto/one-piece-tcg/?op_color=red` y verificar con Query Monitor que la SQL incluye `meta_query` para `_color LIKE '%Red%'`.
  - Visitar `/categoria-producto/magic-the-gathering/?op_color=red` y verificar que **NO** se aplica el filtro (Magic no debe verse afectado).
- **CA cubierto**: CA-2, CA-3, CA-4

### T011 [P] — Implementar `inc/op-filters/panel.php` (render del panel)
- **Archivo**: `wp-content/themes/onplay/inc/op-filters/panel.php`
- **Descripción**:
  - Hook `woocommerce_before_shop_loop` (priority 15) → `onplay_op_render_panel()`.
  - Función protegida por `if ( ! onplay_op_is_archive() ) return;`.
  - Carga `template-parts/op-filters/group-color.php`, `group-card-type.php`, `group-illustration.php`.
  - Wrapper `<aside class="op-filters" data-op-filters>` con header (título + contador + botón limpiar).
  - Mobile: usa `<details class="op-filters--mobile">` para colapsar.
- **Criterio de hecho**: en `/categoria-producto/one-piece-tcg/` aparece el panel a la izquierda. En Magic NO aparece.
- **CA cubierto**: CA-1, CA-7, CA-8

### T012 [P] — Crear template-parts de cada grupo
- **Archivos**:
  - `wp-content/themes/onplay/template-parts/op-filters/group-color.php`
  - `wp-content/themes/onplay/template-parts/op-filters/group-card-type.php`
  - `wp-content/themes/onplay/template-parts/op-filters/group-illustration.php`
- **Descripción**: cada uno itera la constante correspondiente y renderiza:
  ```html
  <div class="op-filters__group">
    <h3 class="op-filters__title">Color</h3>
    <ul class="op-filters__options">
      <li class="op-filters__option">
        <label>
          <input type="checkbox" data-dim="color" data-value="Red" <?php checked( in_array( 'Red', $current_colors, true ) ); ?>>
          <span class="op-filters__swatch" style="background:#D62828"></span>
          <span class="op-filters__label">Red</span>
        </label>
      </li>
      ...
    </ul>
  </div>
  ```
- **Criterio de hecho**: render server-side correcto. Marcar checkbox vía URL directa (`?op_color=red`) refleja el `checked` sin JS.
- **CA cubierto**: CA-5

### T013 — Implementar `inc/op-filters/partial.php` (AJAX endpoint)
- **Archivo**: `wp-content/themes/onplay/inc/op-filters/partial.php`
- **Descripción**: hook `template_redirect` → si `?op_partial=1` y `onplay_op_is_archive()`, devuelve solo `<div data-op-grid-wrap>...</div>` con loop + paginación, sin layout. Headers `nocache_headers()`.
- **Criterio de hecho**:
  - `curl -s "http://onplay.test/categoria-producto/one-piece-tcg/?op_color=red&op_partial=1"` devuelve solo el wrap con productos, sin `<header>` ni `<footer>`.
  - Status 200, Content-Type `text/html`.
- **CA cubierto**: CA-9

### T014 — Implementar `inc/op-filters/enqueue.php`
- **Archivo**: `wp-content/themes/onplay/inc/op-filters/enqueue.php`
- **Descripción**: hook `wp_enqueue_scripts` (priority 20) que agrega `body_class` `onplay-op-archive` cuando `onplay_op_is_archive()`. NO encola CSS/JS separados — el bundle principal los incluye.
- **Criterio de hecho**: en archive OP el `<body>` tiene la clase. En Magic no la tiene.

---

## Fase 3 — Frontend (SCSS + JS)

### T020 — Crear `assets/src/scss/components/_op-filters.scss`
- **Archivo**: `wp-content/themes/onplay/assets/src/scss/components/_op-filters.scss`
- **Descripción**: estilos del panel respetando tokens existentes. Ver `plan.md` §8.
  - `.op-filters` wrapper sticky en desktop.
  - `.op-filters__group` con divider.
  - `.op-filters__option` con checkbox custom + swatch.
  - Responsive ≤768px con `<details>` colapsable.
- **Criterio de hecho**: build SCSS limpio (`npm run build`), sin warnings.

### T021 — Importar el componente en `main.scss`
- **Archivo**: `wp-content/themes/onplay/assets/src/scss/main.scss`
- **Descripción**: agregar `@use 'components/op-filters';` en la sección de imports de componentes.
- **Criterio de hecho**: el CSS compilado contiene las reglas `.op-filters`.

### T022 — Crear `assets/src/js/op-filters.js`
- **Archivo**: `wp-content/themes/onplay/assets/src/js/op-filters.js`
- **Descripción**: vanilla JS con responsabilidades del `plan.md` §6:
  - Read/write state desde URL.
  - Listener change delegado.
  - Refresh grid via fetch al partial.
  - popstate handler.
  - Botón Limpiar.
  - Mobile counter.
- **Criterio de hecho**:
  - Marcar checkbox actualiza URL sin recarga.
  - Network tab muestra fetch a `?op_partial=1`.
  - Grid se reemplaza sin parpadeo.
- **CA cubierto**: CA-5, CA-6, CA-9

### T023 — Importar JS en el bundle principal
- **Archivo**: `wp-content/themes/onplay/assets/src/js/main.js`
- **Descripción**: agregar import `import './op-filters.js';` (o equivalente según el bundler actual del tema).
- **Criterio de hecho**: el `assets/dist/main.js` tras build contiene la lógica del panel.

---

## Fase 4 — Documentación e índices

### T030 [P] — Documentar índices SQL recomendados
- **Archivo**: `docs/op-filters-sql-indexes.md`
- **Descripción**: SQL `CREATE INDEX` del `plan.md` §9.1 con explicación de cuándo aplicarlos.
- **Criterio de hecho**: archivo existe, instrucciones claras para el dueño.

### T031 [P] — Actualizar README del tema
- **Archivo**: `wp-content/themes/onplay/README.md`
- **Descripción**: agregar sección "M-OP-filtros" con descripción de 3-5 líneas + link al spec.
- **Criterio de hecho**: README actualizado.

---

## Fase 5 — QA local

### T040 — Checklist de QA manual
- **Acción**: ejecutar los 13 pasos del `plan.md` §12.
- **Entregable**: capturas en `specs/M-OP-filtros/qa/` (panel desktop, panel mobile, grid filtrado, URL compartida en otro browser).
- **Criterio de hecho**: todos los CA verificados con captura.

### T041 — Lighthouse mobile en categoría OP
- **Acción**: correr Lighthouse mobile en `/categoria-producto/one-piece-tcg/` con y sin filtros aplicados.
- **Criterio de hecho**: score reportado en bitácora. Target ≥ 80 (no debe bajar más de 5 puntos respecto al baseline de la categoría sin panel).

### T042 — Verificación de no-regresión Magic
- **Acción**: visitar `/categoria-producto/magic-the-gathering/` y aplicar 3 filtros del panel Magic existente.
- **Criterio de hecho**: panel Magic funciona idéntico. 0 errores en consola. La URL no contiene params `op_*` injustificados.

---

## Fase 6 — Cierre y deploy

### T050 — Commit + push de la rama feature
- **Acción**: rama `feat/m-op-filtros`, commits convencionales:
  - `feat(op-filters): bootstrap module + query hook`
  - `feat(op-filters): panel + template-parts`
  - `feat(op-filters): AJAX partial + JS interaction`
  - `style(op-filters): SCSS component`
  - `docs(op-filters): SQL indexes + README`
- **Criterio de hecho**: rama publicada, PR creado con descripción que linkea spec.

### T051 — Actualizar sistema documental
- **Acción**: aplicar el bloque correspondiente de `docs/integracion-claude-md.md`:
  - Mover este módulo de `PENDING-CLAUDE.md` a `DONE.md`.
  - Agregar entrada en `CLAUDE.md` §11 con la fecha y commits.
- **Criterio de hecho**: los 3 archivos sincronizados en el commit final.

### T052 — Deploy a Hostinger
- **Acción**: subir solo los archivos del tema (no DB) vía Git Deploy o SFTP.
- **Pre-requisitos**:
  - Backup completo de Hostinger (ver `PENDING-OWNER.md`).
  - Confirmar que HUSKY está desactivado.
  - Smoke test en staging si está disponible.
- **Criterio de hecho**: producción muestra el panel en categoría OP, smoke test ok.

---

## Bitácora

Anotar aquí desviaciones, decisiones tomadas durante implementación, problemas encontrados.

- **2026-05-03**: spec, plan y tasks generados desde el módulo SDD One Piece.
- **2026-05-03 T000 — Auditoría**: 25 productos OP en DB local (≥5 ✓), categoría `one-piece-tcg` term_id 117 con 2 hijos (`booster-packs`, `starter-decks`). Meta del Binder OP completos en samples (`_color`, `_card_type`, `_is_alt_art`, `_set_full_code`, `_card_number`, `_rarity_code`, `_rarity`, `_image_filename`). Distribución: `_color` 5 valores incluyendo dual `Red/Purple` (2 productos); `_card_type` 3 valores (CHARACTER 16 / LEADER 7 / EVENT 2); `_is_alt_art` 19 no + 6 yes. **`_block_icon`: 0 filas — confirmado fuera del MVP** (spec §6). Slug categoría confirmado.
- **2026-05-03 Desviación 1 — pipeline custom**: el plan asumía `pre_get_posts` sobre la main query del archive. Realidad: `inc/woocommerce.php` redirecciona `/product-category/<slug>/` → `/shop/?set=<slug>` (302), y `archive-product.php` usa `onplay_filters_run()` con su propio `WP_Query`, ignorando `pre_get_posts`. **Decisión** (con aprobación del dueño): Aproximación A — extender `inc/filters-ajax.php` mediante 2 filter hooks (`onplay_filters_state_after_parse`, `onplay_filters_meta_query`) en lugar de duplicar el pipeline. Mantengo `pre_get_posts` como red defensiva (canonical/schemas).
- **2026-05-03 Desviación 2 — slug del shop**: spec mencionaba `/categoria-producto/...` y `/tienda/`. Slug real es `/shop/` (verificado vía `wp_get_page_permalink('shop')`). Detección de archive cubre los 3 caminos: `is_product_taxonomy` directo, `is_shop()` con `?set=` descendiente de `one-piece-tcg`, e `is_shop()` con cualquier `op_*` param.
- **2026-05-03 Desviación 3 — T013 dropped**: el endpoint AJAX paralelo `?op_partial=1` se eliminó. Aproximación A reusa el endpoint `wp_ajax_onplay_filter` existente para refresco AJAX. Una única fuente de verdad de chips/paginación/grid.
- **2026-05-03 Desviación 4 — T011/T012 simplificados**: el panel.php delega a `onplay_op_render_sidebar()` y los template-parts heredan la estructura `.filter-group / __head / __body` ya estilizada. Solo SCSS nuevo para swatches OP (letra R/G/B/P/K/Y) y pills. El fork del sidebar ocurre con `return` temprano en `template-parts/filters-sidebar.php`.
- **2026-05-03 Desviación 5 — T022/T023 vía concat**: el bundler era un `copyFileSync`. Cambio mínimo en `package.json`: `build:js` lee `main.js` + `op-filters.js` y los concatena al `dist/main.js`. Sin runtime ES modules (incompatibles con `<script>` no-module en navegador).
- **2026-05-03 Diseño incorporado**: cargado bundle Claude Design (`docs/design-onplay-cl/`) durante implementación. Visual del panel (swatches con letras R/G/B/P/K/Y, pills uppercase mono, grid 2-col) sigue `listing-onepiece.jsx`. NO se implementaron filtros del diseño que carecen de meta backing en el Binder OP: `_attribute`, `_cost` (DON!!), `_power`, `_counter`, `_rarity_code` (filtro), `_set_full_code` (filtro), `_block_icon`. Tampoco condición/idioma — todo OP es NM/EN. Reincorporables si el Binder los popule.
- **2026-05-03 Caso canónico de duales**: spec menciona `EB04-001 Jewelry Bonney` con `_color="Red/Yellow"`. En la DB local **no existe ese SKU** (no hay sets EB04). Caso equivalente: **OP06-001 Uta** con `_color="Red/Purple"`. Funciona idéntico para validar CA-2/CA-11.
- **2026-05-03 Verificación funcional server-side**: counts vía curl + `data-shop-count`:
  - Sin filtro: 25 ✓
  - `?op_color=Red`: 15 (13 Red puros + 2 Red/Purple — duales capturados con LIKE) ✓
  - `?op_color=Purple`: 5 (3 Purple puros + 2 Red/Purple) ✓
  - `?op_type=LEADER`: 7 ✓
  - `?op_alt=alt`: 6 ✓
  - `?op_color=Red&op_type=LEADER`: 5 (AND lógico) ✓
  - `/shop/` Magic sin params: 226 (sin regresión) ✓
- **2026-05-03 SSR de checkboxes (CA-5)**: verificado vía curl + awk — `?op_color=Red&op_type=LEADER` retorna `class="op-filters__option is-active"` y `aria-pressed="true"` solo en los botones correspondientes; el resto queda en `false`. Funciona sin JS.
- **2026-05-03 body_class condicional (T014)**: archive OP trae `onplay-op-archive`; shop Magic NO la trae. Verificado.
- **2026-05-03 Junction Laragon**: el docroot `C:\laragon\www\testmanager\wp-content\themes\onplay` es un junction al repo en OneDrive. **NO hay drift** — edits en repo se reflejan inmediato. Memoria `dev_env_laragon.md` quedaba parcialmente desactualizada y se actualizará al cierre.
- **2026-05-03 Pendiente de QA visual (T040/T041)**: requiere navegador real para capturas de panel desktop, panel mobile colapsado con contador, grid filtrado, URL compartida entre navegadores, Lighthouse mobile (target ≥80, CA del módulo). Owner-side. Carpeta `specs/M-OP-filtros/qa/` se creará al ejecutar.
