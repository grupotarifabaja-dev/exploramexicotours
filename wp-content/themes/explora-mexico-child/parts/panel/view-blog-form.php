<?php
/**
 * Panel — formulario de alta/edición de entrada de blog.
 * Campos: título, categoría (una), etiquetas (CSV), extracto, imagen destacada
 * (wp.media) y contenido (HTML). Guardado vía AJAX (emt_panel_save_post).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$arg     = sanitize_key( get_query_var( 'emt_arg' ) );
$post_id = ( $arg === 'editar' ) ? (int) get_query_var( 'emt_id' ) : 0;
$editing = $post_id && get_post_type( $post_id ) === 'post';
if ( $arg === 'editar' && ! $editing ) {
    echo '<div class="emt-panel__head"><h1>Artículo no encontrado</h1></div>';
    echo '<p><a href="' . esc_url( emt_panel_url( 'blog/' ) ) . '">Volver a la lista</a></p>';
    return;
}

$titulo    = $editing ? get_the_title( $post_id ) : '';
$contenido = $editing ? get_post_field( 'post_content', $post_id ) : '';
$extracto  = $editing ? get_post_field( 'post_excerpt', $post_id ) : '';

$img_id    = $editing ? (int) get_post_thumbnail_id( $post_id ) : 0;
$img_thumb = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';

$cats      = $editing ? get_the_category( $post_id ) : array();
$cat_name  = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
$tags      = $editing ? get_the_terms( $post_id, 'post_tag' ) : array();
$tags_csv  = ( $tags && ! is_wp_error( $tags ) ) ? implode( ', ', wp_list_pluck( $tags, 'name' ) ) : '';

// Categorías existentes para el datalist de ayuda.
$all_cats  = get_categories( array( 'hide_empty' => false ) );
?>
<div class="emt-panel__head">
    <div>
        <h1><?php echo $editing ? 'Editar artículo' : 'Nuevo artículo'; ?></h1>
        <p class="emt-panel__head-sub"><a href="<?php echo esc_url( emt_panel_url( 'blog/' ) ); ?>">&larr; Volver a la lista</a></p>
    </div>
</div>

<form id="emt-blog-form" data-emt-form data-ajax-action="emt_panel_save_post" data-required-draft="titulo" data-required-publish="titulo,contenido" data-post-id="<?php echo (int) $post_id; ?>" novalidate>

    <div class="emt-panel-form__section">
        <h2>Artículo</h2>
        <div class="emt-field" data-field="titulo">
            <label>Título <span class="emt-req">*</span></label>
            <input type="text" name="titulo" value="<?php echo esc_attr( $titulo ); ?>" placeholder="Ej. Ruta del Tequila: guía de un día" required />
            <div class="emt-field__err-msg"></div>
        </div>
        <div class="emt-grid-2">
            <div class="emt-field">
                <label>Categoría</label>
                <input type="text" name="categoria" value="<?php echo esc_attr( $cat_name ); ?>" list="emt-cat-list" placeholder="Ej. Destinos" />
                <datalist id="emt-cat-list">
                    <?php foreach ( $all_cats as $c ) : ?><option value="<?php echo esc_attr( $c->name ); ?>"></option><?php endforeach; ?>
                </datalist>
                <div class="emt-field__help">Si no existe, se crea automáticamente.</div>
            </div>
            <div class="emt-field">
                <label>Etiquetas</label>
                <input type="text" name="etiquetas" value="<?php echo esc_attr( $tags_csv ); ?>" placeholder="Tequila, Jalisco, Pueblos Mágicos" />
                <div class="emt-field__help">Separa con comas.</div>
            </div>
        </div>
        <div class="emt-field">
            <label>Extracto</label>
            <textarea name="extracto" rows="2" placeholder="Resumen breve que aparece en el listado y al compartir."><?php echo esc_textarea( $extracto ); ?></textarea>
        </div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Imagen destacada</h2>
        <div class="emt-image" data-image>
            <div class="emt-image__preview" data-image-preview>
                <?php if ( $img_thumb ) : ?><img src="<?php echo esc_url( $img_thumb ); ?>" alt="" /><?php endif; ?>
            </div>
            <input type="hidden" name="imagen" value="<?php echo (int) $img_id; ?>" data-image-input />
            <div class="emt-image__actions">
                <button type="button" class="emt-panel__btn" data-image-add>Subir / elegir imagen</button>
                <button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-image-remove<?php echo $img_id ? '' : ' style="display:none;"'; ?>>Quitar</button>
            </div>
            <div class="emt-field__help">Se usa como portada del artículo y en la tarjeta del listado.</div>
        </div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Contenido</h2>
        <div class="emt-field" data-field="contenido">
            <label>Cuerpo del artículo <span class="emt-req">*</span></label>
            <?php
            wp_editor( $contenido, 'contenido', array(
                'textarea_name' => 'contenido',
                'textarea_rows' => 16,
                'media_buttons' => true,   // botón "Añadir objeto" para insertar imágenes
                'teeny'         => false,
                'quicktags'     => true,
                'tinymce'       => array(
                    'toolbar1'      => 'formatselect,bold,italic,bullist,numlist,blockquote,link,unlink,alignleft,aligncenter,undo,redo',
                    'block_formats' => 'Párrafo=p;Subtítulo=h2;Subtítulo menor=h3',
                ),
            ) );
            ?>
            <div class="emt-field__err-msg"></div>
            <div class="emt-field__help">Usa la barra para dar formato: subtítulos, listas, citas, enlaces e imágenes. También puedes cambiar a la pestaña «Texto» para pegar HTML.</div>
        </div>
    </div>

    <div class="emt-panel-form__bar">
        <span class="emt-panel-form__msg" data-form-msg></span>
        <button type="submit" class="emt-panel__btn" data-save="draft">Guardar borrador</button>
        <button type="submit" class="emt-panel__btn emt-panel__btn--primary" data-save="publish">Publicar</button>
    </div>
</form>
