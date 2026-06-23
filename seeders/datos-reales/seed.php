<?php
/**
 * Seeder de DATOS REALES de Explora México Tours (contenido de entrega, reutilizable).
 *
 * Carga 2 tours reales (con fotos) y 3 asesores reales a partir de los JSON en
 * ./data/. Es IDEMPOTENTE: identifica por slug (post_name), así que re-ejecutarlo
 * actualiza en vez de duplicar. Las fotos se importan una sola vez (se marcan con
 * la meta `_emt_seed_photo` y se reutilizan en re-ejecuciones).
 *
 * NO hace nada con solo incluir el archivo (seguro). Para ejecutarlo:
 *
 *   WP-CLI (recomendado, requiere un WP-CLI funcional):
 *     wp --require=seeders/datos-reales/seed.php emt seed-datos-reales
 *     wp --require=seeders/datos-reales/seed.php emt seed-datos-reales --dry-run
 *
 *   Programático (p. ej. desde un disparador propio):
 *     require_once '.../seeders/datos-reales/seed.php';
 *     $reporte = emt_seed_datos_reales();
 *
 * Datos reales extraídos de los documentos del cliente. Los asesores no traen
 * foto en el paquete de origen (se cargan sin imagen). peek_url queda en "#"
 * hasta que el cliente entregue los enlaces de reserva.
 */

if ( ! defined( 'ABSPATH' ) ) {
    // Permite incluir el archivo fuera de WP sin efectos (la ejecución real
    // siempre ocurre dentro de WordPress).
    return;
}

/** Resuelve (o crea) un término por nombre y devuelve su term_id. */
function emt_seed_term_id( $name, $taxonomy ) {
    $name = trim( (string) $name );
    if ( $name === '' ) {
        return 0;
    }
    $term = get_term_by( 'name', $name, $taxonomy );
    if ( $term && ! is_wp_error( $term ) ) {
        return (int) $term->term_id;
    }
    $res = wp_insert_term( $name, $taxonomy );
    return is_wp_error( $res ) ? 0 : (int) $res['term_id'];
}

/** Icono sugerido para un ítem de "incluye / no incluye" según su texto. */
function emt_seed_icono_item( $texto ) {
    $t = function_exists( 'mb_strtolower' ) ? mb_strtolower( $texto ) : strtolower( $texto );
    $map = array(
        'bus'       => array( 'transporte', 'van', 'traslado' ),
        'hospedaje' => array( 'aloja', 'hotel', 'noche' ),
        'guia'      => array( 'guía', 'guia' ),
        'entrada'   => array( 'admis', 'entrada', 'museo' ),
        'comida'    => array( 'aliment', 'comida', 'desayuno', 'bebida' ),
        'equipo'    => array( 'chaleco', 'remo', 'equipo', 'llanta', 'embarca', 'panga', 'tubbing' ),
        'foto'      => array( 'foto' ),
    );
    foreach ( $map as $icono => $claves ) {
        foreach ( $claves as $c ) {
            if ( strpos( $t, $c ) !== false ) {
                return $icono;
            }
        }
    }
    return 'otro';
}

