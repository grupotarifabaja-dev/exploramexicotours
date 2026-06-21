<?php
/**
 * Panel — handlers AJAX de escritura (Fase D, P3+).
 * Seguridad: nonce 'emt_panel' + verificación de capability + sanitización en cada acción.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Verifica nonce + capability mínima del panel; corta con error si no. */
function emt_panel_guard( $cap = 'edit_tours' ) {
    if ( ! check_ajax_referer( 'emt_panel', 'nonce', false ) || ! is_user_logged_in() || ! current_user_can( $cap ) ) {
        wp_send_json_error( array( 'msg' => 'Sesión no válida o sin permisos.' ), 403 );
    }
}

/* ============================================================
   Guardar tour (alta o edición)
   ============================================================ */
add_action( 'wp_ajax_emt_panel_save_tour', 'emt_panel_save_tour' );
function emt_panel_save_tour() {
    emt_panel_guard( 'edit_tours' );

    $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
    $titulo  = sanitize_text_field( wp_unslash( $_POST['titulo'] ?? '' ) );
    if ( $titulo === '' ) {
        wp_send_json_error( array( 'msg' => 'El título es obligatorio.', 'field' => 'titulo' ), 400 );
    }

    $status = ( ( $_POST['status'] ?? '' ) === 'publish' ) ? 'publish' : 'draft';
    if ( $status === 'publish' && ! current_user_can( 'publish_tours' ) ) {
        $status = 'draft';
    }
    // Solo al publicar exigimos duración; el borrador puede guardarse parcial.
    if ( $status === 'publish' && sanitize_text_field( wp_unslash( $_POST['duracion_texto'] ?? '' ) ) === '' ) {
        wp_send_json_error( array( 'msg' => 'La duración es obligatoria para publicar.', 'field' => 'duracion_texto' ), 400 );
    }

    $desc = wp_kses_post( wp_unslash( $_POST['descripcion'] ?? '' ) );
    $data = array(
        'post_type'    => 'tour',
        'post_title'   => $titulo,
        'post_content' => $desc,
        'post_excerpt' => sanitize_textarea_field( wp_unslash( $_POST['descripcion'] ?? '' ) ),
        'post_status'  => $status,
    );

    if ( $post_id ) {
        if ( get_post_type( $post_id ) !== 'tour' || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'msg' => 'No puedes editar este tour.' ), 403 );
        }
        $data['ID'] = $post_id;
        wp_update_post( $data );
    } else {
        $post_id = wp_insert_post( $data, true );
        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'msg' => 'No se pudo guardar.' ), 500 );
        }
    }

    // Campos de texto.
    $text = array( 'titulo_en', 'descripcion_en', 'duracion_texto', 'punto_salida', 'fecha_viaje', 'seo_title_override', 'seo_desc_override' );
    foreach ( $text as $f ) {
        update_field( $f, sanitize_text_field( wp_unslash( $_POST[ $f ] ?? '' ) ), $post_id );
    }
    update_field( 'precio_nota', sanitize_textarea_field( wp_unslash( $_POST['precio_nota'] ?? '' ) ), $post_id );
    update_field( 'politica_cancelacion', wp_kses_post( wp_unslash( $_POST['politica_cancelacion'] ?? '' ) ), $post_id );
    update_field( 'peek_url', esc_url_raw( wp_unslash( $_POST['peek_url'] ?? '' ) ), $post_id );
    update_field( 'mapa_embed', esc_url_raw( wp_unslash( $_POST['mapa_embed'] ?? '' ) ), $post_id );

    // Números (vacío => '' para que el autocalc funcione en precio_desde).
    $nums = array( 'precio_desde', 'duracion_horas', 'precio_dbl', 'disp_dbl', 'precio_tpl', 'disp_tpl', 'precio_cuadpl', 'disp_cuadpl', 'precio_menor', 'disp_menor' );
    foreach ( $nums as $f ) {
        $raw = $_POST[ $f ] ?? '';
        update_field( $f, ( $raw === '' ) ? '' : (float) $raw, $post_id );
    }

    // Select + booleanos.
    update_field( 'dificultad', sanitize_key( $_POST['dificultad'] ?? 'facil' ), $post_id );
    update_field( 'salida_garantizada', empty( $_POST['salida_garantizada'] ) ? 0 : 1, $post_id );
    update_field( 'pickup_hotel', empty( $_POST['pickup_hotel'] ) ? 0 : 1, $post_id );
    update_field( 'destacado', empty( $_POST['destacado'] ) ? 0 : 1, $post_id );

    // Idiomas (checkbox).
    $idiomas = array_map( 'sanitize_key', (array) ( $_POST['idiomas'] ?? array() ) );
    update_field( 'idiomas', $idiomas, $post_id );

    // Repeaters incluye / no_incluye.
    foreach ( array( 'incluye', 'no_incluye' ) as $rep ) {
        $rows = array();
        foreach ( (array) ( $_POST[ $rep ] ?? array() ) as $r ) {
            $texto = sanitize_text_field( wp_unslash( $r['texto'] ?? '' ) );
            if ( $texto !== '' ) {
                $rows[] = array( 'icono' => sanitize_key( $r['icono'] ?? 'otro' ), 'texto' => $texto );
            }
        }
        update_field( $rep, $rows, $post_id );
    }

    // Itinerario.
    $itin = array();
    foreach ( (array) ( $_POST['itinerario'] ?? array() ) as $r ) {
        $tit = sanitize_text_field( wp_unslash( $r['titulo'] ?? '' ) );
        if ( $tit === '' ) { continue; }
        $itin[] = array(
            'dia'         => (int) ( $r['dia'] ?? 0 ),
            'hora'        => sanitize_text_field( wp_unslash( $r['hora'] ?? '' ) ),
            'titulo'      => $tit,
            'descripcion' => sanitize_textarea_field( wp_unslash( $r['descripcion'] ?? '' ) ),
            'icono'       => sanitize_key( $r['icono'] ?? 'actividad' ),
        );
    }
    update_field( 'itinerario', $itin, $post_id );

    // Galería + imagen destacada (primera).
    $galeria = array_values( array_filter( array_map( 'intval', (array) ( $_POST['galeria'] ?? array() ) ) ) );
    update_field( 'galeria', $galeria, $post_id );
    if ( $galeria ) {
        set_post_thumbnail( $post_id, $galeria[0] );
    } else {
        delete_post_thumbnail( $post_id );
    }

    // Taxonomías.
    $destino = (int) ( $_POST['destino'] ?? 0 );
    wp_set_object_terms( $post_id, $destino ? array( $destino ) : array(), 'tour_destino' );
    $cat = (int) ( $_POST['categoria'] ?? 0 );
    wp_set_object_terms( $post_id, $cat ? array( $cat ) : array(), 'tour_categoria' );
    $exp = array_map( 'intval', (array) ( $_POST['experiencias'] ?? array() ) );
    wp_set_object_terms( $post_id, $exp, 'tour_experiencia' );

    // Autocalc de precio_desde (no se dispara con update_field).
    if ( function_exists( 'emt_tour_sync_precio_desde' ) ) {
        emt_tour_sync_precio_desde( $post_id );
    }

    wp_send_json_success( array(
        'id'      => $post_id,
        'status'  => get_post_status( $post_id ),
        'msg'     => ( $status === 'publish' ) ? 'Tour publicado.' : 'Borrador guardado.',
        'editUrl' => emt_panel_url( 'tours/editar/' . $post_id . '/' ),
        'listUrl' => emt_panel_url( 'tours/' ),
    ) );
}

