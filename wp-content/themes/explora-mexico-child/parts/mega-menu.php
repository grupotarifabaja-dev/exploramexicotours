<?php
/**
 * Parte: Mega-menú (doc maestro §7.3) — rediseño 2026.
 *
 * AUTOMÁTICO: los tres paneles (destinos, categorías, experiencias) se arman
 * SIEMPRE desde sus taxonomías, mostrando solo términos con tours publicados
 * (hide_empty) y ordenados por número de tours. Así, al publicar un tour nuevo
 * el menú se actualiza solo — sin listas manuales que mantener.
 *
 * La imagen de cada tarjeta usa la cascada editable: imagen del término
 * (imagen_destino, editable en wp-admin) → foto de un tour del término →
 * degradado azul. Contador "N tours" por término. Se incluye desde parts/header.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_lang   = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$emt_prefix = ( $emt_lang === 'en' ) ? '/en' : '';
$emt_tours_url = home_url( $emt_prefix . '/tours/' );

if ( ! function_exists( 'emt_mega_items' ) ) {
    /**
     * Items de un panel del mega-menú, siempre desde la taxonomía.
     *
     * @param string $taxonomy Taxonomía (tour_destino/categoria/experiencia).
     * @param int    $limit    Máximo de items.
     * @return array<int,array{nombre:string,url:string,imagen:string,count:int}>
     */
    function emt_mega_items( $taxonomy, $limit = 10 ) {
        $items = array();
        if ( ! taxonomy_exists( $taxonomy ) ) {
            return $items;
        }
        $terms = get_terms( array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,   // solo términos con tours → el menú refleja lo publicado
            'number'     => $limit,
            'orderby'    => 'count',
            'order'      => 'DESC',
        ) );
        if ( is_wp_error( $terms ) || ! $terms ) {
            return $items;
        }
        foreach ( $terms as $t ) {
            $link = get_term_link( $t );
            $img  = function_exists( 'emt_destino_image_url' ) ? emt_destino_image_url( $t, 'medium' ) : '';
            $items[] = array(
                'nombre' => $t->name,
                'url'    => is_wp_error( $link ) ? '#' : $link,
                'imagen' => $img,
                'count'  => (int) $t->count,
            );
        }
        return $items;
    }
}

/**
 * Render de un panel del mega-menú con tarjetas de foto + overlay del sistema.
 *
 * @param string $id            destinos | categorias | experiencias
 * @param array  $items         Items de emt_mega_items().
 * @param string $ver_todos_url URL del enlace "Ver todos".
 * @return void
 */
function emt_render_mega_panel( $id, $items, $ver_todos_url ) {
    ?>
    <div class="emt-mega" id="emt-mega-<?php echo esc_attr( $id ); ?>" data-mega-panel="<?php echo esc_attr( $id ); ?>" hidden>
        <div class="emt-container">
            <?php if ( ! empty( $items ) ) : ?>
                <div class="emt-mega__grid">
                    <?php foreach ( $items as $item ) : ?>
                        <a class="emt-mega__card emt-img-overlay<?php echo empty( $item['imagen'] ) ? ' emt-mega__card--noimg' : ''; ?>" href="<?php echo esc_url( $item['url'] ); ?>">
                            <?php if ( ! empty( $item['imagen'] ) ) : ?>
                                <span class="emt-mega__img" style="background-image:url('<?php echo esc_url( $item['imagen'] ); ?>');" aria-hidden="true"></span>
                            <?php endif; ?>
                            <span class="emt-img-overlay__content emt-mega__content">
                                <span class="emt-mega__name"><?php echo esc_html( $item['nombre'] ); ?></span>
                                <?php if ( $item['count'] > 0 ) : ?>
                                    <span class="emt-mega__count"><?php printf( esc_html( emt_t( 'n_tours' ) ), $item['count'] ); ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <a class="emt-mega__all" href="<?php echo esc_url( $ver_todos_url ); ?>"><?php echo esc_html( emt_t( 'ver_todos' ) ); ?> <span aria-hidden="true">&rarr;</span></a>
            <?php else : ?>
                <p class="emt-mega__empty"><?php echo esc_html( emt_t( 'sin_resultados' ) ); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

emt_render_mega_panel( 'destinos', emt_mega_items( 'tour_destino' ), $emt_tours_url );
emt_render_mega_panel( 'categorias', emt_mega_items( 'tour_categoria' ), $emt_tours_url );
emt_render_mega_panel( 'experiencias', emt_mega_items( 'tour_experiencia' ), $emt_tours_url );
