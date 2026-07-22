<?php
/**
 * Directorio de asesores (doc maestro §8.4). Usa emt_render_asesor_card().
 *
 * Rediseño 2026 → 2026-07-22: presentación de FICHA DESTACADA (horizontal),
 * SIN filtros. El equipo es pequeño, así que filtrar por especialidad/idioma
 * no aporta; en su lugar cada asesor se presenta como una ficha centrada
 * (media + datos + contacto) vía .emt-asesores-grid--featured. Solo activos.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';

$query = new WP_Query( array(
    'post_type'      => 'asesor',
    'posts_per_page' => 24,
    'meta_query'     => array( array( 'key' => 'activo', 'value' => '1' ) ),
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
) );

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
    </div>
</section>

<section class="emt-section emt-asesor-archive">
    <div class="emt-container">
        <?php if ( $query->have_posts() ) : ?>
            <div class="emt-asesores-grid emt-asesores-grid--featured">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php emt_render_asesor_card( get_the_ID() ); ?>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="emt-empty">
                <span class="emt-empty__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></span>
                <h2 class="emt-empty__title"><?php echo esc_html( emt_t( 'empty_titulo' ) ); ?></h2>
                <p class="emt-empty__text"><?php echo esc_html( emt_t( 'empty_texto' ) ); ?></p>
            </div>
        <?php endif; wp_reset_postdata(); ?>
    </div>
</section>

<?php
get_footer();
