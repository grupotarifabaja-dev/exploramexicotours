<?php
/**
 * Parte reutilizable: filtros + grid de tours (doc maestro §8.2).
 * Usado por archive-tour.php y las plantillas taxonomy-tour_*.php.
 *
 * Variable opcional en scope: $emt_listing_base = array de tax_query a fijar
 * (p. ej. en una taxonomía, para acotar a su término).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_lang   = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$emt_prefix = ( $emt_lang === 'en' ) ? '/en' : '';
$base_url   = home_url( $emt_prefix . '/tours/' );

// Lectura de filtros (GET, saneados).
$f_destino = array_filter( array_map( 'absint', (array) ( $_GET['destino'] ?? array() ) ) );
$f_cat     = array_filter( array_map( 'absint', (array) ( $_GET['categoria'] ?? array() ) ) );
$f_exp     = array_filter( array_map( 'absint', (array) ( $_GET['experiencia'] ?? array() ) ) );
$f_dif     = isset( $_GET['dificultad'] ) ? sanitize_key( $_GET['dificultad'] ) : '';
$f_precio  = isset( $_GET['precio_max'] ) ? absint( $_GET['precio_max'] ) : 0;
$f_q       = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
$paged     = max( 1, (int) get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( $_GET['paged'] ?? 1 ) );

// tax_query.
$tax_query = isset( $emt_listing_base ) && is_array( $emt_listing_base ) ? $emt_listing_base : array();
if ( $f_destino ) { $tax_query[] = array( 'taxonomy' => 'tour_destino', 'field' => 'term_id', 'terms' => $f_destino ); }
if ( $f_cat )     { $tax_query[] = array( 'taxonomy' => 'tour_categoria', 'field' => 'term_id', 'terms' => $f_cat ); }
if ( $f_exp )     { $tax_query[] = array( 'taxonomy' => 'tour_experiencia', 'field' => 'term_id', 'terms' => $f_exp ); }
if ( count( $tax_query ) > 1 ) { $tax_query['relation'] = 'AND'; }

// meta_query.
$meta_query = array();
if ( $f_dif )    { $meta_query[] = array( 'key' => 'dificultad', 'value' => $f_dif ); }
if ( $f_precio ) { $meta_query[] = array( 'key' => 'precio_desde', 'value' => $f_precio, 'type' => 'NUMERIC', 'compare' => '<=' ); }
if ( count( $meta_query ) > 1 ) { $meta_query['relation'] = 'AND'; }

$query = new WP_Query( array(
    'post_type'      => 'tour',
    'posts_per_page' => 9,
    'paged'          => $paged,
    's'              => $f_q,
    'tax_query'      => $tax_query,
    'meta_query'     => $meta_query,
) );

// Términos para los filtros.
$tx_destino = get_terms( array( 'taxonomy' => 'tour_destino', 'hide_empty' => false ) );
$tx_cat     = get_terms( array( 'taxonomy' => 'tour_categoria', 'hide_empty' => false ) );
$tx_exp     = get_terms( array( 'taxonomy' => 'tour_experiencia', 'hide_empty' => false ) );
$difs       = array( 'facil' => 'Fácil', 'moderada' => 'Moderada', 'alta' => 'Alta' );
?>
<div class="emt-container emt-listing">
    <form class="emt-filters" method="get" action="<?php echo esc_url( $base_url ); ?>" data-emt-filters>
        <div class="emt-filters__search">
            <input type="search" name="q" value="<?php echo esc_attr( $f_q ); ?>" placeholder="<?php echo esc_attr( emt_t( 'buscar' ) ); ?>" />
        </div>

        <?php
        $group = function ( $title, $name, $terms, $selected ) {
            if ( is_wp_error( $terms ) || ! $terms ) { return; }
            echo '<fieldset class="emt-filters__group"><legend>' . esc_html( $title ) . '</legend>';
            foreach ( $terms as $t ) {
                printf(
                    '<label class="emt-filters__opt"><input type="checkbox" name="%s[]" value="%d"%s> %s</label>',
                    esc_attr( $name ), (int) $t->term_id,
                    in_array( $t->term_id, $selected, true ) ? ' checked' : '',
                    esc_html( $t->name )
                );
            }
            echo '</fieldset>';
        };
        $group( emt_t( 'destinos' ), 'destino', $tx_destino, $f_destino );
        $group( emt_t( 'categorias' ), 'categoria', $tx_cat, $f_cat );
        $group( emt_t( 'experiencias' ), 'experiencia', $tx_exp, $f_exp );
        ?>

        <fieldset class="emt-filters__group"><legend><?php echo esc_html( emt_t( 'dificultad' ) ); ?></legend>
            <label class="emt-filters__opt"><input type="radio" name="dificultad" value=""<?php checked( $f_dif, '' ); ?>> <?php echo esc_html( emt_t( 'todas' ) ); ?></label>
            <?php foreach ( $difs as $k => $lbl ) : ?>
                <label class="emt-filters__opt"><input type="radio" name="dificultad" value="<?php echo esc_attr( $k ); ?>"<?php checked( $f_dif, $k ); ?>> <?php echo esc_html( $lbl ); ?></label>
            <?php endforeach; ?>
        </fieldset>

        <fieldset class="emt-filters__group"><legend><?php echo esc_html( emt_t( 'precio_max' ) ); ?></legend>
            <input type="number" name="precio_max" min="0" step="100" value="<?php echo $f_precio ? esc_attr( $f_precio ) : ''; ?>" placeholder="MXN" />
        </fieldset>

        <div class="emt-filters__actions">
            <button type="submit" class="emt-btn emt-btn--secondary"><?php echo esc_html( emt_t( 'aplicar_filtros' ) ); ?></button>
            <a class="emt-filters__clear" href="<?php echo esc_url( $base_url ); ?>"><?php echo esc_html( emt_t( 'limpiar_filtros' ) ); ?></a>
        </div>
    </form>

    <div class="emt-listing__results">
        <p class="emt-listing__count"><?php printf( esc_html( emt_t( 'resultados_n' ) ), (int) $query->found_posts ); ?></p>

        <?php if ( $query->have_posts() ) : ?>
            <div class="emt-tours-grid">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php emt_render_tour_card( get_the_ID() ); ?>
                <?php endwhile; ?>
            </div>
            <?php $emt_pag = paginate_links( array( 'total' => $query->max_num_pages, 'current' => $paged, 'mid_size' => 1 ) ); ?>
            <?php if ( $emt_pag ) : ?>
                <nav class="emt-pagination" aria-label="<?php echo esc_attr( emt_t( 'paginacion' ) ); ?>">
                    <?php echo wp_kses_post( $emt_pag ); ?>
                </nav>
            <?php endif; ?>
        <?php else : ?>
            <p class="emt-listing__empty"><?php echo esc_html( emt_t( 'sin_resultados' ) ); ?></p>
        <?php endif; wp_reset_postdata(); ?>
    </div>
</div>
