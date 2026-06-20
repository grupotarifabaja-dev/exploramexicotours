<?php
/**
 * Rol personalizado "Gestor EMT" (emt_gestor) y restricción de acceso al admin
 * (doc maestro §11.3). El cliente solo gestiona Tours y Asesores + Medios.
 *
 * Los CPT tour/asesor usan capabilities propias (capability_type en inc/cpts.php),
 * así el rol queda acotado a esos dos tipos y NADA más.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'EMT_ROLES_VERSION', '1' ); // súbelo si cambian las caps, para re-registrar

/** Capabilities primitivas de CRUD para un CPT con capability_type plural dado. */
function emt_cpt_caps( $plural ) {
    return array(
        "edit_{$plural}", "edit_others_{$plural}", "edit_published_{$plural}", "edit_private_{$plural}",
        "publish_{$plural}", "read_private_{$plural}",
        "delete_{$plural}", "delete_others_{$plural}", "delete_published_{$plural}", "delete_private_{$plural}",
    );
}

/** Todas las caps de tours + asesores. */
function emt_gestor_cpt_caps() {
    return array_merge( emt_cpt_caps( 'tours' ), emt_cpt_caps( 'asesores' ) );
}

/** ¿El usuario actual es Gestor EMT (y no admin)? */
function emt_is_gestor( $user = null ) {
    $user = $user ?: wp_get_current_user();
    return $user && in_array( 'emt_gestor', (array) $user->roles, true ) && ! user_can( $user, 'manage_options' );
}

/**
 * Registra/actualiza el rol y otorga las caps de tour/asesor al administrador.
 * Idempotente (versionado por opción). Se ejecuta en init y en after_switch_theme.
 */
function emt_register_roles() {
    $cpt_caps = emt_gestor_cpt_caps();

    // Caps del rol Gestor EMT: lo justo (CRUD de tours/asesores + medios + leer perfil).
    $gestor = array( 'read' => true, 'upload_files' => true );
    foreach ( $cpt_caps as $c ) {
        $gestor[ $c ] = true;
    }

    if ( ! get_role( 'emt_gestor' ) ) {
        add_role( 'emt_gestor', 'Gestor EMT', $gestor );
    } else {
        $role = get_role( 'emt_gestor' );
        foreach ( $gestor as $c => $grant ) {
            $role->add_cap( $c );
        }
    }

    // El administrador conserva acceso total a los CPT con caps propias.
    $admin = get_role( 'administrator' );
    if ( $admin ) {
        foreach ( $cpt_caps as $c ) {
            $admin->add_cap( $c );
        }
    }
}

add_action( 'after_switch_theme', 'emt_register_roles' );
add_action( 'init', function () {
    if ( get_option( 'emt_roles_version' ) !== EMT_ROLES_VERSION ) {
        emt_register_roles();
        update_option( 'emt_roles_version', EMT_ROLES_VERSION );
    }
}, 5 );

/* ============================================================
   Restricción de menú: el Gestor EMT solo ve Dashboard, Tours,
   Asesores, Medios y su perfil. (Las caps ya ocultan casi todo;
   esto limpia menús de plugins que se cuelan con cap 'read'.)
   ============================================================ */
add_action( 'admin_menu', function () {
    if ( ! emt_is_gestor() ) {
        return;
    }
    $quitar = array(
        'edit.php',                 // Entradas
        'edit.php?post_type=page',  // Páginas
        'edit-comments.php',        // Comentarios
        'themes.php',               // Apariencia
        'plugins.php',              // Plugins
        'users.php',                // Usuarios
        'tools.php',                // Herramientas
        'options-general.php',      // Ajustes
    );
    foreach ( $quitar as $slug ) {
        remove_menu_page( $slug );
    }
}, 999 );

/* ============================================================
   Defensa en profundidad: redirige al Gestor EMT fuera de
   cualquier pantalla de admin no permitida.
   ============================================================ */
add_action( 'admin_init', function () {
    if ( ! emt_is_gestor() || wp_doing_ajax() ) {
        return;
    }
    global $pagenow;

    // Tipo de contenido en juego (lista, alta o edición).
    $cpt = isset( $_REQUEST['post_type'] ) ? sanitize_key( wp_unslash( $_REQUEST['post_type'] ) ) : '';
    if ( $pagenow === 'post.php' && isset( $_GET['post'] ) ) {
        $cpt = get_post_type( (int) $_GET['post'] );
    }

    $siempre   = array( 'index.php', 'profile.php', 'upload.php', 'media-new.php', 'async-upload.php', 'admin-ajax.php' );
    $de_cpt    = array( 'edit.php', 'post-new.php', 'post.php' );
    $permitido = in_array( $pagenow, $siempre, true )
        || ( in_array( $pagenow, $de_cpt, true ) && in_array( $cpt, array( 'tour', 'asesor' ), true ) );

    if ( ! $permitido ) {
        wp_safe_redirect( admin_url( 'edit.php?post_type=tour' ) );
        exit;
    }
} );
