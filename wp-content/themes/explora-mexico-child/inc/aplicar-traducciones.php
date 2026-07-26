<?php
/**
 * Disparador TEMPORAL: aplica las traducciones al inglés de los tours.
 * Lee data/translations-en.json (dentro del tema, se despliega con el tema) y
 * rellena los campos _en de cada tour (por slug), alineando itinerario/incluye
 * con las filas en español ya guardadas (por índice).
 *
 * Uso (admin o con token): /?emt_aplicar_traducciones=emt-tr-2026
 *
 * QUITAR este archivo (y su require en functions.php) tras aplicarlo.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    if ( ! isset( $_GET['emt_aplicar_traducciones'] ) ) { return; }
    $token = 'emt-tr-2026';
    if ( ! current_user_can( 'manage_options' ) && ! hash_equals( $token, (string) $_GET['emt_aplicar_traducciones'] ) ) {
        status_header( 403 ); header( 'Content-Type: text/plain; charset=utf-8' ); echo 'token invalido'; exit;
    }
    header( 'Content-Type: application/json; charset=utf-8' );
    @set_time_limit( 0 );

    $file = get_stylesheet_directory() . '/data/translations-en.json';
    if ( ! file_exists( $file ) ) {
        status_header( 500 ); echo wp_json_encode( array( 'error' => 'archivo de traducciones no encontrado', 'buscado' => $file ) ); exit;
    }
    $data = json_decode( (string) file_get_contents( $file ), true );
    if ( ! is_array( $data ) ) {
        status_header( 500 ); echo wp_json_encode( array( 'error' => 'JSON de traducciones invalido' ) ); exit;
    }

    $out = array();
    foreach ( $data as $slug => $tr ) {
        $post = get_page_by_path( $slug, OBJECT, 'tour' );
        if ( ! $post ) { $out[] = array( 'slug' => $slug, 'accion' => 'NO ENCONTRADO' ); continue; }
        $pid = (int) $post->ID;

        // Textos simples.
        update_field( 'titulo_en', (string) ( $tr['titulo_en'] ?? '' ), $pid );
        update_field( 'descripcion_en', (string) ( $tr['descripcion_en'] ?? '' ), $pid );
        update_field( 'excerpt_en', (string) ( $tr['excerpt_en'] ?? '' ), $pid );
        update_field( 'duracion_texto_en', (string) ( $tr['duracion_texto_en'] ?? '' ), $pid );
        update_field( 'fecha_viaje_en', (string) ( $tr['fecha_viaje_en'] ?? '' ), $pid );
        update_field( 'politica_cancelacion_en', (string) ( $tr['politica_cancelacion_en'] ?? '' ), $pid );
        update_field( 'precio_nota_en', (string) ( $tr['precio_nota_en'] ?? '' ), $pid );

        // Incluye (EN): espeja el icono de la fila ES + texto en inglés por índice.
        $inc    = (array) get_field( 'incluye', $pid );
        $inc_tr = (array) ( $tr['incluye_en'] ?? array() );
        $inc_en = array();
        foreach ( array_values( $inc ) as $i => $row ) {
            $inc_en[] = array(
                'icono' => is_array( $row ) ? ( $row['icono'] ?? 'otro' ) : 'otro',
                'texto' => (string) ( $inc_tr[ $i ] ?? ( is_array( $row ) ? ( $row['texto'] ?? '' ) : '' ) ),
            );
        }
        update_field( 'incluye_en', $inc_en, $pid );

        // No incluye (EN).
        $ninc    = (array) get_field( 'no_incluye', $pid );
        $ninc_tr = (array) ( $tr['no_incluye_en'] ?? array() );
        $ninc_en = array();
        foreach ( array_values( $ninc ) as $i => $row ) {
            $ninc_en[] = array(
                'icono' => is_array( $row ) ? ( $row['icono'] ?? 'otro' ) : 'otro',
                'texto' => (string) ( $ninc_tr[ $i ] ?? ( is_array( $row ) ? ( $row['texto'] ?? '' ) : '' ) ),
            );
        }
        update_field( 'no_incluye_en', $ninc_en, $pid );

        // Itinerario (EN): copia dia/hora/icono de la fila ES + titulo/descripcion en inglés por índice.
        $it    = (array) get_field( 'itinerario', $pid );
        $it_tr = (array) ( $tr['itinerario_en'] ?? array() );
        $it_en = array();
        foreach ( array_values( $it ) as $i => $row ) {
            $row = is_array( $row ) ? $row : array();
            $it_en[] = array(
                'dia'            => $row['dia'] ?? '',
                'hora'           => $row['hora'] ?? '',
                'titulo_en'      => (string) ( $it_tr[ $i ]['titulo_en'] ?? ( $row['titulo'] ?? '' ) ),
                'descripcion_en' => (string) ( $it_tr[ $i ]['descripcion_en'] ?? ( $row['descripcion'] ?? '' ) ),
                'icono'          => $row['icono'] ?? 'actividad',
            );
        }
        update_field( 'itinerario_en', $it_en, $pid );

        $out[] = array(
            'slug' => $slug, 'id' => $pid, 'accion' => 'traducido',
            'incluye' => count( $inc_en ), 'no_incluye' => count( $ninc_en ), 'itinerario' => count( $it_en ),
        );
    }

    echo wp_json_encode( array( 'ok' => true, 'total' => count( $out ), 'tours' => $out ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    exit;
}, 20 );
