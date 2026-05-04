# Plan técnico — M-OP-buscador

**Spec asociada**: `specs/M-OP-buscador/spec.md`
**Repositorio**: `tema-onplay`
**Fecha**: 2026-05-03

---

## 1. Estrategia general

**Extender, no duplicar.** El buscador Magic (M5) ya está construido y funcionando. Este módulo agrega una **capa de detección de contexto** que decide si el endpoint AJAX limita los resultados al catálogo OP o no, y un **indicador visual** en el header cuando estamos en contexto OP.

**Tres bloques de cambio:**

1. **Server-side**: extender `inc/search.php` (creado en M5) para aceptar `tcg=op` y aplicar `tax_query` a `product_cat=one-piece-tcg`.
2. **Header**: extender `header.php` (o el partial que renderice el buscador) para detectar contexto y agregar:
   - Indicador "Buscando en One Piece".
   - `data-tcg="op"` en el wrapper del search.
   - `<input type="hidden" name="tcg" value="op">` (para que el submit normal también respete contexto).
   - Action del form a `/categoria-producto/one-piece-tcg/` cuando aplique.
3. **JS**: extender `assets/src/js/search-autocomplete.js` (M5) para:
   - Leer `data-tcg` del wrapper.
   - Si presente, agregar `&tcg=op` al fetch del autocomplete.
   - Cambiar el render de cada item para usar agrupación por `_card_number` (no `print_key`).

**No se crea un archivo PHP/JS dedicado** porque el costo de mantener dos buscadores paralelos supera el beneficio de tenerlos separados.

---

## 2. Archivos a modificar

```
wp-content/themes/onplay/
├── header.php                                  [MODIFICAR — detección de contexto]
├── inc/
│   ├── search.php                              [MODIFICAR — soportar param tcg=op]
│   └── op-filters/
│       └── helpers.php                         [CREAR — onplay_op_is_single() helper]
├── template-parts/
│   └── search-form.php                         [MODIFICAR — render del indicador + hidden input]
└── assets/
    └── src/
        └── js/
            └── search-autocomplete.js          [MODIFICAR — branch para tcg=op]
```

**Sin archivos nuevos pesados.** El único nuevo es `inc/op-filters/helpers.php` con un helper de detección que usaremos en header y en search.

---

## 3. Lógica clave

### 3.1 Helper de detección (single product OP)

`onplay_op_is_archive()` ya existe (M-OP-filtros). Necesitamos su contraparte para single product:

```php
// inc/op-filters/helpers.php
function onplay_op_is_single() : bool {
    if ( ! is_singular( 'product' ) ) return false;
    $post_id = get_queried_object_id();
    if ( ! $post_id ) return false;
    $cats = get_the_terms( $post_id, 'product_cat' );
    if ( empty( $cats ) || is_wp_error( $cats ) ) return false;
    foreach ( $cats as $cat ) {
        if ( onplay_op_term_descends_from( $cat, 'one-piece-tcg' ) ) {
            return true;
        }
    }
    return false;
}

function onplay_op_in_op_context() : bool {
    return onplay_op_is_archive() || onplay_op_is_single();
}
```

### 3.2 Endpoint AJAX (extensión de `inc/search.php`)

Pseudocódigo del cambio (no reescribimos el archivo entero, solo agregamos la rama):

