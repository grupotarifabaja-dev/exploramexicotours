<?php
/**
 * SEO on-page (doc maestro §9.5):
 *   1. Canonical correcto por idioma (las páginas /en/ se auto-canonicalizan;
 *      antes apuntaban al español y Google no indexaría el inglés).
 *   2. <title> y meta description bilingües, con override editable por tour
 *      (campos seo_title_override / seo_desc_override del panel).
 *   3. Open Graph + Twitter Cards (tarjeta con foto al compartir por
 *      WhatsApp/Facebook/X).
 *   4. Canonical para las rutas propias (blog, contacto, cotización, etc.),
 *      que WordPress no cubre por no ser singulares.
 *
 * No toca wp-admin, el panel (/panel/) ni el under-construction.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** True si estamos sirviendo la versión en inglés. */
function emt_seo_is_en() {
    return function_exists( 'emt_current_lang' ) && emt_current_lang() === 'en';
}

/** URL canónica de la petición actual (path limpio, con /en/ si aplica). */
function emt_seo_current_url() {
    $path = strtok( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), '?' );
    return home_url( $path );
}

/** Título "de contenido" para title/OG: override > EN > nativo. */
function emt_seo_title( $post_id ) {
    $type = get_post_type( $post_id );
    if ( $type === 'tour' && function_exists( 'get_field' ) ) {
        $ov = trim( (string) get_field( 'seo_title_override', $post_id ) );
        if ( $ov !== '' ) { return $ov; }
        if ( emt_seo_is_en() ) {
            $en = trim( (string) get_field( 'titulo_en', $post_id ) );
            if ( $en !== '' ) { return $en; }
        }
    }
    if ( $type === 'post' && emt_seo_is_en() ) {
        $en = trim( (string) get_post_meta( $post_id, 'titulo_en', true ) );
        if ( $en !== '' ) { return $en; }
    }
    return get_the_title( $post_id );
}

/** Descripción para meta/OG: override > EN > extracto > descripción del sitio. */
function emt_seo_description() {
    if ( is_singular() ) {
        $id   = get_queried_object_id();
        $type = get_post_type( $id );
        if ( $type === 'tour' && function_exists( 'get_field' ) ) {
            $ov = trim( (string) get_field( 'seo_desc_override', $id ) );
            if ( $ov !== '' ) { return $ov; }
            if ( emt_seo_is_en() ) {
                $en = trim( (string) get_field( 'excerpt_en', $id ) );
                if ( $en !== '' ) { return wp_strip_all_tags( $en ); }
            }
        }
        if ( $type === 'post' && emt_seo_is_en() ) {
            $en = trim( (string) get_post_meta( $id, 'excerpt_en', true ) );
            if ( $en !== '' ) { return wp_strip_all_tags( $en ); }
        }
        $exc = get_the_excerpt( $id );
        if ( $exc ) { return wp_strip_all_tags( $exc ); }
    }
    if ( is_tax() || is_category() || is_tag() ) {
        $d = term_description();
        if ( $d ) { return wp_strip_all_tags( $d ); }
    }
    return (string) get_bloginfo( 'description' );
}

/** Imagen para OG: header del tour > destacada > poster del hero. */
function emt_seo_image() {
    if ( is_singular() ) {
        $id = get_queried_object_id();
        if ( get_post_type( $id ) === 'tour' && function_exists( 'get_field' ) ) {
            $h   = get_field( 'imagen_header', $id );
            $hid = is_array( $h ) ? (int) ( $h['ID'] ?? $h['id'] ?? 0 ) : (int) $h;
            if ( $hid ) {
                $u = wp_get_attachment_image_url( $hid, 'large' );
                if ( $u ) { return $u; }
            }
        }
        $u = get_the_post_thumbnail_url( $id, 'large' );
        if ( $u ) { return $u; }
    }
    if ( function_exists( 'get_field' ) ) {
        $p = get_field( 'hero_bg_poster', 'option' );
        if ( is_array( $p ) && ! empty( $p['url'] ) ) { return $p['url']; }
    }
    return '';
}

/* ---------- 1. Canonical por idioma (singulares; corrige /en/) ---------- */
add_filter( 'get_canonical_url', function ( $url, $post ) {
    if ( emt_seo_is_en() ) {
        $home = home_url( '/' );
        if ( strpos( $url, $home ) === 0 && strpos( $url, $home . 'en/' ) !== 0 ) {
            $url = $home . 'en/' . ltrim( substr( $url, strlen( $home ) ), '/' );
        }
    }
    return $url;
}, 10, 2 );

/* ---------- 2. <title> bilingüe / override ---------- */

