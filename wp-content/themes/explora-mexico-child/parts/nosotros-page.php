<?php
/**
 * Página "Nosotros / Quiénes Somos" (/nosotros/ y /en/nosotros/).
 * Contenido de "Quiénes Somos.docx" del cliente. Renderizada por inc/nosotros.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';

$L_es = array(
    'eyebrow'     => 'Quiénes somos',
    'title'       => 'Explora México con quien lo conoce',
    'intro'       => '¡Facilitamos tu experiencia de viaje! Déjanos llevarte por esta aventura de explorar Jalisco y todo México.',
    'nosotros_t'  => 'Nosotros',
    'nosotros_p'  => 'Somos una empresa de transporte, tours y actividades guiadas en México. Nacimos en 2009 en la ciudad de Guadalajara, Jalisco, y nos dedicamos a brindar experiencias y soluciones a través de cada uno de los servicios que ofrecemos. Nuestra inquietud viajera nos llevó a explorar sitios naturales, culturales y rincones de México que queremos compartir con más espíritus viajeros, creando experiencias positivas y, sobre todo, conciencia sobre la riqueza turística de México.',
    'mision_t'    => 'Misión',
    'mision_p'    => 'Ofrecer una experiencia de viaje única a nuestros clientes a través de la calidad en los servicios y la atención personalizada de nuestro equipo, con una oferta de excursiones y actividades enfocadas al turismo alternativo, buscando siempre el desarrollo de cada colaborador y compartiendo una conciencia responsable hacia el cuidado, protección y conservación de los recursos naturales como base del turismo en México.',
    'vision_t'    => 'Visión',
    'vision_p'    => 'Consolidarnos como una empresa líder, reconocida a nivel nacional e internacional, en la integración de servicios turísticos en México.',
    'porque_t'    => '¿Por qué reservar con nosotros?',
    'porque'      => array(
        'Más de 15 años de experiencia en turismo.',
        'Capacitación constante de nuestro equipo de trabajo.',
        'Buscamos siempre una atención al cliente positiva.',
        'Cada servicio de tu viaje es evaluado previamente por nuestro equipo.',
        'Asistencia antes, durante y después de cada experiencia de viaje.',
        'Adaptamos las experiencias de viaje a cada viajero.',
        'La retroalimentación de nuestros clientes nos ayuda a mejorar.',
        'Hacemos las cosas con cariño y empeño: nos gusta lo que hacemos.',
    ),
    'servicios_t' => 'Nuestros servicios',
    'servicios'   => array(
        'Transporte ejecutivo',
        'Traslado aeropuerto – hotel – aeropuerto',
        'Renta de Sprinter con chofer',
        'Integración de viajes a la medida',
        'Guías y actividades',
        'Reservación de hoteles nacionales',
        'Paquetes de viaje todo incluido',
        'Parques de diversiones',
        'Tours y actividades',
        'Boletos de autobús y avión',
    ),
    'sellos_t'    => 'Certificaciones y reconocimientos',
    'sellos'      => array(
        'Distintivo Moderniza (SECTUR) — 2017',
        'Distintivo C · Calidad en la Atención al Turista — 2019',
        'Especialista en destino Barrancas del Cobre',
        'Técnico Tequilero «Tequillier»',
        'Distintivo I · Anfitriona al Estilo Jalisco',
        'Miembro AMAV · VisitJalisco',
    ),
    'cta_t'       => '¿Listo para tu próxima aventura?',
    'cta_p'       => 'Cuéntanos qué te imaginas y armamos tu viaje a la medida.',
    'cta_btn'     => 'Cotizar mi viaje',
    'cta_btn2'    => 'Contáctanos',
);

$L_en = array(
    'eyebrow'     => 'About us',
    'title'       => 'Discover Mexico with the locals who know it',
    'intro'       => 'We make travel easy! Let us take you on the adventure of exploring Jalisco and all of Mexico.',
    'nosotros_t'  => 'About us',
    'nosotros_p'  => 'We are a transportation, tours and guided-activities company in Mexico. We were born in 2009 in the city of Guadalajara, Jalisco, and we are dedicated to delivering experiences and solutions through every service we offer. Our traveler spirit led us to explore natural and cultural sites and hidden corners of Mexico that we want to share with fellow travelers, creating positive experiences and, above all, awareness of Mexico\'s tourism richness.',
    'mision_t'    => 'Mission',
    'mision_p'    => 'To offer our clients a unique travel experience through quality services and the personalized attention of our team, with a selection of excursions and activities focused on alternative tourism, always seeking the growth of every team member and sharing a responsible awareness toward the care, protection and conservation of natural resources as the foundation of tourism in Mexico.',
    'vision_t'    => 'Vision',
    'vision_p'    => 'To establish ourselves as a leading company, recognized nationally and internationally, in the integration of tourism services in Mexico.',
    'porque_t'    => 'Why book with us?',
    'porque'      => array(
        'Over 15 years of experience in tourism.',
        'Ongoing training for our team.',
        'We always aim for a positive customer experience.',
        'Every service in your trip is reviewed beforehand by our team.',
        'Assistance before, during and after each travel experience.',
        'We tailor each travel experience to every traveler.',
        'Our clients\' feedback helps us improve.',
        'We do things with care and dedication — we love what we do.',
    ),
    'servicios_t' => 'Our services',
    'servicios'   => array(
        'Executive transportation',
        'Airport – hotel – airport transfers',
        'Sprinter rental with driver',
        'Custom, made-to-measure trips',
        'Guides and activities',
        'Domestic hotel booking',
        'All-inclusive travel packages',
        'Theme parks',
        'Tours and activities',
        'Bus and flight tickets',
    ),
    'sellos_t'    => 'Certifications & recognitions',
    'sellos'      => array(
        'Moderniza Distinction (SECTUR) — 2017',
        'Distinction C · Quality in Tourist Service — 2019',
        'Copper Canyon destination specialist',
        'Tequila Technician «Tequillier»',
        'Distinction I · Host in the Jalisco Style',
        'AMAV member · VisitJalisco',
    ),
    'cta_t'       => 'Ready for your next adventure?',
    'cta_p'       => 'Tell us what you have in mind and we will craft your trip to measure.',
    'cta_btn'     => 'Get a quote',
    'cta_btn2'    => 'Contact us',
);

$L = ( $lang === 'en' ) ? $L_en : $L_es;
$emt_pfx = ( $lang === 'en' ) ? '/en' : '';

get_header();
?>
<main class="emt-nosotros">

    <section class="emt-nosotros-hero">
        <div class="emt-container">
            <?php if ( function_exists( 'emt_breadcrumbs' ) ) { emt_breadcrumbs(); } ?>
            <div class="emt-heading emt-heading--left emt-nosotros-hero__heading">
                <span class="emt-eyebrow"><?php echo esc_html( $L['eyebrow'] ); ?></span>
                <h1 class="emt-title"><?php echo esc_html( $L['title'] ); ?></h1>
            </div>
            <p class="emt-nosotros-hero__intro"><?php echo esc_html( $L['intro'] ); ?></p>
        </div>
    </section>

    <section class="emt-section">
        <div class="emt-container emt-nosotros-block">
            <h2 class="emt-nosotros__h2"><?php echo esc_html( $L['nosotros_t'] ); ?></h2>
            <p class="emt-nosotros__lead"><?php echo esc_html( $L['nosotros_p'] ); ?></p>
        </div>
    </section>

    <section class="emt-section emt-section--tint">
        <div class="emt-container emt-mv-grid">
            <article class="emt-mv-card">
                <h3 class="emt-mv-card__title"><?php echo esc_html( $L['mision_t'] ); ?></h3>
                <p><?php echo esc_html( $L['mision_p'] ); ?></p>
            </article>
            <article class="emt-mv-card">
                <h3 class="emt-mv-card__title"><?php echo esc_html( $L['vision_t'] ); ?></h3>
                <p><?php echo esc_html( $L['vision_p'] ); ?></p>
            </article>
        </div>
    </section>

    <section class="emt-section">
        <div class="emt-container">
            <h2 class="emt-nosotros__h2"><?php echo esc_html( $L['porque_t'] ); ?></h2>
            <ul class="emt-nosotros-reasons">
                <?php foreach ( $L['porque'] as $r ) : ?>
                    <li><?php echo esc_html( $r ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="emt-section emt-section--tint">
        <div class="emt-container">
            <h2 class="emt-nosotros__h2"><?php echo esc_html( $L['servicios_t'] ); ?></h2>
            <ul class="emt-nosotros-services">
                <?php foreach ( $L['servicios'] as $s ) : ?>
                    <li><?php echo esc_html( $s ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="emt-section">
        <div class="emt-container">
            <h2 class="emt-nosotros__h2"><?php echo esc_html( $L['sellos_t'] ); ?></h2>
            <ul class="emt-nosotros-sellos">
                <?php foreach ( $L['sellos'] as $c ) : ?>
                    <li><?php echo esc_html( $c ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="emt-nosotros-cta">
        <div class="emt-container">
            <h2 class="emt-nosotros-cta__title"><?php echo esc_html( $L['cta_t'] ); ?></h2>
            <p class="emt-nosotros-cta__sub"><?php echo esc_html( $L['cta_p'] ); ?></p>
            <div class="emt-nosotros-cta__btns">
                <a class="emt-btn emt-btn--white" href="<?php echo esc_url( home_url( $emt_pfx . '/cotizacion/' ) ); ?>"><?php echo esc_html( $L['cta_btn'] ); ?></a>
                <a class="emt-btn emt-btn--ghost" href="<?php echo esc_url( home_url( $emt_pfx . '/contacto/' ) ); ?>"><?php echo esc_html( $L['cta_btn2'] ); ?></a>
            </div>
        </div>
    </section>

</main>
<?php
get_footer();
