# Imagen de despliegue de Explora México Tours (Opción A del pipeline).
#
# Estrategia: partimos de la imagen oficial de WordPress y "horneamos" nuestro
# child theme dentro de la imagen. Un entrypoint propio (docker-entrypoint-emt.sh)
# sincroniza el theme al webroot en cada arranque, respetando que el webroot suele
# estar montado sobre un VOLUMEN PERSISTENTE (uploads, wp-config, plugins, y en
# el primer arranque el core) que tapa los archivos de la imagen.
#
# Qué actualiza cada deploy: SOLO el child theme (nuestro código).
# Qué se preserva en el volumen: uploads, wp-config (generado por env), plugins, BD (servicio aparte).
#
# La versión de WordPress se fija por tag para builds reproducibles (evita que un
# "latest" cambie el core sin avisar). Ajustable al core que corra producción.
FROM wordpress:6.8-php8.3-apache

# Copia del child theme en DOS ubicaciones dentro de la imagen:
#  1) /usr/src/wordpress/... -> el entrypoint oficial copia /usr/src/wordpress a
#     /var/www/html en el PRIMER arranque (volumen vacío), llevándose el theme.
#  2) /opt/emt/theme/...     -> copia pristina para RE-sincronizar el theme en
#     cada arranque posterior (cuando el volumen ya está poblado).
COPY wp-content/themes/explora-mexico-child /usr/src/wordpress/wp-content/themes/explora-mexico-child
COPY wp-content/themes/explora-mexico-child /opt/emt/theme/explora-mexico-child

# Entrypoint propio que sincroniza el theme y delega en el oficial de WordPress.
COPY deploy/docker-entrypoint-emt.sh /usr/local/bin/docker-entrypoint-emt.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-emt.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint-emt.sh"]
CMD ["apache2-foreground"]
