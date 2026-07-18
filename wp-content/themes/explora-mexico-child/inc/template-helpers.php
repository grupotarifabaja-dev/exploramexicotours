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
    printf( '<span class="%s" aria-hidden="true">%s</span>', esc_attr( $classes ), esc_html( emt_asesor_initials( $post_id ) ) );
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
