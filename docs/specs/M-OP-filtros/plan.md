# Plan técnico — M-OP-filtros

**Spec asociada**: `specs/M-OP-filtros/spec.md`
**Repositorio**: `tema-onplay`
**Fecha**: 2026-05-03

---

## 1. Stack y compatibilidad

| Componente | Versión | Uso |
|---|---|---|
| WordPress | 6.9.4 | core |
| WooCommerce | 10.7.0 | archive de producto |
| PHP | 8.3.x (Hostinger) | backend |
| Tema | `onplay` (custom) | host |
| JS | vanilla + Alpine.js | sin React (consistente con `CLAUDE.md` §2) |
| CSS | SCSS compilado | `assets/src/scss/` |

---

## 2. Archivos a crear / modificar

```
wp-content/themes/onplay/
├── functions.php                              [MODIFICAR — 1 línea de require_once]
├── inc/
│   ├── op-filters.php                         [CREAR — bootstrap del módulo]
│   ├── op-filters/
│   │   ├── query.php                          [CREAR — pre_get_posts + helpers]
│   │   ├── panel.php                          [CREAR — markup del panel]
│   │   ├── partial.php                        [CREAR — render parcial AJAX]
│   │   └── enqueue.php                        [CREAR — assets condicionales]
├── template-parts/
│   └── op-filters/
│       ├── group-color.php                    [CREAR — checkboxes Color]
│       ├── group-card-type.php                [CREAR — checkboxes Card Type]
│       └── group-illustration.php             [CREAR — checkboxes Normal/Alt Art]
├── assets/
│   ├── src/
│   │   ├── scss/
│   │   │   └── components/
│   │   │       └── _op-filters.scss          [CREAR]
│   │   └── js/
│   │       └── op-filters.js                  [CREAR]
│   └── dist/                                  [REGENERADO en build]
└── docs/
    └── op-filters-sql-indexes.md              [CREAR — snippet SQL de índices recomendados]
```

**Modificaciones mínimas a `functions.php`**: una sola línea:

```php
require_once get_stylesheet_directory() . '/inc/op-filters.php';
```

`inc/op-filters.php` hace los `require_once` internos del submódulo. Toda la lógica vive en `inc/op-filters/`.

**Importación SCSS**: agregar `@use 'components/op-filters';` al final de `assets/src/scss/main.scss`.

---

## 3. Hooks y filtros a usar

| Hook | Tipo | Función | Razón |
|---|---|---|---|
| `pre_get_posts` | action | `onplay_op_apply_filters` | Inyectar `meta_query` cuando estamos en archive OP con params |
| `woocommerce_before_shop_loop` | action (priority 15) | `onplay_op_render_panel` | Render del panel sobre el loop |
| `wp_enqueue_scripts` | action (priority 20) | `onplay_op_enqueue_assets` | Encolado condicional |
| `template_redirect` | action | `onplay_op_handle_partial` | Detectar `?op_partial=1` y devolver solo el grid |
| `posts_clauses` | filter | (no usado — preferimos `meta_query` estándar) | — |

---

## 4. Lógica clave

### 4.1 Detector de contexto: `onplay_op_is_archive()`

```php
/**
 * Determina si estamos en un archive de One Piece TCG.
 * Cubre: shop con params op_*, taxonomía product_cat descendiente de 'one-piece-tcg'.
 */
function onplay_op_is_archive() : bool {
    if ( is_admin() || ! function_exists( 'is_product_taxonomy' ) ) {
        return false;
    }
    if ( is_product_taxonomy() ) {
        $term = get_queried_object();
        if ( ! $term || empty( $term->slug ) ) {
            return false;
        }
        return onplay_op_term_descends_from( $term, 'one-piece-tcg' );
    }
    if ( is_shop() ) {
        // Solo activamos en /shop/ si vienen params op_* — para no romper Magic
        return ! empty( $_GET['op_color'] )
            || ! empty( $_GET['op_type'] )
            || ! empty( $_GET['op_alt'] );
    }
    return false;
}
```

### 4.2 Construcción de `meta_query`