```php
function onplay_search_autocomplete_handler() {
    $query = sanitize_text_field( $_GET['q'] ?? '' );
    $tcg   = sanitize_text_field( $_GET['tcg'] ?? '' );

    if ( strlen( $query ) < 2 ) {
        wp_send_json( [ 'results' => [] ] );
    }

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => 8,
        'post_status'    => 'publish',
        's'              => $query,
        // ... resto del query existente de M5
    ];

    // Rama nueva: contexto OP
    if ( $tcg === 'op' ) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => 'one-piece-tcg',
                'include_children' => true,
            ],
        ];
        // Búsqueda también por _card_number
        // Si query parece card number (regex /^[A-Z]{2,5}\d?-\d{3,}$/i), agregar meta_query
        if ( preg_match( '/^[A-Z0-9]{2,7}-\d{1,4}$/i', $query ) ) {
            $args['meta_query'] = [
                [
                    'key'     => '_card_number',
                    'value'   => strtoupper( $query ),
                    'compare' => 'LIKE',
                ],
            ];
            unset( $args['s'] ); // si parece card number, prioriza meta sobre title
        }
    }

    $q = new WP_Query( $args );
    $results = [];

    if ( $tcg === 'op' ) {
        // Agrupar por _card_number — un resultado por card number
        $grouped = [];
        foreach ( $q->posts as $post ) {
            $card_number = get_post_meta( $post->ID, '_card_number', true ) ?: $post->post_title;
            if ( ! isset( $grouped[ $card_number ] ) ) {
                $grouped[ $card_number ] = onplay_op_format_search_result( $post );
            }
        }
        $results = array_values( $grouped );
    } else {
        // Comportamiento Magic (M5) sin cambios
        $results = onplay_search_format_magic_results( $q->posts );
    }

    wp_send_json( [ 'results' => $results, 'context' => $tcg ?: 'global' ] );
}
```

### 3.3 Formateo del resultado OP

```php
function onplay_op_format_search_result( WP_Post $post ) : array {
    $product = wc_get_product( $post->ID );
    return [
        'id'           => $post->ID,
        'title'        => $post->post_title,
        'card_number'  => get_post_meta( $post->ID, '_card_number', true ),
        'set_code'     => get_post_meta( $post->ID, '_set_full_code', true ),
        'is_alt_art'   => get_post_meta( $post->ID, '_is_alt_art', true ) === 'yes',
        'price_html'   => $product ? $product->get_price_html() : '',
        'thumbnail'    => get_the_post_thumbnail_url( $post->ID, 'thumbnail' ) ?: wc_placeholder_img_src(),
        'url'          => get_permalink( $post->ID ),
        'context'      => 'op',
    ];
}
```

### 3.4 Header con detección

```php
// header.php (sección del search)
$op_context = function_exists( 'onplay_op_in_op_context' ) && onplay_op_in_op_context();
?>
<div class="header-search" data-tcg="<?php echo $op_context ? 'op' : 'global'; ?>">
    <form action="<?php echo esc_url( $op_context ? get_term_link( 'one-piece-tcg', 'product_cat' ) : home_url( '/' ) ); ?>"
          method="get"
          class="header-search__form">
        <?php if ( $op_context ) : ?>
            <input type="hidden" name="tcg" value="op">
        <?php endif; ?>
        <input type="search"
               name="<?php echo $op_context ? 's' : 's'; ?>"
               class="header-search__input"
               placeholder="<?php esc_attr_e( 'Buscar carta...', 'onplay' ); ?>"
               autocomplete="off">
        <?php if ( $op_context ) : ?>
            <span class="header-search__context">
                <?php esc_html_e( 'Buscando en One Piece', 'onplay' ); ?>
            </span>
        <?php endif; ?>
    </form>
    <div class="header-search__autocomplete" data-search-autocomplete></div>
</div>
```

### 3.5 JS — branch tcg=op

```javascript
// search-autocomplete.js (extensión)
const wrapper = document.querySelector('.header-search');
const tcg = wrapper?.dataset.tcg || 'global';

const fetchResults = async (query) => {
  const url = new URL('/wp-json/onplay/v1/search', window.location.origin);
  url.searchParams.set('q', query);
  if (tcg === 'op') url.searchParams.set('tcg', 'op');
  const res = await fetch(url.toString(), { credentials: 'same-origin' });
  return res.json();
};

const renderResult = (item) => {
  if (item.context === 'op') return renderOpResult(item);
  return renderMagicResult(item); // existente M5
};

const renderOpResult = (item) => `
  <a href="${item.url}" class="search-result search-result--op">
    <img src="${item.thumbnail}" alt="" loading="lazy" width="60" height="84">
    <div class="search-result__body">
      <div class="search-result__title">${escapeHtml(item.title)}</div>
      <div class="search-result__meta">
        <span class="search-result__set">${escapeHtml(item.set_code)}</span>
        ${item.is_alt_art ? '<span class="badge badge-alt">Alt Art</span>' : ''}
      </div>
      <div class="search-result__price">${item.price_html}</div>
    </div>
  </a>
`;
```

---

## 4. Hooks usados

