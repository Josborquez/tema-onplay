# PENDING — Claude Code (código del tema)

Pendientes que se resuelven en el repo del tema. Algunos están bloqueados por trabajo del dueño (ver [PENDING-OWNER.md](PENDING-OWNER.md)).

---

## Bloqueado por el dueño

### Módulo 0 — Auditoría y limpieza previa ⏸

- Verificar estado real de plugins innecesarios (`event-tickets`, `wc-customer-wallet`, `stackable-ultimate-gutenberg-blocks`).
- Confirmar que los 410 productos tienen atributos correctos.
- **Bug Lamentation (ID 10407):** auditar cuántos productos tienen `pa_estado` / `pa_idioma` vacíos por race condition del manager. Query documentada en §3.2 de CLAUDE.md.
- Decidir con el dueño si se amplía el manager (§3.4) antes o después del MVP.
- Configurar ambiente local de desarrollo (LocalWP/DDEV) con DB copiada de producción.

**Bloqueador:** acceso a producción + decisión del dueño sobre el manager.

---

### Módulo 8 — Checkout custom ⏸

- Override de templates de checkout (un solo paso, layout 2 columnas con resumen sticky).
- Secciones: Contacto, Despacho, Pago, Resumen.
- Validación RUT chileno (módulo 11).
- Captura de RUT para fase 2 (SII).
- Integración con Mercado Pago + Transbank (plugins ya instalados).

**Bloqueador:** el dueño debe configurar shipping zones (Retiro en tienda + Chilexpress) + Webpay sandbox keys + Mercado Pago sandbox keys. Sin esto el criterio "pedido test end-to-end" no se puede validar.

---

## Sin bloqueador — accionable ya

### Módulo 10 — Validación performance + SEO

