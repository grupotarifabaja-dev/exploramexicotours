<?php
/**
 * Filtros del catálogo de tours (Fase D) — lógica compartida entre el render
 * server-side (parts/tour-listing.php, para SEO y sin-JS) y el handler AJAX.
 *
 * Filtros: destino, categoría, experiencia (taxonomías), dificultad, rango de
 * precio (precio_desde) y duración (duracion_horas agrupada en rangos).
 *
 * TOURS SIN PRECIO: los tours con precio_desde vacío se muestran SIEMPRE, incluso
 * con el filtro de precio activo (no se pueden ocultar por un precio que no tienen).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

const EMT_TOURS_PER_PAGE = 9;

/** Grupos de duración (en horas). value => array(min, max, label_key). */
function emt_tour_duracion_grupos() {
    return array(
        '1'   => array( 'min' => 0,   'max' => 24,            'label' => '1 día' ),
        '2-3' => array( 'min' => 25,  'max' => 72,            'label' => '2-3 días' ),
        '4-6' => array( 'min' => 73,  'max' => 144,           'label' => '4-6 días' ),
        '7+'  => array( 'min' => 145, 'max' => PHP_INT_MAX,   'label' => '7+ días' ),
    );
}

/** Límites (min/max) de precio_desde entre los tours con precio, redondeados a 100. */
function emt_tour_price_bounds() {
    static $bounds = null;
    if ( $bounds !== null ) {
        return $bounds;
    }
    global $wpdb;
    $vals = $wpdb->get_col(
        "SELECT pm.meta_value FROM {$wpdb->postmeta} pm
         JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = 'precio_desde' AND p.post_type = 'tour'
           AND p.post_status = 'publish' AND pm.meta_value <> ''"
    );
    $nums = array_filter( array_map( 'floatval', (array) $vals ), function ( $v ) { return $v > 0; } );
    if ( ! $nums ) {
        $bounds = array( 'min' => 0, 'max' => 0 );
        return $bounds;
    }
    $bounds = array(
        'min' => (int) ( floor( min( $nums ) / 100 ) * 100 ),
        'max' => (int) ( ceil( max( $nums ) / 100 ) * 100 ),
    );
    return $bounds;
}

/**
 * Normaliza los filtros desde una fuente ($_GET o $_POST).
 *
 * @param array $src
 * @return array
 */
function emt_tour_parse_filters( $src ) {
    $bounds  = emt_tour_price_bounds();
    $grupos  = emt_tour_duracion_grupos();

    $duracion = array_values( array_intersect(
        array_map( 'sanitize_text_field', (array) ( $src['duracion'] ?? array() ) ),
        array_keys( $grupos )
    ) );

    $precio_min = isset( $src['precio_min'] ) && $src['precio_min'] !== '' ? absint( $src['precio_min'] ) : null;
    $precio_max = isset( $src['precio_max'] ) && $src['precio_max'] !== '' ? absint( $src['precio_max'] ) : null;

    return array(
        'destino'     => array_values( array_filter( array_map( 'absint', (array) ( $src['destino'] ?? array() ) ) ) ),
        'categoria'   => array_values( array_filter( array_map( 'absint', (array) ( $src['categoria'] ?? array() ) ) ) ),
        'experiencia' => array_values( array_filter( array_map( 'absint', (array) ( $src['experiencia'] ?? array() ) ) ) ),
        'dificultad'  => isset( $src['dificultad'] ) ? sanitize_key( $src['dificultad'] ) : '',
        'duracion'    => $duracion,
        'precio_min'  => $precio_min,
        'precio_max'  => $precio_max,
        'q'           => isset( $src['q'] ) ? sanitize_text_field( wp_unslash( $src['q'] ) ) : '',
    );
}

/** ¿El filtro de precio está realmente acotando (distinto del rango completo)? */
function emt_tour_price_is_active( $filters ) {
    $b = emt_tour_price_bounds();
    $min = $filters['precio_min'];
    $max = $filters['precio_max'];
    return ( $min !== null && $min > $b['min'] ) || ( $max !== null && $max < $b['max'] );
}

/**
 * Construye los args de WP_Query a partir de filtros normalizados.
 *
 * @param array $filters   Salida de emt_tour_parse_filters().
 * @param array $base_tax  tax_query fija (p. ej. de una plantilla de taxonomía).
 * @param int   $paged
 * @return array
 */
