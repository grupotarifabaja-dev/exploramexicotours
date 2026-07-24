<?php
/**
 * Listado del blog (ruta /blog/). Query propia + paginación. Reusa los helpers
 * y estilos del blog (emt_render_blog_card, blog.css).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$emt_lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$emt_pfx  = ( $emt_lang === 'en' ) ? '/en' : '';
$paged    = max( 1, (int) get_query_var( 'emt_blog_pag' ) );

$q = new WP_Query( array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => 12,
    'paged'               => $paged,
    'ignore_sticky_posts' => 1,
) );

get_header();
?>
<section class="emt-archive-hero emt-archive-hero--plain">
    <div class="emt-container emt-archive-hero__inner">
        <nav class="emt-breadcrumbs" aria-label="Breadcrumb">
            <ol class="emt-breadcrumbs__list">
                <li class="emt-breadcrumbs__item"><a href="<?php echo esc_url( home_url( $emt_pfx . '/' ) ); ?>"><?php echo esc_html( emt_t( 'inicio' ) ); ?></a></li>
                <li class="emt-breadcrumbs__item"><span aria-current="page"><?php echo esc_html( emt_t( 'blog' ) ); ?></span></li>
            </ol>
        </nav>
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
        <?php if ( $q->have_posts() ) :
            $i = 0; $grid_open = false;
            while ( $q->have_posts() ) : $q->the_post();
                if ( 0 === $i && 1 === $paged ) {
                    emt_render_blog_card( get_the_ID(), true );
                } else {
                    if ( ! $grid_open ) { echo '<div class="emt-post-grid">'; $grid_open = true; }
                    emt_render_blog_card( get_the_ID(), false );
                }
                $i++;
            endwhile;
            if ( $grid_open ) { echo '</div>'; }

            $links = paginate_links( array(
                'base'      => trailingslashit( home_url( $emt_pfx . '/blog/' ) ) . 'page/%#%/',
                'format'    => '',
                'current'   => $paged,
                'total'     => (int) $q->max_num_pages,
                'mid_size'  => 1,
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
            ) );
            if ( $links ) {
                echo '<div class="emt-post-pager"><div class="nav-links">' . $links . '</div></div>';
            }
            wp_reset_postdata();
        else : ?>
            <div class="emt-empty">
                <h2 class="emt-empty__title"><?php echo esc_html( emt_t( 'sin_entradas_titulo' ) ); ?></h2>
                <p class="emt-empty__text"><?php echo esc_html( emt_t( 'sin_entradas_texto' ) ); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
get_footer();
