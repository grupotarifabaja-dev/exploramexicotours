<?php
/**
 * Explora México Child Theme — Bootstrap
 *
 * Carga modular: cada responsabilidad vive en su archivo dentro de inc/.
 *   - inc/enqueues.php           Estilos (parent + child)
 *   - inc/under-construction.php  Modo under construction
 *   - inc/lead-capture.php        Endpoint REST de leads + panel admin "Leads EMT"
 *   - inc/cpts.php                Custom Post Types (tour, asesor)
 *   - inc/taxonomies.php          Taxonomías (destino, categoria, experiencia, especialidad, idioma)
 *   - inc/acf-fields.php          Campos ACF (tour, asesor, Options page) por código
 *   - inc/i18n.php                Sistema bilingüe nativo ES/EN (routing /en/, helpers, hreflang)
 *   - inc/security.php            Hardening básico a nivel de theme (§11.1)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_inc = get_stylesheet_directory() . '/inc/';

require_once $emt_inc . 'enqueues.php';
require_once $emt_inc . 'under-construction.php';
require_once $emt_inc . 'lead-capture.php';
require_once $emt_inc . 'cpts.php';
require_once $emt_inc . 'taxonomies.php';
require_once $emt_inc . 'acf-fields.php';
require_once $emt_inc . 'i18n.php';
require_once $emt_inc . 'security.php';
