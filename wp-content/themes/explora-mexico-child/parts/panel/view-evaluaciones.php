<?php
/**
 * Panel — Evaluaciones de clientes: resumen (promedio, buenas, por asesor)
 * y listado de respuestas de la encuesta /evaluacion/.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$evals = get_posts( array(
    'post_type'      => 'evaluacion',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );

$tot = count( $evals );
$sum = 0; $buenas = 0; $por_asesor = array(); $sum_asesor_calif = 0; $n_asesor_calif = 0;
foreach ( $evals as $ev ) {
    $c = (int) get_post_meta( $ev->ID, 'emt_calif_general', true );
    $sum += $c;
    if ( $c >= 4 ) { $buenas++; }
    $ca = (int) get_post_meta( $ev->ID, 'emt_calif_asesor', true );
    if ( $ca > 0 ) { $sum_asesor_calif += $ca; $n_asesor_calif++; }
    $aid = (int) get_post_meta( $ev->ID, 'emt_asesor_id', true );
    if ( $aid ) {
        if ( ! isset( $por_asesor[ $aid ] ) ) { $por_asesor[ $aid ] = array( 'n' => 0, 'sum' => 0 ); }
        $por_asesor[ $aid ]['n']++;
        $por_asesor[ $aid ]['sum'] += ( $ca > 0 ? $ca : $c );
    }
}
$promedio   = $tot ? round( $sum / $tot, 1 ) : 0;
$pct_buenas = $tot ? round( $buenas / $tot * 100 ) : 0;
$prom_ases  = $n_asesor_calif ? round( $sum_asesor_calif / $n_asesor_calif, 1 ) : 0;

$estrellas = function ( $n ) {
    $n = max( 0, min( 5, (int) round( $n ) ) );
    return str_repeat( '★', $n ) . str_repeat( '☆', 5 - $n );
};
$srv_lbl = array( 'tour' => 'Tour', 'transporte' => 'Transporte', 'cotizacion' => 'Cotización', '' => '—' );
$link_encuesta = home_url( '/evaluacion/' );
?>
<div class="emt-panel__head">
    <div>
        <h1>Evaluaciones <span class="emt-panel__count"><?php echo (int) $tot; ?></span></h1>
        <p class="emt-panel__head-sub">Respuestas de la encuesta post-servicio. Comparte el link con tus clientes al terminar cada tour.</p>
    </div>
    <a class="emt-panel__btn emt-panel__btn--live" href="<?php echo esc_url( $link_encuesta ); ?>" target="_blank" rel="noopener">Ver encuesta &#8599;</a>
</div>

<div class="emt-sharelink">
    <div class="emt-sharelink__txt">
        <strong>Link de la encuesta</strong>
        <span>Envíaselo al cliente por WhatsApp al terminar su tour. Si su experiencia fue buena (4–5 estrellas), la encuesta lo invita automáticamente a dejar reseña en Google.</span>
    </div>
    <div class="emt-sharelink__row">
        <input type="text" readonly value="<?php echo esc_attr( $link_encuesta ); ?>" data-sharelink-input onclick="this.select();" aria-label="Link de la encuesta" />
        <button type="button" class="emt-panel__btn" data-sharelink-copy>Copiar</button>
    </div>
</div>

<div class="emt-panel__metrics">
    <div class="emt-panel__metric">
        <span class="emt-panel__metric-num"><?php echo $tot ? esc_html( number_format_i18n( $promedio, 1 ) ) : '—'; ?></span>
        <span class="emt-panel__metric-label">Calificación promedio</span>
        <span class="emt-panel__metric-sub"><?php echo esc_html( $estrellas( $promedio ) ); ?></span>
    </div>
    <div class="emt-panel__metric">
        <span class="emt-panel__metric-num"><?php echo (int) $pct_buenas; ?>%</span>
        <span class="emt-panel__metric-label">Experiencias buenas</span>
        <span class="emt-panel__metric-sub">4–5 estrellas (invitados a reseñar en Google)</span>
    </div>
    <div class="emt-panel__metric">
        <span class="emt-panel__metric-num"><?php echo $n_asesor_calif ? esc_html( number_format_i18n( $prom_ases, 1 ) ) : '—'; ?></span>
        <span class="emt-panel__metric-label">Atención de asesores</span>
        <span class="emt-panel__metric-sub">Promedio de la calificación al asesor</span>
    </div>
</div>

<?php if ( $por_asesor ) : ?>
<section class="emt-panel__card">
    <h2>Por asesor</h2>
    <ul class="emt-dash-list">
        <?php
        uasort( $por_asesor, function ( $a, $b ) { return ( $b['sum'] / max( 1, $b['n'] ) ) <=> ( $a['sum'] / max( 1, $a['n'] ) ); } );
        foreach ( $por_asesor as $aid => $st ) :
            $prom_a = round( $st['sum'] / max( 1, $st['n'] ), 1 ); ?>
            <li>
                <span class="emt-dash-list__main"><a href="<?php echo esc_url( emt_panel_url( 'asesores/editar/' . $aid . '/' ) ); ?>"><?php echo esc_html( get_the_title( $aid ) ?: 'Asesor #' . $aid ); ?></a></span>
                <span class="emt-dash-when"><?php echo esc_html( $estrellas( $prom_a ) . ' ' . number_format_i18n( $prom_a, 1 ) . ' · ' . $st['n'] . ' evaluaciones' ); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<div class="emt-panel__table-wrap">
    <?php if ( $evals ) : ?>
    <table class="emt-panel__table">
        <thead>
            <tr><th>Fecha</th><th>Calificación</th><th>Asesor</th><th>Servicio</th><th>Comentario</th><th>Cliente</th></tr>
        </thead>
        <tbody>
            <?php foreach ( array_slice( $evals, 0, 100 ) as $ev ) :
                $c   = (int) get_post_meta( $ev->ID, 'emt_calif_general', true );
                $aid = (int) get_post_meta( $ev->ID, 'emt_asesor_id', true );
                $srv = (string) get_post_meta( $ev->ID, 'emt_servicio', true );
                $com = (string) get_post_meta( $ev->ID, 'emt_comentario', true );
                $nom = (string) get_post_meta( $ev->ID, 'emt_nombre', true );
                $wa  = (string) get_post_meta( $ev->ID, 'emt_whatsapp', true );
                ?>
                <tr>
                    <td><?php echo esc_html( get_the_date( 'd M Y H:i', $ev ) ); ?></td>
                    <td><span class="emt-eval-stars-mini<?php echo $c >= 4 ? ' is-buena' : ( $c <= 2 ? ' is-mala' : '' ); ?>"><?php echo esc_html( $estrellas( $c ) ); ?></span></td>
                    <td><?php echo $aid ? esc_html( get_the_title( $aid ) ) : '—'; ?></td>
                    <td><?php echo esc_html( $srv_lbl[ $srv ] ?? '—' ); ?></td>
                    <td class="emt-eval-coment"><?php echo $com ? esc_html( wp_trim_words( $com, 20, '…' ) ) : '—'; ?></td>
                    <td><?php echo esc_html( trim( $nom . ( $wa ? ' · ' . $wa : '' ) ) ?: 'Anónimo' ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p class="emt-panel__empty">Aún no hay evaluaciones. Comparte el link de arriba con tus clientes al terminar su tour.</p>
    <?php endif; ?>
</div>
