<?php
/**
 * Directorio de asesores (doc maestro §8.4). Usa emt_render_asesor_card().
 * Filtro ligero por especialidad / idioma (GET). Solo asesores activos.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_lang   = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$emt_prefix = ( $emt_lang === 'en' ) ? '/en' : '';
$base_url   = home_url( $emt_prefix . '/asesores/' );

$f_esp = array_filter( array_map( 'absint', (array) ( $_GET['especialidad'] ?? array() ) ) );
$f_idi = array_filter( array_map( 'absint', (array) ( $_GET['idioma'] ?? array() ) ) );

$tax_query = array();
if ( $f_esp ) { $tax_query[] = array( 'taxonomy' => 'asesor_especialidad', 'field' => 'term_id', 'terms' => $f_esp ); }
if ( $f_idi ) { $tax_query[] = array( 'taxonomy' => 'asesor_idioma', 'field' => 'term_id', 'terms' => $f_idi ); }
if ( count( $tax_query ) > 1 ) { $tax_query['relation'] = 'AND'; }

$query = new WP_Query( array(
    'post_type'      => 'asesor',
    'posts_per_page' => 24,
    'meta_query'     => array( array( 'key' => 'activo', 'value' => '1' ) ),
    'tax_query'      => $tax_query,
) );

$tx_esp = get_terms( array( 'taxonomy' => 'asesor_especialidad', 'hide_empty' => false ) );
$tx_idi = get_terms( array( 'taxonomy' => 'asesor_idioma', 'hide_empty' => false ) );

get_header();
?>
<section class="emt-archive-hero">
    <div class="emt-container">
        <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
        <h1 class="emt-archive-hero__title"><?php echo esc_html( emt_t( 'nuestro_equipo' ) ); ?></h1>
    </div>
</section>

<div class="emt-container emt-asesor-archive">
    <form class="emt-asesor-filters" method="get" action="<?php echo esc_url( $base_url ); ?>" data-emt-filters>
        <?php
        $grp = function ( $title, $name, $terms, $sel ) {
            if ( is_wp_error( $terms ) || ! $terms ) { return; }
            echo '<fieldset class="emt-filters__group"><legend>' . esc_html( $title ) . '</legend>';
            foreach ( $terms as $t ) {
                printf( '<label class="emt-filters__opt"><input type="checkbox" name="%s[]" value="%d"%s> %s</label>', esc_attr( $name ), (int) $t->term_id, in_array( $t->term_id, $sel, true ) ? ' checked' : '', esc_html( $t->name ) );
            }
            echo '</fieldset>';
        };
        $grp( emt_t( 'especialidades' ), 'especialidad', $tx_esp, $f_esp );
        $grp( emt_t( 'idiomas' ), 'idioma', $tx_idi, $f_idi );
        ?>
        <div class="emt-filters__actions">
            <button type="submit" class="emt-btn emt-btn--secondary"><?php echo esc_html( emt_t( 'aplicar_filtros' ) ); ?></button>
            <a class="emt-filters__clear" href="<?php echo esc_url( $base_url ); ?>"><?php echo esc_html( emt_t( 'limpiar_filtros' ) ); ?></a>
        </div>
    </form>

    <?php if ( $query->have_posts() ) : ?>
        <div class="emt-asesores-grid">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <?php emt_render_asesor_card( get_the_ID() ); ?>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p class="emt-listing__empty"><?php echo esc_html( emt_t( 'sin_resultados' ) ); ?></p>
    <?php endif; wp_reset_postdata(); ?>
</div>

<?php
get_footer();
