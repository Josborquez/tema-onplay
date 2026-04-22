# Prompt 05 — Términos y Condiciones de Servicio (v2.1)

> **v2.1 — Ajustes (2026-04-21):** dirección diferenciada (tributaria Local 53 / física Local 54 Galería Casa Colorada), email corporativo `contacto@onplay.cl` en lugar de gmail, §11 precisada como tienda oficial WPN/TPC/Bandai.
> **v2 — Ajustes previos:** datos legales reales integrados. URL del design file aclarada. "18 años" relajado a 18+/autorización tutor. Nota sobre implementación en el tema al final.

> **Cómo usar:** pega el bloque marcado abajo en una conversación nueva en claude.ai/design sobre el design file existente de Onplay. **URL para Claude Design (web UI):** `https://claude.ai/design/p/d3fa7929-c6f9-4829-8aee-f81703184463?file=Onplay.html&via=share`.

> **Aclaración sobre URLs del design file:** existen dos URLs del mismo proyecto de diseño:
> - `https://claude.ai/design/p/d3fa7929-...` → UI web de Claude Design para editar conversacionalmente. Se usa **en este prompt** y cuando trabajas con la herramienta visual.
> - `https://api.anthropic.com/v1/design/h/f03yAbQW7muzqRVdDJbvww` → Endpoint técnico que descarga el tarball del proyecto para que Claude Code lo implemente. Se usa **en el CLAUDE.md** del tema y en los prompts de Claude Code.
> Ambas URLs apuntan al mismo proyecto de diseño. No son alternativas, son accesos distintos a lo mismo.

---

## Contexto legal antes del prompt

### Base legal chilena aplicable

- **Ley 19.496** (Protección de los Derechos de los Consumidores)
- **Ley 19.628** (Protección de la Vida Privada — datos personales)
- **Ley 19.799** (Documentos electrónicos y firma electrónica)
- **Ley 20.575** (Protección de la vida privada en entornos comerciales)
- **Código Civil chileno** (obligaciones y contratos)

### Datos reales del proveedor (ya integrados en el documento)

- Razón social: **Comercializadora y Distribuidora BM Limitada**
- RUT: **77.862.085-5**
- Giro: Comercializadora de juegos de entretención
- **Domicilio tributario** (§1 identificación del proveedor): Merced 832, Local 53, Santiago Centro, Región Metropolitana, Chile
- **Dirección física de atención** (§8 retiro en tienda, §17 contacto físico): Merced 832, Local 54, Galería Casa Colorada, Santiago Centro, Región Metropolitana, Chile
- Email oficial: **contacto@onplay.cl**
- WhatsApp: **+56 9 6682 6121**
- Sitio: https://onplay.cl
- **Condición comercial:** tienda oficial de Wizards Play Network (WPN), The Pokémon Company y Bandai.

### Placeholders que quedan (para reemplazar antes de publicar)

- `[FLAT_RATE]` — monto fijo Chilexpress (pendiente que lo confirmes)
- `[FECHA_PUBLICACION]` — fecha exacta en que publicas (ej. "19 de abril de 2026")

---

## ---INICIO PROMPT---

Necesito crear la página **"Términos y Condiciones de Servicio"** para el sitio Onplay.cl (tienda chilena online de cartas individuales de Magic: The Gathering y otros TCGs). La página debe mantener la misma estructura visual, tokens de color, tipografía y componentes del design file ya aprobado de Onplay (el archivo `Onplay.html`).

### Estructura de la página

- Ruta: `/legal/terminos-y-condiciones/`
- Header y footer del sitio (los mismos que el resto del tema)
- Hero simple de página legal: título grande + subtítulo "Última actualización: [FECHA_PUBLICACION]" + breadcrumbs (Inicio / Legal / Términos y Condiciones)
- Sidebar de navegación interna (sticky en desktop con scroll-spy que resalta la sección activa, acordeón/dropdown "Ir a sección ▾" en mobile) con las 17 secciones del documento
- Cuerpo del documento con las secciones descritas abajo, jerarquía tipográfica clara (H2 para secciones, H3 para subsecciones, párrafos de ancho máximo ~680px para legibilidad)
- Estilo editorial legal serio: fondos oscuros consistentes con el sitio, tipografía de cuerpo legible, sin colores decorativos dentro de párrafos. El rojo Onplay (`--color-primary`) aparece solo en el título de la página, bordes de sección y enlaces
- Al final, bloque de "Documentos relacionados" con tarjetas-enlace a las otras páginas legales

