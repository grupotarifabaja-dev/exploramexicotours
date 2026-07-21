<?php
/**
 * Parte: Footer (doc maestro §8.1 punto 10-11, §6.4 datos de contacto).
 * Columnas: marca, enlaces, legales, contacto + barra de credenciales + redes.
 * Datos desde la Options page con fallback.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'emt_opt' ) ) {
    /**
     * Lee un campo de la Options page con fallback.
     */
    function emt_opt( $field, $fallback = '' ) {
        if ( function_exists( 'get_field' ) ) {
            $v = get_field( $field, 'option' );
            if ( ! empty( $v ) ) {
                return $v;
            }
        }
        return $fallback;
    }
}

$emt_lang   = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$emt_prefix = ( $emt_lang === 'en' ) ? '/en' : '';
$emt_uri    = get_stylesheet_directory_uri();

$wa       = preg_replace( '/\D/', '', emt_opt( 'wa_number', '523310480670' ) );
$email    = emt_opt( 'email_reservas', 'reserva@exploramexicotours.com' );
$tel      = emt_opt( 'telefono_oficina', '+52 33 3810 3475' );
$dir      = emt_opt( 'direccion_fiscal', 'Calle Durazno 1396, Col. Del Fresno, Guadalajara, Jalisco, C.P. 44900' );

$redes = array_filter( array(
    'facebook'  => emt_opt( 'redes_facebook', 'https://www.facebook.com/share/14efnQMgZxL/' ),
    'instagram' => emt_opt( 'redes_instagram', 'https://www.instagram.com/explora_mexico_tours/' ),
    'tiktok'    => emt_opt( 'redes_tiktok', 'https://www.tiktok.com/@explora_mexico_tours' ),
    'youtube'   => emt_opt( 'redes_youtube' ),
) );
$redes_nombres = array( 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'youtube' => 'YouTube' );

$enlaces = array(
    'tours'    => array( emt_t( 'ver_tour' ) === 'View tour' ? 'Tours' : 'Tours', home_url( $emt_prefix . '/tours/' ) ),
    'destinos' => array( emt_t( 'destinos' ), home_url( $emt_prefix . '/tours/' ) ),
    'asesores' => array( emt_t( 'asesores' ), home_url( $emt_prefix . '/asesores/' ) ),
    'blog'     => array( emt_t( 'blog' ), home_url( $emt_prefix . '/blog/' ) ),
    'contacto' => array( emt_t( 'contacto' ), home_url( $emt_prefix . '/contacto/' ) ),
);

$creds = array(
    array( 'amav.png', 'AMAV Occidente' ),
    array( 'moderniza.png', 'Moderniza SECTUR' ),
    array( 'logos-impresor-1.png', 'AMTAVE' ),
);
?>
<footer class="emt-footer">
    <div class="emt-container emt-footer__grid">

        <div class="emt-footer__col emt-footer__col--brand">
            <img class="emt-footer__logo" src="<?php echo esc_url( $emt_uri . '/assets/images/explora-logo.png' ); ?>" alt="Explora México Tours" width="160" height="48" />
            <p class="emt-footer__desc"><?php echo esc_html( emt_t( 'footer_desc' ) ); ?></p>
        </div>

        <div class="emt-footer__col">
            <h4 class="emt-footer__title"><?php echo esc_html( emt_t( 'enlaces_rapidos' ) ); ?></h4>
            <ul class="emt-footer__links">
                <?php foreach ( $enlaces as $e ) : ?>
                    <li><a href="<?php echo esc_url( $e[1] ); ?>"><?php echo esc_html( $e[0] ); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="emt-footer__col">
            <h4 class="emt-footer__title"><?php echo esc_html( emt_t( 'legales' ) ); ?></h4>
            <ul class="emt-footer__links">
                <li><a href="<?php echo esc_url( home_url( $emt_prefix . '/aviso-de-privacidad/' ) ); ?>"><?php echo esc_html( emt_t( 'aviso_privacidad' ) ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( $emt_prefix . '/terminos-y-condiciones/' ) ); ?>"><?php echo esc_html( emt_t( 'terminos' ) ); ?></a></li>
            </ul>
        </div>

        <div class="emt-footer__col">
            <h4 class="emt-footer__title"><?php echo esc_html( emt_t( 'contacto' ) ); ?></h4>
            <ul class="emt-footer__contact">
                <li><a href="https://wa.me/<?php echo esc_attr( $wa ); ?>">WhatsApp: +<?php echo esc_html( $wa ); ?></a></li>
                <li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
                <li><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $tel ) ); ?>"><?php echo esc_html( $tel ); ?></a></li>
                <li><?php echo esc_html( $dir ); ?></li>
            </ul>
            <?php if ( $redes ) : ?>
                <ul class="emt-footer__social">
                    <?php foreach ( $redes as $net => $url ) : ?>
                        <li><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $redes_nombres[ $net ] ?? ucfirst( $net ) ); ?>"><?php echo esc_html( $redes_nombres[ $net ] ?? ucfirst( $net ) ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="emt-footer__creds">
        <div class="emt-container emt-footer__creds-grid">
            <?php foreach ( $creds as $c ) : ?>
                <img class="emt-footer__cred" src="<?php echo esc_url( $emt_uri . '/assets/images/' . $c[0] ); ?>" alt="<?php echo esc_attr( $c[1] ); ?>" loading="lazy" />
            <?php endforeach; ?>
        </div>
    </div>

    <div class="emt-footer__bottom emt-container">
        <p class="emt-footer__tagline">
            <span>Vibrante.</span> <span>Auténtico.</span> <span>Inspirador.</span> <span>Mexicano.</span>
        </p>
        <p class="emt-footer__copy">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> Explora México Tours · <?php echo esc_html( $dir ); ?></p>
    </div>
</footer>
