<?php
/**
 * Home (doc maestro §8.1). Usa componentes de Fase B (header/footer/tour-card).
 * El hero estacional y el widget de reviews son de Fase D; aquí queda el hero
 * institucional y la estructura base.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_lang   = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$emt_prefix = ( $emt_lang === 'en' ) ? '/en' : '';

get_header();
?>

<!-- 1. Hero institucional (hero estacional configurable: Fase D) -->
<section class="emt-hero">
    <div class="emt-container emt-hero__inner">
        <p class="emt-hero__eyebrow"><?php echo esc_html( emt_t( 'hero_eyebrow' ) ); ?></p>
        <h1 class="emt-hero__title"><?php echo esc_html( emt_t( 'hero_title' ) ); ?></h1>
        <p class="emt-hero__sub"><?php echo esc_html( emt_t( 'hero_sub' ) ); ?></p>
        <div class="emt-hero__cta">
            <a class="emt-btn emt-btn--cta" href="<?php echo esc_url( home_url( $emt_prefix . '/tours/' ) ); ?>"><?php echo esc_html( emt_t( 'ver_todos' ) ); ?></a>
            <a class="emt-btn emt-btn--secondary" href="<?php echo esc_url( home_url( $emt_prefix . '/cotizacion/' ) ); ?>"><?php echo esc_html( emt_t( 'cotizar_grupo' ) ); ?></a>
        </div>
    </div>
</section>

<!-- 2. Destinos destacados -->
<?php
$destinos = get_terms( array( 'taxonomy' => 'tour_destino', 'hide_empty' => false, 'parent' => 0, 'number' => 5 ) );
if ( $destinos && ! is_wp_error( $destinos ) ) : ?>
<section class="emt-home-section">
    <div class="emt-container">
        <header class="emt-section-head">
            <span class="emt-section-head__eyebrow"><?php echo esc_html( emt_t( 'a_donde_ir' ) ); ?></span>
            <h2 class="emt-section-head__title"><?php echo esc_html( emt_t( 'destinos_destacados' ) ); ?></h2>
        </header>
        <ul class="emt-destinos-grid">
            <?php foreach ( $destinos as $d ) :
                $link = get_term_link( $d ); ?>
                <li class="emt-destino-card">
                    <a href="<?php echo esc_url( is_wp_error( $link ) ? '#' : $link ); ?>">
                        <span class="emt-destino-card__bg" aria-hidden="true"></span>
                        <span class="emt-destino-card__name"><?php echo esc_html( $d->name ); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- 3. Tours destacados (destacado = true) -->
<?php
$destacados = new WP_Query( array(
    'post_type'      => 'tour',
    'posts_per_page' => 8,
    'meta_key'       => 'destacado',
    'meta_value'     => '1',
    'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
    'no_found_rows'  => true,
) );
if ( $destacados->have_posts() ) : ?>
<section class="emt-home-section emt-home-section--alt">
    <div class="emt-container">
        <header class="emt-section-head">
            <h2 class="emt-section-head__title"><?php echo esc_html( emt_t( 'tours_imperdibles' ) ); ?></h2>
        </header>
        <div class="emt-tours-grid">
            <?php while ( $destacados->have_posts() ) : $destacados->the_post(); ?>
                <?php emt_render_tour_card( get_the_ID() ); ?>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; wp_reset_postdata(); ?>

<!-- 4. Explora por experiencia -->
<?php
$experiencias = get_terms( array( 'taxonomy' => 'tour_experiencia', 'hide_empty' => false, 'number' => 6 ) );
if ( $experiencias && ! is_wp_error( $experiencias ) ) : ?>
<section class="emt-home-section">
    <div class="emt-container">
        <header class="emt-section-head">
            <h2 class="emt-section-head__title"><?php echo esc_html( emt_t( 'explora_experiencia' ) ); ?></h2>
        </header>
        <ul class="emt-exp-grid">
            <?php foreach ( $experiencias as $x ) :
                $link = get_term_link( $x ); ?>
                <li><a class="emt-exp-card" href="<?php echo esc_url( is_wp_error( $link ) ? '#' : $link ); ?>"><?php echo esc_html( $x->name ); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- 5. Trayectoria (trust line) -->
<section class="emt-trust">
    <div class="emt-container emt-trust__grid">
        <div class="emt-trust__item"><span class="emt-trust__num">20</span><span class="emt-trust__label"><?php echo esc_html( emt_t( 'trust_anios' ) ); ?></span></div>
        <div class="emt-trust__item"><span class="emt-trust__num">70</span><span class="emt-trust__label"><?php echo esc_html( emt_t( 'trust_tours' ) ); ?></span></div>
        <div class="emt-trust__item"><span class="emt-trust__num">15</span><span class="emt-trust__label"><?php echo esc_html( emt_t( 'trust_destinos' ) ); ?></span></div>
        <div class="emt-trust__item"><span class="emt-trust__num">50k</span><span class="emt-trust__label"><?php echo esc_html( emt_t( 'trust_viajeros' ) ); ?></span></div>
    </div>
</section>

<!-- 6. CTA cotización grupos -->
<section class="emt-cta-banner">
    <div class="emt-container emt-cta-banner__inner">
        <h2 class="emt-cta-banner__title"><?php echo esc_html( emt_t( 'cta_grupos_title' ) ); ?></h2>
        <a class="emt-btn emt-btn--cta" href="<?php echo esc_url( home_url( $emt_prefix . '/cotizacion/' ) ); ?>"><?php echo esc_html( emt_t( 'cotizar_grupo' ) ); ?></a>
    </div>
</section>

<?php
get_footer();
