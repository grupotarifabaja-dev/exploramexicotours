<?php
/**
 * Panel — sección Asesores (P4).
 * Lista (tabla) por defecto; si la sub-vista es nuevo/editar, delega al formulario.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$arg = sanitize_key( get_query_var( 'emt_arg' ) );
if ( in_array( $arg, array( 'nuevo', 'editar' ), true ) ) {
    $form = get_stylesheet_directory() . '/parts/panel/view-asesor-form.php';
    if ( file_exists( $form ) ) {
        include $form;
        return;
    }
}

$asesores = get_posts( array(
    'post_type'      => 'asesor',
    'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
) );
?>
<div class="emt-panel__head">
    <div>
        <h1>Asesores <span class="emt-panel__count"><?php echo count( $asesores ); ?></span></h1>
        <p class="emt-panel__head-sub">Gestiona el equipo de asesores de viaje.</p>
    </div>
    <a class="emt-panel__btn emt-panel__btn--primary" href="<?php echo esc_url( emt_panel_url( 'asesores/nuevo/' ) ); ?>">+ Nuevo Asesor</a>
</div>

<div class="emt-panel__toolbar">
    <div class="emt-panel__search">
        <input type="search" placeholder="Buscar asesor…" data-emt-search="#emt-asesores-table" aria-label="Buscar asesor" />
    </div>
</div>

<div class="emt-panel__table-wrap">
    <?php if ( $asesores ) : ?>
    <table class="emt-panel__table" id="emt-asesores-table">
        <thead>
            <tr>
                <th>Asesor</th>
                <th>Puesto</th>
                <th>Idiomas</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $asesores as $a ) :
                $aid    = $a->ID;
                $thumb  = has_post_thumbnail( $aid ) ? get_the_post_thumbnail_url( $aid, 'thumbnail' ) : ( function_exists( 'emt_get_image_or_placeholder' ) ? emt_get_image_or_placeholder( $aid, 'thumbnail' ) : '' );
                $puesto = get_field( 'puesto', $aid );
                $idis   = get_the_terms( $aid, 'asesor_idioma' );
                $idioma = ( $idis && ! is_wp_error( $idis ) ) ? implode( ', ', wp_list_pluck( $idis, 'name' ) ) : '—';
                $activo = get_field( 'activo', $aid );
                $status = get_post_status( $aid );
                $publ   = ( $status === 'publish' );
                // El estado combina publicación + el flag "activo".
                if ( ! $publ ) {
                    $st_lbl = ( $status === 'draft' ? 'Borrador' : ucfirst( $status ) );
                    $st_cls = 'draft';
                } elseif ( $activo ) {
                    $st_lbl = 'Activo';
                    $st_cls = 'publish';
                } else {
                    $st_lbl = 'Inactivo';
                    $st_cls = 'draft';
                }
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img class="emt-panel__thumb emt-panel__thumb--round" src="<?php echo esc_url( $thumb ); ?>" alt="" />
                            <strong><?php echo esc_html( get_the_title( $aid ) ); ?></strong>
                        </div>
                    </td>
                    <td><?php echo $puesto ? esc_html( $puesto ) : '—'; ?></td>
                    <td><?php echo esc_html( $idioma ); ?></td>
                    <td><span class="emt-panel__status emt-panel__status--<?php echo esc_attr( $st_cls ); ?>"><?php echo esc_html( $st_lbl ); ?></span></td>
                    <td>
                        <div class="emt-panel__row-actions">
                            <a class="emt-panel__btn emt-panel__btn--sm" href="<?php echo esc_url( emt_panel_url( 'asesores/editar/' . $aid . '/' ) ); ?>">Editar</a>
                            <a class="emt-panel__btn emt-panel__btn--sm" href="<?php echo esc_url( get_permalink( $aid ) ); ?>" target="_blank" rel="noopener">Ver</a>
                            <button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-emt-delete="emt_panel_delete_asesor" data-id="<?php echo (int) $aid; ?>" data-title="<?php echo esc_attr( get_the_title( $aid ) ); ?>">Eliminar</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p class="emt-panel__empty">Aún no hay asesores. <a href="<?php echo esc_url( emt_panel_url( 'asesores/nuevo/' ) ); ?>">Crea el primero</a>.</p>
    <?php endif; ?>
</div>
