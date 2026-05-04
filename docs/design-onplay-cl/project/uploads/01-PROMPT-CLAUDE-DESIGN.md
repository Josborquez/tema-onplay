# Prompt para Claude Design (claude.ai/design)

> **Cómo usar este documento:** Copia y pega el bloque de abajo (desde `---INICIO PROMPT---` hasta `---FIN PROMPT---`) en una conversación nueva en claude.ai/design. Si quieres iterar, pide cambios específicos (ej. "hazlo más oscuro", "cambia el hero por algo con cartas volando"). No pegues todo el documento, solo la sección marcada.

---

## Contexto del proyecto (para que entiendas antes de copiar el prompt)

Onplay.cl es el sitio hermano de onplaygames.cl, la tienda física de TCGs en Santiago Centro (Merced 832, Galería Casa Colorada). Mientras onplaygames.cl vende productos sellados, accesorios y maneja torneos, **onplay.cl será el canal especializado en venta de singles** — cartas individuales de Magic: The Gathering al inicio, con One Piece, Pokémon y Riftbound a futuro.

Las referencias de la industria son TCGPlayer, Card Kingdom, Hareruya y Star City Games. Todas comparten una estética densa, funcional, casi de "base de datos con carrito" — priorizan la búsqueda y el filtrado sobre el storytelling. Onplay.cl tiene la oportunidad de diferenciarse con una identidad visual más cuidada sin sacrificar la densidad informativa que los compradores serios esperan.

---

## ---INICIO PROMPT---

Diseña los mockups de una tienda online chilena especializada en **venta de cartas individuales de Magic: The Gathering** (y futuros TCGs como One Piece, Pokémon, Riftbound). El sitio se llama **Onplay.cl**.

### Identidad visual

La marca Onplay usa como mascota un **French Bulldog brindle (gris con máscara negra)** — el logo ya existe y debe ser el ancla visual. La paleta está inspirada en ese logo:

- **Rojo carmesí profundo** `#D62828` — color dominante, para acentos, CTAs, badges de precio, highlights
- **Negro carbón** `#0A0A0A` — fondos principales del sitio (dark mode por defecto, como TCGPlayer)
- **Gris carbón** `#1A1A1A` — fondos de cards, paneles, modales
- **Gris medio** `#6B6B6B` — texto secundario, bordes sutiles
- **Blanco hueso** `#F5F5F5` — texto principal sobre fondos oscuros
- **Dorado cálido** `#E8B04B` — acento secundario raro, reservado para badges de "foil", rareza mítica, destacados premium
- **Verde** `#3FB950` — disponibilidad/stock
- **Ámbar** `#F59E0B` — stock bajo / últimas unidades

**Importante:** NO uses morado. La versión antigua del logo era morada y estamos alejándonos de esa paleta intencionalmente. El rojo/negro es la nueva dirección.

### Tono estético

Busca un balance entre **editorial oscuro moderno** (tipo revista de videojuegos premium) y **densidad funcional** (tipo TCGPlayer). Ni totalmente minimalista ni caótico maximalista — precisión y jerarquía clara con momentos de calidez visual. Piensa en: catálogo de naipes coleccionables en un cuarto oscuro iluminado por lámpara, no en supermercado.

Evita absolutamente:
- Gradientes morados, azules o de colores fríos
- Look "SaaS genérico" (fondos blancos, Inter, tarjetas redondeadas sin personalidad)
- Ilustraciones vectoriales planas y amigables tipo startup
- Tipografías de sistema (Arial, Helvetica, Roboto, Inter)

Busca:
- Tipografía display con carácter para titulares (considera algo tipo **Bebas Neue**, **Oswald**, **Anton**, o un serif condensado tipo **Playfair Display Bold** para contrastar)
- Tipografía de cuerpo legible y técnica (considera **IBM Plex Sans**, **JetBrains Mono** para datos, **Inter Tight** solo si es imprescindible)
- Uso de fondos casi negros con texturas sutiles (grano, ruido ligero) en lugar de negros planos
- Líneas finas de 1px en gris medio como divisores, no sombras blandas
- Rojo usado con intención — no como decoración, sino señalando precios, stock, acciones
- Cards de carta con proporción real de carta Magic (63mm × 88mm ratio ≈ 5:7)

### Pantallas a mockear

Diseña estas pantallas, en este orden de prioridad:

