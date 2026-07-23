<?php
/**
 * Listado del blog (posts index / home.php). Rediseno 2026: banda de archivo del
 * sistema + chips de categoria + destacado + grid. Sobrescribe el de Hello Elementor.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<section class="emt-archive-hero emt-archive-hero--plain">
    <div class="emt-container emt-archive-hero__inner">
        <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
        <div class="emt-heading emt-heading--left emt-archive-hero__heading">
            <span class="emt-eyebrow"><?php echo esc_html( emt_t( 'blog_eyebrow' ) ); ?></span>
            <h1 class="emt-title emt-archive-hero__title"><?php echo esc_html( emt_t( 'blog_titulo' ) ); ?></h1>
            <p class="emt-heading__sub emt-archive-hero__sub"><?php echo esc_html( emt_t( 'blog_sub' ) ); ?></p>
        </div>
    </div>
</section>

<section class="emt-section emt-blog-archive">
    <div class="emt-container">
        <?php echo emt_blog_cats_nav(); ?>
        <?php emt_render_blog_loop( true ); ?>
    </div>
</section>
<?php
get_footer();