### Contenido completo (redactado, listo para publicar)

Insertar el siguiente contenido en el cuerpo de la página, respetando jerarquía de encabezados y listas.

---

# Términos y Condiciones de Servicio

**Última actualización:** [FECHA_PUBLICACION]

Bienvenido a Onplay.cl. Estos Términos y Condiciones de Servicio (en adelante, "los Términos") regulan el uso del sitio web **https://onplay.cl** y la compra de productos a través del mismo. Al navegar, registrarte o realizar una compra en nuestro sitio, aceptas en su totalidad los Términos descritos en este documento.

Si no estás de acuerdo con alguna parte de estos Términos, te solicitamos abstenerte de utilizar el sitio.

## 1. Identificación del proveedor

El sitio Onplay.cl es operado por:

- **Razón social:** Comercializadora y Distribuidora BM Limitada
- **RUT:** 77.862.085-5
- **Giro comercial:** Comercializadora de juegos de entretención
- **Domicilio comercial (tributario):** Merced 832, Local 53, Santiago Centro, Región Metropolitana, Chile
- **Dirección física de atención al público:** Merced 832, Local 54, Galería Casa Colorada, Santiago Centro, Región Metropolitana, Chile
- **Correo de contacto:** contacto@onplay.cl
- **WhatsApp:** +56 9 6682 6121

En adelante nos referiremos a esta sociedad como "Onplay", "la Tienda" o "nosotros".

## 2. Aceptación de los Términos

El uso del sitio Onplay.cl constituye la aceptación plena de estos Términos. Onplay se reserva el derecho de modificar estos Términos en cualquier momento, publicando la versión actualizada en esta misma página. La fecha de "Última actualización" indica cuándo se hicieron los cambios más recientes. Te recomendamos revisar este documento periódicamente.

Los cambios entran en vigor desde su publicación y no afectan pedidos ya confirmados con anterioridad.

## 3. Productos ofrecidos

Onplay.cl comercializa principalmente **cartas individuales (singles)** de juegos de cartas coleccionables:

- Magic: The Gathering
- One Piece TCG (próximamente)
- Pokémon TCG (próximamente)
- Riftbound (próximamente)

Cada producto en nuestro catálogo incluye información detallada sobre su condición, idioma, edición, número de colección y si corresponde a una versión **foil** o **regular**. Las imágenes son referenciales: la carta entregada corresponde a la descripción escrita del producto.

Las condiciones de las cartas (Near Mint, Lightly Played, Slightly Played, Moderately Played, Heavily Played y Damaged) se describen en detalle en nuestra página [Condiciones de carta](/ayuda/condiciones-de-carta/).

## 4. Registro y cuenta de usuario

Para realizar compras, puedes registrarte creando una cuenta con tus datos personales o comprar como invitado. Al registrarte declaras que:

- Eres mayor de edad (**18 años o más**) o, si eres menor de edad, cuentas con la **autorización expresa de tu padre, madre o tutor legal** para realizar la compra.
- Los datos entregados son **verídicos, exactos y actualizados**.
- Eres el único responsable de mantener la confidencialidad de tu contraseña.
- Notificarás a Onplay de inmediato cualquier uso no autorizado de tu cuenta.

Onplay se reserva el derecho de suspender o cerrar cuentas que incumplan estos Términos, que presenten actividad sospechosa o que realicen compras con fines fraudulentos.

## 5. Precios, stock y disponibilidad

Todos los precios se expresan en **pesos chilenos (CLP)** e **incluyen IVA**. Los precios están sujetos a cambios sin previo aviso, pero una vez confirmado y pagado un pedido, su precio queda fijo.

El stock exhibido en el sitio se actualiza constantemente, pero puede haber diferencias breves entre lo mostrado y la disponibilidad real producto del flujo de ventas. En caso de que un producto pagado no se encuentre disponible al momento de preparar el pedido, Onplay te contactará para ofrecerte:

1. Reemplazar el producto por uno equivalente en stock.
2. Aplicar el monto como crédito para una compra futura.
3. Reembolsar el monto pagado por ese producto mediante el mismo medio de pago usado originalmente.

## 6. Proceso de compra

El proceso de compra en Onplay.cl consta de los siguientes pasos:

