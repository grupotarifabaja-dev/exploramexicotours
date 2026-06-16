<?php
/**
 * Sistema bilingüe nativo ES/EN (sin plugins) — doc maestro §10.
 *
 * Estrategia de routing: STRIP-PREFIX (Opción A, aprobada).
 * El prefijo /en/ se retira en `do_parse_request` y WordPress resuelve la ruta
 * restante con sus reglas normales (páginas, CPTs, taxonomías y archivos por
 * igual), marcando el idioma como 'en'. El modelo de datos es un solo post con
 * campos gemelos _en (creados en A4); el idioma es un flag, no posts separados.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Captura el path de la request ORIGINAL (antes de que el strip-prefix lo
 * modifique). Fuente única de verdad para detectar el idioma.
 */
if ( ! isset( $GLOBALS['emt_request_path'] ) ) {
    $GLOBALS['emt_request_path'] = isset( $_SERVER['REQUEST_URI'] )
        ? ( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) ?: '/' )
        : '/';
}

/**
 * Idioma actual: 'en' si la URL empieza con /en/ (o es /en), 'es' en otro caso.
 * (§10.1) Lee el path original capturado para ser robusto ante el strip-prefix.
 */
function emt_current_lang() {
    static $lang = null;
    if ( $lang === null ) {
        $path = isset( $GLOBALS['emt_request_path'] ) ? $GLOBALS['emt_request_path'] : '/';
        $lang = preg_match( '#^/en(/|$)#', $path ) ? 'en' : 'es';
    }
    return $lang;
}

/**
 * Routing /en/ (Opción A): registrar query var + regla del home EN + strip-prefix.
 */
add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'lang';
    return $vars;
} );

// Regla de reescritura registrada para el prefijo /en/ (§10.1, criterio #4).
// El strip-prefix la antecede en la resolución; queda registrada y se incluye
// en el flush de activación (inc/cpts.php :: after_switch_theme).
add_action( 'init', function() {
    add_rewrite_rule( '^en/?$', 'index.php?lang=en', 'top' );
} );

// Strip-prefix: retira /en al inicio del REQUEST_URI antes de que WP empareje
// reglas, de modo que la ruta restante resuelve con las reglas normales.
add_filter( 'do_parse_request', function( $continue ) {
    if ( emt_current_lang() !== 'en' ) {
        return $continue;
    }
    $uri   = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';
    $qpos  = strpos( $uri, '?' );
    $path  = ( $qpos === false ) ? $uri : substr( $uri, 0, $qpos );
    $query = ( $qpos === false ) ? '' : substr( $uri, $qpos );

    $new = preg_replace( '#^/en(?=/|$)#', '', $path ); // /en/tours/ -> /tours/ ; /en/ -> /
    if ( $new === '' || $new === null ) {
        $new = '/';
    }
    $_SERVER['REQUEST_URI'] = $new . $query;
    return $continue;
} );

// Asegura la query var lang=en también para las rutas que pasaron por strip-prefix.
add_filter( 'request', function( $vars ) {
    if ( emt_current_lang() === 'en' ) {
        $vars['lang'] = 'en';
    }
    return $vars;
} );

/**
 * Lee un campo ACF respetando el idioma: en EN usa el gemelo {campo}_en y, si
 * está vacío, cae al valor ES. (§10.1)
 */
function emt_get_field( $field_name, $post_id = null ) {
    if ( ! function_exists( 'get_field' ) ) {
        return null;
    }
    $lang  = emt_current_lang();
    $field = ( $lang === 'en' ) ? $field_name . '_en' : $field_name;
    $value = get_field( $field, $post_id );

    if ( empty( $value ) && $lang === 'en' ) {
        $value = get_field( $field_name, $post_id ); // fallback a ES
    }
    return $value;
}

/**
 * Devuelve un string de UI del diccionario del idioma actual.
 * Si la key no existe, devuelve la propia key (no rompe). (§10.1)
 */
function emt_t( $key ) {
    static $translations = null;
    if ( $translations === null ) {
        $lang = emt_current_lang();
        $file = get_stylesheet_directory() . "/assets/i18n/{$lang}.php";
        $translations = file_exists( $file ) ? (array) require $file : array();
    }
    return isset( $translations[ $key ] ) ? $translations[ $key ] : $key;
}

/**
 * URL alterna preservando la ruta actual (§10.3).
 * Sin argumento devuelve la del idioma opuesto; con 'es'/'en' la de ese idioma.
 */
function emt_lang_switch_url( $lang = null ) {
    if ( $lang === null ) {
        $lang = ( emt_current_lang() === 'es' ) ? 'en' : 'es';
    }
    $path    = isset( $GLOBALS['emt_request_path'] ) ? $GLOBALS['emt_request_path'] : '/';
    $es_path = preg_replace( '#^/en(?=/|$)#', '', $path );
    if ( $es_path === '' || $es_path === null ) {
        $es_path = '/';
    }
    if ( $lang === 'en' ) {
        $new = ( $es_path === '/' ) ? '/en/' : '/en' . $es_path;
    } else {
        $new = $es_path;
    }
    return home_url( $new );
}

/**
 * Hreflang en el <head>: es-MX, en, x-default (§10.2).
 * No se emite en la página under construction (no tiene versión EN), para no
 * alterar su salida.
 */
add_action( 'wp_head', 'emt_hreflang_tags', 1 );
function emt_hreflang_tags() {
    if ( defined( 'EMT_UNDER_CONSTRUCTION' ) && EMT_UNDER_CONSTRUCTION
        && ! is_admin() && ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $path    = isset( $GLOBALS['emt_request_path'] ) ? $GLOBALS['emt_request_path'] : '/';
    $es_path = preg_replace( '#^/en(?=/|$)#', '', $path );
    if ( $es_path === '' || $es_path === null ) {
        $es_path = '/';
    }
    $en_path = ( $es_path === '/' ) ? '/en/' : '/en' . $es_path;

    $es_url = esc_url( home_url( $es_path ) );
    $en_url = esc_url( home_url( $en_path ) );

    printf( '<link rel="alternate" hreflang="es-MX" href="%s" />' . "\n", $es_url );
    printf( '<link rel="alternate" hreflang="en" href="%s" />' . "\n", $en_url );
    printf( '<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", $es_url );
}
