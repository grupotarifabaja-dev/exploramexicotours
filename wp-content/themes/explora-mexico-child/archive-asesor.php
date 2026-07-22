<?php
/**
 * Directorio de asesores (doc maestro §8.4). Usa emt_render_asesor_card().
 * Filtro ligero por especialidad / idioma (GET). Solo asesores activos.
 *
 * Rediseño 2026: adopta el sistema de diseño — .emt-heading con línea serape,
 * filtros como chips (.emt-chip con checkbox oculto, auto-submit vía
 * filter-bar.js; sin JS funciona con el botón de <noscript>) y .emt-empty
 * cuando la combinación de filtros queda en cero.
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
<section class="emt-archive-hero emt-archive-hero--plain">
    <div class="emt-container emt-archive-hero__inner">
        <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
        <div class="emt-heading emt-heading--left emt-archive-hero__heading">
            <span class="emt-eyebrow"><?php echo esc_html( emt_t( 'asesores_eyebrow' ) ); ?></span>
            <h1 class="emt-title emt-archive-hero__title"><?php echo esc_html( emt_t( 'nuestro_equipo' ) ); ?></h1>
            <p class="emt-heading__sub emt-archive-hero__sub"><?php echo esc_html( emt_t( 'asesores_sub' ) ); ?></p>
        </div>
        <?php if ( $query->found_posts > 0 ) : ?>
            <p class="emt-archive-hero__count"><?php echo esc_html( $query->found_posts . ' ' . ( $emt_lang === 'en' ? 'advisors' : 'asesores' ) ); ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="emt-section emt-asesor-archive">
    <div class="emt-container">

        <form class="emt-asesor-filters" method="get" action="<?php echo esc_url( $base_url ); ?>" data-emt-filters>
            <?php
            $grp = function ( $title, $name, $terms, $sel ) {
                if ( is_wp_error( $terms ) || ! $terms ) { return; }
                echo '<fieldset class="emt-filters__group"><legend class="emt-filters__legend">' . esc_html( $title ) . '</legend><div class="emt-filters__chips">';
                foreach ( $terms as $t ) {
                    $on = in_array( $t->term_id, $sel, true );
                    printf(
                        '<label class="emt-chip%1$s"><input class="emt-chip__input" type="checkbox" name="%2$s[]" value="%3$d"%4$s><span>%5$s</span></label>',
                        $on ? ' is-selected' : '',
                        esc_attr( $name ),
                        (int) $t->term_id,
                        $on ? ' checked' : '',
                        esc_html( $t->name )
                    );
                }
                echo '</div></fieldset>';
            };
            $grp( emt_t( 'especialidades' ), 'especialidad', $tx_esp, $f_esp );
            $grp( emt_t( 'idiomas' ), 'idioma', $tx_idi, $f_idi );
            ?>
            <div class="emt-filters__actions">
                <noscript><button type="submit" class="emt-btn emt-btn--outline"><?php echo esc_html( emt_t( 'aplicar_filtros' ) ); ?></button></noscript>
                <?php if ( $f_esp || $f_idi ) : ?>
                    <a class="emt-filters__clear" href="<?php echo esc_url( $base_url ); ?>"><?php echo esc_html( emt_t( 'limpiar_filtros' ) ); ?></a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ( $query->have_posts() ) : ?>
            <div class="emt-asesores-grid">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php emt_render_asesor_card( get_the_ID() ); ?>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="emt-empty">
                <span class="emt-empty__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></span>
                <h2 class="emt-empty__title"><?php echo esc_html( emt_t( 'empty_titulo' ) ); ?></h2>
                <p class="emt-empty__text"><?php echo esc_html( emt_t( 'empty_texto' ) ); ?></p>
                <a class="emt-btn emt-btn--outline emt-empty__action" href="<?php echo esc_url( $base_url ); ?>"><?php echo esc_html( emt_t( 'limpiar_filtros' ) ); ?></a>
            </div>
        <?php endif; wp_reset_postdata(); ?>
    </div>
</section>

<?php
get_footer();
