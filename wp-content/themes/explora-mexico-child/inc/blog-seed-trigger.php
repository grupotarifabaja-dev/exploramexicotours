<?php
/**
 * Disparador TEMPORAL para crear las 3 entradas de ejemplo del blog.
 * Idempotente por slug (si ya existe, no la duplica). Contenido on-brand.
 *
 * Uso: abrir en el navegador  /?emt_seed_blog=emt-blog-2026
 * (por la cache del sitio hay que abrirlo en un navegador real, no desde la nube).
 *
 * QUITAR este archivo (y su require en functions.php) tras crear las entradas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    if ( ! isset( $_GET['emt_seed_blog'] ) ) {
        return;
    }
    if ( ! hash_equals( 'emt-blog-2026', (string) $_GET['emt_seed_blog'] ) ) {
        status_header( 403 );
        header( 'Content-Type: text/plain; charset=utf-8' );
        echo 'token invalido';
        exit;
    }

    $admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
    $author = $admins ? (int) $admins[0] : 1;

    $posts = array(
        array(
            'slug'    => 'ruta-del-tequila-guia-de-un-dia',
            'title'   => 'Ruta del Tequila: guía completa de un día desde Guadalajara',
            'date'    => '2026-07-20 09:00:00',
            'cat'     => 'Gastronomía',
            'tags'    => array( 'Tequila', 'Jalisco', 'Pueblos Mágicos' ),
            'excerpt' => 'Campos de agave, destilerías y catas: cómo aprovechar al máximo un día en el Pueblo Mágico de Tequila, a una hora de Guadalajara.',
            'content' => "<p>A menos de una hora de Guadalajara, entre campos infinitos de agave azul, se encuentra el Pueblo Mágico que le dio nombre a la bebida más famosa de México. Un día en Tequila combina paisaje, historia y, por supuesto, degustación. Esta guía te ayuda a planearlo de principio a fin.</p>\n\n<h2>Cómo llegar</h2>\n<p>La forma más cómoda es en transporte privado: sales de tu hotel por la mañana y en poco más de una hora estás entre los agaves, sin preocuparte por estacionamiento ni por manejar de regreso después de las catas. También existe el Tren José Cuervo Express, una experiencia más larga y festiva ideal para ocasiones especiales.</p>\n\n<h2>Qué no te puedes perder</h2>\n<p>El pueblo es pequeño y se recorre a pie, pero hay paradas que valen cada minuto:</p>\n<ul>\n<li>Una destilería tradicional para ver el proceso completo, del agave a la botella.</li>\n<li>La Plaza principal y la Parroquia de Santiago Apóstol.</li>\n<li>El Museo Nacional del Tequila, para entender su historia y su denominación de origen.</li>\n<li>Una cata guiada de blanco, reposado y añejo, con un experto que te enseña a distinguirlos.</li>\n</ul>\n\n<blockquote><p>El tequila no se toma de un trago: se saborea, como se saborea un buen viaje.</p></blockquote>\n\n<h2>Consejos para aprovechar el día</h2>\n<p>Lleva sombrero y protector solar, porque el sol de Jalisco no perdona. Desayuna bien antes de las catas y reserva con anticipación en temporada alta. Y si viajas en grupo, un asesor puede armarte un itinerario a la medida para que solo te preocupes por disfrutar.</p>\n\n<p>Tequila es de esos lugares donde la tradición se vive con los cinco sentidos. Un día basta para enamorarte, pero seguro te vas planeando la siguiente visita.</p>",
        ),
        array(
            'slug'    => 'pueblos-magicos-de-jalisco',
            'title'   => '5 Pueblos Mágicos de Jalisco que tienes que conocer',
            'date'    => '2026-07-15 09:00:00',
            'cat'     => 'Destinos',
            'tags'    => array( 'Jalisco', 'Pueblos Mágicos' ),
            'excerpt' => 'Más allá de Guadalajara: cinco rincones de Jalisco con historia, artesanía y paisajes que enamoran, ideales para tu próximo viaje.',
            'content' => "<p>Jalisco es mucho más que Guadalajara. En sus montañas, lagos y costas se esconden Pueblos Mágicos con historia, artesanía y paisajes que enamoran. Aquí te dejamos cinco que valen un viaje.</p>\n\n<h2>1. Tequila</h2>\n<p>El origen de la bebida nacional, rodeado de campos de agave azul declarados Patrimonio de la Humanidad. Destilerías, catas y un centro histórico encantador.</p>\n\n<h2>2. Tapalpa</h2>\n<p>Un pueblo de montaña con calles empedradas, casas de adobe y bosques ideales para el turismo de aventura. Perfecto para desconectarse un fin de semana.</p>\n\n<h2>3. Mazamitla</h2>\n<p>Conocido como la Suiza mexicana por su clima fresco y sus cabañas entre pinos. Un destino que combina naturaleza, gastronomía y descanso.</p>\n\n<h2>4. Mascota</h2>\n<p>En la sierra, cerca de Puerto Vallarta, un pueblo tranquilo de arquitectura colonial, ríos y leyendas. Ideal para quienes buscan autenticidad.</p>\n\n<h2>5. San Sebastián del Oeste</h2>\n<p>Un antiguo pueblo minero congelado en el tiempo, entre montañas y niebla. Su café de altura y sus vistas lo hacen inolvidable.</p>\n\n<blockquote><p>Cada Pueblo Mágico de Jalisco cuenta una historia distinta de México.</p></blockquote>\n\n<p>Recorrerlos con calma es la mejor forma de descubrir el alma del estado. Si no sabes por dónde empezar, nuestros asesores pueden armarte una ruta a tu medida.</p>",
        ),
        array(
            'slug'    => 'dia-de-muertos-en-jalisco',
            'title'   => 'Día de Muertos en Jalisco: dónde vivirlo en 2026',
            'date'    => '2026-07-10 09:00:00',
            'cat'     => 'Cultura y tradiciones',
            'tags'    => array( 'Día de Muertos', 'Jalisco', 'Guadalajara' ),
            'excerpt' => 'De los altares de Guadalajara a la magia de Calaverandia: una guía para vivir la celebración más emblemática de México sin perderte lo esencial.',
            'content' => "<p>El Día de Muertos es una de las celebraciones más emblemáticas de México, y Jalisco la vive con una intensidad especial. De los altares tradicionales a los grandes espectáculos, aquí te contamos dónde vivirla en 2026.</p>\n\n<h2>Calaverandia</h2>\n<p>El parque temático de Día de Muertos más grande de México, en Guadalajara. Un recorrido nocturno lleno de luz, música, teatro y gastronomía que reinterpreta la tradición de forma espectacular. Ideal para ir en familia o en grupo.</p>\n\n<h2>Altares y ofrendas en el Centro</h2>\n<p>Durante los primeros días de noviembre, plazas y edificios públicos de Guadalajara se llenan de ofrendas monumentales. Recorrerlas a pie es gratuito y profundamente emotivo.</p>\n\n<h2>Pueblos con tradición viva</h2>\n<p>En comunidades cercanas, los panteones se iluminan con veladoras y flores de cempasúchil la noche del 1 al 2 de noviembre. Es la cara más íntima y auténtica de la celebración.</p>\n\n<blockquote><p>En México no le tememos a la muerte: la recibimos con flores, música y memoria.</p></blockquote>\n\n<h2>Consejos para planear tu visita</h2>\n<ul>\n<li>Reserva alojamiento y tours con semanas de anticipación: es temporada alta.</li>\n<li>Lleva ropa cómoda y abrígate por las noches.</li>\n<li>Respeta los espacios de los panteones: son celebraciones familiares, no solo atracciones.</li>\n</ul>\n\n<p>Vivir el Día de Muertos en Jalisco es una experiencia que combina color, emoción y raíces. Si quieres vivirla sin complicaciones, podemos organizar tu visita completa.</p>",
        ),
    );

    $result = array();
    foreach ( $posts as $p ) {
        $existing = get_posts( array( 'post_type' => 'post', 'name' => $p['slug'], 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
        if ( $existing ) {
            $result[] = $p['slug'] . ' — ya existía (#' . $existing[0] . ')';
            continue;
        }
        $id = wp_insert_post( array(
            'post_title'   => $p['title'],
            'post_name'    => $p['slug'],
            'post_content' => $p['content'],
            'post_excerpt' => $p['excerpt'],
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_author'  => $author,
            'post_date'    => $p['date'],
        ), true );
        if ( is_wp_error( $id ) ) {
            $result[] = $p['slug'] . ' — ERROR: ' . $id->get_error_message();
            continue;
        }
        wp_set_post_terms( $id, array( $p['cat'] ), 'category', false );
        wp_set_post_terms( $id, $p['tags'], 'post_tag', false );
        update_post_meta( $id, '_emt_seed_blog', 1 );
        $result[] = $p['slug'] . ' — creada (#' . $id . ')';
    }

    header( 'Content-Type: application/json; charset=utf-8' );
    echo wp_json_encode( array( 'ok' => true, 'entradas' => $result ), JSON_UNESCAPED_UNICODE );
    exit;
} );


/**
 * Disparador TEMPORAL para dejar el blog visible sin acceso al admin:
 * crea la pagina "Blog", la asigna como Pagina de entradas (page_for_posts),
 * asegura una pagina de Inicio como portada (show_on_front=page) y refresca rutas.
 * Uso: /?emt_blog_setup=emt-blog-2026 (abrir en navegador por la cache).
 * QUITAR junto con el resto de disparadores antes de produccion.
 */
