<?php
/**
 * Panel — sección Destinos.
 * El cliente elige qué destinos aparecen en la sección "Destinos imperdibles"
 * del inicio ("Destacado en home") y configura la portada de cada destino
 * (campo de término imagen_destino). Guardado vía AJAX (emt_panel_save_destinos)
 * con nonce + capability + sanitización.
 *
 * Los destinos (términos de tour_destino) se crean/nombran desde Tours; aquí
 * solo se marcan y se les pone portada.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

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
        <h1>Destinos</h1>
        <p class="emt-panel__head-sub">Elige qué destinos se muestran en el inicio y ponles su portada. El nombre de cada destino se gestiona desde los tours.</p>
    </div>
</div>

<?php if ( empty( $destinos ) ) : ?>
    <div class="emt-empty">
        <p>Aún no hay destinos. Se crean al asignar un destino a un tour.</p>
        <a class="emt-panel__btn emt-panel__btn--primary" href="<?php echo esc_url( emt_panel_url( 'tours/nuevo/' ) ); ?>">Crear un tour</a>
    </div>
<?php else : ?>
<form id="emt-destinos-form" data-emt-form data-ajax-action="emt_panel_save_destinos" data-required-draft="" data-required-publish="">

    <div class="emt-panel-form__section">
        <h2>Destinos imperdibles del inicio</h2>
        <p class="emt-field__help" style="margin-bottom:var(--emt-spacing-md);">Marca "Destacado en home" en los destinos que quieras mostrar (hasta 5). Si no marcas ninguno, el inicio mostrará automáticamente los destinos con más tours. Tamaño de portada sugerido: <strong>1200&times;1600 px</strong> (vertical 3:4).</p>

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
    </div>

    <div class="emt-panel-form__bar">
        <span class="emt-panel-form__msg" data-form-msg></span>
        <button type="submit" class="emt-panel__btn emt-panel__btn--primary" data-save="save">Guardar cambios</button>
    </div>
</form>
<?php endif; ?>
