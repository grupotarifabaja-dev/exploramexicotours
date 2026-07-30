<?php
/**
 * Estadísticas propias del sitio (sin depender de Google):
 *   - Visitas a páginas (excluye equipo logueado, panel, bots y 404).
 *   - Vistas por tour (meta _emt_views) para "Tours más vistos".
 *   - Clicks a WhatsApp / llamadas (beacon JS emt-stats.js).
 *   - Solicitudes del cotizador (disponibilidad / más información).
 *   - Formularios enviados (cotización y contacto).
 *
 * Tabla: {prefix}emt_stats (fecha, metrica, total) — un renglón por día y métrica.
 * Cuando llegue GA4 esto se complementa, no se sustituye: alimenta el panel del cliente.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function emt_stats_table() {
    global $wpdb;
    return $wpdb->prefix . 'emt_stats';
}

/** Crea la tabla una sola vez. */
add_action( 'init', function () {
    if ( get_option( 'emt_stats_db_v1' ) ) { return; }
    global $wpdb;
    $t       = emt_stats_table();
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( "CREATE TABLE $t (
        fecha date NOT NULL,
        metrica varchar(32) NOT NULL,
        total int unsigned NOT NULL DEFAULT 0,
        PRIMARY KEY  (fecha, metrica)
    ) $charset;" );
    update_option( 'emt_stats_db_v1', 1, false );
}, 4 );

/** Suma 1 (o $n) a una métrica del día de hoy. */
function emt_stats_bump( $metrica, $n = 1 ) {
    global $wpdb;
    $t = emt_stats_table();
    $wpdb->query( $wpdb->prepare(
        "INSERT INTO $t (fecha, metrica, total) VALUES (%s, %s, %d)
         ON DUPLICATE KEY UPDATE total = total + %d",
        current_time( 'Y-m-d' ), sanitize_key( $metrica ), $n, $n
    ) );
}

/** Total de una métrica en los últimos $dias días (incluye hoy). */
function emt_stats_total( $metrica, $dias = 30 ) {
    global $wpdb;
    $t     = emt_stats_table();
    $desde = gmdate( 'Y-m-d', current_time( 'timestamp' ) - ( $dias - 1 ) * DAY_IN_SECONDS );
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COALESCE(SUM(total),0) FROM $t WHERE metrica = %s AND fecha >= %s", $metrica, $desde
    ) );
}

/** Serie diaria [fecha => total] de los últimos $dias días (rellena ceros). */
function emt_stats_serie( $metrica, $dias = 14 ) {
    global $wpdb;
    $t     = emt_stats_table();
    $hoy_t = current_time( 'timestamp' );
    $desde = gmdate( 'Y-m-d', $hoy_t - ( $dias - 1 ) * DAY_IN_SECONDS );
    $rows  = $wpdb->get_results( $wpdb->prepare(
        "SELECT fecha, total FROM $t WHERE metrica = %s AND fecha >= %s ORDER BY fecha ASC", $metrica, $desde
    ), OBJECT_K );
    $serie = array();
    for ( $i = $dias - 1; $i >= 0; $i-- ) {
        $f = gmdate( 'Y-m-d', $hoy_t - $i * DAY_IN_SECONDS );
        $serie[ $f ] = isset( $rows[ $f ] ) ? (int) $rows[ $f ]->total : 0;
    }
    return $serie;
}

/** Conteo de visitas en el front (bots, equipo y panel excluidos). */
add_action( 'template_redirect', function () {
    if ( is_admin() || wp_doing_ajax() || is_404() ) { return; }
    if ( is_user_logged_in() && current_user_can( 'edit_tours' ) ) { return; } // no contar al equipo
    $req = $_SERVER['REQUEST_URI'] ?? '';
    if ( strpos( $req, '/panel' ) !== false ) { return; }
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ( $ua === '' || preg_match( '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|curl|wget|python|lighthouse|headless|uptime|monitor/i', $ua ) ) { return; }

    emt_stats_bump( 'visitas' );
    if ( is_singular( 'tour' ) ) {
        emt_stats_bump( 'vistas_tour' );
        $id = get_queried_object_id();
        update_post_meta( $id, '_emt_views', (int) get_post_meta( $id, '_emt_views', true ) + 1 );
    }
} , 99 );

/** Beacon de clicks (JS emt-stats.js): WhatsApp, cotizador y llamadas. */
function emt_stats_click() {
    $m  = sanitize_key( $_POST['m'] ?? '' );
    $ok = array(
        'whatsapp' => 'clic_whatsapp',
        'disp'     => 'solicitud_disp',
        'info'     => 'solicitud_info',
        'llamada'  => 'clic_llamada',
    );
    if ( isset( $ok[ $m ] ) ) { emt_stats_bump( $ok[ $m ] ); }
    wp_send_json_success();
}
add_action( 'wp_ajax_emt_stats_click', 'emt_stats_click' );
add_action( 'wp_ajax_nopriv_emt_stats_click', 'emt_stats_click' );

/** Formularios: se cuelga ANTES del handler real (prioridad 1) en las mismas acciones AJAX. */
add_action( 'wp_ajax_nopriv_emt_cotizacion', function () { emt_stats_bump( 'form_cotizacion' ); }, 1 );
add_action( 'wp_ajax_emt_cotizacion',        function () { emt_stats_bump( 'form_cotizacion' ); }, 1 );
add_action( 'wp_ajax_nopriv_emt_contacto',   function () { emt_stats_bump( 'form_contacto' ); }, 1 );
add_action( 'wp_ajax_emt_contacto',          function () { emt_stats_bump( 'form_contacto' ); }, 1 );
