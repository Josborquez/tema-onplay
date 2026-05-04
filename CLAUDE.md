# CLAUDE.md — Onplay.cl (Magic Singles Store) — v3

> **Propósito:** documento maestro para Claude Code. Define contexto, stack, convenciones y arquitectura del proyecto onplay.cl. Se lee al inicio de cada sesión. No sobreescribir sin registrar razón en la sección "Decisiones técnicas".

> **v3 (2026-04-19)** — Ajustada con el código real del onplay-manager y el modelo de datos confirmado en producción. Ver sección 11 para el cambio respecto a versiones anteriores.

---

## 1. Contexto del proyecto

**Onplay.cl** es un e-commerce especializado en la venta de **cartas individuales (singles)** de trading card games, partiendo con **Magic: The Gathering**. Es el sitio hermano de **onplaygames.cl** (productos sellados, accesorios, torneos) pero **completamente independiente**: stock separado, inventario separado, base de datos separada.

- **Propietario (datos legales reales):**
  - **Razón social:** Comercializadora y Distribuidora BM Limitada
  - **RUT:** 77.862.085-5
  - **Giro:** Comercializadora de juegos de entretención
  - **Domicilio tributario:** Merced 832, Local 53, Santiago Centro, Chile
  - **Dirección física de atención:** Merced 832, Local 54, Galería Casa Colorada, Santiago Centro, Chile
  - **Email de contacto:** contacto@onplay.cl
  - **WhatsApp:** +56 9 6682 6121
  - **Condición comercial:** tienda oficial de **Wizards Play Network (WPN)**, **The Pokémon Company** y **Bandai**. Comercializa productos originales bajo los programas oficiales de estos titulares. Magicsur.cl es referencia de industria para el fraseo de esta condición.
  - ⚠️ **NO confundir:** "Onplay Games SpA" era un placeholder de v1/v2 de este documento. La razón social real y correcta es **Comercializadora y Distribuidora BM Limitada**. Toda documentación legal (términos, privacidad, SEO schemas, facturación) debe usar esta razón social + RUT.
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

---

## 11.b. Cierre de sesión 2026-04-19 — Estado y pendientes

### Hecho hoy (sesión completa)
- **M9 core del home** (commits `9998259` → `4669b13` → `1036270` → `75e3ae9` → `735655d`): front-page + template-parts (`hero.php`, `trust-band.php`, `featured-sets.php`, `recent-cards.php`), helpers `onplay_home_get_*`, enqueue de Bebas+IBM Plex+Playfair+Keyrune+mana-font.
- **M9 iconografía oficial**: Keyrune para símbolos de set (`<i class="ss ss-...">`), mana-font para símbolos de mana (`<i class="ss ms-... ms-cost ms-shadow">`) — reemplazó el stack de SVGs inline + fondos de color. Híbridos y phyrexianos ahora se renderizan como símbolo combinado.
- **M9 recientes**: orden `price-desc` dentro del pool de 150 más nuevos en stock + carrusel horizontal con scroll-snap + flechas `←/→` wireadas por módulo JS `RecentCarousel`.
- **M9 hero reconstruido** fiel al diseño `Onplay.cl/home.jsx` variante "hybrid": 2-col con stack flotante de 5 cartas animado, preview "Populares ahora" server-side, stats reales de DB cacheados 1h.
- **M9 PDP — imagen capada a 460px** tras 3 iteraciones (commits `ffcfd8d` → `366c5d3` → `7e5ed4b`). Evita upscale desde la fuente Scryfall `normal` (488px).

### Criterios de aceptación M9 cumplidos
- Home carga con datos reales (hero · trust · featured sets · recent). ✓
- Secciones pobladas desde el catálogo (410 productos). ✓
- Fidelidad al diseño: hero hybrid, glow, Playfair italic, stack flotante con animación. ✓
- Iconografía correcta (Keyrune + mana-font). ✓
- Imagen PDP sin upscale ni bandas. ✓