```php
function onplay_op_apply_filters( WP_Query $query ) : void {
    if ( is_admin() || ! $query->is_main_query() ) return;
    if ( ! onplay_op_is_archive() ) return;

    $meta_query = (array) $query->get( 'meta_query' );

    // Color — LIKE para soportar duales "Red/Yellow"
    $colors = onplay_op_get_param( 'op_color' );
    if ( $colors ) {
        $clause = [ 'relation' => 'OR' ];
        foreach ( $colors as $c ) {
            $clause[] = [
                'key'     => '_color',
                'value'   => sanitize_text_field( $c ),
                'compare' => 'LIKE',
            ];
        }
        $meta_query[] = $clause;
    }

    // Card Type — IN, ya viene en UPPERCASE del Binder OP
    $types = onplay_op_get_param( 'op_type' );
    if ( $types ) {
        $meta_query[] = [
            'key'     => '_card_type',
            'value'   => array_map( 'strtoupper', array_map( 'sanitize_text_field', $types ) ),
            'compare' => 'IN',
        ];
    }

    // Illustration Type — alt → "yes", normal → "no"
    $alt_raw = onplay_op_get_param( 'op_alt' );
    if ( $alt_raw ) {
        $alt_values = [];
        if ( in_array( 'normal', $alt_raw, true ) ) $alt_values[] = 'no';
        if ( in_array( 'alt', $alt_raw, true ) )    $alt_values[] = 'yes';
        if ( $alt_values ) {
            $meta_query[] = [
                'key'     => '_is_alt_art',
                'value'   => $alt_values,
                'compare' => 'IN',
            ];
        }
    }

    if ( count( $meta_query ) > 1 || ( count( $meta_query ) === 1 && ! isset( $meta_query['relation'] ) ) ) {
        $meta_query['relation'] = 'AND';
        $query->set( 'meta_query', $meta_query );
    }
}
```

### 4.3 Helper de parsing de params

```php
function onplay_op_get_param( string $param ) : array {
    $raw = isset( $_GET[ $param ] ) ? sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) : '';
    if ( $raw === '' ) return [];
    return array_values( array_unique( array_filter( array_map( 'trim', explode( ',', $raw ) ) ) ) );
}
```

### 4.4 Catálogo de opciones (constantes del módulo)

```php
const ONPLAY_OP_COLORS = [
    'Red'    => [ 'label' => 'Red',    'swatch' => '#D62828' ],
    'Green'  => [ 'label' => 'Green',  'swatch' => '#3FB950' ],
    'Blue'   => [ 'label' => 'Blue',   'swatch' => '#2D7DD2' ],
    'Purple' => [ 'label' => 'Purple', 'swatch' => '#7B3FA0' ],
    'Black'  => [ 'label' => 'Black',  'swatch' => '#1A1A1A' ],
    'Yellow' => [ 'label' => 'Yellow', 'swatch' => '#E8B04B' ],
];

const ONPLAY_OP_CARD_TYPES = [
    'LEADER'    => 'Leader',
    'CHARACTER' => 'Character',
    'EVENT'     => 'Event',
    'STAGE'     => 'Stage',
];

const ONPLAY_OP_ILLUSTRATION = [
    'normal' => 'Normal',
    'alt'    => 'Alternate Art',
];
```

> **Por qué constantes y no DB**: las opciones de OP son cerradas y oficiales de Bandai. Listarlas dinámicamente con `DISTINCT meta_value` cuesta queries innecesarias y puede mostrar valores corruptos. Si Bandai introduce un nuevo color, se agrega aquí.

---

## 5. Render parcial AJAX (`?op_partial=1`)

```php
function onplay_op_handle_partial() : void {
    if ( ! isset( $_GET['op_partial'] ) || $_GET['op_partial'] !== '1' ) return;
    if ( ! onplay_op_is_archive() ) return;

    // Solo el wrap del grid, sin header/footer
    nocache_headers();
    header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

    echo '<div data-op-grid-wrap>';
    woocommerce_product_loop_start( false );
    while ( have_posts() ) {
        the_post();
        wc_get_template_part( 'content', 'product' );
    }
    woocommerce_product_loop_end( false );
    woocommerce_pagination();
    echo '</div>';
    exit;
}
```

El cliente hace `fetch(url + '&op_partial=1')`, parsea el HTML resultante y reemplaza el `<div data-op-grid-wrap>` actual.

---

## 6. JS — `assets/src/js/op-filters.js`

Vanilla puro, sin Alpine para esta interacción (es lo suficientemente simple).

Responsabilidades:

1. Leer estado inicial desde `URL.searchParams`.
2. Listener `change` delegado en el panel.
3. Construcción de la URL con params actualizados.
4. `history.pushState` para sincronizar URL.
5. `fetch` al partial endpoint y reemplazo del grid.
6. Listener `popstate` para soportar back/forward.
7. Botón "Limpiar filtros" → quita todos los params `op_*` y refresca grid.

