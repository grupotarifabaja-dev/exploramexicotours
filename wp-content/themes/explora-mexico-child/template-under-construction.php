<?php
/**
 * Plantilla: Under Construction
 *
 * Se sirve automáticamente cuando EMT_UNDER_CONSTRUCTION = true
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$wa_number     = get_option( 'emt_whatsapp_number', '523310480670' );
$wa_url        = 'https://wa.me/' . $wa_number;
$lead_endpoint = esc_url_raw( rest_url( 'emt/v1/lead' ) );
$nonce         = wp_create_nonce( 'wp_rest' );

// Logo: URL absoluta del logo. Si quieres cambiarlo, edita esta línea o súbelo a la biblioteca de medios y reemplaza la URL.
$logo_url      = 'https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/explora-logo.png';
$bg_image_url  = 'https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/ChatGPT-Image-19-may-2026-18_39_51.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Explora México Tours · Próximamente un nuevo viaje</title>
<meta name="description" content="Estamos rediseñando la forma en que descubres México. Tours, experiencias y rutas únicas desde Guadalajara. Reserva por WhatsApp mientras lanzamos el nuevo sitio.">
<meta property="og:title" content="Explora México Tours · Próximamente">
<meta property="og:description" content="Una nueva forma de descubrir México. Próximamente.">
<meta property="og:type" content="website">
<meta name="theme-color" content="#003366">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/under-construction.css">
<style>
  .emt-bg-image {
    background-image: url('<?php echo esc_url( $bg_image_url ); ?>');
  }
</style>
<?php wp_head(); ?>
</head>
<body class="emt emt-under-construction">

<div class="emt-bg-image"></div>
<div class="bg-deco"><span></span></div>

<div class="container">

  <header class="site-header">
    <nav class="nav">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo">
        <img src="<?php echo esc_url( $logo_url ); ?>" alt="Explora México Tours" />
      </a>
      <div class="nav-status">
        <span class="pulse"></span>
        <span class="nav-status-text">Nueva web en camino</span>
      </div>
    </nav>
  </header>

  <section class="hero">
    <div class="stamps fade-in">
      <img src="https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/logos-impresor.png" alt="Iconos Explora México Tours" class="stamps-img" />
    </div>

    <div class="hero-content fade-in fade-in-d1">
      <div class="eyebrow">Tu próxima aventura comienza aquí</div>
      <h1>
        Estamos rediseñando <br>
        el <span class="accent-magenta">viaje</span>, la <span class="accent-turquesa">aventura</span><br>
        y el <span class="accent-naranja">descubrimiento</span>.
      </h1>
      <p class="hero-sub">
        Más de 20 años llevando viajeros por Jalisco y todo México.
        Pronto, una nueva plataforma para descubrir, comparar y reservar
        las experiencias más auténticas del país.
      </p>
    </div>

    <div class="trust-line fade-in fade-in-d2">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L15 8L22 9L17 14L18 21L12 18L6 21L7 14L2 9L9 8L12 2Z" stroke-linejoin="round"/></svg>
      <span>Una trayectoria real detrás de cada experiencia</span>
    </div>

    <div class="counter fade-in fade-in-d3">
      <div class="counter-item"><div class="counter-value" data-target="20">0</div><div class="counter-label">Años viajando</div></div>
      <div class="counter-item"><div class="counter-value" data-target="70">0</div><div class="counter-label">Tours activos</div></div>
      <div class="counter-item"><div class="counter-value" data-target="15">0</div><div class="counter-label">Destinos</div></div>
      <div class="counter-item"><div class="counter-value" data-target="50000">0</div><div class="counter-label">Viajeros felices</div></div>
    </div>

    <div class="cta-group fade-in fade-in-d4">
      <a href="<?php echo esc_url( $wa_url ); ?>?text=Hola%2C%20me%20interesa%20reservar%20un%20tour" class="btn btn-primary">
        Reservar por WhatsApp
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
      </a>
    </div>
  </section>

  <section class="destinations" id="destinos">
    <div class="section-header">
      <div class="section-eyebrow">Destinos imperdibles</div>
      <h2 class="section-title">Mientras lanzamos, <span class="accent">seguimos viajando</span></h2>
      <p class="section-subtitle">Explora algunas de nuestras experiencias activas mientras preparamos la nueva plataforma.</p>
    </div>

    <div class="dest-grid">
      <a href="<?php echo esc_url( $wa_url ); ?>?text=Quiero%20info%20del%20tour%20Xantolo" class="dest-card">
        <div class="dest-card-bg" style="background-image: url('https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/xantolo.jpg');"></div>
        <div class="dest-card-content">
          <div class="dest-card-meta">Día de Muertos · Huasteca</div>
          <div class="dest-card-title">Xantolo</div>
          <div class="dest-card-desc">La tradición más vibrante de las almas que regresan</div>
          <div class="dest-card-cta">Reservar →</div>
        </div>
      </a>

      <a href="<?php echo esc_url( $wa_url ); ?>?text=Quiero%20info%20del%20tour%20a%20Tequila" class="dest-card">
        <div class="dest-card-bg" style="background-image: url('https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/tequila.jpg');"></div>
        <div class="dest-card-content">
          <div class="dest-card-meta">Pueblo Mágico · Jalisco</div>
          <div class="dest-card-title">Tequila</div>
          <div class="dest-card-desc">Tour completo a la casa del agave azul</div>
          <div class="dest-card-cta">Reservar →</div>
        </div>
      </a>

      <a href="<?php echo esc_url( $wa_url ); ?>?text=Quiero%20info%20del%20tour%20a%20La%20Paz" class="dest-card">
        <div class="dest-card-bg" style="background-image: url('https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/la-paz.jpg');"></div>
        <div class="dest-card-content">
          <div class="dest-card-meta">Mar de Cortés · Baja California Sur</div>
          <div class="dest-card-title">La Paz</div>
          <div class="dest-card-desc">Encuentros con ballenas y playas vírgenes</div>
          <div class="dest-card-cta">Reservar →</div>
        </div>
      </a>

      <a href="<?php echo esc_url( $wa_url ); ?>?text=Quiero%20info%20del%20tour%20al%20Cañón%20de%20Comala" class="dest-card">
        <div class="dest-card-bg" style="background-image: url('https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/canon-de-comala.jpg');"></div>
        <div class="dest-card-content">
          <div class="dest-card-meta">Aventura · Colima</div>
          <div class="dest-card-title">Cañón de Comala</div>
          <div class="dest-card-desc">Naturaleza salvaje al pie del volcán</div>
          <div class="dest-card-cta">Reservar →</div>
        </div>
      </a>
    </div>
  </section>

  <section class="credentials">
    <div class="creds-header">
      <div class="section-eyebrow">Respaldo</div>
      <h3 class="creds-title">Respaldados por alianzas y comunidad turística</h3>
    </div>
    <div class="creds-grid">
      <div class="cred-item">
        <a href="https://amavoccidente.org/agencia/amavgdl35/" target="_blank" rel="noopener noreferrer">
          <img src="https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/amav.png" alt="AMAV Occidente · Agencia GDL35" class="cred-logo" />
        </a>
      </div>
      <div class="cred-item">
        <img src="https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/moderniza.png" alt="Moderniza SECTUR" class="cred-logo" />
      </div>
      <div class="cred-item">
        <img src="https://exploramexicotour.supratecnia.com/wp-content/uploads/2026/05/logos-impresor-1.png" alt="AMTAVE" class="cred-logo" />
      </div>
    </div>
  </section>

  <footer>
    <div class="footer-content">
      <div class="footer-text">© <?php echo date( 'Y' ); ?> Explora México Tours · Guadalajara, Jalisco</div>
      <div class="footer-links">
        <a href="tel:+523310480670">+52 33 1048 0670</a>
        <a href="mailto:reserva@exploramexicotours.com">reserva@exploramexicotours.com</a>
      </div>
    </div>
    <div class="footer-tagline">
      <span>Vibrante.</span>
      <span>Auténtico.</span>
      <span>Inspirador.</span>
      <span>Mexicano.</span>
    </div>
  </footer>

</div>

<a href="<?php echo esc_url( $wa_url ); ?>" class="wa-float" aria-label="Contáctanos por WhatsApp">
  <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
</a>

<script src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/under-construction.js"></script>
<?php wp_footer(); ?>
</body>
</html>
