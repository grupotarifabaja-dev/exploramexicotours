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
    if ( ! empty( $precio ) ) {
        $schema['offers'] = array(
            '@type'         => 'Offer',
            'price'         => (string) $precio,
            'priceCurrency' => 'MXN',
            'url'           => get_permalink( $id ),
        );
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