```javascript
(function () {
  const PARAMS = { color: 'op_color', type: 'op_type', alt: 'op_alt' };
  const panel = document.querySelector('[data-op-filters]');
  if (!panel) return;

  const readState = () => {
    const url = new URL(window.location.href);
    const out = {};
    for (const [dim, param] of Object.entries(PARAMS)) {
      out[dim] = (url.searchParams.get(param) || '').split(',').filter(Boolean);
    }
    return out;
  };

  const writeState = (state) => {
    const url = new URL(window.location.href);
    for (const [dim, param] of Object.entries(PARAMS)) {
      const arr = state[dim] || [];
      arr.length ? url.searchParams.set(param, arr.join(',')) : url.searchParams.delete(param);
    }
    url.searchParams.delete('paged');
    window.history.pushState(null, '', url.toString());
  };

  const refreshGrid = async () => {
    const url = new URL(window.location.href);
    url.searchParams.set('op_partial', '1');
    panel.setAttribute('aria-busy', 'true');
    try {
      const res = await fetch(url.toString(), { credentials: 'same-origin' });
      const html = await res.text();
      const dom = new DOMParser().parseFromString(html, 'text/html');
      const next = dom.querySelector('[data-op-grid-wrap]');
      const current = document.querySelector('[data-op-grid-wrap]');
      if (next && current) current.replaceWith(next);
    } finally {
      panel.setAttribute('aria-busy', 'false');
      updateMobileCounter();
    }
  };

  const updateMobileCounter = () => {
    const state = readState();
    const total = Object.values(state).reduce((acc, arr) => acc + arr.length, 0);
    const counter = document.querySelector('[data-op-mobile-counter]');
    if (counter) counter.textContent = total > 0 ? `(${total})` : '';
  };

  panel.addEventListener('change', (e) => {
    const cb = e.target.closest('input[type="checkbox"][data-dim]');
    if (!cb) return;
    const state = readState();
    const dim = cb.dataset.dim, val = cb.dataset.value;
    const set = new Set(state[dim] || []);
    cb.checked ? set.add(val) : set.delete(val);
    state[dim] = [...set];
    writeState(state);
    refreshGrid();
  });

  panel.querySelector('[data-op-clear]')?.addEventListener('click', () => {
    const url = new URL(window.location.href);
    for (const param of Object.values(PARAMS)) url.searchParams.delete(param);
    url.searchParams.delete('paged');
    window.history.pushState(null, '', url.toString());
    panel.querySelectorAll('input[type="checkbox"][data-dim]').forEach(cb => cb.checked = false);
    refreshGrid();
  });

  window.addEventListener('popstate', () => {
    syncCheckboxesFromUrl();
    refreshGrid();
  });

  const syncCheckboxesFromUrl = () => {
    const state = readState();
    panel.querySelectorAll('input[type="checkbox"][data-dim]').forEach(cb => {
      cb.checked = (state[cb.dataset.dim] || []).includes(cb.dataset.value);
    });
  };

  updateMobileCounter();
})();
```

---

## 7. Encolado condicional

```php
function onplay_op_enqueue_assets() : void {
    if ( ! onplay_op_is_archive() ) return;
    $base = get_stylesheet_directory_uri();
    $path = get_stylesheet_directory();

    // CSS y JS ya están dentro del bundle principal (main.scss + main.js).
    // Si se decide separarlos, este es el lugar donde se encolarían.
    // Por ahora solo registramos un body class para CSS condicional.
    add_filter( 'body_class', function( $classes ) {
        $classes[] = 'onplay-op-archive';
        return $classes;
    } );
}
```

> **Decisión**: NO crear bundles separados `op-filters.css/js`. El SCSS se compila al `main.css` global con scope `.onplay-op-archive` para evitar afectar otras páginas. Mantiene el flujo de build simple.

---

## 8. Estilos (SCSS)

Resumen de `assets/src/scss/components/_op-filters.scss`:

- `.op-filters` — wrapper. En desktop sticky a 90px del top (debajo del header). Width fixed 240px.
- `.op-filters__group` — un bloque por dimensión. `border-bottom: 1px solid var(--line)` entre grupos.
- `.op-filters__title` — `font-family: var(--display)` Bebas Neue, 14px uppercase.
- `.op-filters__option` — `<label>` con flex, gap 8px, padding vertical 6px. Incluye:
  - `<input type="checkbox">` custom (visual) — 18px, border `var(--line-2)`, fill `var(--carmesi)` cuando checked.
  - Swatch de color (12px circle) para Color group.
  - Texto en `var(--bone)` 13px IBM Plex.
- `.op-filters__clear` — botón "Limpiar" con `var(--btn-ghost)`.
- En mobile (≤768px):
  - `.op-filters` colapsado con `<details>` nativo.
  - Botón "Filtros" muestra contador `(N)`.

Reutiliza tokens existentes (`--carmesi`, `--carbon`, `--line`, etc.) del tema. **No introducir variables nuevas.**

---

## 9. Performance

### 9.1 Índices SQL recomendados

Documentar en `docs/op-filters-sql-indexes.md`. Aplicación es **responsabilidad del dueño** (necesita acceso DB en Hostinger):

