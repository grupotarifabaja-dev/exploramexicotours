<?php
/**
 * Página de Contacto (/contacto/ y /en/contacto/ vía strip-prefix de i18n).
 * Datos de contacto desde la options page (emt-config) + bloque corporativo de
 * Explora México Tours (quiénes somos, clientes, certificaciones) — contenido
 * que aplica a toda la empresa, no solo a Transfer.
 *
 * Queda detrás del under construction como el resto del sitio.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'query_vars', function ( $v ) {
    $v[] = 'emt_contacto';
    return $v;
} );

add_action( 'init', function () {
    add_rewrite_rule( '^contacto/?$', 'index.php?emt_contacto=1', 'top' );
    if ( get_option( 'emt_contacto_rw' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'emt_contacto_rw', '1' );
    }
}, 11 );

add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'emt_contacto' ) ) {
        return;
    }
    status_header( 200 );
    include get_stylesheet_directory() . '/parts/contacto-page.php';
    exit;
}, 20 );
