# CLAUDE.md — Onplay.cl (Magic Singles Store) — v3

> **Propósito:** documento maestro para Claude Code. Define contexto, stack, convenciones y arquitectura del proyecto onplay.cl. Se lee al inicio de cada sesión. No sobreescribir sin registrar razón en la sección "Decisiones técnicas".

> **v3 (2026-04-19)** — Ajustada con el código real del onplay-manager y el modelo de datos confirmado en producción. Ver sección 11 para el cambio respecto a versiones anteriores.

---

## 1. Contexto del proyecto

**Onplay.cl** es un e-commerce especializado en la venta de **cartas individuales (singles)** de trading card games, partiendo con **Magic: The Gathering**. Es el sitio hermano de **onplaygames.cl** (productos sellados, accesorios, torneos) pero **completamente independiente**: stock separado, inventario separado, base de datos separada.

- **Propietario:** Onplay Games SpA — Merced 832, Local 54, Galería Casa Colorada, Santiago Centro, Chile
- **Estado actual del sitio:** WordPress 6.9.4 + WooCommerce 10.7.0 ya instalados en Hostinger, con 410 productos cargados por el onplay-manager. Tema activo actual: `blocksy-child`. El tema custom `onplay` se desarrollará en paralelo y activará cuando esté listo.
- **MVP alcance:** catálogo y carrito de Magic singles funcionando, con checkout Webpay + Mercado Pago (plugins ya instalados), retiro en tienda + envío flat rate Chilexpress. Sin facturación SII en el MVP.
- **Juegos futuros (post-MVP):** One Piece TCG, Pokémon TCG, Riftbound. La arquitectura debe permitirlo sin refactor mayor.

### Convenciones del sitio que NO se tocan

- **Tema viejo `onplay-child-theme v2.0.0` inactivo:** intento descartado. No usar ni referenciar.
- **Categorías bajo `parent=0`:** son **categorías de cartas promocionales**, no huérfanas. Excluir de cualquier query de limpieza.
- **Plugins ya configurados:** Mercado Pago, Transbank Webpay Plus, WooCommerce, Blocksy. No reinstalar, solo integrar en templates.

### Referencias de industria (paridad funcional)

- tcgplayer.com — agrupación de variantes en ficha, filtros
- cardkingdom.com — UX de compra, ficha de producto
- starcitygames.com — velocidad, densidad informativa
- hareruyamtg.com/en — multi-idioma y multi-TCG

---

## 2. Stack técnico

### Hosting e infraestructura

- **Hosting:** Hostinger (mismo plan que onplaygames.cl). Recursos compartidos limitados. Historial de HTTP 503 en onplaygames.cl por límite de procesos.
- **PHP:** 8.3.x
- **MySQL/MariaDB:** versión del hosting (no acceso root)
- **Dominio:** onplay.cl
- **SSL:** Let's Encrypt vía Hostinger

### WordPress core

- **WordPress:** 6.9.4+
- **WooCommerce:** 10.7.0+
- **Tema:** **custom from scratch** — slug: `onplay`. NO child theme de Blocksy. NO page builders.
- **Plugins imprescindibles (ya instalados y funcionando):**
  - WooCommerce
  - Mercado Pago for WooCommerce
  - Transbank Webpay Plus
  - Blocksy + Blocksy Companion (actualmente activos; se desactivan cuando se active el tema `onplay`)
- **Plugins a evaluar/eliminar en Módulo 0:**
  - `event-tickets` — es de onplaygames.cl, no va en este sitio; verificar si está en uso
  - `wc-customer-wallet` — verificar si hay saldos reales de clientes antes de eliminar
  - `stackable-ultimate-gutenberg-blocks` — pesado, evaluar si se usa
  - `woocommerce-products-filter (HUSKY)` — se reemplaza por filtros nativos del tema
- **Plugins a agregar (Módulo 10):**
  - Rank Math SEO (preferido sobre Yoast por peso)
  - LiteSpeed Cache o WP Super Cache
  - Wordfence Security
  - UpdraftPlus (backups)
  - Query Monitor (solo en dev)

### Integraciones externas

- **Scryfall API** (api.scryfall.com) — fuente secundaria para enriquecer datos que el manager no guarda (mana cost, texto oracle, tipo, legalidades). Sin API key. Rate limit: 10 req/seg. Se usa con caché permanente.
- **onplay-manager** — pipeline oficial de ingesta. Repo: https://github.com/Josborquez/onplay-manager. **Contrato documentado en detalle en sección 3.** Para cambios al manager, ver sección 3.3.
- **ManaBox** — app mobile externa (Jose Manuel la usa para escanear cartas y generar CSVs). No interactuamos con ella.

### Frontend del tema

- **Arquitectura:** tema clásico PHP + overrides WooCommerce + bloques Gutenberg custom.
- **CSS:** SCSS compilado a un único `style.css`. Tokens de diseño provienen del design file de Claude Design: `https://api.anthropic.com/v1/design/h/f03yAbQW7muzqRVdDJbvww`
- **JS:** vanilla JS + Alpine.js para interactividad (drawer, filtros, modales). No React/Vue en el tema público.
- **Build:** `package.json` con scripts `build` y `watch` (sass). Output a `/assets/dist/`.

