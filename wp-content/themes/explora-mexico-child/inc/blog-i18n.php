<?php
/**
 * Blog bilingüe: en /en/ usa las traducciones (post meta titulo_en / excerpt_en /
 * contenido_en) capturadas desde el panel, con respaldo automático al español.
 * Solo afecta al post type 'post' en el front.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** True si debemos servir contenido en inglés. */
function emt_blog_is_en() {
    return ! is_admin() && function_exists( 'emt_current_lang' ) && emt_current_lang() === 'en';
}

// Título (the_title recibe $title, $post_id).
add_filter( 'the_title', function ( $title, $post_id = null ) {
    if ( ! emt_blog_is_en() || get_post_type( $post_id ) !== 'post' ) { return $title; }
    $t = (string) get_post_meta( $post_id, 'titulo_en', true );
    return ( trim( $t ) !== '' ) ? $t : $title;
}, 10, 2 );

// Contenido del artículo.
add_filter( 'the_content', function ( $content ) {
    if ( ! emt_blog_is_en() || get_post_type() !== 'post' ) { return $content; }
    $c = (string) get_post_meta( get_the_ID(), 'contenido_en', true );
    return ( trim( wp_strip_all_tags( $c ) ) !== '' ) ? $c : $content;
}, 9 );

// Extracto (listado y compartir).
add_filter( 'get_the_excerpt', function ( $excerpt, $post = null ) {
    $pid = $post ? ( is_object( $post ) ? $post->ID : (int) $post ) : get_the_ID();
    if ( ! emt_blog_is_en() || get_post_type( $pid ) !== 'post' ) { return $excerpt; }
    $e = (string) get_post_meta( $pid, 'excerpt_en', true );
    if ( trim( $e ) !== '' ) { return $e; }
    // Si no hay extracto EN pero sí contenido EN, deriva un resumen de éste.
    $c = (string) get_post_meta( $pid, 'contenido_en', true );
    if ( trim( wp_strip_all_tags( $c ) ) !== '' ) {
        return wp_trim_words( wp_strip_all_tags( $c ), 30 );
    }
    return $excerpt;
}, 10, 2 );
