<?php
/**
 * Flotilla de transporte — fuente única de la lista "de fábrica" y migración
 * al panel: la primera vez que se abre Configuración, las unidades (con sus
 * fotos importadas a la biblioteca de medios) se copian a la option
 * `emt_flotilla` para que el equipo pueda editarlas desde el panel.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Lista de fábrica (la original de la página de Transporte).
 * 'img': archivos en assets/images/flotilla/ (principal + opcional hover).
 */
function emt_flotilla_fabrica() {
    return array(
        array( 'n' => 'Mercedes Benz Sprinter Lux', 'cap' => '20', 'icon' => '🚐', 'img' => array( 'sprinter-lux-1.jpg', 'sprinter-lux-2.jpg' ), 'feats_es' => 'Asientos reclinables, mesa de trabajo con portavasos, A/C, Smart TV, Bluetooth, audio tipo cine, cargadores USB y tipo C, aislante térmico, espacio para maletas, ventanas panorámicas.', 'feats_en' => 'Reclining seats, work table with cup holders, A/C, Smart TV, Bluetooth, cinema-grade audio, USB & USB-C chargers, thermal insulation, luggage space, panoramic windows.' ),
        array( 'n' => 'Mercedes Benz Sprinter Regular', 'cap' => '20', 'icon' => '🚐', 'img' => array( 'sprinter-regular-1.jpg', 'sprinter-regular-2.jpg' ), 'feats_es' => 'Asientos reclinables, A/C, TV y DVD, espacio para maletas, ventanas.', 'feats_en' => 'Reclining seats, A/C, TV & DVD, luggage space, windows.' ),
        array( 'n' => 'Autobús (Irizar, Volvo, Marcopolo, Neobus)', 'cap' => '46–50', 'icon' => '🚌', 'img' => array( 'autobus-irizar-1.jpg' ), 'feats_es' => 'A/C, TV, DVD, audio, cargadores, maletero interior, espacio para maletas, 1 o 2 puertas, ventanas panorámicas.', 'feats_en' => 'A/C, TV, DVD, audio, chargers, interior luggage rack, luggage space, 1 or 2 doors, panoramic windows.' ),
        array( 'n' => 'Toyota Hiace / Urban / Transit', 'cap' => '12', 'icon' => '🚐', 'img' => array( 'hiace-1.jpg' ), 'feats_es' => 'Asientos reclinables, A/C, TV, DVD, audio, parrilla porta equipaje (según unidad).', 'feats_en' => 'Reclining seats, A/C, TV, DVD, audio, roof luggage rack (per unit).' ),
        array( 'n' => 'Suburban línea 2019', 'cap' => '6', 'icon' => '🚙', 'img' => array( 'suburban-2019-1.jpg', 'suburban-2019-2.jpg' ), 'feats_es' => 'A/C, vidrios y seguros eléctricos, DVD, vestiduras en piel, cajuela para maletas.', 'feats_en' => 'A/C, power windows & locks, DVD, leather upholstery, luggage trunk.' ),
        array( 'n' => 'Suburban línea nueva 2023', 'cap' => '6', 'icon' => '🚙', 'img' => array( 'suburban-nueva-1.jpg', 'suburban-nueva-2.jpg' ), 'feats_es' => 'A/C, vidrios y seguros eléctricos, DVD, vestiduras en piel, cajuela.', 'feats_en' => 'A/C, power windows & locks, DVD, leather upholstery, trunk.' ),
        array( 'n' => 'Camry 2023', 'cap' => '4', 'icon' => '🚗', 'img' => array( 'camry-1.jpg', 'camry-2.jpg' ), 'feats_es' => 'A/C, vidrios/seguros eléctricos, vestiduras en tela, cajuela, Bluetooth.', 'feats_en' => 'A/C, power windows/locks, fabric upholstery, trunk, Bluetooth.' ),
        array( 'n' => 'Versa 2025', 'cap' => '3', 'icon' => '🚗', 'img' => array( 'versa-1.jpg' ), 'feats_es' => 'A/C, vidrios/seguros eléctricos, vestiduras en tela, cajuela, CarPlay, Bluetooth.', 'feats_en' => 'A/C, power windows/locks, fabric upholstery, trunk, CarPlay, Bluetooth.' ),
        array( 'n' => 'Mini SUV Suzuki o Mitsubishi 2020', 'cap' => '6 (4 c/equipaje)', 'icon' => '🚙', 'img' => array( 'mini-suv-1.jpg' ), 'feats_es' => 'A/C, vestiduras en tela, parrilla exterior, cajuela para bolsa de mano, CarPlay, Bluetooth.', 'feats_en' => 'A/C, fabric upholstery, exterior rack, carry-on trunk, CarPlay, Bluetooth.' ),
    );
}

/**
 * Importa un archivo del tema a la biblioteca de medios y devuelve su ID
 * (o 0 si falla). Evita duplicados buscando por el meta de origen.
 */
function emt_flotilla_importar_foto( $archivo ) {
    $ruta = get_stylesheet_directory() . '/assets/images/flotilla/' . $archivo;
    if ( ! file_exists( $ruta ) ) { return 0; }

    // ¿Ya se importó antes?
    $prev = get_posts( array(
        'post_type'      => 'attachment',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_emt_flotilla_origen',
        'meta_value'     => $archivo,
    ) );
    if ( $prev ) { return (int) $prev[0]; }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = wp_tempnam( $archivo );
    if ( ! $tmp || ! copy( $ruta, $tmp ) ) { return 0; }

    $id = media_handle_sideload( array( 'name' => $archivo, 'tmp_name' => $tmp ), 0 );
    if ( is_wp_error( $id ) ) {
        @unlink( $tmp );
        return 0;
    }
    update_post_meta( (int) $id, '_emt_flotilla_origen', $archivo );
    return (int) $id;
}

/**
 * Migración única: llena la option `emt_flotilla` con la lista de fábrica
 * (fotos importadas como adjuntos) para que sea editable desde el panel.
 */
function emt_flotilla_migrar() {
    if ( get_option( 'emt_flotilla_migrada' ) === '1' ) { return; }
    $actual = get_option( 'emt_flotilla' );
    if ( is_array( $actual ) && $actual ) {
        update_option( 'emt_flotilla_migrada', '1', false );
        return; // ya hay datos capturados; no se pisan.
    }

    $rows = array();
    foreach ( emt_flotilla_fabrica() as $v ) {
        $fotos = array_values( (array) ( $v['img'] ?? array() ) );
        $rows[] = array(
            'nombre'    => $v['n'],
            'capacidad' => (string) $v['cap'],
            'feats'     => $v['feats_es'],
            'feats_en'  => $v['feats_en'],
            'foto'      => isset( $fotos[0] ) ? emt_flotilla_importar_foto( $fotos[0] ) : 0,
            'foto2'     => isset( $fotos[1] ) ? emt_flotilla_importar_foto( $fotos[1] ) : 0,
        );
    }
    update_option( 'emt_flotilla', $rows, false );
    update_option( 'emt_flotilla_migrada', '1', false );
}
