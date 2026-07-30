<?php
/**
 * Panel — sección Clasificación (Destinos, Categorías y Experiencias).
 *
 * Cada término se gestiona en UNA sola fila: nombre (renombra al salir), portada
 * (imagen editable, se guarda al elegirla) y — solo en Destinos — "Destacado en
 * home". Todo se guarda al momento vía AJAX:
 *   emt_panel_term_add / _rename / _delete / _portada / _destacado
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Fila editable de un término: nombre + contador + portada (+ destacado en destinos). */
if ( ! function_exists( 'emt_render_term_row' ) ) {
    function emt_render_term_row( $t, $with_destacado = false ) {
        $count  = (int) $t->count;
        $img    = function_exists( 'get_field' ) ? get_field( 'imagen_destino', $t ) : null;
        $img_id = is_array( $img ) ? (int) ( $img['ID'] ?? $img['id'] ?? 0 ) : 0;
        $thumb  = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
        $dest   = $with_destacado ? ! empty( get_field( 'destacado', $t ) ) : false;
        ?>
        <div class="emt-term-row" data-term-id="<?php echo (int) $t->term_id; ?>">
            <input type="text" class="emt-term-row__name" value="<?php echo esc_attr( $t->name ); ?>" data-term-name aria-label="Nombre" />
            <span class="emt-term-row__count"><?php echo $count; ?> tour<?php echo ( $count === 1 ) ? '' : 's'; ?></span>

            <div class="emt-term-row__portada emt-image emt-image--xs" data-term-image>
                <div class="emt-image__preview" data-image-preview>
                    <?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt="" /><?php endif; ?>
                </div>
                <input type="hidden" value="<?php echo $img_id; ?>" data-image-input />
                <button type="button" class="emt-panel__btn emt-panel__btn--sm" data-term-portada-add>Portada</button>
                <button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-term-portada-remove<?php echo $img_id ? '' : ' style="display:none;"'; ?>>Quitar</button>
            </div>

            <?php if ( $with_destacado ) : ?>
                <label class="emt-term-row__dest">
                    <input type="checkbox" data-term-destacado value="1"<?php checked( $dest ); ?> />
                    <span>Destacado en home</span>
                </label>
            <?php endif; ?>

            <span class="emt-term-row__msg" data-term-msg></span>
            <button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-term-delete>Eliminar</button>
        </div>
        <?php
    }
}

/** Gestor completo de una taxonomía (crear + lista editable). */
if ( ! function_exists( 'emt_render_term_manager' ) ) {
    function emt_render_term_manager( $tax, $titulo, $ayuda, $ph, $with_destacado = false ) {
        $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
        if ( is_wp_error( $terms ) ) { $terms = array(); }
        ?>
        <div class="emt-panel-form__section emt-tax-manager" data-tax="<?php echo esc_attr( $tax ); ?>" data-has-destacado="<?php echo $with_destacado ? '1' : '0'; ?>">
            <h2><?php echo esc_html( $titulo ); ?></h2>
            <p class="emt-field__help" style="margin-bottom:var(--emt-spacing-md);"><?php echo esc_html( $ayuda ); ?> La portada se usa en el mega-menú y en la página del término.</p>
            <div class="emt-tax-add">
                <input type="text" class="emt-tax-add__input" placeholder="<?php echo esc_attr( $ph ); ?>" data-term-new aria-label="Nombre nuevo" />
                <button type="button" class="emt-panel__btn emt-panel__btn--primary" data-term-add>Agregar</button>
            </div>
            <div class="emt-tax-list" data-tax-list>
                <p class="emt-panel__muted" data-tax-empty<?php echo empty( $terms ) ? '' : ' style="display:none;"'; ?>>Aún no hay. Agrega el primero arriba.</p>
                <?php foreach ( $terms as $t ) { emt_render_term_row( $t, $with_destacado ); } ?>
            </div>
        </div>
        <?php
    }
}
?>
<div class="emt-panel__head">
    <div>
        <h1>Clasificación</h1>
        <p class="emt-panel__head-sub">Crea y organiza los <strong>destinos</strong>, <strong>categorías</strong> y <strong>experiencias</strong> con los que etiquetas tus tours. Cada cambio (nombre, portada, destacado) se guarda al momento.</p>
    </div>
</div>

<?php
emt_render_term_manager(
    'tour_destino',
    'Destinos',
    'El lugar del tour (Jalisco, Chiapas, Oaxaca…). Un tour puede tener varios destinos si el recorrido cruza estados. Marca "Destacado en home" para mostrarlo en "Destinos imperdibles" del inicio (hasta 5).',
    'Nombre del destino',
    true // destinos: incluye "Destacado en home"
);
emt_render_term_manager(
    'tour_categoria',
    'Categorías',
    'El tipo de tour (Cultural, Gastronómico, Aventura, Ecoturismo, Sol y playa…). Un tour puede tener hasta 2; la "Categoría principal" se elige en la ficha del tour.',
    'Nombre de la categoría'
);
emt_render_term_manager(
    'tour_experiencia',
    'Experiencias',
    'El tema u ocasión, transversal a las categorías (Pueblos Mágicos, Ruta del Tequila, Día de Muertos…). Un tour puede tener varias.',
    'Nombre de la experiencia'
);
