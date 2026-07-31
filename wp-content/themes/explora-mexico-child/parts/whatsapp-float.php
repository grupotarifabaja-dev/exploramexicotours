<?php
/**
 * Parte: Botón WhatsApp flotante + FLUJO GUIADO (doc maestro §8.1 y §9.3).
 * Número desde la Options page (wa_number). Animación de pulso.
 *
 * Con JS: el botón abre un widget de preguntas paso a paso (reservar /
 * cotizar / info → destino o personas → email opcional) que arma un mensaje
 * de WhatsApp pre-llenado, con atribución del asesor (?ref) y estado en
 * localStorage. Sin JS: el enlace sigue abriendo el chat directo.
 *
 * NOTA: el under construction tiene su propio botón (.wa-float); este usa una
 * clase distinta (.emt-wa-float) y solo se carga en el sitio real, por lo que
 * no se duplican.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wa = function_exists( 'get_field' ) ? get_field( 'wa_number', 'option' ) : '';
if ( empty( $wa ) ) {
    $wa = '523310480670'; // fallback
}
$wa  = preg_replace( '/\D/', '', (string) $wa );
$msg = rawurlencode( emt_t( 'wa_msg_default' ) );

// Destinos para el paso "¿Qué destino?" (solo con tours publicados).
$wa_destinos = get_terms( array( 'taxonomy' => 'tour_destino', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
$wa_destinos = is_wp_error( $wa_destinos ) ? array() : array_values( wp_list_pluck( $wa_destinos, 'name' ) );

// Atribución: asesor referido (cookie ?ref), igual que el cotizador.
$wa_asesor = '';
if ( ! empty( $_COOKIE['emt_ref_asesor'] ) ) {
    $ref_post = get_page_by_path( sanitize_title( wp_unslash( $_COOKIE['emt_ref_asesor'] ) ), OBJECT, 'asesor' );
    if ( $ref_post && $ref_post->post_status === 'publish' ) {
        $wa_asesor = get_the_title( $ref_post );
    }
}

$wa_cfg = array(
    'wa'       => $wa,
    'destinos' => $wa_destinos,
    'asesor'   => $wa_asesor,
    't'        => array(
        'titulo'    => emt_t( 'wa_g_titulo' ),
        'sub'       => emt_t( 'wa_g_sub' ),
        'pregunta'  => emt_t( 'wa_g_pregunta' ),
        'reservar'  => emt_t( 'wa_g_reservar' ),
        'cotizar'   => emt_t( 'wa_g_cotizar' ),
        'info'      => emt_t( 'wa_g_info' ),
        'q_destino' => emt_t( 'wa_g_q_destino' ),
        'no_se'     => emt_t( 'wa_g_no_se' ),
        'q_pax'     => emt_t( 'wa_g_q_pax' ),
        'q_email'   => emt_t( 'wa_g_q_email' ),
        'email_ph'  => emt_t( 'wa_g_email_ph' ),
        'continuar' => emt_t( 'wa_g_continuar' ),
        'omitir'    => emt_t( 'wa_g_omitir' ),
        'atras'     => emt_t( 'wa_g_atras' ),
        'listo'     => emt_t( 'wa_g_listo' ),
        'abrir'     => emt_t( 'wa_g_abrir' ),
        'reiniciar' => emt_t( 'wa_g_reiniciar' ),
        'msg_tour'  => emt_t( 'wa_g_msg_tour' ),
        'msg_dest'  => emt_t( 'wa_g_msg_dest' ),
        'msg_cot'   => emt_t( 'wa_g_msg_cot' ),
        'msg_pax'   => emt_t( 'wa_g_msg_pax' ),
        'msg_info'  => emt_t( 'wa_g_msg_info' ),
        'msg_email' => emt_t( 'wa_g_msg_email' ),
        'atendido'  => emt_t( 'atendido_por' ),
    ),
);
?>
<a class="emt-wa-float" data-wa-guide-toggle href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo esc_attr( $msg ); ?>"
   target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" aria-expanded="false">
    <span class="emt-wa-float__pulse" aria-hidden="true"></span>
    <svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>
</a>

<div class="emt-wa-guide" data-wa-guide hidden>
    <script type="application/json" data-wa-guide-cfg><?php echo wp_json_encode( $wa_cfg ); ?></script>
    <div class="emt-wa-guide__head">
        <div class="emt-wa-guide__head-txt">
            <strong><?php echo esc_html( emt_t( 'wa_g_titulo' ) ); ?></strong>
            <span><?php echo esc_html( emt_t( 'wa_g_sub' ) ); ?></span>
        </div>
        <button type="button" class="emt-wa-guide__close" data-wa-guide-close aria-label="Cerrar">&times;</button>
    </div>
    <div class="emt-wa-guide__body" data-wa-guide-body></div>
</div>
