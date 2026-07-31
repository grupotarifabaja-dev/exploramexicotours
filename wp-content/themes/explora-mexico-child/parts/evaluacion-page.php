<?php
/**
 * Página /evaluacion/ — encuesta post-servicio (renderizada por inc/evaluacion.php).
 * Corta y por bloques; al enviar, la respuesta decide la pantalla final:
 * buena experiencia -> invitación a reseñar en Google; si no -> agradecimiento.
 * Se comparte por link directo (no aparece en menús; noindex).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Asesores activos para "¿Quién te atendió?".
$emt_ases = get_posts( array( 'post_type' => 'asesor', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );

add_action( 'wp_head', function () {
    echo '<meta name="robots" content="noindex,nofollow" />' . "\n";
}, 1 );

get_header();
?>
<main class="emt-evaluacion">

    <section class="emt-legal-hero">
        <div class="emt-container">
            <span class="emt-eyebrow">Tu opinión</span>
            <h1>¿Cómo fue tu experiencia?</h1>
        </div>
    </section>

    <section class="emt-section">
        <div class="emt-container emt-eval__wrap">

            <form class="emt-eval__form" data-eval-form>
                <input type="hidden" name="action" value="emt_evaluacion" />
                <?php wp_nonce_field( 'emt_evaluacion', 'nonce' ); ?>

                <div class="emt-eval__card">
                    <label class="emt-eval__q">¿Qué servicio tomaste?</label>
                    <div class="emt-eval__opts" data-eval-radios>
                        <label class="emt-eval__opt"><input type="radio" name="servicio" value="tour" /><span>Un tour</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="servicio" value="transporte" /><span>Traslado / transporte</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="servicio" value="cotizacion" /><span>Cotización / información</span></label>
                    </div>
                </div>

                <div class="emt-eval__card">
                    <label class="emt-eval__q" for="emt-eval-asesor">¿Quién te atendió?</label>
                    <select id="emt-eval-asesor" name="asesor_id" class="emt-eval__select">
                        <option value="">Selecciona a tu asesor…</option>
                        <?php foreach ( $emt_ases as $emt_a ) : ?>
                            <option value="<?php echo (int) $emt_a->ID; ?>"><?php echo esc_html( get_the_title( $emt_a ) ); ?></option>
                        <?php endforeach; ?>
                        <option value="0">No lo recuerdo</option>
                    </select>
                </div>

                <div class="emt-eval__card">
                    <label class="emt-eval__q">¿Cómo calificas tu experiencia con Explora México Tours? <span class="emt-req">*</span></label>
                    <div class="emt-eval__stars" data-eval-stars data-target="calif_general" role="radiogroup" aria-label="Calificación general">
                        <?php for ( $emt_i = 1; $emt_i <= 5; $emt_i++ ) : ?><button type="button" class="emt-eval__star" data-star="<?php echo $emt_i; ?>" aria-label="<?php echo $emt_i; ?> de 5">★</button><?php endfor; ?>
                    </div>
                    <input type="hidden" name="calif_general" value="" />
                </div>

                <div class="emt-eval__card">
                    <label class="emt-eval__q">¿Y la atención de tu asesor?</label>
                    <div class="emt-eval__stars" data-eval-stars data-target="calif_asesor" role="radiogroup" aria-label="Calificación del asesor">
                        <?php for ( $emt_i = 1; $emt_i <= 5; $emt_i++ ) : ?><button type="button" class="emt-eval__star" data-star="<?php echo $emt_i; ?>" aria-label="<?php echo $emt_i; ?> de 5">★</button><?php endfor; ?>
                    </div>
                    <input type="hidden" name="calif_asesor" value="" />
                </div>

                <div class="emt-eval__card">
                    <label class="emt-eval__q">¿Resolvimos todas tus dudas?</label>
                    <div class="emt-eval__opts">
                        <label class="emt-eval__opt"><input type="radio" name="resolvimos" value="si" /><span>Sí</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="resolvimos" value="no" /><span>No</span></label>
                    </div>
                </div>

                <div class="emt-eval__card">
                    <label class="emt-eval__q">¿Nos recomendarías con tus amigos y familia?</label>
                    <div class="emt-eval__opts">
                        <label class="emt-eval__opt"><input type="radio" name="recomienda" value="si" /><span>¡Claro que sí!</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="recomienda" value="tal_vez" /><span>Tal vez</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="recomienda" value="no" /><span>No</span></label>
                    </div>
                </div>

                <div class="emt-eval__card">
                    <label class="emt-eval__q">¿Cómo nos conociste?</label>
                    <div class="emt-eval__opts">
                        <label class="emt-eval__opt"><input type="radio" name="canal" value="facebook" /><span>Facebook</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="canal" value="instagram" /><span>Instagram</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="canal" value="whatsapp" /><span>WhatsApp</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="canal" value="web" /><span>Página web</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="canal" value="recomendacion" /><span>Me recomendaron</span></label>
                        <label class="emt-eval__opt"><input type="radio" name="canal" value="otro" /><span>Otro</span></label>
                    </div>
                </div>

                <div class="emt-eval__card">
                    <label class="emt-eval__q" for="emt-eval-coment">¿Algo que quieras contarnos?</label>
                    <textarea id="emt-eval-coment" name="comentario" rows="3" placeholder="Lo que más te gustó, o lo que podemos mejorar…"></textarea>
                    <div class="emt-eval__grid2">
                        <div><label class="emt-eval__q emt-eval__q--sm" for="emt-eval-nombre">Tu nombre (opcional)</label><input id="emt-eval-nombre" type="text" name="nombre" /></div>
                        <div><label class="emt-eval__q emt-eval__q--sm" for="emt-eval-wa">WhatsApp para promociones (opcional)</label><input id="emt-eval-wa" type="tel" name="whatsapp" placeholder="33 0000 0000" /></div>
                    </div>
                </div>

                <p class="emt-eval__err" data-eval-err hidden>Cuéntanos al menos tu calificación general (las estrellas) para enviar. 🙂</p>
                <button type="submit" class="emt-btn emt-btn--cta emt-eval__enviar" data-eval-enviar>Enviar evaluación</button>
            </form>

            <!-- Final: buena experiencia -> invitar a reseñar en Google -->
            <div class="emt-eval__final emt-eval__final--buena" data-eval-final-buena hidden>
                <div class="emt-eval__final-ic">🎉</div>
                <h2>¡Gracias! Nos alegra que la pasaras increíble.</h2>
                <p>¿Nos regalas 30 segundos más? Una reseña en Google nos ayuda muchísimo a que más viajeros nos encuentren.</p>
                <a class="emt-btn emt-btn--cta" data-eval-review-link href="#" target="_blank" rel="noopener noreferrer">Dejar mi reseña en Google ★</a>
                <p class="emt-eval__final-no">O si prefieres, aquí termina — ¡gracias por viajar con nosotros!</p>
            </div>

            <!-- Final: experiencia regular/mala -> solo agradecer -->
            <div class="emt-eval__final" data-eval-final-normal hidden>
                <div class="emt-eval__final-ic">🙏</div>
                <h2>Gracias por tu honestidad.</h2>
                <p>Tu opinión llega directo a nuestro equipo y nos ayuda a mejorar. Si quieres contarnos más, escríbenos por WhatsApp — queremos que tu próxima experiencia sea excelente.</p>
            </div>

        </div>
    </section>
</main>
<?php
get_footer();
