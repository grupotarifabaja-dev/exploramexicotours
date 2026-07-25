<?php
/**
 * Panel — sección Clasificación (Destinos, Categorías y Experiencias).
 *
 * Parte A — el cliente crea, renombra y elimina los términos de las tres
 * taxonomías de tours (add/rename/delete vía AJAX: emt_panel_term_add /
 * emt_panel_term_rename / emt_panel_term_delete).
 *
 * Parte B — para los Destinos, además, elige cuáles aparecen en la sección
 * "Destinos imperdibles" del inicio y les pone portada (emt_panel_save_destinos).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Fila editable de un término (nombre + contador + eliminar). */
if ( ! function_exists( 'emt_render_term_row' ) ) {
    function emt_render_term_row( $t ) {
        $count = (int) $t->count;
        ?>
        <div class="emt-term-row" data-term-id="<?php echo (int) $t->term_id; ?>">
            <input type="text" class="emt-term-row__name" value="<?php echo esc_attr( $t->name ); ?>" data-term-name aria-label="Nombre" />
            <span class="emt-term-row__count"><?php echo $count; ?> tour<?php echo ( $count === 1 ) ? '' : 's'; ?></span>
            <span class="emt-term-row__msg" data-term-msg></span>
            <button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-term-delete>Eliminar</button>
        </div>
        <?php
    }
}

/** Gestor completo de una taxonomía (crear + lista editable). */
if ( ! function_exists( 'emt_render_term_manager' ) ) {
    function emt_render_term_manager( $tax, $titulo, $ayuda, $ph ) {
        $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
        if ( is_wp_error( $terms ) ) { $terms = array(); }
        ?>
        <div class="emt-panel-form__section emt-tax-manager" data-tax="<?php echo esc_attr( $tax ); ?>">
            <h2><?php echo esc_html( $titulo ); ?></h2>
            <p class="emt-field__help" style="margin-bottom:var(--emt-spacing-md);"><?php echo esc_html( $ayuda ); ?></p>
            <div class="emt-tax-add">
                <input type="text" class="emt-tax-add__input" placeholder="<?php echo esc_attr( $ph ); ?>" data-term-new aria-label="Nombre nuevo" />
                <button type="button" class="emt-panel__btn emt-panel__btn--primary" data-term-add>Agregar</button>
            </div>
            <div class="emt-tax-list" data-tax-list>
                <p class="emt-panel__muted" data-tax-empty<?php echo empty( $terms ) ? '' : ' style="display:none;"'; ?>>Aún no hay. Agrega el primero arriba.</p>
                <?php foreach ( $terms as $t ) { emt_render_term_row( $t ); } ?>
            </div>
        </div>
        <?php
    }
}

// Destinos para la Parte B (destacado + portada).
$destinos = get_terms( array(
    'taxonomy'   => 'tour_destino',
    'hide_empty' => false,
    'parent'     => 0,
    'orderby'    => 'name',
    'order'      => 'ASC',
) );
if ( is_wp_error( $destinos ) ) { $destinos = array(); }
?>
<div class="emt-panel__head">
    <div>
        <h1>Clasificación</h1>
        <p class="emt-panel__head-sub">Crea y organiza los <strong>destinos</strong>, <strong>categorías</strong> y <strong>experiencias</strong> con los que etiquetas tus tours. Los cambios se guardan al momento.</p>
    </div>
</div>

<?php
emt_render_term_manager(
    'tour_destino',
    'Destinos',
    'El lugar del tour (Guadalajara, Chiapas, Barrancas del Cobre…). Un tour tiene un destino.',
    'Nombre del destino'
);
emt_render_term_manager(
    'tour_categoria',
    'Categorías',
    'El tipo de tour (Cultural, Gastronómico, Aventura, Ecoturismo…). Un tour tiene una categoría.',
    'Nombre de la categoría'
);
emt_render_term_manager(
    'tour_experiencia',
    'Experiencias',
    'El tema u ocasión, transversal a las categorías (Pueblos Mágicos, Ruta del Tequila, Día de Muertos…). Un tour puede tener varias.',
    'Nombre de la experiencia'
);
?>

<?php if ( ! empty( $destinos ) ) : ?>
<form id="emt-destinos-form" data-emt-form data-ajax-action="emt_panel_save_destinos" data-required-draft="" data-required-publish="">

    <div class="emt-panel-form__section">
        <h2>Portadas y destacados del inicio</h2>
        <p class="emt-field__help" style="margin-bottom:var(--emt-spacing-md);">Marca "Destacado en home" en los destinos que quieras mostrar en la sección "Destinos imperdibles" (hasta 5). Si no marcas ninguno, el inicio mostrará automáticamente los destinos con más tours. Tamaño de portada sugerido: <strong>1200&times;1600 px</strong> (vertical 3:4).</p>

        <div class="emt-destinos-admin">
            <?php foreach ( $destinos as $d ) :
                $dest_val   = get_field( 'destacado', $d );
                $is_dest    = ! empty( $dest_val );
                $img        = get_field( 'imagen_destino', $d );
                $img_id     = is_array( $img ) ? (int) ( $img['ID'] ?? $img['id'] ?? 0 ) : 0;
                $img_thumb  = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
                $tid        = (int) $d->term_id;
                ?>
                <div class="emt-destino-row">
                    <div class="emt-destino-row__info">
                        <span class="emt-destino-row__name"><?php echo esc_html( $d->name ); ?></span>
                        <span class="emt-destino-row__count"><?php echo (int) $d->count; ?> tour<?php echo ( (int) $d->count === 1 ) ? '' : 's'; ?></span>
                        <label class="emt-destino-row__toggle">
                            <input type="hidden" name="destino_ids[]" value="<?php echo $tid; ?>" />
                            <input type="checkbox" name="destacado[<?php echo $tid; ?>]" value="1"<?php checked( $is_dest ); ?> />
                            <span>Destacado en home</span>
                        </label>
                    </div>
                    <div class="emt-destino-row__portada">
                        <div class="emt-image emt-image--sm" data-image>
                            <div class="emt-image__preview" data-image-preview>
                                <?php if ( $img_thumb ) : ?><img src="<?php echo esc_url( $img_thumb ); ?>" alt="" /><?php endif; ?>
                            </div>
                            <input type="hidden" name="imagen_destino[<?php echo $tid; ?>]" value="<?php echo $img_id; ?>" data-image-input />
                            <div class="emt-image__actions">
                                <button type="button" class="emt-panel__btn emt-panel__btn--sm" data-image-add>Portada</button>
                                <button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-image-remove<?php echo $img_id ? '' : ' style="display:none;"'; ?>>Quitar</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="emt-field__help" style="margin-top:var(--emt-spacing-sm);">Si renombras un destino arriba, aquí se actualiza al recargar la página.</div>
    </div>

    <div class="emt-panel-form__bar">
        <span class="emt-panel-form__msg" data-form-msg></span>
        <button type="submit" class="emt-panel__btn emt-panel__btn--primary" data-save="save">Guardar portadas y destacados</button>
    </div>
</form>
<?php endif; ?>
