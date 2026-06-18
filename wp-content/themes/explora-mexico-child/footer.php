<?php
/**
 * Footer raíz del theme (sobrescribe el de Hello Elementor).
 * Cierra <main>, incluye el footer de marca + WhatsApp flotante, wp_footer().
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
</main><!-- /.emt-main -->

<?php
include get_stylesheet_directory() . '/parts/footer.php';
include get_stylesheet_directory() . '/parts/whatsapp-float.php';
wp_footer();
?>
</body>
</html>
