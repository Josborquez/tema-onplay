# M-OP-filtros — Índices SQL recomendados

Snippets para acelerar las queries de filtros OP cuando el catálogo crezca.
**Aplicación es responsabilidad del dueño**: requiere acceso DB en Hostinger
(panel hPanel → MySQL → phpMyAdmin), no del tema.

---

## Cuándo aplicar

- Hoy con 25 productos OP, las queries `meta_query` corren bajo 50 ms — no son cuello.
- **A partir de ~500 productos OP**, queries con `_color LIKE '%Red%'` empiezan a
  hacer table-scan completo. Es el momento de aplicar.
- Validar antes con `EXPLAIN` (ver §Verificación).

---

## SQL — usar SOLO el bloque que aplique al engine

`wp_postmeta` ya tiene índices nativos en `meta_key` y `(meta_key, meta_id)`. Los
índices compuestos abajo agregan cobertura para `meta_value` truncado a la
cantidad mínima útil — `meta_value(20)` es suficiente porque los valores OP son
cortos (`Red`, `LEADER`, `yes`, `OP-15`, etc).

### MariaDB / MySQL 8+

```sql
ALTER TABLE wp_postmeta ADD INDEX onplay_op_color
  (meta_key(20), meta_value(30));

ALTER TABLE wp_postmeta ADD INDEX onplay_op_card_type
  (meta_key(20), meta_value(15));

ALTER TABLE wp_postmeta ADD INDEX onplay_op_alt
  (meta_key(20), meta_value(5));
```

`meta_value(30)` para `_color` cubre con holgura `Red/Yellow/Black` (peor caso
~16 chars). `(15)` para `_card_type` cubre `CHARACTER` (9). `(5)` para
`_is_alt_art` cubre `yes`/`no`.

### MySQL 5.7

`meta_value` en `wp_postmeta` es `LONGTEXT` — los índices sobre LONGTEXT
**requieren** prefijo. La sintaxis arriba sirve igual. No hay diferencia.

---

## Verificación con EXPLAIN

Antes y después de aplicar, correr en phpMyAdmin:

```sql
EXPLAIN
SELECT p.ID
FROM wp_posts p
INNER JOIN wp_postmeta pm ON pm.post_id = p.ID
WHERE p.post_type = 'product'
  AND p.post_status = 'publish'
  AND pm.meta_key = '_color'
  AND pm.meta_value LIKE '%Red%';
```

- **Sin índice**: `type=ALL`, `Extra=Using where`. Escaneo de tabla completa.
- **Con índice**: `type=ref`, `key=onplay_op_color`. Lookup directo.

Si el `EXPLAIN` no cambia tras aplicar, revisar que el optimizer no esté
prefiriendo el índice nativo por estadísticas obsoletas (`ANALYZE TABLE
wp_postmeta;`).

---

## Rollback

```sql
ALTER TABLE wp_postmeta DROP INDEX onplay_op_color;
ALTER TABLE wp_postmeta DROP INDEX onplay_op_card_type;
ALTER TABLE wp_postmeta DROP INDEX onplay_op_alt;
```

Los índices son aditivos — eliminarlos no rompe el módulo, solo vuelve a las
queries no-aceleradas.

---

## Costo de almacenamiento

Cada índice cuesta ≈ 60 bytes × N filas de `wp_postmeta` con ese `meta_key`.
Con ~500 productos OP × 3 meta_keys = ≈ 90 KB total. Despreciable en el plan
Hostinger actual.
