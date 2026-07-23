<?php
/**
 * Helpers de renderizado y plantillas (doc maestro §14·B7, §7.4).
 *
 * Provee:
 *   - emt_render_tour_card( $tour )
 *   - emt_render_asesor_card( $asesor )
 *   - emt_get_image_or_placeholder( $post_id, $size )
 *   - emt_format_price( $amount, $currency )
 *   - emt_breadcrumbs() (+ JSON-LD BreadcrumbList)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Devuelve un ícono SVG inline del set de la UI (sin dependencias).
 * Reemplaza los emojis en metadatos de tours. Hereda el color con currentColor.
 *
 * @param string $name  clock | calendar | pin
 * @param string $class Clase CSS (default emt-meta-icon).
 * @return string SVG o '' si el nombre no existe.
 */
function emt_icon( $name, $class = 'emt-meta-icon' ) {
    $paths = array(
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3 2"/>',
        'calendar' => '<rect x="3.5" y="4.5" width="17" height="16" rx="2"/><path d="M16 3v3M8 3v3M3.5 9.5h17"/>',
        'pin'      => '<path d="M20 10.5c0 5.4-8 11-8 11s-8-5.6-8-11a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10.5" r="2.6"/>',
    );
    if ( ! isset( $paths[ $name ] ) ) { return ''; }
    return '<svg class="' . esc_attr( $class ) . '" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Renderiza una tarjeta de tour incluyendo parts/tour-card.php.
 *
 * @param int|WP_Post $tour ID o post del tour.
 * @return void Imprime el HTML de la tarjeta.
 */
function emt_render_tour_card( $tour ) {
    $emt_card_tour = $tour; // visible para el partial vía scope del include
    include get_stylesheet_directory() . '/parts/tour-card.php';
}

/**
 * Renderiza una tarjeta de asesor incluyendo parts/asesor-card.php.
 *
 * @param int|WP_Post $asesor ID o post del asesor.
 * @return void Imprime el HTML de la tarjeta.
 */
function emt_render_asesor_card( $asesor ) {
    $emt_card_asesor = $asesor;
    include get_stylesheet_directory() . '/parts/asesor-card.php';
}

/**
 * Iniciales de un asesor para el avatar de respaldo (máx. 2, con acentos).
 *
 * @param int $post_id ID del asesor.
 * @return string Iniciales en mayúsculas, p. ej. "FV".
 */
function emt_asesor_initials( $post_id ) {
    $words = preg_split( '/\s+/', trim( wp_strip_all_tags( get_the_title( $post_id ) ) ) );
    $ini   = '';
    foreach ( array_slice( (array) $words, 0, 2 ) as $w ) {
        if ( $w !== '' ) {
            $ini .= mb_strtoupper( mb_substr( $w, 0, 1 ) );
        }
    }
    return $ini;
}

/**
 * Imprime el avatar de un asesor (componente .emt-avatar del sistema).
 * Con foto destacada: recorte circular. Sin foto: iniciales sobre azul.
 *
 * @param int    $post_id  ID del asesor.
 * @param string $size     Modificador del componente: sm|md|lg|xl (md = sin sufijo).
 * @param string $img_size Tamaño de imagen de WP para la foto.
 * @return void
 */
function emt_asesor_avatar( $post_id, $size = 'md', $img_size = 'medium' ) {
    $classes = 'emt-avatar' . ( $size !== 'md' ? ' emt-avatar--' . sanitize_html_class( $size ) : '' );
    $url     = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, $img_size ) : '';
    if ( $url ) {
        printf(
            '<span class="%s"><img src="%s" alt="%s" loading="lazy" /></span>',
            esc_attr( $classes ),
            esc_url( $url ),
            esc_attr( get_the_title( $post_id ) )
        );
        return;
    }
    // Sin foto: silueta gris neutra (avatar placeholder) en vez de iniciales.
    $emt_svg = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>';
    printf( '<span class="%s emt-avatar--placeholder" aria-hidden="true">%s</span>', esc_attr( $classes ), $emt_svg );
}

