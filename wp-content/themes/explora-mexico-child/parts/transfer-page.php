<?php
/**
 * Página "Explora Transfer" (/transporte/ y /en/transporte/).
 * Contenido según explora-transfer-contenido.md del cliente (3_-Transporte.docx).
 * Renderizada por inc/transfer.php; formulario vía assets/js/transfer-form.js.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';

// Copy bilingüe de la sección (mucho texto propio: diccionario local, no global).
$L_es = array(
    'title'        => 'Explora Transfer',
    'hero_eyebrow' => 'Transporte ejecutivo y turístico',
    'hero_sub'     => 'Transporte para ejecutivos, turismo nacional y extranjero, personal y eventos. Crecimiento sostenido desde 2009, con Distintivo Moderniza (SECTUR).',
    'hero_cta'     => 'Reservar transporte',
    'quienes_t'    => 'Quiénes somos',
    'quienes_p'    => 'Explora Transfer by Explora México Tours es una empresa mexicana creada para atender las necesidades de transporte de personas. Nos especializamos en transporte para el ejecutivo que viene por negocios a Guadalajara, así como para el turismo nacional y extranjero. Crecimiento sostenido desde 2009 y certificación Distintivo Moderniza (SECTUR).',
    'servicios_t'  => 'Servicios',
    'flotilla_t'   => 'Nuestra flotilla',
    'flotilla_sub' => 'Unidades para cada tamaño de grupo y tipo de servicio.',
    'pax'          => 'pasajeros',
    'clientes_t'   => 'Confían en nosotros',
    'cert_t'       => 'Certificaciones y reconocimientos',
    'contacto_t'   => 'Contacto Explora Transfer',
    'oficina'      => 'Oficina',
    'domicilio'    => 'Domicilio',
    'operado'      => 'Explora Transfer es operado por Explora México Tours.',
    'form_t'       => 'Reserva tu transporte',
    'form_sub'     => 'Cuéntanos qué necesitas y te contactamos con disponibilidad y tarifa.',
    'f_nombre'     => 'Nombre', 'f_tel' => 'Teléfono', 'f_correo' => 'Correo', 'f_empresa' => 'Empresa',
    'f_tipo'       => 'Tipo de servicio',
    'f_origen'     => 'Origen', 'f_destino' => 'Destino',
    'f_salida'     => 'Fecha y hora de salida', 'f_retorno' => 'Fecha y hora de retorno',
    'f_itin'       => 'Itinerario detallado del traslado',
    'f_adultos'    => 'Adultos', 'f_menores' => 'Menores', 'f_pax' => 'Número de pasajeros',
    'f_extras'     => 'Solicitudes especiales',
    'x_baby'       => 'Baby car seat',
    'x_bebidas'    => 'Bebidas (agua, Electrolit, refresco en lata, cerveza)',
    'x_snacks'     => 'Snacks (cacahuates, chips, frutos secos, box lunch)',
    'f_solicitudes'=> 'Otras solicitudes o comentarios',
    'f_enviar'     => 'Enviar solicitud',
    'f_enviando'   => 'Enviando…',
    'f_error'      => 'Revisa los campos obligatorios.',
    'f_conexion'   => 'Error de conexión. Intenta de nuevo.',
);
$L_en = array(
    'title'        => 'Explora Transfer',
    'hero_eyebrow' => 'Executive & tourist transportation',
    'hero_sub'     => 'Transportation for business travelers, national and international tourism, staff and events. Steady growth since 2009, Distintivo Moderniza certified (SECTUR).',
    'hero_cta'     => 'Book transportation',
    'quienes_t'    => 'About us',
    'quienes_p'    => 'Explora Transfer by Explora México Tours is a Mexican company created to serve people-transportation needs. We specialize in transportation for executives visiting Guadalajara on business, as well as national and international tourism. Steady growth since 2009 and Distintivo Moderniza certification (SECTUR).',
    'servicios_t'  => 'Services',
    'flotilla_t'   => 'Our fleet',
    'flotilla_sub' => 'Vehicles for every group size and type of service.',
    'pax'          => 'passengers',
    'clientes_t'   => 'They trust us',
    'cert_t'       => 'Certifications & recognitions',
    'contacto_t'   => 'Explora Transfer contact',
    'oficina'      => 'Office',
    'domicilio'    => 'Address',
    'operado'      => 'Explora Transfer is operated by Explora México Tours.',
    'form_t'       => 'Book your transportation',
    'form_sub'     => 'Tell us what you need and we will get back to you with availability and pricing.',
    'f_nombre'     => 'Name', 'f_tel' => 'Phone', 'f_correo' => 'Email', 'f_empresa' => 'Company',
    'f_tipo'       => 'Type of service',
    'f_origen'     => 'Pick-up location', 'f_destino' => 'Drop-off location',
    'f_salida'     => 'Departure date & time', 'f_retorno' => 'Return date & time',
    'f_itin'       => 'Detailed itinerary',
    'f_adultos'    => 'Adults', 'f_menores' => 'Children', 'f_pax' => 'Number of passengers',
    'f_extras'     => 'Special requests',
    'x_baby'       => 'Baby car seat',
    'x_bebidas'    => 'Drinks (water, Electrolit, canned soda, beer)',
    'x_snacks'     => 'Snacks (peanuts, chips, dried fruit, box lunch)',
    'f_solicitudes'=> 'Other requests or comments',
    'f_enviar'     => 'Send request',
    'f_enviando'   => 'Sending…',
    'f_error'      => 'Please review the required fields.',
    'f_conexion'   => 'Connection error. Please try again.',
);
$L = ( $lang === 'en' ) ? $L_en : $L_es;

$servicios = ( $lang === 'en' )
    ? array( 'Regular & luxury executive transportation', 'Tourist transportation', 'Staff transportation', 'School transportation', 'Transportation by the hour', 'Airport transfers', 'Local tours', 'Social event transfers', 'Personalized transfers', 'Aircraft rental', 'Security & bodyguard services', 'Yacht & boat rental' )
    : array( 'Transporte ejecutivo regular y de lujo', 'Transporte turístico', 'Transporte de personal', 'Transporte escolar', 'Transporte por horas', 'Traslados desde o al aeropuerto', 'Tours locales', 'Traslados para eventos sociales', 'Traslados personalizados', 'Renta de aeronaves', 'Servicios de seguridad y guardaespaldas', 'Renta de yates y embarcaciones' );

// 'img': fotos reales en assets/images/flotilla/ (principal + opcional secundaria al hover).
$flotilla = array(
    array( 'n' => 'Mercedes Benz Sprinter Lux', 'cap' => 20, 'icon' => '🚐', 'img' => array( 'sprinter-lux-1.jpg', 'sprinter-lux-2.jpg' ), 'feats_es' => 'Asientos reclinables, mesa de trabajo con portavasos, A/C, Smart TV, Bluetooth, audio tipo cine, cargadores USB y tipo C, aislante térmico, espacio para maletas, ventanas panorámicas.', 'feats_en' => 'Reclining seats, work table with cup holders, A/C, Smart TV, Bluetooth, cinema-grade audio, USB & USB-C chargers, thermal insulation, luggage space, panoramic windows.' ),
    array( 'n' => 'Mercedes Benz Sprinter Regular', 'cap' => 20, 'icon' => '🚐', 'img' => array( 'sprinter-regular-1.jpg', 'sprinter-regular-2.jpg' ), 'feats_es' => 'Asientos reclinables, A/C, TV y DVD, espacio para maletas, ventanas.', 'feats_en' => 'Reclining seats, A/C, TV & DVD, luggage space, windows.' ),
    array( 'n' => 'Autobús (Irizar, Volvo, Marcopolo, Neobus)', 'cap' => '46–50', 'icon' => '🚌', 'feats_es' => 'A/C, TV, DVD, audio, cargadores, maletero interior, espacio para maletas, 1 o 2 puertas, ventanas panorámicas.', 'feats_en' => 'A/C, TV, DVD, audio, chargers, interior luggage rack, luggage space, 1 or 2 doors, panoramic windows.' ),
    array( 'n' => 'Toyota Hiace / Urban / Transit', 'cap' => 12, 'icon' => '🚐', 'feats_es' => 'Asientos reclinables, A/C, TV, DVD, audio, parrilla porta equipaje (según unidad).', 'feats_en' => 'Reclining seats, A/C, TV, DVD, audio, roof luggage rack (per unit).' ),
    array( 'n' => 'Suburban línea 2019', 'cap' => 6, 'icon' => '🚙', 'img' => array( 'suburban-2019-1.jpg', 'suburban-2019-2.jpg' ), 'feats_es' => 'A/C, vidrios y seguros eléctricos, DVD, vestiduras en piel, cajuela para maletas.', 'feats_en' => 'A/C, power windows & locks, DVD, leather upholstery, luggage trunk.' ),
    array( 'n' => 'Suburban línea nueva 2023', 'cap' => 6, 'icon' => '🚙', 'img' => array( 'suburban-nueva-1.jpg', 'suburban-nueva-2.jpg' ), 'feats_es' => 'A/C, vidrios y seguros eléctricos, DVD, vestiduras en piel, cajuela.', 'feats_en' => 'A/C, power windows & locks, DVD, leather upholstery, trunk.' ),
    array( 'n' => 'Camry 2023', 'cap' => 4, 'icon' => '🚗', 'img' => array( 'camry-1.jpg', 'camry-2.jpg' ), 'feats_es' => 'A/C, vidrios/seguros eléctricos, vestiduras en tela, cajuela, Bluetooth.', 'feats_en' => 'A/C, power windows/locks, fabric upholstery, trunk, Bluetooth.' ),
    array( 'n' => 'Versa 2025', 'cap' => 3, 'icon' => '🚗', 'feats_es' => 'A/C, vidrios/seguros eléctricos, vestiduras en tela, cajuela, CarPlay, Bluetooth.', 'feats_en' => 'A/C, power windows/locks, fabric upholstery, trunk, CarPlay, Bluetooth.' ),
    array( 'n' => 'Mini SUV Suzuki o Mitsubishi 2020', 'cap' => '6 (4 c/equipaje)', 'icon' => '🚙', 'feats_es' => 'A/C, vestiduras en tela, parrilla exterior, cajuela para bolsa de mano, CarPlay, Bluetooth.', 'feats_en' => 'A/C, fabric upholstery, exterior rack, carry-on trunk, CarPlay, Bluetooth.' ),
);

$clientes = array( 'HCL Technologies', 'Wizeline', 'Wipro', 'TATA Consultancy Services', 'IGT', 'Rosewood Hotels', 'Aeries Tech', 'Tequileño', 'Nimbus', 'Sealed Air Corp', 'Diversey', 'Secretaría de Turismo de Jalisco', 'Casa Maestri', 'Arcos', 'Slalom', 'Greymatters', 'El Cristiano Tequila' );

$certs = ( $lang === 'en' )
    ? array( 'Distintivo I', 'Distintivo M', 'Distintivo C', 'Anfitriona al estilo Jalisco', 'Training in prevention of child and adolescent exploitation in travel and tourism', '"Yo también juego" recognition — Road to the 2026 World Cup' )
    : array( 'Distintivo I', 'Distintivo M', 'Distintivo C', 'Anfitriona al estilo Jalisco', 'Sensibilización y capacitación en prevención de explotación de niños, niñas y adolescentes en viajes y turismo', 'Reconocimiento "Yo también juego" rumbo al mundial 2026' );

$tipos_servicio = ( $lang === 'en' )
    ? array( 'traslado' => 'Transfer service', 'turistico' => 'Tourist transportation', 'personal' => 'Staff transportation', 'escolar' => 'School transportation', 'por_horas' => 'By the hour', 'aeropuerto' => 'Airport transfer', 'tours_locales' => 'Local tours', 'aeronaves' => 'Aircraft rental', 'evento_social' => 'Social event transfer', 'otro' => 'Other' )
    : array( 'traslado' => 'Servicio de traslado', 'turistico' => 'Transporte turístico', 'personal' => 'Transporte de personal', 'escolar' => 'Transporte escolar', 'por_horas' => 'Transporte por horas', 'aeropuerto' => 'Traslados desde o al aeropuerto', 'tours_locales' => 'Tours locales', 'aeronaves' => 'Renta de aeronaves', 'evento_social' => 'Traslado para evento social', 'otro' => 'Otro' );

// Base de los assets de flotilla (fotos reales + logo Explora Transfer).
$emt_flotilla_base = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/flotilla/';

get_header();
?>
<main class="emt-transfer">

    <!-- Hero -->
    <section class="emt-transfer-hero">
        <div class="emt-container emt-transfer-hero__inner">
            <p class="emt-transfer-hero__eyebrow"><?php echo esc_html( $L['hero_eyebrow'] ); ?></p>
            <h1 class="emt-transfer-hero__title">
                <img class="emt-transfer-hero__logo" src="<?php echo esc_url( $emt_flotilla_base . 'logo-explora-transfer.png' ); ?>" alt="<?php echo esc_attr( $L['title'] ); ?>" width="800" height="251" />
            </h1>
            <p class="emt-transfer-hero__sub"><?php echo esc_html( $L['hero_sub'] ); ?></p>
            <a class="emt-btn emt-btn--cta" href="#reservar"><?php echo esc_html( $L['hero_cta'] ); ?></a>
        </div>
    </section>

    <!-- Quiénes somos -->
    <section class="emt-transfer-section">
        <div class="emt-container emt-transfer-quienes">
            <h2><?php echo esc_html( $L['quienes_t'] ); ?></h2>
            <p><?php echo esc_html( $L['quienes_p'] ); ?></p>
        </div>
    </section>

    <!-- Servicios -->
    <section class="emt-transfer-section emt-transfer-section--alt">
        <div class="emt-container">
            <h2><?php echo esc_html( $L['servicios_t'] ); ?></h2>
            <ul class="emt-transfer-servicios">
                <?php foreach ( $servicios as $s ) : ?>
                    <li class="emt-transfer-servicio"><?php echo esc_html( $s ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- Flotilla -->
    <section class="emt-transfer-section">
        <div class="emt-container">
            <h2><?php echo esc_html( $L['flotilla_t'] ); ?></h2>
            <p class="emt-transfer-section__sub"><?php echo esc_html( $L['flotilla_sub'] ); ?></p>
            <div class="emt-transfer-flotilla">
                <?php foreach ( $flotilla as $v ) : $has_img = ! empty( $v['img'] ); ?>
                    <article class="emt-flotilla-card<?php echo $has_img ? ' emt-flotilla-card--foto' : ''; ?>">
                        <?php if ( $has_img ) : ?>
                            <figure class="emt-flotilla-card__media">
                                <img class="emt-flotilla-card__img" src="<?php echo esc_url( $emt_flotilla_base . $v['img'][0] ); ?>" alt="<?php echo esc_attr( $v['n'] ); ?>" loading="lazy" decoding="async" />
                                <?php if ( ! empty( $v['img'][1] ) ) : ?>
                                    <img class="emt-flotilla-card__img emt-flotilla-card__img--alt" src="<?php echo esc_url( $emt_flotilla_base . $v['img'][1] ); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" />
                                <?php endif; ?>
                            </figure>
                        <?php endif; ?>
                        <div class="emt-flotilla-card__body">
                            <?php if ( ! $has_img ) : ?>
                                <div class="emt-flotilla-card__icon" aria-hidden="true"><?php echo esc_html( $v['icon'] ); ?></div>
                            <?php endif; ?>
                            <h3 class="emt-flotilla-card__name"><?php echo esc_html( $v['n'] ); ?></h3>
                            <p class="emt-flotilla-card__cap"><?php echo esc_html( $v['cap'] . ' ' . $L['pax'] ); ?></p>
                            <p class="emt-flotilla-card__feats"><?php echo esc_html( $lang === 'en' ? $v['feats_en'] : $v['feats_es'] ); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Clientes -->
    <section class="emt-transfer-section emt-transfer-section--dark">
        <div class="emt-container">
            <h2><?php echo esc_html( $L['clientes_t'] ); ?></h2>
            <ul class="emt-transfer-clientes">
                <?php foreach ( $clientes as $c ) : ?>
                    <li><?php echo esc_html( $c ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- Certificaciones -->
    <section class="emt-transfer-section">
        <div class="emt-container">
            <h2><?php echo esc_html( $L['cert_t'] ); ?></h2>
            <ul class="emt-transfer-certs">
                <?php foreach ( $certs as $c ) : ?>
                    <li><?php echo esc_html( $c ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- Formulario de reservación -->
    <section class="emt-transfer-section emt-transfer-section--alt" id="reservar">
        <div class="emt-container emt-transfer-form-wrap">
            <h2><?php echo esc_html( $L['form_t'] ); ?></h2>
            <p class="emt-transfer-section__sub"><?php echo esc_html( $L['form_sub'] ); ?></p>

            <form class="emt-transfer-form" data-emt-transfer-form
                  data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
                  data-nonce="<?php echo esc_attr( wp_create_nonce( 'emt_transfer' ) ); ?>"
                  data-msg-error="<?php echo esc_attr( $L['f_error'] ); ?>"
                  data-msg-conexion="<?php echo esc_attr( $L['f_conexion'] ); ?>"
                  data-msg-enviando="<?php echo esc_attr( $L['f_enviando'] ); ?>"
                  novalidate>
                <div class="emt-transfer-form__grid">
                    <div class="emt-field"><label><?php echo esc_html( $L['f_nombre'] ); ?> *</label><input type="text" name="nombre" required /></div>
                    <div class="emt-field"><label><?php echo esc_html( $L['f_tel'] ); ?> *</label><input type="tel" name="telefono" required /></div>
                    <div class="emt-field"><label><?php echo esc_html( $L['f_correo'] ); ?> *</label><input type="email" name="correo" required /></div>
                    <div class="emt-field"><label><?php echo esc_html( $L['f_empresa'] ); ?></label><input type="text" name="empresa" /></div>
                    <div class="emt-field emt-field--full"><label><?php echo esc_html( $L['f_tipo'] ); ?></label>
                        <select name="tipo_servicio">
                            <?php foreach ( $tipos_servicio as $k => $lbl ) : ?>
                                <option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $lbl ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="emt-field"><label><?php echo esc_html( $L['f_origen'] ); ?> *</label><input type="text" name="origen" required /></div>
                    <div class="emt-field"><label><?php echo esc_html( $L['f_destino'] ); ?> *</label><input type="text" name="destino" required /></div>
                    <div class="emt-field"><label><?php echo esc_html( $L['f_salida'] ); ?> *</label><input type="datetime-local" name="fecha_salida" required /></div>
                    <div class="emt-field"><label><?php echo esc_html( $L['f_retorno'] ); ?></label><input type="datetime-local" name="fecha_retorno" /></div>
                    <div class="emt-field emt-field--full"><label><?php echo esc_html( $L['f_itin'] ); ?></label><textarea name="itinerario" rows="3"></textarea></div>
                    <fieldset class="emt-field emt-field--full emt-transfer-form__pax">
                        <legend><?php echo esc_html( $L['f_pax'] ); ?> *</legend>
                        <div class="emt-transfer-form__pax-grid">
                            <div class="emt-field"><label><?php echo esc_html( $L['f_adultos'] ); ?> *</label><input type="number" name="adultos" min="1" value="1" required /></div>
                            <div class="emt-field"><label><?php echo esc_html( $L['f_menores'] ); ?></label><input type="number" name="menores" min="0" value="0" /></div>
                        </div>
                    </fieldset>
                    <fieldset class="emt-field emt-field--full">
                        <legend><?php echo esc_html( $L['f_extras'] ); ?></legend>
                        <label class="emt-transfer-check"><input type="checkbox" name="extras[]" value="baby_seat" /> <?php echo esc_html( $L['x_baby'] ); ?></label>
                        <label class="emt-transfer-check"><input type="checkbox" name="extras[]" value="bebidas" /> <?php echo esc_html( $L['x_bebidas'] ); ?></label>
                        <label class="emt-transfer-check"><input type="checkbox" name="extras[]" value="snacks" /> <?php echo esc_html( $L['x_snacks'] ); ?></label>
                    </fieldset>
                    <div class="emt-field emt-field--full"><label><?php echo esc_html( $L['f_solicitudes'] ); ?></label><textarea name="solicitudes" rows="2"></textarea></div>
                </div>
                <div class="emt-transfer-form__bar">
                    <span class="emt-transfer-form__msg" data-transfer-msg aria-live="polite"></span>
                    <button type="submit" class="emt-btn emt-btn--cta"><?php echo esc_html( $L['f_enviar'] ); ?></button>
                </div>
            </form>
        </div>
    </section>

    <!-- Contacto -->
    <section class="emt-transfer-section">
        <div class="emt-container emt-transfer-contacto">
            <h2><?php echo esc_html( $L['contacto_t'] ); ?></h2>
            <ul>
                <li><strong><?php echo esc_html( $L['oficina'] ); ?>:</strong> <a href="tel:+523338103475">+52 (33) 3810-3475</a></li>
                <li><strong>WhatsApp:</strong> <a href="https://wa.me/523314094298" target="_blank" rel="noopener">+52 33 1409 4298</a> · <a href="https://wa.me/523310480670" target="_blank" rel="noopener">+52 33 1048 0670</a></li>
                <li><strong>Email:</strong> <a href="mailto:renato@exploramexicotours.com">renato@exploramexicotours.com</a> · <a href="mailto:ventas@exploramexicotours.com">ventas@exploramexicotours.com</a></li>
                <li><strong><?php echo esc_html( $L['domicilio'] ); ?>:</strong> Durazno 1396, Colonia Del Fresno, Guadalajara, Jalisco, México, CP 44950</li>
            </ul>
            <p class="emt-transfer-operado"><?php echo esc_html( $L['operado'] ); ?></p>
        </div>
    </section>

</main>
<?php
get_footer();