---

## 3. Modelo de datos (EL CORAZÓN TÉCNICO — leer con atención)

### 3.1 Cómo funciona el pipeline real

El manager procesa CSVs de ManaBox con esta estructura de columnas:

```
Name, Set code, Set name, Collector number, Foil, Rarity, Quantity,
ManaBox ID, Scryfall ID, Purchase price, Misprint, Altered, Condition,
Language, Purchase price currency
```

**Cada fila del CSV genera 1 producto simple en WooCommerce** con SKU único.

**Flujo actual (código real en `server/routes/upload.js` + `server/routes/sync.js`):**

1. `upload.js` lee el CSV, convierte precio USD→CLP (`precio * dolar * 1.1`, redondeado a 100), construye URL de imagen Scryfall desde `Scryfall ID`, mapea `Condition` y `Language` a códigos/etiquetas, y envía al cliente un objeto simplificado.
2. Cliente muestra tabla editable (permite ajustar precio).
3. `sync.js` recibe batches de 50, resuelve categoría `Magic: The Gathering > {set_name}` (crea si no existe), obtiene IDs de atributos globales `pa_estado` y `pa_idioma`, y envía a la REST API de WooCommerce `products/batch` con `create: [...]`.

### 3.2 Qué guarda el manager HOY (verificado en código y en DB)

**Título del producto (`post_title`):**
- Non-foil: nombre limpio de la carta (ej. `Burst Lightning`)
- Foil: nombre con sufijo (ej. `Entomb (Foil)`)
- Doble cara: `Malakir Rebirth // Malakir Mire`

**SKU (`_sku`):** patrón `{SET_CODE}-{COLLECTOR_NUMBER}-{CONDITION_CODE}-{LANGUAGE_CODE}`
- Ejemplo: `SOA-106-NM-EN`, `THB-262-NM-ES`, `F16-9-NM-ES`
- **Foil NO está en el SKU**. Se codifica en el título y en el slug (`entomb-foil`).

**Slug (`post_name`):** kebab-case del nombre, con sufijos `-2`, `-3` para duplicados de nombre, y `-foil` si es foil.

**Meta WooCommerce estándar:**
- `_regular_price`, `_price` (en CLP)
- `_stock`, `_stock_status`, `_manage_stock = yes`
- `_thumbnail_id` (imagen descargada desde URL Scryfall al Media Library)
- `_sku` (estructurado, ver arriba)

**Categorías (`product_cat`):** `Magic: The Gathering > {set_name}` jerárquicas.

**Atributos globales (`pa_estado`, `pa_idioma`):**
- Valores actuales en DB: `pa_estado` tiene solo `NM`; `pa_idioma` tiene EN (264), ES (129), JA (7), ZH (7), PT (2), IT (1).
- ⚠️ **Bug conocido:** producto ID 10407 (Lamentation) tiene estos atributos serializados con `value: ""`. Causa probable: race condition en `initGlobalAttributes()` del manager. Los productos creados después del warm-up sí reciben la asignación correcta como taxonomía global. Esto se verifica con `wp db query "SELECT COUNT(*) FROM wp_term_relationships tr JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id=tr.term_taxonomy_id WHERE tt.taxonomy='pa_idioma'"` comparado con 410 productos totales.

### 3.3 Qué NO guarda el manager hoy (pero el CSV SÍ trae)

**Campos perdidos del CSV:**
- `Scryfall ID` — el manager lo lee solo para construir URL de imagen
- `Set code` — implícito en SKU, no como meta
- `Collector number` — implícito en SKU, no como meta
- `Rarity` — se lee pero no se usa
- `ManaBox ID` — no se lee
- `Foil` (como flag booleano) — solo en título
- `Misprint`, `Altered` — no se leen

### 3.4 Ampliación propuesta al manager (opcional pero recomendada)

Para evitar dependencia total de Scryfall API en runtime y habilitar filtros por rareza/foil, el manager debería guardar 6 meta adicionales. El cambio es trivial (10 líneas) y el dueño del proyecto (Jose Manuel) puede hacerlo.

**En `server/routes/upload.js`** — agregar al objeto `procesados`:
```javascript
scryfall_id:       row["Scryfall ID"],
set_code:          row["Set code"],
collector_number:  row["Collector number"],
rarity:            row["Rarity"],
is_foil:           row["Foil"] === "foil",
manabox_id:        row["ManaBox ID"],
```

**En `server/routes/sync.js`** — agregar al payload de cada producto:
```javascript
meta_data: [
  { key: "_scryfall_id",      value: p.scryfall_id },
  { key: "_set_code",         value: p.set_code },
  { key: "_collector_number", value: p.collector_number },
  { key: "_rarity",           value: p.rarity },
  { key: "_is_foil",          value: p.is_foil ? "yes" : "no" },
  { key: "_manabox_id",       value: p.manabox_id },
],
```

