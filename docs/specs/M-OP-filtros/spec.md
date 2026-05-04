# Spec — M-OP-filtros: Panel facetado de filtros para One Piece TCG

**Tipo**: Módulo add-on al tema `onplay` (post-M9, paralelo a M10).
**Repositorio**: `tema-onplay`
**Estado**: Draft — pendiente de aprobación del dueño antes de implementar.
**Fecha**: 2026-05-03
**Bloque origen en `PENDING-CLAUDE.md`**: "Catálogo / buscador autónomo One Piece — Binder OP ya operativo".

---

## 1. Resumen ejecutivo

Implementar un panel de filtros laterales en las páginas de archive de One Piece TCG (`/categoria-producto/one-piece-tcg/` y descendientes) equivalente en función al panel de Magic (M6) pero adaptado a los meta que escribe el Binder OP. Reproduce la experiencia del listado oficial de Bandai (`https://en.onepiece-cardgame.com/cardlist/`).

Sin este módulo, la categoría One Piece TCG hereda el panel de filtros Magic, que muestra dimensiones inválidas (mana cost, formatos legales, rareza Magic) que no aplican a OP.

---

## 2. Historia de usuario principal

> Como **comprador de One Piece TCG en onplay.cl**, quiero **filtrar el catálogo por color, tipo de carta y arte alternativo**, para **encontrar rápido la carta que necesito sin recorrer 200+ productos**.

### Historias secundarias

- Como comprador en mobile, quiero un panel colapsable con contador de filtros activos para no perder espacio de pantalla.
- Como comprador, quiero compartir un link con mis filtros aplicados (`?op_color=red,green&op_type=leader`).
- Como administrador, quiero que el panel de Magic siga funcionando sin cambios en su categoría.

---

## 3. Criterios de aceptación

### CA-1: Panel visible solo en categoría OP
- **Given** un usuario navegando `/categoria-producto/one-piece-tcg/` o cualquier subcategoría descendiente
- **When** la página termina de renderizar
- **Then** ve el panel `op-filters` a la izquierda en desktop / colapsable en mobile, con las 3 dimensiones: Color, Card Type, Illustration Type.
- **Y** en `/categoria-producto/magic-the-gathering/` el panel de Magic (M6) sigue funcionando idéntico.

### CA-2: Filtro por Color con soporte de duales
- **Given** la URL `?op_color=red`
- **When** la página renderiza
- **Then** se muestran productos con `_color = "Red"` **y** productos con `_color = "Red/Yellow"` (duales).
- **Verificación específica**: `EB04-001 Jewelry Bonney` (`_color = "Red/Yellow"`) aparece al filtrar por Red **y** al filtrar por Yellow.

### CA-3: Multi-select por dimensión, AND entre dimensiones
- **Given** la URL `?op_color=red,green&op_type=leader`
- **Then** se muestran productos donde (`_color` matchea Red **OR** Green) **AND** `_card_type = "LEADER"`.

### CA-4: Filtro Illustration Type
- **Given** la URL `?op_alt=alt`
- **Then** se muestran solo productos con `_is_alt_art = "yes"` (filenames `_p[N]` o `_r[N]`).
- **Y** `?op_alt=normal` muestra solo productos con `_is_alt_art = "no"`.
- **Y** `?op_alt=normal,alt` muestra todos (equivalente a no filtrar).

### CA-5: State sincronizado entre URL y checkboxes
- **Given** una URL con filtros aplicados directamente (`?op_color=blue&op_type=character`)
- **When** la página carga por primera vez
- **Then** los checkboxes correspondientes aparecen marcados **sin necesidad de JS** (render server-side).
- **Y** marcar/desmarcar un checkbox actualiza la URL via `history.pushState` sin recargar.
- **Y** botón atrás del navegador restaura el estado anterior.

### CA-6: Reset de paginación al cambiar filtro
- **Given** un usuario en página 3 con un filtro aplicado
- **When** marca otro filtro
- **Then** vuelve a página 1.

### CA-7: Botón "Limpiar filtros"
- **Given** filtros aplicados
- **When** el usuario hace click en "Limpiar filtros"
- **Then** la URL queda sin params `op_*`, los checkboxes se desmarcan, la grilla se actualiza.

### CA-8: Panel mobile con contador
- **Given** viewport ≤768px
- **When** la página carga
- **Then** el panel está colapsado bajo un botón "Filtros" que muestra `(N)` donde N es la cantidad de checkboxes activos.

### CA-9: Refresco AJAX sin recargar
- **Given** un cambio de checkbox
- **When** se dispara
- **Then** la grilla y la paginación se reemplazan con un fetch a `?op_partial=1`, en menos de 500ms para una categoría con ≤200 productos.
- **Y** ningún otro elemento de la página parpadea (header, footer, sidebar).

