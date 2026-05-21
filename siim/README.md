# SIIM — Sistema Inteligente de Imagen Municipal

Plataforma de análisis de percepción ciudadana con IA y NLP para la Municipalidad Distrital de San Ramón (Chanchamayo, Junín, Perú).

> Proyecto SENATI — Ingeniería de Software con IA
> Especificación completa: `../docs/superpowers/specs/2026-05-21-siim-design.md`

## Stack

- PHP 8.2+ / Laravel 11
- Livewire 3.6 + Volt + Tailwind 3
- MariaDB 11 / Redis 7 / Meilisearch
- Pest 3 / PHPStan max / Pint / Deptrac

## Quick start (dev local)

```bash
cd siim
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate

# Levantar servicios (Docker)
docker compose up -d mariadb redis meilisearch

# Migraciones
php artisan migrate

# Servir
php artisan serve
```

Visitar: <http://127.0.0.1:8000>
Health check: <http://127.0.0.1:8000/health>

## Quality gates

Todos estos comandos se ejecutan desde **`siim/`** (no desde el repo root):

```bash
cd siim
composer check          # corre todo: lint + analyse + deptrac + test
composer lint           # Pint fix
composer lint:test      # Pint check
composer analyse        # PHPStan max
composer deptrac        # arquitectura layers + bounded contexts
composer test           # Pest
composer test:arch      # solo arch tests
composer test:cov       # con coverage >= 70%
```

> **Nota**: si ejecutas Pint desde el repo root (`C:\xampp\htdocs\proyecto_finish`), tomará el `pint.json` del directorio `app/` legacy (preset `psr12`). Siempre ejecutar desde `siim/`.

## Arquitectura

Clean Architecture + DDD con 6 bounded contexts:

```
src/
├── Domain/{Identity, Citizen, Ingestion, Analysis, Conversation, Reporting}
├── Application/{...}/{Commands, Queries, UseCases, Contracts}
└── Infrastructure/{Persistence, Llm, Search, ...}
```

Reglas Deptrac:
- `Domain` no depende de nada.
- `Application` solo depende de `Domain`.
- `Infrastructure` implementa puertos de `Application`.
- HTTP layer (`app/Http`) solo conoce `Application`.
- Cross-context: solo vía Domain Events + Application/Shared/Contracts.

## Estado actual

**F0 Scaffold** — completado. Próxima fase: F1 (Identity + Auth + Dashboard base).
