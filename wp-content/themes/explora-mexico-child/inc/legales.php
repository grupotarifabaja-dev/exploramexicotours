<?php
/**
 * Páginas legales: rutas /aviso-de-privacidad/ y /terminos-y-condiciones/
 * (+ /en/... vía el strip-prefix de i18n). Contenido en parts/legal-page.php.
 * Mismo patrón que inc/nosotros.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'query_vars', function ( $v ) {
    $v[] = 'emt_legal';
    return $v;
} );

add_action( 'init', function () {
    add_rewrite_rule( '^aviso-de-privacidad/?$', 'index.php?emt_legal=privacidad', 'top' );
    add_rewrite_rule( '^terminos-y-condiciones/?$', 'index.php?emt_legal=terminos', 'top' );
    if ( get_option( 'emt_legal_rw' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'emt_legal_rw', '1' );
    }
}, 11 );

add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'emt_legal' ) ) {
        return;
    }
    status_header( 200 );
    include get_stylesheet_directory() . '/parts/legal-page.php';
    exit;
}, 20 );
