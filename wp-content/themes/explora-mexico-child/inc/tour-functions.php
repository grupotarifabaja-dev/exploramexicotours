<?php
/**
 * Helpers del CPT tour (doc maestro §6.1 + precios por ocupación v1.3).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Precios por ocupación de un tour, en orden, solo los que tienen precio > 0.
 *
 * @param int $post_id
 * @return array<int,array{label:string,precio:float,disp:int}>
 *         label = key de diccionario (emt_t) para la ocupación.
 */
function emt_tour_precios( $post_id ) {
    if ( ! function_exists( 'get_field' ) ) {
        return array();
    }
    $defs = array(
        array( 'label' => 'ocup_doble',     'precio' => 'precio_dbl',    'disp' => 'disp_dbl' ),
        array( 'label' => 'ocup_triple',    'precio' => 'precio_tpl',    'disp' => 'disp_tpl' ),
        array( 'label' => 'ocup_cuadruple', 'precio' => 'precio_cuadpl', 'disp' => 'disp_cuadpl' ),
        array( 'label' => 'ocup_menor',     'precio' => 'precio_menor',  'disp' => 'disp_menor' ),
    );
    $rows = array();
    foreach ( $defs as $d ) {
        $p = (float) get_field( $d['precio'], $post_id );
        if ( $p > 0 ) {
            $rows[] = array(
                'label'  => $d['label'],
                'precio' => $p,
                'disp'   => (int) get_field( $d['disp'], $post_id ),
            );
        }
    }
    return $rows;
}

/**
 * Sincroniza precio_desde = menor de los 4 precios por ocupación.
 * Respeta un override manual: si precio_desde ya trae valor, no lo toca.
 * Se ejecuta al guardar un tour (acf/save_post) y se puede llamar directo
 * (p. ej. desde un seeder con update_field, que no dispara acf/save_post).
 *
 * @param int|string $post_id
 * @return void
 */
function emt_tour_sync_precio_desde( $post_id ) {
    if ( ! function_exists( 'get_field' ) || get_post_type( $post_id ) !== 'tour' ) {
        return;
    }
    $manual = get_field( 'precio_desde', $post_id );
    if ( ! empty( $manual ) ) {
        return; // override manual presente
    }
    $precios = array();
    foreach ( emt_tour_precios( $post_id ) as $r ) {
        $precios[] = $r['precio'];
    }
    if ( $precios ) {
        update_field( 'precio_desde', (int) min( $precios ), $post_id );
    }
}
add_action( 'acf/save_post', 'emt_tour_sync_precio_desde', 20 );
