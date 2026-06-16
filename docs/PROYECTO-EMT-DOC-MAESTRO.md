# DOCUMENTO MAESTRO DE PROYECTO

**Proyecto:** Sitio web Explora México Tours (EMT)
**Cliente:** Explora México Tours · Guadalajara, Jalisco
**Propuesta:** P-10688-EMT
**Versión del documento:** 1.2
**Fecha:** Junio 2026
**Stack base:** WordPress + Hello Elementor parent + `explora-mexico-child`
**Plazo:** 4 semanas
**Director técnico:** Fabian Valdez (Supratecnia)

---

## CÓMO USAR ESTE DOCUMENTO

Este documento es la **fuente única de verdad** para construir el sitio. Claude Code debe consultarlo antes de cada decisión técnica. Reglas:

1. **Si este documento dice algo, eso se hace.** Si entra en conflicto con tu memoria de proyectos anteriores, este gana.
2. **Si algo NO está en este documento**, primero pregunta al director técnico, no improvises.
3. **Las convenciones de naming, scoping y estructura son inviolables.** Cambiarlas rompe el sistema de tokens y la migración futura.
4. **No usar plugins fuera de la lista aprobada.** Cada plugin extra genera dependencia y deuda técnica.
5. **Toda decisión que se tome en el camino se documenta aquí** como "Adenda" al final.

---

## 1. CONTEXTO Y OBJETIVOS

### 1.1 Cliente

Explora México Tours es una agencia turística con base en Guadalajara, afiliada a AMAV Occidente (agencia GDL35), con distintivo Moderniza SECTUR y miembro de AMTAVE. Más de 20 años operando tours por Jalisco y el resto de México. Catálogo de aproximadamente 70 tours activos organizados en bloqueos de fecha y cupo (no disponibilidad dinámica).

### 1.2 Situación actual

Sitio comprometido en seguridad, sin actualizaciones desde mayo 2022, sobre WooCommerce 7.1.2 con vulnerabilidades activas. El nuevo sitio se construye desde cero en servidor limpio e independiente, **sin migrar archivos del entorno comprometido** (solo contenido verificado: textos e imágenes validadas).

### 1.3 Modelo de negocio

EMT vende dos tipos de productos:

- **Bloqueos de transporte ejecutivo:** producto fijo, fecha cerrada, cupo definido.
- **Bloqueos de excursiones:** tours con fechas y cupos específicos (Día de Muertos, Tequila Express, Cantaritos, Cata Privada, etc.). Cada variante es un producto distinto con su propio bloqueo.

**Las reservas se procesan en Peek Pro vía enlace directo**. El sitio no procesa pagos en esta fase del proyecto. Para grupos/MICE/personalizados se usa un formulario robusto de cotización.

### 1.4 Objetivos del proyecto

1. Recuperar la presencia digital con un sitio rápido, seguro y bilingüe
2. Aumentar conversión a través de UX consistente y CTAs claros
3. Profesionalizar la imagen del equipo con directorio de asesores
4. Posicionar el catálogo en Google (Schema TouristTrip)
5. Dejar al equipo de EMT una herramienta autoadministrable

### 1.5 Referente visual aprobado