/**
 * Devuelve la URL de la imagen destacada del post o un placeholder del theme./**
 * Devuelve la URL de la imagen destacada del post o un placeholder del theme.
 *
 * @param int    $post_id ID del post.
 * @param string $size    Tamaño de imagen registrado.
 * @return string URL de imagen (real o placeholder).
 */
function emt_get_image_or_placeholder( $post_id, $size = 'large' ) {
    if ( has_post_thumbnail( $post_id ) ) {
        $url = get_the_post_thumbnail_url( $post_id, $size );
        if ( $url ) {
            return $url;
        }
    }
    $ph_file = get_stylesheet_directory() . '/assets/images/placeholders/placeholder.svg';
    $ph_url  = get_stylesheet_directory_uri() . '/assets/images/placeholders/placeholder.svg';
    if ( file_exists( $ph_file ) ) {
        $ph_url .= '?v=' . filemtime( $ph_file ); // cache-bust al cambiar el placeholder
    }
    return $ph_url;
}

/**
 * Imagen de un término de taxonomía de tours, con cascada de respaldo:
 *   1) campo ACF 'imagen_destino' del término (term meta; hoy registrado para
 *      tour_destino — en otras taxonomías simplemente no existe y se salta),
 *   2) foto destacada de un tour publicado con ese término (cualquier taxonomía),
 *   3) '' (el CSS deja el degradado/placeholder).
 *
 * La usan las cards de destinos del home y el mega-menú (misma cascada:
 * UN solo lugar para personalizar imágenes por término).
 *
 * @param WP_Term $term   Término de tour_destino / tour_categoria / tour_experiencia.
 * @param string  $size   Tamaño de imagen.
 * @return string URL de imagen o '' si no hay ninguna.
 */
function emt_destino_image_url( $term, $size = 'medium_large' ) {
    if ( function_exists( 'get_field' ) ) {
        $img = get_field( 'imagen_destino', $term );
        if ( is_array( $img ) ) {
            return $img['sizes'][ $size ] ?? $img['url'] ?? '';
        }
    }
    // Respaldo: foto destacada de un tour con ese término.
    $tours = get_posts( array(
        'post_type'      => 'tour',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => '_thumbnail_id',
        'tax_query'      => array( array(
            'taxonomy' => $term->taxonomy,
            'field'    => 'term_id',
            'terms'    => $term->term_id,
        ) ),
    ) );
    if ( $tours ) {
        $url = get_the_post_thumbnail_url( $tours[0], $size );
        if ( $url ) {
            return $url;
        }
    }
    return '';
}

/**
 * Formatea un precio. MXN sin decimales (doc maestro §6.1).
 *
 * @param int|float|string $amount   Monto.
 * @param string           $currency Código de moneda (default MXN).
 * @return string Precio formateado (p. ej. "$1,899 MXN") o '' si no hay monto.
 */
function emt_format_price( $amount, $currency = 'MXN' ) {
    if ( $amount === '' || $amount === null ) {
        return '';
    }
    $decimals = ( $currency === 'MXN' ) ? 0 : 2;
    return '$' . number_format_i18n( (float) $amount, $decimals ) . ' ' . $currency;
}

/**
 * Construye la lista de crumbs según el contexto actual (doc maestro §7.4).
 *
 * @return array<int,array{name:string,url:string}>
 */