1. Seleccionas los productos y los agregas al carrito.
2. Revisas el resumen del carrito y procedes al checkout.
3. Ingresas los datos de contacto, despacho y método de pago.
4. Confirmas la orden y realizas el pago mediante Webpay Plus (Transbank) o Mercado Pago.
5. Recibes un correo de confirmación con el detalle de tu pedido y un número de orden.

La compra se considera **perfeccionada** una vez que el pago es aprobado por la pasarela y se envía el correo de confirmación. Antes de ese momento, Onplay puede rechazar la orden si detecta errores de precio, problemas de stock o cualquier anomalía en el proceso.

## 7. Medios de pago

Aceptamos los siguientes medios de pago:

- **Webpay Plus (Transbank):** tarjetas de crédito, débito y prepago de emisores chilenos.
- **Mercado Pago:** tarjetas de crédito, débito y saldo Mercado Pago.

El procesamiento de los pagos es realizado íntegramente por Transbank S.A. y Mercado Pago Chile. Onplay **no almacena datos de tarjetas de crédito ni débito** en sus servidores.

## 8. Despachos y retiros

Onplay ofrece dos modalidades de entrega:

- **Retiro en tienda:** Merced 832, Local 54, Galería Casa Colorada, Santiago Centro. Sin costo.
- **Envío Chilexpress a todo Chile:** tarifa fija de [FLAT_RATE] dentro del territorio nacional continental.

Los plazos, horarios, zonas de cobertura y detalles operativos están descritos en nuestra página [Políticas de Envío](/legal/politicas-de-envio/).

El riesgo de pérdida o daño del producto se transfiere al comprador al momento de la entrega al servicio de despacho (en envíos) o al momento del retiro en tienda (en retiros presenciales).

## 9. Devoluciones y garantía

Dada la naturaleza de los productos comercializados (cartas coleccionables abiertas, cuyo estado puede verse afectado por su manipulación), Onplay **no acepta devoluciones** de cartas individuales (singles) una vez entregadas al comprador, salvo los casos establecidos en el artículo 20 de la Ley 19.496 sobre Protección de los Derechos de los Consumidores:

- Producto recibido distinto al ordenado.
- Producto con defectos o daños no informados al momento de la compra.
- Producto no entregado en el plazo acordado.

En estos casos, el cliente tiene derecho a la reparación, reposición o reembolso, conforme a lo establecido en la Ley 19.496.

El procedimiento detallado se describe en nuestras [Políticas de Devolución y Reembolso](/legal/devoluciones-y-reembolsos/).

## 10. Derecho de retracto

El **derecho de retracto** contemplado en el artículo 3 bis de la Ley 19.496 **no aplica** a la compra de cartas individuales (singles) de Onplay.cl. Esto se fundamenta en que:

1. Los singles son productos coleccionables cuyo valor de mercado fluctúa y puede verse afectado por su manipulación.
2. Cada carta se despacha individualmente protegida y verificada en su condición antes del envío.

Sin perjuicio de lo anterior, Onplay garantiza siempre los derechos establecidos por la Ley 19.496 ante defectos, errores de despacho o incumplimiento.

## 11. Propiedad intelectual

Los nombres, logos, artes de cartas, mecánicas de juego y demás elementos de las marcas **Magic: The Gathering**, **Pokémon**, **One Piece** y otros juegos presentes en el catálogo son propiedad de sus respectivos titulares (Wizards of the Coast LLC, The Pokémon Company, Bandai, Riot Games y otros).

**Comercializadora y Distribuidora BM Limitada** opera como **tienda oficial de Wizards Play Network (WPN)**, **The Pokémon Company** y **Bandai**, comercializando productos originales bajo los programas oficiales de estos titulares. Cualquier uso de marcas, artes o elementos protegidos en el catálogo se realiza en el ejercicio de esta condición comercial.

El diseño del sitio Onplay.cl, su marca, logos, textos e imágenes propias son propiedad de Comercializadora y Distribuidora BM Limitada y están protegidos por la legislación chilena de propiedad intelectual. Queda prohibida su reproducción total o parcial sin autorización escrita.

## 12. Limitación de responsabilidad

Onplay hace esfuerzos razonables para que el sitio esté disponible, actualizado y libre de errores, pero no garantiza:

- Disponibilidad ininterrumpida del sitio.
- Ausencia total de errores tipográficos en descripciones de productos o precios.
- Que los productos cumplan expectativas subjetivas del comprador más allá de la descripción entregada.