```sql
-- Solo si el catálogo OP supera ~500 productos. Hoy con 17, las queries son rápidas sin índices.
ALTER TABLE wp_postmeta ADD INDEX onplay_op_color (meta_key(20), meta_value(20));
ALTER TABLE wp_postmeta ADD INDEX onplay_op_card_type (meta_key(20), meta_value(20));
ALTER TABLE wp_postmeta ADD INDEX onplay_op_alt (meta_key(20), meta_value(5));
```

> **Importante**: `wp_postmeta` ya tiene índices nativos. Estos índices compuestos son redundantes con `meta_key` solo, pero ayudan en queries con `meta_value LIKE`. Validar con `EXPLAIN` antes de aplicar en producción.

### 9.2 Otros

- **No hacer `posts_per_page = -1` jamás.** Las queries respetan el `posts_per_page` por defecto del archive (12).
- **No agregar conteos por opción** (`Red (42)`) en MVP — agregaría una query por checkbox.
- **No cachear el panel HTML** — los checkboxes deben reflejar el estado de la URL en tiempo real.
- **Cachear la categoría term** con `wp_cache_get/set` si se invoca varias veces en el render.

---

## 10. Compatibilidad

- **NO romper Magic.** Toda la lógica está protegida por `onplay_op_is_archive()`.
- **Compatible con M6** (filtros Magic): los archives de Magic no entran en `onplay_op_is_archive()` y reciben sus filtros normales.
- **Compatible con paginación** WooCommerce: `pre_get_posts` solo añade `meta_query`, no toca `posts_per_page` ni `orderby`.
- **Compatible con búsqueda interna del header** (M5): mientras la búsqueda no use params `op_*`, no interfiere. Si M-OP-buscador (sub-módulo paralelo) introduce un buscador OP-only, ese módulo debe coordinar con este.
- **Compatible con caché de página** (M10): el dueño debe configurar la regla "Cache Query Strings" para `op_color`, `op_type`, `op_alt` o excluir las URL con esos params.

---

## 11. Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|
| Binder OP cambia el formato de `_color` | Baja | Alto | Documentar el contrato en spec. Si cambia, este módulo se rompe — coordinar antes con el equipo del Binder |
| Más de 500 productos OP causan slow query | Media | Medio | Crear índices SQL del §9.1 |
| Caché del hosting muestra resultados stale | Alta | Medio | Configurar Cache Query Strings o excluir URLs con `op_*` |
| Conflicto con plugin HUSKY (decisión M0: eliminar) | Baja | Alto | Confirmar que HUSKY ya está desactivado/eliminado antes de implementar |
| Categoría OP usa slug distinto a `one-piece-tcg` | Media | Alto | Verificar en Paso 0 del prompt antes de codear |

---

## 12. Plan de testing manual

En orden, en ambiente local con DB copia:

1. Verificar pre-requisitos (5 productos OP en stock, categoría correcta, Binder OP escribió meta).
2. Activar el módulo (require_once en functions.php + build SCSS/JS).
3. Visitar `/categoria-producto/one-piece-tcg/`. Confirmar que aparece el panel.
4. Visitar `/categoria-producto/magic-the-gathering/`. Confirmar que el panel **NO** aparece (panel Magic sí).
5. Click en checkbox "Red". URL debe actualizarse a `?op_color=red`. Grid se refresca. Productos mostrados tienen `_color` que matchea Red (incluyendo duales).
6. Combinar 2 colores + 1 tipo. Verificar AND lógica.
7. Probar `?op_alt=alt` directamente en URL. Checkboxes correspondientes deben aparecer marcados.
8. Click en "Limpiar filtros". URL queda limpia, checkboxes desmarcados.
9. Probar back/forward del navegador.
10. Probar en mobile (DevTools 375px). Panel debe colapsar bajo "Filtros (N)".
11. Probar con JS deshabilitado: form submit recarga la página, filtros siguen funcionando vía URL.
12. Lighthouse mobile en categoría OP. Reportar score.
13. `debug.log` sin warnings nuevos.

---

## 13. Rollback

Si algo sale mal en producción:

1. Comentar la línea `require_once .../inc/op-filters.php` en `functions.php`.
2. Subir el cambio.
3. Limpiar caché del hosting.

El módulo es **completamente removible**. No modifica DB, no escribe meta, no toca productos. Solo agrega un hook a `pre_get_posts` y renderiza markup.

---

## 14. Decisiones abiertas

- [ ] ¿Incluir filtro de Set específico (`_set_full_code`) en el panel? Hoy lo cumple la jerarquía de categorías. Decisión: **NO en MVP**, agregar solo si el dueño lo pide.
- [ ] ¿Incluir filtro Block Icon? Decisión: **NO en MVP** hasta que `_block_icon` se popule.
- [ ] ¿Incluir filtro de Rareza? Decisión: **NO en MVP**, agregar si métricas lo justifican.