### Pendiente para mañana
1. **Validar Lighthouse mobile del home** en local (target M10: ≥85). El hero carga 5 imágenes Scryfall + stack animado + 4 filas preview + Playfair externa — puede pegarle a LCP/CLS.
2. **Decidir re-sync de imágenes Scryfall**: el cap PDP de 460px existe sólo porque el manager guarda `normal` (488px). Si el dueño re-sincroniza con `large` (672px) o `png` (745px), se puede subir el cap a 560–620px. Coordinar con Jose Manuel (ver §3.4, depende de que toque el manager).
3. **M10 — arranque**: instalar Rank Math, Wordfence, UpdraftPlus, LiteSpeed Cache (o WP Super Cache). Configurar schemas `Product` + `Organization` + `BreadcrumbList` desde el tema (o delegar a Rank Math).
4. **M8 — cierre formal pendiente desde antes**: depende de que el dueño configure shipping zones + Webpay sandbox + Mercado Pago sandbox en WC admin. Sin esto el criterio "pedido test end-to-end" no se puede validar. Ver entrada `2026-04-19 Módulo 8 — shipping zones + payment gateways + sandbox keys DIFERIDOS`.
5. **Revisar bug Lamentation (ID 10407)** pendiente del M0: producto con `pa_estado`/`pa_idioma` vacíos por race condition del manager. Auditar cuántos productos están afectados (query documentada en §3.2).
6. **M9 polish opcional** (low priority): (a) autocomplete en el hero search (hoy submite `?q=` al listado), (b) fallback "Buscar en Scryfall" en el autocomplete del header cuando `noResults`, (c) backfill `_onplay_set_code` como term_meta para usar Keyrune en `filters-sidebar` y `featured-sets` (hoy usan el diamante monograma fallback).

### Commits del día (del más reciente al más antiguo)
```
7e5ed4b fix(M9-pdp-image): cap imagen PDP a 460px (evita upscale desde Scryfall normal 488px)
366c5d3 fix(M9-pdp-image): revert columna PDP a 1fr/1.15fr con object-fit cover
ffcfd8d feat(M9-mana-pdp): mana-font oficial + imagen PDP con srcset responsivo
6dd5b7f docs(claude): registrar decisiones M9 hero/recent + Keyrune
c2d55d4 feat(M9-hero): rebuild del hero fiel a Onplay.cl/home.jsx (hybrid)
f34ab22 feat(M9-recent): carrusel horizontal + orden por mayor valor
239055b feat(M9-keyrune): íconos oficiales MTG vía webfont + fallback diamante
2cccb7f feat(M9-wiring): dist rebuild + docs CLAUDE.md §11
0914f6e feat(M9-templates): front-page.php + 4 template-parts de home + SCSS
675fd67 feat(M9-core): helpers de home (featured sets + recent cards)
```

---

## 11.c. Arranque Módulo 10 — 2026-04-21

### Hecho en esta sesión (código del tema)

