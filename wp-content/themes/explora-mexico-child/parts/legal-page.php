<?php
/**
 * Páginas legales (/aviso-de-privacidad/ y /terminos-y-condiciones/, + /en/).
 * Renderizadas por inc/legales.php. Plantillas conforme a LFPDPPP; los campos
 * [RAZÓN SOCIAL COMPLETA], [DOMICILIO FISCAL COMPLETO] y [FECHA DE PUBLICACIÓN]
 * deben completarse con los datos del cliente antes del lanzamiento.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$doc  = get_query_var( 'emt_legal' ) === 'terminos' ? 'terminos' : 'privacidad';
$lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';

$titulos = array(
    'privacidad' => array( 'es' => 'Aviso de Privacidad', 'en' => 'Privacy Notice' ),
    'terminos'   => array( 'es' => 'Términos y Condiciones', 'en' => 'Terms & Conditions' ),
);
$eyebrow = ( $lang === 'en' ) ? 'Legal' : 'Legales';
$titulo  = $titulos[ $doc ][ $lang === 'en' ? 'en' : 'es' ];

$contenido = array(
    'privacidad' => array(
        'es' => <<<'HTML'
<p><strong>Última actualización: [FECHA DE PUBLICACIÓN]</strong></p>
<h2>1. Responsable del tratamiento de sus datos personales</h2>
<p><strong>[RAZÓN SOCIAL COMPLETA]</strong> (en adelante "Explora México Tours"), con domicilio en <strong>[DOMICILIO FISCAL COMPLETO]</strong>, Guadalajara, Jalisco, México, es responsable del tratamiento de sus datos personales conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP), su Reglamento y los Lineamientos del Aviso de Privacidad.</p>
<p>Puede contactarnos para cualquier tema relacionado con este aviso en:</p>
<ul>
<li>Correo electrónico: <strong>reserva@exploramexicotours.com</strong></li>
<li>WhatsApp / teléfono: <strong>+52 33 1048 0670</strong></li>
<li>Sitio web: <strong>https://exploramexicotours.com</strong></li>
</ul>
<h2>2. Datos personales que recabamos</h2>
<p>Recabamos los siguientes datos cuando usted los proporciona a través de nuestros formularios de cotización, contacto, reservación de transporte, nuestro asistente de WhatsApp o al comunicarse con nuestros asesores:</p>
<p><strong>Datos de identificación y contacto:</strong> nombre completo, correo electrónico, número de teléfono y/o WhatsApp.</p>
<p><strong>Datos del viaje:</strong> destino o tour de interés, fechas tentativas, número de viajeros (adultos y menores), punto de partida u hospedaje para traslados, y comentarios que usted decida compartir.</p>
<p><strong>Datos de navegación:</strong> utilizamos una cookie propia de duración limitada (30 días) para identificar al asesor de viajes que le compartió un enlace, con el fin de darle una atención personalizada. Asimismo, llevamos estadísticas internas de uso del sitio (páginas visitadas y clics en botones de contacto) de forma <strong>agregada y sin identificarle personalmente</strong>.</p>
<p><strong>No recabamos datos personales sensibles.</strong> Si un menor de edad viaja con usted, los datos del menor (número y edad aproximada) son proporcionados por usted como padre, madre o tutor.</p>
<h2>3. Finalidades del tratamiento</h2>
<p><strong>Finalidades primarias</strong> (necesarias para el servicio que solicita):</p>
<p>a) Atender sus solicitudes de información, cotización y disponibilidad de tours y traslados.</p>
<p>b) Gestionar reservaciones, pagos y la prestación de los servicios turísticos contratados.</p>
<p>c) Contactarle por correo, teléfono o WhatsApp para dar seguimiento a su solicitud.</p>
<p>d) Asignarle un asesor de viajes y dar continuidad a la atención.</p>
<p>e) Emitir comprobantes y cumplir obligaciones legales aplicables.</p>
<p><strong>Finalidades secundarias</strong> (no necesarias para el servicio):</p>
<p>f) Enviarle información promocional sobre tours, experiencias y ofertas.</p>
<p>g) Elaborar estadísticas internas para mejorar nuestros servicios.</p>
<p>Si no desea que sus datos se utilicen para las finalidades secundarias, puede indicarlo en cualquier momento escribiendo a <strong>reserva@exploramexicotours.com</strong> con el asunto "No deseo publicidad". La negativa no será motivo para negarle los servicios que solicite.</p>
<h2>4. Transferencias de datos</h2>
<p>Sus datos podrán compartirse únicamente con: (i) <strong>proveedores de servicios turísticos</strong> (operadores de tours, transportistas, hoteles y plataformas de reservación) exclusivamente en la medida necesaria para prestar el servicio que usted contrata; y (ii) <strong>autoridades competentes</strong> cuando exista requerimiento legal. No vendemos ni cedemos sus datos personales a terceros para fines distintos de los aquí descritos. Fuera de estos casos, cualquier transferencia requerirá su consentimiento.</p>
<h2>5. Derechos ARCO y revocación del consentimiento</h2>
<p>Usted tiene derecho a <strong>Acceder</strong> a sus datos personales, <strong>Rectificarlos</strong> cuando sean inexactos, <strong>Cancelarlos</strong> y <strong>Oponerse</strong> a su tratamiento (derechos ARCO), así como a revocar el consentimiento que nos haya otorgado y a limitar el uso o divulgación de sus datos.</p>
<p>Para ejercerlos, envíe una solicitud a <strong>reserva@exploramexicotours.com</strong> indicando: (i) su nombre completo y medio de contacto; (ii) documento que acredite su identidad (o la de su representante); (iii) descripción clara de los datos y el derecho que desea ejercer. Responderemos en un plazo máximo de <strong>20 días hábiles</strong> contados desde la recepción de la solicitud, y de resultar procedente, se hará efectiva dentro de los <strong>15 días hábiles</strong> siguientes a la respuesta.</p>
<p>Si considera que su derecho a la protección de datos ha sido vulnerado, puede acudir al <strong>Instituto Nacional de Transparencia, Acceso a la Información y Protección de Datos Personales (INAI)</strong> — www.inai.org.mx.</p>
<h2>6. Cookies y tecnologías similares</h2>
<p>Nuestro sitio utiliza cookies propias con fines funcionales y estadísticos: recordar su idioma, identificar al asesor que le refirió (vigencia de 30 días) y medir de forma agregada el uso del sitio. Usted puede deshabilitar las cookies desde la configuración de su navegador; algunas funciones podrían dejar de operar correctamente. En caso de incorporar herramientas de analítica o publicidad de terceros (p. ej. Google Analytics o Meta), se informará en esta sección.</p>
<h2>7. Conservación y seguridad de los datos</h2>
<p>Sus datos se conservan durante el tiempo necesario para las finalidades descritas y para cumplir obligaciones legales. Aplicamos medidas de seguridad administrativas, técnicas y físicas razonables para protegerlos contra daño, pérdida, alteración o uso no autorizado.</p>
<h2>8. Cambios a este aviso</h2>
<p>Este aviso puede modificarse para reflejar cambios legales o de nuestros servicios. Cualquier cambio se publicará en esta misma página con su fecha de actualización. Le recomendamos revisarla periódicamente.</p>
HTML,
        'en' => <<<'HTML'
<p><strong>Last updated: [PUBLICATION DATE]</strong></p>
<h2>1. Data controller</h2>
<p><strong>[FULL LEGAL NAME]</strong> ("Explora México Tours"), with registered address at <strong>[FULL ADDRESS]</strong>, Guadalajara, Jalisco, Mexico, is responsible for the processing of your personal data in accordance with Mexico's Federal Law on the Protection of Personal Data Held by Private Parties (LFPDPPP).</p>
<p>Contact us regarding this notice at: <strong>reserva@exploramexicotours.com</strong> · WhatsApp <strong>+52 33 1048 0670</strong> · <strong>https://exploramexicotours.com</strong></p>
<h2>2. Personal data we collect</h2>
<p>When you use our quote, contact or transportation forms, our WhatsApp assistant, or communicate with our travel advisors, we may collect: your <strong>name, email and phone/WhatsApp number</strong>; <strong>trip details</strong> (destination or tour of interest, tentative dates, number of adult and minor travelers, pick-up point, and any comments you share); and <strong>browsing data</strong> — a first-party cookie valid for 30 days that identifies the travel advisor who shared a link with you, plus aggregated, non-identifying site-usage statistics (pages visited and clicks on contact buttons).</p>
<p>We do <strong>not</strong> collect sensitive personal data. Information about minors traveling with you is provided by you as their parent or guardian.</p>
<h2>3. Purposes</h2>
<p><strong>Primary purposes:</strong> responding to your information, quote and availability requests; managing bookings, payments and the delivery of contracted services; following up by email, phone or WhatsApp; assigning you a travel advisor; issuing receipts and complying with legal obligations.</p>
<p><strong>Secondary purposes:</strong> sending you promotional information about tours and offers, and internal statistics to improve our services. You may opt out at any time by emailing <strong>reserva@exploramexicotours.com</strong> with the subject "No advertising". Opting out will not affect the services you request.</p>
<h2>4. Data transfers</h2>
<p>Your data may be shared only with: (i) <strong>tourism service providers</strong> (tour operators, transport companies, hotels and booking platforms) strictly as needed to deliver the service you book; and (ii) <strong>competent authorities</strong> upon legal request. We do not sell your personal data. Any other transfer will require your consent.</p>
<h2>5. ARCO rights and consent withdrawal</h2>
<p>You may exercise your rights of <strong>Access, Rectification, Cancellation and Objection</strong> (ARCO), withdraw your consent, and limit the use or disclosure of your data by writing to <strong>reserva@exploramexicotours.com</strong>, including your full name, contact details, proof of identity, and a clear description of your request. We will reply within <strong>20 business days</strong>, and if applicable, act on the request within the following <strong>15 business days</strong>. You may also contact Mexico's data-protection authority, <strong>INAI</strong> (www.inai.org.mx).</p>
<h2>6. Cookies</h2>
<p>We use first-party cookies for functional and statistical purposes: remembering your language, identifying the advisor who referred you (30-day validity), and measuring site usage in aggregate. You can disable cookies in your browser settings; some features may stop working properly. If third-party analytics or advertising tools (e.g. Google Analytics, Meta) are added, this section will be updated.</p>
<h2>7. Retention and security</h2>
<p>We keep your data only as long as needed for the purposes described and to meet legal obligations, and we apply reasonable administrative, technical and physical safeguards to protect it.</p>
<h2>8. Changes to this notice</h2>
<p>We may update this notice to reflect legal or service changes. Updates will be published on this page with their revision date.</p>
HTML,
    ),
    'terminos' => array(
        'es' => <<<'HTML'
<p><strong>Última actualización: [FECHA DE PUBLICACIÓN]</strong></p>
<h2>1. Aceptación</h2>
<p>El uso del sitio <strong>https://exploramexicotours.com</strong> (el "Sitio") y la contratación de los servicios de <strong>[RAZÓN SOCIAL COMPLETA]</strong> ("Explora México Tours", "nosotros") implican la aceptación de estos Términos y Condiciones. Si no está de acuerdo con ellos, le pedimos no utilizar el Sitio ni contratar nuestros servicios.</p>
<h2>2. Nuestros servicios</h2>
<p>Explora México Tours ofrece tours, experiencias turísticas y servicios de transporte turístico y ejecutivo en México, operados directamente o en conjunto con proveedores turísticos seleccionados. La información del Sitio (itinerarios, horarios, fotografías, inclusiones) es descriptiva; los detalles definitivos de cada servicio se confirman al momento de la reservación.</p>
<h2>3. Cotizaciones, precios y pagos</h2>
<p>a) Los precios se expresan en <strong>pesos mexicanos (MXN)</strong> e incluyen IVA salvo indicación en contrario. Los montos mostrados por el cotizador del Sitio son <strong>estimados informativos</strong> y no constituyen una reservación; todo precio se confirma por escrito (correo o WhatsApp) por nuestro equipo antes del pago.</p>
<p>b) Los precios pueden variar por temporada, disponibilidad, número de viajeros y tipo de vehículo o habitación.</p>
<p>c) La reservación se considera confirmada al recibir el pago (total o el anticipo indicado) y la confirmación escrita de Explora México Tours.</p>
<p>d) Los medios de pago disponibles se informan durante el proceso de reservación.</p>
<h2>4. Cambios y cancelaciones</h2>
<p>a) Cada tour cuenta con su <strong>política de cancelación</strong> específica, publicada en la página del propio tour y/o en su confirmación de reservación; dicha política prevalece para ese servicio.</p>
<p>b) Salvo indicación distinta en la política del tour: los cambios de fecha están sujetos a disponibilidad; las cancelaciones con la anticipación mínima señalada podrán generar cargos o reembolsos parciales; y la <strong>no presentación (no-show)</strong> en el punto y hora de salida no genera reembolso.</p>
<p>c) Explora México Tours podrá modificar itinerarios u horarios, o cancelar una salida, por causas de fuerza mayor, condiciones climatológicas, seguridad o mínimos de ocupación. En caso de cancelación de nuestra parte, se ofrecerá una fecha alterna o el reembolso de lo pagado por el servicio no prestado.</p>
<h2>5. Responsabilidades del viajero</h2>
<p>a) Proporcionar información veraz y completa al reservar (incluido el número de adultos y menores).</p>
<p>b) Presentarse puntualmente en el punto de salida y portar identificación oficial vigente.</p>
<p>c) Atender en todo momento las indicaciones del guía u operador, especialmente en actividades al aire libre o de aventura (senderismo, cañonismo, ciclismo, actividades acuáticas), que implican riesgos inherentes y pueden requerir condiciones físicas o de salud adecuadas, así como equipo de seguridad proporcionado.</p>
<p>d) Los <strong>menores de edad</strong> deben viajar acompañados de su padre, madre o tutor, quien es responsable de ellos durante todo el servicio. Algunos tours no admiten menores; esto se indica en cada tour.</p>
<p>e) El consumo de alcohol en tours con degustaciones (p. ej. tequila o vino) está reservado a mayores de 18 años.</p>
<h2>6. Límites de responsabilidad</h2>
<p>Explora México Tours responde por la prestación de los servicios contratados en los términos confirmados. No será responsable por: (i) caso fortuito o fuerza mayor; (ii) daños derivados del incumplimiento del viajero a las indicaciones de seguridad; (iii) pérdida de objetos personales; ni (iv) servicios adquiridos por el viajero directamente con terceros ajenos a la reservación. Lo anterior sin perjuicio de los derechos irrenunciables que otorga la legislación mexicana al consumidor.</p>
<h2>7. Propiedad intelectual</h2>
<p>Los contenidos del Sitio (textos, fotografías, logotipos, diseño y marca "Explora México Tours") son propiedad de Explora México Tours o se usan con autorización de sus titulares. Queda prohibida su reproducción o uso comercial sin consentimiento previo por escrito.</p>
<h2>8. Datos personales</h2>
<p>El tratamiento de sus datos personales se rige por nuestro <a href="/aviso-de-privacidad/">Aviso de Privacidad</a>.</p>
<h2>9. Atención al cliente y quejas</h2>
<p>Para dudas, aclaraciones o quejas: <strong>reserva@exploramexicotours.com</strong> · WhatsApp <strong>+52 33 1048 0670</strong>. También puede acudir a la <strong>Procuraduría Federal del Consumidor (PROFECO)</strong> — www.profeco.gob.mx.</p>
<h2>10. Legislación y jurisdicción</h2>
<p>Estos Términos se rigen por las leyes de los Estados Unidos Mexicanos. Para cualquier controversia, las partes se someten a los tribunales competentes de <strong>Guadalajara, Jalisco</strong>, sin perjuicio de la competencia de PROFECO en materia de consumo.</p>
<h2>11. Modificaciones</h2>
<p>Podemos actualizar estos Términos en cualquier momento; la versión vigente será la publicada en esta página con su fecha de actualización.</p>
HTML,
        'en' => <<<'HTML'
<p><strong>Last updated: [PUBLICATION DATE]</strong></p>
<h2>1. Acceptance</h2>
<p>Using <strong>https://exploramexicotours.com</strong> (the "Site") and booking services from <strong>[FULL LEGAL NAME]</strong> ("Explora México Tours", "we") implies acceptance of these Terms &amp; Conditions.</p>
<h2>2. Our services</h2>
<p>Explora México Tours offers tours, travel experiences and tourist/executive transportation in Mexico, operated directly or with selected partners. Site content (itineraries, schedules, photos, inclusions) is descriptive; final details are confirmed at booking.</p>
<h2>3. Quotes, prices and payment</h2>
<p>a) Prices are in <strong>Mexican pesos (MXN)</strong> and include VAT unless stated otherwise. Amounts shown by the Site's quote tool are <strong>informative estimates</strong>, not a booking; every price is confirmed in writing (email or WhatsApp) by our team before payment.</p>
<p>b) Prices may vary by season, availability, group size and vehicle or room type.</p>
<p>c) A booking is confirmed upon receipt of payment (full or the indicated deposit) and our written confirmation.</p>
<p>d) Available payment methods are informed during the booking process.</p>
<h2>4. Changes and cancellations</h2>
<p>a) Each tour has its own <strong>cancellation policy</strong>, published on the tour's page and/or your booking confirmation; that policy prevails for that service.</p>
<p>b) Unless the tour's policy states otherwise: date changes are subject to availability; cancellations may involve charges or partial refunds depending on notice given; <strong>no-shows</strong> at the departure point are non-refundable.</p>
<p>c) We may modify itineraries or schedules, or cancel a departure, due to force majeure, weather, safety or minimum-occupancy reasons. If we cancel, we will offer an alternative date or a refund for the unrendered service.</p>
<h2>5. Traveler responsibilities</h2>
<p>Provide accurate information when booking (including the number of adults and minors); arrive on time with a valid official ID; follow the guide's or operator's instructions at all times — outdoor and adventure activities (hiking, canyoning, cycling, water activities) carry inherent risks and may require adequate physical condition and the use of provided safety equipment. <strong>Minors</strong> must travel with a parent or guardian, who remains responsible for them; some tours do not admit minors, as indicated on each tour. Alcohol tastings (e.g. tequila or wine) are restricted to travelers 18+.</p>
<h2>6. Liability</h2>
<p>Explora México Tours is responsible for delivering the contracted services as confirmed. We are not liable for: (i) force majeure; (ii) damages arising from a traveler's failure to follow safety instructions; (iii) loss of personal belongings; or (iv) services purchased directly from third parties outside the booking — without prejudice to consumers' non-waivable rights under Mexican law.</p>
<h2>7. Intellectual property</h2>
<p>Site content (texts, photographs, logos, design and the "Explora México Tours" brand) belongs to Explora México Tours or is used with permission. Reproduction or commercial use without prior written consent is prohibited.</p>
<h2>8. Personal data</h2>
<p>Your personal data is processed under our <a href="/en/aviso-de-privacidad/">Privacy Notice</a>.</p>
<h2>9. Customer service</h2>
<p>Questions or complaints: <strong>reserva@exploramexicotours.com</strong> · WhatsApp <strong>+52 33 1048 0670</strong>. You may also contact Mexico's consumer-protection agency, <strong>PROFECO</strong> (www.profeco.gob.mx).</p>
<h2>10. Governing law</h2>
<p>These Terms are governed by the laws of Mexico. Any dispute shall be submitted to the competent courts of <strong>Guadalajara, Jalisco</strong>, without prejudice to PROFECO's jurisdiction in consumer matters.</p>
<h2>11. Changes</h2>
<p>We may update these Terms at any time; the current version is the one published on this page with its revision date.</p>
HTML,
    ),
);

get_header();
?>
<main class="emt-legal">
    <section class="emt-legal-hero">
        <div class="emt-container">
            <span class="emt-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
            <h1><?php echo esc_html( $titulo ); ?></h1>
        </div>
    </section>
    <section class="emt-section">
        <div class="emt-container emt-legal__body">
            <?php echo wp_kses_post( $contenido[ $doc ][ $lang === 'en' ? 'en' : 'es' ] ); ?>
        </div>
    </section>
</main>
<?php
get_footer();