function emt_get_breadcrumb_items() {
    $lang   = function_exists( 'emt_current_lang' ) ? emt_current_lang() : 'es';
    $prefix = ( $lang === 'en' ) ? '/en' : '';
    $items  = array();

    $items[] = array(
        'name' => function_exists( 'emt_t' ) ? emt_t( 'inicio' ) : 'Inicio',
        'url'  => home_url( $prefix . '/' ),
    );

    if ( is_singular( 'tour' ) ) {
        $items[] = array( 'name' => emt_t( 'tours' ), 'url' => home_url( $prefix . '/tours/' ) );
        $terms = get_the_terms( get_the_ID(), 'tour_destino' );
        if ( $terms && ! is_wp_error( $terms ) ) {
            $tl = get_term_link( $terms[0] );
            $items[] = array( 'name' => $terms[0]->name, 'url' => is_wp_error( $tl ) ? '#' : $tl );
        }
        $items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
    } elseif ( is_post_type_archive( 'tour' ) ) {
        $items[] = array( 'name' => emt_t( 'tours' ), 'url' => home_url( $prefix . '/tours/' ) );
    } elseif ( is_tax( array( 'tour_destino', 'tour_categoria', 'tour_experiencia' ) ) ) {
        $items[] = array( 'name' => emt_t( 'tours' ), 'url' => home_url( $prefix . '/tours/' ) );
        $term = get_queried_object();
        if ( $term && isset( $term->name ) ) {
            $items[] = array( 'name' => $term->name, 'url' => '' );
        }
    } elseif ( is_singular( 'asesor' ) ) {
        $items[] = array( 'name' => emt_t( 'asesores' ), 'url' => home_url( $prefix . '/asesores/' ) );
        $items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
    } elseif ( is_post_type_archive( 'asesor' ) ) {
        $items[] = array( 'name' => emt_t( 'asesores' ), 'url' => home_url( $prefix . '/asesores/' ) );
    } elseif ( is_singular( 'post' ) ) {
        $items[] = array( 'name' => emt_t( 'blog' ), 'url' => home_url( $prefix . '/blog/' ) );
        $items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
    } elseif ( is_singular() || is_page() ) {
        $items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
    }

    return $items;
}

/**
 * Imprime los breadcrumbs (nav accesible) + JSON-LD BreadcrumbList (§7.4, §9.5).
 *
 * @return void
 */
function emt_breadcrumbs() {
    $items = emt_get_breadcrumb_items();
    if ( count( $items ) < 2 ) {
        return; // en el home no se muestran
    }

    echo '<nav class="emt-breadcrumbs" aria-label="Breadcrumb"><ol class="emt-breadcrumbs__list">';
    $last = count( $items ) - 1;
    foreach ( $items as $i => $c ) {
        $is_last = ( $i === $last );
        echo '<li class="emt-breadcrumbs__item">';
        if ( ! $is_last && ! empty( $c['url'] ) ) {
            printf( '<a href="%s">%s</a>', esc_url( $c['url'] ), esc_html( $c['name'] ) );
        } else {
            printf( '<span aria-current="page">%s</span>', esc_html( $c['name'] ) );
        }
        echo '</li>';
    }
    echo '</ol></nav>';

    // JSON-LD BreadcrumbList
    $list = array();
    foreach ( $items as $i => $c ) {
        $entry = array(
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $c['name'],
        );
        if ( ! empty( $c['url'] ) ) {
            $entry['item'] = $c['url'];
        }
        $list[] = $entry;
    }
    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $list,
    );
    echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
}


/* ============================================================
   BLOG (listado + tarjetas) — rediseno 2026
   ============================================================ */

/** Minutos estimados de lectura (~200 palabras/min). */
function emt_read_minutes( $post_id ) {
    $words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
    return max( 1, (int) ceil( $words / 200 ) );
}

/** Meta de entrada: fecha + N min de lectura (HTML). */
function emt_post_meta_html( $post_id ) {
    return sprintf(
        '<span class="emt-post-meta">%s <span class="emt-post-meta__dot"></span> %d %s</span>',
        esc_html( get_the_date( '', $post_id ) ),
        emt_read_minutes( $post_id ),
        esc_html( emt_t( 'min_lectura' ) )
    );
}

