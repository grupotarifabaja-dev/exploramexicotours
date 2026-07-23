<?php
/** Panel — barra lateral con secciones y contadores. */
if ( ! defined( 'ABSPATH' ) ) exit;
$view = sanitize_key( get_query_var( 'emt_view' ) ?: 'dashboard' );
$tc   = wp_count_posts( 'tour' );
$ac   = wp_count_posts( 'asesor' );
$bc   = wp_count_posts( 'post' );
$dc   = (int) wp_count_terms( array( 'taxonomy' => 'tour_destino', 'hide_empty' => false, 'parent' => 0 ) );
$nav  = array(
    'dashboard'     => array( 'Inicio', 'dashicons-dashboard', null ),
    'tours'         => array( 'Tours', 'dashicons-palmtree', (int) $tc->publish + (int) $tc->draft ),
    'asesores'      => array( 'Asesores', 'dashicons-businessperson', (int) $ac->publish + (int) $ac->draft ),
    'destinos'      => array( 'Destinos', 'dashicons-location-alt', $dc ),
    'blog'          => array( 'Blog', 'dashicons-admin-post', (int) $bc->publish + (int) $bc->draft ),
    'configuracion' => array( 'Configuración', 'dashicons-admin-settings', null ),
);
?>
<aside class="emt-panel__sidebar">
    <nav aria-label="Secciones del panel">
        <ul>
            <?php foreach ( $nav as $slug => $item ) : ?>
                <li class="<?php echo $view === $slug ? 'is-active' : ''; ?>">
                    <a href="<?php echo esc_url( emt_panel_url( $slug === 'dashboard' ? '' : $slug . '/' ) ); ?>">
                        <span class="dashicons <?php echo esc_attr( $item[1] ); ?>" aria-hidden="true"></span>
                        <span class="emt-panel__nav-label"><?php echo esc_html( $item[0] ); ?></span>
                        <?php if ( $item[2] !== null ) : ?><span class="emt-panel__count"><?php echo (int) $item[2]; ?></span><?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>
