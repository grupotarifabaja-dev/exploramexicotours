<?php
/**
 * MODO UNDER CONSTRUCTION
 *
 * Mientras esta constante esté en true, todo el sitio público redirige a la
 * página plantilla "under-construction" salvo para administradores logueados.
 *
 * Para desactivar y lanzar el sitio real: cambiar a false o eliminar.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'EMT_UNDER_CONSTRUCTION', true );

add_action( 'template_redirect', function() {
    if ( ! defined( 'EMT_UNDER_CONSTRUCTION' ) || ! EMT_UNDER_CONSTRUCTION ) return;

    // Admins logueados ven el sitio normal para poder trabajar
    if ( current_user_can( 'manage_options' ) ) return;

    // Permitir wp-admin, wp-login y AJAX
    if ( is_admin() ) return;
    if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false ) return;
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

    // Servir la plantilla under-construction
    $template = get_stylesheet_directory() . '/template-under-construction.php';
    if ( file_exists( $template ) ) {
        status_header( 200 );
        include $template;
        exit;
    }
});
