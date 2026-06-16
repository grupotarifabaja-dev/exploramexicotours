<?php
/**
 * Parte: Tarjeta de asesor (doc maestro §8.4). Componente reutilizable.
 *
 * Espera $emt_card_asesor (ID o WP_Post). Úsala vía emt_render_asesor_card().
 * Solo renderiza si el asesor tiene activo = true.
 * Puesto bilingüe vía emt_get_field(); WhatsApp directo con el número del asesor.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_asesor = isset( $emt_card_asesor ) ? $emt_card_asesor : null;
$id         = ( $emt_asesor instanceof WP_Post ) ? $emt_asesor->ID : (int) $emt_asesor;
if ( ! $id ) {
    return;
}

// Respeta el flag activo: no muestra inactivos.
$activo = function_exists( 'get_field' ) ? get_field( 'activo', $id ) : true;
if ( ! $activo ) {
    return;
}

$nombre    = get_the_title( $id );
$permalink = get_permalink( $id );
$puesto    = function_exists( 'emt_get_field' ) ? emt_get_field( 'puesto', $id ) : get_post_meta( $id, 'puesto', true );
$whatsapp  = function_exists( 'get_field' ) ? get_field( 'whatsapp', $id ) : get_post_meta( $id, 'whatsapp', true );
$whatsapp  = preg_replace( '/\D/', '', (string) $whatsapp );

$idiomas        = get_the_terms( $id, 'asesor_idioma' );
$especialidades = get_the_terms( $id, 'asesor_especialidad' );

$img = function_exists( 'emt_get_image_or_placeholder' )
    ? emt_get_image_or_placeholder( $id, 'asesor-portrait' )
    : ( has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'large' ) : '' );
?>
<article class="emt-asesor-card">
    <a class="emt-asesor-card__media" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
        <img class="emt-asesor-card__photo" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $nombre ); ?>" loading="lazy" />
    </a>
    <div class="emt-asesor-card__body">
        <h3 class="emt-asesor-card__name"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $nombre ); ?></a></h3>
        <?php if ( $puesto ) : ?><p class="emt-asesor-card__role"><?php echo esc_html( $puesto ); ?></p><?php endif; ?>

        <?php if ( $idiomas && ! is_wp_error( $idiomas ) ) : ?>
            <ul class="emt-asesor-card__langs">
                <?php foreach ( $idiomas as $l ) : ?><li class="emt-asesor-card__lang"><?php echo esc_html( $l->name ); ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ( $especialidades && ! is_wp_error( $especialidades ) ) : ?>
            <ul class="emt-asesor-card__specs">
                <?php foreach ( $especialidades as $s ) : ?><li class="emt-asesor-card__spec"><?php echo esc_html( $s->name ); ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="emt-asesor-card__actions">
            <?php if ( $whatsapp ) : ?>
                <a class="emt-btn emt-btn--whatsapp" href="https://wa.me/<?php echo esc_attr( $whatsapp ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
            <?php endif; ?>
            <a class="emt-btn emt-btn--secondary" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( emt_t( 'ver_perfil' ) ); ?></a>
        </div>
    </div>
</article>
