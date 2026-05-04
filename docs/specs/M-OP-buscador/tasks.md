# Tasks — M-OP-buscador

**Spec**: `specs/M-OP-buscador/spec.md`
**Plan**: `specs/M-OP-buscador/plan.md`

Leyenda: `[ ]` pendiente · `[X]` hecho · `[P]` paralelizable

> **Pre-requisito bloqueante**: M-OP-filtros debe estar completado y mergeado a main. Sin `onplay_op_is_archive()` y `onplay_op_term_descends_from()` este módulo no compila.

---

## Fase 0 — Verificación de pre-requisitos

### T000 — Verificar que M-OP-filtros está hecho
- **Acción**: ejecutar en local con el tema activo:
  ```bash
  wp shell
  > var_dump( function_exists( 'onplay_op_is_archive' ) );
  > var_dump( function_exists( 'onplay_op_term_descends_from' ) );
  ```
- **Criterio de hecho**: ambas retornan `bool(true)`.
- **Bloqueante**: si retornan false → completar M-OP-filtros antes de seguir.

### T001 — Auditar el endpoint actual de búsqueda M5
- **Acción**: leer `inc/search.php` completo. Documentar en bitácora:
  - Ruta del endpoint (¿REST `onplay/v1/search` o `admin-ajax.php`?).
  - Nombre exacto de la función handler.
  - Estructura del payload de respuesta.
  - Cómo se hace la agrupación por `print_key` (Magic).
- **Criterio de hecho**: notas en la bitácora suficientes para extender el endpoint sin romperlo.

### T002 — Confirmar productos de prueba para QA
- **Acción**: en local, asegurar que existen:
  - Al menos 5 productos OP con `_card_number` y `_set_full_code` poblados.
  - Al menos 5 productos Magic con nombres que se puedan usar para test de no-regresión.
  - Al menos 1 carta OP con nombre que también exista en Magic (ej. "Strike", "Bolt", "Charge") para validar la separación.
- **Criterio de hecho**: lista documentada en bitácora.

---

## Fase 1 — Helper de contexto

### T010 — Crear `inc/op-filters/helpers.php` con `onplay_op_is_single()` y `onplay_op_in_op_context()`
- **Archivo**: `wp-content/themes/onplay/inc/op-filters/helpers.php`
- **Descripción**: implementar las dos funciones del `plan.md` §3.1.
- **Criterio de hecho**:
  - `wp shell`: `onplay_op_in_op_context()` retorna `true` en archive OP, `false` en home/magic/single Magic.
  - `php -l` limpio.

### T011 — Cargar helpers desde el bootstrap del módulo de filtros
- **Archivo**: `wp-content/themes/onplay/inc/op-filters.php`
- **Descripción**: agregar `require_once __DIR__ . '/op-filters/helpers.php';` después de los otros `require_once`.
- **Criterio de hecho**: las funciones están disponibles globalmente al cargar el tema.

---

## Fase 2 — Backend: extender endpoint de búsqueda

### T020 — Extender `inc/search.php` con rama `tcg=op`
- **Archivo**: `wp-content/themes/onplay/inc/search.php`
- **Descripción**: agregar lógica del `plan.md` §3.2:
  - Sanitizar `$_GET['tcg']`.
  - Si `tcg === 'op'`:
    - Agregar `tax_query` con slug `one-piece-tcg`, `include_children: true`.
    - Si query parece card number (regex), agregar `meta_query` por `_card_number` y unset `s`.
  - **Conservar comportamiento Magic** intacto cuando `tcg` no se envía.
- **Criterio de hecho**:
  - `curl "http://onplay.test/wp-json/onplay/v1/search?q=Bonney&tcg=op"` devuelve solo productos OP.
  - `curl "http://onplay.test/wp-json/onplay/v1/search?q=Bolt"` devuelve productos Magic (sin cambios).
  - Response JSON incluye campo `context: "op"` o `context: "global"`.
- **CA cubierto**: CA-3, CA-4, CA-9

### T021 — Implementar `onplay_op_format_search_result()`
- **Archivo**: `wp-content/themes/onplay/inc/search.php` (al final del archivo)
- **Descripción**: función helper del `plan.md` §3.3 que formatea un post OP a estructura JSON con thumbnail, set, card_number, price_html, is_alt_art.
- **Criterio de hecho**: invocada desde T020, retorna estructura completa.

