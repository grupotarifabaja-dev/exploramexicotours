<?php
/**
 * Parte: Tarjeta de tour (doc maestro §8.2). Componente reutilizable.
 *
 * Espera la variable $emt_card_tour (ID o WP_Post) en el scope. Úsala vía el
 * helper emt_render_tour_card( $tour ) (inc/template-helpers.php).
 *
 * Bilingüe: emt_get_field() para campos, emt_t() para textos de UI.
 * Hooks: filtro emt_tour_card_classes, acción emt_after_tour_card.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_tour = isset( $emt_card_tour ) ? $emt_card_tour : null;
$id       = ( $emt_tour instanceof WP_Post ) ? $emt_tour->ID : (int) $emt_tour;
if ( ! $id ) {
    return;
}

$lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';

// Título (bilingüe: post_title o gemelo titulo_en).
$title = get_the_title( $id );
if ( $lang === 'en' && function_exists( 'get_field' ) ) {
    $t_en = get_field( 'titulo_en', $id );
    if ( ! empty( $t_en ) ) {
        $title = $t_en;
    }
}

$permalink = get_permalink( $id );
$duracion  = function_exists( 'emt_get_field' ) ? emt_get_field( 'duracion_texto', $id ) : get_post_meta( $id, 'duracion_texto', true );
$precio    = function_exists( 'get_field' ) ? get_field( 'precio_desde', $id ) : get_post_meta( $id, 'precio_desde', true );
$garantia  = function_exists( 'get_field' ) ? get_field( 'salida_garantizada', $id ) : false;
$pickup    = function_exists( 'get_field' ) ? get_field( 'pickup_hotel', $id ) : false;

// Términos.
$destinos   = get_the_terms( $id, 'tour_destino' );
$categorias = get_the_terms( $id, 'tour_categoria' );
$destino    = ( $destinos && ! is_wp_error( $destinos ) ) ? $destinos[0]->name : '';
$categoria  = ( $categorias && ! is_wp_error( $categorias ) ) ? $categorias[0]->name : '';
$tags       = ( $categorias && ! is_wp_error( $categorias ) ) ? wp_list_pluck( $categorias, 'name' ) : array();

// Imagen (helper B7 con fallback a placeholder).
$img = function_exists( 'emt_get_image_or_placeholder' )
    ? emt_get_image_or_placeholder( $id, 'tour-card' )
    : ( has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'large' ) : '' );

$classes = apply_filters( 'emt_tour_card_classes', array( 'emt-tour-card' ), $id );
?>
<article class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $classes ) ) ); ?>">
    <a class="emt-tour-card__media" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
        <img class="emt-tour-card__image" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
        <?php if ( $categoria ) : ?>
            <span class="emt-tour-card__badge emt-tour-card__badge--cat"><?php echo esc_html( $categoria ); ?></span>
        <?php endif; ?>
        <div class="emt-tour-card__flags">
            <?php if ( $garantia ) : ?>
                <span class="emt-tour-card__flag emt-tour-card__flag--ok"><?php echo esc_html( emt_t( 'salida_garantizada' ) ); ?></span>
            <?php endif; ?>
            <?php if ( $pickup ) : ?>
                <span class="emt-tour-card__flag emt-tour-card__flag--pickup"><?php echo esc_html( emt_t( 'pickup_hotel' ) ); ?></span>
            <?php endif; ?>
        </div>
    </a>

    <div class="emt-tour-card__body">
        <h3 class="emt-tour-card__title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>

        <p class="emt-tour-card__meta">
            <?php if ( $destino ) : ?><span class="emt-tour-card__destino">📍 <?php echo esc_html( $destino ); ?></span><?php endif; ?>
            <?php if ( $duracion ) : ?><span class="emt-tour-card__dur"> · <?php echo esc_html( $duracion ); ?></span><?php endif; ?>
        </p>

        <?php if ( $tags ) : ?>
            <p class="emt-tour-card__tags">
                <?php foreach ( $tags as $tag ) : ?><span class="emt-tour-card__tag"><?php echo esc_html( $tag ); ?></span><?php endforeach; ?>
            </p>
        <?php endif; ?>

        <div class="emt-tour-card__footer">
            <?php if ( ! empty( $precio ) ) : ?>
                <p class="emt-tour-card__price">
                    <span class="emt-tour-card__price-label"><?php echo esc_html( emt_t( 'desde' ) ); ?></span>
                    <span class="emt-tour-card__price-value"><?php echo esc_html( function_exists( 'emt_format_price' ) ? emt_format_price( $precio ) : number_format_i18n( (float) $precio ) . ' MXN' ); ?></span>
                </p>
            <?php endif; ?>
            <a class="emt-btn emt-btn--secondary emt-tour-card__cta" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( emt_t( 'ver_tour' ) ); ?> →</a>
        </div>
    </div>
    <?php do_action( 'emt_after_tour_card', $id ); ?>
</article>
