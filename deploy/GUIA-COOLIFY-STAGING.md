# Guía paso a paso — Crear STAGING en Coolify (UI)

Para seguir sin pensar. Objetivo: un WordPress de **staging aislado** que Coolify
construye desde el repo (rama `dev`) con nuestro `Dockerfile`, con su propia BD y
volumen, protegido y con el under construction activo. **Producción no se toca.**

> ⚠️ Regla de oro en cada pantalla: verifica que estás en el **entorno `staging`**
> y en un recurso **NUEVO**. Nunca edites el recurso `explora-mexico-tours`
> (producción). Si una pantalla te ofrece "usar volumen/BD existente", di **NO**:
> siempre **crear nuevos**.

---

## PASO 0 — Prerrequisitos (antes de entrar a Coolify)
1. **DNS**: crear el registro (ver sección DNS al final). Espera a que resuelva a
   la IP del server antes del PASO 6 (el certificado HTTPS lo necesita).
2. **Repo en `dev` completo**: `dev` debe tener el código + el `Dockerfile`
   (merge de #8, #9 y del PR `chore/deploy-staging`).
3. **Conexión GitHub**: Coolify debe poder leer el repo privado
   `grupotarifabaja-dev/exploramexicotours`. En Coolify: **Sources** (o Settings →
   Git) → conectar la **GitHub App** de Coolify a la organización, o usar una
   **Deploy Key**. Si ya hay una fuente GitHub conectada, reutilízala.

---

## PASO 1 — Proyecto y entorno
1. Coolify → **Projects**. Puedes usar el proyecto existente (`hosting-supratecnia`)
   o crear uno nuevo `emt-staging`.
2. Dentro del proyecto, **Environments** → crea/usa un entorno llamado **`staging`**.
   (No uses `production`.)

## PASO 2 — Base de datos MariaDB (staging)
1. En el entorno `staging`: **+ New** → **Database** → **MariaDB** (versión 11,
   para igualar prod).
2. Nombre: `mariadb-staging`. Coolify genera usuario/DB/passwords.
3. **Deploy** la base de datos. Cuando esté "healthy", abre su pantalla y **copia**:
   - **Host interno** (algo tipo `mariadb-xxxxxxxx` — el hostname en la red de Coolify)
   - **Database**, **Username**, **Password**
   Los usarás en el PASO 4.
   > Nota: NO expongas la BD a internet (déjala solo en la red interna de Coolify).

## PASO 3 — Crear la Aplicación (¡Application, no Service!)
1. En el entorno `staging`: **+ New** → **Application** → fuente **Private
   Repository (GitHub App)** (o Public si tu App lo permite).
2. **Repository**: `grupotarifabaja-dev/exploramexicotours`.
3. **Branch**: **`dev`**.
4. **Build Pack**: selecciona **Dockerfile**.
   - **Dockerfile Location**: `/Dockerfile`
   - **Base Directory**: `/`
5. **Ports Exposes**: `80`.
6. Nombre del recurso: `wordpress-staging`. Guarda (Save). **Aún no despliegues.**

## PASO 4 — Variables de entorno (conectar la BD)
En la Application → pestaña **Environment Variables**, agrega (con los valores del PASO 2):
```
WORDPRESS_DB_HOST=<host interno del mariadb-staging>
WORDPRESS_DB_NAME=<database del PASO 2>
WORDPRESS_DB_USER=<username del PASO 2>
WORDPRESS_DB_PASSWORD=<password del PASO 2>
WORDPRESS_TABLE_PREFIX=wp_
WORDPRESS_DEBUG=1
```
Guarda.

## PASO 5 — Almacenamiento persistente (el volumen)
1. En la Application → **Storages** (o **Persistent Storage**) → **+ Add**.
2. Tipo: **Volume**. **Destination Path (en el contenedor)**: **`/var/www/html`**.
   Deja que Coolify **cree un volumen nuevo** (nombre auto). **NO** reutilices el
   volumen de producción.
3. Guarda.
   > Esto preserva uploads, wp-config, plugins y el core entre redeploys. Nuestro
   > entrypoint solo refresca el theme.

## PASO 6 — Dominio + HTTPS
1. En la Application → **Domains** (FQDN): `https://staging.exploramexicotours.com`.
2. Coolify pedirá el certificado (Let's Encrypt vía Traefik) automáticamente.
   **El DNS del PASO 0 debe estar resolviendo** o el cert falla (se puede reintentar).
3. Guarda.

## PASO 7 — Primer deploy
1. Pulsa **Deploy**. Coolify clona `dev`, construye la imagen con el `Dockerfile`
   y arranca el contenedor. Mira los **logs de build** (tarda unos minutos).
2. Cuando esté "healthy", abre `https://staging.exploramexicotours.com`.
3. **Verás el instalador de WordPress** (elige idioma → título del sitio → crea el
   **usuario admin** + contraseña + email). Complétalo. *(Es normal: WordPress se
   instala por primera vez sobre la BD vacía.)*

## PASO 8 — Dependencias (dentro de wp-admin de staging)
1. Entra a `https://staging.exploramexicotours.com/wp-admin`.
2. **Apariencia → Temas → Añadir nuevo** → busca **"Hello Elementor"** → **Instalar**
   (no hace falta activarlo; es el padre). *Sin el padre, el child no activa.*
3. **Plugins → Añadir nuevo → Subir plugin** → sube el **.zip de ACF Pro** →
   **Instalar** → **Activar** → pega la **licencia** de ACF.
4. **Apariencia → Temas** → activa **`Explora México Child`**.
5. *(Elementor / Code Snippets: NO instalar por ahora; el theme es PHP puro. Si
   algo lo pidiera, lo evaluamos.)*

## PASO 9 — Ajustes de staging (no público / no indexable)
1. **Ajustes → Lectura** → marca **"Disuade a los motores de búsqueda…"**
   (esto pone `blog_public = 0`). Guarda.
2. Verifica que el under construction está activo: abre el sitio **en incógnito
   (sin sesión)** → debe mostrar la página "Próximamente".

## PASO 10 — Protección con Basic Auth (contraseña del proxy)
Para que ni siquiera el "Próximamente" sea público en staging:
1. Genera un usuario/clave htpasswd. En cualquier terminal:
   `htpasswd -nbB staging 'TU_CLAVE'`  → copia la línea `staging:$2y$...`
   (o usa un generador htpasswd bcrypt online de confianza).
2. En la Application → **Advanced** → **Custom Labels** (etiquetas Traefik), agrega:
   ```
   traefik.http.routers.<ROUTER>.middlewares=staging-auth
   traefik.http.middlewares.staging-auth.basicauth.users=staging:$2y$...(hash)
   ```
   (Sustituye `<ROUTER>` por el nombre del router https que Coolify ya generó para
   este recurso; lo ves en las labels existentes.)
3. Redeploy. Ahora el sitio pide usuario/clave antes de cargar.
   > Si esto se complica, es opcional: el UC + `blog_public=0` + una URL no
   > difundida ya protegen bastante. Podemos afinar el Basic Auth después.

---

## Verificación final (me pasas capturas o me dices)
- El sitio carga tras Basic Auth y en incógnito muestra el **UC**.
- Como **admin logueado**: theme `Explora México Child` **activo**, y en
  `https://staging.exploramexicotours.com/wp-admin/` aparece el menú del panel.
- Prueba rápida de ACF/CPT: entra a `…/wp-admin/edit.php?post_type=tour` (debe
  existir el CPT Tours) y `…/wp-admin/edit.php?post_type=asesor`.
- (Opcional) corremos el **seeder de datos reales** para poblar staging.

## Qué puede salir mal (avisos)
- **Repo privado sin conexión GitHub** → el deploy falla al clonar. Resuelve el
  PASO 0.3 primero.
- **DNS aún no propaga** → el certificado HTTPS falla. Espera y reintenta el deploy.
- **Activaste el child sin el padre** → error "tema padre no encontrado". Instala
  Hello Elementor (PASO 8.2) primero.
- **Reusar volumen/BD de producción** → NUNCA. Si una pantalla lo sugiere, crea
  nuevos. (Es el único camino por el que staging podría tocar prod.)
- **Olvidar `blog_public=0`** → riesgo de indexación. PASO 9.
- **`WORDPRESS_DB_HOST` con el host equivocado** → "Error estableciendo conexión
  con la BD". Usa el host interno del `mariadb-staging` (PASO 2), no `localhost`.

## DNS exacto a crear
> La IP del servidor no se documenta en el repo por seguridad (es la misma a la
> que resuelve `exploramexicotours.com`; el equipo la tiene en las notas de ops).
```
Tipo:  A
Host:  staging            (→ staging.exploramexicotours.com)
Valor: <IP-DEL-SERVIDOR>   (la misma del server de producción)
TTL:   automático / 300
Proxy: si usas Cloudflare, ponlo en "DNS only" (nube gris) al menos hasta que
       Coolify emita el certificado; luego puedes activar el proxy.
```