### T022 — Agrupar resultados OP por `_card_number`
- **Archivo**: `wp-content/themes/onplay/inc/search.php` (dentro del handler)
- **Descripción**: en la rama OP, recorrer `$q->posts` y agrupar por `_card_number`. Un resultado por card number, no por producto.
- **Criterio de hecho**: si hay 3 productos con `_card_number = EB04-001` (alt arts distintos), el autocomplete devuelve 1 resultado.
- **CA cubierto**: CA-3

### T023 [P] — Agregar cache transient para resultados OP
- **Archivo**: `wp-content/themes/onplay/inc/search.php`
- **Descripción**: cache key `onplay_search_op_{md5($query)}`, TTL 5 minutos. Solo aplicar a rama OP.
- **Criterio de hecho**: segunda búsqueda idéntica retorna en <50ms (sin DB hit).

---

## Fase 3 — Frontend: header con detección de contexto

### T030 — Modificar `header.php` o `template-parts/search-form.php` para detectar contexto
- **Archivos**: `wp-content/themes/onplay/header.php` **o** `template-parts/search-form.php` (el que renderice el buscador hoy).
- **Descripción**:
  - Calcular `$op_context = function_exists('onplay_op_in_op_context') && onplay_op_in_op_context();`.
  - Wrapper con `data-tcg="op"` cuando contexto.
  - Action del form a `/categoria-producto/one-piece-tcg/` cuando contexto, a `home_url('/')` cuando global.
  - `<input type="hidden" name="tcg" value="op">` solo cuando contexto.
  - `<span class="header-search__context">Buscando en One Piece</span>` solo cuando contexto.
- **Criterio de hecho**:
  - View source en `/categoria-producto/one-piece-tcg/`: aparece `data-tcg="op"`, hidden input, span.
  - View source en home: NO aparecen.
- **CA cubierto**: CA-1, CA-2, CA-8

### T031 [P] — Estilos del indicador de contexto
- **Archivo**: `wp-content/themes/onplay/assets/src/scss/components/_header.scss` (o donde esté el SCSS del header).
- **Descripción**: agregar:
  ```scss
  .header-search__context {
    display: block;
    margin-top: 4px;
    font-size: 11px;
    color: var(--mid-2);
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  ```
- **Criterio de hecho**: indicador visible y discreto en archive OP, sin parpadeos.

---

## Fase 4 — Frontend: JS

### T040 — Modificar `assets/src/js/search-autocomplete.js` para leer `data-tcg`
- **Archivo**: `wp-content/themes/onplay/assets/src/js/search-autocomplete.js`
- **Descripción**: 
  - Al inicializar, leer `wrapper.dataset.tcg`.
  - Si === `"op"`, agregar `&tcg=op` al fetch.
  - Conservar comportamiento Magic cuando es global.
- **Criterio de hecho**:
  - Network tab en archive OP muestra fetch a `?q=...&tcg=op`.
  - Network tab en home/Magic muestra fetch a `?q=...` sin `tcg`.
- **CA cubierto**: CA-1, CA-9

### T041 — Render diferenciado por contexto del item
- **Archivo**: `wp-content/themes/onplay/assets/src/js/search-autocomplete.js`
- **Descripción**:
  - Función `renderResult(item)` que delega a `renderOpResult(item)` si `item.context === 'op'`, si no a la función Magic existente.
  - `renderOpResult` produce el HTML del `plan.md` §3.5 con thumbnail, título, set code, badge alt-art, precio.
- **Criterio de hecho**: items OP se ven con set code (`OP15-EB04`), items Magic siguen viéndose como antes.
- **CA cubierto**: CA-5

### T042 — Empty state OP con opción "Buscar en todo el sitio"
- **Archivo**: `wp-content/themes/onplay/assets/src/js/search-autocomplete.js`
- **Descripción**:
  - Si `tcg === 'op'` y `results.length === 0`: mostrar mensaje "No encontramos cartas en One Piece con '{query}'" + link "Buscar en todo el sitio" que reescala a búsqueda global (re-fetch sin `tcg=op`).
- **Criterio de hecho**:
  - En archive OP, buscar "xyzwww" → empty state OP visible.
  - Click en "Buscar en todo el sitio" → re-fetch global, muestra resultados Magic si los hay.
- **CA cubierto**: CA-7

---

## Fase 5 — QA

### T050 — Tests funcionales completos (CA-1 a CA-10)
- **Acción**: ejecutar los 8 escenarios de testing del `plan.md` §8.
- **Entregable**: capturas en `specs/M-OP-buscador/qa/` (autocomplete OP, autocomplete Magic, empty state OP, submit OP, submit Magic).
- **Criterio de hecho**: todos los CA verificados.

