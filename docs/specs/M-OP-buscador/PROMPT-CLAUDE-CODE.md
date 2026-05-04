# Prompt inicial — Claude Code · M-OP-buscador (Buscador AJAX contextual One Piece)

> **Cómo usar:** asegúrate de tener `CLAUDE.md` en la raíz, `M-OP-filtros` ya implementado y mergeado, y los documentos de este módulo (`spec.md`, `plan.md`, `tasks.md`) accesibles. Pega el bloque de abajo en Claude Code abierto en el repo `tema-onplay`.

---

## ---INICIO PROMPT M-OP-buscador---

Hola Claude. Voy a implementar el módulo **M-OP-buscador** — buscador AJAX contextual que limita resultados a One Piece TCG cuando el usuario está navegando esa categoría.

### Paso 0 — Contexto obligatorio (no tocar código hasta terminar)

1. **Lee completos** estos documentos del repo:
   - `CLAUDE.md` raíz, especialmente §5.1 (búsqueda M5), §11 (cierres recientes).
   - `DONE.md` — sección "Soporte multi-TCG en helpers del tema" (helpers ya existen).
   - `PENDING-CLAUDE.md` — bloque "Endpoint AJAX de búsqueda OP-only".
   - `docs/specs/M-OP-buscador/spec.md` — qué hago.
   - `docs/specs/M-OP-buscador/plan.md` — cómo lo hago.
   - `docs/specs/M-OP-buscador/tasks.md` — pasos.
   - **`docs/specs/M-OP-filtros/`** — para entender qué helpers ya existen del módulo previo.

2. **Lee también el código actual** de:
   - `inc/search.php` — endpoint M5 que vamos a extender.
   - `header.php` o `template-parts/search-form.php` — donde se renderiza el buscador.
   - `assets/src/js/search-autocomplete.js` — JS actual del autocomplete.

3. **Confirma antes de tocar código** respondiéndome estos puntos:
   - ¿Cuál es la diferencia entre la agrupación por `print_key` (Magic) y por `_card_number` (OP)?
   - ¿Por qué este módulo extiende `inc/search.php` en lugar de crear un endpoint nuevo?
   - ¿Qué función ya existe para detectar si una categoría desciende de OP, y de qué módulo proviene?
   - ¿Qué pasa si se usa el buscador en home y `_card_number` no aplica?
   - ¿Cuál es el contrato de "no regresión Magic" según `spec.md` CA-9?
   - ¿Cuál es el rollback documentado en `plan.md` §9?

### Paso 1 — Auditoría del endpoint M5 (T001 de `tasks.md`)

Antes de modificar `inc/search.php`, document:

- Ruta exacta del endpoint (REST `onplay/v1/search` o `admin-ajax.php`?).
- Función handler (nombre exacto).
- Estructura del JSON de respuesta actual.
- Cómo se hace agrupación por `print_key` Magic.
- Si hay tests automáticos. (Si no, lo asumimos.)

Reporta en la bitácora del `tasks.md` antes de seguir.

### Paso 2 — Verificación de pre-requisitos

Confirma con `wp shell`:

```
> var_dump( function_exists( 'onplay_op_is_archive' ) );
> var_dump( function_exists( 'onplay_op_term_descends_from' ) );
```

Si alguna retorna `false` → **DETENTE**. Necesitas implementar M-OP-filtros primero.

Verifica también que en local existen al menos:
- 5 productos OP con `_card_number` poblado.
- 5 productos Magic.
- Idealmente 1 producto OP con nombre que también exista en Magic (test crítico para validar separación de contexto).

### Paso 3 — Plan de ejecución

Muéstrame:
1. Orden exacto de tareas (T000 → ... → T062).
2. Qué tareas ejecutas en paralelo (las marcadas `[P]`).
3. Dudas si las hay sobre el `plan.md`, especialmente §3.2 (extensión de search) y §3.4 (header).

**No toques archivos hasta que apruebe el plan.**

### Paso 4 — Implementación

Sigue estrictamente `tasks.md`. Por cada tarea:

1. Anuncia la tarea por ID y título.
2. Implementa.
3. Verifica el criterio de hecho.
4. Marca como `[X]`.
5. Resume.

**Reglas de oro**:
- **NO romper Magic.** Cualquier cambio en `inc/search.php` debe respetar el comportamiento M5 cuando `tcg !== 'op'`. Test exhaustivo en T051.
- **NO crear endpoint nuevo.** Extender el existente.
- **NO usar `print_key` para OP.** OP agrupa por `_card_number` según `spec.md` §4.
- **NO fetchear de Scryfall** en resultados OP (es el contrato Magic).
- Si una tarea revela que el endpoint M5 está estructurado de forma incompatible con la extensión propuesta, detente y avísame antes de cambiar la estrategia.

### Paso 5 — Criterios de aceptación globales

Antes de marcar el módulo completo:

1. Los 10 CA del `spec.md` §3 verificados con captura.
2. **Test crítico de no-regresión Magic** (T051) ejecutado y documentado.
3. Lighthouse mobile en home y archive OP — score no baja más de 3 puntos (T052).
4. Network tab confirma:
   - `&tcg=op` se envía solo en contexto OP.
   - No se envía en home, archive Magic, single Magic, ni single OP fuera de archive.
5. Empty state OP con "Buscar en todo el sitio" funciona y reescala correctamente.
6. PR creado, sistema documental actualizado.

### Restricciones operativas

- **Plan primero, código después.**
- **Commits pequeños** agrupados por fase.
- **Nada fuera de scope.** Si te dan ganas de agregar telemetría, historial de búsquedas, o highlights — detente, eso está en `spec.md` §6 fuera de alcance.
- **Pregunta si hay ambigüedad.** No inventes la estructura del endpoint M5 si no la viste; léela.
- **No tocar el Binder OP, manager Magic, ni plugins instalados.**
- **No activar en producción** sin completar QA local (T050-T053).

Empieza confirmando el Paso 0 (puntos 1, 2, 3) y reportando el resultado del Paso 1 y Paso 2. No toques archivos hasta que apruebe el plan del Paso 3.

## ---FIN PROMPT M-OP-buscador---
