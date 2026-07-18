<?php
/**
 * Panel de gestión FRONTEND a medida (marca EMT) — Fase D.
 * El cliente gestiona Tours, Asesores y Configuración sin entrar a wp-admin.
 *
 * Ruta base /panel/ con sub-vistas. Autenticación reutilizando el rol emt_gestor
 * (capability edit_tours; el administrador también accede). Escrituras vía AJAX
 * con nonce + verificación de capability + sanitización.
 *
 * No afecta el front público ni el under construction.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- Routing ---------- */
add_filter( 'query_vars', function ( $v ) {
    return array_merge( $v, array( 'emt_panel', 'emt_view', 'emt_arg', 'emt_id' ) );
} );

add_action( 'init', function () {
    add_rewrite_rule( '^panel/?$', 'index.php?emt_panel=1&emt_view=dashboard', 'top' );
    add_rewrite_rule( '^panel/([^/]+)/?$', 'index.php?emt_panel=1&emt_view=$matches[1]', 'top' );
    add_rewrite_rule( '^panel/([^/]+)/([^/]+)/?$', 'index.php?emt_panel=1&emt_view=$matches[1]&emt_arg=$matches[2]', 'top' );
    add_rewrite_rule( '^panel/([^/]+)/([^/]+)/([^/]+)/?$', 'index.php?emt_panel=1&emt_view=$matches[1]&emt_arg=$matches[2]&emt_id=$matches[3]', 'top' );

    if ( get_option( 'emt_panel_rw' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'emt_panel_rw', '1' );
    }
}, 11 );

/* ---------- Helpers ---------- */
function emt_panel_url( $path = '' ) {
    return home_url( '/panel/' . ltrim( $path, '/' ) );
}
/** Capability que da acceso al panel (gestor EMT y administrador la tienen). */
function emt_panel_can() {
    return is_user_logged_in() && current_user_can( 'edit_tours' );
}
function emt_panel_is_request() {
    return (bool) get_query_var( 'emt_panel' );
}

/* Ocultar la admin bar de WP en el panel y para el rol Gestor EMT (no usan wp-admin). */
add_filter( 'show_admin_bar', function ( $show ) {
    if ( emt_panel_is_request() ) {
        return false;
    }
    if ( function_exists( 'emt_is_gestor' ) && emt_is_gestor() ) {
        return false;
    }
    return $show;
} );
function emt_panel_view_part( $name ) {
    $file = get_stylesheet_directory() . '/parts/panel/' . $name . '.php';
    if ( file_exists( $file ) ) { include $file; }
}

/* ---------- Assets (solo en el panel) ---------- */
add_action( 'wp_enqueue_scripts', function () {
    if ( ! emt_panel_is_request() ) {
        return;
    }
    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'emt-panel-tokens', "$uri/assets/css/tokens.css", array(), emt_asset_ver( "$dir/assets/css/tokens.css" ) );
    wp_enqueue_style( 'emt-panel', "$uri/assets/css/panel.css", array( 'emt-panel-tokens', 'dashicons' ), emt_asset_ver( "$dir/assets/css/panel.css" ) );

    if ( emt_panel_can() ) {
        wp_enqueue_media(); // librería de medios de WP para galerías
        wp_enqueue_script( 'emt-panel', "$uri/assets/js/panel.js", array( 'jquery' ), emt_asset_ver( "$dir/assets/js/panel.js" ), true );
        wp_localize_script( 'emt-panel', 'EMTPanel', array(
            'ajax'     => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'emt_panel' ),
            'panelUrl' => emt_panel_url(),
        ) );
    }
}, 100 );

/* ---------- Dispatcher (prioridad 1: antes que el redirect del under construction) ---------- */
add_action( 'template_redirect', function () {
    if ( ! emt_panel_is_request() ) {
        return;
    }
    nocache_headers();

    // Sin acceso → login propio del panel.
    if ( ! emt_panel_can() ) {
        emt_panel_render( 'login' );
        exit;
    }

    $view  = sanitize_key( get_query_var( 'emt_view' ) ?: 'dashboard' );
    $valid = array( 'dashboard', 'tours', 'asesores', 'destinos', 'configuracion' );
    if ( ! in_array( $view, $valid, true ) ) {
        wp_safe_redirect( emt_panel_url() );
        exit;
    }
    emt_panel_render( $view );
    exit;
}, 1 );

/* ---------- Render del documento del panel ---------- */
function emt_panel_render( $view ) {
    $logged = emt_panel_can();
    ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Panel · Explora México Tours</title>
    <?php wp_head(); ?>
</head>
<body class="emt-panel<?php echo $logged ? '' : ' emt-panel--login'; ?>">
<?php
    if ( ! $logged ) {
        emt_panel_view_part( 'login' );
    } else {
        emt_panel_view_part( 'header' );
        echo '<div class="emt-panel__shell">';
        emt_panel_view_part( 'sidebar' );
        echo '<main class="emt-panel__main">';
        emt_panel_view_part( 'view-' . $view );
        echo '</main></div>';
    }
    wp_footer();
?>
</body>
</html>
    <?php
}
