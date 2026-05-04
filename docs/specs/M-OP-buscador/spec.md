# Spec — M-OP-buscador: Buscador AJAX contextual para One Piece TCG

**Tipo**: Módulo add-on al tema `onplay`, dependiente de M-OP-filtros y de M5 (búsqueda Magic ya operativa).
**Repositorio**: `tema-onplay`
**Estado**: Draft — pendiente de aprobación del dueño antes de implementar.
**Fecha**: 2026-05-03
**Bloque origen en `PENDING-CLAUDE.md`**: "Endpoint AJAX de búsqueda OP-only — variante de `inc/search.php` que filtra `product_cat=one-piece-tcg` para el header search cuando se está navegando dentro de One Piece".

---

## 1. Resumen ejecutivo

Hacer que el buscador del header **adapte sus resultados al contexto** del usuario. Cuando el usuario está navegando One Piece TCG (cualquier archive descendiente de la categoría OP, o una ficha de producto OP), el buscador limita los resultados al catálogo OP. Cuando está en Magic o en otra parte del sitio, el buscador busca en todo el catálogo (comportamiento actual de M5).

Sin este módulo, un comprador que escribe "Bonney" mientras navega One Piece puede recibir resultados de Magic que también contengan ese texto, o productos de cualquier otra categoría. La experiencia se vuelve confusa.

---

## 2. Historia de usuario principal

> Como **comprador navegando la sección One Piece**, quiero **que el buscador me muestre solo cartas One Piece**, para **no recibir cartas Magic con nombre similar y encontrar lo que busco más rápido**.

### Historias secundarias

- Como comprador en Magic, quiero que el buscador siga funcionando como hoy (todo el catálogo Magic), sin cambios.
- Como comprador en home, quiero que el buscador busque en todos los TCGs disponibles (Magic + One Piece).
- Como administrador, quiero que el comportamiento sea predecible y se vea reflejado visualmente (ej. "Buscando en One Piece") para que el usuario entienda por qué los resultados se ven distintos.

---

## 3. Criterios de aceptación

### CA-1: Detección de contexto OP
- **Given** un usuario navegando `/categoria-producto/one-piece-tcg/...` o un single product cuyo `product_cat` es descendiente de `one-piece-tcg`
- **When** abre el buscador del header
- **Then** la UI muestra un indicador "Buscando en One Piece" (texto pequeño en `var(--mid-2)` debajo del input).
- **Y** los resultados del autocomplete se limitan a productos OP.

### CA-2: Búsqueda global fuera del contexto OP
- **Given** un usuario en home, en archive Magic, o en una ficha Magic
- **When** abre el buscador
- **Then** la UI **NO** muestra el indicador de contexto (queda en modo global).
- **Y** los resultados son del catálogo completo (comportamiento M5 actual, sin cambios).

### CA-3: Búsqueda por nombre de carta OP
- **Given** un usuario en contexto OP escribe "Bonney"
- **When** debounce 200ms se cumple
- **Then** el autocomplete muestra cartas OP cuyo `post_title` contiene "Bonney" (case insensitive), agrupadas por `_card_number` (ej. EB04-001 aparece 1 vez aunque haya 3 productos con ese number).
- **Y** ningún resultado de Magic aparece.

### CA-4: Búsqueda por número de carta
- **Given** un usuario en contexto OP escribe "EB04-001"
- **When** se cumple el debounce
- **Then** aparece la carta `EB04-001 Jewelry Bonney`.
- **Y** la búsqueda por `_card_number` funciona aunque el usuario use guión normal o espacios.

### CA-5: Resultados con metadata visible
- **Given** resultados del autocomplete OP
- **When** se renderizan
- **Then** cada item muestra:
  - Thumbnail (60×84px, ratio 5:7).
  - Nombre de la carta.
  - Set en pequeño (`_set_full_code`, ej. `OP15-EB04`).
  - Precio del producto más barato del grupo.
  - Badge "Alt Art" si todas las versiones son `_is_alt_art = "yes"`.

### CA-6: Performance
- **Given** un catálogo OP con hasta 500 productos
- **When** el usuario escribe en el input
- **Then** el primer resultado aparece en <500ms desde el último keystroke (incluye debounce + fetch + render).

### CA-7: Empty state
- **Given** un usuario en contexto OP escribe un texto sin matches (ej. "xyzwwww")
- **Then** se muestra mensaje "No encontramos cartas en One Piece con 'xyzwwww'" + opción "Buscar en todo el sitio" que reescala a búsqueda global.

### CA-8: Submit completo
- **Given** un usuario hace Enter en el input en contexto OP
- **When** la página redirige
- **Then** va a `/categoria-producto/one-piece-tcg/?s={query}` (no a `/?s={query}` global).
- **Y** la página de resultados muestra solo productos OP que matchean.

