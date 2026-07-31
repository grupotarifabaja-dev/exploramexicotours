<?php
/**
 * Encuesta de evaluación post-servicio: ruta /evaluacion/, CPT oculto
 * `evaluacion` para guardar respuestas, y guardado vía AJAX.
 *
 * Flujo: el cliente califica su experiencia; si fue buena (4-5 estrellas)
 * se le invita a dejar una reseña en Google (link directo con Place ID);
 * si no, solo se agradece. Resultados visibles en Panel > Evaluaciones.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Link directo para dejar reseña en Google (Place ID de Explora México Tours). */
function emt_google_review_link() {
    $link = get_option( 'emt_google_review_link' );
    return $link ? $link : 'https://search.google.com/local/writereview?placeid=ChIJQ7jZQuOtKIQRbFUAVpUwtbA';
}

/** CPT oculto para las respuestas. */
add_action( 'init', function () {
    register_post_type( 'evaluacion', array(
        'labels'          => array( 'name' => 'Evaluaciones', 'singular_name' => 'Evaluación' ),
        'public'          => false,
        'show_ui'         => false,
        'supports'        => array( 'title' ),
        'capability_type' => 'post',
    ) );
}, 6 );

/** Ruta /evaluacion/ (+ /en/evaluacion/ vía strip-prefix). */
add_filter( 'query_vars', function ( $v ) {
    $v[] = 'emt_evaluacion';
    return $v;
} );

add_action( 'init', function () {
    add_rewrite_rule( '^evaluacion/?$', 'index.php?emt_evaluacion=1', 'top' );
    if ( get_option( 'emt_evaluacion_rw' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'emt_evaluacion_rw', '1' );
    }
}, 11 );

add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'emt_evaluacion' ) ) {
        return;
    }
    status_header( 200 );
    include get_stylesheet_directory() . '/parts/evaluacion-page.php';
    exit;
}, 20 );

/** Guardado de la respuesta (AJAX, público, con nonce). */
function emt_evaluacion_guardar() {
    if ( ! check_ajax_referer( 'emt_evaluacion', 'nonce', false ) ) {
        wp_send_json_error( array( 'msg' => 'Sesión inválida, recarga la página.' ), 403 );
    }

    $calif  = min( 5, max( 1, (int) ( $_POST['calif_general'] ?? 0 ) ) );
    $calif_a = min( 5, max( 0, (int) ( $_POST['calif_asesor'] ?? 0 ) ) );
    if ( empty( $_POST['calif_general'] ) ) {
        wp_send_json_error( array( 'msg' => 'Falta la calificación general.' ), 400 );
    }

    $asesor_id = (int) ( $_POST['asesor_id'] ?? 0 );
    if ( $asesor_id && get_post_type( $asesor_id ) !== 'asesor' ) { $asesor_id = 0; }

    $en = function ( $k, $lista ) {
        $v = sanitize_key( $_POST[ $k ] ?? '' );
        return in_array( $v, $lista, true ) ? $v : '';
    };

    $datos = array(
        'calif_general' => $calif,
        'calif_asesor'  => $calif_a,
        'asesor_id'     => $asesor_id,
        'servicio'      => $en( 'servicio', array( 'tour', 'transporte', 'cotizacion' ) ),
        'resolvimos'    => $en( 'resolvimos', array( 'si', 'no' ) ),
        'recomienda'    => $en( 'recomienda', array( 'si', 'tal_vez', 'no' ) ),
        'canal'         => $en( 'canal', array( 'facebook', 'instagram', 'whatsapp', 'web', 'recomendacion', 'otro' ) ),
        'comentario'    => sanitize_textarea_field( wp_unslash( $_POST['comentario'] ?? '' ) ),
        'nombre'        => sanitize_text_field( wp_unslash( $_POST['nombre'] ?? '' ) ),
        'whatsapp'      => preg_replace( '/\D/', '', (string) ( $_POST['whatsapp'] ?? '' ) ),
    );

    $asesor_nom = $asesor_id ? get_the_title( $asesor_id ) : 'Sin asesor';
    $post_id = wp_insert_post( array(
        'post_type'   => 'evaluacion',
        'post_status' => 'publish',
        'post_title'  => sprintf( 'Evaluación %s · %d★ · %s', current_time( 'Y-m-d H:i' ), $calif, $asesor_nom ),
    ), true );
    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( array( 'msg' => 'No se pudo guardar, intenta de nuevo.' ), 500 );
    }
    foreach ( $datos as $k => $v ) {
        update_post_meta( $post_id, 'emt_' . $k, $v );
    }

    // Estadísticas del panel.
    if ( function_exists( 'emt_stats_bump' ) ) {
        emt_stats_bump( 'evaluaciones' );
        if ( $calif >= 4 ) { emt_stats_bump( 'evaluaciones_buenas' ); }
    }

    // Rama mágica: buena experiencia -> invitar a reseñar en Google.
    wp_send_json_success( array(
        'buena'  => ( $calif >= 4 ),
        'review' => ( $calif >= 4 ) ? emt_google_review_link() : '',
    ) );
}
add_action( 'wp_ajax_emt_evaluacion', 'emt_evaluacion_guardar' );
add_action( 'wp_ajax_nopriv_emt_evaluacion', 'emt_evaluacion_guardar' );
