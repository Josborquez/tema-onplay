# Prompt inicial — Claude Code · M-OP-filtros (Panel facetado One Piece TCG)

> **Cómo usar:** asegúrate de tener `CLAUDE.md` (v3 o superior) en la raíz del repo `tema-onplay`, y los documentos `spec.md`, `plan.md`, `tasks.md` de este módulo accesibles para Claude (commit los archivos al repo, en `docs/specs/M-OP-filtros/` por ejemplo). Después abre Claude Code en el directorio del tema y pega el bloque de abajo.

---

## ---INICIO PROMPT M-OP-filtros---

Hola Claude. Voy a implementar el módulo **M-OP-filtros** — panel facetado de filtros para la categoría One Piece TCG en onplay.cl.

### Paso 0 — Contexto obligatorio (no tocar código hasta terminar este paso)

1. **Lee completos** estos documentos del repo, en este orden:
   - `CLAUDE.md` raíz (especialmente §3.7 sobre la deuda meta_query OP, §6 roadmap, §11 cierres recientes).
   - `PENDING-CLAUDE.md` (bloque "Catálogo / buscador autónomo One Piece").
   - `PENDING-OWNER.md` (bloque "Binder OP — operativo, pendientes residuales").
   - `docs/specs/M-OP-filtros/spec.md` — qué hago.
   - `docs/specs/M-OP-filtros/plan.md` — cómo lo hago.
   - `docs/specs/M-OP-filtros/tasks.md` — pasos.

2. **Confirma antes de escribir código** respondiéndome estos puntos:
   - ¿Cuál es la deuda técnica que `CLAUDE.md` §3.7 acepta y cómo la respeta este módulo?
   - ¿Qué meta poblá hoy el Binder OP, según `PENDING-CLAUDE.md`?
   - ¿Cuál es el contrato de `_color` que justifica usar `LIKE` en lugar de `=`?
   - ¿Por qué este módulo NO crea custom taxonomies?
   - ¿Qué deja explícitamente fuera de scope el `spec.md` §6?
   - ¿Cuál es la decisión sobre `_block_icon` y por qué?

### Paso 1 — Auditoría del estado real (T000 de `tasks.md`)

Antes de tocar archivos, ejecuta y reporta:

```bash
wp post list --post_type=product --tax_query='[{"taxonomy":"product_cat","terms":"one-piece-tcg"}]' --format=count
wp term get product_cat one-piece-tcg --field=name --field=slug
wp post meta list <ID_DE_UN_PRODUCTO_OP> --format=table
```

Si:
- Hay menos de 5 productos OP en stock → avísame y pídeme que cargue más antes de seguir.
- Falta `_color` o `_card_type` en el meta → detente, esto es un bloqueo del Binder OP.
- El slug de la categoría no es `one-piece-tcg` → ajusta la spec antes de implementar.

Reporta el resultado en la bitácora del `tasks.md`.

### Paso 2 — Plan de ejecución

Muéstrame:
1. El orden exacto en que vas a ejecutar los tasks (T001 → T002 → ... → T052).
2. Cuáles son paralelizables y cuáles no (los marcados `[P]` en `tasks.md`).
3. Dudas si las hay respecto al `plan.md` o al `spec.md`.

**No toques archivos hasta que yo apruebe este plan.**

### Paso 3 — Implementación

Sigue estrictamente `tasks.md`. Para cada tarea:

1. Anuncia "Iniciando T0XX — <título>".
2. Implementa.
3. Verifica el criterio de hecho documentado en la tarea.
4. Marca como `[X]` en `tasks.md`.
5. Resume qué cambió y qué probar.

**Regla de oro**: NO escribas código que viole `CLAUDE.md` §3.7 (no custom taxonomies para OP), §9 (convenciones PHP/SCSS/JS), ni el contrato del Binder OP. Si una tarea revela una contradicción, detente y avísame.

### Paso 4 — Criterios de aceptación globales

Antes de marcar el módulo completo:

1. Los 11 criterios de aceptación del `spec.md` §3 verificados con captura.
2. Magic NO sufrió regresiones (CA-1, T042).
3. Lighthouse mobile en `/categoria-producto/one-piece-tcg/` ≥ 80 (T041).
4. `debug.log` sin warnings nuevos.
5. PR creado con commits convencionales (T050).
6. `CLAUDE.md` §11 + `DONE.md` + `PENDING-CLAUDE.md` actualizados (T051).

### Restricciones operativas

- **Plan primero, código después.** Pasos 0, 1, 2 antes de tocar nada.
- **Commits pequeños y descriptivos** agrupados por tarea o sub-fase.
- **Nada fuera de scope.** Si te dan ganas de implementar el buscador OP-only, detente — eso es M-OP-buscador, otro módulo.
- **Pregunta si hay ambigüedad.** No inventes valores de meta, slugs, tokens CSS o nombres de hooks.
- **No modifiques** el Binder OP, el manager Magic, ni los plugins instalados.
- **No actives el módulo en producción** hasta que el QA local apruebe (T040-T042).

Empieza confirmando el Paso 0 (puntos 1 y 2) y mostrándome los resultados del Paso 1. No toques archivos hasta que yo apruebe el plan del Paso 2.

## ---FIN PROMPT M-OP-filtros---