/* ============================================================
   Eliminar tour (a la papelera)
   ============================================================ */
add_action( 'wp_ajax_emt_panel_delete_tour', function () {
    emt_panel_guard( 'delete_tours' );
    $id = (int) ( $_POST['id'] ?? 0 );
    if ( ! $id || get_post_type( $id ) !== 'tour' || ! current_user_can( 'delete_post', $id ) ) {
        wp_send_json_error( array( 'msg' => 'No puedes eliminar este tour.' ), 403 );
    }
    wp_trash_post( $id );
    wp_send_json_success( array( 'msg' => 'Tour enviado a la papelera.' ) );
} );

/* ============================================================
   Guardar asesor (alta o edición)  — P4
   ============================================================ */
add_action( 'wp_ajax_emt_panel_save_asesor', 'emt_panel_save_asesor' );
function emt_panel_save_asesor() {
    emt_panel_guard( 'edit_asesores' );

    $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
    $nombre  = sanitize_text_field( wp_unslash( $_POST['titulo'] ?? '' ) );
    if ( $nombre === '' ) {
        wp_send_json_error( array( 'msg' => 'El nombre es obligatorio.', 'field' => 'titulo' ), 400 );
    }

    $status = ( ( $_POST['status'] ?? '' ) === 'publish' ) ? 'publish' : 'draft';
    if ( $status === 'publish' && ! current_user_can( 'publish_asesores' ) ) {
        $status = 'draft';
    }
    // Solo al publicar exigimos los datos de ficha; el borrador puede guardarse parcial.
    if ( $status === 'publish' ) {
        $obligatorios = array( 'puesto' => 'el puesto', 'bio_corta' => 'la bio corta', 'telefono' => 'el teléfono', 'whatsapp' => 'el WhatsApp', 'email' => 'el email' );
        foreach ( $obligatorios as $f => $lbl ) {
            if ( sanitize_text_field( wp_unslash( $_POST[ $f ] ?? '' ) ) === '' ) {
                wp_send_json_error( array( 'msg' => 'Para publicar falta ' . $lbl . '.', 'field' => $f ), 400 );
            }
        }
    }

    $data = array(
        'post_type'    => 'asesor',
        'post_title'   => $nombre,
        'post_excerpt' => sanitize_textarea_field( wp_unslash( $_POST['bio_corta'] ?? '' ) ),
        'post_status'  => $status,
    );

    if ( $post_id ) {
        if ( get_post_type( $post_id ) !== 'asesor' || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'msg' => 'No puedes editar este asesor.' ), 403 );
        }
        $data['ID'] = $post_id;
        wp_update_post( $data );
    } else {
        $post_id = wp_insert_post( $data, true );
        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'msg' => 'No se pudo guardar.' ), 500 );
        }
    }

    // Texto.
    update_field( 'puesto', sanitize_text_field( wp_unslash( $_POST['puesto'] ?? '' ) ), $post_id );
    update_field( 'puesto_en', sanitize_text_field( wp_unslash( $_POST['puesto_en'] ?? '' ) ), $post_id );
    update_field( 'bio_corta', sanitize_textarea_field( wp_unslash( $_POST['bio_corta'] ?? '' ) ), $post_id );
    update_field( 'bio_corta_en', sanitize_textarea_field( wp_unslash( $_POST['bio_corta_en'] ?? '' ) ), $post_id );
    update_field( 'telefono', sanitize_text_field( wp_unslash( $_POST['telefono'] ?? '' ) ), $post_id );
    update_field( 'whatsapp', preg_replace( '/\D/', '', (string) wp_unslash( $_POST['whatsapp'] ?? '' ) ), $post_id );
    update_field( 'email', sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ), $post_id );
    update_field( 'linkedin', esc_url_raw( wp_unslash( $_POST['linkedin'] ?? '' ) ), $post_id );
    update_field( 'instagram', esc_url_raw( wp_unslash( $_POST['instagram'] ?? '' ) ), $post_id );
    update_field( 'activo', empty( $_POST['activo'] ) ? 0 : 1, $post_id );
    $orden = $_POST['orden'] ?? '';
    update_field( 'orden', ( $orden === '' ) ? '' : (int) $orden, $post_id );

    // Foto -> imagen destacada.
    $foto = (int) ( $_POST['foto'] ?? 0 );
    if ( $foto ) {
        set_post_thumbnail( $post_id, $foto );
    } else {
        delete_post_thumbnail( $post_id );
    }

    // Taxonomías (tags por nombre, separadas por coma): idiomas y especialidades.
    foreach ( array( 'idiomas' => 'asesor_idioma', 'especialidades' => 'asesor_especialidad' ) as $field => $tax ) {
        $raw   = sanitize_text_field( wp_unslash( $_POST[ $field ] ?? '' ) );
        $names = array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ) ) );
        wp_set_object_terms( $post_id, $names, $tax, false );
    }

    wp_send_json_success( array(
        'id'      => $post_id,
        'status'  => get_post_status( $post_id ),
        'msg'     => ( $status === 'publish' ) ? 'Asesor publicado.' : 'Borrador guardado.',
        'editUrl' => emt_panel_url( 'asesores/editar/' . $post_id . '/' ),
        'listUrl' => emt_panel_url( 'asesores/' ),
    ) );
}

