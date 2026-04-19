# Prompt inicial — Claude Code (Módulo 1: Fundación del tema con design de Claude Design)

> **Cómo usar:** asegúrate de tener el archivo `CLAUDE.md` (v2 o superior) en la raíz del repo. Después abre Claude Code en el directorio del tema y pega el bloque de abajo. Repite el patrón para cada módulo siguiente.

---

## ---INICIO PROMPT INICIAL MÓDULO 1---

Hola Claude. Voy a arrancar el desarrollo del tema WordPress custom para **onplay.cl**, la tienda de singles de Magic.

### Paso 0 — Contexto obligatorio (no tocar código hasta terminar este paso)

1. **Lee completo el archivo `CLAUDE.md`** en la raíz de este repo. Es la fuente de verdad del proyecto: stack, convenciones, roadmap, modelo de datos, seguridad. No improvises sobre cosas que estén ahí.

2. **Fetchea e implementa el design file de Claude Design.** Ejecuta:
   ```
   Fetch this design file, read its README, and implement the relevant aspects of the design.
   https://api.anthropic.com/v1/design/h/f03yAbQW7muzqRVdDJbvww
   ```
   Descarga el tarball, descomprime, lee el README. Los mockups, tokens de diseño (colores, tipografías, espaciado) y especificaciones de componentes vienen desde ahí. Cuando implementes el Módulo 1, los tokens CSS del tema deben venir de ese design file — reemplazan el placeholder que está en la sección 10 del CLAUDE.md.

3. **Confirma antes de escribir código** respondiéndome estos puntos:
   - Qué stack usamos (tema custom, no child theme; sin builders; sin React)
   - Cuál es el alcance del MVP
   - Cómo se relacionan onplay.cl y onplaygames.cl
   - Qué rol tiene el onplay-manager y por qué su contrato no se cambia desde este proyecto
   - Cuáles son los 10 módulos del roadmap
   - Qué viene en el design file de Claude Design (resumen de componentes + tokens principales)

### Paso 1 — Contexto del entorno real

El sitio ya está en producción con WordPress 6.9.4 y WooCommerce 10.7.0 en Hostinger. **Ya hay 410 productos cargados** por el onplay-manager (productos simples con categorías jerárquicas `Magic: The Gathering > {Set}`). El tema activo actual es `blocksy-child` — el tema custom `onplay` que vas a crear se instalará en paralelo y se activará **solo cuando esté listo y probado**.

**IMPORTANTE — cosas existentes que NO debes tocar:**
- El tema inactivo `onplay-child-theme v2.0.0` es un intento descartado. **No lo uses ni lo referencies.** Lo dejamos morir ahí.
- Las categorías bajo `parent=0` (ej. `Aether Revolt` term_id 464 con count 0) son **categorías de cartas promocionales**, NO son huérfanas a limpiar. Si haces alguna query de limpieza, excluirlas.
- Los plugins Mercado Pago y Transbank Webpay **ya están instalados y configurados** — no los instales ni los configures tú, solo intégralos en los templates de checkout cuando llegue el Módulo 8.

### Paso 2 — Entregables del Módulo 1

Tema custom `onplay` (NO child theme) con:

- [ ] Estructura de directorios según sección 4 del CLAUDE.md
- [ ] `style.css` con header de WP válido (`Theme Name: Onplay`, autor, versión `0.1.0`, text domain `onplay`)
- [ ] `functions.php` mínimo que incluye archivos de `/inc/` en orden
- [ ] `inc/setup.php` con `add_theme_support` para: `title-tag`, `post-thumbnails`, `woocommerce`, `custom-logo`, `html5`, `responsive-embeds`. Registro de menús `primary` y `footer`.
- [ ] `inc/enqueue.php` cargando `assets/dist/style.css` y `assets/dist/main.js` con versionado por `filemtime`
- [ ] `assets/src/scss/main.scss` con imports a `_tokens.scss`, `_reset.scss`, `_typography.scss`, `_layout.scss` y los componentes básicos
- [ ] `assets/src/scss/_tokens.scss` con las variables CSS **extraídas del design file de Claude Design**. Si el design file no está accesible o no trae tokens explícitos, usa los del placeholder de la sección 10 del CLAUDE.md y anótalo como TODO.
- [ ] `assets/src/js/main.js` como entry point vacío con estructura namespace según sección 9 del CLAUDE.md
- [ ] `package.json` con dependencias mínimas (`sass`), scripts `build` y `watch`
- [ ] `header.php` con: logo (usar JPG2 — bulldog con diamante rojo), buscador placeholder (input no funcional aún), navegación principal desde menú `primary`, placeholder de cuenta, placeholder de carrito con contador "0"
- [ ] `footer.php` con: 4 columnas (Juegos, Ayuda, Empresa, Contacto), copyright con año dinámico, placeholders de redes sociales
- [ ] `index.php` como fallback mínimo
- [ ] `front-page.php` con solo "Home coming soon" (se llena en Módulo 9)
- [ ] `.gitignore` apropiado para WordPress + Node (`node_modules`, `assets/dist/*.map`, etc.)
- [ ] `README.md` breve con: cómo instalar el tema, cómo correr el build, link al CLAUDE.md