add_action( 'init', function () {
    if ( ! isset( $_GET['emt_blog_setup'] ) ) {
        return;
    }
    if ( ! hash_equals( 'emt-blog-2026', (string) $_GET['emt_blog_setup'] ) ) {
        status_header( 403 );
        header( 'Content-Type: text/plain; charset=utf-8' );
        echo 'token invalido';
        exit;
    }

    $antes = array(
        'show_on_front'  => get_option( 'show_on_front' ),
        'page_on_front'  => (int) get_option( 'page_on_front' ),
        'page_for_posts' => (int) get_option( 'page_for_posts' ),
    );

    // 1) Pagina "Blog" (posts page).
    $blog = get_page_by_path( 'blog' );
    if ( $blog ) {
        $blog_id = (int) $blog->ID;
    } else {
        $blog_id = wp_insert_post( array(
            'post_title'  => 'Blog',
            'post_name'   => 'blog',
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_content'=> '',
        ), true );
    }
    if ( is_wp_error( $blog_id ) ) {
        header( 'Content-Type: application/json; charset=utf-8' );
        echo wp_json_encode( array( 'error' => 'blog: ' . $blog_id->get_error_message() ) );
        exit;
    }

    // 2) Portada estatica (Inicio) — necesaria para poder tener page_for_posts.
    $front_id = (int) get_option( 'page_on_front' );
    if ( ! $front_id || 'publish' !== get_post_status( $front_id ) ) {
        $home = get_page_by_path( 'inicio' );
        if ( $home ) {
            $front_id = (int) $home->ID;
        } else {
            $new = wp_insert_post( array(
                'post_title'  => 'Inicio',
                'post_name'   => 'inicio',
                'post_status' => 'publish',
                'post_type'   => 'page',
                'post_content'=> '',
            ), true );
            $front_id = is_wp_error( $new ) ? 0 : (int) $new;
        }
    }

    // 3) Aplicar ajustes de Lectura.
    if ( $front_id ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $front_id );
    }
    update_option( 'page_for_posts', $blog_id );
    flush_rewrite_rules();

    $despues = array(
        'show_on_front'  => get_option( 'show_on_front' ),
        'page_on_front'  => (int) get_option( 'page_on_front' ),
        'page_for_posts' => (int) get_option( 'page_for_posts' ),
        'blog_url'       => get_permalink( $blog_id ),
    );

    header( 'Content-Type: application/json; charset=utf-8' );
    echo wp_json_encode( array( 'ok' => true, 'antes' => $antes, 'despues' => $despues ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
    exit;
} );