**Para los 410 productos existentes**, el tema provee un comando WP-CLI `wp onplay:backfill-meta` que:
1. Lee el SKU → extrae `set_code` y `collector_number` por regex
2. Llama a Scryfall API `/cards/{set}/{collector_number}` para obtener `scryfall_id`, `rarity`
3. Infiere `is_foil` del título (`(Foil)`) y del slug
4. Actualiza los meta del producto
5. Cachea también `mana_cost`, `type_line`, `oracle_text`, `colors`, `legalities` (ver sección 3.6)

### 3.5 Clave de agrupación de variantes

La "impresión" (misma carta, mismo set, mismo número) se identifica por:

**Si el manager guarda `_scryfall_id` (futuro):** usar `_scryfall_id` directo — máxima precisión.

**Hoy (sin `_scryfall_id`):** usar **prefijo del SKU** — los primeros dos segmentos:
```php
preg_match('/^([A-Z0-9]+)-(\d+)/', $sku, $m);
$print_key = $m[1] . '-' . $m[2]; // "THB-262"
```

Dos productos con el mismo `print_key` son la misma impresión, distintas variantes por condición/idioma/foil.

### 3.6 Datos enriquecidos desde Scryfall (responsabilidad del tema)

El manager no guarda `mana_cost`, `type_line`, `oracle_text`, `colors`, `color_identity`, `legalities`, `artist`. El tema los obtiene de Scryfall y los cachea en meta `_onplay_*` (prefijo `_onplay_` deja claro que es del tema).

**Cuándo fetchear:**
- En la primera vista de un producto cuyo `_onplay_oracle_text` no existe, un hook `wp_footer` encola un job async (o simplemente hace el fetch en `init` con caché transient de respaldo para evitar fetches repetidos).
- El comando `wp onplay:backfill-meta` procesa todos los productos existentes.

**Endpoint usado:** `https://api.scryfall.com/cards/{set_code_lowercase}/{collector_number}` — no requiere API key. Rate limit: esperar 100ms entre requests. Guardar solo campos utilizados por el tema.

**Meta enriquecidos cacheados:**
- `_onplay_scryfall_id` (si no lo guarda el manager)
- `_onplay_mana_cost`
- `_onplay_cmc`
- `_onplay_type_line`
- `_onplay_oracle_text`
- `_onplay_colors` (JSON encoded)
- `_onplay_color_identity` (JSON encoded)
- `_onplay_legalities` (JSON encoded)
- `_onplay_artist`
- `_onplay_scryfall_uri`
- `_onplay_scryfall_cached_at` (timestamp, permite re-sync manual)

### 3.7 Taxonomías custom del tema (para filtros facetados)

Las queries por `meta_query` se degradan a escala. Las taxonomías WP son rápidas. El tema registra taxonomías ligeras que se populan automáticamente desde los meta enriquecidos mediante un hook `save_post_product`:

- `tcg_color` (WUBRG + Colorless + Multicolor) — desde `_onplay_colors`
- `tcg_rarity` (common, uncommon, rare, mythic) — desde `_rarity` o `_onplay_rarity`
- `tcg_type` (Creature, Instant, Sorcery, etc.) — parseado de `_onplay_type_line`
- `tcg_format_legal` (Standard, Pioneer, Modern, Legacy, Vintage, Commander, Premodern, Pauper) — desde `_onplay_legalities`
- `tcg_foil` (yes/no) — desde `_is_foil` o detectado por sufijo `(Foil)` del título

**Nota:** `pa_estado` y `pa_idioma` (atributos globales del manager) se usan directamente para filtros de condición e idioma. No se duplican en taxonomías custom.

### 3.8 Queries clave del tema

**a) Variantes de la misma impresión** (tabla en ficha):
```php
// Futuro — cuando _scryfall_id esté en meta:
$args = [
    'post_type' => 'product',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => [ ['key' => '_scryfall_id', 'value' => $current_scryfall_id] ],
    'post__not_in' => [ $current_product_id ],
];

// Hoy — agrupación por prefijo de SKU:
// En un hook posts_where, se filtra por SKU LIKE '{print_key}-%'.
// Alternativa más simple: meta_query sobre _sku con LIKE custom.
```
Cachear con transient `onplay_variants_{print_key}` por 1 hora. Invalidar en `save_post_product` del mismo print_key.

**b) Otras impresiones de la misma carta** (otros sets):
Query por `post_title` (agrupando por nombre normalizado sin `(Foil)`), excluyendo el mismo print_key.

**c) Listado con filtros facetados:**
Queries por taxonomía (rápidas). Ver sección 5.2.

---

## 4. Arquitectura del tema

