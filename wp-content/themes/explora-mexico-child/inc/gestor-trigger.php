<?php
/**
 * Disparador TEMPORAL para dar acceso al panel sin wp-admin: crea (o reutiliza)
 * un usuario con rol Gestor EMT y —por defecto— INICIA SESIÓN y redirige a /panel/.
 * Con &pass_link=1 devuelve en su lugar un enlace nativo para establecer contraseña.
 *
 * Uso: /?emt_make_gestor=emt-gestor-2026&user=USUARIO&email=CORREO
 * QUITAR este archivo (+ su require) antes de producción.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    if ( ! isset( $_GET['emt_make_gestor'] ) ) {
        return;
    }
    if ( ! hash_equals( 'emt-gestor-2026', (string) $_GET['emt_make_gestor'] ) ) {
        status_header( 403 );
        header( 'Content-Type: text/plain; charset=utf-8' );
        echo 'token invalido';
        exit;
    }

    $login = sanitize_user( wp_unslash( $_GET['user'] ?? 'gestor' ), true );
    if ( $login === '' ) { $login = 'gestor'; }
    $email = sanitize_email( wp_unslash( $_GET['email'] ?? '' ) );

    if ( function_exists( 'emt_register_roles' ) ) {
        emt_register_roles(); // asegura que el rol emt_gestor exista
    }

    $user = get_user_by( 'login', $login );
    if ( ! $user && is_email( $email ) ) { $user = get_user_by( 'email', $email ); }

    if ( $user ) {
        $uid = (int) $user->ID;
        $u   = new WP_User( $uid );
        if ( ! in_array( 'emt_gestor', (array) $u->roles, true ) && ! user_can( $u, 'manage_options' ) ) {
            $u->set_role( 'emt_gestor' );
        }
    } else {
        if ( ! is_email( $email ) ) {
            header( 'Content-Type: application/json; charset=utf-8' );
            echo wp_json_encode( array( 'error' => 'Falta un ?email= válido para crear el usuario.' ) );
            exit;
        }
        $uid = wp_insert_user( array(
            'user_login'   => $login,
            'user_email'   => $email,
            'user_pass'    => wp_generate_password( 24, true, true ),
            'role'         => 'emt_gestor',
            'display_name' => 'Gestor Explora México',
        ) );
        if ( is_wp_error( $uid ) ) {
            header( 'Content-Type: application/json; charset=utf-8' );
            echo wp_json_encode( array( 'error' => $uid->get_error_message() ) );
            exit;
        }
        $uid = (int) $uid;
    }

    // Modo enlace de contraseña (opcional).
    if ( isset( $_GET['pass_link'] ) ) {
        $key   = get_password_reset_key( get_user_by( 'id', $uid ) );
        $reset = is_wp_error( $key ) ? '' : network_site_url( 'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $login ), 'login' );
        header( 'Content-Type: application/json; charset=utf-8' );
        echo wp_json_encode( array( 'ok' => true, 'usuario' => $login, 'define_tu_contrasena' => $reset, 'nota' => 'Abre el enlace UNA vez (no lo regeneres) para poner tu contraseña.' ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        exit;
    }

    // Por defecto: iniciar sesión en ESTE navegador y llevar al panel.
    wp_clear_auth_cookie();
    wp_set_current_user( $uid );
    wp_set_auth_cookie( $uid, true );
    wp_safe_redirect( home_url( '/panel/' ) );
    exit;
} );
