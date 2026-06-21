<?php
/**
 * Panel — sección Tours.
 * Lista (tabla) por defecto; si la sub-vista es nuevo/editar, delega al formulario (P3).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$arg = sanitize_key( get_query_var( 'emt_arg' ) );
if ( in_array( $arg, array( 'nuevo', 'editar' ), true ) ) {
    $form = get_stylesheet_directory() . '/parts/panel/view-tour-form.php';
    if ( file_exists( $form ) ) {
        include $form;
        return;
    }
}

$tours = get_posts( array(
    'post_type'      => 'tour',
    'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );
?>
<div class="emt-panel__head">
    <div>
        <h1>Tours <span class="emt-panel__count"><?php echo count( $tours ); ?></span></h1>
        <p class="emt-panel__head-sub">Gestiona el catálogo de tours.</p>
    </div>
    <a class="emt-panel__btn emt-panel__btn--primary" href="<?php echo esc_url( emt_panel_url( 'tours/nuevo/' ) ); ?>">+ Nuevo Tour</a>
</div>

<div class="emt-panel__toolbar">
    <div class="emt-panel__search">
        <input type="search" placeholder="Buscar tour…" data-emt-search="#emt-tours-table" aria-label="Buscar tour" />
    </div>
</div>

<div class="emt-panel__table-wrap">
    <?php if ( $tours ) : ?>
    <table class="emt-panel__table" id="emt-tours-table">
        <thead>
            <tr>
                <th>Tour</th>
                <th>Destino</th>
                <th>Desde</th>
                <th>Duración</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $tours as $t ) :
                $tid     = $t->ID;
                $thumb   = has_post_thumbnail( $tid ) ? get_the_post_thumbnail_url( $tid, 'thumbnail' ) : ( function_exists( 'emt_get_image_or_placeholder' ) ? emt_get_image_or_placeholder( $tid, 'thumbnail' ) : '' );
                $dests   = get_the_terms( $tid, 'tour_destino' );
                $destino = ( $dests && ! is_wp_error( $dests ) ) ? $dests[0]->name : '—';
                $precio  = get_field( 'precio_desde', $tid );
                $durac   = get_field( 'duracion_texto', $tid );
                $status  = get_post_status( $tid );
                $st_lbl  = ( $status === 'publish' ) ? 'Publicado' : ( $status === 'draft' ? 'Borrador' : ucfirst( $status ) );
                $st_cls  = ( $status === 'publish' ) ? 'publish' : 'draft';
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img class="emt-panel__thumb" src="<?php echo esc_url( $thumb ); ?>" alt="" />
                            <strong><?php echo esc_html( get_the_title( $tid ) ); ?></strong>
                        </div>
                    </td>
                    <td><?php echo esc_html( $destino ); ?></td>
                    <td><?php echo $precio ? esc_html( emt_format_price( $precio ) ) : '—'; ?></td>
                    <td><?php echo $durac ? esc_html( $durac ) : '—'; ?></td>
                    <td><span class="emt-panel__status emt-panel__status--<?php echo esc_attr( $st_cls ); ?>"><?php echo esc_html( $st_lbl ); ?></span></td>
                    <td>
                        <div class="emt-panel__row-actions">
                            <a class="emt-panel__btn emt-panel__btn--sm" href="<?php echo esc_url( emt_panel_url( 'tours/editar/' . $tid . '/' ) ); ?>">Editar</a>
                            <a class="emt-panel__btn emt-panel__btn--sm" href="<?php echo esc_url( get_permalink( $tid ) ); ?>" target="_blank" rel="noopener">Ver</a>
                            <button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-emt-delete="emt_panel_delete_tour" data-id="<?php echo (int) $tid; ?>" data-title="<?php echo esc_attr( get_the_title( $tid ) ); ?>">Eliminar</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p class="emt-panel__empty">Aún no hay tours. <a href="<?php echo esc_url( emt_panel_url( 'tours/nuevo/' ) ); ?>">Crea el primero</a>.</p>
    <?php endif; ?>
</div>
