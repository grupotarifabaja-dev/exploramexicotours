<?php
/**
 * Cuerpo compartido de las plantillas de taxonomía de tours (doc maestro §8.2/§7.1).
 * Lo incluyen taxonomy-tour_destino/categoria/experiencia.php.
 * Acota el listado al término consultado vía $emt_listing_base.
 *
 * Rediseño 2026: el header adopta el sistema de diseño. Destino usa foto
 * editable (campo `imagen_destino`, con respaldo a la foto de un tour del
 * destino) con overlay para texto legible; categoría y experiencia usan el
 * heading serape sobre sección oscura. Puesto/eyebrow por taxonomía + contador
 * de tours del término.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_term = get_queried_object();
$emt_tax  = isset( $emt_term->taxonomy ) ? $emt_term->taxonomy : '';

// Foto solo en destinos (campo editable imagen_destino, respaldo a foto de tour).
$emt_hero_img = ( $emt_tax === 'tour_destino' && function_exists( 'emt_destino_image_url' ) )
    ? emt_destino_image_url( $emt_term, 'large' )
    : '';

// Eyebrow por taxonomía.
$emt_eyebrow_keys = array(
    'tour_destino'     => 'eyebrow_destino',
    'tour_categoria'   => 'eyebrow_categoria',
    'tour_experiencia' => 'eyebrow_experiencia',
);
$emt_eyebrow = emt_t( $emt_eyebrow_keys[ $emt_tax ] ?? 'eyebrow_tours' );
$emt_count   = isset( $emt_term->count ) ? (int) $emt_term->count : 0;

get_header();
?>
<section class="emt-archive-hero <?php echo $emt_hero_img ? 'emt-archive-hero--photo' : 'emt-archive-hero--plain'; ?>">
    <?php if ( $emt_hero_img ) : ?>
        <div class="emt-archive-hero__media" aria-hidden="true">
            <img src="<?php echo esc_url( $emt_hero_img ); ?>" alt="" />
        </div>
    <?php endif; ?>
    <div class="emt-container emt-archive-hero__inner">
        <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
        <div class="emt-heading emt-heading--left emt-archive-hero__heading">
            <span class="emt-eyebrow"><?php echo esc_html( $emt_eyebrow ); ?></span>
            <h1 class="emt-title emt-archive-hero__title"><?php echo esc_html( $emt_term->name ); ?></h1>
            <?php if ( ! empty( $emt_term->description ) ) : ?>
                <p class="emt-heading__sub emt-archive-hero__sub"><?php echo esc_html( $emt_term->description ); ?></p>
            <?php endif; ?>
        </div>
        <?php if ( $emt_count > 0 ) : ?>
            <p class="emt-archive-hero__count"><?php printf( esc_html( emt_t( 'n_tours' ) ), $emt_count ); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php
$emt_listing_base = array(
    array(
        'taxonomy' => $emt_term->taxonomy,
        'field'    => 'term_id',
        'terms'    => array( (int) $emt_term->term_id ),
    ),
);
include get_stylesheet_directory() . '/parts/tour-listing.php';

get_footer();