/** Importa una imagen local a la biblioteca de medios (reutiliza si ya existe). */
function emt_seed_sideload_image( $path, $post_id, $filename ) {
    $existing = get_posts( array(
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'numberposts' => 1,
        'fields'      => 'ids',
        'meta_key'    => '_emt_seed_photo',
        'meta_value'  => $filename,
    ) );
    if ( $existing ) {
        return (int) $existing[0];
    }
    if ( ! file_exists( $path ) ) {
        return 0;
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = wp_tempnam( $filename );
    if ( ! $tmp || ! @copy( $path, $tmp ) ) {
        return 0;
    }
    $file_array = array( 'name' => $filename, 'tmp_name' => $tmp );
    $att_id = media_handle_sideload( $file_array, $post_id );
    if ( is_wp_error( $att_id ) ) {
        @unlink( $tmp );
        return 0;
    }
    update_post_meta( $att_id, '_emt_seed_photo', $filename );
    return (int) $att_id;
}

/**
 * Ejecuta el seed completo. Devuelve un array de reporte.
 *
 * @param array $opts  'dry_run' => bool, 'status' => 'publish'|'draft'
 */
function emt_seed_datos_reales( $opts = array() ) {
    $dry    = ! empty( $opts['dry_run'] );
    $status = isset( $opts['status'] ) ? $opts['status'] : 'publish';
    $dir    = __DIR__ . '/data';
    $report = array( 'dry_run' => $dry, 'tours' => array(), 'asesores' => array(), 'errores' => array() );

    $tours_json = json_decode( (string) @file_get_contents( "$dir/tours-data.json" ), true );
    $ases_json  = json_decode( (string) @file_get_contents( "$dir/asesores-data.json" ), true );
    if ( ! is_array( $tours_json ) || ! is_array( $ases_json ) ) {
        $report['errores'][] = 'No se pudieron leer los JSON de datos.';
        return $report;
    }

    /* ---------------- TOURS ---------------- */
    foreach ( (array) ( $tours_json['tours'] ?? array() ) as $t ) {
        $slug  = sanitize_title( $t['slug'] ?? $t['titulo'] );
        $found = get_posts( array( 'post_type' => 'tour', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
        $action = $found ? 'actualizado' : 'creado';

        if ( $dry ) {
            $report['tours'][] = array( 'titulo' => $t['titulo'], 'slug' => $slug, 'accion' => $action . ' (dry-run)', 'fotos' => count( (array) ( $t['fotos'] ?? array() ) ) );
            continue;
        }

        $postarr = array(
            'post_type'    => 'tour',
            'post_title'   => $t['titulo'],
            'post_name'    => $slug,
            'post_content' => wp_kses_post( $t['descripcion_breve'] ?? '' ),
            'post_excerpt' => sanitize_textarea_field( $t['descripcion_breve'] ?? '' ),
            'post_status'  => $status,
        );
        if ( $found ) {
            $postarr['ID'] = (int) $found[0];
            $post_id = wp_update_post( $postarr, true );
        } else {
            $post_id = wp_insert_post( $postarr, true );
        }
        if ( is_wp_error( $post_id ) ) {
            $report['errores'][] = "Tour '{$t['titulo']}': " . $post_id->get_error_message();
            continue;
        }

        // Campos de texto / número.
        update_field( 'titulo_en', $t['titulo_en'] ?? '', $post_id );
        update_field( 'descripcion_en', $t['descripcion_breve_en'] ?? '', $post_id );
        update_field( 'excerpt_en', $t['descripcion_breve_en'] ?? '', $post_id );
        update_field( 'precio_desde', isset( $t['precio_desde'] ) ? (float) $t['precio_desde'] : '', $post_id );
        update_field( 'precio_nota', $t['precio_nota'] ?? '', $post_id );
        update_field( 'duracion_texto', $t['duracion_texto'] ?? '', $post_id );
        update_field( 'duracion_horas', isset( $t['duracion_horas'] ) ? (float) $t['duracion_horas'] : '', $post_id );
        update_field( 'dificultad', sanitize_key( $t['dificultad'] ?? 'moderada' ), $post_id );
        update_field( 'punto_salida', $t['punto_salida'] ?? '', $post_id );
        update_field( 'fecha_viaje', $t['fecha_viaje'] ?? '', $post_id );
        update_field( 'idiomas', array_map( 'sanitize_key', (array) ( $t['idiomas'] ?? array() ) ), $post_id );
        update_field( 'salida_garantizada', ! empty( $t['salida_garantizada'] ) ? 1 : 0, $post_id );
        update_field( 'pickup_hotel', ! empty( $t['pickup_hotel'] ) ? 1 : 0, $post_id );
        update_field( 'peek_url', $t['peek_url'] ?? '#', $post_id );
        update_field( 'politica_cancelacion', wp_kses_post( $t['politica_cancelacion'] ?? '' ), $post_id );

        // Repeaters incluye / no_incluye.
        foreach ( array( 'incluye', 'no_incluye' ) as $rep ) {
            $rows = array();
            foreach ( (array) ( $t[ $rep ] ?? array() ) as $texto ) {
                $texto = sanitize_text_field( $texto );
                if ( $texto !== '' ) {
                    $rows[] = array( 'icono' => emt_seed_icono_item( $texto ), 'texto' => $texto );
                }
            }
            update_field( $rep, $rows, $post_id );
        }

        // Itinerario.
        $itin = array();
        $rows = (array) ( $t['itinerario'] ?? array() );
        $n    = count( $rows );
        foreach ( array_values( $rows ) as $i => $r ) {
            $icono  = ( $i === 0 ) ? 'salida' : ( ( $i === $n - 1 ) ? 'regreso' : 'actividad' );
            $itin[] = array(
                'dia'         => (int) ( $r['dia'] ?? ( $i + 1 ) ),
                'hora'        => sanitize_text_field( $r['hora'] ?? '' ),
                'titulo'      => sanitize_text_field( $r['titulo'] ?? '' ),
                'descripcion' => sanitize_textarea_field( $r['descripcion'] ?? '' ),
                'icono'       => $icono,
            );
        }
        update_field( 'itinerario', $itin, $post_id );

        // Taxonomías.
        $dest = emt_seed_term_id( $t['destino'] ?? '', 'tour_destino' );
        wp_set_object_terms( $post_id, $dest ? array( $dest ) : array(), 'tour_destino' );
        $cat = emt_seed_term_id( $t['categoria'] ?? '', 'tour_categoria' );
        wp_set_object_terms( $post_id, $cat ? array( $cat ) : array(), 'tour_categoria' );
        $exp = array();
        foreach ( (array) ( $t['experiencias'] ?? array() ) as $e ) {
            $tid = emt_seed_term_id( $e, 'tour_experiencia' );
            if ( $tid ) { $exp[] = $tid; }
        }
        wp_set_object_terms( $post_id, $exp, 'tour_experiencia' );

        // Fotos -> galería + imagen destacada (primera).
        $galeria = array();
        foreach ( (array) ( $t['fotos'] ?? array() ) as $fname ) {
            $aid = emt_seed_sideload_image( "$dir/fotos/$fname", $post_id, $fname );
            if ( $aid ) { $galeria[] = $aid; }
        }
        if ( $galeria ) {
            update_field( 'galeria', $galeria, $post_id );
            set_post_thumbnail( $post_id, $galeria[0] );
        }

        update_post_meta( $post_id, '_emt_real_seed', 1 );
        $report['tours'][] = array( 'id' => $post_id, 'titulo' => $t['titulo'], 'slug' => $slug, 'accion' => $action, 'fotos' => count( $galeria ) );
    }

    /* ---------------- ASESORES ---------------- */
    foreach ( (array) ( $ases_json['asesores'] ?? array() ) as $a ) {
        $slug  = sanitize_title( $a['slug'] ?? $a['nombre'] );
        $found = get_posts( array( 'post_type' => 'asesor', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
        $action = $found ? 'actualizado' : 'creado';

        if ( $dry ) {
            $report['asesores'][] = array( 'nombre' => $a['nombre'], 'slug' => $slug, 'accion' => $action . ' (dry-run)' );
            continue;
        }

        $postarr = array(
            'post_type'    => 'asesor',
            'post_title'   => $a['nombre'],
            'post_name'    => $slug,
            'post_excerpt' => sanitize_textarea_field( $a['bio_corta'] ?? '' ),
            'post_status'  => $status,
        );
        if ( $found ) {
            $postarr['ID'] = (int) $found[0];
            $post_id = wp_update_post( $postarr, true );
        } else {
            $post_id = wp_insert_post( $postarr, true );
        }
        if ( is_wp_error( $post_id ) ) {
            $report['errores'][] = "Asesor '{$a['nombre']}': " . $post_id->get_error_message();
            continue;
        }

        update_field( 'puesto', sanitize_text_field( $a['puesto'] ?? '' ), $post_id );
        update_field( 'puesto_en', sanitize_text_field( $a['puesto_en'] ?? '' ), $post_id );
        update_field( 'bio_corta', sanitize_textarea_field( $a['bio_corta'] ?? '' ), $post_id );
        update_field( 'bio_corta_en', sanitize_textarea_field( $a['bio_corta_en'] ?? '' ), $post_id );
        update_field( 'telefono', sanitize_text_field( $a['telefono'] ?? '' ), $post_id );
        update_field( 'whatsapp', preg_replace( '/\D/', '', (string) ( $a['whatsapp'] ?? '' ) ), $post_id );
        update_field( 'email', sanitize_email( $a['email'] ?? '' ), $post_id );
        update_field( 'activo', ! empty( $a['activo'] ) ? 1 : 0, $post_id );
        update_field( 'orden', isset( $a['orden'] ) ? (int) $a['orden'] : '', $post_id );

        $idi = array_filter( array_map( 'trim', (array) ( $a['idiomas'] ?? array() ) ) );
        wp_set_object_terms( $post_id, $idi, 'asesor_idioma', false );
        $esp = array_filter( array_map( 'trim', (array) ( $a['especialidades'] ?? array() ) ) );
        wp_set_object_terms( $post_id, $esp, 'asesor_especialidad', false );

        update_post_meta( $post_id, '_emt_real_seed', 1 );
        $report['asesores'][] = array( 'id' => $post_id, 'nombre' => $a['nombre'], 'slug' => $slug, 'accion' => $action );
    }

    return $report;
}

/* ---------------- Comando WP-CLI ---------------- */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'emt seed-datos-reales', function ( $args, $assoc ) {
        $rep = emt_seed_datos_reales( array(
            'dry_run' => isset( $assoc['dry-run'] ),
            'status'  => $assoc['status'] ?? 'publish',
        ) );
        WP_CLI::log( wp_json_encode( $rep, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
        if ( ! empty( $rep['errores'] ) ) {
            WP_CLI::warning( count( $rep['errores'] ) . ' error(es) durante el seed.' );
        } else {
            WP_CLI::success( 'Seed de datos reales completado.' );
        }
    } );
}
