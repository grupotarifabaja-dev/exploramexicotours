# Coolify por API — creación del entorno de staging (validado 2026-07-11)

Registro reproducible de las llamadas usadas para crear **staging** vía la API de
Coolify (v4.1.2), para repetir el proceso en producción u otro entorno.

> **SANITIZADO.** Sin token, IPs, credenciales ni UUIDs reales. Los valores entre
> `<>` se descubren con las llamadas de la sección 1 o viven en las notas de ops.
> El token se guarda **fuera del repo** (archivo local `chmod 600`); las llamadas
> se ejecutan desde el propio server contra `http://localhost:8000` (la API no
> está expuesta al exterior) con:
>
> ```bash
> H="Authorization: Bearer $(cat /root/.emt-coolify-token)"
> A=http://localhost:8000/api/v1
> ```

## 0. Prerrequisitos
- Token API de Coolify (Keys & Tokens → API tokens; permisos read+write+deploy).
- **GitHub App** conectada en Coolify (Sources → GitHub App) con el repo privado
  autorizado — necesaria porque `applications/public` no sirve para repos privados.
- DNS del dominio del entorno apuntando al server ANTES del deploy (Let's Encrypt).
- El repo debe contener `Dockerfile` + `deploy/docker-entrypoint-emt.sh` en la rama a desplegar.

## 1. Descubrimiento (UUIDs)
```bash
curl -s -H "$H" $A/version                 # sanity: debe responder la versión
curl -s -H "$H" $A/projects                # -> <PROJECT_UUID>
curl -s -H "$H" $A/servers                 # -> <SERVER_UUID>
# GitHub App uuid (no hay endpoint GET): de la BD de coolify
docker exec coolify-db psql -U coolify -d coolify -t -A \
  -c "SELECT uuid, name FROM github_apps"  # -> <GITHUB_APP_UUID>
```

## 2. Entorno
```bash
curl -s -X POST -H "$H" -H "Content-Type: application/json" \
  -d '{"name":"staging"}' $A/projects/<PROJECT_UUID>/environments
# -> devuelve <ENV_UUID>. Verificar: GET $A/projects/<PROJECT_UUID>
```

## 3. MariaDB (aislada, del entorno)
```bash
curl -s -X POST -H "$H" -H "Content-Type: application/json" -d '{
  "server_uuid":"<SERVER_UUID>", "project_uuid":"<PROJECT_UUID>",
  "environment_uuid":"<ENV_UUID>", "name":"mariadb-staging-emt",
  "mariadb_database":"wordpress", "image":"mariadb:11", "instant_deploy":true
}' $A/databases/mariadb
# -> <DB_UUID>. Credenciales luego vía GET $A/databases/<DB_UUID>
#    (campos mariadb_user / mariadb_password / mariadb_database; host = <DB_UUID>).
```

## 4. Application (repo privado vía GitHub App)
```bash
curl -s -X POST -H "$H" -H "Content-Type: application/json" -d '{
  "project_uuid":"<PROJECT_UUID>", "server_uuid":"<SERVER_UUID>",
  "environment_uuid":"<ENV_UUID>", "github_app_uuid":"<GITHUB_APP_UUID>",
  "git_repository":"grupotarifabaja-dev/exploramexicotours",
  "git_branch":"dev", "build_pack":"dockerfile", "ports_exposes":"80",
  "name":"wordpress-staging-emt",
  "domains":"https://staging.exploramexicotours.com",
  "instant_deploy":false
}' $A/applications/private-github-app
# -> <APP_UUID>. dockerfile_location por defecto = /Dockerfile (raíz del repo).
```

## 5. Variables de entorno
`POST $A/applications/<APP_UUID>/envs` (una por una), body:
`{"key":"…","value":"…","is_preview":false}` con:

| Key | Valor |
|---|---|
| `WORDPRESS_DB_HOST` | `<DB_UUID>` (hostname interno del contenedor de BD) |
| `WORDPRESS_DB_NAME` | `wordpress` |
| `WORDPRESS_DB_USER` / `WORDPRESS_DB_PASSWORD` | de `GET /databases/<DB_UUID>` |
| `WORDPRESS_TABLE_PREFIX` | `wp_` |
| `WORDPRESS_DEBUG` | `1` en staging, vacío en producción |

Nota: Coolify crea automáticamente una variante `is_preview=true` de cada variable
(para preview-deployments de PR). Es normal; no son duplicados.

## 6. Volumen persistente (preserva uploads/wp-config/plugins entre deploys)
```bash
curl -s -X POST -H "$H" -H "Content-Type: application/json" -d '{
  "type":"persistent", "name":"wordpress-files-staging", "mount_path":"/var/www/html"
}' $A/applications/<APP_UUID>/storages
# type válido: persistent | file. Verificar: GET $A/applications/<APP_UUID>/storages
```

## 7. Deploy y monitoreo
```bash
curl -s -X POST -H "$H" "$A/deploy?uuid=<APP_UUID>"   # -> <DEPLOYMENT_UUID>
curl -s -H "$H" $A/deployments/<DEPLOYMENT_UUID>       # status: queued|in_progress|finished|failed
```

## 8. Verificación post-deploy (lo que se validó en staging)
- `docker ps` → contenedor `<APP_UUID>-…` corriendo imagen `<APP_UUID>:<git-sha>`.
- Logs de arranque: `[emt-entrypoint]` + WordPress copiando core y generando
  `wp-config.php` desde las env vars (primer arranque de volumen vacío).
- Theme completo dentro del webroot (`wp-content/themes/explora-mexico-child/inc/…`).
- Volumen del entorno separado del de producción (`docker volume ls`).
- Aislamiento en la BD de Coolify: app y BD del entorno correcto:
  `SELECT a.name, e.name FROM applications a JOIN environments e ON e.id=a.environment_id;`
- HTTPS del dominio responde (redirige a `/wp-admin/install.php` con BD vacía).
- Producción intacta (contenedores sin reinicio, sitio público sirviendo su contenido).

## Pendientes post-creación (una vez, viven en el volumen)
1. Completar el instalador de WP (o restaurar una BD).
2. Instalar **Hello Elementor** (padre) + **ACF Pro** (zip + licencia) y activar
   el child `explora-mexico-child`.
3. `blog_public = 0` (staging no indexable) + **Basic Auth** en el proxy.
4. Confirmar auto-deploy por webhook (push a la rama → redeploy).

## Notas para PRODUCCIÓN (Bloque siguiente — NO ejecutado aún)
- Mismo procedimiento con `git_branch: main`, entorno `production`, y el detalle
  crítico: **reutilizar el volumen existente de producción** montado en
  `/var/www/html` (los nombres reales del volumen/recurso están en notas de ops,
  no en este repo). En re-arranques con webroot poblado, el entrypoint reemplaza
  SOLO el child theme; el resto del volumen no se toca.
- El Service one-click actual de WordPress se retira DESPUÉS de validar la nueva
  Application (con backup previo y ventana acordada).
