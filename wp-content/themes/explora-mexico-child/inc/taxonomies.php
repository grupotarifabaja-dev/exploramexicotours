<?php
/**
 * Taxonomías de `tour` y `asesor`.
 * Schema según doc maestro §6.3 (jerarquías y tipos) y naming §3.2.
 *
 *   tour_destino       jerárquica   -> tour
 *   tour_categoria     jerárquica   -> tour
 *   tour_experiencia   tags         -> tour
 *   asesor_especialidad tags        -> asesor
 *   asesor_idioma      tags         -> asesor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'emt_register_taxonomies' );
function emt_register_taxonomies() {

    // tour_destino — jerárquica (tipo categoría) -> tour
    register_taxonomy( 'tour_destino', array( 'tour' ), array(
        'labels' => array(
            'name'          => 'Destinos',
            'singular_name' => 'Destino',
            'menu_name'     => 'Destinos',
            'all_items'     => 'Todos los destinos',
            'edit_item'     => 'Editar destino',
            'add_new_item'  => 'Agregar nuevo destino',
            'search_items'  => 'Buscar destinos',
        ),
        'hierarchical' => true,
        'public'       => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite'      => array( 'slug' => 'destino', 'with_front' => false ),
    ) );

    // tour_categoria — jerárquica -> tour
    register_taxonomy( 'tour_categoria', array( 'tour' ), array(
        'labels' => array(
            'name'          => 'Categorías',
            'singular_name' => 'Categoría',
            'menu_name'     => 'Categorías',
            'all_items'     => 'Todas las categorías',
            'edit_item'     => 'Editar categoría',
            'add_new_item'  => 'Agregar nueva categoría',
            'search_items'  => 'Buscar categorías',
        ),
        'hierarchical' => true,
        'public'       => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite'      => array( 'slug' => 'categoria-tour', 'with_front' => false ),
    ) );

    // tour_experiencia — no jerárquica (tags) -> tour
    register_taxonomy( 'tour_experiencia', array( 'tour' ), array(
        'labels' => array(
            'name'          => 'Experiencias',
            'singular_name' => 'Experiencia',
            'menu_name'     => 'Experiencias',
            'all_items'     => 'Todas las experiencias',
            'edit_item'     => 'Editar experiencia',
            'add_new_item'  => 'Agregar nueva experiencia',
            'search_items'  => 'Buscar experiencias',
        ),
        'hierarchical' => false,
        'public'       => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite'      => array( 'slug' => 'experiencia', 'with_front' => false ),
    ) );

    // asesor_especialidad — no jerárquica -> asesor
    register_taxonomy( 'asesor_especialidad', array( 'asesor' ), array(
        'labels' => array(
            'name'          => 'Especialidades',
            'singular_name' => 'Especialidad',
            'menu_name'     => 'Especialidades',
            'all_items'     => 'Todas las especialidades',
            'edit_item'     => 'Editar especialidad',
            'add_new_item'  => 'Agregar nueva especialidad',
            'search_items'  => 'Buscar especialidades',
        ),
        'hierarchical' => false,
        'public'       => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite'      => array( 'slug' => 'especialidad', 'with_front' => false ),
    ) );

    // asesor_idioma — no jerárquica -> asesor
    register_taxonomy( 'asesor_idioma', array( 'asesor' ), array(
        'labels' => array(
            'name'          => 'Idiomas',
            'singular_name' => 'Idioma',
            'menu_name'     => 'Idiomas',
            'all_items'     => 'Todos los idiomas',
            'edit_item'     => 'Editar idioma',
            'add_new_item'  => 'Agregar nuevo idioma',
            'search_items'  => 'Buscar idiomas',
        ),
        'hierarchical' => false,
        'public'       => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite'      => array( 'slug' => 'idioma', 'with_front' => false ),
    ) );
}