- [ ] **Lighthouse mobile ≥85** en Home / Archive (`/tienda/`) / Single (ficha con variantes). Baseline antes de decidir critical CSS inline o mirror local de imágenes Scryfall.
- [ ] **Validar schemas** con [Schema.org Validator](https://validator.schema.org/) o [Rich Results Test](https://search.google.com/test/rich-results) contra URLs locales/staging.
- [ ] **Habilitar CSP** en `inc/security.php` después de capturar los orígenes que inyectan scripts/iframes Webpay + Mercado Pago en sandbox. Hoy queda comentado a propósito.

---

### M-cuenta — verificación pendiente

- [ ] **Smoke test manual en browser** de cada endpoint de `/mi-cuenta/` + responsive ≤560px:
  - `/mi-cuenta/` (login + registro / dashboard)
  - `/mi-cuenta/pedidos/`
  - `/mi-cuenta/direcciones/`
  - `/mi-cuenta/editar-cuenta/`
  - `/mi-cuenta/perder-password/` + reset flow

---

### Páginas legales (derivado del prompt 05) — NUEVO bloque, no estaba en CLAUDE.md

El contenido de **Términos y Condiciones v2.1** está listo (ver `DONE.md`). Falta la **maquetación + plomería** en el tema. Recomendación del prompt: opción C (Page editable + template dedicado + TOC dinámico).

Este bloque aplica a las **6 páginas legales** previstas — el template se construye una vez y se reutiliza:

- `/legal/terminos-y-condiciones/`
- `/legal/politicas-de-privacidad/`
- `/legal/politicas-de-envio/`
- `/legal/devoluciones-y-reembolsos/`
- `/legal/codigo-de-conducta/`
- `/legal/terminos-de-preventa/`

#### Trabajo de tema

- [ ] **`page-legal.php`** — template reutilizable con estructura `<aside class="legal-page__toc">` + `<article class="legal-page__content">` + breadcrumbs + bloque "Documentos relacionados".
- [ ] **Asignación automática** del template — filter `template_include` en `inc/setup.php` o `inc/woocommerce.php` que detecta páginas bajo `/legal/*` y aplica `page-legal.php`.
- [ ] **`assets/src/js/legal-toc.js`** — escanea los `<h2>` del contenido, genera TOC dinámico, scroll-spy con `IntersectionObserver` (no jQuery), colapso en mobile via `<details>`.
- [ ] **SCSS `_legal.scss`** — estilo editorial legal: fondos oscuros consistentes, párrafos ancho máx ~680px, sin colores decorativos en cuerpo, carmesí solo en título / borders / enlaces.
- [ ] **Schema `WebPage`** en JSON-LD desde `inc/seo.php` cuando la página esté bajo `/legal/*`.
- [ ] **Meta `<title>` + description** específicos por página legal (vía Rank Math o filter custom).
- [ ] **Enlace en footer** columna "Legal" con las 6 páginas.

#### Cuando el dueño confirme `[FLAT_RATE]` y `[FECHA_PUBLICACION]`

- [ ] Crear las 6 Pages en WP admin con slugs correctos bajo parent `legal`.
- [ ] Pegar el contenido del prompt 05 (T&C) — los otros 5 vendrán de prompts 06-10.
- [ ] Reemplazar placeholders en cada página.

---

### Catálogo / buscador autónomo One Piece — Binder OP ya operativo

**Estado actual:** el sibling **Binder OP** ya está construido y desplegado, escribiendo meta sin prefijo (`_color`, `_card_type`, `_is_alt_art`, `_set_full_code`, `_card_number`, `_rarity_code`, `_rarity`, `_image_filename`). 25 productos OP cargados en TestManager local (verificado 2026-05-03).

**Contrato de campos** (poblados por el Binder OP — el tema debe leerlos así):

| Meta | Valores | Notas |
|---|---|---|
| `_color` | `Red`, `Green`, `Blue`, `Purple`, `Black`, `Yellow`, duales `Red/Yellow`, `Red/Purple` | Filtrar con `LIKE` para soportar duales |
| `_card_type` | `LEADER` \| `CHARACTER` \| `EVENT` \| `STAGE` (uppercase) | Comparar con `IN`/`=` directo |
| `_is_alt_art` | `yes` \| `no` | Detectado por sufijo `_p[N]` en filename |
| `_set_full_code` | `OP15-EB04`, `OP-07`, `EB-01`, `PRB-02` | Difiere del SKU del producto (que tiene prefijo `OP-`) |
| `_card_number` | `EB04-002`, `OP07-001`, `P-044` | Sin prefijo `OP-` |
| `_rarity_code` | `L` \| `C` \| `UC` \| `R` \| `SR` \| `SEC` \| `TR` \| `P` \| `SP` | — |
| `_rarity` | `leader` \| `common` \| `rare` \| ... | Versión legible |
| `_image_filename` | `EB04-002.png`, `OP07-001_p1.png` | Si tiene `_p[N]` → es alt art |

> ⚠️ `_block_icon` NO está siendo poblado por el binder hoy (verificado en DB 2026-05-03). M-OP-filtros lo dejó **fuera del MVP** — reincorporable cuando el Binder lo popule.

**Lo hecho por M-OP-filtros (ver DONE.md)**:
- ✅ Panel de filtros (Color con duales, Card Type, Illustration Type) en `/shop/?set=one-piece-tcg`.
- ✅ State en URL como query params (`?op_color=Red,Green&op_type=LEADER`).
- ✅ Refresco AJAX sin recarga vía endpoint `onplay_filter` extendido (NO se creó `?op_partial=1` paralelo).
- ✅ SSR de checkboxes desde URL (CA-5).
- ✅ Compatible con jerarquía de categorías y con paginación.
- ✅ No-regresión Magic verificada server-side.

**Trabajo del tema todavía pendiente** (otros sub-módulos OP, no M-OP-filtros):

- [ ] **Skip Scryfall enrichment para SKUs OP-** en `inc/scryfall-enrich.php` (5 líneas: guard `if onplay_tcg_from_sku($sku) === 'op' return early`).
- [ ] **Ficha de producto OP** — mostrar `_color`, `_card_type`, `_card_number`, `_rarity`, alt-art badge si `_is_alt_art = yes`. Iconografía oficial pendiente de coordinar (¿qué fuente para los 6 colores?).
- ✅ **Endpoint AJAX de búsqueda OP-only** (M-OP-buscador) — implementado 2026-05-04 vía rama `tcg=op` en `inc/search.php` + detección de contexto en header. Ver DONE.md.
- [ ] **Si el Binder popula `_block_icon`**: reincorporar el filtro de Block Icon a M-OP-filtros (constante adicional + 1 grupo en `template-parts/op-filters/`, mismo patrón que los otros 3).

**Diferencia con M2 Magic:** Magic registra taxonomías (`tcg_color`, `tcg_rarity`, etc.) por performance a escala. OP queda con `meta_query` por consistencia con el contrato del Binder OP. CLAUDE.md §3.7 advirtió la degradación; aquí se acepta la deuda. Índices SQL recomendados en `docs/op-filters-sql-indexes.md` (aplicar cuando el catálogo OP supere ~500 productos).

---

### M9 — polish opcional (low priority)

- [ ] Autocomplete en el hero search del home (hoy submite `?q=` al listado).
- [ ] Fallback "Buscar en Scryfall" en autocomplete del header cuando `noResults`.
- [ ] Backfill `_onplay_set_code` como term_meta para usar Keyrune en `filters-sidebar.php` y `featured-sets.php` (hoy usan diamante monograma fallback).

---

## Ampliación del manager (opcional, depende del dueño)

§3.4 de CLAUDE.md propone agregar 6 meta al manager: `_scryfall_id`, `_set_code`, `_collector_number`, `_rarity`, `_is_foil`, `_manabox_id`.

- Si el dueño implementa el cambio: el tema migra de agrupación por prefijo de SKU a `_scryfall_id` directo (mayor precisión, menos transients).
- Si NO: el tema sigue con la estrategia actual (regex sobre SKU + cache transient `onplay_variants_{print_key}`).

---

## Tareas estructurales que NO son módulo pero conviene tener listas

- [ ] **Re-evaluar cap PDP de 460px** si el dueño re-sincroniza imágenes con `large` (672px) o `png` (745px). Ver §3.4 de CLAUDE.md.
- [ ] **Documentar comando `wp onplay:backfill-meta`** (cómo correrlo después del re-sync de manager si lo hace).
