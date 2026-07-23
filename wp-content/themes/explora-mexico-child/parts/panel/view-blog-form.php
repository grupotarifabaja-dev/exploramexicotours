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
            <textarea name="contenido" rows="18" placeholder="Escribe aquí el artículo. Separa párrafos con una línea en blanco. Puedes usar subtítulos con &lt;h2&gt;Subtítulo&lt;/h2&gt;, listas con &lt;ul&gt;&lt;li&gt;… y citas con &lt;blockquote&gt;…&lt;/blockquote&gt;."><?php echo esc_textarea( $contenido ); ?></textarea>
            <div class="emt-field__err-msg"></div>
            <div class="emt-field__help">Los párrafos se formatean solos. Para subtítulos usa <code>&lt;h2&gt;</code>, listas <code>&lt;ul&gt;&lt;li&gt;</code> y citas <code>&lt;blockquote&gt;</code>.</div>
        </div>
    </div>

    <div class="emt-panel-form__bar">
        <span class="emt-panel-form__msg" data-form-msg></span>
        <button type="submit" class="emt-panel__btn" data-save="draft">Guardar borrador</button>
        <button type="submit" class="emt-panel__btn emt-panel__btn--primary" data-save="publish">Publicar</button>
    </div>
</form>
