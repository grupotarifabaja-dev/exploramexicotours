<?php
/**
 * Disparador TEMPORAL: importa el video del hero (horneado en el tema en
 * assets/video/) a la biblioteca de medios y lo configura como fondo del
 * hero de portada (option hero_bg_video). Idempotente: no re-importa.
 *
 * Uso: /?emt_hero_video=emt-hero-2026
 * QUITAR este archivo (y su require en functions.php) tras aplicarlo.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    if ( ! isset( $_GET['emt_hero_video'] ) ) { return; }
    if ( ! current_user_can( 'manage_options' ) && ! hash_equals( 'emt-hero-2026', (string) $_GET['emt_hero_video'] ) ) {
        status_header( 403 ); header( 'Content-Type: text/plain; charset=utf-8' ); echo 'token invalido'; exit;
    }
    header( 'Content-Type: application/json; charset=utf-8' );
    @set_time_limit( 0 );

    $archivo = 'explora-hero.mp4';
    $ruta    = get_stylesheet_directory() . '/assets/video/' . $archivo;
    if ( ! file_exists( $ruta ) ) {
        echo wp_json_encode( array( 'error' => 'No se encontró ' . $archivo . ' en el tema.' ) ); exit;
    }

    // ¿Ya importado antes? (evita duplicados en re-ejecuciones)
    $prev = get_posts( array(
        'post_type'      => 'attachment',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_emt_hero_video_origen',
        'meta_value'     => $archivo,
    ) );

    if ( $prev ) {
        $id = (int) $prev[0];
    } else {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $tmp = wp_tempnam( $archivo );
        if ( ! $tmp || ! copy( $ruta, $tmp ) ) {
            echo wp_json_encode( array( 'error' => 'No se pudo copiar el archivo.' ) ); exit;
        }
        $id = media_handle_sideload( array( 'name' => $archivo, 'tmp_name' => $tmp ), 0 );
        if ( is_wp_error( $id ) ) {
            @unlink( $tmp );
            echo wp_json_encode( array( 'error' => $id->get_error_message() ) ); exit;
        }
        update_post_meta( (int) $id, '_emt_hero_video_origen', $archivo );
    }

    update_field( 'hero_bg_video', (int) $id, 'option' );

    echo wp_json_encode( array(
        'ok'        => true,
        'adjunto'   => (int) $id,
        'url'       => wp_get_attachment_url( (int) $id ),
        'accion'    => $prev ? 'ya estaba importado; re-asignado al hero' : 'importado y asignado al hero',
    ), JSON_UNESCAPED_UNICODE );
    exit;
}, 20 );