/* ============================================================
   Eliminar asesor (a la papelera)  — P4
   ============================================================ */
add_action( 'wp_ajax_emt_panel_delete_asesor', function () {
    emt_panel_guard( 'delete_asesores' );
    $id = (int) ( $_POST['id'] ?? 0 );
    if ( ! $id || get_post_type( $id ) !== 'asesor' || ! current_user_can( 'delete_post', $id ) ) {
        wp_send_json_error( array( 'msg' => 'No puedes eliminar este asesor.' ), 403 );
    }
    wp_trash_post( $id );
    wp_send_json_success( array( 'msg' => 'Asesor enviado a la papelera.' ) );
} );

/* ============================================================
   Guardar configuración del sitio (options page emt-config)  — P5
   Acceso: misma capability que da entrada al panel (edit_tours).
   ============================================================ */
add_action( 'wp_ajax_emt_panel_save_config', 'emt_panel_save_config' );
function emt_panel_save_config() {
    emt_panel_guard( 'edit_tours' );

    // Contacto.
    update_field( 'wa_number', preg_replace( '/\D/', '', (string) wp_unslash( $_POST['wa_number'] ?? '' ) ), 'option' );
    update_field( 'telefono_oficina', sanitize_text_field( wp_unslash( $_POST['telefono_oficina'] ?? '' ) ), 'option' );
    update_field( 'email_reservas', sanitize_email( wp_unslash( $_POST['email_reservas'] ?? '' ) ), 'option' );
    update_field( 'email_contacto', sanitize_email( wp_unslash( $_POST['email_contacto'] ?? '' ) ), 'option' );
    update_field( 'direccion_fiscal', sanitize_textarea_field( wp_unslash( $_POST['direccion_fiscal'] ?? '' ) ), 'option' );

    // Redes sociales.
    foreach ( array( 'redes_facebook', 'redes_instagram', 'redes_tiktok', 'redes_youtube' ) as $f ) {
        update_field( $f, esc_url_raw( wp_unslash( $_POST[ $f ] ?? '' ) ), 'option' );
    }

    // Textos del sitio: hero estacional.
    update_field( 'hero_seasonal_active', empty( $_POST['hero_seasonal_active'] ) ? 0 : 1, 'option' );
    update_field( 'hero_seasonal_title', sanitize_text_field( wp_unslash( $_POST['hero_seasonal_title'] ?? '' ) ), 'option' );
    update_field( 'hero_seasonal_subtitle', sanitize_textarea_field( wp_unslash( $_POST['hero_seasonal_subtitle'] ?? '' ) ), 'option' );
    update_field( 'hero_seasonal_cta_text', sanitize_text_field( wp_unslash( $_POST['hero_seasonal_cta_text'] ?? '' ) ), 'option' );
    update_field( 'hero_seasonal_cta_url', esc_url_raw( wp_unslash( $_POST['hero_seasonal_cta_url'] ?? '' ) ), 'option' );

    wp_send_json_success( array( 'msg' => 'Configuración guardada.' ) );
}
