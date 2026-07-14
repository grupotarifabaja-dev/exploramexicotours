<?php
/**
 * Página de Contacto (/contacto/ y /en/contacto/).
 * Datos de contacto de la options page + bloque corporativo de Explora México
 * Tours (quiénes somos, "Confían en nosotros", certificaciones) — texto del
 * cliente (3_-Transporte.docx), reubicado aquí por ser información general de
 * la empresa. Renderizada por inc/contacto.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';

$L_es = array(
    'title'      => 'Contacto',
    'sub'        => 'Estamos listos para ayudarte a planear tu siguiente viaje.',
    'datos_t'    => 'Datos de contacto',
    'whatsapp'   => 'WhatsApp',
    'tel'        => 'Teléfono de oficina',
    'reservas'   => 'Reservaciones',
    'contacto'   => 'Contacto general',
    'domicilio'  => 'Domicilio',
    'quienes_t'  => 'Quiénes somos',
    'quienes_p'  => 'Explora México Tours es una empresa mexicana creada para atender las necesidades de transporte de personas. Nos especializamos en transporte para el ejecutivo que viene por negocios a Guadalajara, así como para el turismo nacional y extranjero, con crecimiento sostenido desde 2009 y certificación Distintivo Moderniza (SECTUR).',
    'clientes_t' => 'Confían en nosotros',
    'cert_t'     => 'Certificaciones y reconocimientos',
);
$L_en = array(
    'title'      => 'Contact',
    'sub'        => 'We are ready to help you plan your next trip.',
    'datos_t'    => 'Contact details',
    'whatsapp'   => 'WhatsApp',
    'tel'        => 'Office phone',
    'reservas'   => 'Bookings',
    'contacto'   => 'General inquiries',
    'domicilio'  => 'Address',
    'quienes_t'  => 'About us',
    'quienes_p'  => 'Explora México Tours is a Mexican company created to serve people-transportation needs. We specialize in transportation for executives visiting Guadalajara on business, as well as national and international tourism, with steady growth since 2009 and Distintivo Moderniza certification (SECTUR).',
    'clientes_t' => 'They trust us',
    'cert_t'     => 'Certifications & recognitions',
);
$L = ( $lang === 'en' ) ? $L_en : $L_es;

// Datos desde la config (con fallbacks del doc maestro).
$o = function ( $field, $default = '' ) {
    if ( ! function_exists( 'get_field' ) ) { return $default; }
    $v = get_field( $field, 'option' );
    return ( $v === null || $v === false || $v === '' ) ? $default : $v;
};
$wa       = preg_replace( '/\D/', '', (string) $o( 'wa_number', '523310480670' ) );
$tel_of   = $o( 'telefono_oficina', '+52 (33) 3810-3475' );
$mail_res = $o( 'email_reservas', 'reserva@exploramexicotours.com' );
$mail_con = $o( 'email_contacto', '' );
$direccion = $o( 'direccion_fiscal', 'Durazno 1396, Colonia Del Fresno, Guadalajara, Jalisco, México, CP 44950' );

$clientes = array( 'HCL Technologies', 'Wizeline', 'Wipro', 'TATA Consultancy Services', 'IGT', 'Rosewood Hotels', 'Aeries Tech', 'Tequileño', 'Nimbus', 'Sealed Air Corp', 'Diversey', 'Secretaría de Turismo del Estado de Jalisco', 'Casa Maestri', 'Arcos', 'Slalom', 'Greymatters', 'El Cristiano Tequila' );

$certs = ( $lang === 'en' )
    ? array( 'Distintivo I', 'Distintivo M', 'Distintivo C', 'Anfitriona al estilo Jalisco', 'Training in prevention of child and adolescent exploitation in travel and tourism', '"Yo también juego" recognition — Road to the 2026 World Cup' )
    : array( 'Distintivo I', 'Distintivo M', 'Distintivo C', 'Anfitriona al estilo Jalisco', 'Sensibilización y capacitación en prevención de explotación de niños, niñas y adolescentes en viajes y turismo', 'Reconocimiento "Yo también juego" rumbo al mundial 2026' );

get_header();
?>
<main class="emt-contacto">

    <!-- Hero -->
    <section class="emt-contacto-hero">
        <div class="emt-container">
            <h1 class="emt-contacto-hero__title"><?php echo esc_html( $L['title'] ); ?></h1>
            <p class="emt-contacto-hero__sub"><?php echo esc_html( $L['sub'] ); ?></p>
        </div>
    </section>

    <!-- Datos de contacto -->
    <section class="emt-corp-section">
        <div class="emt-container emt-contacto-datos">
            <h2><?php echo esc_html( $L['datos_t'] ); ?></h2>
            <ul>
                <li><strong><?php echo esc_html( $L['whatsapp'] ); ?>:</strong> <a href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" rel="noopener">+<?php echo esc_html( $wa ); ?></a></li>
                <li><strong><?php echo esc_html( $L['tel'] ); ?>:</strong> <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $tel_of ) ); ?>"><?php echo esc_html( $tel_of ); ?></a></li>
                <li><strong><?php echo esc_html( $L['reservas'] ); ?>:</strong> <a href="mailto:<?php echo esc_attr( $mail_res ); ?>"><?php echo esc_html( $mail_res ); ?></a></li>
                <?php if ( $mail_con ) : ?>
                    <li><strong><?php echo esc_html( $L['contacto'] ); ?>:</strong> <a href="mailto:<?php echo esc_attr( $mail_con ); ?>"><?php echo esc_html( $mail_con ); ?></a></li>
                <?php endif; ?>
                <li><strong><?php echo esc_html( $L['domicilio'] ); ?>:</strong> <?php echo esc_html( $direccion ); ?></li>
            </ul>
        </div>
    </section>

    <!-- Quiénes somos (corporativo) -->
    <section class="emt-corp-section emt-corp-section--alt">
        <div class="emt-container emt-corp-quienes">
            <h2><?php echo esc_html( $L['quienes_t'] ); ?></h2>
            <p><?php echo esc_html( $L['quienes_p'] ); ?></p>
        </div>
    </section>

    <!-- Confían en nosotros -->
    <section class="emt-corp-section emt-corp-section--dark">
        <div class="emt-container">
            <h2><?php echo esc_html( $L['clientes_t'] ); ?></h2>
            <ul class="emt-corp-clientes">
                <?php foreach ( $clientes as $c ) : ?>
                    <li><?php echo esc_html( $c ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- Certificaciones y reconocimientos -->
    <section class="emt-corp-section">
        <div class="emt-container">
            <h2><?php echo esc_html( $L['cert_t'] ); ?></h2>
            <ul class="emt-corp-certs">
                <?php foreach ( $certs as $c ) : ?>
                    <li><?php echo esc_html( $c ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

</main>
<?php
get_footer();
