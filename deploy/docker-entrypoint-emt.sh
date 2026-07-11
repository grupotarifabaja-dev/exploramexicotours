#!/bin/bash
# Entrypoint de Explora México Tours.
#
# Refresca el child theme desde la imagen hacia el webroot (que normalmente vive
# en un volumen persistente que tapa los archivos de la imagen) y luego delega en
# el entrypoint oficial de WordPress (que puebla el core en el primer arranque y
# genera wp-config desde las variables WORDPRESS_DB_*).
#
# Idempotente y seguro: solo toca el directorio del child theme; no toca uploads,
# wp-config, plugins ni la base de datos.
set -euo pipefail

WEBROOT="/var/www/html"
THEME_SRC="/opt/emt/theme/explora-mexico-child"
THEME_DEST="${WEBROOT}/wp-content/themes/explora-mexico-child"

if [ -f "${WEBROOT}/wp-load.php" ] && [ -d "${THEME_SRC}" ]; then
  # Re-arranque: el webroot ya está poblado -> actualizamos el theme desde la imagen.
  echo "[emt-entrypoint] Actualizando child theme en el webroot…"
  mkdir -p "$(dirname "${THEME_DEST}")"
  rm -rf "${THEME_DEST}"
  cp -a "${THEME_SRC}" "${THEME_DEST}"
  chown -R www-data:www-data "${THEME_DEST}"
else
  # Primer arranque: el entrypoint oficial copiará /usr/src/wordpress (con el theme)
  # al webroot; aquí no hacemos nada todavía.
  echo "[emt-entrypoint] Primer arranque: el core (con el theme) lo instala WordPress."
fi

# Delegar en el entrypoint oficial de WordPress (setup de core + wp-config + CMD).
exec docker-entrypoint.sh "$@"
