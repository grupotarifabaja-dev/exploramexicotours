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
    $imagen_header = get_field( 'imagen_header', $id );
    $incluye   = emt_get_field( 'incluye', $id );
    $no_incl   = emt_get_field( 'no_incluye', $id );
    $itin      = emt_get_field( 'itinerario', $id );
    $politica  = emt_get_field( 'politica_cancelacion', $id );
    $mapa      = get_field( 'mapa_embed', $id );
    $relacion  = get_field( 'tour_relacionados', $id );
    $fecha     = emt_get_field( 'fecha_viaje', $id );
    $precios   = function_exists( 'emt_tour_precios' ) ? emt_tour_precios( $id ) : array();
    $precios_veh = function_exists( 'emt_tour_precios_vehiculo' ) ? emt_tour_precios_vehiculo( $id ) : array();
    $precio_nota = get_field( 'precio_nota', $id );

    $destinos  = get_the_terms( $id, 'tour_destino' );
    $destino   = ( $destinos && ! is_wp_error( $destinos ) ) ? $destinos[0]->name : '';

    $cotiza_url = home_url( ( $lang === 'en' ? '/en' : '' ) . '/cotizacion/' );
    ?>
    <article class="emt-single-tour">
        <?php
        // Imagen de header: dedicada (imagen_header) -> destacada -> galería[0] -> placeholder.
        $hero_img = '';
        if ( is_array( $imagen_header ) && ! empty( $imagen_header['url'] ) ) {
            $hero_img = $imagen_header['sizes']['large'] ?? $imagen_header['url'];
        } elseif ( has_post_thumbnail( $id ) ) {
            $hero_img = get_the_post_thumbnail_url( $id, 'large' );
        } elseif ( ! empty( $galeria[0]['url'] ) ) {
            $hero_img = $galeria[0]['sizes']['large'] ?? $galeria[0]['url'];
        } else {
            $hero_img = emt_get_image_or_placeholder( $id, 'large' );
        }

        // Galería (para el lightbox "Ver galería").
        $gallery_imgs = array();
        if ( is_array( $galeria ) ) {
            foreach ( $galeria as $g ) {
                $full = $g['url'] ?? '';
                if ( ! $full ) { continue; }
                $gallery_imgs[] = array( 'thumb' => $g['sizes']['medium'] ?? $full, 'full' => $full, 'alt' => $g['alt'] ?? '' );
            }
        }
        $lb_data = array_map( function ( $im ) { return array( 'src' => $im['full'], 'alt' => $im['alt'] ); }, $gallery_imgs );
        ?>
        <!-- Hero (mismo formato que el header de destino: foto + overlay + serape) -->
        <section class="emt-tour-hero <?php echo $hero_img ? 'emt-tour-hero--photo' : 'emt-tour-hero--plain'; ?>">
            <?php if ( $hero_img ) : ?>
                <div class="emt-tour-hero__media" aria-hidden="true">
                    <img src="<?php echo esc_url( $hero_img ); ?>" alt="" />
                </div>
            <?php endif; ?>
            <div class="emt-container emt-tour-hero__inner">
                <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
                <div class="emt-heading emt-heading--left emt-tour-hero__heading">
                    <span class="emt-eyebrow"><?php echo esc_html( $destino ? $destino : emt_t( 'eyebrow_tours' ) ); ?></span>
                    <h1 class="emt-title emt-tour-hero__title"><?php echo esc_html( $titulo ); ?></h1>
                </div>
                <ul class="emt-tour-hero__meta">
                    <?php if ( $duracion ) : ?><li><?php echo emt_icon( 'clock' ); ?><span><?php echo esc_html( $duracion ); ?></span></li><?php endif; ?>
                    <?php if ( $dificultad ) : ?><li><?php echo esc_html( emt_t( 'dificultad' ) . ': ' . emt_t( $dificultad ) ); ?></li><?php endif; ?>
                    <?php if ( $fecha ) : ?><li><?php echo emt_icon( 'calendar' ); ?><span><?php echo esc_html( $fecha ); ?></span></li><?php endif; ?>
                </ul>
                <div class="emt-tour-hero__flags">
                    <?php if ( $garantia ) : ?><span class="emt-flag emt-flag--ok"><?php echo esc_html( emt_t( 'salida_garantizada' ) ); ?></span><?php endif; ?>
                    <?php if ( $pickup ) : ?><span class="emt-flag emt-flag--pickup"><?php echo esc_html( emt_t( 'pickup_hotel' ) ); ?></span><?php endif; ?>
                </div>
            </div>
        </section>

        <div class="emt-container">

            <?php if ( ! empty( $gallery_imgs ) ) : ?>
                <div class="emt-tour-gallery-strip" data-gallery>
                    <ul class="emt-tour-gallery-strip__grid">
                        <?php foreach ( $gallery_imgs as $gi => $g ) : ?>
                            <li><button type="button" class="emt-tour-gallery-strip__thumb" data-gallery-open="<?php echo (int) $gi; ?>" aria-label="<?php echo esc_attr( emt_t( 'ver_galeria' ) . ' ' . ( $gi + 1 ) ); ?>">
                                <img src="<?php echo esc_url( $g['thumb'] ); ?>" alt="<?php echo esc_attr( $g['alt'] ); ?>" loading="lazy" decoding="async" />
                            </button></li>
                        <?php endforeach; ?>
                    </ul>
                    <script type="application/json" data-gallery-data><?php echo wp_json_encode( $lb_data ); ?></script>
                </div>
            <?php endif; ?>

            <div class="emt-tour-body">
                <!-- Columna principal -->
                <div class="emt-tour-main">
                    <div class="emt-tour-desc"><?php
                        $desc_en = ( $lang === 'en' ) ? get_field( 'descripcion_en', $id ) : '';
                        if ( ! empty( $desc_en ) ) { echo wp_kses_post( $desc_en ); } else { the_content(); }
                    ?></div>

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

                    <?php if ( $precios_veh ) : ?>
                        <section class="emt-tour-precios">
                            <h2><?php echo esc_html( emt_t( 'precios' ) ); ?></h2>
                            <table class="emt-precios-tabla">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html( emt_t( 'capacidad' ) ); ?></th>
                                        <th><?php echo esc_html( emt_t( 'vehiculo' ) ); ?></th>
                                        <th><?php echo esc_html( emt_t( 'precio_persona' ) ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $precios_veh as $row ) : ?>
                                        <tr>
                                            <td><?php echo esc_html( $row['capacidad'] ); ?></td>
                                            <td><?php echo esc_html( $row['vehiculo'] ); ?></td>
                                            <td class="emt-precios-tabla__precio"><?php echo $row['precio'] !== null ? esc_html( emt_format_price( $row['precio'] ) ) : esc_html( emt_t( 'consultar' ) ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if ( $precio_nota && ! $precios ) : ?>
                                <p class="emt-precios-nota"><?php echo esc_html( $precio_nota ); ?></p>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>

                    <?php if ( is_array( $itin ) && $itin ) : ?>
                        <section class="emt-tour-itin">
                            <h2><?php echo esc_html( emt_t( 'itinerario' ) ); ?></h2>
                            <?php foreach ( $itin as $paso ) : ?>
                                <details class="emt-itin__item">
                                    <summary><?php echo esc_html( trim( ( isset( $paso['dia'] ) ? emt_t( 'dia_label' ) . ' ' . $paso['dia'] . ' · ' : '' ) . ( $paso['hora'] ?? '' ) . ' ' . ( $paso['titulo'] ?? $paso['titulo_en'] ?? '' ) ) ); ?></summary>
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
                        <?php else : ?>
                            <p class="emt-reserve-card__price emt-reserve-card__price--consultar"><strong><?php echo esc_html( emt_t( 'consultar_precio' ) ); ?></strong></p>
                        <?php endif; ?>
                        <?php
                        // CTA primario: reservar por WhatsApp con mensaje prellenado (bilingüe).
                        $wa_num = function_exists( 'get_field' ) ? preg_replace( '/\D/', '', (string) get_field( 'wa_number', 'option' ) ) : '';
                        if ( $wa_num === '' ) { $wa_num = '523310480670'; }
                        $wa_msg = ( $lang === 'en' )
                            ? "Hi, I'm interested in the {$titulo} tour"
                            : "Hola, me interesa el tour {$titulo}";
                        $wa_url = 'https://wa.me/' . $wa_num . '?text=' . rawurlencode( $wa_msg );
                        ?>
                        <a href="<?php echo esc_url( $wa_url ); ?>" class="emt-btn emt-btn--cta emt-reserve-card__wa" data-tour-id="<?php echo esc_attr( $id ); ?>" target="_blank" rel="noopener"><?php echo esc_html( emt_t( 'reservar_whatsapp' ) ); ?></a>
                        <?php if ( $peek && $peek !== '#' ) : ?>
                            <a href="<?php echo esc_url( $peek ); ?>" class="emt-btn emt-btn--secondary emt-btn--peek" data-tour-id="<?php echo esc_attr( $id ); ?>" data-tour-title="<?php echo esc_attr( $titulo ); ?>" target="_blank" rel="noopener"><?php echo esc_html( emt_t( 'reservar_ahora' ) ); ?></a>
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

    <!-- Lightbox de galería (controlado por assets/js/tour-gallery.js) -->
    <div class="emt-lightbox" data-lightbox hidden role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( emt_t( 'galeria' ) ); ?>">
        <button type="button" class="emt-lightbox__close" data-lb-close aria-label="<?php echo esc_attr( emt_t( 'cerrar' ) ); ?>">&times;</button>
        <button type="button" class="emt-lightbox__nav emt-lightbox__nav--prev" data-lb-prev aria-label="<?php echo esc_attr( emt_t( 'anterior' ) ); ?>">&#8249;</button>
        <figure class="emt-lightbox__stage">
            <img class="emt-lightbox__img" data-lb-img src="" alt="" />
        </figure>
        <button type="button" class="emt-lightbox__nav emt-lightbox__nav--next" data-lb-next aria-label="<?php echo esc_attr( emt_t( 'siguiente' ) ); ?>">&#8250;</button>
        <div class="emt-lightbox__counter" data-lb-counter aria-live="polite"></div>
    </div>
    <?php
endwhile;

get_footer();
