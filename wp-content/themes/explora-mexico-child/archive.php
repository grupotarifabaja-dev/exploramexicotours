<?php
/**
 * Archivos de blog (categoria, etiqueta, fecha, autor, busqueda). Reusa el
 * layout del listado con el sistema de diseno. Sobrescribe el de Hello Elementor.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$emt_cur_cat = is_category() ? (int) get_queried_object_id() : 0;
$emt_title   = is_category() ? single_cat_title( '', false )
             : ( is_tag() ? single_tag_title( '', false )
             : ( is_search() ? esc_html( emt_t( 'buscar' ) ) : wp_strip_all_tags( get_the_archive_title() ) ) );
?>
<section class="emt-archive-hero emt-archive-hero--plain">
    <div class="emt-container emt-archive-hero__inner">
        <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
        <div class="emt-heading emt-heading--left emt-archive-hero__heading">
            <span class="emt-eyebrow"><?php echo esc_html( emt_t( 'blog_eyebrow' ) ); ?></span>
            <h1 class="emt-title emt-archive-hero__title"><?php echo esc_html( $emt_title ); ?></h1>
            <?php $emt_desc = get_the_archive_description(); if ( $emt_desc ) : ?>
                <p class="emt-heading__sub emt-archive-hero__sub"><?php echo wp_kses_post( $emt_desc ); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="emt-section emt-blog-archive">
    <div class="emt-container">
        <?php echo emt_blog_cats_nav( $emt_cur_cat ); ?>
        <?php emt_render_blog_loop( false ); ?>
    </div>
</section>
<?php
get_footer();
