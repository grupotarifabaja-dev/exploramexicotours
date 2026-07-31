<?php
/**
 * Disparador TEMPORAL: descarga los logos de empresas que confían en
 * Explora (fuentes públicas) a la biblioteca de medios y guarda la option
 * `emt_avales_logos` (slug => attachment_id) que usa el carrusel del home.
 * Idempotente: no re-importa (meta _emt_aval_origen).
 *
 * Uso: /?emt_avales_logos=emt-logos-2026
 * QUITAR este archivo (y su require en functions.php) tras aplicarlo en producción.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    if ( ! isset( $_GET['emt_avales_logos'] ) ) { return; }
    if ( ! current_user_can( 'manage_options' ) && ! hash_equals( 'emt-logos-2026', (string) $_GET['emt_avales_logos'] ) ) {
        status_header( 403 ); header( 'Content-Type: text/plain; charset=utf-8' ); echo 'token invalido'; exit;
    }
    header( 'Content-Type: application/json; charset=utf-8' );
    @set_time_limit( 0 );

    // Empresas de la lista del cliente (3.-Transporte.docx). Fuentes públicas.
    $fuentes = array(
        'tcs'      => array( 'url' => 'https://upload.wikimedia.org/wikipedia/commons/9/99/TATA_Consultancy_Services_Logo_blue.svg', 'nombre' => 'TATA Consultancy Services' ),
        'wipro'    => array( 'url' => 'https://upload.wikimedia.org/wikipedia/commons/8/80/Wipro_Logo_Black.svg', 'nombre' => 'Wipro' ),
        'igt'      => array( 'url' => 'https://upload.wikimedia.org/wikipedia/commons/3/31/IGT_logo.png', 'nombre' => 'IGT' ),
        'rosewood' => array( 'url' => 'https://upload.wikimedia.org/wikipedia/commons/d/db/Rosewood_hotel_resorts_logo.jpg', 'nombre' => 'Rosewood Hotels & Resorts' ),
    );

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Permitir SVG solo durante esta importación (WP lo bloquea por defecto).
    $permitir_svg = function ( $mimes ) { $mimes['svg'] = 'image/svg+xml'; return $mimes; };
    add_filter( 'upload_mimes', $permitir_svg );
    add_filter( 'wp_check_filetype_and_ext', function ( $data, $file, $filename ) {
        if ( preg_match( '/\.svg$/i', $filename ) ) {
            $data['ext'] = 'svg'; $data['type'] = 'image/svg+xml';
        }
        return $data;
    }, 10, 3 );

    $logos  = is_array( get_option( 'emt_avales_logos' ) ) ? get_option( 'emt_avales_logos' ) : array();
    $salida = array();

    foreach ( $fuentes as $slug => $f ) {
        // ¿Ya importado?
        $prev = get_posts( array(
            'post_type'      => 'attachment',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_emt_aval_origen',
            'meta_value'     => $slug,
        ) );
        if ( $prev ) {
            $logos[ $slug ]  = (int) $prev[0];
            $salida[ $slug ] = array( 'ok' => true, 'adjunto' => (int) $prev[0], 'accion' => 'ya estaba importado' );
            continue;
        }

        $tmp = download_url( $f['url'] );
        if ( is_wp_error( $tmp ) ) {
            $salida[ $slug ] = array( 'ok' => false, 'error' => $tmp->get_error_message() );
            continue;
        }
        $nombre_archivo = 'aval-' . $slug . '.' . strtolower( pathinfo( wp_parse_url( $f['url'], PHP_URL_PATH ), PATHINFO_EXTENSION ) );
        $id = media_handle_sideload( array( 'name' => $nombre_archivo, 'tmp_name' => $tmp ), 0, $f['nombre'] );
        if ( is_wp_error( $id ) ) {
            @unlink( $tmp );
            $salida[ $slug ] = array( 'ok' => false, 'error' => $id->get_error_message() );
            continue;
        }
        update_post_meta( (int) $id, '_emt_aval_origen', $slug );
        update_post_meta( (int) $id, '_wp_attachment_image_alt', $f['nombre'] );
        $logos[ $slug ]  = (int) $id;
        $salida[ $slug ] = array( 'ok' => true, 'adjunto' => (int) $id, 'url' => wp_get_attachment_url( (int) $id ), 'accion' => 'importado' );
    }

    update_option( 'emt_avales_logos', $logos, true );

    echo wp_json_encode( array( 'ok' => true, 'logos' => $salida ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    exit;
}, 20 );