[visitjalisco.mx](https://visitjalisco.mx). Tomamos de ahí:
- Mega-menú visual con imágenes por categoría
- Hero estacional rotativo (tipo "Mundial 2026")
- Taxonomía combinada destino + experiencia
- Cards limpias de experiencia
- Arquitectura de URLs limpias y SEO fuerte
- Blog editorial integrado

**No copiamos:**
- Ausencia de precios (EMT sí los muestra)
- Sistema de login de operadores
- Listados de hoteles/restaurantes (no aplica)

---

## 2. STACK TÉCNICO

### 2.1 Servidor

- **Hosting:** Hetzner Cloud (CX32 mínimo recomendado, 3vCPU + 8GB RAM)
- **Panel:** CloudPanel (gratis, optimizado para Hetzner)
- **PHP:** 8.2+ con OPcache habilitado, memory_limit 512MB
- **Base de datos:** MariaDB 10.6+ / MySQL 8+
- **Webserver:** Nginx (CloudPanel default)
- **SSL:** Let's Encrypt con renovación automática
- **CDN/Seguridad:** Cloudflare gratis al frente
- **Correos:** Google Workspace separado (NO en el servidor)

> **Nota v1.2 — Realidad de infraestructura (corrección):** en producción el servidor Hetzner **NO usa CloudPanel/Nginx** como se especifica arriba. Corre **Coolify** (PaaS self-hosted) con **Traefik** como proxy, y el sitio vive en **contenedores Docker** (`wordpress:latest` + `mariadb:11`). Implicaciones:
> - **Deploy:** Coolify permite **auto-deploy desde GitHub** (push → build → release). Falta definir el pipeline `dev`→staging / `main`→producción ↔ Coolify (pendiente, ver §18 v1.2).
> - **Persistencia:** uploads, `wp-config` y la BD viven en **volúmenes Docker** (p. ej. `…_wordpress-files`), no en filesystem tradicional (`/var/www` no existe). Lo que deba sobrevivir a un redeploy va en un volumen montado.
> - **Dependencias de entorno:** plugins como **ACF Pro** son dependencia del **entorno/imagen del contenedor**, no del repo (no se versionan en Git); deben instalarse/activarse dentro del contenedor. El theme registra CPTs/taxonomías/campos ACF por código, pero asume ACF Pro presente.
> - **Acceso SSH a producción:** solo por **llave** (no contraseña).

### 2.2 WordPress

- **WordPress:** 6.5+ (siempre la versión estable más reciente)
- **Tema padre:** Hello Elementor (last stable)
- **Tema hijo:** `explora-mexico-child` (este es el que construimos)
- **NO usar:** WooCommerce, page builders adicionales a Elementor, plugins de SEO pesados (Yoast, RankMath solo si imprescindible)

### 2.3 Plugins obligatorios

| Plugin | Uso | Notas |
|---|---|---|
| Elementor Pro | Constructor de plantillas | Licencia del cliente |
| ACF Pro | Campos personalizados | Licencia agencia o cliente |
| Code Snippets Pro | Lógica custom versionada | Evita editar functions.php directo |
| Wordfence o Solid Security Pro | Seguridad | Configurado según sec.md |
| WP Mail SMTP | Email transaccional vía Workspace | |

### 2.4 Lo que NO se usa (decisión arquitectónica)

- **WooCommerce:** NO. Catálogo se maneja con CPT custom + ACF.
- **WPML/Polylang:** NO. Multi-idioma nativo en código.
- **Plugins de mantenimiento como LightStart:** NO. Under construction ya está en el child theme.
- **Plugins de constructor adicionales (Divi, WPBakery):** NO.
- **Plugins de WhatsApp comerciales:** NO. Módulo WhatsApp es custom.
- **Plugins de testimonios/reviews:** NO. Custom usando Google Places API.

---

## 3. CONVENCIONES DE CÓDIGO

### 3.1 Prefijos obligatorios

Todo lo que se cree para EMT lleva prefijo `emt_` (PHP) o `emt-` (CSS/HTML):

```php
// Funciones PHP
emt_get_tour_data()
emt_render_tour_card()
emt_register_post_types()

// Opciones WordPress
'emt_whatsapp_number'
'emt_hero_seasonal_active'

// Meta keys
'_emt_tour_price_from'
'_emt_tour_peek_url'

// Hooks custom
do_action('emt_after_tour_card');
apply_filters('emt_tour_card_classes', $classes);
```

```css
/* Clases CSS */
.emt-card
.emt-card__title
.emt-card--featured

/* CSS Variables */
--emt-color-azul-profundo
--emt-spacing-md
--emt-radius-lg
```

### 3.2 Naming de CPTs y taxonomías

| Elemento | Slug | Singular | Plural |
|---|---|---|---|
| CPT Tour | `tour` | Tour | Tours |
| CPT Asesor | `asesor` | Asesor | Asesores |
| Taxonomía destino | `tour_destino` | Destino | Destinos |
| Taxonomía categoría | `tour_categoria` | Categoría | Categorías |
| Taxonomía experiencia | `tour_experiencia` | Experiencia | Experiencias |
| Taxonomía especialidad asesor | `asesor_especialidad` | Especialidad | Especialidades |
| Taxonomía idioma asesor | `asesor_idioma` | Idioma | Idiomas |

### 3.3 Naming de campos ACF

Convención: `field_emt_{cpt}_{nombre}` para el field key. Para meta key visible: snake_case sin prefijo emt (lo agrega WP automático con `_`).

```
Field name: precio_desde           → meta_key: precio_desde
Field name: peek_url               → meta_key: peek_url
Field name: incluye                → meta_key: incluye
```

### 3.4 BEM para CSS

```css
/* Bloque */          .emt-tour-card { }
/* Elemento */        .emt-tour-card__image { }
                      .emt-tour-card__title { }
                      .emt-tour-card__price { }
/* Modificador */     .emt-tour-card--featured { }
                      .emt-tour-card--horizontal { }
```

### 3.5 Estructura de archivos PHP

```php
<?php
/**
 * Nombre del archivo y propósito
 *
 * @package ExploraMexicoChild
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Constantes
// 2. Hooks (add_action / add_filter)
// 3. Funciones (siempre con prefijo emt_)
// 4. Clases (si aplica, prefijo EMT_)
```

### 3.6 Seguridad obligatoria

- Toda salida HTML debe pasar por `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` según corresponda
- Todo input por `sanitize_text_field()`, `sanitize_email()`, `absint()`, etc.
- Toda query a BD usa `$wpdb->prepare()`. Nunca concatenar.
- Todo endpoint REST tiene `permission_callback` definido
- Toda acción AJAX usa nonces con `wp_verify_nonce()`
- Nunca usar `eval()`, `exec()`, `system()`, `shell_exec()`

### 3.7 Performance

- **Queries:** usar `WP_Query` con `'no_found_rows' => true` cuando no necesites paginación; `'update_post_meta_cache' => false` si no usas meta
- **Imágenes:** todas con `loading="lazy"`, formatos modernos (WebP), responsive con `srcset`
- **CSS:** sin `!important` salvo excepciones documentadas; sin selectores con más de 3 niveles de profundidad
- **JS:** modular, defer por default, sin librerías pesadas (jQuery solo si lo requiere un plugin)
- **Transients:** cachear queries pesadas (mega-menú, contadores) con `set_transient()`

---

## 4. ESTRUCTURA DE CARPETAS

```
/wp-content/themes/explora-mexico-child/
├── style.css                          # Tokens CSS + encabezado del tema
├── functions.php                      # Bootstrap, requires
├── screenshot.png                     # Captura del tema
├── README.md                          # Instrucciones para developers
│
├── inc/                               # Lógica PHP separada por dominio
│   ├── setup.php                      # Setup del theme (supports, sizes)
│   ├── enqueues.php                   # Carga de CSS/JS
│   ├── cpts.php                       # Registro de CPTs
│   ├── taxonomies.php                 # Registro de taxonomías
│   ├── acf-fields.php                 # ACF en código (no via admin)
│   ├── menus.php                      # Menús + mega-menú
│   ├── i18n.php                       # Sistema bilingüe nativo
│   ├── tour-functions.php             # Helpers de CPT tour
│   ├── asesor-functions.php           # Helpers de CPT asesor
│   ├── hero-seasonal.php              # Hero rotativo estacional
│   ├── peek-integration.php           # Tracking de clicks a Peek
│   ├── google-reviews.php             # Integración Places API
│   ├── whatsapp-guided.php            # Módulo WhatsApp custom
│   ├── lead-capture.php               # Captura de cotizaciones
│   ├── seo-schema.php                 # Schema.org TouristTrip
│   ├── admin-panel.php                # Personalizaciones admin
│   ├── security.php                   # Hardening (2FA, headers, etc.)
│   └── under-construction.php         # Lógica del modo UC (heredada)
│
├── templates/                         # Plantillas de página
│   ├── template-under-construction.php
│   ├── template-cotizacion.php
│   ├── single-tour.php
│   ├── single-asesor.php
│   ├── archive-tour.php
│   ├── archive-asesor.php
│   ├── taxonomy-tour_destino.php
│   ├── taxonomy-tour_categoria.php
│   ├── taxonomy-tour_experiencia.php
│   └── front-page.php                 # Home custom
│
├── parts/                             # Partials reutilizables
│   ├── header.php
│   ├── footer.php
│   ├── mega-menu.php
│   ├── hero-seasonal.php
│   ├── tour-card.php
│   ├── tour-card-featured.php
│   ├── asesor-card.php
│   ├── filter-bar.php
│   ├── breadcrumbs.php
│   ├── lang-switcher.php
│   ├── whatsapp-float.php
│   └── credentials-bar.php
│
├── assets/
│   ├── css/
│   │   ├── base.css                   # Reset, tipografías, body
│   │   ├── tokens.css                 # Variables CSS (importado por style.css)
│   │   ├── components.css             # Botones, cards, badges
│   │   ├── header.css
│   │   ├── footer.css
│   │   ├── mega-menu.css
│   │   ├── home.css
│   │   ├── tour-archive.css
│   │   ├── tour-single.css
│   │   ├── asesor-archive.css
│   │   ├── asesor-single.css
│   │   ├── cotizacion.css
│   │   └── under-construction.css
│   │
│   ├── js/
│   │   ├── core.js                    # Bootstrap JS
│   │   ├── mega-menu.js
│   │   ├── hero-seasonal.js
│   │   ├── lang-switcher.js
│   │   ├── whatsapp-guided.js
│   │   ├── filter-bar.js
│   │   ├── lead-form.js
│   │   └── under-construction.js
│   │
│   ├── images/                        # Imágenes propias del theme
│   │   ├── logo.svg
│   │   ├── logo-white.svg
│   │   ├── icons/                     # SVGs de iconografía propia
│   │   └── placeholders/              # Placeholders para cuando falta foto
│   │
│   └── i18n/                          # Traducciones
│       ├── en.php                     # Diccionario inglés
│       └── es.php                     # Diccionario español (default)
│
└── languages/                         # Archivos .po/.mo de WP (no usado)
```

---

## 5. SISTEMA DE TOKENS Y DESIGN SYSTEM

### 5.1 Paleta de colores (CSS variables)

```css
:root {
  /* === PALETA PRINCIPAL === */
  --emt-azul-profundo: #003366;       /* Encabezados, nav, texto principal */
  --emt-azul-brillante: #0057B8;      /* Enlaces, botones secundarios */

  /* === COLORES DE ACENTO === */
  --emt-rosa: #E91E63;                /* CTAs primarios, ofertas */
  --emt-turquesa: #009FAE;            /* Iconos, categorías */
  --emt-naranja: #F28C00;             /* Destacados, badges */
  --emt-coral: #E63946;               /* Alertas, promociones */
  --emt-verde: #8BC34A;               /* Éxito, naturaleza */

  /* === NEUTROS === */
  --emt-blanco: #FFFFFF;
  --emt-gris-claro: #F4F6F8;          /* Fondos suaves */
  --emt-gris-medio: #B0B5BD;          /* Bordes, texto secundario */
  --emt-gris-oscuro: #1F2937;         /* Texto principal */

  /* === SEMÁNTICOS === */
  --emt-color-text: var(--emt-gris-oscuro);
  --emt-color-text-muted: var(--emt-gris-medio);
  --emt-color-link: var(--emt-azul-brillante);
  --emt-color-cta: var(--emt-rosa);
  --emt-color-cta-hover: #d11757;
  --emt-color-success: var(--emt-verde);
  --emt-color-warning: var(--emt-naranja);
  --emt-color-danger: var(--emt-coral);
  --emt-color-border: rgba(0, 51, 102, 0.08);
}
```

### 5.2 Tipografía

```css
:root {
  --emt-font-display: 'Poppins', system-ui, sans-serif;  /* Titulares */
  --emt-font-body: 'Inter', system-ui, sans-serif;       /* Cuerpo */

  /* Escala tipográfica */
  --emt-fs-xs: 0.75rem;    /* 12px - micro */
  --emt-fs-sm: 0.875rem;   /* 14px - small */
  --emt-fs-base: 1rem;     /* 16px - base */
  --emt-fs-md: 1.125rem;   /* 18px - lead */
  --emt-fs-lg: 1.5rem;     /* 24px - h4 */
  --emt-fs-xl: 2rem;       /* 32px - h3 */
  --emt-fs-2xl: 2.75rem;   /* 44px - h2 */
  --emt-fs-3xl: 4rem;      /* 64px - h1 / display */

  /* Pesos */
  --emt-fw-normal: 400;
  --emt-fw-medium: 500;
  --emt-fw-semibold: 600;
  --emt-fw-bold: 700;
  --emt-fw-black: 800;

  /* Line heights */
  --emt-lh-tight: 1.1;     /* titulares grandes */
  --emt-lh-snug: 1.3;      /* titulares medios */
  --emt-lh-normal: 1.5;    /* cuerpo */
  --emt-lh-relaxed: 1.65;  /* texto largo lectura */
}
```

### 5.3 Espaciado y layout

```css
:root {
  /* Espaciado (escala base 4px) */
  --emt-spacing-xs: 0.25rem;   /* 4px */
  --emt-spacing-sm: 0.5rem;    /* 8px */
  --emt-spacing-md: 1rem;      /* 16px */
  --emt-spacing-lg: 1.5rem;    /* 24px */
  --emt-spacing-xl: 2rem;      /* 32px */
  --emt-spacing-2xl: 3rem;     /* 48px */
  --emt-spacing-3xl: 4rem;     /* 64px */
  --emt-spacing-4xl: 6rem;     /* 96px */

  /* Layout */
  --emt-container-max: 1280px;
  --emt-container-narrow: 880px;
  --emt-container-wide: 1440px;
  --emt-gutter: 1.5rem;

  /* Border radius */
  --emt-radius-sm: 6px;
  --emt-radius-md: 12px;
  --emt-radius-lg: 18px;
  --emt-radius-xl: 24px;
  --emt-radius-pill: 999px;

  /* Sombras */
  --emt-shadow-sm: 0 1px 3px rgba(0, 51, 102, 0.06);
  --emt-shadow-md: 0 4px 12px rgba(0, 51, 102, 0.08);
  --emt-shadow-lg: 0 12px 30px rgba(0, 51, 102, 0.12);
  --emt-shadow-xl: 0 24px 50px rgba(0, 51, 102, 0.18);

  /* Transiciones */
  --emt-transition-fast: 0.15s ease;
  --emt-transition-base: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  --emt-transition-slow: 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
```

### 5.4 Breakpoints

```css
/* Mobile first - usar min-width */
@media (min-width: 480px)  { /* sm  - mobile landscape */ }
@media (min-width: 720px)  { /* md  - tablet */ }
@media (min-width: 1024px) { /* lg  - desktop */ }
@media (min-width: 1280px) { /* xl  - desktop ancho */ }
@media (min-width: 1440px) { /* 2xl - desktop XL */ }
```

---

## 6. SCHEMA DE DATOS

### 6.1 CPT `tour`

**Registro:**
```php
register_post_type('tour', [
    'labels' => [
        'name' => 'Tours',
        'singular_name' => 'Tour',
        'menu_name' => 'Tours',
        'add_new_item' => 'Agregar nuevo tour',
        'edit_item' => 'Editar tour',
    ],
    'public' => true,
    'has_archive' => 'tours',
    'rewrite' => ['slug' => 'tours', 'with_front' => false],
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
    'menu_icon' => 'dashicons-palmtree',
    'menu_position' => 5,
    'show_in_rest' => true,
]);
```

**Campos ACF:**

| Campo | Tipo | Required | Default | Notas |
|---|---|---|---|---|
| `precio_desde` | número | sí | - | MXN sin decimales |
| `precio_desde_usd` | número | no | null | Para extranjeros |
| `duracion_texto` | texto | sí | - | "1 día", "3 días/2 noches" |
| `duracion_horas` | número | no | - | Para filtros |
| `dificultad` | select | sí | "fácil" | fácil/moderada/alta |
| `idiomas` | checkbox | sí | ["es"] | es, en, fr, otros |
| `punto_salida` | texto | sí | - | "Guadalajara, terminal GDL" |
| `punto_salida_lat` | número | no | - | Para mapa |
| `punto_salida_lng` | número | no | - | Para mapa |
| `peek_url` | url | sí | - | URL de reserva en Peek |
| `incluye` | repeater | sí | - | Lista de items incluidos |
| `no_incluye` | repeater | no | - | Lista de items no incluidos |
| `itinerario` | repeater | sí | - | Día/hora/actividad |
| `politica_cancelacion` | wysiwyg | sí | - | Texto enriquecido |
| `galeria` | gallery | sí | - | Mínimo 4 imágenes |
| `mapa_embed` | url | no | - | URL Google Maps embed |
| `pickup_hotel` | true_false | no | false | Indicador destacado |
| `salida_garantizada` | true_false | no | false | Indicador destacado |
| `destacado` | true_false | no | false | Para home |
| `orden_destacado` | número | no | 99 | Orden en home (menor = primero) |
| `tour_relacionados` | relationship | no | - | Hasta 4 tours |
| `seo_title_override` | texto | no | - | Override de SEO title |
| `seo_desc_override` | textarea | no | - | Override de meta description |

**Repeater `itinerario` (subcampos):**
- `dia` (número): 1, 2, 3...
- `hora` (texto): "08:00", "13:00"
- `titulo` (texto): "Salida desde GDL"
- `descripcion` (textarea)
- `icono` (select): salida, parada, comida, actividad, hospedaje, regreso

**Repeater `incluye` y `no_incluye` (subcampos):**
- `icono` (select): bus, comida, guia, entrada, equipo, hospedaje, foto, otro
- `texto` (texto)

**Campos EN (multi-idioma nativo):**
Para cada campo de texto largo, gemelo con sufijo `_en`:
- `titulo_en`, `excerpt_en`, `descripcion_en`
- `politica_cancelacion_en`
- `incluye_en` (repeater), `no_incluye_en` (repeater)
- `itinerario_en` (repeater, mismo schema con campos `titulo_en` y `descripcion_en`)

### 6.2 CPT `asesor`

| Campo | Tipo | Required | Notas |
|---|---|---|---|
| Title | (post title) | sí | Nombre completo |
| Featured image | thumbnail | sí | Foto profesional 3:4 |
| `puesto` | texto | sí | "Ventas Corporativas" |
| `puesto_en` | texto | no | Inglés |
| `bio_corta` | textarea | sí | 3-4 líneas |
| `bio_corta_en` | textarea | no | |
| `telefono` | texto | sí | "+52 33 1048 0670" |
| `whatsapp` | texto | sí | Solo dígitos: "523310480670" |
| `email` | email | sí | reserva@... |
| `linkedin` | url | no | |
| `instagram` | url | no | |
| `activo` | true_false | sí (default true) | Para ocultar sin borrar |
| `orden` | número | no | Para ordenar directorio |

### 6.3 Taxonomías

**`tour_destino`** (jerárquica, tipo categoría):
- Jalisco
  - Guadalajara
  - Tequila
  - Chapala
  - Tapalpa
- Colima
  - Cañón de Comala
- Baja California Sur
  - La Paz
- Huasteca
  - Xantolo

**`tour_categoria`** (jerárquica):
- Cultural
- Aventura
- Gastronómico
- Religioso
- Familiar
- MICE / Corporativo
- Transporte ejecutivo

**`tour_experiencia`** (no jerárquica, tags):
- Gastronomía
- Aventura y naturaleza
- Lujo y exclusividad
- Cultura
- Bienestar
- Romance
- Día de Muertos
- Mundial 2026

**`asesor_especialidad`** (no jerárquica):
- Tours Jalisco
- Tours nacionales
- MICE
- Grupos
- Luna de miel
- Familiar

**`asesor_idioma`** (no jerárquica):
- Español
- Inglés
- Francés

### 6.4 Custom Fields globales (Options page ACF)

Página `Configuración EMT` en admin con:

- `wa_number` (texto): "523310480670"
- `email_reservas` (email): "reserva@exploramexicotours.com"
- `email_contacto` (email)
- `telefono_oficina` (texto)
- `direccion_fiscal` (textarea)
- `redes_facebook` (url)
- `redes_instagram` (url)
- `redes_tiktok` (url)
- `redes_youtube` (url)
- `google_place_id` (texto): para Reviews API
- `google_places_api_key` (texto, privado)
- `peek_account_id` (texto): para tracking
- `under_construction_mode` (true_false): activa/desactiva UC
- `hero_seasonal_active` (true_false): activa banner estacional
- `hero_seasonal_title` (texto)
- `hero_seasonal_subtitle` (textarea)
- `hero_seasonal_image` (imagen)
- `hero_seasonal_cta_text` (texto)
- `hero_seasonal_cta_url` (url)
- `hero_seasonal_video` (texto, opcional): URL de video MP4
- `mega_menu_destinos` (repeater): nombre, imagen, url, orden
- `mega_menu_experiencias` (repeater): nombre, imagen, url, orden

---

## 7. ARQUITECTURA DE INFORMACIÓN

### 7.1 Sitemap

```
/                                       Home
/tours/                                 Listado general de tours
/tours/destino/{slug}/                  Listado por destino
/tours/categoria/{slug}/                Listado por categoría
/tours/experiencia/{slug}/              Listado por experiencia
/tours/{slug}/                          Ficha individual de tour
/asesores/                              Directorio
/asesores/{slug}/                       Perfil de asesor
/nosotros/                              Página estática
/transporte/                            Sección de transporte ejecutivo
/contacto/                              Formulario contacto
/blog/                                  Listado de entradas
/blog/{slug}/                           Entrada individual
/cotizacion/                            Formulario grupos/MICE
/aviso-de-privacidad/                   Legal
/terminos-y-condiciones/                Legal

/en/                                    Home inglés
/en/tours/...                           Todo el árbol replicado con prefijo
```

### 7.2 URL slugs estándar

- Lowercase
- Sin acentos ni ñ (transliterado): `dia-de-muertos`, no `día-de-muertos`
- Guiones medios, no underscores
- Sin palabras vacías cuando se pueda: `tequila-cuervo` no `tour-a-tequila-en-tren-cuervo`

### 7.3 Mega-menú (estructura)

**Botón 1: "¿A dónde ir?"** → Destinos
```
┌─────────────────────────────────────────────────┐
│ Jalisco          Colima         Baja Cal Sur    │
│ [imagen]         [imagen]       [imagen]        │
│                                                 │
│ Huasteca         Otros destinos  Ver todos →    │
│ [imagen]         [imagen]                       │
└─────────────────────────────────────────────────┘
```

**Botón 2: "¿Qué tour?"** → Categorías
```
┌─────────────────────────────────────────────────┐
│ Cultural         Aventura       Gastronómico    │
│ [imagen]         [imagen]       [imagen]        │
│                                                 │
│ Familiar         MICE           Transporte      │
│ [imagen]         [imagen]       [imagen]        │
└─────────────────────────────────────────────────┘
```

**Botón 3: "Experiencias"** → Por intención (taxonomía experiencia)
```
Gastronomía · Aventura · Lujo · Cultura · Romance · Día de Muertos
```

**Items lineales** (sin mega):
- Asesores
- Blog
- Contacto

**A la derecha:**
- Selector ES/EN
- Botón "Cotizar grupo" (rosa)

### 7.4 Patrón de breadcrumbs

```
Inicio › Tours › Jalisco › Tour a Tequila Express
Inicio › Asesores › María López
Inicio › Blog › Categoría › Título del artículo
```

---

## 8. ESPECIFICACIÓN POR PÁGINA

### 8.1 Home (`front-page.php`)

**Estructura vertical:**

1. **Header sticky** con mega-menú, selector idioma, CTA cotización
2. **Hero estacional rotativo** (configurable desde admin)
   - Si `hero_seasonal_active = true`: muestra slide configurado (imagen/video + título + subtítulo + CTA)
   - Si `false`: muestra hero institucional fijo
   - Indicador de progreso si hay múltiples slides
3. **Grid de destinos destacados** (tipo VJ "A dónde ir")
   - 4-5 cards visuales por destino
   - Hover: scale + elevación
4. **"Tours imperdibles"** - carrusel/grid con 6-8 tours marcados como `destacado = true`
   - Card incluye: imagen, badge categoría, título, duración, precio desde, CTA "Ver tour"
5. **"Explora por experiencia"** - bloque con 6 cards de taxonomía experiencia (Gastronomía, Aventura, etc.)
6. **Bloque "Una trayectoria real"** - números (años, tours, viajeros) tipo trust line del UC
7. **Testimonios + Google Reviews** - widget con últimas 6 reviews de la Place API
8. **Blog destacado** - 3 últimas entradas con foto, título, excerpt
9. **CTA cotización grupos** - banner azul/magenta con formulario rápido o link a /cotizacion
10. **Credenciales** - sellos AMAV, Moderniza, AMTAVE en barra
11. **Footer**

### 8.2 Listado de tours (`archive-tour.php`)

**Estructura:**

1. Header + breadcrumbs
2. **Hero compacto** del listado con título dinámico ("Tours en Jalisco", "Tours Culturales", etc.) e imagen de fondo si la taxonomía la define
3. **Filter bar** sticky lateral o superior:
   - Filtro por destino (multiselect)
   - Filtro por categoría
   - Filtro por experiencia
   - Filtro por dificultad (radio)
   - Filtro por duración (range slider 1-5+ días)
   - Filtro por rango de precio (range slider)
   - Búsqueda libre
   - Botón "Limpiar filtros"
   - Contador de resultados
4. **Grid de cards** (3 columnas desktop, 2 tablet, 1 móvil)
5. **Paginación** o "Cargar más" con AJAX
6. **CTA "No encuentras lo que buscas? Cotizar a la medida"**

**Card de tour (componente reutilizable):**
```
┌─────────────────────────┐
│  [Imagen principal]     │
│  [Badge categoría]      │
│  [Badge "Salida gar."]  │
├─────────────────────────┤
│  Tour a Tequila Cuervo  │ ← Título h3
│  📍 Jalisco · 1 día     │ ← Meta info
│  Cultural · Gastronómico│ ← Tags
│  ────────────────────   │
│  Desde $1,899 MXN       │ ← Precio
│  [Ver tour →]           │ ← CTA azul
└─────────────────────────┘
```

### 8.3 Ficha de tour (`single-tour.php`)

**Estructura:**

1. Header + breadcrumbs
2. **Hero del tour:**
   - Galería grid (1 imagen grande + 4 thumbnails clicables que abren lightbox)
   - Título grande
   - Meta: destino, duración, dificultad, idiomas (iconos)
   - Badges: salida garantizada, pickup hotel (si aplica)
3. **Barra sticky de reserva** (aparece al hacer scroll):
   - Precio desde
   - Botón rosa "Reservar ahora" → Peek
   - Botón secundario "Solicitar cotización"
4. **Cuerpo dos columnas (desktop):**
   - **Columna principal (8/12):**
     - Descripción larga (contenido del editor)
     - Itinerario expandible por día (acordeón)
     - "Qué incluye" (lista con iconos verdes)
     - "Qué NO incluye" (lista con iconos rojos)
     - Mapa embed del punto de salida
     - Política de cancelación
     - FAQ (si aplica)
   - **Columna lateral sticky (4/12):**
     - Card de reserva (precio + CTA Peek + cotización)
     - "Asesor recomendado" si hay match por especialidad
     - Box "Comparte este tour" (FB, X, WhatsApp, copiar link)
5. **Reviews del tour** (si hay)
6. **Tours relacionados** (4 cards)
7. **Footer**

**Schema.org TouristTrip:** inyectado en `<head>` con todos los datos del tour.

### 8.4 Directorio de asesores (`archive-asesor.php`)

1. Header + breadcrumbs
2. Hero con título "Nuestro equipo de asesores"
3. Filter bar: por idioma, por especialidad
4. Grid de cards (4 columnas desktop):
   - Foto vertical 3:4
   - Nombre
   - Puesto
   - Idiomas (banderitas o iconos)
   - Especialidades (tags)
   - Botones: WhatsApp directo, Ver perfil
5. CTA cotización grupos

### 8.5 Perfil de asesor (`single-asesor.php`)

1. Header
2. **Hero del asesor:**
   - Foto grande
   - Nombre, puesto
   - Bio corta
   - Idiomas, especialidades
   - **Acciones:**
     - Botón verde grande "WhatsApp directo"
     - Botón "Llamar"
     - Botón "Enviar email"
     - **Descargar vCard** (`/asesores/{slug}/vcard`)
     - **Código QR** visible (que contiene la vCard)
3. "Tours que recomiendo" - 4 tours seleccionados (si aplica via campo)
4. Otros asesores del mismo equipo (3 cards)
5. Footer

**Endpoint vCard:**
```
URL: /asesores/{slug}/vcard
Output: archivo .vcf con BEGIN:VCARD ... END:VCARD
Tracking: increment counter cada descarga
```

**Atribución de ventas:**
- URL: `/asesores/{slug}/?ref=mlopez`
- El `?ref` se guarda en cookie 30 días
- Al click en Peek o al enviar cotización, se pasa como custom dimension a GA4

### 8.6 Cotización (`template-cotizacion.php`)

Página con formulario robusto:

- Nombre completo
- Email
- WhatsApp
- Tipo de viaje (radio: Grupo / MICE / Personalizado / Otro)
- Número de personas
- Fechas tentativas
- Destino o tour de interés (opcional)
- Detalles del grupo (textarea)
- Campo oculto `ref` si viene de un asesor

Submit → guarda en CPT `cotizacion` (interno, no público) + envía email a `reserva@exploramexicotours.com` + responde WhatsApp del asesor referido si aplica.

### 8.7 Páginas estáticas (`page.php`)

Nosotros, Transporte, Contacto, Aviso de privacidad, Términos: estructura simple con hero, contenido editable desde admin, sidebar opcional.

---

## 9. INTEGRACIONES

### 9.1 Peek (motor de reservas)

**Modelo:** redirección con tracking. NO se embebe widget en esta fase.

```php
// En single-tour.php, el CTA principal:
<a href="<?php echo esc_url( $peek_url ); ?>"
   class="emt-btn emt-btn--primary emt-btn--peek"
   data-tour-id="<?php echo esc_attr( $tour_id ); ?>"
   data-tour-title="<?php echo esc_attr( get_the_title() ); ?>"
   target="_blank" rel="noopener">
   Reservar ahora
</a>
```

**Tracking JS:**
```javascript
// En core.js
document.querySelectorAll('.emt-btn--peek').forEach(btn => {
  btn.addEventListener('click', e => {
    if (window.gtag) {
      gtag('event', 'click_reservar_peek', {
        tour_id: btn.dataset.tourId,
        tour_title: btn.dataset.tourTitle,
        ref_asesor: getCookie('emt_ref_asesor') || 'directo'
      });
    }
    if (window.fbq) {
      fbq('track', 'InitiateCheckout', { content_name: btn.dataset.tourTitle });
    }
  });
});
```

### 9.2 Google Reviews

**Endpoint custom:**
```
GET /wp-json/emt/v1/reviews
Response: array de reviews cacheado (transient 6 horas)
```

Llamada interna a Google Places API con `place_id` configurado. Output sanitizado y limitado a 20 reviews.

### 9.3 WhatsApp con flujo guiado

Módulo flotante (botón verde esquina inferior derecha) que abre un widget con preguntas paso a paso:

1. "¡Hola! ¿En qué te ayudamos?"
   - [Reservar un tour]
   - [Solicitar cotización]
   - [Información general]
2. Según selección, ramifica preguntas:
   - Tour: ¿qué destino?
   - Cotización: ¿cuántas personas?
3. Captura email opcional
4. Genera mensaje WhatsApp pre-llenado y abre `wa.me/523310480670?text=...`

**Estado** del flujo en `localStorage` para no perder progreso si recargan.

### 9.4 Analítica

**Google Analytics 4 (vía GTM):**
- Pageviews automáticos
- Custom events:
  - `click_reservar_peek` (tour_id, tour_title, ref_asesor)
  - `submit_cotizacion`
  - `click_whatsapp`
  - `vcard_download` (asesor_id)
  - `lang_switch` (from, to)
  - `mega_menu_open`

**Meta Pixel:**
- PageView automático
- `Lead` en envío de cotización
- `InitiateCheckout` en click a Peek

**Search Console:** verificación por meta tag o DNS.

### 9.5 Schema.org (SEO)

Cada ficha de tour inyecta JSON-LD tipo `TouristTrip`:

```json
{
  "@context": "https://schema.org",
  "@type": "TouristTrip",
  "name": "Tour a Tequila Cuervo",
  "description": "...",
  "image": ["..."],
  "offers": {
    "@type": "Offer",
    "price": "1899",
    "priceCurrency": "MXN",
    "url": "..."
  },
  "provider": {
    "@type": "TravelAgency",
    "name": "Explora México Tours",
    "address": "...",
    "telephone": "+523310480670"
  },
  "itinerary": [...]
}
```

Home: schema `Organization` + `TravelAgency`.
Perfil asesor: schema `Person` con `worksFor`.

---

## 10. INTERNACIONALIZACIÓN (i18n)

### 10.1 Sistema bilingüe nativo (sin plugins)

**URLs:**
- ES (default): `exploramexicotours.com/tours/tequila-cuervo/`
- EN: `exploramexicotours.com/en/tours/tequila-cuervo/`

**Rewrite rules** en `inc/i18n.php`:
```php
add_rewrite_rule('^en/?$', 'index.php?lang=en', 'top');
add_rewrite_rule('^en/(.+?)/?$', 'index.php?lang=en&pagename=$matches[1]', 'top');
```

**Detección de idioma:**
```php
function emt_current_lang() {
    if (strpos($_SERVER['REQUEST_URI'], '/en/') === 0
        || $_SERVER['REQUEST_URI'] === '/en'
        || $_SERVER['REQUEST_URI'] === '/en/') {
        return 'en';
    }
    return 'es';
}
```

**Función helper para campos:**
```php
function emt_get_field($field_name, $post_id = null) {
    $lang = emt_current_lang();
    $field = ($lang === 'en') ? $field_name . '_en' : $field_name;
    $value = get_field($field, $post_id);
    // Fallback a ES si EN está vacío
    if (empty($value) && $lang === 'en') {
        $value = get_field($field_name, $post_id);
    }
    return $value;
}
```

**Diccionario de UI strings** (`assets/i18n/es.php`, `en.php`):
```php
// es.php
return [
    'reservar_ahora' => 'Reservar ahora',
    'ver_tour' => 'Ver tour',
    'desde' => 'Desde',
    'duracion' => 'Duración',
    // ...
];
```

**Helper:**
```php
function emt_t($key) {
    static $translations = null;
    if ($translations === null) {
        $lang = emt_current_lang();
        $translations = require get_stylesheet_directory() . "/assets/i18n/{$lang}.php";
    }
    return $translations[$key] ?? $key;
}
```

### 10.2 Hreflang

En `<head>` de cada página:
```html
<link rel="alternate" hreflang="es-MX" href="https://exploramexicotours.com/tours/tequila-cuervo/" />
<link rel="alternate" hreflang="en" href="https://exploramexicotours.com/en/tours/tequila-cuervo/" />
<link rel="alternate" hreflang="x-default" href="https://exploramexicotours.com/tours/tequila-cuervo/" />
```

### 10.3 Switcher de idioma

Componente en header. Switch entre ES/EN preserva la URL actual:
- En `/tours/tequila-cuervo/` → switch a EN → `/en/tours/tequila-cuervo/`
- En `/en/asesores/maria-lopez/` → switch a ES → `/asesores/maria-lopez/`

---

## 11. SEGURIDAD

### 11.1 Hardening obligatorio

- **2FA obligatorio** para todos los usuarios con rol Administrator
- **Wordfence o Solid Security Pro** activo y configurado
- **wp-config.php:**
  ```php
  define('DISALLOW_FILE_EDIT', true);
  define('DISALLOW_FILE_MODS', true);  // Solo si no se necesita instalar plugins desde admin
  define('FORCE_SSL_ADMIN', true);
  define('WP_AUTO_UPDATE_CORE', 'minor');
  ```
- **Esconder versión de WP** del HTML output
- **Deshabilitar XML-RPC** salvo necesidad explícita
- **Limitar intentos de login** (parte del plugin de seguridad)
- **Headers de seguridad** vía .htaccess o Nginx:
  - `Strict-Transport-Security`
  - `X-Frame-Options: SAMEORIGIN`
  - `X-Content-Type-Options: nosniff`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Content-Security-Policy` (configurar según los recursos externos: Google Fonts, Cloudflare, Peek)

### 11.2 Backups

- **Diarios automáticos** vía script o plugin (UpdraftPlus / BackWPup)
- **Retención:** 30 días
- **Destino:** S3, Backblaze B2, o snapshot Hetzner (no en el mismo servidor)
- **Test mensual** de restauración

### 11.3 Roles de usuario

| Rol | Permisos |
|---|---|
| Administrator | Todo (solo Fabian y owner EMT) |
| Editor | Tours, asesores, blog, páginas, medios |
| Tour Manager (custom) | Solo CPT tour (CRUD) |
| Asesor Manager (custom) | Solo CPT asesor (CRUD) |
| Author | Blog únicamente |

### 11.4 Compliance LFPDPPP

- Aviso de privacidad accesible desde footer en todas las páginas
- Banner de cookies con consent (opt-in para tracking)
- Form de cotización con checkbox de aceptación
- Política de retención de datos clara

---

## 12. SEO TÉCNICO

### 12.1 Esenciales

- Sitemap XML automático (no plugin pesado; función custom o RankMath Lite)
- Robots.txt configurado
- Schema.org en cada tipo de página
- Open Graph tags + Twitter Cards
- Canonical URLs correctos
- 404 personalizada con sugerencias
- Redirects 301 mapeados del sitio viejo (cuando aplique)
- Slugs limpios y descriptivos
- Alt text en todas las imágenes
- Heading hierarchy correcta (h1 único por página)

### 12.2 Core Web Vitals

Objetivos del proyecto:
- **LCP** < 2.5s
- **FID** < 100ms (CLS más relevante hoy: INP < 200ms)
- **CLS** < 0.1

Acciones:
- Imágenes con dimensiones explícitas y `loading="lazy"`
- Fonts con `font-display: swap` y preload de variantes críticas
- CSS crítico inline en `<head>` (opcional fase 2)
- JS modular con `defer`
- CDN Cloudflare al frente

### 12.3 Sitemap structure

```
/sitemap.xml
├── /sitemap-pages.xml
├── /sitemap-tours.xml
├── /sitemap-asesores.xml
├── /sitemap-blog.xml
└── /sitemap-en.xml (todas las URLs inglés)
```

---

## 13. PERFORMANCE

### 13.1 Optimizaciones obligatorias

- **Cache de página:** WP Super Cache o LiteSpeed Cache (según servidor)
- **Object cache:** Redis o Memcached si Hetzner lo permite
- **Imágenes:**
  - WebP convertido vía plugin ligero o conversión manual
  - Responsive `srcset` automático
  - Lazy loading nativo
  - Tamaños registrados en `setup.php`: `tour-card` (600x750), `tour-hero` (1920x800), `asesor-portrait` (400x500)
- **Minificación CSS/JS** vía Cloudflare o plugin ligero
- **Database:** índices en custom meta queries frecuentes (`peek_url`, `destacado`)
- **HTTP/2** y **gzip/brotli** en Nginx

### 13.2 No-go (cosas que matan performance)

- Plugins de sliders pesados (Revolution Slider, Slider Revolution)
- Page builders adicionales a Elementor
- Visual Composer / WPBakery
- Plugin de "optimización" que hace más de lo que dice
- Más de 15 plugins activos en total
- Queries sin `'no_found_rows'` en bucles grandes

---

## 14. ORDEN DE CONSTRUCCIÓN (Recomendación técnica)

**Mi recomendación para Claude Code: construir en este orden, no improvisar.**

### Fase A — Cimientos (Día 1-3)

**A1.** Setup del child theme: `style.css`, `functions.php`, `inc/setup.php`, `inc/enqueues.php`. Cargar tokens CSS y tipografías. Heredar de Hello Elementor.

**A2.** Registrar CPTs `tour` y `asesor` (`inc/cpts.php`).

**A3.** Registrar taxonomías (`inc/taxonomies.php`).

**A4.** Registrar campos ACF en código (`inc/acf-fields.php`) — usar export PHP, no JSON.

**A5.** Sistema bilingüe (`inc/i18n.php`) + diccionario base.

**A6.** Options page de configuración EMT (`inc/admin-panel.php`).

**A7.** Setup de seguridad básica (`inc/security.php`).

**Resultado:** WordPress con la estructura de datos lista, vacío de contenido pero administrable.

### Fase B — Componentes base (Día 4-6)

**B1.** Header con mega-menú estático (sin datos reales).

**B2.** Footer con credenciales y enlaces.

**B3.** Componente `tour-card.php` (parts).

**B4.** Componente `asesor-card.php` (parts).

**B5.** Selector de idioma.

**B6.** Botón WhatsApp flotante.

**B7.** Helper functions: `emt_get_field()`, `emt_t()`, `emt_render_card()`, `emt_breadcrumbs()`.

**Resultado:** sistema de componentes listo para usarse en plantillas.

### Fase C — Plantillas críticas (Día 7-12)

**C1.** `front-page.php` (Home) — sin hero estacional aún, solo estructura.

**C2.** `archive-tour.php` (Listado de tours) — con filtros básicos.

**C3.** `taxonomy-tour_destino.php`, `taxonomy-tour_categoria.php`, `taxonomy-tour_experiencia.php`.

**C4.** `single-tour.php` (Ficha de tour).

**C5.** `archive-asesor.php` (Directorio).

**C6.** `single-asesor.php` (Perfil con vCard endpoint y QR).

**Resultado:** sitio navegable de extremo a extremo.

### Fase D — Diferenciadores y carga de contenido (Día 13-17)

**D1.** Hero estacional configurable desde admin.

**D2.** Mega-menú dinámico con imágenes desde Options.

**D3.** Filtros avanzados en archive-tour (AJAX, no recarga).

**D4.** Schema.org TouristTrip en single-tour.

**D5.** Integración Peek con tracking GA4 + Meta Pixel.

**D6.** Módulo WhatsApp con flujo guiado.

**D7.** Integración Google Reviews (Places API + transient cache).

**D8.** Carga de los 70 tours (vía CSV import o manual).

**D9.** Carga de asesores.

### Fase E — Páginas estáticas y formularios (Día 18-21)

**E1.** Nosotros, Transporte, Contacto.

**E2.** `template-cotizacion.php` (formulario).

**E3.** Sistema de email transaccional + Workspace SMTP.

**E4.** CPT `cotizacion` para guardar leads.

**E5.** Aviso de privacidad LFPDPPP + términos.

### Fase F — Hardening, SEO, QA (Día 22-26)

**F1.** Sitemap XML custom o RankMath Lite.

**F2.** Schema.org en home, asesores, blog.

**F3.** Headers de seguridad.

**F4.** Cache + optimización imágenes.

**F5.** 2FA obligatorio admins.

**F6.** Backups configurados.

**F7.** QA exhaustivo (ver §15).

### Fase G — Lanzamiento (Día 27-28)

**G1.** Cambio de `EMT_UNDER_CONSTRUCTION = false`.

**G2.** Submit a Search Console.

**G3.** Verificar tracking GA4 + Meta Pixel.

**G4.** Capacitación al equipo.

**G5.** Entrega de manual operativo.

**G6.** Plan de cierre del sitio viejo (handover).

---

## 15. DEFINICIÓN DE HECHO (DoD)

Una plantilla, componente o feature está **HECHO** cuando cumple TODOS estos puntos:

### Para todo:
- [ ] Código sigue convenciones (§3): prefijos, BEM, escapes, sanitización
- [ ] Sin errores en consola JS
- [ ] Sin warnings PHP en `WP_DEBUG = true`
- [ ] Responsive en móvil (320px+), tablet (768px), desktop (1280px+)
- [ ] Funciona en Chrome, Safari, Firefox últimas 2 versiones
- [ ] Accesible: contrastes WCAG AA, navegable por teclado, alt en imágenes
- [ ] Pasa Lighthouse > 85 en Performance, > 95 en SEO y Accessibility

### Para plantillas:
- [ ] Versión ES funcional
- [ ] Versión EN funcional con switcher
- [ ] Schema.org correspondiente inyectado
- [ ] Meta tags (title, description, OG, Twitter) correctos
- [ ] Breadcrumbs presentes
- [ ] Tracking de eventos relevantes disparado

### Para componentes:
- [ ] Documentado en `parts/` con comentario PHPDoc del propósito
- [ ] Acepta parámetros (no hardcoded)
- [ ] Tiene fallback si datos faltan (no rompe)

### Para integraciones:
- [ ] Maneja errores (try/catch, timeouts, fallbacks)
- [ ] No expone API keys en frontend
- [ ] Cache implementado si aplica
- [ ] Logs en error_log cuando falla

---

## 16. CHECKLIST QA PRE-LANZAMIENTO

### Funcional
- [ ] Todos los tours se ven correctamente en listado y ficha
- [ ] Todos los asesores se ven correctamente
- [ ] Filtros funcionan en archive
- [ ] Switch ES/EN preserva URL
- [ ] Todos los CTAs van a destino correcto (Peek, WhatsApp, cotización)
- [ ] Formulario de cotización envía email y guarda en BD
- [ ] WhatsApp guiado completa flujo y abre wa.me
- [ ] vCard se descarga correctamente y abre en iPhone/Android
- [ ] QR código contiene la vCard, no solo URL
- [ ] Atribución por asesor se rastrea en GA4

### Performance
- [ ] Home carga en menos de 3s en conexión 4G simulada
- [ ] LCP < 2.5s, INP < 200ms, CLS < 0.1
- [ ] Imágenes en WebP con fallback
- [ ] Sin CSS bloqueante crítico
- [ ] JS con defer

### SEO
- [ ] Sitemap XML accesible
- [ ] Schema.org valida en https://validator.schema.org
- [ ] Open Graph valida en https://www.opengraph.xyz
- [ ] Hreflang correctos en ES y EN
- [ ] Canonical correcto en cada página
- [ ] Search Console verificado

### Seguridad
- [ ] HTTPS forzado
- [ ] 2FA activo en admins
- [ ] Wordfence/Solid configurado y activo
- [ ] Backups corriendo
- [ ] wp-config con DISALLOW_FILE_EDIT
- [ ] Headers de seguridad presentes
- [ ] Aviso de privacidad y cookies activos

### Contenido
- [ ] 70 tours cargados y validados
- [ ] 100% de tours tienen precio, duración, itinerario, galería, política de cancelación, URL Peek
- [ ] Asesores con foto profesional y datos completos
- [ ] Páginas legales redactadas y aprobadas por cliente
- [ ] Redirects 301 del sitio viejo configurados (si aplica)

### Capacitación y handover
- [ ] Manual de operación entregado
- [ ] Capacitación grabada y enviada
- [ ] Credenciales entregadas al cliente
- [ ] Documentación del repo actualizada
- [ ] Plan de cierre del sitio viejo entregado

---

## 17. NOTAS PARA CLAUDE CODE

### Instrucciones de operación

1. **Lee este documento completo antes de empezar.** No saltes secciones.
2. **Pregunta antes de improvisar.** Si una decisión no está aquí, no la tomes solo, escala al director técnico.
3. **Sigue el orden de construcción (§14).** No saltes fases.
4. **Documenta cada commit** con mensaje claro: `[Fase B] Agregar tour-card.php con galería responsive`.
5. **Cuando termines una fase**, pausa y reporta para validación humana antes de continuar.
6. **Si encuentras conflicto** entre este documento y un plugin/dependencia, prioriza este documento y propón la solución, no la implementes sin validar.

### Comandos útiles

```bash
# Levantar entorno local
wp server

# Importar tours desde CSV
wp emt tour import /path/to/tours.csv

# Limpiar cache
wp cache flush

# Verificar i18n
wp emt i18n verify

# Activar/desactivar under construction
wp option update emt_under_construction true
wp option update emt_under_construction false
```

### Estructura de commits

```
feat(home): agregar hero estacional rotativo
fix(tour-card): corregir overlay en imágenes oscuras
refactor(i18n): extraer helper emt_t a inc/i18n.php
docs(readme): actualizar instrucciones de setup
chore(deps): actualizar Hello Elementor a 3.x
```

---

## 18. ADENDA — DECISIONES EN EL CAMINO

(Esta sección se actualiza durante el proyecto. Cualquier decisión nueva se documenta aquí con fecha.)

### v1.0 — Definiciones iniciales (junio 2026)
- Stack confirmado: WordPress + Hello Elementor + child + ACF Pro
- Sin WooCommerce (decisión arquitectónica firme)
- Sin WPML (bilingüe nativo en código)
- Booking: Peek (enlace + tracking GA4/Meta)
- Servidor: Hetzner Cloud + CloudPanel (sin cPanel)
- Correos: Google Workspace (separado del servidor)
- Referente visual aprobado: visitjalisco.mx
- Bajo construcción ya en producción con child theme activo
- Identidad de marca definida según manual oficial EMT
- Precio cerrado: $18,000 MXN + IVA (descuento AMAV 20%)
- Pago: 50% al inicio + 50% al lanzamiento
- 90 días de mantenimiento + 365 días de monitoreo incluidos

### v1.1 — Cierre del documento (junio 2026)

**Google Cloud Platform — gestión bajo cuenta de Supratecnia:**
- El proyecto Google Cloud se crea en la cuenta de Supratecnia, no del cliente
- Razón: cliente sin equipo técnico interno, evita fricción con tarjetas y permisos
- Costo real estimado: 0 USD/mes (dentro del crédito gratis de 200 USD)
- Si en el futuro EMT quiere migrar el proyecto a su propio Google Cloud, la transferencia es directa (~2 clicks en consola)

**Place ID y API Key — pendientes operativos:**
- Place ID se obtiene cuando se valide que EMT tiene ficha de Google Business completa
- Si no la tienen, se ayuda al cliente a reclamarla en business.google.com (fuera de alcance técnico pero impacta SEO)
- API Key se restringe a HTTP referrers de los dominios de producción + staging
- Restricciones de Places API únicamente, no acceso a otras APIs

**Tracking Peek — confirmación pendiente del cliente:**
- Se solicitó al cliente confirmar 3 puntos con su operador de Peek Pro:
  1. ¿Hay Account ID o Partner ID asignado?
  2. ¿Tienen Peek Tracking Pixel instalado en el sitio anterior?
  3. ¿Quién es su Account Manager en Peek?
- **Plan A (tracking nativo Peek):** se integra script de Peek + se reportan conversiones bilaterales (GA4 + Peek)
- **Plan B (sin tracking nativo):** se manejan eventos personalizados solo en GA4 + Meta Pixel — esto NO bloquea el proyecto
- La integración Peek queda en Fase D del orden de construcción, lo cual da margen para la respuesta del cliente

**Decisiones bloqueantes para Fase A (ninguna):**
- Ni Place ID ni respuesta de Peek detienen el inicio
- Claude Code puede arrancar inmediatamente con Fase A (cimientos) — ver §14

### v1.2 — Cierre de Fase A · Cimientos (junio 2026)

Decisiones de construcción tomadas durante la Fase A (ver PR #2). Todas verificadas en entorno local (Local by Flywheel) antes del merge a `dev`.

**Corrección de infraestructura (ver §2.1):**
- Producción corre **Coolify + Docker** (WordPress + MariaDB en contenedores, Traefik proxy), **no CloudPanel/Nginx**. Implicaciones de deploy, persistencia de volúmenes y ACF Pro como dependencia de entorno documentadas en §2.1.

**CPT `asesor` (el §6.2 no especificaba el registro):**
- `has_archive => 'asesores'`, `rewrite slug => 'asesores'`, `supports => [title, thumbnail, excerpt, revisions]`, `menu_icon => dashicons-businessperson`, `menu_position => 6`, `show_in_rest => true`.

**Slugs de URL de taxonomías (el §6.3 no los definía):**
- `tour_destino → destino`, `tour_categoria → categoria-tour` (evita choque con la `category` nativa de WP), `tour_experiencia → experiencia`, `asesor_especialidad → especialidad`, `asesor_idioma → idioma`.
- Todas con `show_in_rest => true` y `show_admin_column => true`.

**Campos ACF (§6.1/§6.2/§6.4 — registrados por código con `acf_add_local_field_group`):**
- Organizados en **pestañas (tabs)** para usabilidad (no estaba en el doc).
- Tipos inferidos donde el doc era ambiguo: `dificultad` select (`facil`/`moderada`/`alta`, valores ascii con labels acentuados, default `facil`); `idiomas` checkbox (`es`/`en`/`fr`/`otros`, default `["es"]`); `excerpt_en` textarea, `descripcion_en` wysiwyg; `true_false` con `ui:1`; `tour_relacionados` `return_format:id, max:4`; imágenes `return_format:array`.
- Prefijo de field keys: `field_emt_{cpt}_` para tour/asesor; **`field_emt_config_`** para la Options page (el §3.3 solo definía `{cpt}`).
- Conteos finales (sin contar tabs): **tour = 30** (23 base + 7 gemelos `_en`), **config = 22**, **asesor = 11**.
- Los 3 grupos son **locales** (`local => php`): no editables desde la UI de ACF.

**i18n — routing bilingüe (§10):**
- Estrategia **strip-prefix (Opción A)**: el prefijo `/en/` se retira en `do_parse_request` y WP resuelve la ruta restante con sus reglas normales → cubre páginas, CPTs, taxonomías y archivos uniformemente.
- **Motivo del cambio:** el snippet del §10.1 (`add_rewrite_rule('^en/(.+?)/?$', '…pagename=$matches[1]')`) **rompía con CPTs/taxonomías** (solo resolvía Páginas estáticas → 404 para `/en/tours/…`), contradiciendo el §7.1 ("árbol replicado con prefijo"). El §10.1 queda **reemplazado** por strip-prefix.
- `emt_current_lang()` captura el path **original** en `$GLOBALS['emt_request_path']` al cargar el módulo, en vez de leer `$_SERVER['REQUEST_URI']` en vivo (el strip-prefix lo modifica). Mismo comportamiento, más robusto.
- Se conservó la regla `^en/?$ → index.php?lang=en` registrada (+ flush en `after_switch_theme`) como artefacto/fallback.

**Seguridad — división theme vs servidor (§11.1):**
- **En el theme** (`inc/security.php`): ocultar versión de WP (generator + `?ver` del core, surgical), deshabilitar XML-RPC autenticado + quitar `X-Pingback`, headers `X-Frame-Options`/`X-Content-Type-Options`/`Referrer-Policy` vía `send_headers`, quitar RSD/wlwmanifest/shortlink, forzar `DISALLOW_FILE_EDIT`.
- **Diferido a servidor (Nginx/Cloudflare):** `Content-Security-Policy` (requiere mapear recursos externos), `Strict-Transport-Security` (HSTS), bloqueo total de `xmlrpc.php` (el filtro del theme solo cubre métodos autenticados).

**A6 cubierto en A4:** la Options page "Configuración EMT" se implementó dentro del bloque de ACF.

**Pendientes anotados para fases futuras:**
- Definir el **pipeline de deploy Coolify ↔ GitHub** (auto-deploy `dev`→staging, `main`→producción).
- **CSP** y **HSTS** a nivel servidor/Cloudflare (cuando el sitio esté completo y los recursos externos mapeados).
- **Bloqueo total de `xmlrpc.php`** vía regla Nginx (`deny`), complementando el filtro del theme.
- **Roles custom (§11.3):** Tour Manager y Asesor Manager (CRUD restringido a su CPT) — no entraban en A7.

### (próximas decisiones aquí)

---

## 19. CONTACTOS DEL PROYECTO

| Rol | Nombre | Contacto |
|---|---|---|
| Director técnico | Fabian Valdez | soporte@supratecnia.com · 33-1295-4302 |
| Owner EMT | (por confirmar) | (por confirmar) |
| WhatsApp principal EMT | - | 523310480670 |
| Email reservas | - | reserva@exploramexicotours.com |
| Domicilio EMT | - | Guadalajara, Jalisco |

---

## 20. CHECKLIST PRE-FASE A (KICKOFF)

Antes de que Claude Code escriba la primera línea de código, validar que estos elementos están en mano:

### Infraestructura
- [ ] Servidor Hetzner Cloud activo con CloudPanel instalado
- [ ] PHP 8.2+ y MariaDB/MySQL configurados según §2.1
- [ ] Dominio apuntando al servidor (DNS configurado)
- [ ] SSL Let's Encrypt activo
- [ ] WordPress instalado en versión estable
- [ ] Hello Elementor instalado (no activado todavía)
- [ ] Elementor Pro con licencia válida
- [ ] ACF Pro con licencia válida
- [ ] Code Snippets Pro con licencia válida

### Repositorio
- [ ] Repo Git creado (GitHub / GitLab)
- [ ] Este documento maestro commiteado en `/docs/PROYECTO-EMT-DOC-MAESTRO.md`
- [ ] README inicial del proyecto
- [ ] `.gitignore` configurado (excluir `wp-config.php`, uploads, etc.)
- [ ] Branch strategy definida (main / dev / feature)

### Accesos
- [ ] SSH al servidor configurado y probado
- [ ] WP admin con cuenta de director técnico
- [ ] Google Workspace activo con 10 cuentas creadas o en proceso
- [ ] Google Cloud Platform: proyecto creado, Places API habilitada, API Key restringida
- [ ] GA4 property creada
- [ ] GTM container creado
- [ ] Meta Business Manager con Pixel creado
- [ ] Search Console verificado

### Contenido base
- [ ] Logo de EMT en formatos: SVG, PNG transparente, PNG fondo oscuro
- [ ] Manual de marca PDF disponible en el repo (`/docs/branding/`)
- [ ] Imagen de hero estacional inicial (Día de Muertos o lo que aplique al lanzamiento)
- [ ] Lista preliminar de tours a cargar (al menos 10 para el primer release de listados)
- [ ] Lista preliminar de asesores con fotos

### Decisiones cerradas
- [ ] Confirmación escrita del cliente: se mantiene Peek como motor de reservas
- [ ] Confirmación escrita del cliente: alcance según propuesta P-10688-EMT
- [ ] Respuesta del cliente sobre tracking Peek (Plan A o Plan B)
- [ ] Aviso de privacidad LFPDPPP redactado o aprobado por jurídico EMT
- [ ] Términos y condiciones redactados o aprobados

### Setup inicial técnico
- [ ] Entorno de desarrollo local funcional (DevKinsta / Local / Docker)
- [ ] Conexión al servidor de staging probada
- [ ] WP-CLI instalado y operativo en servidor
- [ ] Plugin de backups configurado y primera corrida exitosa

**Si todo lo anterior está marcado, Fase A puede iniciar.**

---

**FIN DEL DOCUMENTO MAESTRO**

Este documento debe revisarse y actualizarse al cierre de cada fase del proyecto. Toda decisión nueva, cambio de alcance o aprendizaje técnico relevante se incorpora a §18 Adenda.
