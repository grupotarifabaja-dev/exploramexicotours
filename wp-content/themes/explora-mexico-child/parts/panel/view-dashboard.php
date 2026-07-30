<?php
/** Panel — inicio: estadísticas del sitio + actividad reciente. */
if ( ! defined( 'ABSPATH' ) ) exit;

$tc = wp_count_posts( 'tour' );
$ac = wp_count_posts( 'asesor' );
$bc = wp_count_posts( 'post' );

// Tours con precio vs. "Consultar" (precio_desde o tramos por vehículo con precio).
$pub_tours   = get_posts( array( 'post_type' => 'tour', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$con_precio  = 0;
foreach ( $pub_tours as $ptid ) {
    $desde = get_field( 'precio_desde', $ptid );
    if ( ! empty( $desde ) ) { $con_precio++; continue; }
    $pv = (array) get_field( 'precios_vehiculo', $ptid );
    foreach ( $pv as $row ) {
        if ( ! empty( $row['precio'] ) ) { $con_precio++; break; }
    }
}
$sin_precio = max( 0, count( $pub_tours ) - $con_precio );

// Términos.
$n_dest = wp_count_terms( array( 'taxonomy' => 'tour_destino', 'hide_empty' => false ) );
$n_exp  = wp_count_terms( array( 'taxonomy' => 'tour_experiencia', 'hide_empty' => false ) );
if ( is_wp_error( $n_dest ) ) { $n_dest = 0; }
if ( is_wp_error( $n_exp ) )  { $n_exp = 0; }

// Descargas de vCard (contador que ya registra asesor-functions).
$vcards = 0;
$ases_ids = get_posts( array( 'post_type' => 'asesor', 'post_status' => array( 'publish', 'draft' ), 'posts_per_page' => -1, 'fields' => 'ids' ) );
foreach ( $ases_ids as $aid ) { $vcards += (int) get_post_meta( $aid, '_emt_vcard_downloads', true ); }

// Actividad reciente (últimos contenidos modificados).
$recientes = get_posts( array(
    'post_type'      => array( 'tour', 'asesor', 'post' ),
    'post_status'    => array( 'publish', 'draft' ),
    'posts_per_page' => 6,
    'orderby'        => 'modified',
    'order'          => 'DESC',
) );
$tipo_lbl = array( 'tour' => 'Tour', 'asesor' => 'Asesor', 'post' => 'Blog' );
$edit_url = function ( $p ) {
    switch ( $p->post_type ) {
        case 'tour':   return emt_panel_url( 'tours/editar/' . $p->ID . '/' );
        case 'asesor': return emt_panel_url( 'asesores/editar/' . $p->ID . '/' );
        default:       return emt_panel_url( 'blog/editar/' . $p->ID . '/' );
    }
};

// Borradores pendientes de publicar.
$borradores = get_posts( array(
    'post_type'      => array( 'tour', 'asesor', 'post' ),
    'post_status'    => 'draft',
    'posts_per_page' => 6,
    'orderby'        => 'modified',
    'order'          => 'DESC',
) );
?>
<div class="emt-panel__head">
    <div>
        <h1>Inicio</h1>
        <p class="emt-panel__head-sub">Así está tu sitio hoy.</p>
    </div>
    <a class="emt-panel__btn emt-panel__btn--primary" href="<?php echo esc_url( emt_panel_url( 'tours/nuevo/' ) ); ?>">+ Nuevo Tour</a>
</div>

<div class="emt-panel__metrics">
    <a class="emt-panel__metric" href="<?php echo esc_url( emt_panel_url( 'tours/' ) ); ?>">
        <span class="emt-panel__metric-num"><?php echo (int) $tc->publish; ?></span>
        <span class="emt-panel__metric-label">Tours publicados</span>
        <?php if ( (int) $tc->draft > 0 ) : ?><span class="emt-panel__metric-sub"><?php echo (int) $tc->draft; ?> en borrador</span><?php endif; ?>
    </a>
    <a class="emt-panel__metric" href="<?php echo esc_url( emt_panel_url( 'tours/' ) ); ?>">
        <span class="emt-panel__metric-num"><?php echo (int) $con_precio; ?></span>
        <span class="emt-panel__metric-label">Tours con precio</span>
        <?php if ( $sin_precio > 0 ) : ?><span class="emt-panel__metric-sub"><?php echo (int) $sin_precio; ?> en "Consultar precio"</span><?php endif; ?>
    </a>
    <a class="emt-panel__metric" href="<?php echo esc_url( emt_panel_url( 'asesores/' ) ); ?>">
        <span class="emt-panel__metric-num"><?php echo (int) $ac->publish; ?></span>
        <span class="emt-panel__metric-label">Asesores activos</span>
        <?php if ( $vcards > 0 ) : ?><span class="emt-panel__metric-sub"><?php echo (int) $vcards; ?> contactos descargados</span><?php endif; ?>
    </a>
    <a class="emt-panel__metric" href="<?php echo esc_url( emt_panel_url( 'blog/' ) ); ?>">
        <span class="emt-panel__metric-num"><?php echo (int) $bc->publish; ?></span>
        <span class="emt-panel__metric-label">Entradas de blog</span>
        <?php if ( (int) $bc->draft > 0 ) : ?><span class="emt-panel__metric-sub"><?php echo (int) $bc->draft; ?> en borrador</span><?php endif; ?>
    </a>
    <a class="emt-panel__metric" href="<?php echo esc_url( emt_panel_url( 'destinos/' ) ); ?>">
        <span class="emt-panel__metric-num"><?php echo (int) $n_dest; ?></span>
        <span class="emt-panel__metric-label">Destinos</span>
        <span class="emt-panel__metric-sub"><?php echo (int) $n_exp; ?> experiencias</span>
    </a>
</div>

<div class="emt-dash-cols">
    <section class="emt-panel__card">
        <h2>Actividad reciente</h2>
        <?php if ( $recientes ) : ?>
            <ul class="emt-dash-list">
                <?php foreach ( $recientes as $p ) : ?>
                    <li>
                        <span class="emt-dash-list__main">
                            <span class="emt-dash-tag emt-dash-tag--<?php echo esc_attr( $p->post_type ); ?>"><?php echo esc_html( $tipo_lbl[ $p->post_type ] ?? $p->post_type ); ?></span>
                            <a href="<?php echo esc_url( $edit_url( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a>
                        </span>
                        <span class="emt-dash-when">hace <?php echo esc_html( human_time_diff( get_post_modified_time( 'U', false, $p ), current_time( 'timestamp' ) ) ); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p class="emt-panel__muted">Sin actividad todavía.</p>
        <?php endif; ?>
    </section>

    <section class="emt-panel__card">
        <h2>Pendientes de publicar</h2>
        <?php if ( $borradores ) : ?>
            <ul class="emt-dash-list">
                <?php foreach ( $borradores as $p ) : ?>
                    <li>
                        <span class="emt-dash-list__main">
                            <span class="emt-dash-tag emt-dash-tag--<?php echo esc_attr( $p->post_type ); ?>"><?php echo esc_html( $tipo_lbl[ $p->post_type ] ?? $p->post_type ); ?></span>
                            <a href="<?php echo esc_url( $edit_url( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ?: '(sin título)' ); ?></a>
                        </span>
                        <span class="emt-panel__status emt-panel__status--draft">Borrador</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p class="emt-panel__muted">Todo publicado ✓</p>
        <?php endif; ?>
    </section>
</div>
