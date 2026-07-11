# Pipeline de despliegue — Explora México Tours (Opción A)

Despliegue **GitHub → Coolify** con imagen custom. El child theme se hornea en la
imagen y un entrypoint lo sincroniza al webroot en cada arranque; **uploads,
wp-config, plugins y la base de datos se preservan** en el volumen / servicio de BD.

- `dev` → **staging** (`staging.exploramexicotours.com`)
- `main` → **producción** (`exploramexicotours.com`) — *solo tras validar en staging*

## Artefactos (en el repo)
- `Dockerfile` — `FROM wordpress:6.8-php8.3-apache` + child theme + entrypoint.
- `deploy/docker-entrypoint-emt.sh` — sincroniza el theme al webroot y delega en el entrypoint oficial de WP.
- `.dockerignore` — mantiene el contexto de build pequeño.

## Cómo funciona (respeta el volumen)
1. **Primer arranque** (volumen vacío): el entrypoint oficial de WordPress copia
   `/usr/src/wordpress` → `/var/www/html` (incluyendo nuestro theme) y genera
   `wp-config.php` desde las variables `WORDPRESS_DB_*`.
2. **Cada re-deploy** (volumen ya poblado): nuestro entrypoint reemplaza SOLO
   `wp-content/themes/explora-mexico-child` con la copia de la imagen; el resto
   (uploads, wp-config, plugins) queda intacto.

## Configuración en Coolify (staging)

> Crear como recurso **nuevo y aislado**, sin compartir nada con producción.

1. **Proyecto / entorno**: usar un entorno `staging` (no `production`).
2. **Base de datos**: crear un servicio **MariaDB** propio de staging (su volumen).
3. **Aplicación** (tipo *Dockerfile* / *Application*, conectada a este repo):
   - **Branch**: `dev`.
   - **Build**: Dockerfile en la raíz del repo (`./Dockerfile`).
   - **Puerto expuesto**: `80`.
   - **Variables de entorno**:
     - `WORDPRESS_DB_HOST` = host del MariaDB de staging
     - `WORDPRESS_DB_NAME`, `WORDPRESS_DB_USER`, `WORDPRESS_DB_PASSWORD`
     - `WORDPRESS_TABLE_PREFIX` = `wp_`
     - `WORDPRESS_DEBUG` = `1` (en staging; en prod, vacío)
   - **Almacenamiento persistente**: volumen montado en **`/var/www/html`**
     (preserva uploads, wp-config, plugins y core entre redeploys).
   - **Dominio**: `https://staging.exploramexicotours.com`.
   - **Auto-deploy**: activar en push a `dev`.
4. **Protección de staging** (no público / no indexable):
   - **Basic Auth** en el proxy (middleware Traefik de Coolify) para todo el sitio.
   - `blog_public = 0` (Ajustes → Lectura: desalentar indexación) tras el primer deploy.
   - El **under construction sigue activo** (el theme lleva `EMT_UNDER_CONSTRUCTION = true`).

## Dependencias a instalar en staging (una vez; persisten en el volumen)
- **ACF Pro** (subir el .zip + activar licencia). **Requerido** por el theme.
- **Hello Elementor** (theme padre). Requerido.
- Elementor / Code Snippets: **evaluar** — el theme es PHP puro; a priori **no** se necesitan.

## Producción (cuando se apruebe, Bloque posterior)
Mismo Dockerfile, branch `main`, entorno `production`, **reusando el volumen
persistente existente de producción** (el nombre del volumen y los IDs del recurso
NO se documentan en el repo por seguridad; están en las notas de ops / memoria).
Requiere migrar el recurso actual de *Service* (compose one-click) a *Application*
(Dockerfile) — operación delicada: se valida antes en staging y se hace con el
backup a mano.

## Rollback
- **Coolify**: redeploy del commit/imagen anterior (la app guarda el historial).
- **Datos**: el volumen y la BD no se tocan en un redeploy de código; para datos,
  restaurar desde backup (`/root/emt-backups/…`).

## Verificación post-deploy (staging)
- `https://staging.exploramexicotours.com` responde (tras Basic Auth) y sirve el UC.
- Como admin logueado: theme `explora-mexico-child` activo, `get_field` disponible
  (ACF Pro), CPT `tour`/`asesor` registrados, panel `/panel/` accesible.