```
wp-content/themes/onplay/
├── style.css                         # Header WP + import al bundle
├── functions.php                     # Bootstrap
├── index.php
├── header.php
├── footer.php
├── front-page.php                    # Home custom
├── searchform.php
├── assets/
│   ├── src/
│   │   ├── scss/
│   │   │   ├── main.scss
│   │   │   ├── _tokens.scss          # Variables CSS del design file Claude Design
│   │   │   ├── _reset.scss
│   │   │   ├── _typography.scss
│   │   │   ├── _layout.scss
│   │   │   ├── components/
│   │   │   │   ├── _button.scss
│   │   │   │   ├── _card-product.scss
│   │   │   │   ├── _cart-drawer.scss
│   │   │   │   ├── _filters.scss
│   │   │   │   ├── _variant-table.scss
│   │   │   │   └── _mana-symbols.scss
│   │   │   └── templates/
│   │   │       ├── _home.scss
│   │   │       ├── _shop.scss
│   │   │       ├── _single-product.scss
│   │   │       └── _checkout.scss
│   │   └── js/
│   │       ├── main.js
│   │       ├── cart-drawer.js
│   │       ├── filters.js
│   │       ├── variant-table.js
│   │       ├── search-autocomplete.js
│   │       └── lazy-images.js
│   └── dist/                         # Compilado
├── inc/
│   ├── setup.php                     # theme supports, menus, sidebars
│   ├── enqueue.php
│   ├── taxonomies.php                # registro tcg_color, tcg_rarity, etc.
│   ├── meta-to-taxonomy.php          # hook save_post_product → taxonomías
│   ├── scryfall-enrich.php           # fetch y caché de Scryfall
│   ├── woocommerce.php               # overrides de hooks WC
│   ├── search.php                    # endpoint AJAX autocomplete
│   ├── filters-ajax.php              # endpoint AJAX filtros
│   ├── variants-query.php            # queries de agrupación
│   ├── cli.php                       # comandos WP-CLI (backfill-meta, etc.)
│   ├── shortcodes.php
│   ├── blocks.php
│   └── security.php
├── blocks/                           # bloques Gutenberg custom
│   ├── hero-search/
│   ├── featured-cards/
│   └── set-showcase/
├── template-parts/
│   ├── header/
│   ├── footer/
│   ├── product-card.php
│   ├── variant-table.php
│   ├── other-printings.php
│   ├── filters-sidebar.php
│   └── cart-drawer.php
├── woocommerce/                      # overrides de templates WC
│   ├── archive-product.php
│   ├── content-product.php
│   ├── single-product.php
│   ├── single-product/
│   │   ├── add-to-cart/
│   │   │   └── simple.php
│   │   └── ...
│   ├── cart/
│   └── checkout/
└── package.json
```

---

## 5. Funcionalidades del MVP

### 5.1 Búsqueda

- Input principal en header, siempre visible, centrado.
- Autocomplete AJAX (`inc/search.php`) con match sobre `post_title`. Debounce 200ms. Hasta 8 resultados.
- Agrupación de resultados por `print_key`: 50 SKUs de Lightning Bolt no son 50 resultados, son 1 por impresión (Lightning Bolt MH2, Lightning Bolt LEA, etc.), cada uno con "desde $X.XXX".
- Fallback: "Buscar en Scryfall" si no hay resultados locales.

### 5.2 Listado / Archive

- Plantilla: `woocommerce/archive-product.php`.
- Sidebar izquierdo con filtros facetados AJAX:
  - Set (product_cat, multi-select con buscador)
  - Color (`tcg_color` checkboxes WUBRG + colorless + multicolor)
  - Rareza (`tcg_rarity` checkboxes)
  - Tipo (`tcg_type` checkboxes)
  - Condición (`pa_estado` — hoy solo NM, pero la UI se deja lista)
  - Idioma (`pa_idioma`)
  - Foil (`tcg_foil` toggle)
  - Solo en stock (toggle default ON — `_stock_status = 'instock'`)
  - Rango de precio CLP (slider)
  - Formato legal (`tcg_format_legal`)
- **Agrupación visual del listado:** 1 card por impresión (print_key), mostrando el precio mínimo en stock. Opción "ver todas las variantes" expande.
- Grid responsivo: 6 cols desktop, 4 tablet, 2 mobile.
- Ordenar: precio asc/desc, nombre A-Z, recién ingresado.

### 5.3 Ficha de producto — EL CORAZÓN DEL SITIO

Layout híbrido TCGPlayer/Card Kingdom. URL apunta al SKU específico; la ficha muestra agrupación.

**Estructura vertical:**

