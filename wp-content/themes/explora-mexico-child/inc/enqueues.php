<?php
/**
 * Enqueues de estilos del theme.
 *
 * Hereda los estilos del tema padre (Hello Elementor) y carga el child.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'hello-elementor-parent', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style(
        'explora-mexico-child',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'hello-elementor-parent' ),
        wp_get_theme()->get( 'Version' )
    );
});
