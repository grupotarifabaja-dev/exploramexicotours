<?php
/**
 * Listado de tours (doc maestro §8.2). Reutiliza parts/tour-listing.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<section class="emt-archive-hero">
    <div class="emt-container">
        <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
        <h1 class="emt-archive-hero__title"><?php echo esc_html( emt_t( 'tours' ) ); ?></h1>
        <p class="emt-archive-hero__sub"><?php echo esc_html( emt_t( 'tours_sub' ) ); ?></p>
    </div>
</section>

<?php include get_stylesheet_directory() . '/parts/tour-listing.php'; ?>

<?php
get_footer();
