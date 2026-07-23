<?php
/**
 * Página "Contacto" (/contacto/ y /en/contacto/ vía strip-prefix i18n).
 * Datos de contacto + formulario general. Las solicitudes se guardan en la
 * opción `emt_contactos` (sin autoload) y se notifican por email.
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

add_action( 'wp_ajax_emt_contacto', 'emt_contacto_guardar' );
add_action( 'wp_ajax_nopriv_emt_contacto', 'emt_contacto_guardar' );
function emt_contacto_guardar() {
    if ( ! check_ajax_referer( 'emt_contacto', 'nonce', false ) ) {
        wp_send_json_error( array( 'msg' => 'Sesión no válida. Recarga la página.' ), 403 );
    }

    $nombre  = sanitize_text_field( wp_unslash( $_POST['nombre'] ?? '' ) );
    $email   = sanitize_email( wp_unslash( $_POST['correo'] ?? '' ) );
    $mensaje = sanitize_textarea_field( wp_unslash( $_POST['mensaje'] ?? '' ) );

    if ( $nombre === '' || ! is_email( $email ) || $mensaje === '' ) {
        wp_send_json_error( array( 'msg' => 'Revisa los campos obligatorios.' ), 400 );
    }

    $solicitud = array(
        'fecha_registro' => current_time( 'mysql' ),
        'nombre'         => $nombre,
        'correo'         => $email,
        'telefono'       => sanitize_text_field( wp_unslash( $_POST['telefono'] ?? '' ) ),
        'asunto'         => sanitize_text_field( wp_unslash( $_POST['asunto'] ?? '' ) ),
        'mensaje'        => $mensaje,
        'ip'             => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
    );

    $todas   = (array) get_option( 'emt_contactos', array() );
    $todas[] = $solicitud;
    if ( false === get_option( 'emt_contactos', false ) ) {
        add_option( 'emt_contactos', $todas, '', false );
    } else {
        update_option( 'emt_contactos', $todas );
    }

    wp_mail(
        get_option( 'admin_email' ),
        '[EMT] Nuevo mensaje de contacto',
        "Nuevo mensaje desde el sitio:\n\nNombre: {$nombre}\nCorreo: {$email}\nTeléfono: {$solicitud['telefono']}\nAsunto: {$solicitud['asunto']}\n\nMensaje:\n{$mensaje}"
    );

    $lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
    wp_send_json_success( array(
        'msg' => ( $lang === 'en' )
            ? 'Message sent! We will get back to you soon.'
            : '¡Mensaje enviado! Te responderemos muy pronto.',
    ) );
}