| Hook | Tipo | Función | Razón |
|---|---|---|---|
| `rest_api_init` | action | (existente M5) | Endpoint REST `onplay/v1/search` |
| `wp_enqueue_scripts` | action | (existente M5) | Encolado del JS de autocomplete |

**No se agregan hooks nuevos.** Solo se extiende lógica existente.

---

## 5. Compatibilidad y no-regresión

### 5.1 Magic intacto

- Si `tcg` param no se envía o es distinto a `op`, el endpoint ejecuta exactamente el código existente de M5.
- El header sin `data-tcg="op"` no envía el param y el JS sigue rama Magic.
- Se mantienen todos los hooks, nombres de funciones y rutas REST.

### 5.2 Home

- En home, `onplay_op_in_op_context()` retorna `false`.
- Buscador en home se comporta global (búsqueda en todo el catálogo).
- Submit form va a `/?s={query}`.

### 5.3 Plugins de búsqueda externos

- Si el sitio instala SearchWP o similar en M10, ese plugin reemplaza el endpoint y este módulo deja de aplicar. Documentar como TODO en `PENDING-CLAUDE.md` para revisar al instalar.

---

## 6. Performance

- **Cache transient** del resultado del autocomplete OP por 5 minutos: `onplay_search_op_{md5(query)}`.
- **Sin meta_query si no parece card number** — la búsqueda por `s` (que va a `post_title`) es más rápida que por `_card_number LIKE`.
- **`posts_per_page = 8`** para autocomplete (no 20+).
- **Sin imágenes Scryfall**: thumbnails locales del Media Library.
- **Lazy load** en thumbnails del autocomplete.

---

## 7. Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|
| Cambio en `inc/search.php` rompe búsqueda Magic | Media | **Alto** | Tests manuales exhaustivos del flow Magic en T040 |
| Detección de contexto falla en sub-categorías profundas | Media | Medio | `onplay_op_term_descends_from()` ya cubre con `get_ancestors()` |
| Usuario en checkout con producto OP en carrito ve indicador OP en buscador | Baja | Cosmético | No tratamos checkout como contexto OP (es página de transacción) |
| Empty state confunde al usuario | Media | Medio | A/B testear el wording con métricas |

---

## 8. Plan de testing manual

1. **Verificar M-OP-filtros completado** (provee `onplay_op_is_archive`).
2. **Levantar local con DB copia** que tenga al menos 5 productos OP y 5 Magic con nombres similares (ej. cartas con "Bolt" o "Strike" en ambos juegos).
3. **Tests de contexto**:
   - Home → buscar "Bolt" → resultados Magic (M5) + OP si existe match.
   - `/categoria-producto/one-piece-tcg/` → buscar "Bonney" → solo OP.
   - `/categoria-producto/one-piece-tcg/booster-packs/op15-eb04/` → buscar "EB04-001" → resultado por card number.
   - `/categoria-producto/magic-the-gathering/` → buscar "Bolt" → comportamiento M5 sin cambios.
   - Single product OP → buscar "Bolt" → solo OP.
4. **Tests de empty state OP** (CA-7).
5. **Tests de submit form** (CA-8).
6. **Tests de no-regresión Magic** (CA-9).
7. **Lighthouse mobile** en home y archive OP — score no debe bajar.
8. **Network tab**: confirmar que `&tcg=op` se envía solo en contexto OP.

---

## 9. Rollback

- Quitar el bloque `if ( $tcg === 'op' )` agregado a `inc/search.php`.
- Quitar `data-tcg`, hidden input y span de contexto en `header.php` / `search-form.php`.
- Quitar el branch en `search-autocomplete.js`.

Magic queda intacto, OP vuelve a buscar global.

---

## 10. Decisiones abiertas

- [ ] ¿Endpoint REST custom (`/wp-json/onplay/v1/search`) o `admin-ajax.php`? — verificar qué usa M5 y mantener consistencia. Default: lo mismo que M5.
- [ ] ¿Cachear con transient o con object cache? — depende de si el hosting tiene Redis/Memcached. Default: transient.
- [ ] ¿Agregar telemetría de búsquedas (qué buscan los usuarios)? — postergado a post-MVP, depende de plugin analytics.
