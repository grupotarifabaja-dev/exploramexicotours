<?php
/**
 * Disparador TEMPORAL: reasigna Destino, Categoria y Experiencia de los 24 tours
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
    'xantolo-dia-de-muertos-huasteca' => array( 'destino' => 'San Luis Potosí', 'categoria' => 'Cultural', 'experiencias' => array('Día de Muertos') ),
    'dia-de-muertos-michoacan' => array( 'destino' => 'Michoacán', 'categoria' => 'Cultural', 'experiencias' => array('Día de Muertos', 'Pueblos Mágicos') ),
    'dia-de-muertos-en-mixquic' => array( 'destino' => 'Ciudad de México', 'categoria' => 'Cultural', 'experiencias' => array('Día de Muertos') ),
    'barrancas-del-cobre' => array( 'destino' => 'Chihuahua', 'categoria' => 'Ecoturismo', 'experiencias' => array('Naturaleza y paisajes') ),
    'oaxaca-ciudad' => array( 'destino' => 'Oaxaca', 'categoria' => 'Cultural', 'experiencias' => array() ),
    'oaxaca-y-sus-playas' => array( 'destino' => 'Oaxaca', 'categoria' => 'Aventura', 'experiencias' => array('Naturaleza y paisajes') ),
    'explora-chiapas' => array( 'destino' => 'Chiapas', 'categoria' => 'Aventura', 'experiencias' => array('Naturaleza y paisajes') ),
    'ciudades-coloniales' => array( 'destino' => 'Querétaro – Guanajuato – Michoacán', 'categoria' => 'Cultural', 'experiencias' => array('Pueblos Mágicos') ),
    'tour-tequila-tradicion-con-distincion' => array( 'destino' => 'Jalisco', 'categoria' => 'Gastronómico', 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos') ),
    'tour-tequila-jose-cuervo-express' => array( 'destino' => 'Jalisco', 'categoria' => 'Gastronómico', 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos') ),
    'tour-tequila-la-rojena' => array( 'destino' => 'Jalisco', 'categoria' => 'Gastronómico', 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos') ),
    'tour-tequila-jornalero' => array( 'destino' => 'Jalisco', 'categoria' => 'Gastronómico', 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos') ),
    'tour-tequila-a-tu-alcance' => array( 'destino' => 'Jalisco', 'categoria' => 'Gastronómico', 'experiencias' => array('Ruta del Tequila', 'Pueblos Mágicos') ),
    'tour-tequilero-hasta-los-huesos' => array( 'destino' => 'Jalisco', 'categoria' => 'Gastronómico', 'experiencias' => array('Ruta del Tequila', 'Día de Muertos', 'Pueblos Mágicos') ),
    'guadalajara-y-tlaquepaque' => array( 'destino' => 'Jalisco', 'categoria' => 'Cultural', 'experiencias' => array() ),
    'chapala-y-ajijic' => array( 'destino' => 'Jalisco', 'categoria' => 'Cultural', 'experiencias' => array('Pueblos Mágicos', 'Naturaleza y paisajes') ),
    'mazamitla' => array( 'destino' => 'Jalisco', 'categoria' => 'Ecoturismo', 'experiencias' => array('Pueblos Mágicos', 'Naturaleza y paisajes') ),
    'canonismo-en-jalisco' => array( 'destino' => 'Jalisco', 'categoria' => 'Aventura', 'experiencias' => array('Naturaleza y paisajes') ),
    'dia-de-muertos-y-calaverandia' => array( 'destino' => 'Jalisco', 'categoria' => 'Cultural', 'experiencias' => array('Día de Muertos') ),
    'guadalajara-y-modelado-en-barro' => array( 'destino' => 'Jalisco', 'categoria' => 'Cultural', 'experiencias' => array() ),
    'vinedos-de-chapala' => array( 'destino' => 'Jalisco', 'categoria' => 'Gastronómico', 'experiencias' => array('Naturaleza y paisajes') ),
    'isla-de-mezcala-y-ajijic' => array( 'destino' => 'Jalisco', 'categoria' => 'Cultural', 'experiencias' => array('Pueblos Mágicos', 'Naturaleza y paisajes') ),
    'senderismo-petroglifos-mezcala' => array( 'destino' => 'Jalisco', 'categoria' => 'Ecoturismo', 'experiencias' => array('Naturaleza y paisajes') ),
    'aventura-en-bici-la-primavera' => array( 'destino' => 'Jalisco', 'categoria' => 'Aventura', 'experiencias' => array('Naturaleza y paisajes') ),
    );

    $out = array();
    foreach ( $map as $slug => $c ) {
        $post = get_page_by_path( $slug, OBJECT, 'tour' );
        if ( ! $post ) { $out[] = array( 'slug' => $slug, 'accion' => 'NO ENCONTRADO' ); continue; }
        $pid = (int) $post->ID;

        $d = $tid( $c['destino'], 'tour_destino' );
        wp_set_object_terms( $pid, $d ? array( $d ) : array(), 'tour_destino' );

        $ca = $tid( $c['categoria'], 'tour_categoria' );
        wp_set_object_terms( $pid, $ca ? array( $ca ) : array(), 'tour_categoria' );

        $exp = array();
        foreach ( (array) $c['experiencias'] as $e ) { $id = $tid( $e, 'tour_experiencia' ); if ( $id ) { $exp[] = $id; } }
        wp_set_object_terms( $pid, $exp, 'tour_experiencia' );

        $out[] = array( 'slug' => $slug, 'id' => $pid, 'destino' => $c['destino'], 'categoria' => $c['categoria'], 'experiencias' => $c['experiencias'], 'accion' => 'reasignado' );
    }

    $limpiados = array();
    if ( ! empty( $_GET['limpiar'] ) ) {
        foreach ( array( 'tour_categoria', 'tour_experiencia' ) as $tax ) {
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