```
┌──────────────────────────────────────────────────────────────┐
│  BREADCRUMBS (Home > Magic > Modern Horizons 2)              │
├────────────────────────┬─────────────────────────────────────┤
│                        │  Lightning Bolt                     │
│                        │  {R} · Instant                      │
│   [IMAGEN GRANDE       │  Modern Horizons 2 · #125 · Common │
│    ZOOM HOVER]         │                                     │
│                        │  "Lightning Bolt deals 3 damage..." │
│                        │                                     │
│                        │  Legal: Modern, Legacy, Vintage...  │
├────────────────────────┴─────────────────────────────────────┤
│  VARIANTES EN STOCK                                          │
│  ┌────────┬────────┬───────┬──────────┬──────┬────────┐      │
│  │ Cond.  │ Idioma │ Foil  │ Precio   │ Stock│ Acción │      │
│  ├────────┼────────┼───────┼──────────┼──────┼────────┤      │
│  │ NM     │ EN     │ No    │ $1.500   │ 4    │ [+ Ag] │      │
│  │ NM     │ ES     │ No    │ $1.800   │ 1    │ [+ Ag] │      │
│  │ NM     │ EN     │ Foil  │ $8.500   │ 1    │ [+ Ag] │      │
│  └────────┴────────┴───────┴──────────┴──────┴────────┘      │
│  Fila actual destacada (SKU de la URL)                       │
├──────────────────────────────────────────────────────────────┤
│  OTRAS IMPRESIONES DE ESTA CARTA                             │
│  [carrusel con otros sets]                                   │
└──────────────────────────────────────────────────────────────┘
```

**Detalles de la tabla:**
- Solo variantes con stock > 0.
- Fila del SKU actual destacada.
- Cantidad editable antes de agregar.
- AJAX add-to-cart, actualiza drawer sin recargar.
- Click en fila ≠ cambiar de página. "Agregar" es el botón de acción. Ícono "ver" opcional al final de fila lleva a URL propia del SKU.
- Orden: precio ascendente default.

**Info principal:**
- Mana cost con íconos SVG (Scryfall / Keyrune Font).
- Texto oracle con reemplazo de `{R}` → ícono.
- Badges de formato legal clicables.
- Set con ícono.

**SEO:**
- Cada SKU indexable con canonical a sí mismo.
- Schema.org `Product` con precio, disponibilidad, SKU, brand (el set).
- Title: `Lightning Bolt (NM, EN) - Modern Horizons 2 | Onplay.cl`.

### 5.4 Carrito

Drawer lateral derecho, items compactos (miniatura 60px, nombre con variante, cantidad, subtotal, eliminar). El nombre en el drawer incluye condición/idioma/foil de forma legible.

### 5.5 Checkout

- Un paso. Layout 2 columnas (campos + resumen sticky).
- Secciones: Contacto, Despacho, Pago, Resumen.
- Despacho: retiro en Merced 832 (gratis) o Chilexpress flat rate.
- Pago: Webpay Plus o Mercado Pago (plugins ya configurados).
- Validación RUT chileno (módulo 11).
- Captura RUT para fase 2 (SII).

### 5.6 Cuenta

Mínimo: registro, login, recuperación, historial de pedidos, direcciones. Sin social login. Wishlist en fase 2.

---

## 6. Roadmap por módulos

### Módulo 0 — Auditoría y limpieza previa
Antes de escribir una sola línea de código del tema:
- Verificar estado real de plugins innecesarios (`event-tickets`, `wc-customer-wallet`, `stackable-ultimate-gutenberg-blocks`)
- Confirmar que los 410 productos tienen atributos correctos (investigar bug Lamentation)
- Decidir con el dueño: ¿se amplía el manager (sección 3.4) antes o después del MVP?
- Configurar ambiente local de desarrollo (LocalWP/DDEV) con DB copiada de producción

**Aceptación:** lista de plugins a eliminar documentada, decisión manager documentada en sección 11, ambiente local levantado con copia de DB.

### Módulo 1 — Fundación del tema
Scaffolding. `style.css`, `functions.php`, `/inc`, assets. Tokens del design file de Claude Design. Build SCSS. Header y footer sin funcionalidad.

**Aceptación:** tema activable sin errores en dev, assets cargando, tokens visibles en DevTools, logo JPG2 en header.

### Módulo 2 — Enriquecimiento Scryfall + Taxonomías custom
- `inc/scryfall-enrich.php` con función `onplay_enrich_from_scryfall($product_id)` que fetchea datos faltantes y los guarda como `_onplay_*`.
- Hook async (cron/wp_schedule_single_event) para enriquecer productos nuevos.
- Registro de taxonomías `tcg_color`, `tcg_rarity`, `tcg_type`, `tcg_format_legal`, `tcg_foil`.
- Hook `save_post_product` que popula las taxonomías desde los meta enriquecidos.
- Comando `wp onplay:backfill-meta` que procesa los 410 productos existentes (cuidar rate limit de Scryfall: 100ms entre requests, batch de 50 con progreso visible).

**Aceptación:**
1. Correr `wp onplay:backfill-meta` en ambiente de dev (DB copia de producción) sin errores.
2. Al finalizar, los 410 productos tienen meta `_onplay_mana_cost`, `_onplay_type_line`, `_onplay_oracle_text`, `_onplay_colors`, `_onplay_legalities`.
3. Taxonomías `tcg_color`, `tcg_rarity`, etc. tienen términos asignados en cantidad consistente (ej. `tcg_color[Red]` tiene al menos 1 producto).
4. Un producto nuevo creado por el manager se enriquece automáticamente en <5 minutos.

