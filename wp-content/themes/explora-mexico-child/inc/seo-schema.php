<?php
/**
 * Schema.org JSON-LD (doc maestro §9.5).
 * TouristTrip en la ficha de tour, inyectado en <head>.
 * (BreadcrumbList lo emite emt_breadcrumbs() en inc/template-helpers.php.)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_head', 'emt_tour_schema', 5 );
function emt_tour_schema() {
    if ( ! is_singular( 'tour' ) ) {
        return;
    }
    $id     = get_the_ID();
    $precio = function_exists( 'get_field' ) ? get_field( 'precio_desde', $id ) : '';
    $images = array();
    if ( has_post_thumbnail( $id ) ) {
        $images[] = get_the_post_thumbnail_url( $id, 'full' );
    }
    $gal = function_exists( 'get_field' ) ? get_field( 'galeria', $id ) : array();
    if ( is_array( $gal ) ) {
        foreach ( $gal as $img ) {
            if ( ! empty( $img['url'] ) ) {
                $images[] = $img['url'];
            }
        }
    }

    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'TouristTrip',
        'name'        => get_the_title( $id ),
        'description' => wp_strip_all_tags( get_the_excerpt( $id ) ),
        'provider'    => array(
            '@type'     => 'TravelAgency',
            'name'      => 'Explora México Tours',
            'telephone' => '+523310480670',
        ),
    );
    if ( $images ) {
        $schema['image'] = array_values( array_unique( $images ) );
    }
    $ocup = function_exists( 'emt_tour_precios' ) ? emt_tour_precios( $id ) : array();
    if ( count( $ocup ) > 1 ) {
        $vals = array_map( function ( $r ) { return $r['precio']; }, $ocup );
        $schema['offers'] = array(
            '@type'         => 'AggregateOffer',
            'priceCurrency' => 'MXN',
            'lowPrice'      => (string) (int) min( $vals ),
            'highPrice'     => (string) (int) max( $vals ),
            'offerCount'    => count( $ocup ),
            'url'           => get_permalink( $id ),
        );
    } elseif ( ! empty( $precio ) ) {
        $schema['offers'] = array(
            '@type'         => 'Offer',
            'price'         => (string) $precio,
            'priceCurrency' => 'MXN',
            'url'           => get_permalink( $id ),
        );
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}

add_action( 'wp_head', 'emt_asesor_schema', 5 );
function emt_asesor_schema() {
    if ( ! is_singular( 'asesor' ) ) {
        return;
    }
    $id     = get_the_ID();
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Person',
        'name'     => get_the_title( $id ),
        'worksFor' => array(
            '@type' => 'TravelAgency',
            'name'  => 'Explora México Tours',
        ),
    );
    if ( function_exists( 'get_field' ) ) {
        $puesto = get_field( 'puesto', $id );
        $tel    = get_field( 'telefono', $id );
        $email  = get_field( 'email', $id );
        if ( $puesto ) { $schema['jobTitle'] = $puesto; }
        if ( $tel )    { $schema['telephone'] = $tel; }
        if ( $email )  { $schema['email'] = $email; }
    }
    if ( has_post_thumbnail( $id ) ) {
        $schema['image'] = get_the_post_thumbnail_url( $id, 'full' );
    }
    echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
