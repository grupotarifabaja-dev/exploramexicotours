<?php
/**
 * MODO UNDER CONSTRUCTION / PROTECCIÓN DE STAGING
 *
 * PRODUCCIÓN (exploramexicotours.com): pública desde el lanzamiento
 * (2026-07-31). Para una ventana de mantenimiento futura, forzar true abajo.
 *
 * STAGING (host que empieza con "staging."): es ambiente interno de pruebas.
 * Los visitantes anónimos ven la plantilla under-construction y NUNCA el
 * sitio de prueba; cualquier usuario logueado (admin o gestor) lo ve normal
 * para poder revisar. Además, staging siempre manda cabecera y meta noindex
 * para que los buscadores no lo indexen ni compita con producción.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** True si esta petición llegó por el dominio de staging. */
function emt_is_staging_host() {
    $host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : '';
    return strpos( $host, 'staging.' ) === 0;
}

// Candado por dominio: staging oculto para anónimos; producción pública.
define( 'EMT_UNDER_CONSTRUCTION', emt_is_staging_host() );

add_action( 'template_redirect', function() {
    if ( ! defined( 'EMT_UNDER_CONSTRUCTION' ) || ! EMT_UNDER_CONSTRUCTION ) return;

    // Cualquier usuario logueado (admin o gestor) ve el sitio para revisar.
    if ( is_user_logged_in() ) return;

    // Permitir wp-admin, wp-login y AJAX
    if ( is_admin() ) return;
    if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false ) return;
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

    // Servir la plantilla under-construction
    $template = get_stylesheet_directory() . '/template-under-construction.php';
    if ( file_exists( $template ) ) {
        status_header( 200 );
        include $template;
        exit;
    }
});

/* Staging jamás se indexa (aplica a toda respuesta, logueado o no). */
add_action( 'send_headers', function () {
    if ( emt_is_staging_host() ) {
        header( 'X-Robots-Tag: noindex, nofollow', true );
    }
} );
add_action( 'wp_head', function () {
    if ( emt_is_staging_host() ) {
        echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
    }
}, 0 );
add_filter( 'robots_txt', function ( $output ) {
    if ( emt_is_staging_host() ) {
        return "User-agent: *\nDisallow: /\n";
    }
    return $output;
}, 99 );
