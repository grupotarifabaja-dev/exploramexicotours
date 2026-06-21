<?php
/** Panel — barra superior (logo + usuario + Ver sitio + Salir). */
if ( ! defined( 'ABSPATH' ) ) exit;
$user = wp_get_current_user();
$logo = get_stylesheet_directory_uri() . '/assets/images/explora-logo.png';
?>
<header class="emt-panel__topbar">
    <a class="emt-panel__brand" href="<?php echo esc_url( emt_panel_url() ); ?>">
        <img src="<?php echo esc_url( $logo ); ?>" alt="Explora México Tours" />
        <span class="emt-panel__brand-text">Panel de gestión</span>
    </a>
    <div class="emt-panel__topbar-actions">
        <span class="emt-panel__user">Hola, <?php echo esc_html( $user->display_name ); ?></span>
        <a class="emt-panel__btn emt-panel__btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">Ver sitio</a>
        <a class="emt-panel__btn emt-panel__btn--ghost" href="<?php echo esc_url( wp_logout_url( emt_panel_url() ) ); ?>">Salir</a>
    </div>
</header>
