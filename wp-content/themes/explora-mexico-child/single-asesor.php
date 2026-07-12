<?php
/**
 * Perfil de asesor (doc maestro §8.5). Bilingüe vía emt_get_field().
 * Acciones: WhatsApp, llamar, email, descargar vCard, QR (contiene la vCard).
 * Person schema lo inyecta inc/seo-schema.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) :
    the_post();
    $id     = get_the_ID();
    $nombre = get_the_title();
    $puesto = emt_get_field( 'puesto', $id );
    $bio    = emt_get_field( 'bio_corta', $id );
    $tel    = get_field( 'telefono', $id );
    $wa     = preg_replace( '/\D/', '', (string) get_field( 'whatsapp', $id ) );
    $email  = get_field( 'email', $id );
    $vcard  = function_exists( 'emt_asesor_vcard_url' ) ? emt_asesor_vcard_url( $id ) : '';

    $idiomas = get_the_terms( $id, 'asesor_idioma' );
    $especs  = get_the_terms( $id, 'asesor_especialidad' );
    $foto    = emt_get_image_or_placeholder( $id, 'large' );
    ?>
    <article class="emt-single-asesor">
        <div class="emt-container">
            <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>

            <header class="emt-asesor-hero">
                <div class="emt-asesor-hero__media">
                    <img src="<?php echo esc_url( $foto ); ?>" alt="<?php echo esc_attr( $nombre ); ?>" />
                </div>
                <div class="emt-asesor-hero__info">
                    <h1 class="emt-asesor-hero__name"><?php echo esc_html( $nombre ); ?></h1>
                    <?php if ( $puesto ) : ?><p class="emt-asesor-hero__role"><?php echo esc_html( $puesto ); ?></p><?php endif; ?>
                    <?php if ( $bio ) : ?><p class="emt-asesor-hero__bio"><?php echo esc_html( $bio ); ?></p><?php endif; ?>

                    <?php if ( $idiomas && ! is_wp_error( $idiomas ) ) : ?>
                        <ul class="emt-asesor-card__langs"><?php foreach ( $idiomas as $l ) : ?><li class="emt-asesor-card__lang"><?php echo esc_html( $l->name ); ?></li><?php endforeach; ?></ul>
                    <?php endif; ?>
                    <?php if ( $especs && ! is_wp_error( $especs ) ) : ?>
                        <ul class="emt-asesor-card__specs"><?php foreach ( $especs as $s ) : ?><li class="emt-asesor-card__spec"><?php echo esc_html( $s->name ); ?></li><?php endforeach; ?></ul>
                    <?php endif; ?>

                    <div class="emt-asesor-actions">
                        <?php if ( $wa ) : ?><a class="emt-btn emt-btn--whatsapp" href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a><?php endif; ?>
                        <?php if ( $tel ) : ?><a class="emt-btn emt-btn--secondary" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $tel ) ); ?>"><?php echo esc_html( emt_t( 'llamar' ) ); ?></a><?php endif; ?>
                        <?php if ( $email ) : ?><a class="emt-btn emt-btn--secondary" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( emt_t( 'enviar_email' ) ); ?></a><?php endif; ?>
                        <?php if ( $vcard ) : ?><a class="emt-btn emt-btn--cta" href="<?php echo esc_url( $vcard ); ?>"><?php echo esc_html( emt_t( 'descargar_vcard' ) ); ?></a><?php endif; ?>
                    </div>

                    <?php if ( $vcard ) : ?>
                        <div class="emt-asesor-qr" data-vcard-url="<?php echo esc_url( $vcard ); ?>" aria-label="QR vCard">
                            <noscript><a href="<?php echo esc_url( $vcard ); ?>">vCard</a></noscript>
                        </div>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Otros asesores del equipo -->
            <?php
            $otros = new WP_Query( array(
                'post_type'      => 'asesor',
                'posts_per_page' => 3,
                'post__not_in'   => array( $id ),
                'meta_query'     => array( array( 'key' => 'activo', 'value' => '1' ) ),
                'orderby'        => 'rand',
                'no_found_rows'  => true,
            ) );
            if ( $otros->have_posts() ) : ?>
                <section class="emt-asesor-otros">
                    <h2><?php echo esc_html( emt_t( 'otros_asesores' ) ); ?></h2>
                    <div class="emt-asesores-grid">
                        <?php while ( $otros->have_posts() ) : $otros->the_post(); ?>
                            <?php emt_render_asesor_card( get_the_ID() ); ?>
                        <?php endwhile; ?>
                    </div>
                </section>
            <?php endif; wp_reset_postdata(); ?>
        </div>
    </article>
    <?php
endwhile;

get_footer();
