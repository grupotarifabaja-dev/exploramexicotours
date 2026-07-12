<?php
/**
 * Custom Post Types: `tour` y `asesor`.
 * Schema según doc maestro §6.1 y §6.2 (naming §3.2).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'emt_register_cpts' );
function emt_register_cpts() {

    // CPT: tour (§6.1) — registro exacto del doc maestro.
    register_post_type( 'tour', array(
        'labels' => array(
            'name'          => 'Tours',
            'singular_name' => 'Tour',
            'menu_name'     => 'Tours',
            'add_new_item'  => 'Agregar nuevo tour',
            'edit_item'     => 'Editar tour',
        ),
        'public'        => true,
        'has_archive'   => 'tours',
        'rewrite'       => array( 'slug' => 'tours', 'with_front' => false ),
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
        'menu_icon'     => 'dashicons-palmtree',
        'menu_position' => 5,
        'show_in_rest'  => true,
        'capability_type' => array( 'tour', 'tours' ),
        'map_meta_cap'    => true,
    ) );

    // CPT: asesor (§6.2)
    register_post_type( 'asesor', array(
        'labels' => array(
            'name'          => 'Asesores',
            'singular_name' => 'Asesor',
            'menu_name'     => 'Asesores',
            'add_new_item'  => 'Agregar nuevo asesor',
            'edit_item'     => 'Editar asesor',
        ),
        'public'        => true,
        'has_archive'   => 'asesores',
        'rewrite'       => array( 'slug' => 'asesores', 'with_front' => false ),
        'supports'      => array( 'title', 'thumbnail', 'excerpt', 'revisions' ),
        'menu_icon'     => 'dashicons-businessperson',
        'menu_position' => 6,
        'show_in_rest'  => true,
        'capability_type' => array( 'asesor', 'asesores' ),
        'map_meta_cap'    => true,
    ) );
}

/**
 * Flush de rewrite rules al activar el theme, para que los permalinks
 * de los CPTs y taxonomías funcionen sin re-guardar enlaces manualmente.
 */
add_action( 'after_switch_theme', 'emt_flush_rewrites_on_activation' );
function emt_flush_rewrites_on_activation() {
    emt_register_cpts();
    if ( function_exists( 'emt_register_taxonomies' ) ) {
        emt_register_taxonomies();
    }
    flush_rewrite_rules();
}