- **`inc/security.php` nuevo** — cabeceras de seguridad en `send_headers`: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` restrictiva, `X-XSS-Protection: 0`, `Strict-Transport-Security: max-age=31536000` (solo con `is_ssl()`). Remueve `wp_generator`, `wlwmanifest`, `rsd`, `rest_output_link_wp_head`; desactiva XML-RPC + pingback; filtra `?ver=X` de styles/scripts cuando coincide con `get_bloginfo('version')`. Backup de `DISALLOW_FILE_EDIT` a nivel de tema (la config canónica sigue siendo en `wp-config.php`). **CSP queda comentado a propósito** hasta auditar qué scripts inyectan Transbank Webpay y Mercado Pago en el flow de checkout (M8); habilitar CSP sin esa auditoría rompería pagos.
- **`inc/seo.php` nuevo** — emite JSON-LD desde el tema: `Organization` (marca pública "Onplay", `legalName` **Comercializadora y Distribuidora BM Limitada**, `taxID` 77.862.085-5, `email` contacto@onplay.cl, dirección física Merced 832 Local 54 Galería Casa Colorada) en todas las páginas, `WebSite + SearchAction` apuntando a `/tienda/?q={search_term_string}` solo en home, `BreadcrumbList` en archive (`is_shop()` + `is_tax('product_cat')`) y en single-product, y `Product` en single con `priceCurrency=CLP`, `sku`, `availability`, `itemCondition=NewCondition`, `brand={Set name}`, `image` del thumbnail full. Más OG tags + Twitter Card `summary_large_image` con descripción tomada del oracle cuando existe. Canonical mínimo solo en home/shop/product_cat (WP core ya lo emite en `is_singular()`).
- **`inc/enqueue.php` — performance** — los 3 CSS externos (Google Fonts, Keyrune, mana-font) pasan a carga no-bloqueante con patrón `<link rel="preload" as="style" onload="this.rel='stylesheet'">` + `<noscript>` de respaldo. Agregué `preconnect` a `cdn.jsdelivr.net`. En el PDP emito `<link rel="preload" as="image" fetchpriority="high">` del thumbnail full (candidato LCP). `script_loader_tag` agrega `defer` a `onplay-main`.

### Decisiones clave del arranque

1. **Schemas los emite el tema, NO Rank Math.** Razón: control total de los datos críticos (precio CLP sin conversión fantasma, SKU estructurado correcto, brand = nombre del set). Cuando el dueño instale Rank Math, debe **desactivar en Rank Math → Titles & Meta → Products/Categories** la emisión de schemas `Product`, `Organization`, `BreadcrumbList`, `WebSite`. Rank Math queda responsable solo de: sitemap XML, `<title>` / meta description con templates, canonical en posts/pages (aunque WP core ya lo hace en `is_singular`). **Rollback:** borrar `inc/seo.php` + el `require_once` en functions.php, y activar todos los schemas en Rank Math.
2. **CSP diferido, no omitido.** Comentario explícito en `inc/security.php` para activarlo después de hacer un pedido test en sandbox de Transbank + Mercado Pago y capturar los orígenes que inyectan scripts/iframes. Sin esa lista, cualquier CSP razonable rompe el checkout (razón: los gateways cargan SDK de terceros en runtime). Prioridad: M10-validation después de que el dueño configure sandbox keys (pendiente desde M8).
3. **`DISALLOW_FILE_EDIT` definido como fallback desde el tema.** La fuente de verdad sigue siendo `wp-config.php` (decisión de §7 del CLAUDE.md), pero el tema lo define si aún no está — red de seguridad mientras el dueño edita wp-config.

### Pendiente en esta sesión (próximos commits)

- **Correr Lighthouse mobile local** en Home / Archive (`/tienda/`) / Single (una ficha con variantes). Baseline antes de decidir si necesitamos critical CSS inline o mirror local de imágenes Scryfall. Target: ≥85 en cada una.
- **Validar schemas** con [Schema.org Validator](https://validator.schema.org/) o [Rich Results Test](https://search.google.com/test/rich-results) contra URLs locales/staging una vez estén accesibles.

### Pendiente del dueño (wp-admin / hosting — bloquea cierre de M10)

1. **Plugins a instalar y configurar:**
   - Rank Math SEO — desactivar sus schemas Product/Organization/BreadcrumbList/WebSite (ya los emite el tema). Configurar templates de title/meta + sitemap.
   - Wordfence Security — firewall + login rate limiting + 2FA para admin.
   - UpdraftPlus — backup diario → Google Drive.
   - LiteSpeed Cache (si el hosting es LiteSpeed) o WP Super Cache. **Importante:** configurar para NO purgar en cada write del manager; purgar solo al final del batch de 50 (ver §8 Performance).
2. **`wp-config.php` — agregar:** `define('DISALLOW_FILE_EDIT', true);` (el tema tiene un backup pero este es el canónico).
3. **Cerrar M8 primero** (pendiente desde antes): configurar shipping zones (Chile > Retiro en tienda + Chilexpress flat rate), Webpay Plus con keys sandbox, Mercado Pago sandbox. Hasta que M8 valide end-to-end, M10 no puede validar CSP ni hacer pedido test integral.
4. **Switch de tema blocksy-child → onplay en producción** — último paso de M10. Pre-requisitos: backup completo con UpdraftPlus, Lighthouse validado, schemas validados, pedido test sandbox OK.

### Criterios de aceptación M10 — estado

- [ ] Lighthouse mobile ≥85 en Home/Archive/Single (pendiente de medir).
- [x] Schemas `Product` + `Organization` + `BreadcrumbList` + `WebSite` emitidos desde el tema.
- [x] Headers de seguridad (X-Frame-Options, X-Content-Type-Options, Referrer-Policy, HSTS) enviados por el tema.
- [ ] Rank Math configurado (dueño).
- [ ] Wordfence + 2FA (dueño).
- [ ] Backup UpdraftPlus verificado (dueño).
- [ ] Switch de tema en producción (dueño, último paso).

### Mini-módulo "M-cuenta" — skin de `/mi-cuenta/` (2026-04-21)

Previo al switch de tema decidimos skinear My Account porque sin overrides WC sirve plantillas default (clases `woocommerce-MyAccount-*`, gris-sobre-gris, no paridad visual con cart/checkout). Overrides creados en `wp-content/themes/onplay/woocommerce/myaccount/`:

- **`my-account.php`** — wrapper 2-col: nav lateral (240px) + contenido. Delega a los hooks `woocommerce_account_navigation` + `woocommerce_account_content` para no romper endpoints de plugins (wallet, subscriptions, etc. futuros).
- **`navigation.php`** — sidebar con iconos SVG inline por endpoint (`dashboard`, `orders`, `downloads`, `edit-address`, `payment-methods`, `edit-account`, `customer-logout`). Endpoints ausentes del mapa caen al fallback sin icono — no rompen al agregarse nuevos.
- **`dashboard.php`** — saludo + tarjeta "último pedido" (con status pill colorizada por `wc_get_customer_last_order()`) + empty state con CTA al shop + 3 tiles (Pedidos / Direcciones / Datos de cuenta).
- **`form-login.php`** — split login/registro cuando WC tiene registro habilitado (`myaccount-auth--split`); solo login centrado si no (`--solo`). Mantiene hooks `woocommerce_login_form_*`, `woocommerce_register_form_*` y nonces para compatibilidad con reCAPTCHA/social login futuros.
- **`orders.php`** — **lista como tarjetas, no tabla**. Razón: responsive sin "data-title" hacks de la tabla WC; cada tarjeta tiene head (pedido # + status pill), meta (fecha / items / total) y actions (view / pay / cancel). Paginación separada con botones btn-ghost. Empty state con CTA al shop.
- **`my-address.php`** — grid 2-col (facturación | envío) con estado `--filled` / `--empty` (border dashed cuando vacía). Action "Editar" o "Añadir" según corresponda.
- **`form-edit-address.php`** — form render via `woocommerce_form_field()` en grid 2-col; el SCSS normaliza las clases `form-row-wide/first/last` que WC emite.
- **`form-edit-account.php`** — nombre/apellido en 2-col, email wide, fieldset "Cambio de contraseña" con 3 campos (actual + nueva + confirmar) en layout de 2-col. Hint `"Déjalo en blanco para no cambiarla"`.
- **`form-lost-password.php`** + **`form-reset-password.php`** — narrow forms centrados con kicker + título + lede + campos + submit.

**SCSS:** `assets/src/scss/templates/_myaccount.scss` nuevo (~720 líneas), importado en `main.scss`. Define las 5 clases raíz: `.myaccount` (wrapper/nav), `.myaccount-dashboard` + `.myaccount-card` + `.myaccount-tile`, `.myaccount-auth` (login/register), `.myaccount-orders` + `.myaccount-order`, `.myaccount-addresses` + `.myaccount-address`, `.myaccount-form` (genérico para edit-account/edit-address/lost/reset). Mirrors de cart/checkout: fondo `--carbon`, bordes `--line`, status pills con `color-mix()` desde `--green/--amber/--carmesi`, inputs con `background:--carbon-2 / border:--line-2 / focus:--carmesi`. Status map: completed/processing → green, on-hold/pending → amber, cancelled/failed/refunded → carmesi.

**Decisiones puntuales:**
- **Lista de pedidos como tarjetas, no tabla.** La tabla WC tiene 4-5 columnas y en mobile se degrada con `data-title` CSS que se ve mal. Las tarjetas apilan head + meta grid (3 cols → 2 cols ≤560px) + actions; mejor lectura y coherencia con el card-based design del resto del tema. **Rollback:** restaurar `<table class="woocommerce-orders-table">` + thead/tbody siguiendo `wc_get_account_orders_columns()` — el markup default vive en el commit de WC; el SCSS de tabla habría que agregarlo.
- **Iconos SVG inline (no Lucide/Feather import).** Los 7 iconos pesan ~2KB gzipped combinados dentro del HTML de la página; evitamos una font/sprite sheet extra. Stroke-width 2, currentColor → tiñen al hover con `.myaccount__nav-icon { color: var(--carmesi) }`. **Rollback:** reemplazar por `<i class="lucide lucide-{name}">` + enqueue de Lucide CSS.
- **Password change en el mismo form de datos de cuenta.** Es el comportamiento nativo de WC (`form-edit-account.php`) con hook `woocommerce_save_account_details` — mantenemos la experiencia default para no romper validación del core. **Rollback:** no aplica, es el comportamiento WC.
- **Sin skin para `view-order.php` ni `form-add-payment-method.php` ni `downloads.php` en esta iteración.** Rationale: view-order usa `woocommerce/order/*` templates (otra carpeta), no `myaccount/`; lo cubriría una iteración "M-cuenta v2" después del MVP. Payment methods + downloads no aplican al modelo del MVP (sin productos descargables, sin suscripciones). La vista default queda como fallback — heredan los estilos globales del tema vía `.woocommerce-MyAccount-content`, no rompen.

**Aceptación M-cuenta:**
- [x] `/mi-cuenta/` muestra login + registro con el look del tema.
- [x] Logueado, `/mi-cuenta/` muestra dashboard con nav lateral + saludo + (último pedido | empty state) + 3 tiles.
- [x] `/mi-cuenta/pedidos/` lista pedidos como cards o empty state.
- [x] `/mi-cuenta/direcciones/` lista 2 tarjetas (facturación + envío) con action editar/añadir.
- [x] `/mi-cuenta/editar-cuenta/` renderiza nombre + email + password change en el estilo del tema.
- [x] `/mi-cuenta/perder-password/` + reset flow skineados.
- [x] PHP lint limpio en los 10 templates, SCSS compila sin errores.
- [ ] **Verificación manual en browser pendiente** (smoke test de cada endpoint + mobile responsive ≤560px).

---

## 11.d. M-OP-filtros — Panel facetado One Piece TCG (2026-05-03)

### Hecho

- **`inc/op-filters.php`** + **`inc/op-filters/{query,panel,enqueue}.php`** — bootstrap, helpers de detección de archive OP, render del sidebar, body_class condicional `onplay-op-archive`.
- **`template-parts/op-filters/group-{color,card-type,illustration}.php`** — 3 grupos con SSR de `aria-pressed`/`is-active` desde URL.
- **`assets/src/scss/components/_op-filters.scss`** — swatches (R/G/B/P/K/Y), pills mono uppercase, mobile collapse con contador.
- **`assets/src/js/op-filters.js`** — extensión del módulo `Filters` existente con monkey-patch sobre `defaultState`/`readStateFromURL`/`writeStateToURL`/`applyStateToUI`/`buildQuery` + click handler para botones aria-pressed + mobile toggle.
- **Modificaciones puntuales**: `template-parts/filters-sidebar.php` (4 líneas, fork temprano si archive OP), `inc/filters-ajax.php` (2 hooks de extensión), `package.json` (build:js concat), `functions.php` (1 require_once), `README.md` del tema.
- **Docs**: `docs/specs/M-OP-filtros/` (spec/plan/tasks con bitácora completa), `docs/op-filters-sql-indexes.md`, `docs/design-onplay-cl/` (bundle Claude Design integrado).

### Decisiones clave

1. **Aproximación A — extensión, no duplicación.** El plan original asumía `pre_get_posts` sobre la main query del archive. Realidad: `inc/woocommerce.php` redirecciona `/product-category/<slug>/` → `/shop/?set=<slug>` (302), y `archive-product.php` usa `onplay_filters_run()` con su propio `WP_Query` que ignora `pre_get_posts`. **Decisión aprobada por el dueño**: extender `inc/filters-ajax.php` mediante `apply_filters('onplay_filters_state_after_parse')` y `apply_filters('onplay_filters_meta_query')`. Sin endpoint AJAX paralelo, sin pipeline duplicado. Mantengo `pre_get_posts` como red defensiva para canonical/schemas.
2. **Sin custom taxonomies para OP.** Decisión heredada de §3.7. Operamos vía `meta_query` con `_color LIKE` (duales como `Red/Purple`), `_card_type IN`, `_is_alt_art =`. Mitigación: índices SQL recomendados en `docs/op-filters-sql-indexes.md`.
3. **Diseño Claude Design integrado.** El bundle `zB3RicDLFnMe55CrqwI51w` (`Onplay.html` + `listing-onepiece.jsx`) llegó durante la implementación. Se incorporaron los visuales del panel (swatches con letras R/G/B/P/K/Y, pills uppercase mono, grid 2-col, borde carmesí en activo). Se omitieron filtros del diseño que no tienen meta backing en el Binder OP (`_attribute`, `_cost`, `_power`, `_counter`, `_block_icon`, condición/idioma — todo OP es NM/EN). Reincorporables en iteración futura si el Binder los popule.
4. **Bundle JS via concat.** El `build:js` original era `copyFileSync` puro. Cambio mínimo en `package.json`: lee `main.js` + `op-filters.js` y los concatena al `dist/main.js`. Sin runtime ES modules en el navegador. Si en el futuro se introduce un bundler real, op-filters.js se vuelve un module `import`.
5. **Junction Laragon documentado.** El docroot `C:\laragon\www\testmanager\wp-content\themes\onplay` es un **junction** al repo en OneDrive. NO hay drift — la memoria `dev_env_laragon.md` describía un escenario antiguo de copia manual.

### Verificación server-side cubierta

- Counts: 25 sin filtro / 15 Red (con duales) / 5 Purple (con duales) / 7 LEADER / 6 alt-art / 5 combinado / Magic 226 sin afectación.
- SSR `aria-pressed`/`is-active` desde URL (CA-5).
- `body_class` condicional (T014).
- No-regresión Magic (T042 server-side): sidebar Magic intacto, sin op_* injustificados.

### Pendiente owner (verificación visual + Lighthouse)

- Smoke en navegador: panel desktop, panel mobile colapsado con contador, grid filtrado, URL compartida entre browsers, back/forward del navegador.
- Lighthouse mobile en `/shop/?set=one-piece-tcg` (target ≥ 80, no degradar más de 5 pts vs baseline).
- Capturas en `docs/specs/M-OP-filtros/qa/`.

### Commits del módulo

Rama `feat/m-op-filtros` (local, no pushed):

```
fcb2a7d docs(status+claude): registrar M-OP-filtros y traer sistema status
6a7c3aa docs(op-filters): integrar bundle Claude Design
656a9ca docs(op-filters): spec + plan + tasks + SQL indexes + README
2c72dad build(op-filters): rebuild dist bundle
90b8fc9 style(op-filters): SCSS component + JS extension + bundle concat
151621c feat(op-filters): sidebar render + template-parts + fork
24717c1 feat(op-filters): bootstrap module + query hook extension points
```

---

## 11.e. M-OP-buscador — Buscador AJAX contextual One Piece (2026-05-04)

### Hecho

- **`inc/op-filters/helpers.php`** (nuevo) — `onplay_op_is_single()` (detecta single product cuyo `product_cat` desciende de `one-piece-tcg`), `onplay_op_in_op_context()` (combina archive + single), `onplay_op_get_descendant_tt_ids()` (cache static de los 10 term_taxonomy_ids OP).
- **`inc/search.php`** — handler ramificado por `?tcg=op`. Nueva función `onplay_search_products_op($q, $limit)` con SQL crudo: `INNER JOIN wp_term_relationships` + búsqueda LIKE en `post_title`/`_sku`/`_card_number`, agrupación por `_card_number` con regla "regular gana sobre alt-art como representante". Magic preserva comportamiento M5 intacto (solo se agrega `context: "global"|"op"` al payload).
- **`header.php`** — `onplay_op_in_op_context()` decide si agregar `data-tcg="op"`, hidden inputs (`set=one-piece-tcg` + `tcg=op`), span `[data-onplay-search-context]` "Buscando en One Piece", modifier `--op`. Form action condicional (`/shop/?set=one-piece-tcg` cuando OP, `/shop/` cuando global).
- **`inc/enqueue.php`** — i18n strings nuevos (`opNoResults`, `opSearchAll`, `opAltArt`).
- **`assets/src/js/op-search.js`** (nuevo) + **`package.json`** — monkey-patch sobre `Search.fetch`/`Search.render`. Agrega `&tcg=op` al fetch cuando `data-tcg`. Render diferenciado: items OP con set_code (`OP15-EB04`) + badge "Alt Art"; empty state OP con CTA "Buscar en todo el sitio" que dispara `doGlobalRefetch()` sin `tcg=op`. Concatenado al bundle via `build:js` extendido.
- **`_search-autocomplete.scss`** — indicador absolute en `var(--carmesi)`/mono/uppercase, borde input tenue en contexto, badge alt-art en gold, empty state con CTA.

### Decisiones clave

1. **Aproximación A — extensión, no duplicación.** El plan asumía endpoint REST y `WP_Query`/template-parts paralelos. Realidad: M5 es admin-ajax con SQL crudo. Se extiende ese endpoint con rama `tcg=op` y la JS hace monkey-patch del módulo `Search` existente. NO se crea endpoint paralelo, NO se duplica SQL, NO se crean archivos de template/JS distintos al patrón `op-*` ya consolidado en M-OP-filtros.
2. **Agrupación OP por `_card_number`, no por `print_key`.** `print_key` es un concepto Magic (set+collector). En OP, el card_number es la identidad lógica de la carta (variantes alt-art comparten card_number). El payload reusa el campo `print_key` con el valor del card_number para no romper el JS heredado.
3. **Regla "regular gana sobre alt-art".** Cuando hay 2+ productos con el mismo `_card_number`, el representante del grupo es la versión NO alt-art (si existe). El precio "Desde" sigue mostrando el mínimo de todos los productos del grupo.
4. **Bundle JS via concat (mismo patrón M-OP-filtros).** `op-search.js` se concatena al `dist/main.js` después de `op-filters.js`. Sin runtime ES modules.
5. **T023 cache transient — fuera por YAGNI.** Latencia OP paritaria con Magic en local (~950ms P95, env-bound no SQL-bound). Cache invalidation requeriría hook `save_post_product` selectivo, footgun no justificado por mejora marginal medible. Reactivable si Hostinger muestra >500ms.
6. **Slug del shop = `/shop/`** (descubierto en M-OP-filtros). Submit form va a `/shop/?set=one-piece-tcg` cuando OP, `/shop/` cuando global. NO a `/categoria-producto/...` (esos se redirigen 302 al shop por `inc/woocommerce.php`).

### Verificación cubierta

- 6 escenarios CA via endpoint AJAX real (Bonney, EB04 prefix, Carina agrupado, Bolt empty OP, Bolt Magic global, Burst Magic). Todos ✓.
- SSR header: `data-tcg`/`--op`/hidden inputs/span correctos en archive OP + single OP; ausentes en home + Magic.
- No-regresión Magic: payload idéntico estructuralmente, solo se añade campo `context: "global"`.
- Edge cases: `q=""` y `q="B"` (1 char) devuelven vacío sin error.
- `debug.log` sin warnings nuevos atribuibles al módulo.

### Pendiente owner (verificación visual)

- Capturas en `docs/specs/M-OP-buscador/qa/`: autocomplete OP con resultados, empty state OP con CTA, autocomplete Magic sin cambios, indicador "Buscando en One Piece" en archive y single OP, submit form OP llegando al shop con set+q.
- Lighthouse mobile pre/post (CA-6 perf, target ≤3 puntos de degradación).
- Smoke en mobile ≤768px del indicador.

### Commits del módulo

Rama `feat/m-op-buscador` (local, no pushed; basada en `feat/m-op-filtros`):

```
67adb9e build(search): rebuild dist bundle
77cc672 feat(search-js): rama tcg=op + render diferenciado + empty state CTA
892458e style(search): indicador OP, badge alt-art, empty state con CTA
fee5db8 feat(header): detección de contexto OP + indicador visual
44ee4e1 feat(search): rama tcg=op en endpoint con tax JOIN y agrupación por card_number
b219957 feat(search): helpers de contexto OP (is_single + in_op_context + tt_ids)
```

---

## 12. Cómo interactuar con Claude Code en este proyecto

- Empezar cada sesión: "estoy trabajando en el módulo X". Claude Code lee este CLAUDE.md primero.
- Al terminar un módulo: actualizar sección 11 con lo que cambió.
- No marcar módulo como implementado sin verificar criterios de aceptación.
- Contradicción código vs este documento: detener, preguntar al dueño, resolver, actualizar aquí.
- Contradicción tema vs manager: manager gana. Documentar ajuste en sección 11.
- Antes del Módulo 2: **verificar en ambiente local con DB copia** que los 410 productos existen con los meta documentados en sección 3.2. Ejecutar `wp post meta list <ID>` en 5-10 productos muestra antes de empezar.
- Antes del Módulo 8: **el checkout nunca se prueba primero en producción**. Sandbox de Transbank y Mercado Pago (keys de test en ambiente local), verificación end-to-end, luego switch a producción.
