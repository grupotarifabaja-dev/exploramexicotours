<?php
/** Panel — dashboard con métricas y accesos rápidos. */
if ( ! defined( 'ABSPATH' ) ) exit;
$tc = wp_count_posts( 'tour' );
$ac = wp_count_posts( 'asesor' );
?>
<div class="emt-panel__head">
    <div>
        <h1>Inicio</h1>
        <p class="emt-panel__head-sub">Resumen de tu contenido.</p>
    </div>
    <a class="emt-panel__btn emt-panel__btn--primary" href="<?php echo esc_url( emt_panel_url( 'tours/nuevo/' ) ); ?>">+ Nuevo Tour</a>
</div>

<div class="emt-panel__metrics">
    <a class="emt-panel__metric" href="<?php echo esc_url( emt_panel_url( 'tours/' ) ); ?>">
        <span class="emt-panel__metric-num"><?php echo (int) $tc->publish; ?></span>
        <span class="emt-panel__metric-label">Tours publicados</span>
    </a>
    <a class="emt-panel__metric" href="<?php echo esc_url( emt_panel_url( 'tours/' ) ); ?>">
        <span class="emt-panel__metric-num"><?php echo (int) $tc->draft; ?></span>
        <span class="emt-panel__metric-label">Tours en borrador</span>
    </a>
    <a class="emt-panel__metric" href="<?php echo esc_url( emt_panel_url( 'asesores/' ) ); ?>">
        <span class="emt-panel__metric-num"><?php echo (int) $ac->publish + (int) $ac->draft; ?></span>
        <span class="emt-panel__metric-label">Asesores</span>
    </a>
</div>

<section class="emt-panel__card">
    <h2>Accesos rápidos</h2>
    <div class="emt-panel__quick">
        <a class="emt-panel__btn emt-panel__btn--primary" href="<?php echo esc_url( emt_panel_url( 'tours/nuevo/' ) ); ?>">+ Nuevo Tour</a>
        <a class="emt-panel__btn" href="<?php echo esc_url( emt_panel_url( 'tours/' ) ); ?>">Gestionar Tours</a>
        <a class="emt-panel__btn" href="<?php echo esc_url( emt_panel_url( 'asesores/' ) ); ?>">Gestionar Asesores</a>
        <a class="emt-panel__btn" href="<?php echo esc_url( emt_panel_url( 'configuracion/' ) ); ?>">Configuración</a>
    </div>
</section>
