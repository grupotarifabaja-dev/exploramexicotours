<?php
/**
 * Página "Contacto" (/contacto/). Renderizada por inc/contacto.php.
 * Datos de contacto (desde emt_opt, mismos del footer) + mapa + formulario AJAX.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
$o    = function ( $k, $d = '' ) { return function_exists( 'emt_opt' ) ? emt_opt( $k, $d ) : $d; };

$wa   = preg_replace( '/\D/', '', $o( 'wa_number', '523310480670' ) );
$tel  = $o( 'telefono_oficina', '+52 33 3810 3475' );
$mail1= $o( 'email_reservas', 'reserva@exploramexicotours.com' );
$mail2= 'ventas@exploramexicotours.com';
$dir  = $o( 'direccion_fiscal', 'Calle Durazno 1396, Col. Del Fresno, Guadalajara, Jalisco, C.P. 44900' );
$redes = array(
    'Facebook'  => $o( 'redes_facebook', 'https://www.facebook.com/share/14efnQMgZxL/' ),
    'Instagram' => $o( 'redes_instagram', 'https://www.instagram.com/explora_mexico_tours/' ),
    'TikTok'    => $o( 'redes_tiktok', 'https://www.tiktok.com/@explora_mexico_tours' ),
);
$map_src = 'https://www.google.com/maps?q=' . rawurlencode( $dir ) . '&output=embed';

$L = ( $lang === 'en' ) ? array(
    'eyebrow' => 'We are here to help', 'title' => 'Contact us',
    'sub'     => 'Questions, ideas or a trip in mind? Reach out and our team will get back to you.',
    'datos_t' => 'Contact details', 'oficina' => 'Office', 'domicilio' => 'Address', 'redes' => 'Follow us',
    'form_t'  => 'Send us a message',
    'f_nombre'=> 'Full name', 'f_correo' => 'Email', 'f_tel' => 'Phone / WhatsApp', 'f_asunto' => 'Subject', 'f_mensaje' => 'Message',
    'f_enviar'=> 'Send message', 'f_enviando' => 'Sending…',
    'f_error' => 'Please review the required fields.', 'f_conexion' => 'Connection error. Please try again.',
) : array(
    'eyebrow' => 'Estamos para ayudarte', 'title' => 'Contáctanos',
    'sub'     => '¿Dudas, ideas o un viaje en mente? Escríbenos y nuestro equipo te responderá.',
    'datos_t' => 'Datos de contacto', 'oficina' => 'Oficina', 'domicilio' => 'Domicilio', 'redes' => 'Síguenos',
    'form_t'  => 'Envíanos un mensaje',
    'f_nombre'=> 'Nombre completo', 'f_correo' => 'Correo', 'f_tel' => 'Teléfono / WhatsApp', 'f_asunto' => 'Asunto', 'f_mensaje' => 'Mensaje',
    'f_enviar'=> 'Enviar mensaje', 'f_enviando' => 'Enviando…',
    'f_error' => 'Revisa los campos obligatorios.', 'f_conexion' => 'Error de conexión. Intenta de nuevo.',
);

get_header();
?>
<main class="emt-contacto">

    <section class="emt-archive-hero emt-archive-hero--plain">
        <div class="emt-container emt-archive-hero__inner">
            <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
            <div class="emt-heading emt-heading--left emt-archive-hero__heading">
                <span class="emt-eyebrow"><?php echo esc_html( $L['eyebrow'] ); ?></span>
                <h1 class="emt-title emt-archive-hero__title"><?php echo esc_html( $L['title'] ); ?></h1>
                <p class="emt-heading__sub emt-archive-hero__sub"><?php echo esc_html( $L['sub'] ); ?></p>
            </div>
        </div>
    </section>

    <section class="emt-section emt-contacto-body">
        <div class="emt-container emt-contacto-grid">

            <aside class="emt-contacto-info">
                <h2 class="emt-contacto-info__title"><?php echo esc_html( $L['datos_t'] ); ?></h2>
                <ul class="emt-contacto-list">
                    <li><strong><?php echo esc_html( $L['oficina'] ); ?>:</strong> <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $tel ) ); ?>"><?php echo esc_html( $tel ); ?></a></li>
                    <li><strong>WhatsApp:</strong> <a href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" rel="noopener">+<?php echo esc_html( $wa ); ?></a></li>
                    <li><strong>Email:</strong> <a href="mailto:<?php echo esc_attr( $mail1 ); ?>"><?php echo esc_html( $mail1 ); ?></a> · <a href="mailto:<?php echo esc_attr( $mail2 ); ?>"><?php echo esc_html( $mail2 ); ?></a></li>
                    <li><strong><?php echo esc_html( $L['domicilio'] ); ?>:</strong> <?php echo esc_html( $dir ); ?></li>
                </ul>

                <div class="emt-contacto-redes">
                    <span class="emt-contacto-redes__label"><?php echo esc_html( $L['redes'] ); ?>:</span>
                    <?php foreach ( $redes as $nombre => $url ) : if ( ! $url ) continue; ?>
                        <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $nombre ); ?></a>
                    <?php endforeach; ?>
                </div>

                <div class="emt-contacto-map">
                    <iframe src="<?php echo esc_url( $map_src ); ?>" title="Mapa" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            </aside>

            <div class="emt-contacto-form-wrap">
                <h2 class="emt-contacto-form-title"><?php echo esc_html( $L['form_t'] ); ?></h2>
                <form class="emt-contacto-form" data-emt-contacto-form
                      data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
                      data-nonce="<?php echo esc_attr( wp_create_nonce( 'emt_contacto' ) ); ?>"
                      data-msg-error="<?php echo esc_attr( $L['f_error'] ); ?>"
                      data-msg-conexion="<?php echo esc_attr( $L['f_conexion'] ); ?>"
                      data-msg-enviando="<?php echo esc_attr( $L['f_enviando'] ); ?>"
                      novalidate>
                    <div class="emt-contacto-form__grid">
                        <div class="emt-field"><label><?php echo esc_html( $L['f_nombre'] ); ?> *</label><input type="text" name="nombre" required /></div>
                        <div class="emt-field"><label><?php echo esc_html( $L['f_tel'] ); ?></label><input type="tel" name="telefono" /></div>
                        <div class="emt-field"><label><?php echo esc_html( $L['f_correo'] ); ?> *</label><input type="email" name="correo" required /></div>
                        <div class="emt-field"><label><?php echo esc_html( $L['f_asunto'] ); ?></label><input type="text" name="asunto" /></div>
                        <div class="emt-field emt-field--full"><label><?php echo esc_html( $L['f_mensaje'] ); ?> *</label><textarea name="mensaje" rows="5" required></textarea></div>
                    </div>
                    <div class="emt-contacto-form__bar">
                        <span class="emt-contacto-form__msg" data-contacto-msg aria-live="polite"></span>
                        <button type="submit" class="emt-btn emt-btn--cta"><?php echo esc_html( $L['f_enviar'] ); ?></button>
                    </div>
                </form>
            </div>

        </div>
    </section>

</main>
<?php
get_footer();
