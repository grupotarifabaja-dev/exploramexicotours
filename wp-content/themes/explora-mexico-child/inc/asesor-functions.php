<?php
/**
 * Funciones del CPT asesor (doc maestro §8.5):
 *   - Endpoint vCard: /asesores/{slug}/vcard  -> archivo .vcf descargable.
 *   - Atribución de ventas: ?ref={slug} -> cookie emt_ref_asesor (30 días).
 *
 * Funcionalidad sensible: la vCard expone datos de contacto del asesor; el ?ref
 * solo guarda el slug del asesor (no datos personales del visitante).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** URL de la vCard de un asesor. */
function emt_asesor_vcard_url( $post_id ) {
    return home_url( '/asesores/' . get_post_field( 'post_name', $post_id ) . '/vcard' );
}

/** Rewrite rule + query var del endpoint vCard. */
add_action( 'init', function () {
    add_rewrite_rule( '^asesores/([^/]+)/vcard/?$', 'index.php?post_type=asesor&name=$matches[1]&emt_vcard=1', 'top' );
} );
add_filter( 'query_vars', function ( $vars ) {
    $vars[] = 'emt_vcard';
    return $vars;
} );

/** Escapa un valor para vCard 3.0. */
function emt_vcard_escape( $v ) {
    return str_replace( array( '\\', ';', ',', "\n" ), array( '\\\\', '\\;', '\\,', '\\n' ), (string) $v );
}

/** Sirve el archivo .vcf cuando se solicita el endpoint vCard. */
add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'emt_vcard' ) ) {
        return;
    }
    $id = get_queried_object_id();
    if ( ! $id || get_post_type( $id ) !== 'asesor' ) {
        status_header( 404 );
        return;
    }

    $nombre = get_the_title( $id );
    $puesto = function_exists( 'get_field' ) ? (string) get_field( 'puesto', $id ) : '';
    $tel    = function_exists( 'get_field' ) ? (string) get_field( 'telefono', $id ) : '';
    $wa     = function_exists( 'get_field' ) ? preg_replace( '/\D/', '', (string) get_field( 'whatsapp', $id ) ) : '';
    $email  = function_exists( 'get_field' ) ? (string) get_field( 'email', $id ) : '';

    $lines   = array();
    $lines[] = 'BEGIN:VCARD';
    $lines[] = 'VERSION:3.0';
    $lines[] = 'FN:' . emt_vcard_escape( $nombre );
    $lines[] = 'N:' . emt_vcard_escape( $nombre ) . ';;;;';
    $lines[] = 'ORG:Explora Mexico Tours';
    if ( $puesto ) { $lines[] = 'TITLE:' . emt_vcard_escape( $puesto ); }
    if ( $tel )    { $lines[] = 'TEL;TYPE=WORK,VOICE:' . emt_vcard_escape( $tel ); }
    if ( $wa )     { $lines[] = 'TEL;TYPE=CELL:+' . $wa; }
    if ( $email )  { $lines[] = 'EMAIL;TYPE=WORK:' . emt_vcard_escape( $email ); }
    $lines[] = 'URL:' . get_permalink( $id );
    $lines[] = 'END:VCARD';
    $vcf = implode( "\r\n", $lines ) . "\r\n";

    // Contador de descargas (tracking).
    $n = (int) get_post_meta( $id, '_emt_vcard_downloads', true );
    update_post_meta( $id, '_emt_vcard_downloads', $n + 1 );

    nocache_headers();
    header( 'Content-Type: text/vcard; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $nombre ) . '.vcf"' );
    echo $vcf;
    exit;
} );

/** Atribución: guarda ?ref={slug} en cookie por 30 días. */
add_action( 'init', function () {
    if ( empty( $_GET['ref'] ) || headers_sent() ) {
        return;
    }
    $ref = sanitize_title( wp_unslash( $_GET['ref'] ) );
    if ( $ref ) {
        setcookie( 'emt_ref_asesor', $ref, time() + 30 * DAY_IN_SECONDS, defined( 'COOKIEPATH' ) ? COOKIEPATH : '/', defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '' );
    }
} );
