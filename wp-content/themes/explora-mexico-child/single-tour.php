<?php
/**
 * Ficha de tour (doc maestro §8.3). Bilingüe vía emt_get_field().
 * Schema TouristTrip lo inyecta inc/seo-schema.php en <head>.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) :
    the_post();
    $id   = get_the_ID();
    $lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';

    $titulo = get_the_title();
    if ( $lang === 'en' ) {
        $t_en = get_field( 'titulo_en', $id );
        if ( ! empty( $t_en ) ) { $titulo = $t_en; }
    }

    $precio    = get_field( 'precio_desde', $id );
    $peek      = get_field( 'peek_url', $id );
    $duracion  = emt_get_field( 'duracion_texto', $id );
    $dificultad= get_field( 'dificultad', $id );
    $garantia  = get_field( 'salida_garantizada', $id );
    $pickup    = get_field( 'pickup_hotel', $id );
    $galeria   = get_field( 'galeria', $id );
    $incluye   = emt_get_field( 'incluye', $id );
    $no_incl   = emt_get_field( 'no_incluye', $id );
    $itin      = emt_get_field( 'itinerario', $id );
    $politica  = emt_get_field( 'politica_cancelacion', $id );
    $mapa      = get_field( 'mapa_embed', $id );
    $relacion  = get_field( 'tour_relacionados', $id );
    $fecha     = get_field( 'fecha_viaje', $id );
    $precios   = function_exists( 'emt_tour_precios' ) ? emt_tour_precios( $id ) : array();
    $precio_nota = get_field( 'precio_nota', $id );

    $destinos  = get_the_terms( $id, 'tour_destino' );
    $destino   = ( $destinos && ! is_wp_error( $destinos ) ) ? $destinos[0]->name : '';

    $cotiza_url = home_url( ( $lang === 'en' ? '/en' : '' ) . '/cotizacion/' );
    ?>
    <article class="emt-single-tour">
        <div class="emt-container">
            <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>

            <!-- Hero -->
            <header class="emt-tour-hero">
                <div class="emt-tour-gallery">
                    <?php
                    $main = has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'large' ) : ( ! empty( $galeria[0]['url'] ) ? $galeria[0]['url'] : emt_get_image_or_placeholder( $id, 'large' ) );
                    ?>
                    <img class="emt-tour-gallery__main" src="<?php echo esc_url( $main ); ?>" alt="<?php echo esc_attr( $titulo ); ?>" />
                    <?php if ( is_array( $galeria ) && count( $galeria ) > 1 ) : ?>
                        <div class="emt-tour-gallery__thumbs">
                            <?php foreach ( array_slice( $galeria, 0, 4 ) as $g ) : ?>
                                <img src="<?php echo esc_url( $g['sizes']['thumbnail'] ?? $g['url'] ); ?>" alt="" loading="lazy" />
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h1 class="emt-tour-hero__title"><?php echo esc_html( $titulo ); ?></h1>
                <ul class="emt-tour-hero__meta">
                    <?php if ( $destino ) : ?><li>📍 <?php echo esc_html( $destino ); ?></li><?php endif; ?>
                    <?php if ( $duracion ) : ?><li>⏱ <?php echo esc_html( $duracion ); ?></li><?php endif; ?>
                    <?php if ( $dificultad ) : ?><li><?php echo esc_html( emt_t( 'dificultad' ) . ': ' . $dificultad ); ?></li><?php endif; ?>
                    <?php if ( $fecha ) : ?><li>📅 <?php echo esc_html( $fecha ); ?></li><?php endif; ?>
                </ul>
                <div class="emt-tour-hero__flags">
                    <?php if ( $garantia ) : ?><span class="emt-flag emt-flag--ok"><?php echo esc_html( emt_t( 'salida_garantizada' ) ); ?></span><?php endif; ?>
                    <?php if ( $pickup ) : ?><span class="emt-flag emt-flag--pickup"><?php echo esc_html( emt_t( 'pickup_hotel' ) ); ?></span><?php endif; ?>
                </div>
            </header>

            <div class="emt-tour-body">
                <!-- Columna principal -->
                <div class="emt-tour-main">
                    <div class="emt-tour-desc"><?php the_content(); ?></div>

                    <?php if ( $precios ) : ?>
                        <section class="emt-tour-precios">
                            <h2><?php echo esc_html( emt_t( 'precios' ) ); ?></h2>
                            <table class="emt-precios-tabla">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html( emt_t( 'ocupacion' ) ); ?></th>
                                        <th><?php echo esc_html( emt_t( 'precio_persona' ) ); ?></th>
                                        <th><?php echo esc_html( emt_t( 'disponibilidad' ) ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $precios as $row ) : ?>
                                        <tr>
                                            <td><?php echo esc_html( emt_t( $row['label'] ) ); ?></td>
                                            <td class="emt-precios-tabla__precio"><?php echo esc_html( emt_format_price( $row['precio'] ) ); ?></td>
                                            <td><?php echo $row['disp'] > 0 ? esc_html( $row['disp'] . ' ' . emt_t( 'asientos' ) ) : '—'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if ( $precio_nota ) : ?>
                                <p class="emt-precios-nota"><?php echo esc_html( $precio_nota ); ?></p>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>

                    <?php if ( is_array( $itin ) && $itin ) : ?>
                        <section class="emt-tour-itin">
                            <h2><?php echo esc_html( emt_t( 'itinerario' ) ); ?></h2>
                            <?php foreach ( $itin as $paso ) : ?>
                                <details class="emt-itin__item">
                                    <summary><?php echo esc_html( trim( ( isset( $paso['dia'] ) ? 'Día ' . $paso['dia'] . ' · ' : '' ) . ( $paso['hora'] ?? '' ) . ' ' . ( $paso['titulo'] ?? $paso['titulo_en'] ?? '' ) ) ); ?></summary>
                                    <?php if ( ! empty( $paso['descripcion'] ) || ! empty( $paso['descripcion_en'] ) ) : ?>
                                        <p><?php echo esc_html( $paso['descripcion'] ?? $paso['descripcion_en'] ); ?></p>
                                    <?php endif; ?>
                                </details>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <?php if ( is_array( $incluye ) && $incluye ) : ?>
                        <section class="emt-tour-incluye">
                            <h2><?php echo esc_html( emt_t( 'incluye' ) ); ?></h2>
                            <ul class="emt-list emt-list--ok">
                                <?php foreach ( $incluye as $it ) : ?><li><?php echo esc_html( $it['texto'] ?? '' ); ?></li><?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <?php if ( is_array( $no_incl ) && $no_incl ) : ?>
                        <section class="emt-tour-noincluye">
                            <h2><?php echo esc_html( emt_t( 'no_incluye' ) ); ?></h2>
                            <ul class="emt-list emt-list--no">
                                <?php foreach ( $no_incl as $it ) : ?><li><?php echo esc_html( $it['texto'] ?? '' ); ?></li><?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <?php if ( $mapa ) : ?>
                        <section class="emt-tour-map">
                            <h2><?php echo esc_html( emt_t( 'punto_salida' ) ); ?></h2>
                            <div class="emt-tour-map__embed"><iframe src="<?php echo esc_url( $mapa ); ?>" loading="lazy" title="Mapa"></iframe></div>
                        </section>
                    <?php endif; ?>

                    <?php if ( $politica ) : ?>
                        <section class="emt-tour-politica">
                            <h2><?php echo esc_html( emt_t( 'politica_cancelacion' ) ); ?></h2>
                            <div><?php echo wp_kses_post( $politica ); ?></div>
                        </section>
                    <?php endif; ?>
                </div>

                <!-- Sidebar de reserva -->
                <aside class="emt-tour-aside">
                    <div class="emt-reserve-card">
                        <?php if ( ! empty( $precio ) ) : ?>
                            <p class="emt-reserve-card__price"><span><?php echo esc_html( emt_t( 'desde' ) ); ?></span><strong><?php echo esc_html( emt_format_price( $precio ) ); ?></strong></p>
                        <?php endif; ?>
                        <?php if ( $peek ) : ?>
                            <a href="<?php echo esc_url( $peek ); ?>" class="emt-btn emt-btn--cta emt-btn--peek" data-tour-id="<?php echo esc_attr( $id ); ?>" data-tour-title="<?php echo esc_attr( $titulo ); ?>" target="_blank" rel="noopener"><?php echo esc_html( emt_t( 'reservar_ahora' ) ); ?></a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( $cotiza_url ); ?>" class="emt-btn emt-btn--secondary"><?php echo esc_html( emt_t( 'solicitar_cotizacion' ) ); ?></a>
                    </div>
                </aside>
            </div>

            <!-- Tours relacionados -->
            <?php if ( is_array( $relacion ) && $relacion ) : ?>
                <section class="emt-tour-related">
                    <h2><?php echo esc_html( emt_t( 'tours_relacionados' ) ); ?></h2>
                    <div class="emt-tours-grid">
                        <?php foreach ( array_slice( $relacion, 0, 4 ) as $rel ) : ?>
                            <?php emt_render_tour_card( $rel ); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </article>
    <?php
endwhile;

get_footer();
