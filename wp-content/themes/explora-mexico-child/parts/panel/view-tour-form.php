<?php
/**
 * Panel — formulario de alta/edición de tour (P3). Todos los campos ACF.
 * Guardado vía AJAX (emt_panel_save_tour) con nonce + capability + sanitización.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$arg     = sanitize_key( get_query_var( 'emt_arg' ) );
$post_id = ( $arg === 'editar' ) ? (int) get_query_var( 'emt_id' ) : 0;
$editing = $post_id && get_post_type( $post_id ) === 'tour';
if ( $arg === 'editar' && ! $editing ) {
    echo '<div class="emt-panel__head"><h1>Tour no encontrado</h1></div>';
    echo '<p><a href="' . esc_url( emt_panel_url( 'tours/' ) ) . '">Volver a la lista</a></p>';
    return;
}

// Valores actuales (o vacío en alta).
$g = function ( $field, $default = '' ) use ( $post_id ) {
    if ( ! $post_id ) { return $default; }
    $v = get_field( $field, $post_id );
    return ( $v === null || $v === false ) ? $default : $v;
};
$titulo   = $editing ? get_the_title( $post_id ) : '';
$descrip  = $editing ? get_post_field( 'post_content', $post_id ) : '';
$idiomas  = (array) $g( 'idiomas', array() );
// El campo 'galeria' (ACF gallery) devuelve arrays de imagen completos según su
// return_format; normalizamos a IDs para que la galería se re-renderice al editar.
$galeria  = array_values( array_filter( array_map(
    function ( $item ) { return is_array( $item ) ? (int) ( $item['ID'] ?? $item['id'] ?? 0 ) : (int) $item; },
    (array) $g( 'galeria', array() )
) ) );
$inc      = (array) $g( 'incluye', array() );
$noinc    = (array) $g( 'no_incluye', array() );
$itin     = (array) $g( 'itinerario', array() );

$sel_destino = $editing ? ( ( $tt = get_the_terms( $post_id, 'tour_destino' ) ) && ! is_wp_error( $tt ) ? $tt[0]->term_id : 0 ) : 0;
$sel_cat     = $editing ? ( ( $tc = get_the_terms( $post_id, 'tour_categoria' ) ) && ! is_wp_error( $tc ) ? $tc[0]->term_id : 0 ) : 0;
$sel_exp     = $editing ? wp_list_pluck( (array) get_the_terms( $post_id, 'tour_experiencia' ) ?: array(), 'term_id' ) : array();

$tx_dest = get_terms( array( 'taxonomy' => 'tour_destino', 'hide_empty' => false ) );
$tx_cat  = get_terms( array( 'taxonomy' => 'tour_categoria', 'hide_empty' => false ) );
$tx_exp  = get_terms( array( 'taxonomy' => 'tour_experiencia', 'hide_empty' => false ) );
$dif     = array( 'facil' => 'Fácil', 'moderada' => 'Moderada', 'alta' => 'Alta' );
$idi_opts= array( 'es' => 'Español', 'en' => 'Inglés', 'fr' => 'Francés', 'otros' => 'Otros' );
$inc_ico = array( 'bus' => 'Bus', 'comida' => 'Comida', 'guia' => 'Guía', 'entrada' => 'Entrada', 'equipo' => 'Equipo', 'hospedaje' => 'Hospedaje', 'foto' => 'Foto', 'otro' => 'Otro' );
$itin_ico= array( 'salida' => 'Salida', 'parada' => 'Parada', 'comida' => 'Comida', 'actividad' => 'Actividad', 'hospedaje' => 'Hospedaje', 'regreso' => 'Regreso' );
?>
<div class="emt-panel__head">
    <div>
        <h1><?php echo $editing ? 'Editar tour' : 'Nuevo tour'; ?></h1>
        <p class="emt-panel__head-sub"><a href="<?php echo esc_url( emt_panel_url( 'tours/' ) ); ?>">&larr; Volver a la lista</a></p>
    </div>
</div>

<form id="emt-tour-form" data-emt-form data-ajax-action="emt_panel_save_tour" data-required-draft="titulo" data-required-publish="titulo,duracion_texto" data-post-id="<?php echo (int) $post_id; ?>" novalidate>

    <div class="emt-panel-form__section">
        <h2>Datos básicos</h2>
        <div class="emt-field" data-field="titulo">
            <label>Título <span class="emt-req">*</span></label>
            <input type="text" name="titulo" value="<?php echo esc_attr( $titulo ); ?>" required />
            <div class="emt-field__err-msg"></div>
        </div>
        <div class="emt-field"><label>Título (EN)</label><input type="text" name="titulo_en" value="<?php echo esc_attr( $g( 'titulo_en' ) ); ?>" /></div>
        <div class="emt-field"><label>Descripción</label><textarea name="descripcion"><?php echo esc_textarea( $descrip ); ?></textarea></div>
        <div class="emt-field"><label>Descripción (EN)</label><textarea name="descripcion_en"><?php echo esc_textarea( $g( 'descripcion_en' ) ); ?></textarea></div>
        <div class="emt-grid-3">
            <div class="emt-field"><label>Destino</label>
                <select name="destino"><option value="">—</option>
                    <?php foreach ( $tx_dest as $t ) : ?><option value="<?php echo (int) $t->term_id; ?>" <?php selected( $sel_destino, $t->term_id ); ?>><?php echo esc_html( $t->name ); ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="emt-field"><label>Categoría</label>
                <select name="categoria"><option value="">—</option>
                    <?php foreach ( $tx_cat as $t ) : ?><option value="<?php echo (int) $t->term_id; ?>" <?php selected( $sel_cat, $t->term_id ); ?>><?php echo esc_html( $t->name ); ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="emt-field"><label>Experiencias</label>
            <div class="emt-checks">
                <?php foreach ( $tx_exp as $t ) : ?>
                    <label><input type="checkbox" name="experiencias[]" value="<?php echo (int) $t->term_id; ?>" <?php checked( in_array( $t->term_id, $sel_exp, true ) ); ?> /> <?php echo esc_html( $t->name ); ?></label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Precios por ocupación</h2>
        <div class="emt-precios-grid">
            <span class="emt-precios-grid__h">Ocupación</span><span class="emt-precios-grid__h">Precio (MXN)</span><span class="emt-precios-grid__h">Disponibilidad</span>
            <?php
            $ocup = array( 'dbl' => 'Doble', 'tpl' => 'Triple', 'cuadpl' => 'Cuádruple', 'menor' => 'Menor (6-12)' );
            foreach ( $ocup as $k => $lbl ) : ?>
                <label style="font-size:14px;"><?php echo esc_html( $lbl ); ?></label>
                <input type="number" name="precio_<?php echo $k; ?>" value="<?php echo esc_attr( $g( 'precio_' . $k ) ); ?>" min="0" step="1" />
                <input type="number" name="disp_<?php echo $k; ?>" value="<?php echo esc_attr( $g( 'disp_' . $k ) ); ?>" min="0" step="1" placeholder="asientos" />
            <?php endforeach; ?>
        </div>
        <div class="emt-grid-2" style="margin-top:16px;">
            <div class="emt-field"><label>Precio desde (MXN)</label><input type="number" name="precio_desde" value="<?php echo esc_attr( $g( 'precio_desde' ) ); ?>" min="0" /><div class="emt-field__help">Déjalo vacío: se autocalcula como el menor de los 4.</div></div>
            <div class="emt-field"><label>Fecha del viaje</label><input type="text" name="fecha_viaje" value="<?php echo esc_attr( $g( 'fecha_viaje' ) ); ?>" placeholder="30 octubre – 1 noviembre 2026" /></div>
        </div>
        <div class="emt-field"><label>Nota de precios</label><textarea name="precio_nota" placeholder="Máximo 4 por habitación incluyendo menores."><?php echo esc_textarea( $g( 'precio_nota' ) ); ?></textarea></div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Precios por vehículo</h2>
        <div class="emt-field__help" style="margin-bottom:12px;">Modelo alternativo (p. ej. tours de Tequila): precio POR PERSONA según capacidad del grupo y vehículo. Usa este O el de ocupación, no ambos. Deja el precio vacío para mostrar "Consultar".</div>
        <div id="emt-pv" data-repeater="precios_vehiculo">
            <?php $pv = (array) $g( 'precios_vehiculo', array() ); $vi = 0; foreach ( $pv as $row ) :
                $cap = trim( (string) ( $row['capacidad'] ?? '' ) );
                $veh = trim( (string) ( $row['vehiculo'] ?? '' ) );
                if ( $cap === '' && $veh === '' ) { continue; } ?>
                <div class="emt-repeater__item" data-row>
                    <div class="emt-repeater__item-head"><span></span><button type="button" class="emt-repeater__remove" data-remove>Quitar</button></div>
                    <div class="emt-grid-3">
                        <div class="emt-field"><label>Capacidad</label><input type="text" name="precios_vehiculo[<?php echo $vi; ?>][capacidad]" value="<?php echo esc_attr( $cap ); ?>" placeholder="2 pax" /></div>
                        <div class="emt-field"><label>Vehículo</label><input type="text" name="precios_vehiculo[<?php echo $vi; ?>][vehiculo]" value="<?php echo esc_attr( $veh ); ?>" placeholder="Sedán" /></div>
                        <div class="emt-field"><label>Precio p/p (MXN)</label><input type="number" name="precios_vehiculo[<?php echo $vi; ?>][precio]" value="<?php echo esc_attr( $row['precio'] ?? '' ); ?>" min="0" step="1" placeholder="vacío = Consultar" /></div>
                    </div>
                </div>
            <?php $vi++; endforeach; ?>
        </div>
        <button type="button" class="emt-panel__btn" data-repeater-add="precios_vehiculo">+ Agregar tramo</button>
    </div>

    <div class="emt-panel-form__section">
        <h2>Logística</h2>
        <div class="emt-grid-2">
            <div class="emt-field"><label>Duración (texto) <span class="emt-req">*</span></label><input type="text" name="duracion_texto" value="<?php echo esc_attr( $g( 'duracion_texto' ) ); ?>" placeholder="3 días / 2 noches" required /><div class="emt-field__err-msg"></div></div>
            <div class="emt-field"><label>Duración (horas)</label><input type="number" name="duracion_horas" value="<?php echo esc_attr( $g( 'duracion_horas' ) ); ?>" min="0" /></div>
        </div>
        <div class="emt-grid-2">
            <div class="emt-field"><label>Dificultad</label>
                <select name="dificultad"><?php foreach ( $dif as $k => $v ) : ?><option value="<?php echo $k; ?>" <?php selected( $g( 'dificultad', 'facil' ), $k ); ?>><?php echo esc_html( $v ); ?></option><?php endforeach; ?></select>
            </div>
            <div class="emt-field"><label>Punto de salida</label><input type="text" name="punto_salida" value="<?php echo esc_attr( $g( 'punto_salida' ) ); ?>" /></div>
        </div>
        <div class="emt-field"><label>Idiomas</label>
            <div class="emt-checks"><?php foreach ( $idi_opts as $k => $v ) : ?><label><input type="checkbox" name="idiomas[]" value="<?php echo $k; ?>" <?php checked( in_array( $k, $idiomas, true ) ); ?> /> <?php echo esc_html( $v ); ?></label><?php endforeach; ?></div>
        </div>
        <div class="emt-field"><label>Indicadores</label>
            <div class="emt-checks">
                <label><input type="checkbox" name="salida_garantizada" value="1" <?php checked( $g( 'salida_garantizada' ) ); ?> /> Salida garantizada</label>
                <label><input type="checkbox" name="pickup_hotel" value="1" <?php checked( $g( 'pickup_hotel' ) ); ?> /> Pickup en hotel</label>
                <label><input type="checkbox" name="destacado" value="1" <?php checked( $g( 'destacado' ) ); ?> /> Destacado (home)</label>
            </div>
        </div>
        <div class="emt-field"><label>Orden en imperdibles del home — menor = primero (el menor va en grande)</label><input type="number" name="orden_destacado" value="<?php echo esc_attr( $g( 'orden_destacado', 99 ) ); ?>" min="0" step="1" /></div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Itinerario</h2>
        <div id="emt-itin" data-repeater="itinerario">
            <?php
            $itin_rows = $itin ? $itin : array();
            $ri = 0;
            foreach ( $itin_rows as $row ) : ?>
                <div class="emt-repeater__item" data-row>
                    <div class="emt-repeater__item-head"><strong>Día/parada</strong><button type="button" class="emt-repeater__remove" data-remove>Quitar</button></div>
                    <div class="emt-grid-3">
                        <div class="emt-field"><label>Día</label><input type="number" name="itinerario[<?php echo $ri; ?>][dia]" value="<?php echo esc_attr( $row['dia'] ?? '' ); ?>" /></div>
                        <div class="emt-field"><label>Hora</label><input type="text" name="itinerario[<?php echo $ri; ?>][hora]" value="<?php echo esc_attr( $row['hora'] ?? '' ); ?>" /></div>
                        <div class="emt-field"><label>Icono</label><select name="itinerario[<?php echo $ri; ?>][icono]"><?php foreach ( $itin_ico as $k => $v ) : ?><option value="<?php echo $k; ?>" <?php selected( $row['icono'] ?? '', $k ); ?>><?php echo esc_html( $v ); ?></option><?php endforeach; ?></select></div>
                    </div>
                    <div class="emt-field"><label>Título</label><input type="text" name="itinerario[<?php echo $ri; ?>][titulo]" value="<?php echo esc_attr( $row['titulo'] ?? '' ); ?>" /></div>
                    <div class="emt-field"><label>Descripción</label><textarea name="itinerario[<?php echo $ri; ?>][descripcion]"><?php echo esc_textarea( $row['descripcion'] ?? '' ); ?></textarea></div>
                </div>
            <?php $ri++; endforeach; ?>
        </div>
        <button type="button" class="emt-panel__btn" data-repeater-add="itinerario">+ Agregar día</button>
    </div>

    <div class="emt-panel-form__section">
        <h2>Incluye / No incluye</h2>
        <div class="emt-grid-2">
            <div>
                <strong>Incluye</strong>
                <div id="emt-inc" data-repeater="incluye">
                    <?php $ci = 0; foreach ( $inc as $row ) : ?>
                        <div class="emt-repeater__item" data-row><div class="emt-repeater__item-head"><span></span><button type="button" class="emt-repeater__remove" data-remove>Quitar</button></div>
                            <input type="text" name="incluye[<?php echo $ci; ?>][texto]" value="<?php echo esc_attr( $row['texto'] ?? '' ); ?>" placeholder="Transporte redondo" />
                            <input type="hidden" name="incluye[<?php echo $ci; ?>][icono]" value="otro" />
                        </div>
                    <?php $ci++; endforeach; ?>
                </div>
                <button type="button" class="emt-panel__btn emt-panel__btn--sm" data-repeater-add="incluye">+ Agregar</button>
            </div>
            <div>
                <strong>No incluye</strong>
                <div id="emt-noinc" data-repeater="no_incluye">
                    <?php $ni = 0; foreach ( $noinc as $row ) : ?>
                        <div class="emt-repeater__item" data-row><div class="emt-repeater__item-head"><span></span><button type="button" class="emt-repeater__remove" data-remove>Quitar</button></div>
                            <input type="text" name="no_incluye[<?php echo $ni; ?>][texto]" value="<?php echo esc_attr( $row['texto'] ?? '' ); ?>" placeholder="Propinas" />
                            <input type="hidden" name="no_incluye[<?php echo $ni; ?>][icono]" value="otro" />
                        </div>
                    <?php $ni++; endforeach; ?>
                </div>
                <button type="button" class="emt-panel__btn emt-panel__btn--sm" data-repeater-add="no_incluye">+ Agregar</button>
            </div>
        </div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Galería</h2>
        <div class="emt-gallery" data-gallery>
            <div class="emt-gallery__items" data-gallery-items>
                <?php foreach ( $galeria as $att_id ) : $img = wp_get_attachment_image_url( $att_id, 'thumbnail' ); if ( ! $img ) { continue; } ?>
                    <div class="emt-gallery__item" data-att="<?php echo (int) $att_id; ?>">
                        <img src="<?php echo esc_url( $img ); ?>" alt="" />
                        <button type="button" data-remove-img>&times;</button>
                        <input type="hidden" name="galeria[]" value="<?php echo (int) $att_id; ?>" />
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="emt-panel__btn" data-gallery-add>Subir / elegir fotos</button>
            <div class="emt-field__help">La primera foto será la imagen destacada.</div>
        </div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Políticas, mapa, reserva y SEO</h2>
        <div class="emt-field"><label>Política de cancelación</label><textarea name="politica_cancelacion"><?php echo esc_textarea( $g( 'politica_cancelacion' ) ); ?></textarea></div>
        <div class="emt-grid-2">
            <div class="emt-field"><label>Mapa (embed URL)</label><input type="url" name="mapa_embed" value="<?php echo esc_attr( $g( 'mapa_embed' ) ); ?>" /></div>
            <div class="emt-field"><label>URL de reserva (Peek)</label><input type="url" name="peek_url" value="<?php echo esc_attr( $g( 'peek_url' ) ); ?>" placeholder="#" /></div>
        </div>
        <div class="emt-grid-2">
            <div class="emt-field"><label>SEO título (override)</label><input type="text" name="seo_title_override" value="<?php echo esc_attr( $g( 'seo_title_override' ) ); ?>" /></div>
            <div class="emt-field"><label>SEO descripción (override)</label><input type="text" name="seo_desc_override" value="<?php echo esc_attr( $g( 'seo_desc_override' ) ); ?>" /></div>
        </div>
    </div>

    <div class="emt-panel-form__bar">
        <span class="emt-panel-form__msg" data-form-msg></span>
        <button type="submit" class="emt-panel__btn" data-save="draft">Guardar borrador</button>
        <button type="submit" class="emt-panel__btn emt-panel__btn--primary" data-save="publish">Publicar</button>
    </div>
</form>

<?php
// Plantillas de filas para el repeater (clonadas por JS).
?>
<template id="emt-tpl-itinerario">
    <div class="emt-repeater__item" data-row>
        <div class="emt-repeater__item-head"><strong>Día/parada</strong><button type="button" class="emt-repeater__remove" data-remove>Quitar</button></div>
        <div class="emt-grid-3">
            <div class="emt-field"><label>Día</label><input type="number" data-name="itinerario|__i__|dia" /></div>
            <div class="emt-field"><label>Hora</label><input type="text" data-name="itinerario|__i__|hora" /></div>
            <div class="emt-field"><label>Icono</label><select data-name="itinerario|__i__|icono"><?php foreach ( $itin_ico as $k => $v ) : ?><option value="<?php echo $k; ?>"><?php echo esc_html( $v ); ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="emt-field"><label>Título</label><input type="text" data-name="itinerario|__i__|titulo" /></div>
        <div class="emt-field"><label>Descripción</label><textarea data-name="itinerario|__i__|descripcion"></textarea></div>
    </div>
</template>
<template id="emt-tpl-precios_vehiculo">
    <div class="emt-repeater__item" data-row>
        <div class="emt-repeater__item-head"><span></span><button type="button" class="emt-repeater__remove" data-remove>Quitar</button></div>
        <div class="emt-grid-3">
            <div class="emt-field"><label>Capacidad</label><input type="text" data-name="precios_vehiculo|__i__|capacidad" placeholder="2 pax" /></div>
            <div class="emt-field"><label>Vehículo</label><input type="text" data-name="precios_vehiculo|__i__|vehiculo" placeholder="Sedán" /></div>
            <div class="emt-field"><label>Precio p/p (MXN)</label><input type="number" data-name="precios_vehiculo|__i__|precio" min="0" step="1" placeholder="vacío = Consultar" /></div>
        </div>
    </div>
</template>
<template id="emt-tpl-incluye">
    <div class="emt-repeater__item" data-row><div class="emt-repeater__item-head"><span></span><button type="button" class="emt-repeater__remove" data-remove>Quitar</button></div>
        <input type="text" data-name="incluye|__i__|texto" placeholder="Ítem incluido" />
        <input type="hidden" data-name="incluye|__i__|icono" value="otro" />
    </div>
</template>
<template id="emt-tpl-no_incluye">
    <div class="emt-repeater__item" data-row><div class="emt-repeater__item-head"><span></span><button type="button" class="emt-repeater__remove" data-remove>Quitar</button></div>
        <input type="text" data-name="no_incluye|__i__|texto" placeholder="Ítem no incluido" />
        <input type="hidden" data-name="no_incluye|__i__|icono" value="otro" />
    </div>
</template>
