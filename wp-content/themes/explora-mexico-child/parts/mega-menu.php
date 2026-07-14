<?php
/**
 * Parte: Mega-menú (doc maestro §7.3).
 * Tres paneles: destinos, categorías (con imagen) y experiencias (lineal).
 * Datos desde la Options page (§6.4); fallback a términos de taxonomía.
 *
 * Se incluye desde parts/header.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'emt_mega_items' ) ) {
    /**
     * Devuelve items para un panel del mega-menú.
     * Prioriza la Options page; si está vacía, cae a términos de la taxonomía.
     *
     * @param string|null $options_field Campo repeater de la options page (o null).
     * @param string      $taxonomy      Taxonomía de fallback.
     * @param int         $limit         Máximo de items.
     * @return array<int,array{nombre:string,url:string,imagen:?array,fallback:bool}>
     */
    function emt_mega_items( $options_field, $taxonomy, $limit = 8 ) {
        $items = array();

        if ( $options_field && function_exists( 'get_field' ) ) {
            $rows = get_field( $options_field, 'option' );
            if ( is_array( $rows ) ) {
                foreach ( $rows as $r ) {
                    if ( empty( $r['nombre'] ) ) {
                        continue;
                    }
                    $items[] = array(
                        'nombre'   => $r['nombre'],
                        'url'      => isset( $r['url'] ) ? $r['url'] : '#',
                        'imagen'   => isset( $r['imagen'] ) ? $r['imagen'] : null,
                        'orden'    => isset( $r['orden'] ) ? (int) $r['orden'] : 99,
                        'fallback' => false,
                    );
                }
            }
        }

        if ( empty( $items ) && taxonomy_exists( $taxonomy ) ) {
            $terms = get_terms( array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'number'     => $limit,
            ) );
            if ( ! is_wp_error( $terms ) ) {
                foreach ( $terms as $t ) {
                    $link = get_term_link( $t );
                    // Misma cascada que las cards del home: imagen del término
                    // (imagen_destino) -> foto destacada de un tour con el
                    // término -> null (placeholder degradado del CSS).
                    $img_url = function_exists( 'emt_destino_image_url' ) ? emt_destino_image_url( $t, 'medium' ) : '';
                    $items[] = array(
                        'nombre'   => $t->name,
                        'url'      => is_wp_error( $link ) ? '#' : $link,
                        'imagen'   => $img_url ? array( 'url' => $img_url ) : null,
                        'orden'    => 99,
                        'fallback' => true,
                    );
                }
            }
        }

        usort( $items, function ( $a, $b ) {
            return $a['orden'] <=> $b['orden'];
        } );

        return array_slice( $items, 0, $limit );
    }
}

/**
 * Render de un panel visual (destinos / categorías).
 */
function emt_render_mega_panel( $id, $items ) {
    ?>
    <div class="emt-mega" id="emt-mega-<?php echo esc_attr( $id ); ?>" data-mega-panel="<?php echo esc_attr( $id ); ?>" hidden>
        <div class="emt-container">
            <ul class="emt-mega__grid">
                <?php foreach ( $items as $item ) : ?>
                    <li class="emt-mega__item">
                        <a class="emt-mega__link" href="<?php echo esc_url( $item['url'] ); ?>">
                            <?php if ( ! empty( $item['imagen']['url'] ) ) : ?>
                                <span class="emt-mega__img" style="background-image:url('<?php echo esc_url( $item['imagen']['url'] ); ?>');"></span>
                            <?php else : ?>
                                <span class="emt-mega__img emt-mega__img--placeholder" aria-hidden="true"></span>
                            <?php endif; ?>
                            <span class="emt-mega__name"><?php echo esc_html( $item['nombre'] ); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if ( empty( $items ) ) : ?>
                    <li class="emt-mega__empty"><?php echo esc_html( emt_t( 'sin_resultados' ) ); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php
}

$emt_mega_destinos     = emt_mega_items( 'mega_menu_destinos', 'tour_destino' );
$emt_mega_categorias   = emt_mega_items( null, 'tour_categoria' );
$emt_mega_experiencias = emt_mega_items( 'mega_menu_experiencias', 'tour_experiencia' );

emt_render_mega_panel( 'destinos', $emt_mega_destinos );
emt_render_mega_panel( 'categorias', $emt_mega_categorias );
?>
<div class="emt-mega emt-mega--linear" id="emt-mega-experiencias" data-mega-panel="experiencias" hidden>
    <div class="emt-container">
        <ul class="emt-mega__linear">
            <?php foreach ( $emt_mega_experiencias as $item ) : ?>
                <li><a class="emt-mega__chip" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['nombre'] ); ?></a></li>
            <?php endforeach; ?>
            <?php if ( empty( $emt_mega_experiencias ) ) : ?>
                <li class="emt-mega__empty"><?php echo esc_html( emt_t( 'sin_resultados' ) ); ?></li>
            <?php endif; ?>
        </ul>
    </div>
</div>