### Paso 3 — Criterios de aceptación

1. El tema es activable en un WordPress local de desarrollo sin errores (warnings PHP, notices, etc.) — **NO activarlo en producción hasta que el Módulo 10 termine**.
2. Al compilar (`npm run build`), `assets/dist/style.css` existe y tiene las variables CSS del token file.
3. Al inspeccionar cualquier página del sitio con DevTools → `:root` muestra las variables (`--color-primary`, `--font-display`, etc.).
4. Header y footer se renderizan con la estructura correcta en cualquier página.
5. No hay console.error en la consola del navegador.
6. El header usa el logo nuevo de onplay (JPG2 — bulldog con diamante rojo), no el morado viejo (JPG3).
7. Los colores visibles reflejan la paleta del design file (o del placeholder si no se pudo fetchear), no morados.

### Paso 4 — Cómo trabajamos

- **Plan primero, código después.** Antes de crear archivos, muéstrame:
  1. El plan de qué vas a crear y en qué orden.
  2. Lo que rescataste del design file de Claude Design (componentes, tokens, patrones a aplicar).
  3. Dudas si las hay respecto al CLAUDE.md.
- **Commits pequeños y descriptivos**, agrupados por sub-tarea.
- **Nada fuera de scope del Módulo 1.** Si te dan ganas de implementar el buscador funcional, detente — eso es el Módulo 5.
- **Pregunta si hay ambigüedad.** No inventes.

Empieza confirmando el Paso 0 (puntos 1, 2, 3) y mostrándome tu plan. No toques archivos hasta que yo apruebe el plan.

## ---FIN PROMPT INICIAL MÓDULO 1---

---

## Plantilla para módulos siguientes

Cuando termines el Módulo 1, usa esta plantilla para arrancar el Módulo 2 (y adaptarla para los siguientes):

```
Voy a arrancar el Módulo {N} — {nombre}.

1. Re-lee CLAUDE.md (puede haber cambiado; revisa la sección 11 "Decisiones técnicas" por si hay algo nuevo).
2. Confirma que el Módulo {N-1} está terminado: lista los criterios de aceptación de ese módulo y di cuáles están hechos y cuáles no.
3. Muéstrame el plan del Módulo {N} con los entregables de la sección 6 del CLAUDE.md, tu orden de ejecución, y dudas si las hay.
4. Espera mi aprobación antes de tocar código.
```

### Notas especiales por módulo

- **Módulo 2 (taxonomías + bridge meta→taxonomía):** antes de codear, Claude Code debe **leer los meta reales de un producto del sitio en producción** para verificar los nombres exactos (`_scryfall_id`, `_set_code`, etc.) que el manager escribe. Puede hacerlo pidiéndome el output de `wp post meta list <ID>` de un producto representativo, o conectándose vía WP-CLI si tiene acceso SSH configurado.

- **Módulo 4 (tabla de variantes agrupada):** es el feature más complejo y el más diferenciador del sitio. Antes de codear, revisar la sección 5.3 del CLAUDE.md en profundidad y el mockup correspondiente del design file. Si el mockup no detalla bien la tabla, pedirme que itere con Claude Design antes de empezar.

- **Módulo 8 (checkout):** los plugins Mercado Pago y Transbank Webpay ya están instalados. NO reinstalarlos ni reconfigurarlos. Solo integrar sus pasarelas en el template custom del checkout.

---

## Tips operativos

- **Desarrolla local, no en producción.** Configura un WordPress local (LocalWP, DDEV, Docker, o Hostinger tiene ambiente de staging) con una copia de la DB de onplay.cl para probar con datos reales. Deploya a Hostinger solo cuando el módulo está aprobado.
- **Credenciales nunca en el repo.** Nada de `.env` committeado, nada de `wp-config.php` en el repo.
- **Antes del Módulo 5 y 6 (búsqueda y filtros):** tener Query Monitor instalado en el ambiente de dev. Son los módulos que más tensionan la DB y Hostinger va a doler si las queries no están optimizadas.
- **Respaldo antes de activar el tema en producción.** UpdraftPlus o el backup nativo de Hostinger, full (archivos + DB), inmediatamente antes del switch en el Módulo 10.
- **El onplay-manager sigue funcionando durante todo el desarrollo.** Si el tema rompe algo que el manager asume (meta, taxonomías, estructura), el problema es del tema, no del manager. Verificar compatibilidad antes de cada merge a `main`.
