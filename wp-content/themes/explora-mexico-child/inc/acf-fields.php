<?php
/**
 * Registro de campos ACF por CÓDIGO (local field groups).
 * Schema según doc maestro §6.1 (tour), §6.2 (asesor) y §6.4 (Options page).
 * Convención de keys §3.3: field_emt_{contexto}_{nombre}; meta_key = nombre.
 *
 * Todo se registra con acf_add_local_field_group(), por lo que NO es editable
 * desde ACF > Field Groups (son grupos locales).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Options page "Configuración EMT" (§6.4).
 */
add_action( 'acf/init', 'emt_acf_register_options_page' );
function emt_acf_register_options_page() {
    if ( ! function_exists( 'acf_add_options_page' ) ) return;

    acf_add_options_page( array(
        'page_title' => 'Configuración EMT',
        'menu_title' => 'Configuración EMT',
        'menu_slug'  => 'emt-config',
        'capability' => 'manage_options',
        'icon_url'   => 'dashicons-admin-settings',
        'position'   => 59,
        'redirect'   => false,
    ) );
}

/**
 * Field groups (tour, asesor, configuración).
 */
add_action( 'acf/init', 'emt_acf_register_field_groups' );
function emt_acf_register_field_groups() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    /* =========================================================
       GRUPO 1 — TOUR · DATOS  (§6.1 + precios por ocupación, v1.3)
       40 campos no-tab (30 base + 10 de precios por ocupación).
       ========================================================= */
    acf_add_local_field_group( array(
        'key'    => 'group_emt_tour',
        'title'  => 'Tour - Datos',
        'fields' => array(

            // --- Tab: Precio y duración ---
            array( 'key' => 'field_emt_tour_tab_precio', 'label' => 'Precio y duración', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_precio_desde', 'label' => 'Precio desde (MXN)', 'name' => 'precio_desde', 'type' => 'number', 'required' => 0, 'instructions' => 'Se autocalcula como el menor de los 4 precios por ocupación si lo dejas vacío. Captura un valor solo para forzar un override manual.' ),
            array( 'key' => 'field_emt_tour_precio_desde_usd', 'label' => 'Precio desde (USD)', 'name' => 'precio_desde_usd', 'type' => 'number', 'required' => 0, 'instructions' => 'Para extranjeros' ),

            // Precios por ocupación — campos FIJOS (no repeater) + disponibilidad (asientos).
            array( 'key' => 'field_emt_tour_precio_dbl', 'label' => 'Precio Doble (DBL)', 'name' => 'precio_dbl', 'type' => 'number', 'required' => 0, 'wrapper' => array( 'width' => '60' ) ),
            array( 'key' => 'field_emt_tour_disp_dbl', 'label' => 'Disponibilidad DBL', 'name' => 'disp_dbl', 'type' => 'number', 'required' => 0, 'instructions' => 'Asientos', 'wrapper' => array( 'width' => '40' ) ),
            array( 'key' => 'field_emt_tour_precio_tpl', 'label' => 'Precio Triple (TPL)', 'name' => 'precio_tpl', 'type' => 'number', 'required' => 0, 'wrapper' => array( 'width' => '60' ) ),
            array( 'key' => 'field_emt_tour_disp_tpl', 'label' => 'Disponibilidad TPL', 'name' => 'disp_tpl', 'type' => 'number', 'required' => 0, 'instructions' => 'Asientos', 'wrapper' => array( 'width' => '40' ) ),
            array( 'key' => 'field_emt_tour_precio_cuadpl', 'label' => 'Precio Cuádruple (CuADPL)', 'name' => 'precio_cuadpl', 'type' => 'number', 'required' => 0, 'wrapper' => array( 'width' => '60' ) ),
            array( 'key' => 'field_emt_tour_disp_cuadpl', 'label' => 'Disponibilidad CuADPL', 'name' => 'disp_cuadpl', 'type' => 'number', 'required' => 0, 'instructions' => 'Asientos', 'wrapper' => array( 'width' => '40' ) ),
            array( 'key' => 'field_emt_tour_precio_menor', 'label' => 'Precio Menor 6-12', 'name' => 'precio_menor', 'type' => 'number', 'required' => 0, 'wrapper' => array( 'width' => '60' ) ),
            array( 'key' => 'field_emt_tour_disp_menor', 'label' => 'Disponibilidad Menor', 'name' => 'disp_menor', 'type' => 'number', 'required' => 0, 'instructions' => 'Asientos', 'wrapper' => array( 'width' => '40' ) ),
            array( 'key' => 'field_emt_tour_precio_nota', 'label' => 'Nota de precios', 'name' => 'precio_nota', 'type' => 'textarea', 'rows' => 2, 'required' => 0, 'instructions' => 'Observaciones (p. ej. "máximo 4 por habitación incluyendo menores").' ),

            // Modelo alternativo: precios por capacidad de grupo/vehículo (p. ej. tours de Tequila).
            // Coexiste con el de ocupación: un tour usa uno u otro (o ninguno -> "Consultar precio").
            array( 'key' => 'field_emt_tour_precios_vehiculo', 'label' => 'Precios por vehículo', 'name' => 'precios_vehiculo', 'type' => 'repeater', 'required' => 0, 'layout' => 'table', 'button_label' => 'Agregar tramo', 'instructions' => 'Precio POR PERSONA según capacidad del grupo y vehículo. Deja el precio vacío para mostrar "Consultar".', 'sub_fields' => array(
                array( 'key' => 'field_emt_tour_pv_capacidad', 'label' => 'Capacidad', 'name' => 'capacidad', 'type' => 'text', 'instructions' => '"2 pax", "11-15 pax"' ),
                array( 'key' => 'field_emt_tour_pv_vehiculo', 'label' => 'Vehículo', 'name' => 'vehiculo', 'type' => 'text', 'instructions' => '"Sedán", "Sprinter Lux"' ),
                array( 'key' => 'field_emt_tour_pv_precio', 'label' => 'Precio p/p (MXN)', 'name' => 'precio', 'type' => 'number', 'required' => 0 ),
            ) ),
            array( 'key' => 'field_emt_tour_fecha_viaje', 'label' => 'Fecha del viaje', 'name' => 'fecha_viaje', 'type' => 'text', 'required' => 0, 'instructions' => 'P. ej. "30 octubre – 1 noviembre 2026".' ),
            array( 'key' => 'field_emt_tour_duracion_texto', 'label' => 'Duración (texto)', 'name' => 'duracion_texto', 'type' => 'text', 'required' => 1, 'instructions' => '"1 día", "3 días/2 noches"' ),
            array( 'key' => 'field_emt_tour_duracion_horas', 'label' => 'Duración (horas)', 'name' => 'duracion_horas', 'type' => 'number', 'required' => 0, 'instructions' => 'Para filtros' ),
            array( 'key' => 'field_emt_tour_dificultad', 'label' => 'Dificultad', 'name' => 'dificultad', 'type' => 'select', 'required' => 1, 'choices' => array( 'facil' => 'Fácil', 'moderada' => 'Moderada', 'alta' => 'Alta' ), 'default_value' => 'facil', 'return_format' => 'value' ),

            // --- Tab: Logística y salida ---
            array( 'key' => 'field_emt_tour_tab_logistica', 'label' => 'Logística', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_idiomas', 'label' => 'Idiomas', 'name' => 'idiomas', 'type' => 'checkbox', 'required' => 1, 'choices' => array( 'es' => 'Español', 'en' => 'Inglés', 'fr' => 'Francés', 'otros' => 'Otros' ), 'default_value' => array( 'es' ) ),
            array( 'key' => 'field_emt_tour_punto_salida', 'label' => 'Punto de salida', 'name' => 'punto_salida', 'type' => 'text', 'required' => 1, 'instructions' => '"Guadalajara, terminal GDL"' ),
            array( 'key' => 'field_emt_tour_punto_salida_lat', 'label' => 'Latitud punto de salida', 'name' => 'punto_salida_lat', 'type' => 'number', 'required' => 0, 'instructions' => 'Para mapa' ),
            array( 'key' => 'field_emt_tour_punto_salida_lng', 'label' => 'Longitud punto de salida', 'name' => 'punto_salida_lng', 'type' => 'number', 'required' => 0, 'instructions' => 'Para mapa' ),

            // --- Tab: Reserva ---
            array( 'key' => 'field_emt_tour_tab_reserva', 'label' => 'Reserva', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_peek_url', 'label' => 'URL de reserva (Peek)', 'name' => 'peek_url', 'type' => 'url', 'required' => 1, 'instructions' => 'URL de reserva en Peek' ),

            // --- Tab: Incluye / No incluye ---
            array( 'key' => 'field_emt_tour_tab_incluye', 'label' => 'Incluye / No incluye', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_incluye', 'label' => 'Incluye', 'name' => 'incluye', 'type' => 'repeater', 'required' => 1, 'layout' => 'table', 'button_label' => 'Agregar ítem', 'sub_fields' => array(
                array( 'key' => 'field_emt_tour_incluye_icono', 'label' => 'Icono', 'name' => 'icono', 'type' => 'select', 'choices' => array( 'bus' => 'Bus', 'comida' => 'Comida', 'guia' => 'Guía', 'entrada' => 'Entrada', 'equipo' => 'Equipo', 'hospedaje' => 'Hospedaje', 'foto' => 'Foto', 'otro' => 'Otro' ) ),
                array( 'key' => 'field_emt_tour_incluye_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text' ),
            ) ),
            array( 'key' => 'field_emt_tour_no_incluye', 'label' => 'No incluye', 'name' => 'no_incluye', 'type' => 'repeater', 'required' => 0, 'layout' => 'table', 'button_label' => 'Agregar ítem', 'sub_fields' => array(
                array( 'key' => 'field_emt_tour_no_incluye_icono', 'label' => 'Icono', 'name' => 'icono', 'type' => 'select', 'choices' => array( 'bus' => 'Bus', 'comida' => 'Comida', 'guia' => 'Guía', 'entrada' => 'Entrada', 'equipo' => 'Equipo', 'hospedaje' => 'Hospedaje', 'foto' => 'Foto', 'otro' => 'Otro' ) ),
                array( 'key' => 'field_emt_tour_no_incluye_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text' ),
            ) ),

            // --- Tab: Itinerario ---
            array( 'key' => 'field_emt_tour_tab_itinerario', 'label' => 'Itinerario', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_itinerario', 'label' => 'Itinerario', 'name' => 'itinerario', 'type' => 'repeater', 'required' => 1, 'layout' => 'block', 'button_label' => 'Agregar día/parada', 'sub_fields' => array(
                array( 'key' => 'field_emt_tour_itinerario_dia', 'label' => 'Día', 'name' => 'dia', 'type' => 'number' ),
                array( 'key' => 'field_emt_tour_itinerario_hora', 'label' => 'Hora', 'name' => 'hora', 'type' => 'text', 'instructions' => '"08:00", "13:00"' ),
                array( 'key' => 'field_emt_tour_itinerario_titulo', 'label' => 'Título', 'name' => 'titulo', 'type' => 'text', 'instructions' => '"Salida desde GDL"' ),
                array( 'key' => 'field_emt_tour_itinerario_descripcion', 'label' => 'Descripción', 'name' => 'descripcion', 'type' => 'textarea' ),
                array( 'key' => 'field_emt_tour_itinerario_icono', 'label' => 'Icono', 'name' => 'icono', 'type' => 'select', 'choices' => array( 'salida' => 'Salida', 'parada' => 'Parada', 'comida' => 'Comida', 'actividad' => 'Actividad', 'hospedaje' => 'Hospedaje', 'regreso' => 'Regreso' ) ),
            ) ),

            // --- Tab: Políticas ---
            array( 'key' => 'field_emt_tour_tab_politicas', 'label' => 'Políticas', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_politica_cancelacion', 'label' => 'Política de cancelación', 'name' => 'politica_cancelacion', 'type' => 'wysiwyg', 'required' => 1 ),

            // --- Tab: Galería y mapa ---
            array( 'key' => 'field_emt_tour_tab_galeria', 'label' => 'Galería y mapa', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_galeria', 'label' => 'Galería', 'name' => 'galeria', 'type' => 'gallery', 'required' => 1, 'min' => 4, 'insert' => 'append', 'library' => 'all', 'instructions' => 'Mínimo 4 imágenes' ),
            array( 'key' => 'field_emt_tour_mapa_embed', 'label' => 'Mapa (embed URL)', 'name' => 'mapa_embed', 'type' => 'url', 'required' => 0, 'instructions' => 'URL Google Maps embed' ),

            // --- Tab: Indicadores ---
            array( 'key' => 'field_emt_tour_tab_indicadores', 'label' => 'Indicadores', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_pickup_hotel', 'label' => 'Pickup en hotel', 'name' => 'pickup_hotel', 'type' => 'true_false', 'default_value' => 0, 'ui' => 1, 'instructions' => 'Indicador destacado' ),
            array( 'key' => 'field_emt_tour_salida_garantizada', 'label' => 'Salida garantizada', 'name' => 'salida_garantizada', 'type' => 'true_false', 'default_value' => 0, 'ui' => 1, 'instructions' => 'Indicador destacado' ),
            array( 'key' => 'field_emt_tour_destacado', 'label' => 'Destacado', 'name' => 'destacado', 'type' => 'true_false', 'default_value' => 0, 'ui' => 1, 'instructions' => 'Para home' ),
            array( 'key' => 'field_emt_tour_orden_destacado', 'label' => 'Orden destacado', 'name' => 'orden_destacado', 'type' => 'number', 'required' => 0, 'default_value' => 99, 'instructions' => 'Menor = primero' ),

            // --- Tab: Relacionados ---
            array( 'key' => 'field_emt_tour_tab_relacionados', 'label' => 'Relacionados', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_relacionados', 'label' => 'Tours relacionados', 'name' => 'tour_relacionados', 'type' => 'relationship', 'required' => 0, 'post_type' => array( 'tour' ), 'max' => 4, 'return_format' => 'id', 'instructions' => 'Hasta 4 tours' ),

            // --- Tab: SEO ---
            array( 'key' => 'field_emt_tour_tab_seo', 'label' => 'SEO', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_seo_title_override', 'label' => 'SEO title (override)', 'name' => 'seo_title_override', 'type' => 'text', 'required' => 0 ),
            array( 'key' => 'field_emt_tour_seo_desc_override', 'label' => 'SEO description (override)', 'name' => 'seo_desc_override', 'type' => 'textarea', 'required' => 0 ),

            // --- Tab: Inglés (EN) — campos gemelos bilingües (§6.1) ---
            array( 'key' => 'field_emt_tour_tab_en', 'label' => 'Inglés (EN)', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_tour_titulo_en', 'label' => 'Título (EN)', 'name' => 'titulo_en', 'type' => 'text', 'required' => 0 ),
            array( 'key' => 'field_emt_tour_excerpt_en', 'label' => 'Excerpt (EN)', 'name' => 'excerpt_en', 'type' => 'textarea', 'required' => 0 ),
            array( 'key' => 'field_emt_tour_descripcion_en', 'label' => 'Descripción (EN)', 'name' => 'descripcion_en', 'type' => 'wysiwyg', 'required' => 0 ),
            array( 'key' => 'field_emt_tour_politica_cancelacion_en', 'label' => 'Política de cancelación (EN)', 'name' => 'politica_cancelacion_en', 'type' => 'wysiwyg', 'required' => 0 ),
            array( 'key' => 'field_emt_tour_incluye_en', 'label' => 'Incluye (EN)', 'name' => 'incluye_en', 'type' => 'repeater', 'required' => 0, 'layout' => 'table', 'button_label' => 'Agregar ítem', 'sub_fields' => array(
                array( 'key' => 'field_emt_tour_incluye_en_icono', 'label' => 'Icono', 'name' => 'icono', 'type' => 'select', 'choices' => array( 'bus' => 'Bus', 'comida' => 'Comida', 'guia' => 'Guía', 'entrada' => 'Entrada', 'equipo' => 'Equipo', 'hospedaje' => 'Hospedaje', 'foto' => 'Foto', 'otro' => 'Otro' ) ),
                array( 'key' => 'field_emt_tour_incluye_en_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text' ),
            ) ),
            array( 'key' => 'field_emt_tour_no_incluye_en', 'label' => 'No incluye (EN)', 'name' => 'no_incluye_en', 'type' => 'repeater', 'required' => 0, 'layout' => 'table', 'button_label' => 'Agregar ítem', 'sub_fields' => array(
                array( 'key' => 'field_emt_tour_no_incluye_en_icono', 'label' => 'Icono', 'name' => 'icono', 'type' => 'select', 'choices' => array( 'bus' => 'Bus', 'comida' => 'Comida', 'guia' => 'Guía', 'entrada' => 'Entrada', 'equipo' => 'Equipo', 'hospedaje' => 'Hospedaje', 'foto' => 'Foto', 'otro' => 'Otro' ) ),
                array( 'key' => 'field_emt_tour_no_incluye_en_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'text' ),
            ) ),
            array( 'key' => 'field_emt_tour_itinerario_en', 'label' => 'Itinerario (EN)', 'name' => 'itinerario_en', 'type' => 'repeater', 'required' => 0, 'layout' => 'block', 'button_label' => 'Agregar día/parada', 'sub_fields' => array(
                array( 'key' => 'field_emt_tour_itinerario_en_dia', 'label' => 'Día', 'name' => 'dia', 'type' => 'number' ),
                array( 'key' => 'field_emt_tour_itinerario_en_hora', 'label' => 'Hora', 'name' => 'hora', 'type' => 'text' ),
                array( 'key' => 'field_emt_tour_itinerario_en_titulo_en', 'label' => 'Título (EN)', 'name' => 'titulo_en', 'type' => 'text' ),
                array( 'key' => 'field_emt_tour_itinerario_en_descripcion_en', 'label' => 'Descripción (EN)', 'name' => 'descripcion_en', 'type' => 'textarea' ),
                array( 'key' => 'field_emt_tour_itinerario_en_icono', 'label' => 'Icono', 'name' => 'icono', 'type' => 'select', 'choices' => array( 'salida' => 'Salida', 'parada' => 'Parada', 'comida' => 'Comida', 'actividad' => 'Actividad', 'hospedaje' => 'Hospedaje', 'regreso' => 'Regreso' ) ),
            ) ),
        ),
        'location' => array(
            array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'tour' ) ),
        ),
        'menu_order'      => 0,
        'position'        => 'normal',
        'style'           => 'default',
        'active'          => true,
        'description'     => 'Campos del CPT tour (doc maestro §6.1). Registrado por código.',
    ) );

    /* =========================================================
       GRUPO 2 — ASESOR · DATOS  (§6.2)
       ========================================================= */
    acf_add_local_field_group( array(
        'key'    => 'group_emt_asesor',
        'title'  => 'Asesor - Datos',
        'fields' => array(
            array( 'key' => 'field_emt_asesor_puesto', 'label' => 'Puesto', 'name' => 'puesto', 'type' => 'text', 'required' => 1, 'instructions' => '"Ventas Corporativas"' ),
            array( 'key' => 'field_emt_asesor_puesto_en', 'label' => 'Puesto (EN)', 'name' => 'puesto_en', 'type' => 'text', 'required' => 0 ),
            array( 'key' => 'field_emt_asesor_bio_corta', 'label' => 'Bio corta', 'name' => 'bio_corta', 'type' => 'textarea', 'required' => 1, 'instructions' => '3-4 líneas' ),
            array( 'key' => 'field_emt_asesor_bio_corta_en', 'label' => 'Bio corta (EN)', 'name' => 'bio_corta_en', 'type' => 'textarea', 'required' => 0 ),
            array( 'key' => 'field_emt_asesor_telefono', 'label' => 'Teléfono', 'name' => 'telefono', 'type' => 'text', 'required' => 1, 'instructions' => '"+52 33 1048 0670"' ),
            array( 'key' => 'field_emt_asesor_whatsapp', 'label' => 'WhatsApp', 'name' => 'whatsapp', 'type' => 'text', 'required' => 1, 'instructions' => 'Solo dígitos: "523310480670"' ),
            array( 'key' => 'field_emt_asesor_email', 'label' => 'Email', 'name' => 'email', 'type' => 'email', 'required' => 1 ),
            array( 'key' => 'field_emt_asesor_linkedin', 'label' => 'LinkedIn', 'name' => 'linkedin', 'type' => 'url', 'required' => 0 ),
            array( 'key' => 'field_emt_asesor_instagram', 'label' => 'Instagram', 'name' => 'instagram', 'type' => 'url', 'required' => 0 ),
            array( 'key' => 'field_emt_asesor_activo', 'label' => 'Activo', 'name' => 'activo', 'type' => 'true_false', 'default_value' => 1, 'ui' => 1, 'instructions' => 'Para ocultar sin borrar' ),
            array( 'key' => 'field_emt_asesor_orden', 'label' => 'Orden', 'name' => 'orden', 'type' => 'number', 'required' => 0, 'instructions' => 'Para ordenar directorio' ),
        ),
        'location' => array(
            array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'asesor' ) ),
        ),
        'active'      => true,
        'description' => 'Campos del CPT asesor (doc maestro §6.2). Registrado por código.',
    ) );

    /* =========================================================
       GRUPO 3 — CONFIGURACIÓN EMT (Options page · §6.4)
       ========================================================= */
    acf_add_local_field_group( array(
        'key'    => 'group_emt_config',
        'title'  => 'Configuración EMT',
        'fields' => array(

            array( 'key' => 'field_emt_config_tab_contacto', 'label' => 'Contacto', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_config_wa_number', 'label' => 'WhatsApp (número)', 'name' => 'wa_number', 'type' => 'text', 'default_value' => '523310480670' ),
            array( 'key' => 'field_emt_config_email_reservas', 'label' => 'Email reservas', 'name' => 'email_reservas', 'type' => 'email', 'default_value' => 'reserva@exploramexicotours.com' ),
            array( 'key' => 'field_emt_config_email_contacto', 'label' => 'Email contacto', 'name' => 'email_contacto', 'type' => 'email' ),
            array( 'key' => 'field_emt_config_telefono_oficina', 'label' => 'Teléfono oficina', 'name' => 'telefono_oficina', 'type' => 'text' ),
            array( 'key' => 'field_emt_config_direccion_fiscal', 'label' => 'Dirección fiscal', 'name' => 'direccion_fiscal', 'type' => 'textarea' ),

            array( 'key' => 'field_emt_config_tab_redes', 'label' => 'Redes', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_config_redes_facebook', 'label' => 'Facebook', 'name' => 'redes_facebook', 'type' => 'url' ),
            array( 'key' => 'field_emt_config_redes_instagram', 'label' => 'Instagram', 'name' => 'redes_instagram', 'type' => 'url' ),
            array( 'key' => 'field_emt_config_redes_tiktok', 'label' => 'TikTok', 'name' => 'redes_tiktok', 'type' => 'url' ),
            array( 'key' => 'field_emt_config_redes_youtube', 'label' => 'YouTube', 'name' => 'redes_youtube', 'type' => 'url' ),

            array( 'key' => 'field_emt_config_tab_integraciones', 'label' => 'Integraciones', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_config_google_place_id', 'label' => 'Google Place ID', 'name' => 'google_place_id', 'type' => 'text', 'instructions' => 'Para Reviews API' ),
            array( 'key' => 'field_emt_config_google_places_api_key', 'label' => 'Google Places API Key', 'name' => 'google_places_api_key', 'type' => 'text', 'instructions' => 'Privado' ),
            array( 'key' => 'field_emt_config_peek_account_id', 'label' => 'Peek Account ID', 'name' => 'peek_account_id', 'type' => 'text', 'instructions' => 'Para tracking' ),
            array( 'key' => 'field_emt_config_under_construction_mode', 'label' => 'Modo Under Construction', 'name' => 'under_construction_mode', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'Activa/desactiva UC' ),

            array( 'key' => 'field_emt_config_tab_hero', 'label' => 'Hero estacional', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_config_hero_seasonal_active', 'label' => 'Hero estacional activo', 'name' => 'hero_seasonal_active', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'Activa banner estacional' ),
            array( 'key' => 'field_emt_config_hero_seasonal_title', 'label' => 'Hero título', 'name' => 'hero_seasonal_title', 'type' => 'text' ),
            array( 'key' => 'field_emt_config_hero_seasonal_subtitle', 'label' => 'Hero subtítulo', 'name' => 'hero_seasonal_subtitle', 'type' => 'textarea' ),
            array( 'key' => 'field_emt_config_hero_seasonal_image', 'label' => 'Hero imagen', 'name' => 'hero_seasonal_image', 'type' => 'image', 'return_format' => 'array' ),
            array( 'key' => 'field_emt_config_hero_seasonal_cta_text', 'label' => 'Hero CTA texto', 'name' => 'hero_seasonal_cta_text', 'type' => 'text' ),
            array( 'key' => 'field_emt_config_hero_seasonal_cta_url', 'label' => 'Hero CTA URL', 'name' => 'hero_seasonal_cta_url', 'type' => 'url' ),
            array( 'key' => 'field_emt_config_hero_seasonal_video', 'label' => 'Hero video (MP4 URL)', 'name' => 'hero_seasonal_video', 'type' => 'text', 'instructions' => 'URL de video MP4 (opcional)' ),

            array( 'key' => 'field_emt_config_tab_hero_bg', 'label' => 'Hero portada (video de fondo)', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_config_hero_bg_video', 'label' => 'Video de fondo del hero', 'name' => 'hero_bg_video', 'type' => 'file', 'return_format' => 'array', 'mime_types' => 'mp4,webm', 'instructions' => 'Video de fondo del hero de la portada (MP4 recomendado, sin audio, ligero). Si está vacío se usa la imagen de respaldo.' ),
            array( 'key' => 'field_emt_config_hero_bg_poster', 'label' => 'Imagen de respaldo del hero', 'name' => 'hero_bg_poster', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Se muestra mientras carga el video, en móvil y si el navegador no reproduce el video. Si no hay video ni imagen, el hero usa el degradado azul.' ),

            array( 'key' => 'field_emt_config_tab_megamenu', 'label' => 'Mega-menú', 'name' => '', 'type' => 'tab', 'placement' => 'top' ),
            array( 'key' => 'field_emt_config_mega_menu_destinos', 'label' => 'Mega-menú destinos', 'name' => 'mega_menu_destinos', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Agregar destino', 'sub_fields' => array(
                array( 'key' => 'field_emt_config_mm_destinos_nombre', 'label' => 'Nombre', 'name' => 'nombre', 'type' => 'text' ),
                array( 'key' => 'field_emt_config_mm_destinos_imagen', 'label' => 'Imagen', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array' ),
                array( 'key' => 'field_emt_config_mm_destinos_url', 'label' => 'URL', 'name' => 'url', 'type' => 'url' ),
                array( 'key' => 'field_emt_config_mm_destinos_orden', 'label' => 'Orden', 'name' => 'orden', 'type' => 'number' ),
            ) ),
            array( 'key' => 'field_emt_config_mega_menu_experiencias', 'label' => 'Mega-menú experiencias', 'name' => 'mega_menu_experiencias', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Agregar experiencia', 'sub_fields' => array(
                array( 'key' => 'field_emt_config_mm_exp_nombre', 'label' => 'Nombre', 'name' => 'nombre', 'type' => 'text' ),
                array( 'key' => 'field_emt_config_mm_exp_imagen', 'label' => 'Imagen', 'name' => 'imagen', 'type' => 'image', 'return_format' => 'array' ),
                array( 'key' => 'field_emt_config_mm_exp_url', 'label' => 'URL', 'name' => 'url', 'type' => 'url' ),
                array( 'key' => 'field_emt_config_mm_exp_orden', 'label' => 'Orden', 'name' => 'orden', 'type' => 'number' ),
            ) ),
        ),
        'location' => array(
            array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'emt-config' ) ),
        ),
        'active'      => true,
        'description' => 'Configuración global EMT (doc maestro §6.4). Registrado por código.',
    ) );

    // Imagen por destino (term meta de tour_destino). Editable en wp-admin
    // (Tours → Destinos → editar término). Usada en las cards de destinos del home.
    acf_add_local_field_group( array(
        'key'    => 'group_emt_destino',
        'title'  => 'Destino',
        'fields' => array(
            array( 'key' => 'field_emt_destino_imagen', 'label' => 'Imagen del destino', 'name' => 'imagen_destino', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Foto representativa del destino para las cards del home. Si se deja vacía, se usa la foto destacada de un tour del destino.' ),
        ),
        'location' => array(
            array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'tour_destino' ) ),
        ),
        'active'      => true,
        'description' => 'Campos del término de destino. Registrado por código.',
    ) );
}
