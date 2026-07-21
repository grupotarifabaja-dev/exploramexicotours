<?php
/**
 * Página "Nosotros / Quiénes Somos": ruta /nosotros/ (+ /en/nosotros/ vía el
 * strip-prefix de i18n). Contenido estático en parts/nosotros-page.php.
 * Queda detrás del under construction como el resto del sitio.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'query_vars', function ( $v ) {
    $v[] = 'emt_nosotros';
    return $v;
} );

add_action( 'init', function () {
    add_rewrite_rule( '^nosotros/?$', 'index.php?emt_nosotros=1', 'top' );
    if ( get_option( 'emt_nosotros_rw' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'emt_nosotros_rw', '1' );
    }
}, 11 );

add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'emt_nosotros' ) ) {
        return;
    }
    status_header( 200 );
    include get_stylesheet_directory() . '/parts/nosotros-page.php';
    exit;
}, 20 );
