<?php
/**
 * Carrusel automático (marquee) de logos de certificaciones y reconocimientos.
 * Reutilizable: se incluye en Nosotros y Transporte. Logos en
 * assets/images/certificaciones/. CSS en components.css (.emt-cert-marquee).
 * El set se duplica para un loop continuo; la 2ª copia es aria-hidden.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$emt_cert_base = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/certificaciones/';
$emt_certs = array(
    array( 'amav.png', 'AMAV — Asociación Mexicana de Agencias de Viajes' ),
    array( 'moderniza.png', 'Distintivo Moderniza (SECTUR)' ),
    array( 'distintivo-c.png', 'Distintivo C — Calidad en la Atención al Turista' ),
    array( 'distintivo-i.png', 'Distintivo I — Espacio Incluyente' ),
    array( 'anfitrion-jalisco.png', 'Anfitrión al Estilo Jalisco' ),
    array( 'yo-tambien-juego.png', 'Reconocimiento «Yo también juego»' ),
    array( 'barrancas-del-cobre.png', 'Especialista en destino Barrancas del Cobre' ),
    array( 'tequillier.png', 'Técnico Tequilero «Tequillier»' ),
    array( 'turismo.png', 'Turismo' ),
);
?>
<div class="emt-cert-marquee" aria-label="Certificaciones y reconocimientos">
    <div class="emt-cert-marquee__track">
        <?php for ( $emt_c = 0; $emt_c < 2; $emt_c++ ) : ?>
            <?php foreach ( $emt_certs as $emt_logo ) : ?>
                <div class="emt-cert-item"<?php echo $emt_c ? ' aria-hidden="true"' : ''; ?>>
                    <img src="<?php echo esc_url( $emt_cert_base . $emt_logo[0] ); ?>" alt="<?php echo $emt_c ? '' : esc_attr( $emt_logo[1] ); ?>" loading="lazy" decoding="async" />
                </div>
            <?php endforeach; ?>
        <?php endfor; ?>
    </div>
</div>
