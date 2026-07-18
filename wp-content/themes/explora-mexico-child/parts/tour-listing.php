<?php
/**
 * Parte reutilizable: filtros + grid de tours (doc maestro §8.2).
 * Usado por archive-tour.php y las plantillas taxonomy-tour_*.php.
 *
 * Filtrado con AJAX + "cargar más" (assets/js/tour-filter.js) y degradación a
 * GET sin JS. La lógica de filtros vive en inc/tour-filters.php (compartida con
 * el handler AJAX). Variable opcional en scope: $emt_listing_base (tax_query fija).
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

// Datos para los controles.
$tx_destino = get_terms( array( 'taxonomy' => 'tour_destino', 'hide_empty' => false ) );
$tx_cat     = get_terms( array( 'taxonomy' => 'tour_categoria', 'hide_empty' => false ) );
$tx_exp     = get_terms( array( 'taxonomy' => 'tour_experiencia', 'hide_empty' => false ) );
$difs       = array( 'facil' => 'Fácil', 'moderada' => 'Moderada', 'alta' => 'Alta' );
$grupos_dur = emt_tour_duracion_grupos();
$bounds     = emt_tour_price_bounds();
$has_price  = ( $bounds['max'] > $bounds['min'] );
$cur_pmin   = $filters['precio_min'] !== null ? $filters['precio_min'] : $bounds['min'];
$cur_pmax   = $filters['precio_max'] !== null ? $filters['precio_max'] : $bounds['max'];
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
        // Filtros como chips del sistema: el checkbox/radio va oculto dentro del
        // .emt-chip y el estado seleccionado lo pinta :has(:checked) (components.css).
        // El JS de filtros (tour-filter.js) sigue operando sobre los inputs reales.
        $group = function ( $title, $name, $terms, $selected ) {
            if ( is_wp_error( $terms ) || ! $terms ) { return; }
            echo '<fieldset class="emt-filters__group"><legend>' . esc_html( $title ) . '</legend><div class="emt-filters__chips">';
            foreach ( $terms as $t ) {
                printf(
                    '<label class="emt-chip"><input class="emt-chip__input" type="checkbox" name="%s[]" value="%d"%s><span>%s</span></label>',
                    esc_attr( $name ), (int) $t->term_id,
                    in_array( $t->term_id, $selected, true ) ? ' checked' : '',
                    esc_html( $t->name )
                );
            }
            echo '</div></fieldset>';
        };
        $group( emt_t( 'destinos' ), 'destino', $tx_destino, $filters['destino'] );
        $group( emt_t( 'categorias' ), 'categoria', $tx_cat, $filters['categoria'] );
        $group( emt_t( 'experiencias' ), 'experiencia', $tx_exp, $filters['experiencia'] );
        ?>

        <fieldset class="emt-filters__group"><legend><?php echo esc_html( emt_t( 'duracion' ) ); ?></legend>
            <div class="emt-filters__chips">
                <?php foreach ( $grupos_dur as $val => $g ) : ?>
                    <label class="emt-chip"><input class="emt-chip__input" type="checkbox" name="duracion[]" value="<?php echo esc_attr( $val ); ?>"<?php echo in_array( $val, $filters['duracion'], true ) ? ' checked' : ''; ?>><span><?php echo esc_html( $g['label'] ); ?></span></label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <fieldset class="emt-filters__group"><legend><?php echo esc_html( emt_t( 'dificultad' ) ); ?></legend>
            <div class="emt-filters__chips">
                <label class="emt-chip"><input class="emt-chip__input" type="radio" name="dificultad" value=""<?php checked( $filters['dificultad'], '' ); ?>><span><?php echo esc_html( emt_t( 'todas' ) ); ?></span></label>
                <?php foreach ( $difs as $k => $lbl ) : ?>
                    <label class="emt-chip"><input class="emt-chip__input" type="radio" name="dificultad" value="<?php echo esc_attr( $k ); ?>"<?php checked( $filters['dificultad'], $k ); ?>><span><?php echo esc_html( $lbl ); ?></span></label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <?php if ( $has_price ) : ?>
        <fieldset class="emt-filters__group emt-filters__group--price"><legend><?php echo esc_html( emt_t( 'precio' ) ); ?> (MXN)</legend>
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
        </fieldset>
        <?php endif; ?>

        <div class="emt-filters__actions">
            <button type="submit" class="emt-btn emt-btn--secondary"><?php echo esc_html( emt_t( 'aplicar_filtros' ) ); ?></button>
            <a class="emt-filters__clear" href="<?php echo esc_url( $scoped_url ); ?>" data-emt-clear><?php echo esc_html( emt_t( 'limpiar_filtros' ) ); ?></a>
        </div>
    </form>

    <div class="emt-listing__results" data-emt-results aria-busy="false">
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