### Módulo 3 — Templates de producto (card + ficha sin agrupación)
Override de `archive-product.php`, `content-product.php`, `single-product.php`. Ficha con info básica de carta (imagen, mana cost con íconos, texto oracle renderizado, set, badges de formato). Usar add-to-cart default de WC por ahora.

**Aceptación:** productos reales del catálogo se ven según mockup del design file. Íconos de mana renderizan. Badges de formato funcionan.

### Módulo 4 — Tabla de variantes agrupada (print_key)
Función `onplay_get_variants_by_print_key($sku)` con cache transient. Template `template-parts/variant-table.php`. Reemplazo del add-to-cart. AJAX con drawer.

**Aceptación:** en una ficha con 3+ variantes de misma impresión, la tabla muestra solo las en stock, ordena por precio, add-to-cart AJAX actualiza drawer. Cache invalidada al guardar cualquier producto con mismo `print_key`.

### Módulo 5 — Otras impresiones + Búsqueda
- Sección "Otras impresiones de esta carta" (query por `post_title` normalizado, excluyendo `print_key` actual).
- Input de búsqueda en header funcional.
- Endpoint AJAX autocomplete con debounce.
- Resultados agrupados por `print_key`.

**Aceptación:** autocomplete en <500ms con agrupación. Otras impresiones visibles cuando existen.

### Módulo 6 — Listado con filtros facetados
Sidebar de filtros basado en taxonomías. URLs amigables con query params. Sin recarga. Agrupación visual del listado por `print_key`.

**Aceptación:** combinar 3 filtros muestra correctos. URL shareable. Back del navegador funciona. "Lightning Bolt" muestra 1 card por impresión, no 10 por condición.

### Módulo 7 — Carrito y drawer
Drawer con operaciones full. Página `/carrito/` customizada. Nombre del item incluye variante.

**Aceptación:** agregar/modificar/eliminar refleja inmediato, subtotal correcto, nombre claro.

### Módulo 8 — Checkout custom
Override templates. Un paso. Validación RUT. Integración con Mercado Pago + Transbank (plugins ya configurados).

**Aceptación:** pedido test en sandbox de ambos gateways end-to-end, email de confirmación, pedido en WP admin con todos los campos.

### Módulo 9 — Home
Front-page con hero de búsqueda, recién ingresados, por set, confianza, footer.

**Aceptación:** home carga <2s, secciones pobladas con datos reales.

### Módulo 10 — Performance, SEO, seguridad, lanzamiento
- Lighthouse mobile >85 en Home/Archive/Single.
- Rank Math configurado con schemas.
- Wordfence + headers de seguridad.
- Respaldo completo pre-lanzamiento.
- Switch de tema `blocksy-child` → `onplay` en producción.

**Aceptación:** Lighthouse targets cumplidos, schemas validados, WPO auditado, backup verificado, switch exitoso.

---

## 7. Seguridad

- Actualizaciones automáticas de core y plugins críticos.
- Wordfence con firewall, login rate limiting.
- `define('DISALLOW_FILE_EDIT', true);` en wp-config.php.
- Ocultar versión WP, remover generator meta.
- 2FA para admins.
- Headers: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP, HSTS.
- Un único admin real (Jose Manuel).
- Respaldos diarios UpdraftPlus → Google Drive.
- **REST API del manager:** crear key WooCommerce **dedicada** con permisos `read_write`, rotar anualmente. No usar credenciales compartidas con otros integraciones.
- HTTPS forzado con HSTS.
- Sanitización/escape riguroso en todo código custom (`sanitize_*`, `esc_*`, `wp_nonce_*`, `$wpdb->prepare`).

---

## 8. Performance y SEO

### Performance (Lighthouse mobile >85)

- WebP para imágenes; las de Scryfall son externas → considerar mirror local si latencia es problema.
- Lazy loading nativo.
- Critical CSS inline.
- JS diferido.
- **Caching de página con cuidado:** el manager hace escrituras batch de 50. Configurar el plugin de caché para NO purgar en cada write sino al final del batch. Si no es configurable, hook que pause purge durante sync y lo ejecute una vez al terminar.
- Transients para queries de agrupación.
- Taxonomías (rápidas) en lugar de meta_query (lento).
- Cloudflare proxy.

### SEO

- Rank Math (por peso).
- URLs amigables.
- Schema.org `Product` con set como brand, `Organization`, `BreadcrumbList`, `SearchAction`.
- Sitemap XML.
- Canonical.
- OG + Twitter Cards.
- Duplicación del texto oracle (viene de Scryfall, miles de sitios lo tienen): aceptar para el 95% del catálogo, enriquecer con descripción única solo para las top 100 cartas.

---

## 9. Convenciones de código