En ningún caso Onplay será responsable por daños indirectos, lucro cesante, pérdida de oportunidad o cualquier daño derivado del uso o imposibilidad de uso del sitio, más allá del valor del producto comprado.

Nada de lo anterior limita los derechos garantizados por la Ley 19.496 ni otras leyes chilenas aplicables.

## 13. Protección de datos personales

El tratamiento de los datos personales de los usuarios se rige por la **Ley 19.628 sobre Protección de la Vida Privada** y se describe en detalle en nuestras [Políticas de Privacidad](/legal/politicas-de-privacidad/).

Al usar el sitio Onplay.cl autorizas el uso de tus datos personales para las finalidades descritas en esa política.

## 14. Conducta del usuario

Al interactuar con Onplay a través del sitio, de nuestras redes sociales o en la tienda física, aceptas cumplir con nuestro [Código de Conducta](/legal/codigo-de-conducta/). El incumplimiento puede implicar la suspensión de tu cuenta, la cancelación de pedidos pendientes o la prohibición de ingreso a la tienda física.

## 15. Resolución de disputas

Ante cualquier discrepancia o reclamo, te invitamos a contactarnos primero por los canales oficiales: **contacto@onplay.cl** o WhatsApp **+56 9 6682 6121**. Trabajamos siempre por encontrar una solución amistosa y ágil.

Si no se alcanzara un acuerdo, el consumidor podrá recurrir a los mecanismos contemplados por la Ley 19.496, incluyendo la mediación del **Servicio Nacional del Consumidor (SERNAC)** a través de https://www.sernac.cl.

## 16. Ley aplicable y jurisdicción

Estos Términos se rigen por las **leyes de la República de Chile**. Cualquier controversia derivada de la relación comercial con Onplay se someterá a los tribunales ordinarios de justicia de **Santiago, Chile**, sin perjuicio del derecho de los consumidores a acudir a los tribunales correspondientes a su domicilio según la Ley 19.496.

## 17. Contacto

Cualquier consulta, solicitud o aclaración respecto a estos Términos puede dirigirse a:

- **Email:** contacto@onplay.cl
- **WhatsApp:** +56 9 6682 6121
- **Dirección física:** Merced 832, Local 54, Galería Casa Colorada, Santiago Centro, Región Metropolitana, Chile

---

### Bloque final de documentos relacionados

Incluye un divider horizontal y un bloque de cierre con tarjetas-enlace (grid de 3-4 columnas desktop, stack en mobile) a las otras páginas legales:

- **Políticas de Privacidad** → `/legal/politicas-de-privacidad/`
- **Políticas de Envío** → `/legal/politicas-de-envio/`
- **Políticas de Devolución y Reembolso** → `/legal/devoluciones-y-reembolsos/`
- **Código de Conducta** → `/legal/codigo-de-conducta/`
- **Términos y Condiciones de Preventa** → `/legal/terminos-de-preventa/`

### Consideraciones finales del diseño

- El documento es largo. El sidebar de navegación interna es crítico — usarlo con scroll-spy (el enlace de la sección activa se resalta al hacer scroll).
- En mobile, colapsar el sidebar a un `<details>` o dropdown "Ir a sección ▾" arriba del contenido.
- Sin fondos de color dentro de los párrafos. Lectura como texto legal serio, no como blog.
- Los nombres de otras páginas linkeadas (Condiciones de carta, Políticas de Envío, etc.) son enlaces clickeables al implementar, aunque algunas URLs aún no existan — quedarán como 404 temporal y se resolverán al publicar el resto de páginas legales en el mismo deploy.
- Meta tags: title `Términos y Condiciones de Servicio | Onplay.cl`, description corta.
- Canonical: `https://onplay.cl/legal/terminos-y-condiciones/`.
- Schema.org: con `WebPage` es suficiente para documentos legales.

## ---FIN PROMPT---

---

## Checklist de publicación

- [x] Razón social completada (Comercializadora y Distribuidora BM Limitada)
- [x] RUT completado (77.862.085-5)
- [x] Giro, direcciones (tributaria Local 53 / física Local 54), email (contacto@onplay.cl) y WhatsApp confirmados e integrados
- [x] Condición comercial actualizada (§11): tienda oficial WPN / The Pokémon Company / Bandai
- [ ] **Reemplazar `[FLAT_RATE]`** (1 aparición en sección 8)
- [ ] **Reemplazar `[FECHA_PUBLICACION]`** (1 aparición en cuerpo + 1 en hero de página)
- [ ] Validación por abogado o asesor legal — especialmente sección 10 (retracto)
- [ ] Enlaces a otras páginas legales apuntan a URLs correctas del sitio
- [ ] Breadcrumbs configurados correctamente
- [ ] Meta tags de SEO agregados
- [ ] Enlazado desde el footer del sitio en columna "Legal"

