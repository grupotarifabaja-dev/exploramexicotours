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

<?php if ( function_exists( 'emt_stats_total' ) ) :
    $v_hoy  = emt_stats_total( 'visitas', 1 );
    $v_7    = emt_stats_total( 'visitas', 7 );
    $v_30   = emt_stats_total( 'visitas', 30 );
    $wa_30  = emt_stats_total( 'clic_whatsapp', 30 ) + emt_stats_total( 'solicitud_disp', 30 ) + emt_stats_total( 'solicitud_info', 30 );
    $sol_30 = emt_stats_total( 'solicitud_disp', 30 ) + emt_stats_total( 'solicitud_info', 30 );
    $for_30 = emt_stats_total( 'form_cotizacion', 30 ) + emt_stats_total( 'form_contacto', 30 );
    $serie  = emt_stats_serie( 'visitas', 14 );
    $s_max  = max( 1, max( $serie ) );
    // Tours más vistos (total acumulado).
    $top_tours = get_posts( array( 'post_type' => 'tour', 'post_status' => 'publish', 'posts_per_page' => 5, 'meta_key' => '_emt_views', 'orderby' => 'meta_value_num', 'order' => 'DESC' ) );
    $top_tours = array_filter( $top_tours, function ( $p ) { return (int) get_post_meta( $p->ID, '_emt_views', true ) > 0; } );
?>
<section class="emt-panel__card emt-stats-hero">
    <h2>Actividad de visitantes <span class="emt-stats-hero__badge">últimos 30 días</span></h2>
    <div class="emt-stats-hero__grid">
        <div class="emt-stat-big emt-stat-big--rosa">
            <span class="emt-stat-big__num"><?php echo number_format_i18n( $v_30 ); ?></span>
            <span class="emt-stat-big__label">Visitas al sitio</span>
            <span class="emt-stat-big__sub">Hoy: <?php echo number_format_i18n( $v_hoy ); ?> · 7 días: <?php echo number_format_i18n( $v_7 ); ?></span>
        </div>
        <div class="emt-stat-big emt-stat-big--verde">
            <span class="emt-stat-big__num"><?php echo number_format_i18n( $wa_30 ); ?></span>
            <span class="emt-stat-big__label">Clicks a WhatsApp</span>
            <span class="emt-stat-big__sub">Mensajes iniciados desde el sitio</span>
        </div>
        <div class="emt-stat-big emt-stat-big--azul">
            <span class="emt-stat-big__num"><?php echo number_format_i18n( $sol_30 ); ?></span>
            <span class="emt-stat-big__label">Solicitudes del cotizador</span>
            <span class="emt-stat-big__sub">Disponibilidad + más información</span>
        </div>
        <div class="emt-stat-big emt-stat-big--turquesa">
            <span class="emt-stat-big__num"><?php echo number_format_i18n( $for_30 ); ?></span>
            <span class="emt-stat-big__label">Formularios enviados</span>
            <span class="emt-stat-big__sub">Cotización + contacto</span>
        </div>
    </div>
    <div class="emt-stats-chart" role="img" aria-label="Visitas de los últimos 14 días">
        <?php foreach ( $serie as $f => $n ) :
            $h = max( 4, (int) round( $n / $s_max * 100 ) ); ?>
            <div class="emt-stats-chart__col" title="<?php echo esc_attr( date_i18n( 'j M', strtotime( $f ) ) . ': ' . $n . ' visitas' ); ?>">
                <span class="emt-stats-chart__bar" style="height:<?php echo $h; ?>%"></span>
                <span class="emt-stats-chart__day"><?php echo esc_html( date_i18n( 'd', strtotime( $f ) ) ); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ( $top_tours ) : ?>
        <div class="emt-stats-top">
            <h3>Tours más vistos</h3>
            <ol>
                <?php foreach ( $top_tours as $p ) : ?>
                    <li><a href="<?php echo esc_url( emt_panel_url( 'tours/editar/' . $p->ID . '/' ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a> <span><?php echo number_format_i18n( (int) get_post_meta( $p->ID, '_emt_views', true ) ); ?> vistas</span></li>
                <?php endforeach; ?>
            </ol>
        </div>
    <?php endif; ?>
    <p class="emt-stats-hero__note">Medición propia del sitio (no cuenta al equipo ni robots). Empezó a registrar el <?php echo esc_html( date_i18n( 'j \d\e F \d\e Y', strtotime( get_option( 'emt_stats_inicio' ) ?: current_time( 'Y-m-d' ) ) ) ); ?>.</p>
    <?php if ( ! get_option( 'emt_stats_inicio' ) ) { update_option( 'emt_stats_inicio', current_time( 'Y-m-d' ), false ); } ?>
</section>
<?php endif; ?>

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
