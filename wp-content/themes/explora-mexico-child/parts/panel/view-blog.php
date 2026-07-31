<?php
/**
 * Panel — sección Blog. Lista de entradas; delega al formulario en nuevo/editar.
 * CRUD de entradas (post) desde el panel del cliente, sin acceso al admin de WP.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$arg = sanitize_key( get_query_var( 'emt_arg' ) );
if ( in_array( $arg, array( 'nuevo', 'editar' ), true ) ) {
    $form = get_stylesheet_directory() . '/parts/panel/view-blog-form.php';
    if ( file_exists( $form ) ) {
        include $form;
        return;
    }
}

$entradas = get_posts( array(
    'post_type'      => 'post',
    'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );
?>
<div class="emt-panel__head">
    <div>
        <h1>Blog <span class="emt-panel__count"><?php echo count( $entradas ); ?></span></h1>
        <p class="emt-panel__head-sub">Crea, edita y publica artículos del blog.</p>
    </div>
    <a class="emt-panel__btn emt-panel__btn--primary" href="<?php echo esc_url( emt_panel_url( 'blog/nuevo/' ) ); ?>">+ Nuevo artículo</a>
</div>

<div class="emt-panel__toolbar">
    <div class="emt-panel__search">
        <input type="search" placeholder="Buscar artículo…" data-emt-search="#emt-blog-table" aria-label="Buscar artículo" />
    </div>
</div>

<div class="emt-panel__table-wrap">
    <?php if ( $entradas ) : ?>
    <table class="emt-panel__table" id="emt-blog-table">
        <thead>
            <tr>
                <th>Artículo</th>
                <th>Categoría</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $entradas as $p ) :
                $pid    = $p->ID;
                $thumb  = has_post_thumbnail( $pid ) ? get_the_post_thumbnail_url( $pid, 'thumbnail' ) : ( function_exists( 'emt_get_image_or_placeholder' ) ? emt_get_image_or_placeholder( $pid, 'thumbnail' ) : '' );
                $cats   = get_the_category( $pid );
                $catn   = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '—';
                $status = get_post_status( $pid );
                $publ   = ( $status === 'publish' );
                $st_lbl = $publ ? 'Publicado' : ( $status === 'draft' ? 'Borrador' : ucfirst( $status ) );
                $st_cls = $publ ? 'publish' : 'draft';
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img class="emt-panel__thumb" src="<?php echo esc_url( $thumb ); ?>" alt="" />
                            <strong><?php echo esc_html( get_the_title( $pid ) ); ?></strong>
                        </div>
                    </td>
                    <td><?php echo esc_html( $catn ); ?></td>
                    <td><?php echo esc_html( get_the_date( 'd/m/Y', $pid ) ); ?></td>
                    <td><span class="emt-panel__status emt-panel__status--<?php echo esc_attr( $st_cls ); ?>"><?php echo esc_html( $st_lbl ); ?></span></td>
                    <td>
                        <div class="emt-panel__row-actions">
                            <a class="emt-panel__btn emt-panel__btn--sm" href="<?php echo esc_url( emt_panel_url( 'blog/editar/' . $pid . '/' ) ); ?>">Editar</a>
                            <a class="emt-panel__btn emt-panel__btn--sm" href="<?php echo esc_url( get_permalink( $pid ) ); ?>" target="_blank" rel="noopener">Ver</a>
                            <button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-emt-delete="emt_panel_delete_post" data-id="<?php echo (int) $pid; ?>" data-title="<?php echo esc_attr( get_the_title( $pid ) ); ?>">Eliminar</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p class="emt-panel__empty">Aún no hay artículos. <a href="<?php echo esc_url( emt_panel_url( 'blog/nuevo/' ) ); ?>">Crea el primero</a>.</p>
    <?php endif; ?>
</div>
