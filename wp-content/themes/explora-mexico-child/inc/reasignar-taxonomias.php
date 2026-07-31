<?php
/**
 * Disparador TEMPORAL: reasigna Destinos, Categorias (1a = principal) y Experiencias de los 26 tours
 * segun el modelo diferenciado (Categoria = tipo de tour; Experiencia = tema/ocasion).
 * Independiente del seeder horneado (que puede estar desactualizado en la imagen).
 *
 * Uso (admin o con token): /?emt_reasignar_tax=emt-tax-2026
 *   &limpiar=1  -> ademas elimina terminos de categoria/experiencia que queden en 0 tours.
 *
 * QUITAR este archivo (y su require en functions.php) tras aplicarlo.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    if ( ! isset( $_GET['emt_reasignar_tax'] ) ) { return; }
    $token = 'emt-tax-2026';
    if ( ! current_user_can( 'manage_options' ) && ! hash_equals( $token, (string) $_GET['emt_reasignar_tax'] ) ) {
        status_header( 403 ); header( 'Content-Type: text/plain; charset=utf-8' ); echo 'token invalido'; exit;
    }
    header( 'Content-Type: application/json; charset=utf-8' );
    @set_time_limit( 0 );

    $tid = function ( $name, $tax ) {
        $name = trim( (string) $name );
        if ( $name === '' ) { return 0; }
        $t = get_term_by( 'name', $name, $tax );
        if ( $t && ! is_wp_error( $t ) ) { return (int) $t->term_id; }
        $r = wp_insert_term( $name, $tax );
        return is_wp_error( $r ) ? 0 : (int) $r['term_id'];
    };

    $map = array(
    'xantolo-dia-de-muertos-huasteca' => array( 'destinos' => array('San Luis Potosí'), 'categorias' => array('Cultural'), 'experiencias' => array('Día de Muertos', 'Naturaleza y paisajes') ),
    'dia-de-muertos-michoacan' => array( 'destinos' => array('Michoacán'), 'categorias' => array('Cultural'), 'experiencias' => array('Día de Muertos', 'Pueblos Mágicos') ),
    'dia-de-muertos-en-mixquic' => array( 'destinos' => array('Ciudad de México'), 'categorias' => array('Cultural'), 'experiencias' => array('Día de Muertos') ),
    'barrancas-del-cobre' => array( 'destinos' => array('Chihuahua'), 'categorias' => array('Ecoturismo', 'Cultural'), 'experiencias' => array('Naturaleza y paisajes') ),
    'oaxaca-ciudad' => array( 'destinos' => array('Oaxaca'), 'categorias' => array('Cultural', 'Gastronómico'), 'experiencias' => array('Arte y artesanías') ),
    'oaxaca-y-sus-playas' => array( 'destinos' => array('Oaxaca'), 'categorias' => array('Sol y playa'), 'experiencias' => array('Naturaleza y paisajes') ),
    'explora-chiapas' => array( 'destinos' => array('Chiapas'), 'categorias' => array('Ecoturismo', 'Cultural'), 'experiencias' => array('Naturaleza y paisajes', 'Pueblos Mágicos') ),
    'ciudades-coloniales' => array( 'destinos' => array('Querétaro', 'Guanajuato', 'Michoacán'), 'categorias' => array('Cultural'), 'experiencias' => array('Pueblos Mágicos') ),
    'tour-tequila-tradicion-con-distincion' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Gastronómico'), 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos') ),
    'tour-tequila-jose-cuervo-express' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Gastronómico'), 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos', 'En familia') ),
    'tour-tequila-la-rojena' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Gastronómico'), 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos') ),
    'tour-tequila-jornalero' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Gastronómico'), 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos') ),
    'tour-tequila-a-tu-alcance' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Gastronómico'), 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos') ),
    'tour-tequilero-hasta-los-huesos' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Gastronómico'), 'experiencias' => array('Ruta del Tequila', 'Día de Muertos', 'Pueblos Mágicos') ),
    'guadalajara-y-tlaquepaque' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Cultural'), 'experiencias' => array('Pueblos Mágicos', 'Arte y artesanías') ),
    'chapala-y-ajijic' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Cultural'), 'experiencias' => array('Pueblos Mágicos', 'Naturaleza y paisajes') ),
    'mazamitla' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Ecoturismo'), 'experiencias' => array('Pueblos Mágicos', 'Naturaleza y paisajes', 'En familia') ),
    'canonismo-en-jalisco' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Aventura'), 'experiencias' => array('Naturaleza y paisajes') ),
    'dia-de-muertos-y-calaverandia' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Cultural'), 'experiencias' => array('Día de Muertos', 'En familia') ),
    'guadalajara-y-modelado-en-barro' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Cultural'), 'experiencias' => array('Pueblos Mágicos', 'Arte y artesanías') ),
    'vinedos-de-chapala' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Gastronómico'), 'experiencias' => array('Naturaleza y paisajes', 'En pareja') ),
    'isla-de-mezcala-y-ajijic' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Cultural'), 'experiencias' => array('Pueblos Mágicos', 'Naturaleza y paisajes') ),
    'senderismo-petroglifos-mezcala' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Ecoturismo', 'Cultural'), 'experiencias' => array('Naturaleza y paisajes') ),
    'aventura-en-bici-la-primavera' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Aventura'), 'experiencias' => array('Naturaleza y paisajes') ),
    'san-sebastian-y-mascota' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Cultural', 'Ecoturismo'), 'experiencias' => array('Pueblos Mágicos', 'Naturaleza y paisajes', 'En pareja') ),
    'recuerdo-de-mexico-sesion-fotografica' => array( 'destinos' => array('Jalisco'), 'categorias' => array('Cultural'), 'experiencias' => array('Día de Muertos', 'En familia', 'En pareja') ),
    );

    $out = array();
    foreach ( $map as $slug => $c ) {
        $post = get_page_by_path( $slug, OBJECT, 'tour' );
        if ( ! $post ) { $out[] = array( 'slug' => $slug, 'accion' => 'NO ENCONTRADO' ); continue; }
        $pid = (int) $post->ID;

        $dest = array();
        foreach ( (array) $c['destinos'] as $d ) { $id = $tid( $d, 'tour_destino' ); if ( $id ) { $dest[] = $id; } }
        wp_set_object_terms( $pid, $dest, 'tour_destino' );

        $cats = array();
        foreach ( (array) $c['categorias'] as $ca ) { $id = $tid( $ca, 'tour_categoria' ); if ( $id ) { $cats[] = $id; } }
        wp_set_object_terms( $pid, $cats, 'tour_categoria' );
        // La primera categoría de la lista es la PRINCIPAL (etiqueta en tarjetas).
        if ( function_exists( 'update_field' ) ) {
            update_field( 'categoria_principal', ( count( $cats ) > 1 ) ? $cats[0] : '', $pid );
        }

        $exp = array();
        foreach ( (array) $c['experiencias'] as $e ) { $id = $tid( $e, 'tour_experiencia' ); if ( $id ) { $exp[] = $id; } }
        wp_set_object_terms( $pid, $exp, 'tour_experiencia' );

        $out[] = array( 'slug' => $slug, 'id' => $pid, 'destinos' => $c['destinos'], 'categorias' => $c['categorias'], 'experiencias' => $c['experiencias'], 'accion' => 'reasignado' );
    }

    $limpiados = array();
    if ( ! empty( $_GET['limpiar'] ) ) {
        foreach ( array( 'tour_destino', 'tour_categoria', 'tour_experiencia' ) as $tax ) {
            $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
            if ( is_wp_error( $terms ) ) { continue; }
            foreach ( $terms as $term ) {
                if ( (int) $term->count === 0 ) {
                    wp_delete_term( $term->term_id, $tax );
                    $limpiados[] = $tax . ':' . $term->name;
                }
            }
        }
    }

    echo wp_json_encode( array( 'ok' => true, 'tours' => $out, 'eliminados' => $limpiados ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    exit;
}, 20 );