### CA-9: Sin regresión de M5
- **Given** un usuario en archive Magic o home
- **When** usa el buscador
- **Then** el comportamiento es idéntico al actual: autocomplete por `print_key` Magic, fallback "Buscar en Scryfall" si 0 resultados, submit a búsqueda global.

### CA-10: Casos exactos

| Contexto | Query | Resultado esperado |
|---|---|---|
| `/categoria-producto/one-piece-tcg/` | "Bonney" | Solo productos OP, agrupados por `_card_number`, EB04-001 visible |
| `/categoria-producto/one-piece-tcg/booster-packs/op15-eb04/` | "EB04-001" | EB04-001 Jewelry Bonney, único resultado |
| `/categoria-producto/magic-the-gathering/` | "Bonney" | Sin cambio (búsqueda global Magic, M5) |
| Home | "Bonney" | Búsqueda global (puede mostrar OP + Magic si ambos tienen match) |
| Single product OP | "Bolt" | Solo OP (contexto detectado) |

---

## 4. Reglas de negocio

- **Detección de contexto debe ser inequívoca.** Si hay duda, default a global. No discriminar a usuarios en contexto ambiguo.
- **No introducir un endpoint AJAX nuevo.** Reutilizar `inc/search.php` con un parámetro `tcg=op` opcional.
- **Reglas de agrupación OP**: agrupar por `_card_number` (cada número es una carta lógica), no por `print_key` (concepto Magic que no aplica a OP).
- **El indicador de contexto se renderiza server-side** en el header cuando `onplay_op_is_archive()` o `onplay_op_is_single()` retornan true.
- **Submit form respeta el contexto**: el `<form>` apunta a `/categoria-producto/one-piece-tcg/` cuando contexto OP, a `/?s=` cuando global.
- **Strings al usuario en español de Chile**, text domain `onplay`.
- **Imágenes**: usar `_image_filename` o `wc_placeholder_img` si no hay thumbnail. No fetchear de Scryfall (es para Magic, no OP).

---

## 5. Casos límite y errores

- **Usuario en `/wp-admin/`**: no aplica, el buscador no se renderiza ahí.
- **Búsqueda con menos de 2 caracteres**: ignorar (no fetch).
- **Búsqueda con caracteres especiales** (`'`, `"`, `<`, `>`): sanitizar con `sanitize_text_field`.
- **Catálogo OP vacío**: el indicador "Buscando en One Piece" se muestra igual, pero el autocomplete dice "Aún no hay cartas en One Piece. Búscalas en todo el sitio".
- **Error de fetch**: mostrar "Error al buscar. Intenta de nuevo" sin romper la página.
- **Producto OP con `post_title` vacío** (caso degenerado): excluir del resultado.

---

## 6. Fuera de alcance

- Filtros dentro del autocomplete (color, tipo). El usuario filtra después de aterrizar en el listado.
- Highlight de términos en los resultados (nice-to-have, no MVP).
- Historial de búsquedas recientes.
- Sugerencias inteligentes ("¿quisiste decir...?").
- Búsqueda por texto del oracle / habilidad de la carta (no aplica a OP que no tiene oracle).
- Soporte multi-idioma de la búsqueda (hoy todos los productos OP están en EN).
- Cambios al endpoint `inc/search.php` que afecten Magic. **Solo extensión, no modificación.**

---

## 7. Dependencias

- **M-OP-filtros completado** (provee `onplay_op_is_archive()`).
- **M5 (búsqueda Magic) operativo** (provee `inc/search.php` y la UI base del autocomplete).
- **`onplay_tcg_from_sku()`** ya existe en el tema según `DONE.md` ("Soporte multi-TCG en helpers del tema").
- **Binder OP** poblando `_card_number`, `_image_filename`, `_set_full_code`.

---

## 8. Métricas de éxito

- **TTFR** (time to first result) < 500ms en P95 con catálogo de 500 productos OP.
- 0 errores de "no encuentro X que sé que existe" reportados durante la primera semana.
- Tasa de bounce en búsqueda OP < 50% (usuario llega al listado y al menos hace un click más).
- 0 regresiones de M5 medidas con tests manuales.

---

## 9. Dudas pendientes

- [NECESITA ACLARACIÓN: ¿queremos un toggle visible "buscar en todo el sitio" siempre, o solo cuando empty state? Sugerencia: solo en empty state para no distraer.]
- [NECESITA ACLARACIÓN: ¿el indicador "Buscando en One Piece" debe ser dismissable? Sugerencia: NO en MVP, mantenerlo siempre visible en contexto OP para evitar confusión.]
- [NECESITA ACLARACIÓN: ¿soportamos búsqueda por `_set_full_code` exacto (`OP15-EB04`) o solo por nombre + card_number? Sugerencia: empezar con nombre + card_number, agregar set si métricas lo justifican.]
