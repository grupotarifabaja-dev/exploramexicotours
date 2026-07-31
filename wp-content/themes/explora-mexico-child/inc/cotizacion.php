<?php
/**
 * Página "Cotización de grupos" (/cotizacion/ y /en/cotizacion/ vía strip-prefix i18n).
 * Formulario robusto (doc maestro §8.6). Las solicitudes se guardan en la opción
 * `emt_cotizaciones` (sin autoload) y se notifican por email. Captura el asesor
 * referido desde la cookie `emt_ref_asesor` (atribución de ventas, ver asesor-functions).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- Routing ---------- */
add_filter( 'query_vars', function ( $v ) {
    $v[] = 'emt_cotizacion';
    return $v;
} );

add_action( 'init', function () {
    add_rewrite_rule( '^cotizacion/?$', 'index.php?emt_cotizacion=1', 'top' );
    if ( get_option( 'emt_cotiza_rw' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'emt_cotiza_rw', '1' );
    }
}, 11 );

add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'emt_cotizacion' ) ) {
        return;
    }
    status_header( 200 );
    include get_stylesheet_directory() . '/parts/cotizacion-page.php';
    exit;
}, 20 );

/* ---------- Envío del formulario ---------- */
add_action( 'wp_ajax_emt_cotizacion', 'emt_cotizacion_guardar' );
add_action( 'wp_ajax_nopriv_emt_cotizacion', 'emt_cotizacion_guardar' );
function emt_cotizacion_guardar() {
    if ( ! check_ajax_referer( 'emt_cotizacion', 'nonce', false ) ) {
        wp_send_json_error( array( 'msg' => 'Sesión no válida. Recarga la página.' ), 403 );
    }

    $nombre   = sanitize_text_field( wp_unslash( $_POST['nombre'] ?? '' ) );
    $email    = sanitize_email( wp_unslash( $_POST['correo'] ?? '' ) );
    $tel      = sanitize_text_field( wp_unslash( $_POST['telefono'] ?? '' ) );
    $personas = absint( $_POST['personas'] ?? 0 );

    if ( $nombre === '' || ! is_email( $email ) || $tel === '' || $personas < 1 ) {
        wp_send_json_error( array( 'msg' => 'Revisa los campos obligatorios.' ), 400 );
    }

    $tipos = array( 'grupo', 'mice', 'personalizado', 'otro' );
    $tipo  = sanitize_key( $_POST['tipo_viaje'] ?? 'grupo' );
    if ( ! in_array( $tipo, $tipos, true ) ) {
        $tipo = 'grupo';
    }

    $ref = isset( $_COOKIE['emt_ref_asesor'] ) ? sanitize_title( wp_unslash( $_COOKIE['emt_ref_asesor'] ) ) : '';

    $solicitud = array(
        'fecha_registro' => current_time( 'mysql' ),
        'nombre'         => $nombre,
        'correo'         => $email,
        'telefono'       => $tel,
        'tipo_viaje'     => $tipo,
        'personas'       => $personas,
        'fechas'         => sanitize_text_field( wp_unslash( $_POST['fechas'] ?? '' ) ),
        'interes'        => sanitize_text_field( wp_unslash( $_POST['interes'] ?? '' ) ),
        'detalles'       => sanitize_textarea_field( wp_unslash( $_POST['detalles'] ?? '' ) ),
        'ref_asesor'     => $ref,
        'ip'             => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
    );

    $todas   = (array) get_option( 'emt_cotizaciones', array() );
    $todas[] = $solicitud;
    if ( false === get_option( 'emt_cotizaciones', false ) ) {
        add_option( 'emt_cotizaciones', $todas, '', false );
    } else {
        update_option( 'emt_cotizaciones', $todas );
    }

    $to = get_option( 'admin_email' );
    wp_mail(
        $to,
        '[EMT] Nueva solicitud de cotización de grupo',
        "Nueva cotización:\n\nNombre: {$nombre}\nCorreo: {$email}\nWhatsApp: {$tel}\nTipo: {$tipo}\nPersonas: {$personas}\nFechas: {$solicitud['fechas']}\nInterés: {$solicitud['interes']}\nAsesor referido: " . ( $ref !== '' ? $ref : '—' ) . "\n\nDetalles:\n{$solicitud['detalles']}"
    );

    $lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
    wp_send_json_success( array(
        'msg' => ( $lang === 'en' )
            ? 'Request received! Our team will contact you shortly with a tailored proposal.'
            : '¡Solicitud recibida! Nuestro equipo te contactará en breve con una propuesta a la medida.',
    ) );
}
