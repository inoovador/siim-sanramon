# SIIM F1 — Identity + Auth + Shell + Dashboard Implementation Plan

> **For agentic workers:** Use superpowers:subagent-driven-development. Steps use checkbox tracking.

**Goal:** Convertir scaffold F0 en aplicación visualmente profesional con auth + roles + shell admin + dashboard con KPIs + páginas navegables — base para todas las features de F2+.

**Spec reference:** `docs/superpowers/specs/2026-05-21-siim-design.md` §6 (frontend), §3 (Identity), §7 (F1 roadmap).

**Architecture:** Capa Identity en Domain/Application con use cases mínimos (RegisterUser, AssignRole). Spatie permissions provee infraestructura RBAC. UI Livewire 3 + Volt: login/register brandeados, shell admin con sidebar+topbar persistente, dashboard con widgets de KPI (data mock, real en F2).

**Tech stack:** Laravel 11 + Livewire 3 + Volt + Spatie Permission 6 + Tailwind brand SIIM + ApexCharts.js (charts).

---

## Tareas F1 (ejecución agresiva, mínimo TDD donde aplica)

### Task 1 — Spatie Permission setup

- [ ] Publish Spatie migrations + config.
- [ ] Run migration.
- [ ] Seeder `RolesAndPermissionsSeeder` crea roles `admin`, `analyst`, `citizen`.
- [ ] Default seeder llama al seeder de roles.
- [ ] Commit `feat(f1): Spatie roles + seeder admin/analyst/citizen`.

### Task 2 — User domain mínimo + role assignment

- [ ] Eloquent `User` model usa `HasRoles` trait Spatie.
- [ ] Domain `SIIM\Domain\Identity\UserId` (UUID value object).
- [ ] Domain `SIIM\Domain\Identity\Role` (enum: admin/analyst/citizen).
- [ ] Application `SIIM\Application\Identity\UseCases\AssignDefaultRole` — al registrar usuario, asignar rol `citizen` por defecto.
- [ ] Listener en `Registered` event ejecuta use case.
- [ ] Pest test: registro → user tiene rol citizen.
- [ ] Commit.

### Task 3 — Páginas auth brandeadas

- [ ] Sobrescribir `resources/views/livewire/auth/login.blade.php` con diseño brand SIIM:
  - Card central con shadow-brand-lg
  - Logo SIIM placeholder (texto serif)
  - Header gradient verde canopy → verde más claro
  - Form con `<x-input-text>` brandeado
  - Footer "Municipalidad Distrital de San Ramón"
- [ ] Mismo tratamiento para `register.blade.php`, `forgot-password.blade.php`, `reset-password.blade.php`, `verify-email.blade.php`, `confirm-password.blade.php`.
- [ ] Sobrescribir `resources/views/layouts/guest.blade.php` con fondo gradient brand.
- [ ] Verify visual: login/register/forgot todos brand-consistent.
- [ ] Commit `feat(f1): páginas auth brandeadas paleta SIIM`.

### Task 4 — Layout admin shell (sidebar + topbar)

- [ ] Crear `resources/views/components/layouts/app.blade.php` (nueva, no usar Breeze default):
  - Sidebar fijo izquierda: logo SIIM + nav vertical con secciones
    - Dashboard
    - Comentarios
    - Análisis
    - Chatbot RAG
    - Reportes
    - Fuentes
    - Configuración (solo admin)
    - Usuarios (solo admin)
    - Auditoría
  - Topbar fijo: breadcrumb + búsqueda placeholder + avatar user + dropdown logout
  - Main content area con padding + scroll independiente
- [ ] Componente Blade `<x-sidebar-link href="..." icon="..." active>` con highlight `brand-canopy`.
- [ ] Componente `<x-icon name="..." />` con SVG inline (lucide-style: dashboard, message-square, brain, bar-chart, file-text, database, settings, users, history).
- [ ] Commit `feat(f1): shell admin con sidebar + topbar brandeado`.

### Task 5 — Dashboard página con KPIs + charts mock

- [ ] Ruta `/panel` (middleware `auth`).
- [ ] Componente Livewire `App\Livewire\Panel\Dashboard` con Volt o full class.
- [ ] 4 KPI cards en grid superior:
  - Comentarios totales (mock: 1247)
  - Sentimiento positivo % (mock: 64%)
  - Comentarios hoy (mock: 23)
  - Tema trending (mock: "Obras públicas")
- [ ] Chart line "Sentimiento últimos 30 días" (datos mock, ApexCharts).
- [ ] Chart donut "Distribución por canal" (mock).
- [ ] Tabla "Últimos 10 comentarios" (datos mock con seed).
- [ ] Footer card "Topics trending" con chips brand.
- [ ] Commit `feat(f1): dashboard con KPIs + charts + tabla mock`.

### Task 6 — Páginas placeholder navegables

Para cada item del sidebar (salvo Dashboard), crear página Livewire placeholder con:
- Título serif brand
- Empty state ilustrado (SVG simple + texto "Aún no implementado — F2+")
- Breadcrumb correcto

Páginas:
- [ ] `/panel/comentarios` → `Panel\Comments\Index`
- [ ] `/panel/temas` → `Panel\Topics\Index`
- [ ] `/panel/fuentes` → `Panel\Sources\Index`
- [ ] `/panel/chat-rag` → `Panel\RagChat\Index`
- [ ] `/panel/reportes` → `Panel\Reports\Index`
- [ ] `/panel/configuracion` → `Panel\Config\Index` (gated admin)
- [ ] `/panel/usuarios` → `Panel\Users\Index` (gated admin)
- [ ] `/panel/auditoria` → `Panel\Audit\Index`
- [ ] Tests Feature: GET cada ruta como user authenticated → 200; gated admin → 403 para analyst.
- [ ] Commit `feat(f1): páginas placeholder navegables por bounded context`.

### Task 7 — Quality gates F1

- [ ] `composer check` verde (lint + analyse + deptrac + test).
- [ ] Arch tests siguen verdes (Domain no toca Eloquent, etc.).
- [ ] Smoke manual:
  - Register nuevo user → redirige a /panel
  - Login → /panel
  - Sidebar funciona, links navegan
  - Dashboard muestra KPIs + charts visibles
- [ ] Commit final si hay fixes.

### Task 8 — Tag

- [ ] `git tag v0.2.0-F1`.
- [ ] Reportar evidencia.

---

## Notas

- ApexCharts cargado vía CDN o npm (preferir npm install).
- Datos mock generados via seeder o factory para repetibilidad.
- F2 reemplaza mocks con datos reales de ingesta.
- TDD aplicable a Task 2 (use case + listener) y Task 6 (tests rutas). Tasks 3-5 son mayormente UI — verification visual + assertion básico.
- Si Pint reporta fixes nuevos → aplicar + recommit junto.
