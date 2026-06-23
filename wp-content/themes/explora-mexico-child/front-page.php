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

<!-- 1. Hero institucional con video de fondo (configurable en Configuración) -->
<?php
// Fondo del hero: video > imagen de respaldo > degradado azul (nunca se rompe).
$hero_video  = function_exists( 'get_field' ) ? get_field( 'hero_bg_video', 'option' ) : null;
$hero_poster = function_exists( 'get_field' ) ? get_field( 'hero_bg_poster', 'option' ) : null;
$hero_video_url  = is_array( $hero_video ) ? ( $hero_video['url'] ?? '' ) : '';
$hero_video_mime = is_array( $hero_video ) ? ( $hero_video['mime_type'] ?? 'video/mp4' ) : 'video/mp4';
$hero_poster_url = is_array( $hero_poster ) ? ( $hero_poster['url'] ?? '' ) : '';
$hero_has_media  = ( $hero_video_url || $hero_poster_url );
?>
<section class="emt-hero<?php echo $hero_has_media ? ' emt-hero--media' : ''; ?>">
    <?php if ( $hero_video_url ) : ?>
        <video class="emt-hero__media emt-hero__video" muted loop playsinline preload="none"
               <?php if ( $hero_poster_url ) : ?>poster="<?php echo esc_url( $hero_poster_url ); ?>"<?php endif; ?>
               data-hero-video data-src="<?php echo esc_url( $hero_video_url ); ?>" data-type="<?php echo esc_attr( $hero_video_mime ); ?>" aria-hidden="true"></video>
    <?php elseif ( $hero_poster_url ) : ?>
        <div class="emt-hero__media emt-hero__poster" style="background-image:url('<?php echo esc_url( $hero_poster_url ); ?>')" aria-hidden="true"></div>
    <?php endif; ?>
    <?php if ( $hero_has_media ) : ?><span class="emt-hero__overlay" aria-hidden="true"></span><?php endif; ?>
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
                $link = get_term_link( $d );
                $img  = function_exists( 'emt_destino_image_url' ) ? emt_destino_image_url( $d ) : '';
                ?>
                <li class="emt-destino-card<?php echo $img ? ' emt-destino-card--has-img' : ''; ?>">
                    <a href="<?php echo esc_url( is_wp_error( $link ) ? '#' : $link ); ?>">
                        <span class="emt-destino-card__bg"<?php if ( $img ) : ?> style="background-image:url('<?php echo esc_url( $img ); ?>')"<?php endif; ?> aria-hidden="true"></span>
                        <span class="emt-destino-card__overlay" aria-hidden="true"></span>
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
