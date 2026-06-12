# ADR 001 · Stack base del proyecto

**Fecha:** 2026-06-12
**Estado:** Aprobado
**Decisores:** Fabian Valdez

## Contexto

Necesitamos definir el stack técnico para reconstruir el sitio de Explora México Tours desde cero en un servidor limpio.

El sitio anterior estaba sobre WooCommerce 7.1.2 con vulnerabilidades activas, plantilla genérica y sin updates desde 2022.

## Decisión

**WordPress + Hello Elementor (parent) + `explora-mexico-child` (custom child theme), sin WooCommerce.**

### Componentes específicos
- WordPress 6.5+
- Tema padre: Hello Elementor
- Tema hijo: `explora-mexico-child` (versionado en este repo)
- ACF Pro para campos personalizados
- Elementor Pro como constructor
- Code Snippets Pro para lógica versionada
- Wordfence o Solid Security Pro

### Servidor
- Hetzner Cloud + CloudPanel
- PHP 8.2+, MariaDB 10.6+, Nginx
- Google Workspace para correos (separado)

### Lo que NO se usa
- WooCommerce (catálogo es CPT custom, reservas van a Peek)
- WPML / Polylang (multi-idioma nativo en código)
- Page builders adicionales (Divi, WPBakery)

## Consecuencias

### Positivas
- Sitio significativamente más rápido sin Woo
- Schema SEO correcto para turismo (`TouristTrip` vs `Product`)
- Menor superficie de seguridad
- Control total del código y la arquitectura

### Negativas
- Si en el futuro EMT quiere vender directo en el sitio (sin Peek), hay que agregar WooCommerce u otra solución de e-commerce
- Multi-idioma nativo requiere mantenimiento manual de strings cuando se actualicen

## Alternativas consideradas

### A) WooCommerce + WooCommerce Bookings
Rechazada porque el motor de reservas es Peek. Mantener Woo solo añade peso muerto.

### B) Headless con Next.js + WP como CMS
Rechazada por complejidad operativa para el equipo del cliente. El equipo de EMT necesita administrar contenido fácilmente desde wp-admin.

### C) Squarespace / Wix
Rechazada por limitaciones de SEO, multi-idioma personalizado y migración futura.
