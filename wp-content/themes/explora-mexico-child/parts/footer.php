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
                <?php $emt_social_svg = array(
                    'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.55-1.5h1.65V3.6c-.3-.04-1.3-.13-2.45-.13-2.4 0-4.05 1.47-4.05 4.17v2.26H7.5V13h2.7v8h3.3z"/></svg>',
                    'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.2c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23a3.72 3.72 0 0 1-.9 1.38c-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.72 3.72 0 0 1-1.38-.9 3.72 3.72 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.21 15.58 2.2 15.2 2.2 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.21 8.8 2.2 12 2.2m0 1.8c-3.15 0-3.5.01-4.74.07-1.08.05-1.67.23-2.06.38-.52.2-.89.44-1.28.83-.39.39-.63.76-.83 1.28-.15.39-.33.98-.38 2.06-.06 1.24-.07 1.59-.07 4.74s.01 3.5.07 4.74c.05 1.08.23 1.67.38 2.06.2.52.44.89.83 1.28.39.39.76.63 1.28.83.39.15.98.33 2.06.38 1.24.06 1.59.07 4.74.07s3.5-.01 4.74-.07c1.08-.05 1.67-.23 2.06-.38.52-.2.89-.44 1.28-.83.39-.39.63-.76.83-1.28.15-.39.33-.98.38-2.06.06-1.24.07-1.59.07-4.74s-.01-3.5-.07-4.74c-.05-1.08-.23-1.67-.38-2.06a2.9 2.9 0 0 0-.83-1.28 2.9 2.9 0 0 0-1.28-.83c-.39-.15-.98-.33-2.06-.38-1.24-.06-1.59-.07-4.74-.07zm0 3.06a4.94 4.94 0 1 1 0 9.88 4.94 4.94 0 0 1 0-9.88zm0 1.8a3.14 3.14 0 1 0 0 6.28 3.14 3.14 0 0 0 0-6.28zm5.15-2.02a1.15 1.15 0 1 1 0 2.3 1.15 1.15 0 0 1 0-2.3z"/></svg>',
                    'tiktok' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.6 3c.36 1.94 1.6 3.32 3.56 3.6v2.62c-1.32.13-2.5-.24-3.72-1.08v5.71c0 4.2-3.63 6.56-6.98 5.06-2.66-1.19-3.63-4.44-2.06-6.94 1.2-1.91 3.35-2.74 5.63-2.24v2.72c-.34-.08-.66-.13-.98-.12-1.36.05-2.36 1.05-2.33 2.32.03 1.3 1.1 2.3 2.4 2.24 1.28-.05 2.18-1.05 2.18-2.42V3h2.3z"/></svg>',
                    'youtube' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2s-.2-1.36-.78-1.95c-.74-.79-1.57-.79-1.95-.84C16.14 4.2 12 4.2 12 4.2h-.01s-4.14 0-6.87.21c-.38.05-1.21.05-1.95.84C2.6 5.84 2.4 7.2 2.4 7.2S2.2 8.8 2.2 10.4v1.18c0 1.6.2 3.2.2 3.2s.2 1.36.77 1.95c.74.79 1.7.77 2.14.85 1.55.15 6.69.2 6.69.2s4.14-.01 6.87-.22c.38-.05 1.21-.05 1.95-.84.58-.59.78-1.95.78-1.95s.2-1.6.2-3.2V10.4c0-1.6-.2-3.2-.2-3.2zM9.98 14.6V8.9l5.3 2.87-5.3 2.83z"/></svg>',
                ); ?>
                <ul class="emt-footer__social emt-footer__social--icons">
                    <?php foreach ( $redes as $net => $url ) : ?>
                        <li><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $redes_nombres[ $net ] ?? ucfirst( $net ) ); ?>" title="<?php echo esc_attr( $redes_nombres[ $net ] ?? ucfirst( $net ) ); ?>"><?php echo $emt_social_svg[ $net ] ?? esc_html( $redes_nombres[ $net ] ?? ucfirst( $net ) ); // phpcs:ignore -- SVG estático del tema ?></a></li>
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