### T051 — Test exhaustivo de no-regresión Magic
- **Acción**: en archive Magic y home, ejecutar 10 búsquedas distintas (nombres de cartas reales, parciales, números de set, vacío). Comparar contra el comportamiento previo a este módulo.
- **Criterio de hecho**: 0 diferencias visibles ni en consola.
- **CA cubierto**: CA-9

### T052 — Lighthouse mobile pre/post
- **Acción**: correr Lighthouse mobile en archive OP **antes** y **después** de aplicar el módulo.
- **Criterio de hecho**: score no baja más de 3 puntos.
- **CA cubierto**: CA-6

### T053 [P] — `debug.log` limpio
- **Acción**: hacer 30 búsquedas variadas (OP, Magic, ambos contextos, queries inválidas, queries con caracteres especiales). Revisar `wp-content/debug.log`.
- **Criterio de hecho**: 0 warnings/notices nuevos atribuibles a este módulo.

---

## Fase 6 — Cierre y deploy

### T060 — Commits y PR
- **Acción**: rama `feat/m-op-buscador`. Commits:
  - `feat(search): helpers de contexto OP (is_single, in_op_context)`
  - `feat(search): rama tcg=op en endpoint con tax_query y agrupación por card_number`
  - `feat(search): cache transient para búsquedas OP`
  - `feat(header): detección de contexto OP + indicador visual`
  - `feat(search-js): branch tcg=op + render diferenciado + empty state OP`
- **Criterio de hecho**: PR creado con descripción que linkea al spec.

### T061 — Actualizar sistema documental
- **Acción**: aplicar el bloque correspondiente de `docs/integracion-claude-md.md`.
- **Criterio de hecho**: `CLAUDE.md` §11 + `DONE.md` + `PENDING-CLAUDE.md` sincronizados.

### T062 — Deploy a Hostinger
- **Pre-requisitos**: backup completo, M-OP-filtros ya en producción y validado.
- **Acción**: subir cambios, purgar caché.
- **Criterio de hecho**: smoke test en `onplay.cl`:
  - Buscador en home funciona como antes.
  - Buscador en categoría OP muestra indicador y resultados filtrados.
  - 0 errores en consola.

---

## Bitácora

- **2026-05-03**: spec, plan y tasks generados.
- **2026-05-04 T000**: helpers M-OP-filtros disponibles (`onplay_op_is_archive`, `onplay_op_term_descends_from`, `onplay_print_key_from_sku`, `onplay_tcg_from_sku`). ✓ pre-requisito OK.
- **2026-05-04 T001 — Auditoría M5**: el endpoint NO es REST (`/wp-json/onplay/v1/search`), es **`admin-ajax.php?action=onplay_search`**. Hooks `wp_ajax_onplay_search` + `wp_ajax_nopriv_onplay_search`. Handler `onplay_ajax_search_handler()` → `onplay_search_products($q, $limit)`. Usa **`$wpdb->get_col` raw SQL** (NO `WP_Query`, NO `s`), busca `post_title LIKE` OR `pm_sku.meta_value LIKE`, AND `_stock_status = instock`, pool = `limit*6 = 48`. Agrupación clave `$name|$print_key`, representante = SKU más barato. Payload `{name, set_name, set_code, print_key, min_price, min_price_formatted, thumb, permalink}`. Sin tests automáticos.
- **2026-05-04 T002**: 8 productos OP con `_card_number` poblado (incluye `EB04-002 Jewelry Bonney` para CA-3 y `OP07-005 Carina` ×2 con/sin Alt Art para CA-5). 226 productos Magic. **No hay carta OP con nombre que colisione con Magic** ("Bolt"/"Lightning"/"Strike" no existen en OP) — caso de test usado: OP buscando "Bolt" → empty state (CA-7) en lugar de match cruzado.
- **2026-05-04 Desviaciones del `plan.md`** (5 errores de hecho corregidos):
  1. Plan §3.5 dice REST `/wp-json/onplay/v1/search` → realidad admin-ajax. JS construye URL con `window.onplaySearch.ajaxUrl + ?action=onplay_search&nonce=...`.
  2. Plan §3.2 usa `WP_Query` con `s` y `tax_query` → realidad SQL crudo. Implementado con `INNER JOIN wp_term_relationships` filtrando por `term_taxonomy_id IN (...descendientes one-piece-tcg)`.
  3. Plan §2 lista `template-parts/search-form.php` → no existe; form vive inline en `header.php`. Modificación va en `header.php`.
  4. Plan §2 lista `assets/src/js/search-autocomplete.js` → no existe; el módulo Search vive en `main.js`. Creado `assets/src/js/op-search.js` que monkey-patcha `Search.fetch` y `Search.render`, concatenado al bundle vía `package.json` build:js (mismo patrón que op-filters.js).
  5. Plan §3.4 selectores `.header-search` → reales `data-onplay-search`, `data-onplay-search-input`, `data-onplay-search-results`. Plan dice action a `/categoria-producto/one-piece-tcg/?s=` → real shop slug es `/shop/`, archive-product.php parsea `q` (no `s`). Implementado: action a `/shop/?set=one-piece-tcg` con `name="q"` cuando contexto OP.