/** Nav de categorias del blog (chips). $current_id resalta la categoria activa. */
function emt_blog_cats_nav( $current_id = 0 ) {
    $cats = get_categories( array( 'hide_empty' => true ) );
    if ( empty( $cats ) || is_wp_error( $cats ) ) {
        return '';
    }
    $blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' );
    $out = '<nav class="emt-post-cats" aria-label="' . esc_attr( emt_t( 'categorias' ) ) . '">';
    $out .= sprintf( '<a class="emt-post-cat%s" href="%s">%s</a>', $current_id ? '' : ' is-on', esc_url( $blog_url ), esc_html( emt_t( 'todas' ) ) );
    foreach ( $cats as $c ) {
        $sel = ( (int) $current_id === (int) $c->term_id ) ? ' is-on' : '';
        $out .= sprintf( '<a class="emt-post-cat%s" href="%s">%s</a>', $sel, esc_url( get_category_link( $c->term_id ) ), esc_html( $c->name ) );
    }
    $out .= '</nav>';
    return $out;
}

/** Tarjeta de entrada del blog. $featured = tarjeta destacada grande. */
function emt_render_blog_card( $post_id, $featured = false ) {
    $url     = get_permalink( $post_id );
    $img     = emt_get_image_or_placeholder( $post_id, $featured ? 'large' : 'medium_large' );
    $cats    = get_the_category( $post_id );
    $cat     = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0] : null;
    $excerpt = wp_trim_words( get_the_excerpt( $post_id ), $featured ? 30 : 20, '…' );

    $badge = function ( $extra ) use ( $cat ) {
        if ( ! $cat ) { return ''; }
        return sprintf( '<a class="emt-post-badge%s" href="%s">%s</a>', $extra, esc_url( get_category_link( $cat->term_id ) ), esc_html( $cat->name ) );
    };

    if ( $featured ) {
        printf(
            '<article class="emt-post-featured">'
            . '<a class="emt-post-featured__media" href="%1$s" tabindex="-1" aria-hidden="true"><img src="%2$s" alt="" loading="lazy" /></a>'
            . '<div class="emt-post-featured__body">%3$s'
            . '<h2 class="emt-post-featured__title"><a href="%1$s">%4$s</a></h2>'
            . '<p class="emt-post-featured__excerpt">%5$s</p>%6$s'
            . '<a class="emt-post-readmore" href="%1$s">%7$s &rarr;</a></div></article>',
            esc_url( $url ), esc_url( $img ), $badge( '' ),
            esc_html( get_the_title( $post_id ) ), esc_html( $excerpt ),
            emt_post_meta_html( $post_id ), esc_html( emt_t( 'leer_mas' ) )
        );
        return;
    }

    printf(
        '<article class="emt-post-card">'
        . '<a class="emt-post-card__media" href="%1$s" tabindex="-1" aria-hidden="true"><img src="%2$s" alt="" loading="lazy" />%3$s</a>'
        . '<div class="emt-post-card__body">'
        . '<h3 class="emt-post-card__title"><a href="%1$s">%4$s</a></h3>'
        . '<p class="emt-post-card__excerpt">%5$s</p>%6$s</div></article>',
        esc_url( $url ), esc_url( $img ), $badge( ' emt-post-badge--overlay' ),
        esc_html( get_the_title( $post_id ) ), esc_html( $excerpt ),
        emt_post_meta_html( $post_id )
    );
}

/** Bucle del listado: destacado (1a entrada de la pag. 1) + grid + paginacion. */
function emt_render_blog_loop( $show_featured = true ) {
    if ( have_posts() ) {
        $idx = 0; $grid_open = false;
        while ( have_posts() ) {
            the_post();
            if ( $show_featured && 0 === $idx && ! is_paged() ) {
                emt_render_blog_card( get_the_ID(), true );
            } else {
                if ( ! $grid_open ) { echo '<div class="emt-post-grid">'; $grid_open = true; }
                emt_render_blog_card( get_the_ID(), false );
            }
            $idx++;
        }
        if ( $grid_open ) { echo '</div>'; }
        echo '<div class="emt-post-pager">';
        the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) );
        echo '</div>';
    } else {
        printf(
            '<div class="emt-empty"><h2 class="emt-empty__title">%s</h2><p class="emt-empty__text">%s</p></div>',
            esc_html( emt_t( 'sin_entradas_titulo' ) ),
            esc_html( emt_t( 'sin_entradas_texto' ) )
        );
    }
}
