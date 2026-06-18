<?php
/**
 * Enqueues del theme.
 *
 * Orden de carga: TODO el CSS/JS del child se encola con prioridad 20 en
 * wp_enqueue_scripts, DESPUÉS del CSS de Hello Elementor (prioridad 10), para
 * que nuestros estilos del design system ganen de forma natural — sin trucos
 * de especificidad.
 *
 * Cache-busting: versión por filemtime() de cada archivo, así cada cambio
 * genera un ?ver nuevo automáticamente (sin hard-refresh manual).
 *
 * Los assets del sitio real NO se cargan en la página under construction
 * (visitante anónimo), para no alterar su salida.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Versión de asset basada en mtime (con fallback a la versión del theme). */
function emt_asset_ver( $file ) {
    return file_exists( $file ) ? (string) filemtime( $file ) : wp_get_theme()->get( 'Version' );
}

/** ¿Estamos sirviendo la página under construction a un anónimo? */
function emt_is_uc_front() {
    return defined( 'EMT_UNDER_CONSTRUCTION' ) && EMT_UNDER_CONSTRUCTION
        && ! is_admin() && ! current_user_can( 'manage_options' );
}

/* Base: estilo del padre + child (prioridad 20 = después de Hello Elementor). */
add_action( 'wp_enqueue_scripts', function () {
    $dir = get_stylesheet_directory();
    wp_enqueue_style( 'hello-elementor-parent', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style(
        'explora-mexico-child',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'hello-elementor-parent' ),
        emt_asset_ver( "$dir/style.css" )
    );
}, 20 );

/**
 * Assets del SITIO REAL (componentes Fase B). Cada uno solo si su archivo existe.
 */
function emt_enqueue_site_assets() {
    if ( emt_is_uc_front() ) {
        return;
    }

    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();

    if ( file_exists( "$dir/assets/css/tokens.css" ) ) {
        wp_enqueue_style( 'emt-tokens', "$uri/assets/css/tokens.css", array( 'explora-mexico-child' ), emt_asset_ver( "$dir/assets/css/tokens.css" ) );
    }

    $deps   = array( 'emt-tokens' );
    $styles = array( 'header', 'mega-menu', 'footer', 'tour-card', 'asesor-card', 'lang-switcher', 'whatsapp-float', 'breadcrumbs' );
    foreach ( $styles as $s ) {
        $file = "$dir/assets/css/$s.css";
        if ( file_exists( $file ) ) {
            wp_enqueue_style( "emt-$s", "$uri/assets/css/$s.css", $deps, emt_asset_ver( $file ) );
        }
    }

    foreach ( array( 'mega-menu' ) as $j ) {
        $file = "$dir/assets/js/$j.js";
        if ( file_exists( $file ) ) {
            wp_enqueue_script( "emt-$j", "$uri/assets/js/$j.js", array(), emt_asset_ver( $file ), true );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'emt_enqueue_site_assets', 20 );

/**
 * CSS/JS específico por plantilla (Fase C). Solo fuera del under construction.
 */
function emt_enqueue_template_assets() {
    if ( emt_is_uc_front() ) {
        return;
    }
    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();

    $map = array();
    if ( is_front_page() ) {
        $map[] = 'home';
    }
    if ( is_post_type_archive( 'tour' ) || is_tax( array( 'tour_destino', 'tour_categoria', 'tour_experiencia' ) ) ) {
        $map[] = 'tour-archive';
    }
    if ( is_singular( 'tour' ) ) {
        $map[] = 'tour-single';
    }
    if ( is_post_type_archive( 'asesor' ) ) {
        $map[] = 'asesor-archive';
    }
    if ( is_singular( 'asesor' ) ) {
        $map[] = 'asesor-single';
    }

    foreach ( $map as $m ) {
        $file = "$dir/assets/css/$m.css";
        if ( file_exists( $file ) ) {
            wp_enqueue_style( "emt-tpl-$m", "$uri/assets/css/$m.css", array( 'emt-tokens' ), emt_asset_ver( $file ) );
        }
    }

    if ( ( is_post_type_archive( array( 'tour', 'asesor' ) ) || is_tax( array( 'tour_destino', 'tour_categoria', 'tour_experiencia' ) ) )
        && file_exists( "$dir/assets/js/filter-bar.js" ) ) {
        wp_enqueue_script( 'emt-filter-bar', "$uri/assets/js/filter-bar.js", array(), emt_asset_ver( "$dir/assets/js/filter-bar.js" ), true );
    }

    if ( is_singular( 'asesor' ) && file_exists( "$dir/assets/js/asesor-qr.js" ) ) {
        wp_enqueue_script( 'emt-asesor-qr', "$uri/assets/js/asesor-qr.js", array(), emt_asset_ver( "$dir/assets/js/asesor-qr.js" ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'emt_enqueue_template_assets', 20 );
