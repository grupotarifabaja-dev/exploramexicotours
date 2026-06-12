# Explora México Child Theme

Child theme de Hello Elementor para Explora México Tours.
Incluye modo "Under Construction" activable por constante.

---

## Instalación

### 1. Subir el theme al servidor

Estructura final esperada:

```
/wp-content/themes/
├── hello-elementor/              ← parent (instalar desde WP admin)
└── explora-mexico-child/         ← este theme
    ├── style.css
    ├── functions.php
    ├── template-under-construction.php
    └── assets/
        ├── under-construction.css
        └── under-construction.js
```

Sube vía FTP, SSH o subiendo el ZIP en WP admin → Apariencia → Temas → Añadir nuevo → Subir tema.

### 2. Pre-requisitos en WordPress

- Instala el tema padre **Hello Elementor** (Apariencia → Temas → Añadir nuevo → busca "Hello Elementor")
- Instala el plugin **Elementor** (gratis, suficiente para la fase under construction)

### 3. Activar el child theme

Apariencia → Temas → activar "Explora México Child"

### 4. Subir las imágenes

El theme espera dos archivos en `/wp-content/themes/explora-mexico-child/assets/images/`:

- **`logo.png`** — Logo de Explora México Tours (formato PNG con transparencia, alto recomendado 200-400px)
- **`hero-bg.png`** — Imagen de fondo difuminada que aparece detrás de todo (formato JPG o PNG, ancho recomendado 1920px, peso < 500KB)

Sube ambas vía FTP/SSH o desde **wp-admin → Apariencia → Editor de archivos del tema**.

Si quieres usar la imagen que ya subiste a la biblioteca de medios, copia la URL y edita la línea 18-19 de `template-under-construction.php`:

```php
$logo_url     = 'https://exploramexicotours.com/wp-content/uploads/.../logo.png';
$bg_image_url = 'https://exploramexicotours.com/wp-content/uploads/.../hero-bg.png';
```

### 5. Configurar el WhatsApp

Hay dos opciones:

**A) Edita directamente la constante** en `functions.php` línea ~50:
```php
$wa_number = get_option( 'emt_whatsapp_number', '523300000000' );
```
Reemplaza `523300000000` por el número real.

**B) Setea la opción desde wp-admin** (recomendado):
Ve a `wp-admin/options.php` (URL directa), busca `emt_whatsapp_number` y ponle el valor:
```
523312345678
```
(formato: código país + LADA + número, sin + ni espacios)

### 6. Verificar

- Visita `exploramexicotours.com` desde una ventana de incógnito → debes ver el under construction
- Visita el sitio logueado como admin → debes ver el sitio normal (puedes seguir trabajando)
- Menú lateral del admin: aparece **"Leads EMT"** con los correos capturados

---

## Lanzar el sitio real (desactivar under construction)

Cuando esté listo el sitio definitivo, edita `functions.php` y cambia:

```php
define( 'EMT_UNDER_CONSTRUCTION', true );
```

a:

```php
define( 'EMT_UNDER_CONSTRUCTION', false );
```

O simplemente elimina la línea. El sitio queda accesible para todos.

---

## Endpoints

- **Captura de leads:** `POST /wp-json/emt/v1/lead` con body `{ "email": "..." }`
- **Panel de leads:** `wp-admin/admin.php?page=emt-leads`
- **Exportar CSV:** botón en el panel de leads

---

## Stack y notas técnicas

- **Tokens CSS:** definidos en `style.css` con prefijo `--emt-*`
- **Scope:** las clases del under construction están scopeadas a `body.emt-under-construction` para que no contaminen el resto del sitio cuando construyamos las páginas reales
- **REST API:** la captura de leads usa el nonce de WP REST estándar, no hay endpoints públicos sin protección
- **Compatible con:** WordPress 6.x, PHP 7.4+