### CA-10: Compatibilidad con paginación de WooCommerce
- **Given** filtros aplicados que devuelven 50 productos
- **When** el usuario va a página 2
- **Then** los filtros se preservan en la URL y los productos de página 2 también respetan el filtro.

### CA-11: Casos de prueba exactos

| Filtro | Producto que DEBE aparecer | Producto que NO debe aparecer |
|---|---|---|
| `?op_color=red` | EB04-001 Jewelry Bonney (`_color=Red/Yellow`) | OP07-XXX Bonney verde puro |
| `?op_color=yellow` | EB04-001 Jewelry Bonney | Cartas solo Red |
| `?op_type=leader` | EB04-001 (Leader) | EB04-002 si es Character |
| `?op_alt=alt` | OP07-001_p1.png | OP07-001.png (sin sufijo) |
| `?op_color=red&op_type=leader` | EB04-001 (Red/Yellow + LEADER) | EB04-002 si es Character aunque sea Red |

---

## 4. Reglas de negocio

- **No introducir custom taxonomies para OP** — decisión heredada de `CLAUDE.md` §3.7 y `PENDING-CLAUDE.md`. Filtros operan vía `meta_query`.
- **Soporte de duales con `LIKE`** — `_color` puede ser `"Red/Yellow"`. Filtrar por Red implica `LIKE '%Red%'`, no `= 'Red'`.
- **`_card_type` ya viene en UPPERCASE** del Binder OP. Comparar con `=` o `IN` directos.
- **`_is_alt_art` viene como string `"yes"`/`"no"`** — comparar string, no boolean.
- **`_block_icon` queda fuera del MVP** hasta que el Binder OP lo popule (ver `PENDING-OWNER.md`).
- **Variantes condición/idioma no aplican a OP** — todo es NM/EN, no se renderiza tabla `print_key` como en Magic.
- **El panel debe degradar sin JS** — render server-side de checkboxes según `?op_*` params; el JS solo agrega refresco sin recarga.
- **Strings al usuario en español de Chile** — text domain `onplay`.

---

## 5. Casos límite y errores

- **Categoría OP vacía**: si filtros devuelven 0 productos, mostrar empty state "No encontramos cartas con esos filtros" + botón "Limpiar filtros".
- **Param inválido en URL** (`?op_color=mauve`): ignorar silenciosamente, no romper.
- **Producto OP sin meta `_color`**: no aparece en ningún filtro de color (filtrado por existencia del meta).
- **Caché agresiva del hosting**: la URL con `op_*` debe estar fuera de caché HTML, o configurar regla "Cache Query Strings" para los params `op_*` (responsabilidad del dueño en M10).
- **JS deshabilitado**: el form se envía con submit normal y la página recarga; los checkboxes siguen funcionando vía URL.

---

## 6. Fuera de alcance

- Filtro Block Icon (depende de que el Binder OP lo populé).
- Filtro por rareza (`_rarity_code`) — postergado hasta tener métricas de uso. Solo se implementa si el dueño lo pide.
- Iconografía oficial OP (símbolos de bloque, mana de OP) — pendiente de coordinar fuente.
- Conteos por opción (`Red (42)`, `Yellow (18)`) — costoso, postergado.
- Persistir filtros del usuario en cookie/localStorage.
- Tabla de variantes por carta — no aplica a OP por modelo de datos.
- Cambios al Binder OP — su contrato es inmutable desde este módulo.

---

## 7. Dependencias

- **`CLAUDE.md` §3.7** vigente (deuda aceptada de meta_query).
- **Binder OP operativo** y escribiendo los meta documentados.
- **Al menos 5 productos OP en DB local** para QA realista.
- **Categoría `one-piece-tcg` existe** como term de `product_cat`.

---

## 8. Métricas de éxito

- Lighthouse mobile en `/categoria-producto/one-piece-tcg/` ≥ 80 (target M10 es 85, este módulo no debe bajarlo más de 5 puntos).
- Time-to-interactive del panel < 1.5s en 4G simulado.
- 0 errores en consola al aplicar combinaciones de 3 filtros.
- 0 warnings en `debug.log` por queries malformadas.
- Compartir una URL con filtros entre dos navegadores muestra el mismo resultado.

---

## 9. Dudas pendientes (responder antes de pasar a `plan.md`)

- [NECESITA ACLARACIÓN: ¿la categoría raíz se llama `one-piece-tcg` o el slug real es otro? Verificar con `wp term get product_cat one-piece-tcg`.]
- [NECESITA ACLARACIÓN: ¿queremos incluir filtro por `_set_full_code` (set específico) en el panel? Hoy la jerarquía de categorías ya cumple ese rol, pero el panel de Magic sí lo tiene como filtro adicional.]
- [NECESITA ACLARACIÓN: ¿se popula `_block_icon` o lo dejamos fuera definitivamente del MVP?]
