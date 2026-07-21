<?php
/**
 * Parte: Header con mega-menú (doc maestro §7.3, §8.1).
 * Logo + navegación + selector de idioma + CTA "Cotizar grupo".
 * Sticky con backdrop-blur (header.css) y hamburguesa en móvil (mega-menu.js).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_lang   = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$emt_prefix = ( $emt_lang === 'en' ) ? '/en' : '';
$emt_logo   = get_stylesheet_directory_uri() . '/assets/images/explora-logo.png';

$emt_links = array(
    'asesores'   => home_url( $emt_prefix . '/asesores/' ),
    'transporte' => home_url( $emt_prefix . '/transporte/' ),
    'blog'     => home_url( $emt_prefix . '/blog/' ),
    'contacto' => home_url( $emt_prefix . '/contacto/' ),
    'nosotros' => home_url( $emt_prefix . '/nosotros/' ),
);
$emt_triggers = array(
    'destinos'     => emt_t( 'a_donde_ir' ),
    'categorias'   => emt_t( 'que_tour' ),
    'experiencias' => emt_t( 'experiencias' ),
);
?>
<header class="emt-header" id="emt-header" data-emt-header>
    <div class="emt-header__inner emt-container">

        <a class="emt-header__logo" href="<?php echo esc_url( home_url( $emt_prefix . '/' ) ); ?>" aria-label="Explora México Tours">
            <img src="<?php echo esc_url( $emt_logo ); ?>" alt="Explora México Tours" width="160" height="48" />
        </a>

        <nav class="emt-header__nav" aria-label="<?php echo esc_attr( emt_t( 'menu' ) ); ?>">
            <ul class="emt-nav">
                <?php foreach ( $emt_triggers as $key => $label ) : ?>
                    <li class="emt-nav__item emt-nav__item--mega">
                        <button type="button" class="emt-nav__trigger" data-mega-trigger="<?php echo esc_attr( $key ); ?>"
                            aria-expanded="false" aria-controls="emt-mega-<?php echo esc_attr( $key ); ?>" aria-haspopup="true">
                            <?php echo esc_html( $label ); ?>
                            <span class="emt-nav__caret" aria-hidden="true"></span>
                        </button>
                    </li>
                <?php endforeach; ?>
                <li class="emt-nav__item"><a class="emt-nav__link" href="<?php echo esc_url( $emt_links['asesores'] ); ?>"><?php echo esc_html( emt_t( 'asesores' ) ); ?></a></li>
                <li class="emt-nav__item"><a class="emt-nav__link" href="<?php echo esc_url( $emt_links['transporte'] ); ?>"><?php echo esc_html( emt_t( 'transporte' ) ); ?></a></li>
                <li class="emt-nav__item"><a class="emt-nav__link" href="<?php echo esc_url( $emt_links['blog'] ); ?>"><?php echo esc_html( emt_t( 'blog' ) ); ?></a></li>
                <li class="emt-nav__item"><a class="emt-nav__link" href="<?php echo esc_url( $emt_links['nosotros'] ); ?>"><?php echo esc_html( emt_t( 'nosotros' ) ); ?></a></li>
                <li class="emt-nav__item"><a class="emt-nav__link" href="<?php echo esc_url( $emt_links['contacto'] ); ?>"><?php echo esc_html( emt_t( 'contacto' ) ); ?></a></li>
            </ul>
        </nav>

        <div class="emt-header__actions">
            <?php
            $emt_switcher = get_stylesheet_directory() . '/parts/lang-switcher.php';
            if ( file_exists( $emt_switcher ) ) {
                include $emt_switcher;
            }
            ?>
            <a class="emt-btn emt-btn--cta" href="<?php echo esc_url( home_url( $emt_prefix . '/cotizacion/' ) ); ?>"><?php echo esc_html( emt_t( 'cotizar_grupo' ) ); ?></a>
            <button type="button" class="emt-header__burger" data-emt-burger aria-expanded="false" aria-controls="emt-drawer" aria-label="<?php echo esc_attr( emt_t( 'menu' ) ); ?>">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <?php include get_stylesheet_directory() . '/parts/mega-menu.php'; ?>

    <!-- Drawer móvil -->
    <div class="emt-drawer" id="emt-drawer" data-emt-drawer hidden>
        <nav aria-label="<?php echo esc_attr( emt_t( 'menu' ) ); ?>">
            <ul class="emt-drawer__list">
                <?php foreach ( $emt_triggers as $key => $label ) : ?>
                    <li><a class="emt-drawer__link" href="<?php echo esc_url( home_url( $emt_prefix . '/tours/' ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
                <?php endforeach; ?>
                <li><a class="emt-drawer__link" href="<?php echo esc_url( $emt_links['asesores'] ); ?>"><?php echo esc_html( emt_t( 'asesores' ) ); ?></a></li>
                <li><a class="emt-drawer__link" href="<?php echo esc_url( $emt_links['transporte'] ); ?>"><?php echo esc_html( emt_t( 'transporte' ) ); ?></a></li>
                <li><a class="emt-drawer__link" href="<?php echo esc_url( $emt_links['blog'] ); ?>"><?php echo esc_html( emt_t( 'blog' ) ); ?></a></li>
                <li><a class="emt-drawer__link" href="<?php echo esc_url( $emt_links['nosotros'] ); ?>"><?php echo esc_html( emt_t( 'nosotros' ) ); ?></a></li>
                <li><a class="emt-drawer__link" href="<?php echo esc_url( $emt_links['contacto'] ); ?>"><?php echo esc_html( emt_t( 'contacto' ) ); ?></a></li>
            </ul>
        </nav>
    </div>
    <div class="emt-drawer__overlay" data-emt-drawer-overlay hidden></div>
</header>
