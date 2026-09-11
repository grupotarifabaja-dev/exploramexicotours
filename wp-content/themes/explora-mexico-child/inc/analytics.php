<?php
/**
 * Meta Pixel (Facebook) — instalación base.
 *
 * Carga el pixel SOLO en el sitio público de producción:
 *   · Fuera de wp-admin, del panel de gestión (/panel/), feeds y embeds.
 *   · Nunca en staging (staging.*), para no ensuciar los datos del cliente
 *     con tráfico de pruebas.
 *   · No se carga para usuarios logueados con permisos de gestión, así las
 *     visitas del propio equipo no cuentan como tráfico.
 *
 * Solo evento PageView (instalación básica). Si más adelante se quieren
 * eventos de conversión (Lead al enviar formularios, Contact al dar clic en
 * WhatsApp), se agregan aquí sin tocar nada más.
 *
 * El ID se puede sobreescribir sin editar código con el filtro
 * 'emt_meta_pixel_id' o definiendo EMT_META_PIXEL_ID en wp-config.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** ID del pixel de Meta, o '' para desactivarlo. */
function emt_meta_pixel_id() {
    $id = defined( 'EMT_META_PIXEL_ID' ) ? EMT_META_PIXEL_ID : '2135905200673577';
    return preg_replace( '/\D/', '', (string) apply_filters( 'emt_meta_pixel_id', $id ) );
}

/** ¿Debe emitirse el pixel en esta petición? */
function emt_meta_pixel_activo() {
    if ( emt_meta_pixel_id() === '' ) { return false; }
    if ( is_admin() || is_feed() || is_embed() ) { return false; }
    if ( function_exists( 'emt_panel_is_request' ) && emt_panel_is_request() ) { return false; }
    // Staging: ambiente interno, no debe reportar a la cuenta publicitaria.
    if ( function_exists( 'emt_is_staging_host' ) && emt_is_staging_host() ) { return false; }
    // El propio equipo no cuenta como visita.
    if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) { return false; }
    return true;
}

add_action( 'wp_head', function () {
    if ( ! emt_meta_pixel_activo() ) { return; }
    $id = emt_meta_pixel_id();
    ?>
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo esc_js( $id ); ?>');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=<?php echo esc_attr( $id ); ?>&ev=PageView&noscript=1"
alt="" /></noscript>
<!-- End Meta Pixel Code -->
    <?php
}, 2 );
