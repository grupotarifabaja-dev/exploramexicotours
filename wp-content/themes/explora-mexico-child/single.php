<?php
/**
 * Entrada individual del blog (single.php). Rediseno 2026: portada con foto +
 * overlay, cuerpo legible, etiquetas y "Sigue leyendo". Sobrescribe el de Hello Elementor.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) :
    the_post();
    $pid       = get_the_ID();
    $cats      = get_the_category();
    $cat       = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0] : null;
    $has_cover = has_post_thumbnail( $pid );
    $cover     = emt_get_image_or_placeholder( $pid, 'full' );
    $autor     = get_the_author();
    $partes    = preg_split( '/\s+/', trim( $autor ) );
    $iniciales = strtoupper( mb_substr( $partes[0], 0, 1 ) . ( isset( $partes[1] ) ? mb_substr( $partes[1], 0, 1 ) : '' ) );
    ?>
    <article class="emt-single-post">
        <header class="emt-post-hero<?php echo $has_cover ? ' emt-post-hero--photo' : ''; ?>"<?php echo $has_cover ? ' style="background-image:url(' . esc_url( $cover ) . ')"' : ''; ?>>
            <div class="emt-container emt-post-hero__in">
                <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
                <?php if ( $cat ) : ?>
                    <a class="emt-post-badge" href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
                <?php endif; ?>
                <h1 class="emt-post-hero__title"><?php the_title(); ?></h1>
                <div class="emt-post-hero__meta">
                    <span class="emt-post-author"><span class="emt-post-author__av" aria-hidden="true"><?php echo esc_html( $iniciales ); ?></span> <?php echo esc_html( emt_t( 'por_autor' ) . ' ' . $autor ); ?></span>
                    <span class="emt-post-meta__dot"></span> <?php echo esc_html( get_the_date() ); ?>
                    <span class="emt-post-meta__dot"></span> <?php echo esc_html( emt_read_minutes( $pid ) . ' ' . emt_t( 'min_lectura' ) ); ?>
                </div>
            </div>
        </header>

        <div class="emt-post-article">
            <?php the_content(); ?>

            <?php $tags = get_the_tags(); if ( $tags && ! is_wp_error( $tags ) ) : ?>
                <div class="emt-post-tags">
                    <?php foreach ( $tags as $t ) : ?>
                        <a class="emt-post-tag" href="<?php echo esc_url( get_tag_link( $t->term_id ) ); ?>">#<?php echo esc_html( $t->name ); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php
        if ( $cat ) :
            $rel = new WP_Query( array(
                'post_type'           => 'post',
                'posts_per_page'      => 3,
                'post__not_in'        => array( $pid ),
                'category__in'        => array( $cat->term_id ),
                'ignore_sticky_posts' => 1,
                'no_found_rows'       => true,
            ) );
            if ( $rel->have_posts() ) : ?>
                <section class="emt-section emt-section--tint emt-post-related">
                    <div class="emt-container">
                        <div class="emt-heading emt-heading--left">
                            <span class="emt-eyebrow"><?php echo esc_html( emt_t( 'blog_eyebrow' ) ); ?></span>
                            <h2 class="emt-title"><?php echo esc_html( emt_t( 'sigue_leyendo' ) ); ?></h2>
                        </div>
                        <div class="emt-post-grid">
                            <?php while ( $rel->have_posts() ) : $rel->the_post(); emt_render_blog_card( get_the_ID(), false ); endwhile; wp_reset_postdata(); ?>
                        </div>
                    </div>
                </section>
            <?php endif;
        endif;
        ?>
    </article>
    <?php
endwhile;

get_footer();