function emt_tour_build_query_args( $filters, $base_tax, $paged ) {
    // tax_query.
    $tax_query = ( is_array( $base_tax ) && $base_tax ) ? $base_tax : array();
    if ( $filters['destino'] )     { $tax_query[] = array( 'taxonomy' => 'tour_destino', 'field' => 'term_id', 'terms' => $filters['destino'] ); }
    if ( $filters['categoria'] )   { $tax_query[] = array( 'taxonomy' => 'tour_categoria', 'field' => 'term_id', 'terms' => $filters['categoria'] ); }
    if ( $filters['experiencia'] ) { $tax_query[] = array( 'taxonomy' => 'tour_experiencia', 'field' => 'term_id', 'terms' => $filters['experiencia'] ); }
    if ( count( $tax_query ) > 1 ) { $tax_query['relation'] = 'AND'; }

    // meta_query.
    $meta_query = array( 'relation' => 'AND' );

    if ( $filters['dificultad'] ) {
        $meta_query[] = array( 'key' => 'dificultad', 'value' => $filters['dificultad'] );
    }

    // Duración: OR de rangos sobre duracion_horas.
    if ( $filters['duracion'] ) {
        $grupos = emt_tour_duracion_grupos();
        $dur_or = array( 'relation' => 'OR' );
        foreach ( $filters['duracion'] as $g ) {
            if ( ! isset( $grupos[ $g ] ) ) { continue; }
            $rango = $grupos[ $g ];
            if ( $rango['max'] === PHP_INT_MAX ) {
                $dur_or[] = array( 'key' => 'duracion_horas', 'value' => $rango['min'], 'type' => 'NUMERIC', 'compare' => '>=' );
            } else {
                $dur_or[] = array( 'key' => 'duracion_horas', 'value' => array( $rango['min'], $rango['max'] ), 'type' => 'NUMERIC', 'compare' => 'BETWEEN' );
            }
        }
        if ( count( $dur_or ) > 1 ) { $meta_query[] = $dur_or; }
    }

    // Precio: solo si acota. Los tours SIN precio (vacío o sin meta) SIEMPRE pasan.
    if ( emt_tour_price_is_active( $filters ) ) {
        $b   = emt_tour_price_bounds();
        $min = $filters['precio_min'] !== null ? $filters['precio_min'] : $b['min'];
        $max = $filters['precio_max'] !== null ? $filters['precio_max'] : $b['max'];
        $meta_query[] = array(
            'relation' => 'OR',
            array( 'key' => 'precio_desde', 'value' => array( $min, $max ), 'type' => 'NUMERIC', 'compare' => 'BETWEEN' ),
            array( 'key' => 'precio_desde', 'value' => '', 'compare' => '=' ),
            array( 'key' => 'precio_desde', 'compare' => 'NOT EXISTS' ),
        );
    }

    return array(
        'post_type'      => 'tour',
        'post_status'    => 'publish',
        'posts_per_page' => EMT_TOURS_PER_PAGE,
        'paged'          => max( 1, (int) $paged ),
        's'              => $filters['q'],
        'tax_query'      => $tax_query,
        'meta_query'     => $meta_query,
    );
}

/** Renderiza las tarjetas de un WP_Query a string (para la respuesta AJAX). */
function emt_tour_render_cards_html( $query ) {
    ob_start();
    while ( $query->have_posts() ) {
        $query->the_post();
        emt_render_tour_card( get_the_ID() );
    }
    wp_reset_postdata();
    return ob_get_clean();
}

/** Etiqueta "X tours encontrados". */
function emt_tour_count_label( $n ) {
    $fmt = function_exists( 'emt_t' ) ? emt_t( 'resultados_n' ) : '%d tours';
    return sprintf( $fmt, (int) $n );
}

/* ============================================================
   Handler AJAX (admin-ajax): filtra y devuelve las tarjetas.
   ============================================================ */
add_action( 'wp_ajax_emt_filter_tours', 'emt_tour_ajax_filter' );
add_action( 'wp_ajax_nopriv_emt_filter_tours', 'emt_tour_ajax_filter' );
function emt_tour_ajax_filter() {
    check_ajax_referer( 'emt_filter', 'nonce' );

    $filters = emt_tour_parse_filters( $_POST );
    $paged   = max( 1, absint( $_POST['paged'] ?? 1 ) );

    // tax_query base (si venimos de una plantilla de taxonomía).
    $base_tax  = array();
    $base_taxo = isset( $_POST['base_tax'] ) ? sanitize_key( $_POST['base_tax'] ) : '';
    $base_term = isset( $_POST['base_term'] ) ? absint( $_POST['base_term'] ) : 0;
    if ( $base_term && in_array( $base_taxo, array( 'tour_destino', 'tour_categoria', 'tour_experiencia' ), true ) ) {
        $base_tax = array( array( 'taxonomy' => $base_taxo, 'field' => 'term_id', 'terms' => array( $base_term ) ) );
    }

    $query = new WP_Query( emt_tour_build_query_args( $filters, $base_tax, $paged ) );

    wp_send_json_success( array(
        'html'        => emt_tour_render_cards_html( $query ),
        'found'       => (int) $query->found_posts,
        'page'        => $paged,
        'max_pages'   => (int) $query->max_num_pages,
        'has_more'    => $paged < (int) $query->max_num_pages,
        'count_label' => emt_tour_count_label( $query->found_posts ),
        'empty'       => ( $query->found_posts === 0 ),
    ) );
}
