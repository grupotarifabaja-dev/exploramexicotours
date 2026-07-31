<?php
/**
 * Disparador TEMPORAL v2: separa por horarios/paradas los itinerarios de los
 * 26 tours (formato "Sesión fotográfica": una fila por horario o parada, con
 * día, hora, icono, título y descripción, en ES y EN).
 * Fuente: seeders/datos-reales/data/tours-data.json + docx del cliente.
 *
 * Uso: /?emt_fix_itin=emt-itin-2026   (idempotente: re-escribe los repetidores)
 * QUITAR este archivo (y su require en functions.php) tras aplicarlo en producción.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    if ( ! isset( $_GET['emt_fix_itin'] ) ) { return; }
    if ( ! current_user_can( 'manage_options' ) && ! hash_equals( 'emt-itin-2026', (string) $_GET['emt_fix_itin'] ) ) {
        status_header( 403 ); header( 'Content-Type: text/plain; charset=utf-8' ); echo 'token invalido'; exit;
    }
    header( 'Content-Type: application/json; charset=utf-8' );
    @set_time_limit( 0 );

    $F = function ( $dia, $hora, $icono, $titulo, $desc ) {
        return array( 'dia' => $dia, 'hora' => $hora, 'icono' => $icono, 'titulo' => $titulo, 'descripcion' => $desc );
    };
    $E = function ( $titulo, $desc ) {
        return array( 'titulo_en' => $titulo, 'descripcion_en' => $desc );
    };

    $tours = array();

    /* ---------- Xantolo · Huasteca Potosina (3 días) ---------- */
    $tours['xantolo-dia-de-muertos-huasteca'] = array(
        'es' => array(
            $F( 1, '05:00', 'salida', 'Salida de Guadalajara', 'Salida del punto de encuentro con destino a la Huasteca Potosina.' ),
            $F( 1, '', 'actividad', 'Cascadas de Tamasopo', 'Arribo y tiempo libre para disfrutar de los escenarios naturales de Tamasopo.' ),
            $F( 1, '', 'hospedaje', 'Alojamiento en Ciudad Valles', 'Traslado a Ciudad Valles y noche de descanso.' ),
            $F( 2, '', 'actividad', 'Cascada El Meco · tubbing', 'Actividad de tubbing en la Cascada El Meco.' ),
            $F( 2, '', 'parada', 'Minas Viejas y Micos', 'Visita a la Cascada de Minas Viejas y a las Cascadas de Micos para fotografías.' ),
            $F( 2, '', 'actividad', 'Noche de Xantolo en Ciudad Valles', 'Por la noche, recorrido por el centro de Ciudad Valles para admirar los altares y ofrendas del Xantolo.' ),
            $F( 3, '', 'actividad', 'Cascada de Tamul y Cueva del Agua', 'Visita a la imponente Cascada de Tamul y a la Cueva del Agua.' ),
            $F( 3, '', 'regreso', 'Regreso a Guadalajara', 'Al finalizar, traslado de regreso a Guadalajara. Fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Departure from Guadalajara', 'Departure from the meeting point towards the Huasteca Potosina.' ),
            $E( 'Tamasopo Waterfalls', 'Arrival and free time to enjoy the natural scenery of Tamasopo.' ),
            $E( 'Overnight in Ciudad Valles', 'Transfer to Ciudad Valles and a night of rest.' ),
            $E( 'El Meco Waterfall · tubing', 'Tubing activity at El Meco Waterfall.' ),
            $E( 'Minas Viejas & Micos', 'Visit to Minas Viejas Waterfall and the Micos Waterfalls for photos.' ),
            $E( 'Xantolo night in Ciudad Valles', 'In the evening, stroll through downtown Ciudad Valles to admire the Xantolo altars and offerings.' ),
            $E( 'Tamul Waterfall & Water Cave', 'Visit to the impressive Tamul Waterfall and the Water Cave.' ),
            $E( 'Return to Guadalajara', 'Transfer back to Guadalajara. End of services.' ),
        ),
    );

    /* ---------- Día de Muertos en Michoacán (3 días) ---------- */
    $tours['dia-de-muertos-michoacan'] = array(
        'es' => array(
            $F( 1, '06:00', 'salida', 'Salida de Guadalajara', 'Salida con destino a Capula, Michoacán.' ),
            $F( 1, '', 'comida', 'Capula: desayuno y artesanías', 'Tiempo libre para desayunar y comprar artesanías (célebres catrinas de barro).' ),
            $F( 1, '', 'parada', 'Isla de Janitzio', 'Visita a la Isla de Janitzio en el Lago de Pátzcuaro.' ),
            $F( 1, '', 'hospedaje', 'Alojamiento en Morelia', 'Por la tarde, regreso a Morelia para registro y descanso.' ),
            $F( 2, '', 'parada', 'Tzurumútaro y su panteón', 'Visita al cementerio decorado con cempasúchil, uno de los más emblemáticos de la Noche de Muertos.' ),
            $F( 2, '', 'parada', 'Pátzcuaro', 'Recorrido por Pátzcuaro y la Plaza Vasco de Quiroga.' ),
            $F( 2, '', 'parada', 'Tzintzuntzan', 'Visita a Las Yácatas y al Ex convento de Santa Ana.' ),
            $F( 2, '', 'regreso', 'Regreso nocturno a Morelia', 'Cerca de la medianoche, regreso a Morelia para descansar.' ),
            $F( 3, '', 'actividad', 'Mañana libre en Morelia', 'Mañana libre para el centro de la ciudad, museos o paseo en tranvía.' ),
            $F( 3, '', 'regreso', 'Regreso a Guadalajara', 'Después de la comida, traslado de regreso a Guadalajara. Fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Departure from Guadalajara', 'Departure towards Capula, Michoacán.' ),
            $E( 'Capula: breakfast & crafts', 'Free time for breakfast and to shop for crafts (the famous clay catrinas).' ),
            $E( 'Janitzio Island', 'Visit to Janitzio Island on Lake Pátzcuaro.' ),
            $E( 'Overnight in Morelia', 'In the afternoon, return to Morelia for check-in and rest.' ),
            $E( 'Tzurumútaro cemetery', 'Visit to the cemetery decorated with marigolds, one of the most iconic sites of the Night of the Dead.' ),
            $E( 'Pátzcuaro', 'Tour of Pátzcuaro and Vasco de Quiroga Square.' ),
            $E( 'Tzintzuntzan', 'Visit to Las Yácatas and the former convent of Santa Ana.' ),
            $E( 'Night return to Morelia', 'Around midnight, return to Morelia to rest.' ),
            $E( 'Free morning in Morelia', 'Free morning for the city center, museums or the sightseeing trolley.' ),
            $E( 'Return to Guadalajara', 'After lunch, transfer back to Guadalajara. End of services.' ),
        ),
    );

    /* ---------- Día de Muertos en Mixquic (día único) ---------- */
    $tours['dia-de-muertos-en-mixquic'] = array(
        'es' => array(
            $F( 1, '12:45', 'salida', 'Cita en punto de encuentro', 'Encuentro en el Hotel Barceló Centro CDMX.' ),
            $F( 1, '13:00', 'salida', 'Salida a Mixquic', 'Traslado a San Andrés Mixquic.' ),
            $F( 1, '', 'actividad', 'Altares y ofrendas de Mixquic', 'Tarde y noche para vivir la celebración: altares, ofrendas, el panteón iluminado con velas y el ambiente único de la Alumbrada.' ),
            $F( 1, '22:00', 'regreso', 'Fin de actividades', 'Regreso al punto de salida.' ),
            $F( 1, '23:45', 'parada', 'Arribo a Ciudad de México', 'Llegada al Hotel Barceló Centro CDMX. Fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Meeting point', 'Meet-up at Hotel Barceló Centro CDMX.' ),
            $E( 'Departure to Mixquic', 'Transfer to San Andrés Mixquic.' ),
            $E( 'Mixquic altars & offerings', 'Afternoon and evening to live the celebration: altars, offerings, the candle-lit cemetery and the unique atmosphere of the Alumbrada.' ),
            $E( 'End of activities', 'Return to the departure point.' ),
            $E( 'Arrival in Mexico City', 'Arrival at Hotel Barceló Centro CDMX. End of services.' ),
        ),
    );

    /* ---------- Barrancas del Cobre (5 días) ---------- */
    $tours['barrancas-del-cobre'] = array(
        'es' => array(
            $F( 1, '', 'salida', 'Llegada a Chihuahua', 'Recepción en el aeropuerto con cartel con su nombre y traslado al hotel.' ),
            $F( 1, '', 'actividad', 'Paseo opcional por la ciudad', 'Día libre: Museo de Pancho Villa (cerrado los lunes), Palacio de Gobierno y sus murales, Catedral, Acueducto Colonial, Quinta Gameros, Calabozo de Miguel Hidalgo y Centro Histórico.' ),
            $F( 1, '', 'hospedaje', 'Alojamiento en Chihuahua', 'Noche en Chihuahua.' ),
            $F( 2, '08:30', 'salida', 'Chihuahua – Creel', 'Cita en el lobby del hotel para traslado terrestre al Pueblo Mágico de Creel.' ),
            $F( 2, '', 'parada', 'Comunidad Menonita', 'Visita en Cuauhtémoc (cerrada los domingos).' ),
            $F( 2, '', 'actividad', 'Alrededores de Creel', 'Lago de Arareko, Valle de los Hongos, Valle de las Ranas, Misión Jesuita de San Ignacio y una cueva habitada por Tarahumaras. Tarde libre.' ),
            $F( 2, '', 'hospedaje', 'Alojamiento en Creel', 'Noche en Creel.' ),
            $F( 3, '08:30', 'salida', 'Creel – Divisadero', 'Traslado por carretera a Posada Barrancas Divisadero.' ),
            $F( 3, '', 'actividad', 'Parque de aventura y miradores', 'Tour de miradores incluido; teleférico, tirolesas, piedra volada y puente colgante (opcionales no incluidos). Degustación opcional de las típicas gorditas de Divisadero. Tarde libre.' ),
            $F( 3, '', 'hospedaje', 'Alojamiento en Barrancas', 'Noche frente a las Barrancas del Cobre.' ),
            $F( 4, '08:30', 'salida', 'Tren Chepe Express', 'Traslado a la estación Divisadero para abordar el Chepe Express rumbo a El Fuerte, entre puentes y túneles.' ),
            $F( 4, '14:35', 'parada', 'Llegada a El Fuerte', 'Recepción y traslado al hotel. Tarde libre para las calles empedradas, la iglesia colonial, el palacio municipal y el museo local.' ),
            $F( 4, '', 'hospedaje', 'Alojamiento en El Fuerte', 'Noche en el Pueblo Mágico de El Fuerte.' ),
            $F( 5, '', 'regreso', 'El Fuerte – Aeropuerto de Los Mochis', 'Mañana libre y traslado al aeropuerto según el horario de su vuelo. Fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Arrival in Chihuahua', 'Airport reception with a name sign and transfer to your hotel.' ),
            $E( 'Optional city walk', 'Free day: Pancho Villa Museum (closed Mondays), Government Palace and its murals, Cathedral, Colonial Aqueduct, Quinta Gameros, Miguel Hidalgo dungeon and the Historic Center.' ),
            $E( 'Overnight in Chihuahua', 'Night in Chihuahua.' ),
            $E( 'Chihuahua – Creel', 'Hotel-lobby meet-up for the land transfer to the Magical Town of Creel.' ),
            $E( 'Mennonite Community', 'Visit in Cuauhtémoc (closed on Sundays).' ),
            $E( 'Around Creel', 'Lake Arareko, Valley of the Mushrooms, Valley of the Frogs, San Ignacio Jesuit Mission and a cave inhabited by Tarahumara people. Free afternoon.' ),
            $E( 'Overnight in Creel', 'Night in Creel.' ),
            $E( 'Creel – Divisadero', 'Road transfer to Posada Barrancas Divisadero.' ),
            $E( 'Adventure park & viewpoints', 'Included viewpoints tour; cable car, ziplines, "flying stone" and hanging bridge (optional, not included). Optional tasting of the typical Divisadero gorditas. Free afternoon.' ),
            $E( 'Overnight at the canyon', 'Night facing the Copper Canyon.' ),
            $E( 'Chepe Express train', 'Transfer to Divisadero station to board the Chepe Express to El Fuerte, across bridges and tunnels.' ),
            $E( 'Arrival in El Fuerte', 'Reception and hotel transfer. Free afternoon for the cobbled streets, colonial church, town hall and local museum.' ),
            $E( 'Overnight in El Fuerte', 'Night in the Magical Town of El Fuerte.' ),
            $E( 'El Fuerte – Los Mochis airport', 'Free morning and airport transfer according to your flight time. End of services.' ),
        ),
    );

    /* ---------- Oaxaca Ciudad (5 días) ---------- */
    $tours['oaxaca-ciudad'] = array(
        'es' => array(
            $F( 1, '', 'salida', 'Llegada a Oaxaca', 'Recepción en el aeropuerto y traslado a su hotel.' ),
            $F( 1, '', 'hospedaje', 'Tarde libre', 'Tarde libre para un primer paseo por la ciudad.' ),
            $F( 2, '', 'parada', 'Árbol del Tule', 'Inicio frente al Árbol del Tule, con su imponente copa y altura.' ),
            $F( 2, '', 'actividad', 'Teotitlán del Valle', 'Taller de teñido natural para entender el arte textil zapoteco.' ),
            $F( 2, '', 'parada', 'Mitla', 'Las grecas milenarias de la "Ciudad de los Muertos" zapoteca.' ),
            $F( 2, '', 'actividad', 'Hierve el Agua y mezcal', 'Caminata frente a las cascadas petrificadas y cierre con degustación de mezcal artesanal.' ),
            $F( 3, '', 'parada', 'Monte Albán', 'Antigua capital zapoteca: templos, terrazas y plazas.' ),
            $F( 3, '', 'actividad', 'Alebrijes en Arrazola', 'Taller auténtico de tallado y pintado, sin compras obligadas.' ),
            $F( 3, '', 'parada', 'San Bartolo Coyotepec', 'El famoso barro negro oaxaqueño.' ),
            $F( 4, '', 'actividad', 'Centro de Oaxaca', 'Medio día caminando: zócalo, catedral, templo de Santo Domingo y calles coloridas.' ),
            $F( 4, '', 'hospedaje', 'Tarde libre', 'Compras, museos o degustar mezcal.' ),
            $F( 5, '', 'regreso', 'Traslado al aeropuerto', 'Traslado al aeropuerto de Oaxaca. Fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Arrival in Oaxaca', 'Airport reception and transfer to your hotel.' ),
            $E( 'Free afternoon', 'Free afternoon for a first stroll around the city.' ),
            $E( 'Tule Tree', 'Start facing the Tule Tree, with its massive crown and height.' ),
            $E( 'Teotitlán del Valle', 'Natural-dye workshop to understand Zapotec textile art.' ),
            $E( 'Mitla', 'The ancient fretwork of the Zapotec "City of the Dead".' ),
            $E( 'Hierve el Agua & mezcal', 'Walk by the petrified waterfalls and close with an artisanal mezcal tasting.' ),
            $E( 'Monte Albán', 'Ancient Zapotec capital: temples, terraces and plazas.' ),
            $E( 'Alebrijes in Arrazola', 'Authentic carving and painting workshop, with no forced shopping.' ),
            $E( 'San Bartolo Coyotepec', 'Oaxaca’s famous black clay pottery.' ),
            $E( 'Downtown Oaxaca', 'Half-day walking tour: main square, cathedral, Santo Domingo temple and colorful streets.' ),
            $E( 'Free afternoon', 'Shopping, museums or mezcal tasting.' ),
            $E( 'Airport transfer', 'Transfer to Oaxaca airport. End of services.' ),
        ),
    );

    /* ---------- Oaxaca y sus Playas (5 días, del docx del cliente) ---------- */
    $tours['oaxaca-y-sus-playas'] = array(
        'es' => array(
            $F( 1, '', 'salida', 'Llegada a Puerto Escondido', 'Recepción en el aeropuerto y traslado a su alojamiento.' ),
            $F( 1, '', 'actividad', 'Tortugas y bioluminiscencia', 'Por la tarde, liberación de tortugas y visita a la laguna de Manialtepec para apreciar el fenómeno de la bioluminiscencia. Regreso al hotel.' ),
            $F( 2, '', 'actividad', 'Manglares de Ventanilla', 'Navegación por los manglares observando flora y fauna en su hábitat natural.' ),
            $F( 2, '', 'parada', 'Mazunte', 'Degustación de mezcal, chocolate y café.' ),
            $F( 2, '', 'parada', 'Zipolite y Puerto Ángel', 'La icónica playa de ambiente libre y relajado, y el pintoresco pueblo pesquero. Descanso en la playa Estacahuite antes del regreso al hotel.' ),
            $F( 3, '', 'actividad', 'Bahías de Huatulco en lancha', 'Navegación para explorar el Bufadero y el Rostro de la Piedra, y visita a la playa El Órgano.' ),
            $F( 3, '', 'comida', 'Bahía del Maguey', 'Pescados y mariscos frescos con vistas a la bahía.' ),
            $F( 3, '', 'parada', 'La Crucecita', 'Visita a una fábrica textilera local y degustación de mezcal.' ),
            $F( 4, '', 'parada', 'City tour Puerto Escondido', 'Mercado Benito Juárez, miradores, playa Zicatela (la "tercera ola más grande del mundo"), Andador Turístico, Bahía Principal y Zona Adoquinada.' ),
            $F( 4, '', 'actividad', 'Paseo por las 7 bahías', 'Navegación por la costa en busca de encuentros con tortugas marinas y otra vida marina.' ),
            $F( 5, '', 'regreso', 'Traslado al aeropuerto', 'Traslado al aeropuerto de Puerto Escondido. Fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Arrival in Puerto Escondido', 'Airport reception and transfer to your accommodation.' ),
            $E( 'Turtles & bioluminescence', 'In the afternoon, turtle release and a visit to Manialtepec lagoon to witness the bioluminescence phenomenon. Return to the hotel.' ),
            $E( 'Ventanilla mangroves', 'Boat ride through the mangroves, observing flora and fauna in their natural habitat.' ),
            $E( 'Mazunte', 'Mezcal, chocolate and coffee tasting.' ),
            $E( 'Zipolite & Puerto Ángel', 'The iconic free-spirited beach and the picturesque fishing village. Relax at Estacahuite beach before returning to the hotel.' ),
            $E( 'Huatulco bays by boat', 'Sail to explore the Bufadero and the Face in the Rock, and visit El Órgano beach.' ),
            $E( 'Maguey Bay', 'Fresh fish and seafood with bay views.' ),
            $E( 'La Crucecita', 'Visit to a local textile workshop and mezcal tasting.' ),
            $E( 'Puerto Escondido city tour', 'Benito Juárez market, viewpoints, Zicatela beach (the "third biggest wave in the world"), tourist walkway, Main Bay and the cobbled quarter.' ),
            $E( 'Seven bays boat ride', 'Coastal navigation in search of sea turtles and other marine life.' ),
            $E( 'Airport transfer', 'Transfer to Puerto Escondido airport. End of services.' ),
        ),
    );

    /* ---------- Explora Chiapas (5 días) ---------- */
    $tours['explora-chiapas'] = array(
        'es' => array(
            $F( 1, '', 'salida', 'Llegada a Tuxtla Gutiérrez', 'Recepción en el aeropuerto y traslado a su hotel en San Cristóbal de las Casas.' ),
            $F( 1, '', 'hospedaje', 'Noche en San Cristóbal', 'Registro en el hotel y noche libre para descansar.' ),
            $F( 2, '', 'salida', 'Salida temprano a Palenque', 'Con escala en Ocosingo para desayunar.' ),
            $F( 2, '', 'actividad', 'Cascadas de Agua Azul', 'Parque formado por la unión de los ríos Otulún, Shumuljá y Tulijá.' ),
            $F( 2, '', 'parada', 'Cascada de Misol-Ha', 'Caída de 40 metros rodeada de selva.' ),
            $F( 2, '', 'actividad', 'Zona Arqueológica de Palenque', 'Recorrido guiado por uno de los sitios mayas más importantes de Mesoamérica. Regreso a San Cristóbal.' ),
            $F( 3, '', 'actividad', 'Cascadas de El Chiflón', 'Cadena de cascadas del Río San Vicente: El Suspiro, Ala de Ángel, Arco Iris, Quinceañera y Velo de Novia (120 m).' ),
            $F( 3, '', 'parada', 'Lagunas de Montebello', 'Visita a Tziscao y al lago internacional en la frontera con Guatemala. Regreso a San Cristóbal.' ),
            $F( 4, '', 'parada', 'Cañón del Sumidero', 'Visita a los miradores del cañón.' ),
            $F( 4, '', 'actividad', 'Lancha por el Grijalva', 'Recorrido de 1 h 50 min: presa de Chicoasén, cueva de la virgen y árbol de navidad.' ),
            $F( 4, '', 'parada', 'Chiapa de Corzo', 'Tiempo libre en el Pueblo Mágico. Regreso a San Cristóbal.' ),
            $F( 5, '', 'regreso', 'Traslado al aeropuerto', 'Mañana libre y, a la hora acordada, traslado al aeropuerto de Tuxtla Gutiérrez. Fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Arrival in Tuxtla Gutiérrez', 'Airport reception and transfer to your hotel in San Cristóbal de las Casas.' ),
            $E( 'Night in San Cristóbal', 'Hotel check-in and free evening to rest.' ),
            $E( 'Early departure to Palenque', 'With a breakfast stop in Ocosingo.' ),
            $E( 'Agua Azul waterfalls', 'Park formed by the meeting of the Otulún, Shumuljá and Tulijá rivers.' ),
            $E( 'Misol-Ha waterfall', 'A 40-meter drop surrounded by jungle.' ),
            $E( 'Palenque Archaeological Site', 'Guided tour of one of the most important Mayan sites in Mesoamerica. Return to San Cristóbal.' ),
            $E( 'El Chiflón waterfalls', 'Chain of waterfalls on the San Vicente river: El Suspiro, Ala de Ángel, Arco Iris, Quinceañera and Velo de Novia (120 m).' ),
            $E( 'Montebello lakes', 'Visit to Tziscao and the international lake on the border with Guatemala. Return to San Cristóbal.' ),
            $E( 'Sumidero Canyon', 'Visit to the canyon viewpoints.' ),
            $E( 'Boat ride on the Grijalva', '1 h 50 min ride: Chicoasén dam, the virgin’s cave and the Christmas-tree formation.' ),
            $E( 'Chiapa de Corzo', 'Free time in the Magical Town. Return to San Cristóbal.' ),
            $E( 'Airport transfer', 'Free morning and, at the agreed time, transfer to Tuxtla Gutiérrez airport. End of services.' ),
        ),
    );

    /* ---------- Ciudades Coloniales (7 días) ---------- */
    $tours['ciudades-coloniales'] = array(
        'es' => array(
            $F( 1, '12:00', 'salida', 'Ciudad de México – Morelia', 'Salida del punto de encuentro (hotel o aeropuerto de CDMX) y traslado a Morelia.' ),
            $F( 1, '', 'actividad', 'Centro de Morelia', 'Paseo vespertino por el centro histórico.' ),
            $F( 1, '', 'hospedaje', 'Alojamiento en Morelia', 'Noche en Morelia.' ),
            $F( 2, '', 'actividad', 'Ruta Don Vasco', 'Recorrido cultural por Michoacán siguiendo los pasos de Vasco de Quiroga: Pátzcuaro, Tzintzuntzan y Quiroga. Regreso a Morelia.' ),
            $F( 3, '', 'comida', 'Desayuno y salida', 'Después del desayuno, salida hacia Querétaro.' ),
            $F( 3, '', 'actividad', 'Querétaro en tranvía', 'Recorrido vespertino en tranvía para conocer la ciudad. Alojamiento en Querétaro.' ),
            $F( 4, '', 'parada', 'Ruta del vino', 'Pueblos Mágicos de Tequisquiapan y San Sebastián de Bernal.' ),
            $F( 4, '', 'comida', 'Quesos y viñedo', 'Degustación de quesos y visita a uno de los viñedos más antiguos de la ruta. Regreso a Querétaro.' ),
            $F( 5, '', 'salida', 'Querétaro – Guanajuato', 'Traslado a Guanajuato.' ),
            $F( 5, '', 'actividad', 'Callejones de Guanajuato', 'Recorrido a pie por calles y callejones; por la noche, callejoneada con una tradicional estudiantina. Alojamiento en Guanajuato.' ),
            $F( 6, '', 'parada', 'Ruta de la Independencia', 'Dolores Hidalgo (cuna de la Independencia), santuario de Atotonilco y San Miguel de Allende con su Parroquia de San Miguel Arcángel. Regreso a Guanajuato.' ),
            $F( 7, '', 'actividad', 'Guanajuato a fondo', 'Recorrido guiado: Museo de las Momias, Monumento a El Pípila, túneles, mina del barrio de Valenciana, Iglesia de San Cayetano y mercado local.' ),
            $F( 7, '16:00', 'regreso', 'Traslado final', 'Traslado a Guadalajara (aeropuerto u hotel). Fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Mexico City – Morelia', 'Departure from the meeting point (CDMX hotel or airport) and transfer to Morelia.' ),
            $E( 'Downtown Morelia', 'Evening stroll through the historic center.' ),
            $E( 'Overnight in Morelia', 'Night in Morelia.' ),
            $E( 'Don Vasco Route', 'Cultural circuit through Michoacán following Vasco de Quiroga’s footsteps: Pátzcuaro, Tzintzuntzan and Quiroga. Return to Morelia.' ),
            $E( 'Breakfast & departure', 'After breakfast, departure to Querétaro.' ),
            $E( 'Querétaro by trolley', 'Evening trolley ride to get to know the city. Overnight in Querétaro.' ),
            $E( 'Wine route', 'The Magical Towns of Tequisquiapan and San Sebastián de Bernal.' ),
            $E( 'Cheese & winery', 'Cheese tasting and a visit to one of the oldest wineries on the route. Return to Querétaro.' ),
            $E( 'Querétaro – Guanajuato', 'Transfer to Guanajuato.' ),
            $E( 'Guanajuato alleys', 'Walking tour through streets and alleys; at night, a traditional estudiantina serenade walk. Overnight in Guanajuato.' ),
            $E( 'Independence Route', 'Dolores Hidalgo (cradle of Independence), Atotonilco sanctuary and San Miguel de Allende with its Parish of San Miguel Arcángel. Return to Guanajuato.' ),
            $E( 'Guanajuato in depth', 'Guided tour: Mummies Museum, El Pípila monument, tunnels, Valenciana mine, San Cayetano church and local market.' ),
            $E( 'Final transfer', 'Transfer to Guadalajara (airport or hotel). End of services.' ),
        ),
    );

    /* ---------- Tequila "Tradición con distinción" ---------- */
    $tours['tour-tequila-tradicion-con-distincion'] = array(
        'es' => array(
            $F( 1, '10:00', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '10:15', 'salida', 'Salida a Tequila', 'Traslado al Pueblo Mágico de Tequila.' ),
            $F( 1, '11:30', 'actividad', 'Recorrido en destilería', 'Arribo a la destilería e inicio del recorrido premium.' ),
            $F( 1, '14:00', 'comida', 'Centro de Tequila', 'Tiempo para comer y visitar la Parroquia de Santiago Apóstol, la Plaza principal y su quiosco, el callejón José Cuervo y el Museo Nacional del Tequila (admisión no incluida).' ),
            $F( 1, '16:30', 'actividad', 'Degustación íntima', 'Copa de bienvenida de pulque tradicional, introducción al mundo del agave y degustación guiada de cuatro variedades de tequila con chocolate, quesos y bocadillos de la región.' ),
            $F( 1, '18:30', 'comida', 'Cena maridaje', 'Cena maridaje en un lugar secreto.' ),
            $F( 1, '21:30', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '23:00', 'parada', 'Arribo y fin de servicios', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Departure to Tequila', 'Transfer to the Magical Town of Tequila.' ),
            $E( 'Distillery tour', 'Arrival at the distillery and start of the premium tour.' ),
            $E( 'Downtown Tequila', 'Time for lunch and to visit the Santiago Apóstol Parish, the main square and its kiosk, the José Cuervo alley and the National Tequila Museum (admission not included).' ),
            $E( 'Intimate tasting', 'Traditional pulque welcome drink, introduction to the agave world and a guided tasting of four tequila varieties with chocolate, cheese and regional snacks.' ),
            $E( 'Pairing dinner', 'Pairing dinner at a secret location.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'Arrival & end of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Tequila en tren José Cuervo Express ---------- */
    $tours['tour-tequila-jose-cuervo-express'] = array(
        'es' => array(
            $F( 1, '07:00', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '', 'parada', 'Campos de agave y jima', 'Traslado terrestre compartido Guadalajara–Tequila con visita a los campos de agave y demostración de jima.' ),
            $F( 1, '', 'actividad', 'Día completo en Tequila', 'Recorrido por la destilería La Rojeña®, Centro Cultural Juan Beckmann Gallardo y tiempo libre para comer y pasear.' ),
            $F( 1, '', 'actividad', 'Regreso en tren al atardecer', 'Tren José Cuervo Express (vagón Express) con alimentos ligeros, coctelería, lotería, catado educativo, espectáculo mexicano y brindis tradicional.' ),
            $F( 1, '22:00', 'regreso', 'Arribo a Guadalajara', 'Llegada aproximada y fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Agave fields & jima', 'Shared land transfer Guadalajara–Tequila with a visit to the agave fields and a jima (harvest) demonstration.' ),
            $E( 'Full day in Tequila', 'Tour of the La Rojeña® distillery, Juan Beckmann Gallardo Cultural Center and free time for lunch and strolling.' ),
            $E( 'Sunset train ride back', 'José Cuervo Express train (Express car) with light food, cocktails, Mexican lotería, educational tasting, Mexican show and a traditional toast.' ),
            $E( 'Arrival in Guadalajara', 'Approximate arrival time and end of services.' ),
        ),
    );

    /* ---------- Tequila "La Rojeña" ---------- */
    $tours['tour-tequila-la-rojena'] = array(
        'es' => array(
            $F( 1, '09:00', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '09:15', 'salida', 'Salida a Tequila', 'Traslado al Pueblo Mágico de Tequila.' ),
            $F( 1, '10:30', 'parada', 'Arribo al centro de Tequila', 'Llegada al corazón del pueblo.' ),
            $F( 1, '11:00', 'actividad', 'Recorrido La Rojeña', 'Visita guiada a la destilería José Cuervo La Rojeña.' ),
            $F( 1, '13:00', 'comida', 'Comida y paseo', 'Tiempo para comer y visitar la Parroquia de Santiago Apóstol, la Plaza principal y su quiosco, el callejón José Cuervo y el Museo Nacional del Tequila (admisión no incluida).' ),
            $F( 1, '15:00', 'parada', 'Cantaritos de Amatitán', 'Parada opcional para los famosos cantaritos.' ),
            $F( 1, '17:30', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '19:00', 'parada', 'Arribo y fin de servicios', 'Drop off en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Departure to Tequila', 'Transfer to the Magical Town of Tequila.' ),
            $E( 'Arrival in downtown Tequila', 'Arrival at the heart of the town.' ),
            $E( 'La Rojeña tour', 'Guided visit to the José Cuervo La Rojeña distillery.' ),
            $E( 'Lunch & stroll', 'Time for lunch and to visit the Santiago Apóstol Parish, the main square and its kiosk, the José Cuervo alley and the National Tequila Museum (admission not included).' ),
            $E( 'Cantaritos in Amatitán', 'Optional stop for the famous cantaritos.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'Arrival & end of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Tequila Jornalero ---------- */
    $tours['tour-tequila-jornalero'] = array(
        'es' => array(
            $F( 1, '07:40', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '07:50', 'salida', 'Salida a Tequila', 'Traslado a los campos de agave.' ),
            $F( 1, '09:00', 'actividad', 'Campos y destilería artesanal', 'Campos de agave, demostración de labores del campo, desayuno rústico a pie de campo, recorrido de fábrica y cata guiada.' ),
            $F( 1, '12:00', 'parada', 'Traslado al centro de Tequila', 'Camino al corazón del Pueblo Mágico.' ),
            $F( 1, '12:30', 'comida', 'Comida y paseo', 'Tiempo para comer y visitar la Parroquia de Santiago Apóstol, la Plaza principal y su quiosco, el callejón José Cuervo y el Museo Nacional del Tequila (admisión no incluida).' ),
            $F( 1, '14:30', 'parada', 'Cantaritos de Amatitán', 'Parada para los famosos cantaritos.' ),
            $F( 1, '17:00', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '18:15', 'parada', 'Arribo y fin de servicios', 'Drop off en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Departure to Tequila', 'Transfer to the agave fields.' ),
            $E( 'Fields & artisanal distillery', 'Agave fields, field-work demonstration, rustic field-side breakfast, factory tour and guided tasting.' ),
            $E( 'Transfer to downtown Tequila', 'On the way to the heart of the Magical Town.' ),
            $E( 'Lunch & stroll', 'Time for lunch and to visit the Santiago Apóstol Parish, the main square and its kiosk, the José Cuervo alley and the National Tequila Museum (admission not included).' ),
            $E( 'Cantaritos in Amatitán', 'Stop for the famous cantaritos.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'Arrival & end of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Tequila a tu alcance ---------- */
    $tours['tour-tequila-a-tu-alcance'] = array(
        'es' => array(
            $F( 1, '09:00', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '09:15', 'salida', 'Salida a Tequila', 'Traslado al Pueblo Mágico de Tequila.' ),
            $F( 1, '10:30', 'parada', 'Arribo al centro de Tequila', 'Llegada al corazón del pueblo.' ),
            $F( 1, '11:00', 'actividad', 'Recorrido en destilería local', 'Visita guiada con cata.' ),
            $F( 1, '13:00', 'comida', 'Comida y paseo', 'Tiempo para comer y visitar la Parroquia de Santiago Apóstol, la Plaza principal y su quiosco, el callejón José Cuervo y el Museo Nacional del Tequila (admisión no incluida).' ),
            $F( 1, '15:00', 'parada', 'Cantaritos de Amatitán', 'Parada opcional para los famosos cantaritos.' ),
            $F( 1, '17:30', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '19:00', 'parada', 'Arribo y fin de servicios', 'Drop off en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Departure to Tequila', 'Transfer to the Magical Town of Tequila.' ),
            $E( 'Arrival in downtown Tequila', 'Arrival at the heart of the town.' ),
            $E( 'Local distillery tour', 'Guided visit with tasting.' ),
            $E( 'Lunch & stroll', 'Time for lunch and to visit the Santiago Apóstol Parish, the main square and its kiosk, the José Cuervo alley and the National Tequila Museum (admission not included).' ),
            $E( 'Cantaritos in Amatitán', 'Optional stop for the famous cantaritos.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'Arrival & end of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Tequilero hasta los huesos ---------- */
    $tours['tour-tequilero-hasta-los-huesos'] = array(
        'es' => array(
            $F( 1, '10:45', 'salida', 'Pick up en punto de encuentro', 'Recogida para iniciar la experiencia.' ),
            $F( 1, '11:00', 'salida', 'Salida a Tequila', 'Traslado al Pueblo Mágico.' ),
            $F( 1, '13:00', 'actividad', 'Recorrido en destilería local', 'Visita guiada a la destilería.' ),
            $F( 1, '16:00', 'parada', 'Tequila en Día de Muertos', 'Tiempo libre: Parroquia de Santiago Apóstol, Plaza principal, quiosco, callejón José Cuervo, Museo Nacional del Tequila (admisión no incluida), altares y ofrendas del festival.' ),
            $F( 1, '19:00', 'comida', 'Cata y cena maridaje', 'Cata guiada y cena maridaje.' ),
            $F( 1, '21:00', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '23:00', 'parada', 'Arribo y fin de servicios', 'Llegada al punto de encuentro.' ),
        ),
        'en' => array(
            $E( 'Meeting-point pick-up', 'Pick-up to start the experience.' ),
            $E( 'Departure to Tequila', 'Transfer to the Magical Town.' ),
            $E( 'Local distillery tour', 'Guided distillery visit.' ),
            $E( 'Tequila on Day of the Dead', 'Free time: Santiago Apóstol Parish, main square, kiosk, José Cuervo alley, National Tequila Museum (admission not included), plus the festival’s altars and offerings.' ),
            $E( 'Tasting & pairing dinner', 'Guided tasting and pairing dinner.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'Arrival & end of services', 'Arrival at the meeting point.' ),
        ),
    );

    /* ---------- Guadalajara y Tlaquepaque ---------- */
    $tours['guadalajara-y-tlaquepaque'] = array(
        'es' => array(
            $F( 1, '09:30', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '10:00', 'actividad', 'Centro de Guadalajara', 'Recorrido por la Cruz de Plazas y el Hospicio Cabañas.' ),
            $F( 1, '13:30', 'salida', 'Salida a Tlaquepaque', 'Traslado al Pueblo Mágico de Tlaquepaque.' ),
            $F( 1, '14:30', 'comida', 'Comida en Tlaquepaque', 'Arribo y tiempo para comer.' ),
            $F( 1, '16:00', 'actividad', 'Taller de dulces mexicanos', 'Visita al taller y tiempo libre para pasear y comprar.' ),
            $F( 1, '18:00', 'regreso', 'Regreso al hotel', 'Traslado de regreso.' ),
            $F( 1, '19:00', 'parada', 'Fin de servicios', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Downtown Guadalajara', 'Tour of the Cruz de Plazas and the Hospicio Cabañas.' ),
            $E( 'Departure to Tlaquepaque', 'Transfer to the Magical Town of Tlaquepaque.' ),
            $E( 'Lunch in Tlaquepaque', 'Arrival and time for lunch.' ),
            $E( 'Mexican candy workshop', 'Workshop visit and free time to stroll and shop.' ),
            $E( 'Return to your hotel', 'Transfer back.' ),
            $E( 'End of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Chapala y Ajijic ---------- */
    $tours['chapala-y-ajijic'] = array(
        'es' => array(
            $F( 1, '09:30', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '10:30', 'parada', 'Tienda charra', 'Parada opcional en la tienda charra más grande de Latinoamérica.' ),
            $F( 1, '12:00', 'parada', 'Malecón de Chapala', 'Arribo y tiempo libre para caminar y tomar fotografías.' ),
            $F( 1, '14:00', 'comida', 'Ajijic', 'Comida y visita a la plaza principal, la iglesia, el muro de las calaveras y su malecón.' ),
            $F( 1, '18:00', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '19:30', 'parada', 'Fin de servicios', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Charro store', 'Optional stop at the biggest charro store in Latin America.' ),
            $E( 'Chapala boardwalk', 'Arrival and free time to walk and take photos.' ),
            $E( 'Ajijic', 'Lunch and a visit to the main square, the church, the skull wall and its boardwalk.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'End of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Mazamitla ---------- */
    $tours['mazamitla'] = array(
        'es' => array(
            $F( 1, '08:30', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '11:00', 'parada', 'Arribo a Mazamitla', 'Traslado al punto de encuentro para el recorrido.' ),
            $F( 1, '12:00', 'actividad', 'Recorrido Sierra del Tigre', 'Inicio del recorrido por la sierra.' ),
            $F( 1, '14:30', 'comida', 'Tiempo para comida', 'Regreso del recorrido y comida.' ),
            $F( 1, '16:00', 'actividad', 'Centro de Mazamitla', 'Tiempo libre en el centro, con opción de rentar vehículo todo terreno.' ),
            $F( 1, '18:00', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '20:30', 'parada', 'Fin de servicios', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Arrival in Mazamitla', 'Transfer to the meeting point for the tour.' ),
            $E( 'Sierra del Tigre tour', 'Start of the mountain tour.' ),
            $E( 'Lunch time', 'Back from the tour and time for lunch.' ),
            $E( 'Downtown Mazamitla', 'Free time in the center, with an option to rent an ATV.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'End of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Cañonismo en Jalisco ---------- */
    $tours['canonismo-en-jalisco'] = array(
        'es' => array(
            $F( 1, '06:00', 'salida', 'Registro de participantes', 'Registro, tallaje de equipo y firma de carta responsiva.' ),
            $F( 1, '07:00', 'salida', 'Salida a Chiquilistlán', 'Traslado al punto de la actividad.' ),
            $F( 1, '10:00', 'actividad', 'Cañonismo', 'Preparación de equipo, instrucciones de uso y seguridad, e inicio del recorrido.' ),
            $F( 1, '15:00', 'parada', 'Fin de la actividad', 'Tiempo para cambiarse.' ),
            $F( 1, '16:00', 'comida', 'Tiempo para comida', 'Comida después del recorrido.' ),
            $F( 1, '18:00', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '21:00', 'parada', 'Fin de servicios', 'Arribo al punto de encuentro.' ),
        ),
        'en' => array(
            $E( 'Participant check-in', 'Registration, gear fitting and liability waiver signing.' ),
            $E( 'Departure to Chiquilistlán', 'Transfer to the activity site.' ),
            $E( 'Canyoning', 'Gear preparation, safety briefing and start of the descent.' ),
            $E( 'End of the activity', 'Time to change clothes.' ),
            $E( 'Lunch time', 'Lunch after the tour.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'End of services', 'Arrival at the meeting point.' ),
        ),
    );

    /* ---------- Día de Muertos y Calaverandia ---------- */
    $tours['dia-de-muertos-y-calaverandia'] = array(
        'es' => array(
            $F( 1, '14:00', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '15:00', 'actividad', 'Museo Panteón de Belén', 'Recorrido por el histórico panteón en el centro de Guadalajara.' ),
            $F( 1, '16:30', 'parada', 'Tianguis de cartón', 'Visita y tiempo para compras.' ),
            $F( 1, '18:00', 'salida', 'Salida a Calaverandia', 'Traslado al parque temático.' ),
            $F( 1, '19:30', 'actividad', 'Calaverandia', 'Tiempo para disfrutar el parque temático de Día de Muertos.' ),
            $F( 1, '23:00', 'regreso', 'Regreso al hotel', 'Traslado de regreso y fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Belén Cemetery Museum', 'Tour of the historic cemetery in downtown Guadalajara.' ),
            $E( 'Cardboard market', 'Visit and time for shopping.' ),
            $E( 'Departure to Calaverandia', 'Transfer to the theme park.' ),
            $E( 'Calaverandia', 'Time to enjoy the Day of the Dead theme park.' ),
            $E( 'Return to your hotel', 'Transfer back and end of services.' ),
        ),
    );

    /* ---------- Guadalajara y Modelado en Barro ---------- */
    $tours['guadalajara-y-modelado-en-barro'] = array(
        'es' => array(
            $F( 1, '09:30', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '10:00', 'actividad', 'Centro de Guadalajara', 'Recorrido por la Cruz de Plazas y el Hospicio Cabañas.' ),
            $F( 1, '13:30', 'salida', 'Salida a Tlaquepaque', 'Traslado al Pueblo Mágico de Tlaquepaque.' ),
            $F( 1, '14:30', 'comida', 'Comida en Tlaquepaque', 'Arribo y tiempo para comer.' ),
            $F( 1, '16:00', 'actividad', 'Modelado en barro', 'Actividad artesanal de modelado en barro.' ),
            $F( 1, '18:30', 'regreso', 'Regreso al hotel', 'Traslado de regreso.' ),
            $F( 1, '19:00', 'parada', 'Fin de servicios', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Downtown Guadalajara', 'Tour of the Cruz de Plazas and the Hospicio Cabañas.' ),
            $E( 'Departure to Tlaquepaque', 'Transfer to the Magical Town of Tlaquepaque.' ),
            $E( 'Lunch in Tlaquepaque', 'Arrival and time for lunch.' ),
            $E( 'Clay modeling', 'Hands-on clay modeling craft activity.' ),
            $E( 'Return to your hotel', 'Transfer back.' ),
            $E( 'End of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Viñedos de Chapala ---------- */
    $tours['vinedos-de-chapala'] = array(
        'es' => array(
            $F( 1, '10:00', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '11:45', 'parada', 'Viñedos La Estramancia', 'Arribo al viñedo.' ),
            $F( 1, '12:00', 'actividad', 'Recorrido y degustación', 'Visita guiada por el viñedo con degustación.' ),
            $F( 1, '14:00', 'comida', 'Ajijic', 'Comida y visita a la plaza principal, la iglesia, el muro de las calaveras y su malecón.' ),
            $F( 1, '18:00', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '20:00', 'parada', 'Fin de servicios', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'La Estramancia vineyards', 'Arrival at the winery.' ),
            $E( 'Tour & tasting', 'Guided vineyard visit with tasting.' ),
            $E( 'Ajijic', 'Lunch and a visit to the main square, the church, the skull wall and its boardwalk.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'End of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Isla de Mezcala y Ajijic ---------- */
    $tours['isla-de-mezcala-y-ajijic'] = array(
        'es' => array(
            $F( 1, '09:30', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '11:00', 'actividad', 'Isla de Mezcala', 'Arribo y tiempo para visitar la isla histórica.' ),
            $F( 1, '13:00', 'salida', 'Traslado a Ajijic', 'Camino al pueblo a orillas del lago.' ),
            $F( 1, '14:00', 'comida', 'Ajijic', 'Tiempo libre para comer, caminar por sus calles, visitar su iglesia y tomar fotografías.' ),
            $F( 1, '17:30', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '19:30', 'parada', 'Fin de servicios', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Mezcala Island', 'Arrival and time to visit the historic island.' ),
            $E( 'Transfer to Ajijic', 'On the way to the lakeside town.' ),
            $E( 'Ajijic', 'Free time for lunch, walking its streets, visiting the church and taking photos.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'End of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Senderismo y Petroglifos Isla de Mezcala ---------- */
    $tours['senderismo-petroglifos-mezcala'] = array(
        'es' => array(
            $F( 1, '06:00', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '07:30', 'actividad', 'Inicio del sendero', 'Ascenso de aproximadamente 2.5 horas según el ritmo del grupo.' ),
            $F( 1, '11:00', 'actividad', 'Descenso', 'Bajada de aproximadamente 2 horas según el ritmo del grupo.' ),
            $F( 1, '13:00', 'salida', 'Traslado a Ajijic', 'Arribo al punto de encuentro y traslado.' ),
            $F( 1, '14:00', 'comida', 'Comida en Ajijic', 'Tiempo para comer y pasear.' ),
            $F( 1, '16:00', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 1, '18:00', 'parada', 'Fin de servicios', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Trailhead', 'Roughly 2.5-hour ascent depending on the group’s pace.' ),
            $E( 'Descent', 'Roughly 2-hour descent depending on the group’s pace.' ),
            $E( 'Transfer to Ajijic', 'Arrival at the meeting point and transfer.' ),
            $E( 'Lunch in Ajijic', 'Time for lunch and a stroll.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'End of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Aventura en Bici Bosque La Primavera ---------- */
    $tours['aventura-en-bici-la-primavera'] = array(
        'es' => array(
            $F( 1, '08:00', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado. Dos horarios disponibles: turno de mañana (8:00–13:00) o de tarde (14:00–19:00); se muestran los horarios del turno de mañana.' ),
            $F( 1, '09:00', 'actividad', 'Equipo e inicio del recorrido', 'Recogida de equipo e inicio de la ruta en el Bosque La Primavera.' ),
            $F( 1, '12:00', 'parada', 'Entrega de equipo', 'Regreso al punto de encuentro y entrega del equipo.' ),
            $F( 1, '13:00', 'regreso', 'Fin de servicio', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point. Two schedules available: morning (8:00–13:00) or afternoon (14:00–19:00); morning times are shown.' ),
            $E( 'Gear & ride start', 'Gear pick-up and start of the ride in La Primavera Forest.' ),
            $E( 'Gear return', 'Back at the meeting point and gear hand-in.' ),
            $E( 'End of service', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- San Sebastián del Oeste y Mascota (2 días) ---------- */
    $tours['san-sebastian-y-mascota'] = array(
        'es' => array(
            $F( 1, '08:00', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado.' ),
            $F( 1, '11:00', 'comida', 'Desayuno en Mascota', 'Parada opcional para desayunar.' ),
            $F( 1, '12:30', 'salida', 'Salida a San Sebastián', 'Traslado al Pueblo Mágico.' ),
            $F( 1, '13:30', 'comida', 'Comida en San Sebastián', 'Arribo y tiempo libre para comer.' ),
            $F( 1, '16:00', 'actividad', 'Cerro de la Bufa y Real Alto', 'Visita al mirador y al pueblo Real Alto.' ),
            $F( 1, '19:00', 'hospedaje', 'Noche en San Sebastián', 'Regreso y noche libre para descansar.' ),
            $F( 2, '08:00', 'actividad', 'Mañana libre', 'Desayuno y caminata por el pueblo.' ),
            $F( 2, '11:00', 'salida', 'Salida a Mascota', 'Entrega de habitaciones y traslado.' ),
            $F( 2, '12:30', 'actividad', 'Fábrica de raicilla', 'Visita con degustación.' ),
            $F( 2, '13:30', 'comida', 'Comida en Mascota', 'Tiempo libre para comer.' ),
            $F( 2, '16:00', 'regreso', 'Regreso a Guadalajara', 'Traslado de regreso.' ),
            $F( 2, '20:00', 'parada', 'Fin de servicios', 'Dejada de pasajeros en su hotel.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point.' ),
            $E( 'Breakfast in Mascota', 'Optional breakfast stop.' ),
            $E( 'Departure to San Sebastián', 'Transfer to the Magical Town.' ),
            $E( 'Lunch in San Sebastián', 'Arrival and free time for lunch.' ),
            $E( 'La Bufa hill & Real Alto', 'Visit to the viewpoint and the Real Alto village.' ),
            $E( 'Night in San Sebastián', 'Return and free evening to rest.' ),
            $E( 'Free morning', 'Breakfast and a walk around town.' ),
            $E( 'Departure to Mascota', 'Check-out and transfer.' ),
            $E( 'Raicilla distillery', 'Visit with tasting.' ),
            $E( 'Lunch in Mascota', 'Free time for lunch.' ),
            $E( 'Return to Guadalajara', 'Transfer back.' ),
            $E( 'End of services', 'Drop-off at your hotel.' ),
        ),
    );

    /* ---------- Recuerdo de México · Sesión fotográfica (ya aplicado; idempotente) ---------- */
    $tours['recuerdo-de-mexico-sesion-fotografica'] = array(
        'es' => array(
            $F( 1, '13:30', 'salida', 'Pick up de pasajeros', 'Recogida en el punto acordado para iniciar la experiencia.' ),
            $F( 1, '14:30', 'actividad', 'Showroom: atuendo, maquillaje y peinado', 'Arribo al showroom para la selección del atuendo tradicional, maquillaje y peinado.' ),
            $F( 1, '16:00', 'parada', 'Traslado al set de la sesión', 'Salida hacia el escenario elegido para la sesión fotográfica.' ),
            $F( 1, '18:30', 'parada', 'Regreso al showroom', 'Entrega del atuendo tradicional.' ),
            $F( 1, '19:00', 'regreso', 'Regreso al hotel', 'Traslado de regreso; dejada de pasajeros en su alojamiento alrededor de las 20:00 y fin de servicios.' ),
        ),
        'en' => array(
            $E( 'Passenger pick-up', 'Pick-up at the agreed point to start the experience.' ),
            $E( 'Showroom: outfit, make-up and hairstyling', 'Arrival at the showroom to choose your traditional outfit, plus make-up and hairstyling.' ),
            $E( 'Transfer to the photo-session location', 'Departure to the chosen setting for the photo session.' ),
            $E( 'Back to the showroom', 'Return of the traditional outfit.' ),
            $E( 'Return to your hotel', 'Drop-off at your accommodation around 8:00 p.m.; end of services.' ),
        ),
    );

    /* ---------- Aplicar ---------- */
    $res = array();
    foreach ( $tours as $slug => $data ) {
        $post = get_page_by_path( $slug, OBJECT, 'tour' );
        if ( ! $post ) { $res[ $slug ] = 'NO ENCONTRADO'; continue; }
        if ( count( $data['es'] ) !== count( $data['en'] ) ) { $res[ $slug ] = 'ERROR: ES/EN desalineados'; continue; }
        update_field( 'itinerario', $data['es'], $post->ID );
        update_field( 'itinerario_en', $data['en'], $post->ID );
        $res[ $slug ] = count( $data['es'] ) . ' paradas';
    }

    echo wp_json_encode( array( 'ok' => true, 'tours' => $res ), JSON_UNESCAPED_UNICODE );
    exit;
}, 20 );