**1. Home / Landing**
- Header sticky con: logo Onplay (bulldog + wordmark), buscador prominente al centro (ocupando ~50% del header), nav secundaria (Magic, One Piece, Pokémon, Riftbound — los últimos 3 con badge "Próximamente"), icono de cuenta, icono de carrito con contador
- Hero: no un banner genérico, sino una sección que comunique "busca y encuentra" — considera un buscador gigante tipo Algolia con autocomplete visible mostrando ejemplos de cartas, o una grilla de cartas destacadas con precio visible
- Sección "Singles recién ingresados" en carrusel horizontal con cards de carta
- Sección "Por set" con los últimos 4-6 sets mostrando una carta icónica de cada uno
- Sección de confianza: retiro en Merced 832, despachos Chile, pagos Webpay/Mercado Pago
- Footer denso con columnas: Juegos, Ayuda, Empresa, Contacto, redes sociales

**2. Listado / Búsqueda de cartas**
- Sidebar izquierdo con filtros densos estilo TCGPlayer/Card Kingdom: Set (multi-select con búsqueda interna), Color (WUBRG con iconos de mana reales), Tipo, Rareza, Condición (NM/LP/SP/MP/HP/DMG), Foil sí/no, Idioma (EN/ES/JP), Rango de precio (slider + inputs), Solo en stock (toggle)
- Área principal con cards en grid responsivo (4-6 columnas desktop, 2 mobile)
- Cada card muestra: imagen de la carta (proporción 5:7, ocupa ~70% del card), nombre, set (con ícono del set si es posible), condición, idioma, badge foil si aplica, precio en CLP prominente, stock, botón "Agregar"
- Barra superior con: total de resultados, sort (precio asc/desc, nombre, recién ingresado), vista grid/lista
- Paginación inferior

**3. Ficha de producto (carta individual)**
- Imagen grande a la izquierda con zoom al hover (algo tipo Steam / Amazon)
- Panel derecho con nombre de la carta, tipo, texto, mana cost con iconos reales de mana, set, número de colección
- **Selector de variantes** — esta es la pantalla más crítica de diseño. El comprador debe poder elegir entre distintas versiones de la misma carta que tienes en stock: tabla compacta con filas por combinación condición/idioma/foil, cada fila muestra precio, stock, y botón agregar. Referencia directa: cómo lo hace Card Kingdom o Star City.
- Abajo: "Otros prints de esta carta" (mismo nombre, distintos sets), "Cartas similares"

**4. Carrito y Checkout**
- Carrito lateral (drawer desde la derecha) con items compactos, subtotal, CTA "Ir al checkout"
- Checkout de un paso (no multi-step) con secciones verticales: Datos → Despacho → Pago → Resumen sticky a la derecha
- Opciones de despacho: "Retiro en tienda Merced 832 - Gratis" y "Envío flat rate Chilexpress - $XXX"
- Pagos: Webpay Plus (Transbank) y Mercado Pago, con sus logos oficiales

**5. Card de producto en mobile**
- Versión optimizada del card para 2 columnas en mobile, sin perder precio y botón de agregar

### Entregables esperados

Por cada pantalla, genera un mockup de alta fidelidad (HTML+CSS en artifact, no imagen). Incluye:
- Estados hover visibles en al menos los CTAs principales
- Dark mode como default (el sitio NO tendrá light mode)
- Mobile-first o al menos indicación clara de cómo responde
- Uso consistente del sistema de color definido arriba
- Datos de ejemplo realistas: cartas reales de Magic (ej. Lightning Bolt, Counterspell, Black Lotus, Sol Ring, Thoughtseize), sets reales (Modern Horizons 3, Murders at Karlov Manor, Premodern-legal como Urza's Saga), precios en CLP coherentes ($500 - $450.000)

Al final, entrega también un **mini design system** con: paleta hex completa con nombres semánticos, escala tipográfica (h1-h6 + body), sistema de espaciado (4/8/16/24/32/48/64), componentes reutilizables (Button primario/secundario/ghost, Input, Badge, Card, Tag).

## ---FIN PROMPT---

---

## Notas para iteración

Después de que Claude Design entregue la primera pasada, cosas que probablemente querrás refinar:

1. **El hero** casi siempre sale genérico en la primera iteración. Pídele 3 alternativas distintas si no te convence.
2. **El selector de variantes** en la ficha de producto es LA pantalla más delicada. Si no sale bien, pásale screenshots de Card Kingdom o Star City como referencia concreta y pídele que se inspire en eso.
3. Pide que te muestre el mockup con **al menos 24 cartas reales diferentes** en el listado, no 4 repetidas. Los mockups con pocos datos engañan.
4. Cuando te guste la dirección, pide "ahora genera la **versión mobile** de estas mismas pantallas" — no lo hace bien si se lo pides todo junto la primera vez.
5. Una vez aprobados, pídele que exporte las variables CSS y la escala tipográfica en un solo bloque — eso se reutiliza textual en el CLAUDE.md de implementación.
