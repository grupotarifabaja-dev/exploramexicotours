<?php
/**
 * Listado de tours (doc maestro §8.2). Reutiliza parts/tour-listing.php.
 * Rediseño 2026: header con la estructura del sistema (eyebrow + serape),
 * misma plantilla visual que las taxonomías (variante sin foto = oscura).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<section class="emt-archive-hero emt-archive-hero--plain">
    <div class="emt-container emt-archive-hero__inner">
        <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
        <div class="emt-heading emt-heading--left emt-archive-hero__heading">
            <span class="emt-eyebrow"><?php echo esc_html( emt_t( 'eyebrow_tours' ) ); ?></span>
            <h1 class="emt-title emt-archive-hero__title"><?php echo esc_html( emt_t( 'tours' ) ); ?></h1>
            <p class="emt-heading__sub emt-archive-hero__sub"><?php echo esc_html( emt_t( 'tours_sub' ) ); ?></p>
        </div>
    </div>
</section>

<?php include get_stylesheet_directory() . '/parts/tour-listing.php'; ?>

<?php
get_footer();
