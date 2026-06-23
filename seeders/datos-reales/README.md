# Seeder de datos reales — Explora México Tours

Carga el contenido **real** de EMT en una instalación de WordPress: **2 tours** (con
fotos) y **3 asesores**. Pensado para poblar entornos nuevos (local/staging) con
datos representativos — por ejemplo, para capturas de las guías del cliente o para
QA del front.

## Contenido

```
seeders/datos-reales/
├── seed.php                 Lógica del seeder (idempotente) + comando WP-CLI
├── data/
│   ├── tours-data.json      2 tours reales
│   ├── asesores-data.json   3 asesores reales
│   └── fotos/               5 fotos de los tours (los asesores no traen foto)
└── README.md
```

## Qué carga

| Tipo | Elementos | Notas |
|------|-----------|-------|
| Tours | Xantolo · Día de Muertos en la Huasteca Potosina (4 fotos) · Día de Muertos en Michoacán (1 foto) | Estado `publish`. `peek_url` queda en `#` hasta tener los enlaces de reserva. |
| Asesores | Samantha Arellano · Alma Aréchiga · Magdiel Muñoz | **Sin foto** (no incluidas en el paquete de origen). |

Crea automáticamente los términos de taxonomía que falten (destinos, categorías,
experiencias, idiomas y especialidades) y **reutiliza** los que ya existan.

## Cómo ejecutarlo

### Opción A — WP-CLI (recomendada)

Requiere un WP-CLI funcional contra la base de datos del entorno:

```bash
# Vista previa sin escribir nada:
wp --require=seeders/datos-reales/seed.php emt seed-datos-reales --dry-run

# Ejecutar:
wp --require=seeders/datos-reales/seed.php emt seed-datos-reales

# Cargar como borrador en vez de publicado:
wp --require=seeders/datos-reales/seed.php emt seed-datos-reales --status=draft
```

### Opción B — Programática

Desde cualquier contexto ya dentro de WordPress (un script propio, un disparador
temporal, etc.):

```php
require_once __DIR__ . '/seeders/datos-reales/seed.php';
$reporte = emt_seed_datos_reales();              // o array( 'dry_run' => true )
```

> En entornos donde WP-CLI no puede conectarse a la BD (p. ej. Local by Flywheel
> con un PHP CLI sin `mysqli`), use la Opción B disparándola desde un mu-plugin
> temporal que incluya `seed.php` y llame a `emt_seed_datos_reales()`.

## Idempotencia y limpieza

- **Idempotente:** identifica tours/asesores por `slug` (`post_name`); re-ejecutar
  **actualiza** en lugar de duplicar. Las fotos se importan una sola vez (se marcan
  con la meta `_emt_seed_photo` y se reutilizan).
- Todo lo creado por el seeder queda marcado con la meta `_emt_real_seed = 1`, lo
  que facilita identificarlo o eliminarlo después.

## Origen de los datos

Datos reales extraídos de los documentos del cliente. `precio_desde` corresponde al
precio más bajo (menor 6–12); la tabla completa de ocupación se resume en
`precio_nota`. Punto de salida real: Av. Cruz del Sur 2371, Jardines de la Cruz,
Guadalajara.
