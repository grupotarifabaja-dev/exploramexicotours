<?php
/**
 * Ruta propia del listado del blog: /blog/ (+ /en/blog/ vía strip-prefix i18n) y
 * /blog/page/N/. Se sirve como las demás páginas custom (cotización, contacto),
 * SIN usar la "página de entradas" de WordPress — así la home (/, /en/) sigue
 * siendo front-page.php y no se rompe la home en inglés.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'query_vars', function ( $v ) {
    $v[] = 'emt_blog_list';
    $v[] = 'emt_blog_pag';
    return $v;
} );

add_action( 'init', function () {
    add_rewrite_rule( '^blog/page/([0-9]+)/?$', 'index.php?emt_blog_list=1&emt_blog_pag=$matches[1]', 'top' );
    add_rewrite_rule( '^blog/?$', 'index.php?emt_blog_list=1', 'top' );
    if ( get_option( 'emt_blog_route_rw' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'emt_blog_route_rw', '1' );
    }
}, 11 );

add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'emt_blog_list' ) ) {
        return;
    }
    status_header( 200 );
    include get_stylesheet_directory() . '/parts/blog-listing-page.php';
    exit;
}, 20 );
