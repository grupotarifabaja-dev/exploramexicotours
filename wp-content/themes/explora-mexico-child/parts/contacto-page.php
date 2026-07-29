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

    <?php $emt_hdr = function_exists( 'emt_page_header_image_url' ) ? emt_page_header_image_url( 'contacto', 'large' ) : ''; ?>
    <section class="emt-archive-hero <?php echo $emt_hdr ? 'emt-archive-hero--photo' : 'emt-archive-hero--plain'; ?>">
        <?php if ( $emt_hdr ) : ?><div class="emt-archive-hero__media" aria-hidden="true"><img src="<?php echo esc_url( $emt_hdr ); ?>" alt="" /></div><?php endif; ?>
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
                <ul class="emt-contacto-list emt-contacto-list--icons">
                    <li><span class="emt-contacto-list__ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.6 2.81.72A2 2 0 0 1 22 16.92z"/></svg></span><span><strong><?php echo esc_html( $L['oficina'] ); ?>:</strong> <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $tel ) ); ?>"><?php echo esc_html( $tel ); ?></a></span></li>
                    <li><span class="emt-contacto-list__ic emt-contacto-list__ic--wa" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2a9.9 9.9 0 0 0-8.45 15.06L2 22l5.08-1.55A9.9 9.9 0 1 0 12.04 2zm5.77 14.06c-.24.68-1.4 1.3-1.93 1.35-.52.05-1.01.24-3.4-.7-2.87-1.13-4.7-4.06-4.84-4.25-.14-.19-1.16-1.55-1.16-2.95 0-1.4.74-2.09 1-2.38.26-.29.57-.36.76-.36l.55.01c.18.01.41-.07.65.5.24.58.82 2 .9 2.14.07.14.12.31.02.5-.1.19-.15.31-.29.48l-.44.51c-.14.14-.29.3-.12.58.16.29.73 1.2 1.57 1.95 1.08.96 1.99 1.26 2.27 1.4.29.14.45.12.62-.07.17-.19.72-.84.91-1.13.19-.29.38-.24.64-.14.26.1 1.66.78 1.94.92.29.14.48.22.55.34.07.12.07.68-.17 1.35z"/></svg></span><span><strong>WhatsApp:</strong> <a href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" rel="noopener">+<?php echo esc_html( $wa ); ?></a></span></li>
                    <li><span class="emt-contacto-list__ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg></span><span><strong>Email:</strong> <a href="mailto:<?php echo esc_attr( $mail1 ); ?>"><?php echo esc_html( $mail1 ); ?></a> · <a href="mailto:<?php echo esc_attr( $mail2 ); ?>"><?php echo esc_html( $mail2 ); ?></a></span></li>
                    <li><span class="emt-contacto-list__ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span><strong><?php echo esc_html( $L['domicilio'] ); ?>:</strong> <?php echo esc_html( $dir ); ?></span></li>
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
