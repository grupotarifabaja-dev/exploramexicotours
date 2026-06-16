<?php
/**
 * Parte: Selector de idioma ES/EN (doc maestro §10.3).
 * Usa emt_lang_switch_url() (inc/i18n.php) para preservar la ruta actual.
 * Se integra en el header.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_current = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$emt_langs   = array(
    'es' => array( 'label' => 'ES', 'name' => 'Español' ),
    'en' => array( 'label' => 'EN', 'name' => 'English' ),
);
?>
<div class="emt-lang" role="group" aria-label="<?php echo esc_attr( emt_t( 'idiomas' ) ); ?>">
    <?php foreach ( $emt_langs as $code => $data ) : ?>
        <?php
        $is_active = ( $code === $emt_current );
        $url       = function_exists( 'emt_lang_switch_url' ) ? emt_lang_switch_url( $code ) : home_url( '/' );
        ?>
        <a
            class="emt-lang__opt<?php echo $is_active ? ' is-active' : ''; ?>"
            href="<?php echo esc_url( $url ); ?>"
            lang="<?php echo esc_attr( $code ); ?>"
            hreflang="<?php echo esc_attr( $code ); ?>"
            aria-label="<?php echo esc_attr( $data['name'] ); ?>"
            <?php echo $is_active ? 'aria-current="true"' : ''; ?>
        ><?php echo esc_html( $data['label'] ); ?></a>
    <?php endforeach; ?>
</div>
