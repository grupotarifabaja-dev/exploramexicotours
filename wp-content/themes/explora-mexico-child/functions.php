<?php
/**
 * Explora México Child Theme
 * Functions y enqueues
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Heredar estilos del tema padre (Hello Elementor)
 */
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
 * MODO UNDER CONSTRUCTION
 *
 * Mientras esta constante esté en true, todo el sitio público redirige a la
 * página plantilla "under-construction" salvo para administradores logueados.
 *
 * Para desactivar y lanzar el sitio real: cambiar a false o eliminar.
 */
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

/**
 * Endpoint para captura de leads del under construction
 * URL: /wp-json/emt/v1/lead
 */
add_action( 'rest_api_init', function() {
    register_rest_route( 'emt/v1', '/lead', array(
        'methods'  => 'POST',
        'callback' => 'emt_capture_lead',
        'permission_callback' => '__return_true',
    ));
});

function emt_capture_lead( $request ) {
    $email = sanitize_email( $request->get_param( 'email' ) );

    if ( ! is_email( $email ) ) {
        return new WP_Error( 'invalid_email', 'Correo no válido', array( 'status' => 400 ) );
    }

    // Guardar en option array
    $leads = get_option( 'emt_leads', array() );
    $leads[] = array(
        'email' => $email,
        'date'  => current_time( 'mysql' ),
        'ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
    );
    update_option( 'emt_leads', $leads );

    // Email de notificación al admin
    $admin_email = get_option( 'admin_email' );
    wp_mail(
        $admin_email,
        '[EMT] Nuevo lead del under construction',
        "Nuevo correo capturado: {$email}\nFecha: " . current_time( 'mysql' )
    );

    return array( 'success' => true, 'message' => '¡Gracias! Te avisaremos al lanzamiento.' );
}

/**
 * Panel admin: ver leads capturados
 */
add_action( 'admin_menu', function() {
    add_menu_page(
        'Leads EMT',
        'Leads EMT',
        'manage_options',
        'emt-leads',
        'emt_render_leads_page',
        'dashicons-email-alt',
        30
    );
});

function emt_render_leads_page() {
    $leads = array_reverse( get_option( 'emt_leads', array() ) );
    echo '<div class="wrap"><h1>Leads capturados (' . count( $leads ) . ')</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Correo</th><th>Fecha</th><th>IP</th></tr></thead><tbody>';
    if ( empty( $leads ) ) {
        echo '<tr><td colspan="3">Aún no hay leads.</td></tr>';
    } else {
        foreach ( $leads as $lead ) {
            echo '<tr>';
            echo '<td>' . esc_html( $lead['email'] ) . '</td>';
            echo '<td>' . esc_html( $lead['date'] ) . '</td>';
            echo '<td>' . esc_html( $lead['ip'] ) . '</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table>';

    if ( ! empty( $leads ) ) {
        echo '<p style="margin-top:20px;"><a href="' . esc_url( admin_url( 'admin.php?page=emt-leads&export=csv' ) ) . '" class="button button-primary">Exportar a CSV</a></p>';
    }
    echo '</div>';

    // Exportar CSV
    if ( isset( $_GET['export'] ) && $_GET['export'] === 'csv' ) {
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="emt-leads-' . date( 'Y-m-d' ) . '.csv"' );
        echo "Correo,Fecha,IP\n";
        foreach ( $leads as $lead ) {
            echo "{$lead['email']},{$lead['date']},{$lead['ip']}\n";
        }
        exit;
    }
}
