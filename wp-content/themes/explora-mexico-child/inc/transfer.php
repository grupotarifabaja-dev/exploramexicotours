<?php
/**
 * Sección "Explora Transfer" (transporte): página /transporte/ (+ /en/transporte/
 * vía el strip-prefix de i18n) y formulario de reservación.
 *
 * Las solicitudes se guardan en la opción `emt_transfer_solicitudes` (mismo
 * patrón que emt_leads) vía AJAX con nonce + sanitización. Por ahora solo BD.
 *
 * La página queda detrás del under construction como el resto del sitio.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- Routing ---------- */
add_filter( 'query_vars', function ( $v ) {
    $v[] = 'emt_transporte';
    return $v;
} );

add_action( 'init', function () {
    add_rewrite_rule( '^transporte/?$', 'index.php?emt_transporte=1', 'top' );
    if ( get_option( 'emt_transfer_rw' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'emt_transfer_rw', '1' );
    }
}, 11 );

/* Render de la página (después del UC: si el UC intercepta, aquí no se llega). */
add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'emt_transporte' ) ) {
        return;
    }
    status_header( 200 );
    include get_stylesheet_directory() . '/parts/transfer-page.php';
    exit;
}, 20 );

/* ---------- Solicitudes de reservación ---------- */
add_action( 'wp_ajax_emt_transfer_solicitud', 'emt_transfer_guardar_solicitud' );
add_action( 'wp_ajax_nopriv_emt_transfer_solicitud', 'emt_transfer_guardar_solicitud' );
function emt_transfer_guardar_solicitud() {
    if ( ! check_ajax_referer( 'emt_transfer', 'nonce', false ) ) {
        wp_send_json_error( array( 'msg' => 'Sesión no válida. Recarga la página.' ), 403 );
    }

    $nombre = sanitize_text_field( wp_unslash( $_POST['nombre'] ?? '' ) );
    $tel    = sanitize_text_field( wp_unslash( $_POST['telefono'] ?? '' ) );
    $email  = sanitize_email( wp_unslash( $_POST['correo'] ?? '' ) );
    $origen  = sanitize_text_field( wp_unslash( $_POST['origen'] ?? '' ) );
    $destino = sanitize_text_field( wp_unslash( $_POST['destino'] ?? '' ) );
    $salida  = sanitize_text_field( wp_unslash( $_POST['fecha_salida'] ?? '' ) );
    $adultos = absint( $_POST['adultos'] ?? 0 );

    if ( $nombre === '' || $tel === '' || ! is_email( $email ) || $origen === '' || $destino === '' || $salida === '' || $adultos < 1 ) {
        wp_send_json_error( array( 'msg' => 'Revisa los campos obligatorios.' ), 400 );
    }

    $tipos = array(
        'traslado', 'turistico', 'personal', 'escolar', 'por_horas',
        'aeropuerto', 'tours_locales', 'aeronaves', 'evento_social', 'otro',
    );
    $tipo = sanitize_key( $_POST['tipo_servicio'] ?? 'otro' );
    if ( ! in_array( $tipo, $tipos, true ) ) {
        $tipo = 'otro';
    }

    $extras_validos = array( 'baby_seat', 'bebidas', 'snacks' );
    $extras = array_values( array_intersect(
        array_map( 'sanitize_key', (array) ( $_POST['extras'] ?? array() ) ),
        $extras_validos
    ) );

    $vehiculo = sanitize_text_field( wp_unslash( $_POST['vehiculo'] ?? '' ) );

    $solicitud = array(
        'fecha_registro' => current_time( 'mysql' ),
        'nombre'         => $nombre,
        'telefono'       => $tel,
        'correo'         => $email,
        'empresa'        => sanitize_text_field( wp_unslash( $_POST['empresa'] ?? '' ) ),
        'tipo_servicio'  => $tipo,
        'vehiculo'       => $vehiculo,
        'origen'         => $origen,
        'destino'        => $destino,
        'fecha_salida'   => $salida,
        'fecha_retorno'  => sanitize_text_field( wp_unslash( $_POST['fecha_retorno'] ?? '' ) ),
        'itinerario'     => sanitize_textarea_field( wp_unslash( $_POST['itinerario'] ?? '' ) ),
        'adultos'        => $adultos,
        'menores'        => absint( $_POST['menores'] ?? 0 ),
        'extras'         => $extras,
        'solicitudes'    => sanitize_textarea_field( wp_unslash( $_POST['solicitudes'] ?? '' ) ),
        'ip'             => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
    );

    $todas   = (array) get_option( 'emt_transfer_solicitudes', array() );
    $todas[] = $solicitud;
    if ( false === get_option( 'emt_transfer_solicitudes', false ) ) {
        add_option( 'emt_transfer_solicitudes', $todas, '', false ); // sin autoload
    } else {
        update_option( 'emt_transfer_solicitudes', $todas );
    }

    // Notificación al equipo de Transfer.
    $admin_email = get_option( 'admin_email' );
    wp_mail(
        $admin_email,
        '[EMT Transfer] Nueva solicitud de reservación',
        "Nueva solicitud de transporte:\n\nNombre: {$nombre}\nTeléfono: {$tel}\nCorreo: {$email}\nServicio: {$tipo}\nVehículo de interés: " . ( $vehiculo !== '' ? $vehiculo : '—' ) . "\nOrigen: {$origen}\nDestino: {$destino}\nSalida: {$salida}\nPasajeros: {$adultos} adultos, {$solicitud['menores']} menores"
    );

    $lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
    wp_send_json_success( array(
        'msg' => ( $lang === 'en' )
            ? 'Request received! Our team will contact you shortly to confirm availability and pricing.'
            : '¡Solicitud recibida! Nuestro equipo te contactará en breve para confirmar disponibilidad y tarifa.',
    ) );
}