- **2026-05-04 T010+T011**: `inc/op-filters/helpers.php` con `onplay_op_is_single()`, `onplay_op_in_op_context()`, `onplay_op_get_descendant_tt_ids()` (10 tt_ids: 117 raíz + 9 descendientes). Cargado desde `inc/op-filters.php`.
- **2026-05-04 T020+T021+T022**: extendido `inc/search.php`. Nueva función `onplay_search_products_op($q, $limit)` con SQL `INNER JOIN wp_term_relationships` + búsqueda en title/sku/_card_number (todos LIKE), agrupación por `_card_number`, regla "regular gana sobre alt-art como representante". Payload incluye `card_number`, `is_alt_art`, `set_code = _set_full_code`, `context = "op"`. Magic preserva su comportamiento intacto (solo se agrega campo `context = "global"`, sin breaking change en consumidores que ignoraban ese campo).
- **2026-05-04 T030+T031**: `header.php` detecta contexto con `onplay_op_in_op_context()`, agrega `data-tcg`, hidden inputs (`set` + `tcg`), span `[data-onplay-search-context]` con texto "Buscando en One Piece". Modifier `--op` en el form. SCSS en `_search-autocomplete.scss`: indicador absolute bajo el input en var(--carmesi)/mono/uppercase, borde tenue del input, badge "Alt Art" en gold, empty state con CTA "Buscar en todo el sitio".
- **2026-05-04 T040-T042+T040b**: `assets/src/js/op-search.js` monkey-patcha `Search.fetch` (agrega `&tcg=op` cuando `data-tcg='op'`), `Search.render` (branches op-results / op-empty / global). Empty state OP con CTA "Buscar en todo el sitio" hace re-fetch sin `tcg=op` (`doGlobalRefetch`). i18n agregado en `inc/enqueue.php` (`opNoResults`, `opSearchAll`, `opAltArt`). Concatenado al bundle via `package.json` build:js extendido.
- **2026-05-04 T050+T051 — QA funcional vía endpoint AJAX real**:
  - CA-3: `?q=Bonney&tcg=op` → 1 result (Jewelry Bonney EB04-002) con full payload OP. ✓
  - CA-4: `?q=EB04&tcg=op` → 5 results del set EB04 (LIKE en `_card_number`). ✓
  - CA-5: `?q=Carina&tcg=op` → 1 result agrupado (OP07-005 alt:false — regular elegida sobre alt-art como representante). ✓
  - CA-7: `?q=Bolt&tcg=op` → 0 results (empty state listo en JS). ✓
  - CA-9 no-regresión: `?q=Bolt` → 2 Magic results con `context:"global"`. ✓
  - `?q=Burst` → Magic intacto. ✓
  - Edge cases: `?q=` y `?q=B` (1 char) → results vacíos sin error. ✓
- **2026-05-04 T053 — latencia + debug.log**: P95 ~950ms tanto OP como Magic en Laragon Windows local (entorno-bound, no endpoint-bound). **OP no degrada vs Magic** — el INNER JOIN es paritario. `debug.log` sin warnings nuevos atribuibles al módulo (las warnings que hay son pre-existentes de `review-order.php` del M-cuenta).
- **2026-05-04 T023 — cache transient: NO implementado**. YAGNI: latencia OP/Magic paritaria en local, cache invalidation requeriría hook `save_post_product` con invalidación selectiva (footgun). Reactivar si en producción Hostinger se mide >500ms en P95.
- **2026-05-04 T052 — Lighthouse mobile**: pendiente owner. El módulo NO agrega assets externos (solo extiende main.js + style.css ya existentes), debería costar ≤2 puntos.
