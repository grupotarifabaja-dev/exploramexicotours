<?php
/**
 * Disparador TEMPORAL para crear un usuario con rol Gestor EMT (acceso a /panel/)
 * sin necesitar el wp-admin. NO define la contraseña: devuelve un enlace de
 * "establecer contraseña" (flujo nativo de WP) para que el usuario ponga la suya.
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

    header( 'Content-Type: application/json; charset=utf-8' );

    if ( ! is_email( $email ) ) {
        echo wp_json_encode( array( 'error' => 'Falta un ?email= válido en la URL.' ) );
        exit;
    }

    if ( function_exists( 'emt_register_roles' ) ) {
        emt_register_roles(); // asegura que el rol emt_gestor exista
    }

    $user = get_user_by( 'login', $login );
    if ( ! $user ) { $user = get_user_by( 'email', $email ); }

    if ( $user ) {
        $uid = $user->ID;
        $u   = new WP_User( $uid );
        if ( ! in_array( 'emt_gestor', (array) $u->roles, true ) && ! user_can( $u, 'manage_options' ) ) {
            $u->set_role( 'emt_gestor' );
        }
        $creado = false;
    } else {
        $uid = wp_insert_user( array(
            'user_login'   => $login,
            'user_email'   => $email,
            'user_pass'    => wp_generate_password( 24, true, true ),
            'role'         => 'emt_gestor',
            'display_name' => 'Gestor Explora México',
        ) );
        if ( is_wp_error( $uid ) ) {
            echo wp_json_encode( array( 'error' => $uid->get_error_message() ) );
            exit;
        }
        $creado = true;
    }

    $key   = get_password_reset_key( get_user_by( 'id', $uid ) );
    $reset = is_wp_error( $key ) ? '' : network_site_url( 'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $login ), 'login' );

    echo wp_json_encode( array(
        'ok'                   => true,
        'creado'               => $creado,
        'usuario'              => $login,
        'email'                => $email,
        'rol'                  => 'emt_gestor',
        'define_tu_contrasena' => $reset,
        'panel'                => home_url( '/panel/' ),
        'pasos'                => '1) Abre "define_tu_contrasena" y pon tu contraseña. 2) Entra en "panel" con tu usuario y esa contraseña.',
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
    exit;
} );
