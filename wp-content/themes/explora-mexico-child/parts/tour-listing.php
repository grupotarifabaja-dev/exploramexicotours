<?php
/**
 * Parte reutilizable: filtros + grid de tours (doc maestro §8.2).
 * Usado por archive-tour.php y las plantillas taxonomy-tour_*.php.
 *
 * Filtrado con AJAX + "cargar más" (assets/js/tour-filter.js) y degradación a
 * GET sin JS. La lógica de filtros vive en inc/tour-filters.php (compartida con
 * el handler AJAX). Variable opcional en scope: $emt_listing_base (tax_query fija).
 *
 * Rediseño 2026 (filtros que escalan): controles en grupos colapsables (acordeón)
 * con lista compacta + conteo por opción, "Ver más" para listas largas y buscador
 * dentro de Destinos. Los filtros activos se muestran como chips removibles ARRIBA
 * de los resultados. Todo con degradación sin JS (grupos abiertos, todo visible).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_lang   = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$emt_prefix = ( $emt_lang === 'en' ) ? '/en' : '';
$base_url   = home_url( $emt_prefix . '/tours/' );

$filters = emt_tour_parse_filters( $_GET );
$paged   = max( 1, absint( get_query_var( 'paged' ) ?: ( $_GET['paged'] ?? 1 ) ) );
$base    = isset( $emt_listing_base ) && is_array( $emt_listing_base ) ? $emt_listing_base : array();

// Taxonomía base (para acotar el AJAX en plantillas de taxonomía).
$base_taxo = '';
$base_term = 0;
if ( isset( $base[0]['taxonomy'], $base[0]['terms'] ) ) {
    $base_taxo = $base[0]['taxonomy'];
    $bt        = $base[0]['terms'];
    $base_term = is_array( $bt ) ? (int) reset( $bt ) : (int) $bt;
}

// En una taxonomía, el form (GET sin JS) y "limpiar" apuntan al término (conserva el scope).
$scoped_url = $base_url;
if ( $base_taxo && $base_term ) {
    $tl = get_term_link( $base_term, $base_taxo );
    if ( ! is_wp_error( $tl ) ) { $scoped_url = $tl; }
}

$query = new WP_Query( emt_tour_build_query_args( $filters, $base, $paged ) );

// Datos para los controles. hide_empty: solo términos con tours (sin callejones sin salida).
$tx_destino = get_terms( array( 'taxonomy' => 'tour_destino', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
$tx_cat     = get_terms( array( 'taxonomy' => 'tour_categoria', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
$tx_exp     = get_terms( array( 'taxonomy' => 'tour_experiencia', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
$difs       = array( 'facil' => 'Fácil', 'moderada' => 'Moderada', 'alta' => 'Alta' );
$grupos_dur = emt_tour_duracion_grupos();
$bounds     = emt_tour_price_bounds();
$has_price  = ( $bounds['max'] > $bounds['min'] );
$cur_pmin   = $filters['precio_min'] !== null ? $filters['precio_min'] : $bounds['min'];
$cur_pmax   = $filters['precio_max'] !== null ? $filters['precio_max'] : $bounds['max'];

/**
 * Grupo de facetas (acordeón) para una taxonomía: lista compacta + conteo,
 * "Ver más" tras $collapse opciones y buscador opcional.
 */
$emt_facet_tax = function ( $title, $name, $terms, $selected, $collapse = 0, $searchable = false, $open = false ) {
    if ( is_wp_error( $terms ) || ! $terms ) { return; }
    $has_sel = false;
    foreach ( $terms as $t ) { if ( in_array( $t->term_id, $selected, true ) ) { $has_sel = true; break; } }
    $open  = $open || $has_sel;               // si hay algo seleccionado, abre el grupo
    $total = count( $terms );
    ?>
    <div class="emt-facet<?php echo $open ? ' is-open' : ''; ?>" data-emt-facet>
        <button type="button" class="emt-facet__head" data-facet-toggle aria-expanded="<?php echo $open ? 'true' : 'false'; ?>">
            <span><?php echo esc_html( $title ); ?></span>
            <span class="emt-facet__caret" aria-hidden="true"></span>
        </button>
        <div class="emt-facet__body">
            <?php if ( $searchable ) : ?>
                <div class="emt-facet__search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" class="emt-facet__search-input" data-facet-search placeholder="<?php echo esc_attr( emt_t( 'buscar_destino' ) ); ?>" aria-label="<?php echo esc_attr( emt_t( 'buscar_destino' ) ); ?>" />
                </div>
            <?php endif; ?>
            <ul class="emt-opts">
                <?php $i = 0; foreach ( $terms as $t ) :
                    $is_extra = ( $collapse && $i >= $collapse ) ? ' is-extra' : '';
                    ?>
                    <li class="emt-opt-item<?php echo $is_extra; ?>">
                        <label class="emt-opt">
                            <input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo (int) $t->term_id; ?>"<?php echo in_array( $t->term_id, $selected, true ) ? ' checked' : ''; ?> />
                            <span class="emt-opt__name"><?php echo esc_html( $t->name ); ?></span>
                            <span class="emt-opt__count"><?php echo (int) $t->count; ?></span>
                        </label>
                    </li>
                <?php $i++; endforeach; ?>
            </ul>
            <p class="emt-facet__nomatch" data-facet-nomatch hidden><?php echo esc_html( emt_t( 'sin_resultados' ) ); ?></p>
            <?php if ( $collapse && $total > $collapse ) : ?>
                <button type="button" class="emt-facet__more" data-facet-more>
                    <span class="emt-facet__more-mas"><?php printf( esc_html( emt_t( 'ver_mas_n' ) ), (int) ( $total - $collapse ) ); ?></span>
                    <span class="emt-facet__more-menos"><?php echo esc_html( emt_t( 'ver_menos' ) ); ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
};

