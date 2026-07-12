<?php
/**
 * Header raíz del theme (sobrescribe el de Hello Elementor).
 * Boilerplate <head> + apertura de <body> + inclusión del header de marca (parts/header.php).
 * Cargado por get_header() en las plantillas del sitio real (Fase C).
 *
 * Nota: la página under construction NO usa este archivo (construye su propio
 * <head> y hace exit antes del template normal), así que no la afecta.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$emt_html_lang = ( function_exists( 'emt_current_lang' ) && emt_current_lang() === 'en' ) ? 'en' : 'es-MX';
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( $emt_html_lang ); ?>">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'emt-site' ); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open(); } ?>

<a class="emt-skip-link screen-reader-text" href="#emt-main"><?php echo esc_html( function_exists( 'emt_t' ) ? emt_t( 'saltar_contenido' ) : 'Saltar al contenido' ); ?></a>

<?php include get_stylesheet_directory() . '/parts/header.php'; ?>

<main class="emt-main" id="emt-main">
