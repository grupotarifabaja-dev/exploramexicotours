<?php
/** Panel — login propio con marca EMT (reutiliza la autenticación segura de WP). */
if ( ! defined( 'ABSPATH' ) ) exit;
$logo = get_stylesheet_directory_uri() . '/assets/images/explora-logo.png';
?>
<div class="emt-panel-login">
    <div class="emt-panel-login__card">
        <img class="emt-panel-login__logo" src="<?php echo esc_url( $logo ); ?>" alt="Explora México Tours" />
        <h1>Panel de gestión</h1>
        <?php if ( is_user_logged_in() ) : ?>
            <p class="emt-panel-login__error">Tu cuenta no tiene permisos para este panel. <a href="<?php echo esc_url( wp_logout_url( emt_panel_url() ) ); ?>">Cambiar de cuenta</a>.</p>
        <?php else : ?>
            <p class="emt-panel-login__sub">Inicia sesión para gestionar tus tours y asesores.</p>
            <?php
            wp_login_form( array(
                'redirect'       => emt_panel_url(),
                'label_username' => 'Usuario o correo',
                'label_password' => 'Contraseña',
                'label_log_in'   => 'Entrar',
                'remember'       => true,
            ) );
            ?>
        <?php endif; ?>
    </div>
</div>