---

## Implementación en el tema WordPress (lo que no cubre el prompt de Claude Design)

Claude Design genera la maqueta HTML/CSS. Para integrarla al tema `onplay` de WordPress hay tres opciones. **Mi recomendación:** opción C.

### Opción A — Page de WordPress con editor
- Crear una Page en WP admin con slug `terminos-y-condiciones` bajo parent `legal`
- Pegar el contenido del documento usando el editor Gutenberg
- El tema usa `page.php` genérico o `page-legal.php` para envolver el contenido
- **Ventaja:** editable desde admin sin tocar código
- **Desventaja:** el sidebar TOC con scroll-spy requiere JS custom que detecta los `<h2>` del contenido. Funciona pero más delicado

### Opción B — Template fijo hardcoded en el tema
- Crear `page-legal-terminos.php` con el HTML directo
- El contenido vive en el tema, no en la DB
- **Ventaja:** control total del layout y TOC estático confiable
- **Desventaja:** editar el texto requiere tocar código

### Opción C (recomendada) — Page + template dedicado con TOC dinámico
- Crear Page `terminos-y-condiciones` en WP admin con contenido usando bloques Gutenberg
- Crear template `page-legal.php` que el tema asigna automáticamente a páginas bajo `/legal/*`
- Template incluye `<aside class="legal-toc">` con JS que escanea los `<h2>` del content y genera enlaces automáticamente
- Scroll-spy se implementa con IntersectionObserver (no jQuery)
- El contenido es editable desde admin, el layout lo controla el tema

**Estructura del template `page-legal.php` recomendada:**

```php
<?php
/**
 * Template: Página Legal
 * Usado para todas las páginas bajo /legal/*
 * Incluye sidebar TOC con scroll-spy dinámico
 */
get_header(); ?>

<main class="legal-page">
    <div class="legal-page__container">
        <aside class="legal-page__toc" aria-label="Navegación del documento">
            <nav id="legal-toc"></nav>
        </aside>

        <article class="legal-page__content">
            <header class="legal-page__header">
                <nav class="breadcrumbs">
                    <a href="/">Inicio</a> / <a href="/legal/">Legal</a> /
                    <span><?php the_title(); ?></span>
                </nav>
                <h1><?php the_title(); ?></h1>
                <p class="legal-page__updated">
                    Última actualización: <?php echo get_the_modified_date('j \d\e F \d\e Y'); ?>
                </p>
            </header>

            <div class="legal-page__body">
                <?php the_content(); ?>
            </div>

            <footer class="legal-page__related">
                <h2>Documentos relacionados</h2>
                <?php // Loop de otras páginas bajo /legal/ excluyendo la actual ?>
            </footer>
        </article>
    </div>
</main>

<?php get_footer(); ?>
```

**JS para TOC dinámico (`assets/src/js/legal-toc.js`):**

```javascript
// Genera el TOC automáticamente desde los H2 del contenido
// Implementa scroll-spy con IntersectionObserver
// Colapsa en mobile con <details>
```

**Asignación del template a la Page:**

```php
// En inc/woocommerce.php o inc/setup.php
add_filter('template_include', function($template) {
    if (is_page() && strpos($_SERVER['REQUEST_URI'], '/legal/') === 0) {
        $custom = locate_template('page-legal.php');
        if ($custom) return $custom;
    }
    return $template;
});
```

Este template es reutilizado por las 6 páginas legales — se construye **una vez** y todas las páginas lo usan.

---

## Siguiente paso

Cuando confirmes que el prompt 05 v2 está correcto, procedo con **Prompt 06 — Políticas de Privacidad** (Ley 19.628, la más delicada en términos de compliance de datos).

Si quieres, después del Prompt 06 (o al final de los 6 legales) puedo armar un **Prompt 04b — Template `page-legal.php` para el tema onplay** que Claude Code implementa una vez y sirve para las 6 páginas. Así la estructura queda unificada y el contenido es editable desde WP admin.

¿Avanzamos?
