<?php
/**
 * Disparador TEMPORAL para poblar tours/asesores reales vía el seeder de datos
 * reales (seeders/datos-reales). Idempotente (identifica por slug), por lo que
 * re-ejecutarlo actualiza en vez de duplicar.
 *
 * Uso (admin o con token): /?emt_run_seed=TOKEN
 * El seeder se hornea en la imagen en /opt/emt/seeders (ver Dockerfile).
 *
 * QUITAR este archivo (y su require en functions.php) tras poblar producción.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    if ( ! isset( $_GET['emt_run_seed'] ) ) {
        return;
    }
    $token = 'emt-seed-2026-jalisco';
    if ( ! hash_equals( $token, (string) $_GET['emt_run_seed'] ) ) {
        status_header( 403 );
        header( 'Content-Type: text/plain; charset=utf-8' );
        echo 'token invalido';
        exit;
    }

    $candidatos = array(
        '/opt/emt/seeders/datos-reales/seed.php',
        get_stylesheet_directory() . '/../../../seeders/datos-reales/seed.php',
    );
    $seed = '';
    foreach ( $candidatos as $c ) {
        if ( file_exists( $c ) ) { $seed = $c; break; }
    }
    header( 'Content-Type: application/json; charset=utf-8' );
    if ( $seed === '' ) {
        status_header( 500 );
        echo wp_json_encode( array( 'error' => 'seeder no encontrado', 'buscado' => $candidatos ) );
        exit;
    }

    @set_time_limit( 0 );
    @ignore_user_abort( true );
    require_once $seed;
    if ( ! function_exists( 'emt_seed_datos_reales' ) ) {
        status_header( 500 );
        echo wp_json_encode( array( 'error' => 'funcion del seeder no disponible' ) );
        exit;
    }
    $rep = emt_seed_datos_reales( array( 'status' => 'publish' ) );
    echo wp_json_encode( $rep, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    exit;
}, 5 );