/** Título propio de las rutas virtuales (transporte, evaluación, etc.), o '' si no aplica. */
function emt_titulo_ruta_propia() {
    $en    = function_exists( 'emt_current_lang' ) && emt_current_lang() === 'en';
    $rutas = array(
        'emt_transporte' => $en ? 'Explora Transfer · Tourist & Executive Transportation' : 'Explora Transfer · Transporte turístico y ejecutivo',
        'emt_evaluacion' => $en ? 'How was your experience?' : '¿Cómo fue tu experiencia?',
        'emt_nosotros'   => $en ? 'About us' : 'Nosotros',
        'emt_cotizacion' => $en ? 'Get a group travel quote' : 'Cotiza tu viaje de grupo',
        'emt_contacto'   => $en ? 'Contact' : 'Contacto',
        'emt_legal'      => $en ? 'Legal information' : 'Información legal',
        'emt_blog_list'  => 'Blog',
    );
    foreach ( $rutas as $var => $titulo ) {
        if ( get_query_var( $var ) ) { return $titulo; }
    }
    return '';
}

// Vía de máxima precedencia: si otro plugin (p. ej. Elementor) resuelve el
// título con pre_get_document_title, este filtro a prioridad 99 gana igual.
add_filter( 'pre_get_document_title', function ( $title ) {
    if ( is_admin() ) { return $title; }
    $propio = emt_titulo_ruta_propia();
    return $propio !== '' ? $propio . ' – ' . get_bloginfo( 'name' ) : $title;
}, 99 );

add_filter( 'document_title_parts', function ( $parts ) {
    if ( is_admin() ) { return $parts; }

    // Rutas propias (query vars): sin esto, el título del documento cae en el
    // de la página asignada al blog ("Blog – ...") porque la query principal
    // de estas URLs virtuales no resuelve a un contenido singular.
    $emt_en    = function_exists( 'emt_current_lang' ) && emt_current_lang() === 'en';
    $emt_rutas = array(
        'emt_transporte' => $emt_en ? 'Explora Transfer · Tourist & Executive Transportation' : 'Explora Transfer · Transporte turístico y ejecutivo',
        'emt_evaluacion' => $emt_en ? 'How was your experience?' : '¿Cómo fue tu experiencia?',
        'emt_nosotros'   => $emt_en ? 'About us' : 'Nosotros',
        'emt_cotizacion' => $emt_en ? 'Get a group travel quote' : 'Cotiza tu viaje de grupo',
        'emt_contacto'   => $emt_en ? 'Contact' : 'Contacto',
        'emt_legal'      => $emt_en ? 'Legal information' : 'Información legal',
        'emt_blog_list'  => 'Blog',
    );
    foreach ( $emt_rutas as $emt_var => $emt_titulo ) {
        if ( get_query_var( $emt_var ) ) {
            $parts['title'] = $emt_titulo;
            return $parts;
        }
    }

    if ( is_singular() ) {
        $id = get_queried_object_id();
        if ( in_array( get_post_type( $id ), array( 'tour', 'post' ), true ) ) {
            $parts['title'] = emt_seo_title( $id );
        }
    }
    return $parts;
} );

/* ---------- 2b/3/4. Meta description + OG/Twitter + canonical de rutas propias ---------- */
add_action( 'wp', function () {
    // El tema padre (Hello Elementor) emite su propia meta description (solo
    // singular, siempre en español); la retiramos y emitimos la nuestra.
    remove_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );
} );

add_action( 'wp_head', function () {
    // Fuera del front público (panel, feeds, embeds) no emitimos nada.
    if ( is_admin() || is_feed() || is_embed() || ( function_exists( 'emt_panel_is_request' ) && emt_panel_is_request() ) ) {
        return;
    }
    if ( is_404() ) { return; }

    $desc  = trim( (string) emt_seo_description() );
    $img   = emt_seo_image();
    $url   = is_singular() ? wp_get_canonical_url( get_queried_object_id() ) : emt_seo_current_url();
    $title = is_singular() ? emt_seo_title( get_queried_object_id() ) : wp_get_document_title();
    $site  = get_bloginfo( 'name' );

    if ( $desc !== '' ) {
        printf( '<meta name="description" content="%s" />' . "\n", esc_attr( wp_trim_words( $desc, 32 ) ) );
    }

    // Canonical para vistas no singulares (archivos y rutas propias como
    // /blog/, /contacto/, /cotizacion/; WP solo lo emite en singulares).
    if ( ! is_singular() ) {
        printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $url ) );
    }

    // Open Graph + Twitter Card.
    printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $site ) );
    printf( '<meta property="og:type" content="%s" />' . "\n", is_singular( 'post' ) ? 'article' : 'website' );
    printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
    if ( $desc !== '' ) {
        printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( wp_trim_words( $desc, 32 ) ) );
    }
    printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
    printf( '<meta property="og:locale" content="%s" />' . "\n", emt_seo_is_en() ? 'en_US' : 'es_MX' );
    if ( $img ) {
        printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $img ) );
        printf( '<meta name="twitter:card" content="summary_large_image" />' . "\n" );
        printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $img ) );
    } else {
        printf( '<meta name="twitter:card" content="summary" />' . "\n" );
    }
    printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );
    if ( $desc !== '' ) {
        printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( wp_trim_words( $desc, 32 ) ) );
    }
}, 4 );
