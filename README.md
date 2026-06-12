# Explora México Tours · Sitio web

Repositorio del nuevo sitio web de **Explora México Tours** (EMT).
Stack: WordPress + Hello Elementor parent + child theme custom `explora-mexico-child`.

---

## 📋 Antes de empezar

Lee primero el documento maestro del proyecto:
👉 [`docs/PROYECTO-EMT-DOC-MAESTRO.md`](docs/PROYECTO-EMT-DOC-MAESTRO.md)

Es la fuente única de verdad. Toda decisión técnica, arquitectura, convenciones y orden de construcción están ahí.

---

## 🏗️ Estructura del repositorio

```
exploramexicotours/
├── README.md                            ← Este archivo
├── docs/                                ← Documentación del proyecto
│   ├── PROYECTO-EMT-DOC-MAESTRO.md     ← Doc maestro (LEER PRIMERO)
│   ├── decisiones/                      ← Architecture Decision Records
│   └── branding/                        ← Manual de marca, logos
├── wp-content/
│   └── themes/
│       └── explora-mexico-child/        ← Child theme custom
├── scripts/                             ← Scripts de deploy y mantenimiento
└── .github/workflows/                   ← CI/CD
```

---

## 🚀 Setup local

### Requisitos
- PHP 8.2+
- MariaDB 10.6+ / MySQL 8+
- WordPress 6.5+
- WP-CLI
- Hello Elementor + Elementor Pro + ACF Pro + Code Snippets Pro

### Pasos

```bash
# 1. Clonar el repo
git clone git@github.com:TU_USUARIO/exploramexicotours.git
cd exploramexicotours

# 2. Levantar WordPress local (DevKinsta / Local / Docker)
# Y dentro de tu instalación WordPress local:

# 3. Symlink del child theme al repo
ln -s /ruta/al/repo/wp-content/themes/explora-mexico-child \
      /ruta/wp-local/wp-content/themes/explora-mexico-child

# 4. Activar tema desde wp-admin o WP-CLI
wp theme activate explora-mexico-child
```

---

## 🌳 Estrategia de branches

| Branch | Propósito | Deploy automático |
|---|---|---|
| `main` | Producción · solo merges desde `dev` con tag | exploramexicotours.com |
| `dev` | Desarrollo activo · base de feature branches | modelos.exploramexicotours.com (staging) |
| `feature/*` | Features individuales | — |
| `hotfix/*` | Bugs urgentes en producción | — |

### Workflow estándar

```bash
git checkout dev
git pull
git checkout -b feature/agregar-mega-menu
# ... trabajo, commits ...
git push origin feature/agregar-mega-menu
# Crear Pull Request → dev
```

---

## ✏️ Convenciones de commit

Formato: `<tipo>(<ámbito>): <descripción corta>`

**Tipos:**
- `feat`: nueva funcionalidad
- `fix`: corrección de bug
- `refactor`: cambio sin alterar comportamiento
- `style`: cambios de formato/estilo CSS
- `docs`: documentación
- `chore`: mantenimiento (deps, configs)
- `test`: pruebas

**Ejemplos:**
```
feat(home): agregar hero estacional rotativo
fix(tour-card): corregir overlay en imágenes oscuras
refactor(i18n): extraer helper emt_t a inc/i18n.php
docs(readme): actualizar pasos de setup local
```

---

## 🔐 Variables y secrets

**Nunca commitear:**
- `wp-config.php`
- Archivos `.env`
- API keys de Google, Meta, Peek
- Credenciales SSH o de base de datos
- Backups de BD

Las credenciales viven en gestor de contraseñas (1Password / Bitwarden). El equipo accede a ellas previa autorización.

---

## 📞 Contactos

- **Director técnico:** Fabian Valdez · soporte@supratecnia.com · 33-1295-4302
- **Cliente:** Explora México Tours · reserva@exploramexicotours.com

---

## 📄 Licencia

Código propietario de Supratecnia para uso exclusivo de Explora México Tours.
No redistribuir.
