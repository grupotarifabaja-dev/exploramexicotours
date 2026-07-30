<?php
/**
 * Disparador TEMPORAL: corrige el itinerario de "Recuerdo de México · Sesión
 * fotográfica" (estaba capturado en una sola línea; ahora una parada por horario).
 * Uso: /?emt_fix_itin=emt-itin-2026
 * QUITAR este archivo (y su require en functions.php) tras aplicarlo.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    if ( ! isset( $_GET['emt_fix_itin'] ) ) { return; }
    if ( ! current_user_can( 'manage_options' ) && ! hash_equals( 'emt-itin-2026', (string) $_GET['emt_fix_itin'] ) ) {
        status_header( 403 ); header( 'Content-Type: text/plain; charset=utf-8' ); echo 'token invalido'; exit;
    }
    header( 'Content-Type: application/json; charset=utf-8' );

    $post = get_page_by_path( 'recuerdo-de-mexico-sesion-fotografica', OBJECT, 'tour' );
    if ( ! $post ) { echo wp_json_encode( array( 'error' => 'tour no encontrado' ) ); exit; }

    $itin = array(
        array( 'dia' => 1, 'hora' => '13:30', 'icono' => 'salida', 'titulo' => 'Pick up de pasajeros', 'descripcion' => 'Recogida en el punto acordado para iniciar la experiencia.' ),
        array( 'dia' => 1, 'hora' => '14:30', 'icono' => 'actividad', 'titulo' => 'Showroom: atuendo, maquillaje y peinado', 'descripcion' => 'Arribo al showroom para la selección del atuendo tradicional, maquillaje y peinado.' ),
        array( 'dia' => 1, 'hora' => '16:00', 'icono' => 'parada', 'titulo' => 'Traslado al set de la sesión', 'descripcion' => 'Salida hacia el escenario elegido para la sesión fotográfica.' ),
        array( 'dia' => 1, 'hora' => '18:30', 'icono' => 'parada', 'titulo' => 'Regreso al showroom', 'descripcion' => 'Entrega del atuendo tradicional.' ),
        array( 'dia' => 1, 'hora' => '19:00', 'icono' => 'regreso', 'titulo' => 'Regreso al hotel', 'descripcion' => 'Traslado de regreso; dejada de pasajeros en su alojamiento alrededor de las 20:00 y fin de servicios.' ),
    );
    $itin_en = array(
        array( 'titulo_en' => 'Passenger pick-up', 'descripcion_en' => 'Pick-up at the agreed point to start the experience.' ),
        array( 'titulo_en' => 'Showroom: outfit, make-up and hairstyling', 'descripcion_en' => 'Arrival at the showroom to choose your traditional outfit, plus make-up and hairstyling.' ),
        array( 'titulo_en' => 'Transfer to the photo-session location', 'descripcion_en' => 'Departure to the chosen setting for the photo session.' ),
        array( 'titulo_en' => 'Back to the showroom', 'descripcion_en' => 'Return of the traditional outfit.' ),
        array( 'titulo_en' => 'Return to your hotel', 'descripcion_en' => 'Drop-off at your accommodation around 8:00 p.m.; end of services.' ),
    );
    update_field( 'itinerario', $itin, $post->ID );
    update_field( 'itinerario_en', $itin_en, $post->ID );

    echo wp_json_encode( array( 'ok' => true, 'tour' => $post->ID, 'paradas' => count( $itin ) ), JSON_UNESCAPED_UNICODE );
    exit;
}, 20 );
