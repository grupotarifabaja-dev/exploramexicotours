<?php
/**
 * Panel — formulario de alta/edición de entrada de blog.
 * Campos ES + traducción EN (título, extracto y contenido) con pestañas de idioma.
 * Guardado vía AJAX (emt_panel_save_post). El contenido EN se guarda como post
 * meta (titulo_en / excerpt_en / contenido_en) y la web en /en/ lo usa con
 * respaldo automático al español.
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

$titulo_en    = $editing ? (string) get_post_meta( $post_id, 'titulo_en', true ) : '';
$extracto_en  = $editing ? (string) get_post_meta( $post_id, 'excerpt_en', true ) : '';
$contenido_en = $editing ? (string) get_post_meta( $post_id, 'contenido_en', true ) : '';

$img_id    = $editing ? (int) get_post_thumbnail_id( $post_id ) : 0;
$img_thumb = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';

$cats      = $editing ? get_the_category( $post_id ) : array();
$cat_name  = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
$tags      = $editing ? get_the_terms( $post_id, 'post_tag' ) : array();
$tags_csv  = ( $tags && ! is_wp_error( $tags ) ) ? implode( ', ', wp_list_pluck( $tags, 'name' ) ) : '';

$all_cats  = get_categories( array( 'hide_empty' => false ) );
?>
<div class="emt-panel__head">
    <div>
        <h1><?php echo $editing ? 'Editar artículo' : 'Nuevo artículo'; ?></h1>
        <p class="emt-panel__head-sub"><a href="<?php echo esc_url( emt_panel_url( 'blog/' ) ); ?>">&larr; Volver a la lista</a></p>
    </div>
    <?php if ( $editing && get_post_status( $post_id ) === 'publish' ) : ?><a class="emt-panel__btn emt-panel__btn--live" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" target="_blank" rel="noopener">Ver en vivo &#8599;</a><?php endif; ?>
</div>

<form id="emt-blog-form" data-emt-form data-ajax-action="emt_panel_save_post" data-required-draft="titulo" data-required-publish="titulo,contenido" data-post-id="<?php echo (int) $post_id; ?>" novalidate>

    <div class="emt-lang-tabs" data-lang-tabs role="tablist" aria-label="Idioma de los campos">
        <button type="button" class="emt-lang-tab is-active" data-lang-tab="es" role="tab" aria-selected="true">Español</button>
        <button type="button" class="emt-lang-tab" data-lang-tab="en" role="tab" aria-selected="false">English</button>
        <span class="emt-lang-tabs__hint">Cambia entre los campos en <strong>español</strong> e <strong>inglés</strong>. El inglés es opcional: si lo dejas vacío, la web en inglés usa el texto en español.</span>
    </div>

    <div class="emt-panel-form__section">
        <h2>Artículo</h2>
        <div class="emt-field emt-i18n-es" data-field="titulo">
            <label>Título <span class="emt-req">*</span></label>
            <input type="text" name="titulo" value="<?php echo esc_attr( $titulo ); ?>" placeholder="Ej. Ruta del Tequila: guía de un día" required />
            <div class="emt-field__err-msg"></div>
        </div>
        <div class="emt-field emt-i18n-en">
            <label>Título (EN)</label>
            <input type="text" name="titulo_en" value="<?php echo esc_attr( $titulo_en ); ?>" placeholder="e.g. Tequila Route: a one-day guide" />
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
        <div class="emt-field emt-i18n-es">
            <label>Extracto</label>
            <textarea name="extracto" rows="2" placeholder="Resumen breve que aparece en el listado y al compartir."><?php echo esc_textarea( $extracto ); ?></textarea>
        </div>
        <div class="emt-field emt-i18n-en">
            <label>Extracto (EN)</label>
            <textarea name="extracto_en" rows="2" placeholder="Short summary (EN)."><?php echo esc_textarea( $extracto_en ); ?></textarea>
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
        <div class="emt-field emt-i18n-es" data-field="contenido">
            <label>Cuerpo del artículo <span class="emt-req">*</span></label>
            <?php
            wp_editor( $contenido, 'contenido', array(
                'textarea_name' => 'contenido',
                'textarea_rows' => 16,
                'media_buttons' => true,
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
        <div class="emt-field emt-i18n-en">
            <label>Cuerpo del artículo (EN)</label>
            <?php
            wp_editor( $contenido_en, 'contenido_en', array(
                'textarea_name' => 'contenido_en',
                'textarea_rows' => 16,
                'media_buttons' => true,
                'teeny'         => false,
                'quicktags'     => true,
                'tinymce'       => array(
                    'toolbar1'      => 'formatselect,bold,italic,bullist,numlist,blockquote,link,unlink,alignleft,aligncenter,undo,redo',
                    'block_formats' => 'Paragraph=p;Heading=h2;Subheading=h3',
                ),
            ) );
            ?>
            <div class="emt-field__help">Traducción al inglés. Si lo dejas vacío, la web en inglés muestra el contenido en español.</div>
        </div>
    </div>

    <div class="emt-panel-form__bar">
        <span class="emt-panel-form__msg" data-form-msg></span>
        <button type="submit" class="emt-panel__btn" data-save="draft">Guardar borrador</button>
        <button type="submit" class="emt-panel__btn emt-panel__btn--primary" data-save="publish">Publicar</button>
    </div>
</form>
