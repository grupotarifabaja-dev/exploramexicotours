<?php
/**
 * Explora México Child Theme — Bootstrap
 *
 * Carga modular: cada responsabilidad vive en su archivo dentro de inc/.
 *   - inc/enqueues.php           Estilos (parent + child)
 *   - inc/under-construction.php  Modo under construction
 *   - inc/lead-capture.php        Endpoint REST de leads + panel admin "Leads EMT"
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_inc = get_stylesheet_directory() . '/inc/';

require_once $emt_inc . 'enqueues.php';
require_once $emt_inc . 'under-construction.php';
require_once $emt_inc . 'lead-capture.php';
