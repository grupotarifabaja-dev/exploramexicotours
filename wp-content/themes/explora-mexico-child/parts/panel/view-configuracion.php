<?php
/**
 * Panel — sección Configuración del sitio (P5).
 * Edita los campos clave de la options page (emt-config): contacto, redes y
 * textos del hero estacional. Guardado vía AJAX (emt_panel_save_config) con
 * nonce + capability + sanitización.
 *
 * Por seguridad NO se exponen aquí: claves de API (Google/Peek), Place ID, el
 * modo Under Construction ni los repetidores del mega-menú. Esos quedan en
 * wp-admin para el administrador.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$o = function ( $field, $default = '' ) {
    $v = get_field( $field, 'option' );
    return ( $v === null || $v === false ) ? $default : $v;
};
?>
<div class="emt-panel__head">
    <div>
        <h1>Configuración</h1>
        <p class="emt-panel__head-sub">Datos de contacto, redes sociales y textos del sitio.</p>
    </div>
</div>

<form id="emt-config-form" data-emt-form data-ajax-action="emt_panel_save_config" data-required-draft="" data-required-publish="">

    <div class="emt-panel-form__section">
        <h2>Contacto</h2>
        <div class="emt-grid-2">
            <div class="emt-field"><label>WhatsApp (número)</label><input type="text" name="wa_number" value="<?php echo esc_attr( $o( 'wa_number' ) ); ?>" placeholder="523310480670" /><div class="emt-field__help">Solo dígitos, con lada de país.</div></div>
            <div class="emt-field"><label>Teléfono oficina</label><input type="text" name="telefono_oficina" value="<?php echo esc_attr( $o( 'telefono_oficina' ) ); ?>" /></div>
        </div>
        <div class="emt-grid-2">
            <div class="emt-field"><label>Email reservas</label><input type="email" name="email_reservas" value="<?php echo esc_attr( $o( 'email_reservas' ) ); ?>" /></div>
            <div class="emt-field"><label>Email contacto</label><input type="email" name="email_contacto" value="<?php echo esc_attr( $o( 'email_contacto' ) ); ?>" /></div>
        </div>
        <div class="emt-field"><label>Dirección fiscal</label><textarea name="direccion_fiscal"><?php echo esc_textarea( $o( 'direccion_fiscal' ) ); ?></textarea></div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Redes sociales</h2>
        <div class="emt-grid-2">
            <div class="emt-field"><label>Facebook</label><input type="url" name="redes_facebook" value="<?php echo esc_attr( $o( 'redes_facebook' ) ); ?>" placeholder="https://facebook.com/…" /></div>
            <div class="emt-field"><label>Instagram</label><input type="url" name="redes_instagram" value="<?php echo esc_attr( $o( 'redes_instagram' ) ); ?>" placeholder="https://instagram.com/…" /></div>
        </div>
        <div class="emt-grid-2">
            <div class="emt-field"><label>TikTok</label><input type="url" name="redes_tiktok" value="<?php echo esc_attr( $o( 'redes_tiktok' ) ); ?>" placeholder="https://tiktok.com/@…" /></div>
            <div class="emt-field"><label>YouTube</label><input type="url" name="redes_youtube" value="<?php echo esc_attr( $o( 'redes_youtube' ) ); ?>" placeholder="https://youtube.com/@…" /></div>
        </div>
    </div>

    <div class="emt-panel-form__section">
        <h2>Textos del sitio (hero estacional)</h2>
        <div class="emt-field"><label>Mostrar banner estacional</label>
            <div class="emt-checks">
                <label><input type="checkbox" name="hero_seasonal_active" value="1" <?php checked( (bool) $o( 'hero_seasonal_active' ) ); ?> /> Activo en la portada</label>
            </div>
        </div>
        <div class="emt-field"><label>Título</label><input type="text" name="hero_seasonal_title" value="<?php echo esc_attr( $o( 'hero_seasonal_title' ) ); ?>" /></div>
        <div class="emt-field"><label>Subtítulo</label><textarea name="hero_seasonal_subtitle"><?php echo esc_textarea( $o( 'hero_seasonal_subtitle' ) ); ?></textarea></div>
        <div class="emt-grid-2">
            <div class="emt-field"><label>Texto del botón (CTA)</label><input type="text" name="hero_seasonal_cta_text" value="<?php echo esc_attr( $o( 'hero_seasonal_cta_text' ) ); ?>" placeholder="Ver tours" /></div>
            <div class="emt-field"><label>URL del botón (CTA)</label><input type="url" name="hero_seasonal_cta_url" value="<?php echo esc_attr( $o( 'hero_seasonal_cta_url' ) ); ?>" /></div>
        </div>
    </div>

    <div class="emt-panel-form__bar">
        <span class="emt-panel-form__msg" data-form-msg></span>
        <button type="submit" class="emt-panel__btn emt-panel__btn--primary" data-save="save">Guardar cambios</button>
    </div>
</form>
