<?php
/**
 * Página "Cotización de grupos" (/cotizacion/). Renderizada por inc/cotizacion.php.
 * Formulario enviado por AJAX (assets/js/cotizacion-form.js).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$lang = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';

$L_es = array(
    'eyebrow'   => 'Viajes a la medida',
    'title'     => 'Cotiza tu viaje de grupo',
    'sub'       => 'Grupos, empresas (MICE), familias o experiencias personalizadas por México. Cuéntanos qué necesitas y te armamos una propuesta a la medida.',
    'benef_t'   => '¿Por qué cotizar con nosotros?',
    'benef'     => array( 'Más de 15 años creando viajes por México', 'Atención personalizada por un asesor', 'Transporte, hospedaje y experiencias en un solo lugar', 'Distintivo Moderniza (SECTUR)' ),
    'form_t'    => 'Solicita tu propuesta',
    'f_nombre'  => 'Nombre completo', 'f_correo' => 'Correo', 'f_tel' => 'WhatsApp',
    'f_tipo'    => 'Tipo de viaje',
    't_grupo'   => 'Grupo', 't_mice' => 'Empresa / MICE', 't_personalizado' => 'Personalizado', 't_otro' => 'Otro',
    'f_personas'=> 'Número de personas',
    'f_fechas'  => 'Fechas tentativas', 'f_fechas_ph' => 'Ej. segunda quincena de octubre',
    'f_interes' => 'Destino o tour de interés (opcional)',
    'f_detalles'=> 'Cuéntanos más de tu grupo',
    'f_enviar'  => 'Enviar solicitud', 'f_enviando' => 'Enviando…',
    'f_error'   => 'Revisa los campos obligatorios.',
    'f_conexion'=> 'Error de conexión. Intenta de nuevo.',
    'wa_o'      => 'o escríbenos directo por WhatsApp',
);
$L_en = array(
    'eyebrow'   => 'Tailor-made trips',
    'title'     => 'Get a group travel quote',
    'sub'       => 'Groups, companies (MICE), families or custom experiences across Mexico. Tell us what you need and we will build a tailored proposal.',
    'benef_t'   => 'Why quote with us?',
    'benef'     => array( 'Over 15 years crafting trips across Mexico', 'Personalized attention from an advisor', 'Transport, lodging and experiences in one place', 'Distintivo Moderniza (SECTUR)' ),
    'form_t'    => 'Request your proposal',
    'f_nombre'  => 'Full name', 'f_correo' => 'Email', 'f_tel' => 'WhatsApp',
    'f_tipo'    => 'Trip type',
    't_grupo'   => 'Group', 't_mice' => 'Company / MICE', 't_personalizado' => 'Custom', 't_otro' => 'Other',
    'f_personas'=> 'Number of travelers',
    'f_fechas'  => 'Tentative dates', 'f_fechas_ph' => 'e.g. second half of October',
    'f_interes' => 'Destination or tour of interest (optional)',
    'f_detalles'=> 'Tell us more about your group',
    'f_enviar'  => 'Send request', 'f_enviando' => 'Sending…',
    'f_error'   => 'Please review the required fields.',
    'f_conexion'=> 'Connection error. Please try again.',
    'wa_o'      => 'or message us directly on WhatsApp',
);
$L = ( $lang === 'en' ) ? $L_en : $L_es;

$tipos = array( 'grupo' => $L['t_grupo'], 'mice' => $L['t_mice'], 'personalizado' => $L['t_personalizado'], 'otro' => $L['t_otro'] );

get_header();
?>
<main class="emt-cotiza">

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

    <section class="emt-section emt-cotiza-body">
        <div class="emt-container emt-cotiza-grid">

            <aside class="emt-cotiza-aside">
                <h2 class="emt-cotiza-aside__title"><?php echo esc_html( $L['benef_t'] ); ?></h2>
                <ul class="emt-cotiza-benefits">
                    <?php foreach ( $L['benef'] as $b ) : ?>
                        <li><?php echo esc_html( $b ); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="emt-cotiza-wa">
                    <?php echo esc_html( $L['wa_o'] ); ?>:<br />
                    <a class="emt-btn emt-btn--whatsapp" href="https://wa.me/523310480670" target="_blank" rel="noopener">WhatsApp</a>
                </p>
            </aside>

            <div class="emt-cotiza-form-wrap">
                <h2 class="emt-cotiza-form-title"><?php echo esc_html( $L['form_t'] ); ?></h2>
                <form class="emt-cotiza-form" data-emt-cotiza-form
                      data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
                      data-nonce="<?php echo esc_attr( wp_create_nonce( 'emt_cotizacion' ) ); ?>"
                      data-msg-error="<?php echo esc_attr( $L['f_error'] ); ?>"
                      data-msg-conexion="<?php echo esc_attr( $L['f_conexion'] ); ?>"
                      data-msg-enviando="<?php echo esc_attr( $L['f_enviando'] ); ?>"
                      novalidate>
                    <div class="emt-cotiza-form__grid">
                        <div class="emt-field"><label><?php echo esc_html( $L['f_nombre'] ); ?> *</label><input type="text" name="nombre" required /></div>
                        <div class="emt-field"><label><?php echo esc_html( $L['f_tel'] ); ?> *</label><input type="tel" name="telefono" required /></div>
                        <div class="emt-field"><label><?php echo esc_html( $L['f_correo'] ); ?> *</label><input type="email" name="correo" required /></div>
                        <div class="emt-field"><label><?php echo esc_html( $L['f_personas'] ); ?> *</label><input type="number" name="personas" min="1" value="1" required /></div>
                        <fieldset class="emt-field emt-field--full">
                            <legend><?php echo esc_html( $L['f_tipo'] ); ?></legend>
                            <div class="emt-cotiza-radios">
                                <?php $first = true; foreach ( $tipos as $k => $lbl ) : ?>
                                    <label class="emt-cotiza-radio"><input type="radio" name="tipo_viaje" value="<?php echo esc_attr( $k ); ?>" <?php checked( $first ); $first = false; ?> /> <?php echo esc_html( $lbl ); ?></label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                        <div class="emt-field"><label><?php echo esc_html( $L['f_fechas'] ); ?></label><input type="text" name="fechas" placeholder="<?php echo esc_attr( $L['f_fechas_ph'] ); ?>" /></div>
                        <div class="emt-field"><label><?php echo esc_html( $L['f_interes'] ); ?></label><input type="text" name="interes" /></div>
                        <div class="emt-field emt-field--full"><label><?php echo esc_html( $L['f_detalles'] ); ?></label><textarea name="detalles" rows="4"></textarea></div>
                    </div>
                    <div class="emt-cotiza-form__bar">
                        <span class="emt-cotiza-form__msg" data-cotiza-msg aria-live="polite"></span>
                        <button type="submit" class="emt-btn emt-btn--cta"><?php echo esc_html( $L['f_enviar'] ); ?></button>
                    </div>
                </form>
            </div>

        </div>
    </section>

</main>
<?php
get_footer();