### PHP
- PSR-12 adaptado WP.
- Namespace `Onplay\`.
- `/inc/` registra hooks al incluirse.
- Prefijo funciones globales: `onplay_`.
- **Meta del manager:** nombres como están (`_sku`, `_regular_price`, etc.).
- **Meta propios del tema:** prefijo `_onplay_` (ej. `_onplay_oracle_text`).
- Text domain `onplay`.

### SCSS
- BEM (`.product-card`, `.product-card__image`, `.product-card--foil`).
- CSS vars en `:root`.
- Sin `!important` salvo override puntual documentado.

### JS
- ES modules.
- `fetch` nativo.
- Event delegation.
- Namespace `onplay` global.

### Git
- `main` estable. Features en `feature/modulo-X-nombre`.
- Antes de merge: build + smoke test + consola sin errores.

### Consistencia con el manager
- El tema **no** modifica el manager desde este proyecto.
- Cambios al manager los coordina el dueño (ver sección 3.4).
- Si hay tensión, **el manager gana**; se ajusta el tema.

---

## 10. Tokens de diseño

> **Fuente:** design file de Claude Design `https://api.anthropic.com/v1/design/h/f03yAbQW7muzqRVdDJbvww`. Claude Code debe fetchearlo, leer su README, y reemplazar el placeholder de abajo con los tokens reales.

**Placeholder inicial (reemplazar en Módulo 1):**

```css
:root {
  /* Colores (derivados de logo JPG2 — bulldog brindle con diamante rojo) */
  --color-primary: #D62828;
  --color-bg: #0A0A0A;
  --color-surface: #1A1A1A;
  --color-border: #2A2A2A;
  --color-text: #F5F5F5;
  --color-text-muted: #6B6B6B;
  --color-accent-gold: #E8B04B;
  --color-success: #3FB950;
  --color-warning: #F59E0B;

  /* Tipografía */
  --font-display: 'Bebas Neue', 'Oswald', sans-serif;
  --font-body: 'IBM Plex Sans', system-ui, sans-serif;
  --font-mono: 'JetBrains Mono', ui-monospace, monospace;

  /* Espaciado (4px base) */
  --space-1: 4px; --space-2: 8px; --space-3: 12px; --space-4: 16px;
  --space-6: 24px; --space-8: 32px; --space-12: 48px; --space-16: 64px;

  /* Layout */
  --radius-sm: 4px; --radius-md: 8px; --radius-lg: 12px;
  --shadow-card: 0 2px 8px rgba(0,0,0,0.4);
}
```

---

## 11. Decisiones técnicas (log)

