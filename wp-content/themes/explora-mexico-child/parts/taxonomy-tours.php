<?php
/**
 * Cuerpo compartido de las plantillas de taxonomía de tours (doc maestro §8.2/§7.1).
 * Lo incluyen taxonomy-tour_destino/categoria/experiencia.php.
 * Acota el listado al término consultado vía $emt_listing_base.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_term = get_queried_object();

get_header();
?>
<section class="emt-archive-hero">
    <div class="emt-container">
        <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
        <h1 class="emt-archive-hero__title"><?php echo esc_html( $emt_term->name ); ?></h1>
        <?php if ( ! empty( $emt_term->description ) ) : ?>
            <p class="emt-archive-hero__sub"><?php echo esc_html( $emt_term->description ); ?></p>
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
