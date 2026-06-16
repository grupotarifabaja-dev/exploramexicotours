<?php
/**
 * Enqueues de estilos del theme.
 *
 * Hereda los estilos del tema padre (Hello Elementor) y carga el child.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'hello-elementor-parent', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style(
        'explora-mexico-child',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'hello-elementor-parent' ),
        wp_get_theme()->get( 'Version' )
    );
});

/**
 * Carga de assets del SITIO REAL (componentes Fase B).
 *
 * NO se cargan en la página under construction (visitante anónimo), para no
 * alterar su salida; sí se cargan para admins (que ven el sitio real) y cuando
 * UC esté desactivado. Cada asset se encola solo si su archivo existe.
 */
function emt_enqueue_site_assets() {
    if ( defined( 'EMT_UNDER_CONSTRUCTION' ) && EMT_UNDER_CONSTRUCTION
        && ! is_admin() && ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();
    $ver = wp_get_theme()->get( 'Version' );

    if ( file_exists( "$dir/assets/css/tokens.css" ) ) {
        wp_enqueue_style( 'emt-tokens', "$uri/assets/css/tokens.css", array( 'explora-mexico-child' ), $ver );
    }

    $deps   = array( 'emt-tokens' );
    $styles = array( 'header', 'mega-menu', 'footer', 'tour-card', 'asesor-card', 'lang-switcher', 'whatsapp-float' );
    foreach ( $styles as $s ) {
        if ( file_exists( "$dir/assets/css/$s.css" ) ) {
            wp_enqueue_style( "emt-$s", "$uri/assets/css/$s.css", $deps, $ver );
        }
    }

    foreach ( array( 'mega-menu' ) as $j ) {
        if ( file_exists( "$dir/assets/js/$j.js" ) ) {
            wp_enqueue_script( "emt-$j", "$uri/assets/js/$j.js", array(), $ver, true );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'emt_enqueue_site_assets' );