- `2026-04-18` Custom theme en vez de child theme de Blocksy. Razón: control total de performance.
- `2026-04-18` Sin React/Vue en el tema. Alpine.js para interactividad.
- `2026-04-18` Sin wishlist ni SII en MVP.
- `2026-04-19` Productos simples (1 SKU por variante) en vez de variables. Razón: es lo que el manager produce y el manager es el pipeline oficial.
- `2026-04-19` Categorías WC nativas (`Magic > Set`) en vez de taxonomía `tcg_set`. Razón: es lo que el manager crea.
- `2026-04-19` Bridge meta→taxonomía para filtros vía hook `save_post_product`. Taxonomías rápidas; el manager no cambia.
- `2026-04-19` Ficha híbrida TCGPlayer: URL por SKU + tabla agrupada por `print_key`. Razón: mejor UX + SEO.
- `2026-04-19` **Clave de agrupación es prefijo del SKU** (`set_code-collector_number`), no `_scryfall_id`, porque el manager hoy no guarda `_scryfall_id`.
- `2026-04-19` **Enriquecimiento vía Scryfall API desde el tema** (sección 3.6) para datos que el manager no guarda (mana_cost, texto oracle, colores, legalidades). Cacheado como `_onplay_*` con comando WP-CLI `wp onplay:backfill-meta` para cubrir los 410 existentes.
- `2026-04-19` **Ampliación del manager pendiente de decisión** (sección 3.4): agregar 6 meta desde CSV ManaBox. Impacto positivo pero no bloqueante.
- `2026-04-19` Bug del producto 10407 (Lamentation) con atributos vacíos: se asume race condition en `initGlobalAttributes()` del manager. Estimar impacto real con comando de auditoría en Módulo 0.
- `2026-04-19` **Módulo 5 — fusión de "Variantes en stock" + "Otras impresiones de esta carta" en una sola tabla agrupada por nombre normalizado de carta** (`inc/variants-query.php` → `onplay_get_variants_by_card`). El diseño original del PDP define una sola tabla "Variantes disponibles" con chips de filtro Condición/Foil que cubre todas las impresiones del mismo nombre, no dos secciones separadas como sugería CLAUDE.md sec 5.3. Razón: paridad con el design file y mejor UX (un solo lugar para comparar todas las opciones). **Rollback:** restaurar la query separada por `print_key` en una función `onplay_get_variants_by_print_key` y reintroducir la sección "otras impresiones" como `template-parts/other-printings.php`. La función actual ordena con el `print_key` actual primero (líneas 154-179 de `variants-query.php`), así que el comportamiento "current first" se conserva.
- `2026-04-19` **Módulo 5 — fallback "Buscar en Scryfall" pendiente.** El render del autocomplete (`assets/src/js/main.js:402-409`) muestra solo `noResults: 'Sin resultados locales'` sin link externo. Se completará en una iteración menor o como parte del Módulo 10 (SEO/lanzamiento).
- `2026-04-19` **Módulo 6 — counts facetados solo en filtro de Set, no Amazon-style en todos los filtros.** El diseño original (`Onplay.cl/listing.jsx:128`) muestra el contador `(N)` únicamente en cada set, calculado sobre el catálogo total (no recalculado al cambiar otros filtros). Razón: fidelidad al diseño + simpleza (evita N+1 queries por request). **Rollback:** ampliar la función `onplay_facet_counts()` para devolver counts por taxonomía aplicando los demás filtros, y renderizar `(N)` en color/rareza/condición/idioma.
- `2026-04-19` **Módulo 6 — paginación con agrupación por `print_key` se hace en PHP, no en SQL.** Estrategia: cargar todos los IDs que matchean los filtros (in-stock/tax_query/meta_query), colapsar en PHP por `print_key` quedándose con el SKU más barato como representante, paginar el array resultante. OK para catálogo ≤ ~1000 productos (hoy 410). **Rollback / migración a SQL:** si la query base supera ~50ms o el catálogo crece, mover el colapso a una subquery SQL con `GROUP BY` sobre el prefijo del SKU (helper aislado en `onplay_collapse_products_by_print_key()` para que el reemplazo sea local).
- `2026-04-19` **Módulo 6 — desvíos de CLAUDE.md sec 5.2 por fidelidad al diseño:** (a) grid de 4 columnas en desktop (no 6); (b) sin filtros de Tipo (`tcg_type`) ni Formato legal (`tcg_format_legal`) — los registramos como taxonomías para uso futuro pero no exponemos UI en el sidebar del MVP; (c) Foil es toggle de 3 opciones (Todo/Regular/Foil), no un checkbox simple; (d) sin opción "Multicolor" en el filtro de colores (el diseño solo tiene WUBRG + Colorless). **Rollback:** los filtros faltantes pueden agregarse al `template-parts/filters-sidebar.php` consumiendo las taxonomías ya registradas en `inc/taxonomies.php` sin tocar el endpoint AJAX (que ya soporta tax_query genérica).
- `2026-04-19` **Módulo 6 — sin librerías externas para slider de precio ni combobox de sets.** Vanilla JS para ambos. Razón: regla "sin React/Vue, Alpine solo si imprescindible" + control total de tamaño de bundle. **Rollback:** si el componente vanilla se vuelve costoso de mantener, evaluar `nouislider` (~12KB) o `Choices.js` para el combobox.
- `2026-04-19` **Módulo 6 — toggle "Solo en stock" default ON.** El listado por defecto oculta productos sin stock (consistente con el diseño y con la realidad operativa de un catálogo de singles). El usuario puede destogglear para ver todo.
- `2026-04-19` **Módulo 6 — el form de búsqueda del header apunta a la shop page con param `q`, no a `/?s=`.** El Enter nativo del input ahora va a `wc_get_page_permalink('shop') . '?q=...'`. Razón: `/?s=...` dispara la búsqueda nativa de WP que no usa nuestro `woocommerce/archive-product.php`, así que queda sin agrupación por `print_key` ni filtros ni estilos — contradiciendo el criterio de aceptación "Lightning Bolt muestra 1 card por impresión". El endpoint `onplay_filters_run` ya aceptaba el param `q` (lo mapea a `WP_Query::$args['s']` en `filters-ajax.php:201-203`), así que el cambio es solo cliente: `header.php` cambia `action` + `name="s"→name="q"` + `value` lee de `$_GET['q']`. **Rollback:** volver `action` a `home_url('/')` y `name` a `s` deja la búsqueda rota pero restaura el comportamiento nativo de WP para integraciones futuras que dependan de él.

---

## 12. Cómo interactuar con Claude Code en este proyecto

- Empezar cada sesión: "estoy trabajando en el módulo X". Claude Code lee este CLAUDE.md primero.
- Al terminar un módulo: actualizar sección 11 con lo que cambió.
- No marcar módulo como implementado sin verificar criterios de aceptación.
- Contradicción código vs este documento: detener, preguntar al dueño, resolver, actualizar aquí.
- Contradicción tema vs manager: manager gana. Documentar ajuste en sección 11.
- Antes del Módulo 2: **verificar en ambiente local con DB copia** que los 410 productos existen con los meta documentados en sección 3.2. Ejecutar `wp post meta list <ID>` en 5-10 productos muestra antes de empezar.
- Antes del Módulo 8: **el checkout nunca se prueba primero en producción**. Sandbox de Transbank y Mercado Pago (keys de test en ambiente local), verificación end-to-end, luego switch a producción.
