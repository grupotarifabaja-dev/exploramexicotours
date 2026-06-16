<?php
/**
 * Hardening básico a nivel de THEME/código — doc maestro §11.1.
 *
 * Alcance: SOLO lo que corresponde al código del theme. El hardening de
 * INFRAESTRUCTURA es responsabilidad del servidor/plugins, NO de este theme:
 *   - 2FA, Wordfence/Solid Security, límite de intentos de login.
 *   - HSTS (Strict-Transport-Security): cabecera de servidor (Nginx/Cloudflare);
 *     no se pone aquí porque en local es http y rompería el acceso.
 *   - Backups, roles de usuario.
 *   - Constantes canónicas de wp-config: DISALLOW_FILE_MODS, FORCE_SSL_ADMIN,
 *     WP_AUTO_UPDATE_CORE.
 *
 * TODO — Content-Security-Policy: NO implementar aún. Requiere mapear todos los
 * recursos externos (Google Fonts, Peek, Cloudflare, GA4/GTM, Meta Pixel); un
 * CSP mal configurado rompe el sitio. Configurar cuando el sitio esté completo,
 * preferiblemente en servidor (Nginx/Cloudflare) o aquí con allowlist completa.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   1 + 3. Ocultar la versión de WordPress del HTML output
   ============================================================ */
remove_action( 'wp_head', 'wp_generator' );          // <meta name="generator">
add_filter( 'the_generator', '__return_empty_string' ); // feeds / otros contextos

// Quitar ?ver=<wp_version> de scripts/estilos del CORE (no de assets propios versionados).
function emt_strip_core_version_query( $src ) {
    if ( ! $src ) {
        return $src;
    }
    $query = wp_parse_url( $src, PHP_URL_QUERY );
    if ( ! $query ) {
        return $src;
    }
    parse_str( $query, $args );
    if ( isset( $args['ver'] ) && $args['ver'] === get_bloginfo( 'version' ) ) {
        $src = remove_query_arg( 'ver', $src ); // solo cuando el ver == versión de WP
    }
    return $src;
}
add_filter( 'style_loader_src', 'emt_strip_core_version_query', 9999 );
add_filter( 'script_loader_src', 'emt_strip_core_version_query', 9999 );

/* ============================================================
   2. Deshabilitar XML-RPC
   ============================================================ */
add_filter( 'xmlrpc_enabled', '__return_false' );

// Quitar la cabecera X-Pingback (anuncia xmlrpc.php).
add_filter( 'wp_headers', function( $headers ) {
    unset( $headers['X-Pingback'] );
    return $headers;
} );

/* ============================================================
   4. Headers de seguridad (vía PHP). CSP queda como TODO (ver arriba).
   ============================================================ */
add_action( 'send_headers', 'emt_security_headers' );
function emt_security_headers() {
    if ( headers_sent() ) {
        return;
    }
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    // TODO: header( 'Content-Security-Policy', ... ) — pendiente (mapear recursos externos).
}

/* ============================================================
   5. Deshabilitar edición de archivos desde el admin.
   El lugar canónico es wp-config.php; aquí se fuerza de forma defensiva
   solo si no está ya definida.
   ============================================================ */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}

/* ============================================================
   6. Quitar enlaces innecesarios del <head> que filtran información.
   ============================================================ */
remove_action( 'wp_head', 'rsd_link' );                        // Really Simple Discovery (xmlrpc)
remove_action( 'wp_head', 'wlwmanifest_link' );                // Windows Live Writer
remove_action( 'wp_head', 'wp_shortlink_wp_head' );            // shortlink ?p=
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' ); // rel prev/next
