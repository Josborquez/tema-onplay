# Onplay — tema WordPress

Tema custom para **onplay.cl**, tienda especializada en singles de Magic: The Gathering.
Dark UI carmesí/black con acento Bebas Neue + IBM Plex Sans. No es child theme de nada.

> ⚠️ **NO activar en producción hasta que el Módulo 10 termine.** El tema activo en onplay.cl sigue siendo `blocksy-child` hasta entonces.

## Instalación

1. Copiar la carpeta completa a `wp-content/themes/onplay/` de tu WordPress local (LocalWP / DDEV / Docker).
2. Activar en **Apariencia → Temas** solo en el ambiente de desarrollo.
3. En **Apariencia → Personalizar → Identidad del sitio**, subir un custom logo (opcional: el tema trae un fallback en `assets/img/logo.png`).
4. En **Apariencia → Menús**, crear menú "Juegos" y asignarlo a la ubicación `primary`. Si no hay menú, el header usa un fallback hardcoded con `Magic` + placeholders "Pronto" para los otros TCGs.

## Build del CSS/JS

Requiere Node ≥ 18 y npm.

```bash
cd wp-content/themes/onplay
npm install      # una vez
npm run build    # compila SCSS → assets/dist/style.css + copia main.js
npm run watch    # autocompila cada vez que guardas un .scss
```

**`assets/dist/` sí se committea** — Hostinger no corre Node, así que el bundle compilado tiene que vivir en el repo.

## Estructura

```
onplay/
├── style.css                  # Header de tema WP (shim, editar assets/src/scss/main.scss)
├── functions.php              # Bootstrap: require_once de /inc/
├── header.php, footer.php
├── front-page.php             # Placeholder Módulo 1, home real en Módulo 9
├── index.php                  # Fallback
├── inc/
│   ├── setup.php              # theme supports + register_nav_menus
│   └── enqueue.php            # Carga Google Fonts + dist/style.css + dist/main.js
├── assets/
│   ├── src/
│   │   ├── scss/              # Fuentes SCSS (tokens, componentes)
│   │   └── js/main.js         # Entry point con namespace window.onplay
│   ├── dist/                  # Output compilado
│   └── img/logo.png           # Logo oficial Onplay Games (bulldog + diamante)
├── package.json
└── README.md
```

Detalle completo de la arquitectura: ver `CLAUDE.md` en la raíz del repo (sección 4).

## Tokens de diseño

Definidos en `assets/src/scss/_tokens.scss`. Fuente: design file de Claude Design `qsNZptfkkYAPh-YFwd9hQA`. **No editar valores** sin coordinar con el design file.

Variables clave:
- `--carmesi #D62828` · `--black #0A0A0A` · `--carbon #141414` · `--bone #F5F5F5`
- Display: `Bebas Neue` · Body: `IBM Plex Sans` · Mono: `IBM Plex Mono`
- Radios casi cuadrados (2-4px), espaciado base 4px.

## Roadmap

Este tema se construye por módulos. Ver `CLAUDE.md` sección 6:

| Módulo | Qué | Estado |
|--------|-----|--------|
| 0 | Auditoría y limpieza previa | ⏸ pendiente (depende de acceso a producción) |
| 1 | **Fundación del tema** | ✅ en curso |
| 2 | Enriquecimiento Scryfall + taxonomías custom | — |
| 3 | Templates de producto | — |
| 4 | Tabla de variantes agrupada por `print_key` | — |
| 5 | Otras impresiones + búsqueda | — |
| 6 | Listado con filtros facetados | — |
| 7 | Carrito y drawer | — |
| 8 | Checkout custom (Webpay + Mercado Pago) | — |
| 9 | Home | — |
| 10 | Performance, SEO, seguridad, lanzamiento | — |

## Contrato con el onplay-manager

El pipeline oficial de ingesta es **onplay-manager** (`github.com/Josborquez/onplay-manager`). Este tema **no modifica** su contrato. Si hay tensión tema vs manager, el manager gana y se ajusta el tema. Detalle completo en `CLAUDE.md` sección 3.

## Licencia

GPL-2.0-or-later. Propiedad de Onplay Games SpA.
