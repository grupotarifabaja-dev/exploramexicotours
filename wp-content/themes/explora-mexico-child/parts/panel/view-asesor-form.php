<?php
/**
 * Panel — formulario de alta/edición de asesor (P4).
 * Campos: nombre (título), puesto(_en), bio_corta(_en), foto (imagen destacada),
 * teléfono, whatsapp, email, linkedin, instagram, idiomas y especialidades
 * (taxonomías por nombre), activo, orden.
 * Guardado vía AJAX (emt_panel_save_asesor) con nonce + capability + sanitización.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$arg     = sanitize_key( get_query_var( 'emt_arg' ) );
$post_id = ( $arg === 'editar' ) ? (int) get_query_var( 'emt_id' ) : 0;
$editing = $post_id && get_post_type( $post_id ) === 'asesor';
if ( $arg === 'editar' && ! $editing ) {
    echo '<div class="emt-panel__head"><h1>Asesor no encontrado</h1></div>';
    echo '<p><a href="' . esc_url( emt_panel_url( 'asesores/' ) ) . '">Volver a la lista</a></p>';
    return;
}

// Valores actuales (o vacío en alta).
$g = function ( $field, $default = '' ) use ( $post_id ) {
    if ( ! $post_id ) { return $default; }
    $v = get_field( $field, $post_id );
    return ( $v === null || $v === false ) ? $default : $v;
};
$nombre = $editing ? get_the_title( $post_id ) : '';
$activo = $editing ? (bool) $g( 'activo', true ) : true;

// Foto = imagen destacada.
$foto_id    = $editing ? (int) get_post_thumbnail_id( $post_id ) : 0;
$foto_thumb = $foto_id ? wp_get_attachment_image_url( $foto_id, 'thumbnail' ) : '';

// Taxonomías (tags) como texto separado por coma.
$tax_csv = function ( $tax ) use ( $post_id, $editing ) {
    if ( ! $editing ) { return ''; }
    $terms = get_the_terms( $post_id, $tax );
    return ( $terms && ! is_wp_error( $terms ) ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '';
};
$idiomas_csv = $tax_csv( 'asesor_idioma' );
$espec_csv   = $tax_csv( 'asesor_especialidad' );
?>
<div class="emt-panel__head">
    <div>
        <h1><?php echo $editing ? 'Editar asesor' : 'Nuevo asesor'; ?></h1>
        <p class="emt-panel__head-sub"><a href="<?php echo esc_url( emt_panel_url( 'asesores/' ) ); ?>">&larr; Volver a la lista</a></p>
    </div>
</div>

<form id="emt-asesor-form" data-emt-form data-ajax-action="emt_panel_save_asesor" data-required-draft="titulo" data-required-publish="titulo,puesto,bio_corta,telefono,whatsapp,email" data-post-id="<?php echo (int) $post_id; ?>" novalidate>

    <div class="emt-panel-form__section">
        <h2>Datos básicos</h2>
        <div class="emt-grid-2">
            <div class="emt-field" data-field="titulo">
                <label>Nombre <span class="emt-req">*</span></label>
                <input type="text" name="titulo" value="<?php echo esc_attr( $nombre ); ?>" required />
                <div class="emt-field__err-msg"></div>
            </div>
            <div class="emt-field"><label>Puesto <span class="emt-req">*</span></label><input type="text" name="puesto" value="<?php echo esc_attr( $g( 'puesto' ) ); ?>" placeholder="Ventas Corporativas" required /><div class="emt-field__err-msg"></div></div>
        </div>
        <div class="emt-field"><label>Puesto (EN)</label><input type="text" name="puesto_en" value="<?php echo esc_attr( $g( 'puesto_en' ) ); ?>" /></div>
        <div class="emt-field"><label>Bio corta <span class="emt-req">*</span></label><textarea name="bio_corta" placeholder="3-4 líneas presentando al asesor." required><?php echo esc_textarea( $g( 'bio_corta' ) ); ?></textarea><div class="emt-field__err-msg"></div></div>
        <div class="emt-field"><label>Bio corta (EN)</label><textarea name="bio_corta_en"><?php echo esc_textarea( $g( 'bio_corta_en' ) ); ?></textarea></div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Foto</h2>
        <div class="emt-image" data-image>
            <div class="emt-image__preview" data-image-preview>
                <?php if ( $foto_thumb ) : ?><img src="<?php echo esc_url( $foto_thumb ); ?>" alt="" /><?php endif; ?>
            </div>
            <input type="hidden" name="foto" value="<?php echo (int) $foto_id; ?>" data-image-input />
            <div class="emt-image__actions">
                <button type="button" class="emt-panel__btn" data-image-add>Subir / elegir foto</button>
                <button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-image-remove<?php echo $foto_id ? '' : ' style="display:none;"'; ?>>Quitar</button>
            </div>
            <div class="emt-field__help">Se usará como imagen del asesor.</div>
        </div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Contacto</h2>
        <div class="emt-grid-2">
            <div class="emt-field"><label>Teléfono <span class="emt-req">*</span></label><input type="text" name="telefono" value="<?php echo esc_attr( $g( 'telefono' ) ); ?>" placeholder="+52 33 1048 0670" required /><div class="emt-field__err-msg"></div></div>
            <div class="emt-field"><label>WhatsApp <span class="emt-req">*</span></label><input type="text" name="whatsapp" value="<?php echo esc_attr( $g( 'whatsapp' ) ); ?>" placeholder="523310480670" required /><div class="emt-field__help">Solo dígitos, con lada de país.</div><div class="emt-field__err-msg"></div></div>
        </div>
        <div class="emt-grid-2">
            <div class="emt-field"><label>Email <span class="emt-req">*</span></label><input type="email" name="email" value="<?php echo esc_attr( $g( 'email' ) ); ?>" required /><div class="emt-field__err-msg"></div></div>
            <div class="emt-field"><label>Orden</label><input type="number" name="orden" value="<?php echo esc_attr( $g( 'orden' ) ); ?>" min="0" /><div class="emt-field__help">Menor = primero en el directorio.</div></div>
        </div>
        <div class="emt-grid-2">
            <div class="emt-field"><label>LinkedIn</label><input type="url" name="linkedin" value="<?php echo esc_attr( $g( 'linkedin' ) ); ?>" placeholder="https://linkedin.com/in/…" /></div>
            <div class="emt-field"><label>Instagram</label><input type="url" name="instagram" value="<?php echo esc_attr( $g( 'instagram' ) ); ?>" placeholder="https://instagram.com/…" /></div>
        </div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Idiomas y especialidades</h2>
        <div class="emt-grid-2">
            <div class="emt-field"><label>Idiomas</label><input type="text" name="idiomas" value="<?php echo esc_attr( $idiomas_csv ); ?>" placeholder="Español, Inglés, Francés" /><div class="emt-field__help">Separa con comas.</div></div>
            <div class="emt-field"><label>Especialidades</label><input type="text" name="especialidades" value="<?php echo esc_attr( $espec_csv ); ?>" placeholder="Bodas, Grupos, Lujo" /><div class="emt-field__help">Separa con comas.</div></div>
        </div>
        <div class="emt-field"><label>Visibilidad</label>
            <div class="emt-checks">
                <label><input type="checkbox" name="activo" value="1" <?php checked( $activo ); ?> /> Activo (visible en el directorio)</label>
            </div>
        </div>
    </div>

    <div class="emt-panel-form__bar">
        <span class="emt-panel-form__msg" data-form-msg></span>
        <button type="submit" class="emt-panel__btn" data-save="draft">Guardar borrador</button>
        <button type="submit" class="emt-panel__btn emt-panel__btn--primary" data-save="publish">Publicar</button>
    </div>
</form>