/**
 * Grupo de facetas simple (acordeón) para opciones fijas (duración / dificultad).
 */
$emt_facet_simple = function ( $title, $body_cb, $open = false ) {
    ?>
    <div class="emt-facet<?php echo $open ? ' is-open' : ''; ?>" data-emt-facet>
        <button type="button" class="emt-facet__head" data-facet-toggle aria-expanded="<?php echo $open ? 'true' : 'false'; ?>">
            <span><?php echo esc_html( $title ); ?></span>
            <span class="emt-facet__caret" aria-hidden="true"></span>
        </button>
        <div class="emt-facet__body">
            <ul class="emt-opts"><?php $body_cb(); ?></ul>
        </div>
    </div>
    <?php
};
?>
<div class="emt-container emt-listing" data-emt-listing>
    <button type="button" class="emt-btn emt-btn--secondary emt-filters__toggle" data-emt-filters-toggle aria-expanded="false"><?php echo esc_html( emt_t( 'filtros' ) ); ?></button>

    <form class="emt-filters" method="get" action="<?php echo esc_url( $scoped_url ); ?>" data-emt-filters
          data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
          data-nonce="<?php echo esc_attr( wp_create_nonce( 'emt_filter' ) ); ?>"
          data-base-url="<?php echo esc_url( $scoped_url ); ?>">
        <?php if ( $base_taxo && $base_term ) : ?>
            <input type="hidden" name="base_tax" value="<?php echo esc_attr( $base_taxo ); ?>" />
            <input type="hidden" name="base_term" value="<?php echo (int) $base_term; ?>" />
        <?php endif; ?>

        <div class="emt-filters__search">
            <input type="search" name="q" value="<?php echo esc_attr( $filters['q'] ); ?>" placeholder="<?php echo esc_attr( emt_t( 'buscar' ) ); ?>" />
        </div>

        <?php
        // Destinos: abierto, con buscador y "ver más" tras 6. Categorías: abierto.
        // Experiencias/Duración/Dificultad: colapsados por defecto.
        $emt_facet_tax( emt_t( 'destinos' ), 'destino', $tx_destino, $filters['destino'], 6, true, true );
        $emt_facet_tax( emt_t( 'categorias' ), 'categoria', $tx_cat, $filters['categoria'], 0, false, true );
        $emt_facet_tax( emt_t( 'experiencias' ), 'experiencia', $tx_exp, $filters['experiencia'], 8, false, false );

        $emt_facet_simple( emt_t( 'duracion' ), function () use ( $grupos_dur, $filters ) {
            foreach ( $grupos_dur as $val => $g ) {
                printf(
                    '<li class="emt-opt-item"><label class="emt-opt"><input type="checkbox" name="duracion[]" value="%s"%s><span class="emt-opt__name">%s</span></label></li>',
                    esc_attr( $val ),
                    in_array( $val, $filters['duracion'], true ) ? ' checked' : '',
                    esc_html( $g['label'] )
                );
            }
        }, in_array( true, array_map( function ( $v ) use ( $filters ) { return in_array( $v, $filters['duracion'], true ); }, array_keys( $grupos_dur ) ), true ) );

        $emt_facet_simple( emt_t( 'dificultad' ), function () use ( $difs, $filters ) {
            printf(
                '<li class="emt-opt-item"><label class="emt-opt"><input type="radio" name="dificultad" value=""%s><span class="emt-opt__name">%s</span></label></li>',
                checked( $filters['dificultad'], '', false ),
                esc_html( emt_t( 'todas' ) )
            );
            foreach ( $difs as $k => $lbl ) {
                printf(
                    '<li class="emt-opt-item"><label class="emt-opt"><input type="radio" name="dificultad" value="%s"%s><span class="emt-opt__name">%s</span></label></li>',
                    esc_attr( $k ),
                    checked( $filters['dificultad'], $k, false ),
                    esc_html( $lbl )
                );
            }
        }, ( $filters['dificultad'] !== '' ) );
        ?>

        <?php if ( $has_price ) : ?>
        <div class="emt-facet is-open" data-emt-facet>
            <button type="button" class="emt-facet__head" data-facet-toggle aria-expanded="true">
                <span><?php echo esc_html( emt_t( 'precio' ) ); ?> (MXN)</span>
                <span class="emt-facet__caret" aria-hidden="true"></span>
            </button>
            <div class="emt-facet__body">
                <div class="emt-range" data-emt-range data-min="<?php echo (int) $bounds['min']; ?>" data-max="<?php echo (int) $bounds['max']; ?>">
                    <div class="emt-range__slider">
                        <span class="emt-range__track"></span>
                        <span class="emt-range__fill" data-range-fill></span>
                        <input type="range" class="emt-range__input" name="precio_min" min="<?php echo (int) $bounds['min']; ?>" max="<?php echo (int) $bounds['max']; ?>" step="100" value="<?php echo (int) $cur_pmin; ?>" data-range-min aria-label="<?php echo esc_attr( emt_t( 'precio_min_aria' ) ); ?>" />
                        <input type="range" class="emt-range__input" name="precio_max" min="<?php echo (int) $bounds['min']; ?>" max="<?php echo (int) $bounds['max']; ?>" step="100" value="<?php echo (int) $cur_pmax; ?>" data-range-max aria-label="<?php echo esc_attr( emt_t( 'precio_max_aria' ) ); ?>" />
                    </div>
                    <div class="emt-range__values"><span data-range-lo></span> – <span data-range-hi></span></div>
                </div>
                <p class="emt-range__note"><?php echo esc_html( emt_t( 'precio_sin_nota' ) ); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="emt-filters__actions">
            <button type="submit" class="emt-btn emt-btn--secondary"><?php echo esc_html( emt_t( 'aplicar_filtros' ) ); ?></button>
            <a class="emt-filters__clear" href="<?php echo esc_url( $scoped_url ); ?>" data-emt-clear><?php echo esc_html( emt_t( 'limpiar_filtros' ) ); ?></a>
        </div>
    </form>

    <div class="emt-listing__results" data-emt-results aria-busy="false">
        <div class="emt-active-filters" data-emt-active data-label="<?php echo esc_attr( emt_t( 'filtros_activos' ) ); ?>" data-clear="<?php echo esc_attr( emt_t( 'limpiar_todo' ) ); ?>" data-remove="<?php echo esc_attr( emt_t( 'quitar' ) ); ?>" hidden></div>

        <p class="emt-listing__count" data-emt-count><?php echo esc_html( emt_tour_count_label( $query->found_posts ) ); ?></p>

        <div class="emt-tours-grid" data-emt-grid>
            <?php if ( $query->have_posts() ) : ?>
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php emt_render_tour_card( get_the_ID() ); ?>
                <?php endwhile; ?>
            <?php endif; wp_reset_postdata(); ?>
        </div>

        <p class="emt-listing__empty" data-emt-empty<?php echo $query->found_posts ? ' hidden' : ''; ?>><?php echo esc_html( emt_t( 'sin_resultados' ) ); ?></p>

        <div class="emt-listing__loader" data-emt-loader aria-hidden="true"><span class="emt-spinner"></span></div>

        <!-- "Cargar más" (con JS). Sin JS se usa la paginación de abajo. -->
        <div class="emt-listing__more">
            <button type="button" class="emt-btn emt-btn--secondary emt-loadmore" data-emt-loadmore data-page="<?php echo (int) $paged; ?>" data-max="<?php echo (int) $query->max_num_pages; ?>" hidden><?php echo esc_html( emt_t( 'cargar_mas' ) ); ?></button>
        </div>

        <?php $emt_pag = paginate_links( array( 'total' => $query->max_num_pages, 'current' => $paged, 'mid_size' => 1 ) ); ?>
        <?php if ( $emt_pag ) : ?>
            <nav class="emt-pagination" data-emt-pagination aria-label="<?php echo esc_attr( emt_t( 'paginacion' ) ); ?>">
                <?php echo wp_kses_post( $emt_pag ); ?>
            </nav>
        <?php endif; ?>
    </div>
</div>
