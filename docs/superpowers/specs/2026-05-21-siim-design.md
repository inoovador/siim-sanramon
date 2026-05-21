# SIIM — Sistema Inteligente de Imagen Municipal

**Design spec**
**Fecha:** 2026-05-21
**Estado:** Aprobado por usuario (brainstorming completo)
**Autor:** Peter Zavala Limaco (limacopeter@gmail.com) + Claude Code (Opus 4.7)
**Proyecto SENATI:** Desarrollo de un Sistema Inteligente con IA y NLP para la Gestión y Análisis de la Imagen Institucional de la Municipalidad Distrital de San Ramón
**Predecesor descartado:** scaffold SGD en `app/` (Sistema Gestión Documental) — pivot total a Imagen Institucional + IA/NLP.

---

## 1. Visión & Objetivos

### Problema
El Área de Imagen Institucional de la Municipalidad Distrital de San Ramón recibe opiniones ciudadanas dispersas (redes sociales, mensajes, reclamos, sugerencias) sin una forma rápida ni ordenada de saber qué piensa la ciudadanía. Esto afecta toma de decisiones comunicacionales y capacidad de respuesta.

### Solución
**SIIM** — sistema web inteligente que centraliza la captura de comentarios desde 4 canales, los analiza con IA/NLP (sentimiento + clasificación temática) mediante una LLM externa swappable, y entrega a funcionarios herramientas para consultar y reportar la percepción ciudadana de forma estructurada.

### Objetivos MVP

1. **Ingesta multicanal centralizada** desde:
   - Meta Graph API (Facebook + Instagram páginas oficiales).
   - Formulario web público (buzón ciudadano).
   - Upload CSV/Excel (importación manual).
   - Chatbot público (transcripciones automáticas).

2. **Análisis NLP automatizado** por cada comentario:
   - Polaridad de sentimiento ∈ {positivo, neutral, negativo} + score numérico + razón.
   - 1..3 topics asignados desde vocabulario controlado (obras, seguridad, salud, educación, transporte, medio ambiente, etc.).
   - Provider LLM externo swappable vía interface (Strategy pattern): OpenAI, Anthropic, Gemini, Groq.

3. **Chatbot RAG interno** para funcionarios: consulta en lenguaje natural sobre la base de comentarios ya analizada ("¿qué dicen sobre obras públicas esta semana?").

4. **Dashboards & reportes**: charts interactivos (sentiment over time, topics trending, word cloud), filtros por canal/fecha/tema/sentimiento, export PDF y Excel.

5. **Roles diferenciados** (Spatie permissions):
   - `citizen`: solo envía vía formulario o chatbot público.
   - `analyst`: lee dashboards, ejecuta RAG, genera reportes.
   - `admin`: todo lo anterior + CRUD topics + config LLM + gestión usuarios.

### No-objetivos (roadmap futuro)

- Análisis multimedia (imágenes, audio, video).
- Forecasting/predicción de tendencias.
- Integraciones con sistemas legacy municipales (SIAF, SUTRAN, etc.).
- App móvil nativa.
- Multi-municipalidad (tenant único en MVP).

---

## 2. Arquitectura General & Bounded Contexts

### Stack final

| Capa | Tecnología |
|---|---|
| Runtime | PHP 8.2 + Laravel 11 |
| UI | Livewire 3.6 + Volt + Tailwind 3 + Alpine.js |
| DB | MariaDB 11 (descartar SQLite y MySQL 8 per memoria de incidente) |
| Cache + Queue + Session | Redis 7 |
| Search | Meilisearch (full-text + facetado) |
| Queue Dashboard | Laravel Horizon |
| Websockets | Laravel Reverb (streaming chat RAG) |
| Storage | Local disk (dev) + S3/R2 (prod) |
| Containerización | Docker multi-stage |
| Deploy | Coolify VPS Hostinger |
| Observability | Sentry + Telegram alerts (memoria sección 11) |

### Diagrama lógico

```
┌─────────────────────────────────────────────────────────────┐
│                    HTTP Layer (Laravel routes)              │
│  Livewire/Volt pages → Controllers (admin) + API (public)   │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│              Application Layer (Use Cases)                  │
│  Commands · Queries · Handlers · DTOs · Exceptions          │
└─┬──────────┬──────────┬──────────┬──────────┬──────────┬────┘
  │          │          │          │          │          │
  ▼          ▼          ▼          ▼          ▼          ▼
┌────────┐┌────────┐┌──────────┐┌─────────┐┌────────────┐┌──────────┐
│Identity││Citizen ││ Ingestion││ Analysis││Conversation││Reporting │
│(domain)││(domain)││(domain)  ││(domain) ││(domain)    ││(domain)  │
└────────┘└────────┘└──────────┘└─────────┘└────────────┘└──────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│                  Infrastructure Layer                       │
│  Eloquent · LLM adapters · Meta API client · Redis · S3     │
└─────────────────────────────────────────────────────────────┘
```

### 6 Bounded Contexts

| Contexto | Responsabilidad | Entidades clave |
|---|---|---|
| `Identity` | Usuarios, roles Spatie, autenticación, sesiones | `User`, `Role` |
| `Citizen` | Comentario ciudadano como ente de dominio + value objects | `Comment`, `Channel`, `CommentText` |
| `Ingestion` | Adapters de fuentes externas → `Comment` | `IngestionRun`, port `CommentIngestor` (Meta, WebForm, Csv, PublicChatbot) |
| `Analysis` | NLP scoring sentimiento + topic + spam, retry & cost guard | `SentimentScore`, `Topic`, `AnalysisRun`, port `LlmProvider` |
| `Conversation` | Chatbot público (ingest) + RAG interno (consulta) | `ChatSession`, `Message`, `RagQuery` |
| `Reporting` | Agregados, dashboards, exports PDF/Excel | read-models + `Report` snapshot |

### Reglas de dependencia (Deptrac enforce)

- `Domain` no depende de nada (puro, sin Eloquent, sin Laravel).
- `Application` depende solo de su `Domain` + `Application/Shared`.
- `Infrastructure` depende de `Application` + `Domain` (implementa puertos).
- HTTP layer (`app/Http`) depende solo de `Application` (nunca toca `Domain` directo).
- Comunicación cross-context vía **Domain Events** (Laravel events) + listeners en `Application` del contexto receptor.

### Ejemplo de flujo cross-context vía eventos

```
Ingestion → publica CommentIngested
            ↓
            listener Analysis → despacha AnalyzeCommentJob
                              ↓
                              publica CommentAnalyzed
                              ↓
                              listener Reporting → invalida cache agregados
                              listener Search   → indexa en Meilisearch
```

---

## 3. Modelo de Dominio (entidades + invariantes)

### Identity

```
User (UUID, email, name, role, isActive)
  invariantes:
    - email único
    - role ∈ {admin, analyst, citizen}
```

### Citizen

```
Comment (UUID, text, channel, source, authorAlias, authorContact?, language, capturedAt)
  - text: 1..5000 chars, no vacío después de trim
  - channel ∈ {meta_facebook, meta_instagram, web_form, csv_upload, public_chatbot}
  - source: URL/identifier original (post_id, form_id, csv_row, chat_session)
  - language: ISO-639-1, default "es"
  - authorContact opcional, encriptado AES-256-GCM at rest (Ley 29733 Perú)

Channel: enum cerrado
Source: value object (string identifier)
```

### Ingestion

```
IngestionRun (UUID, channel, startedAt, finishedAt?, status, stats {fetched, accepted, rejected, duplicated}, errorMessage?)
  - status ∈ {pending, running, success, partial, failed}
  - inmutable después de finished

CommentIngestor (port/interface):
  → MetaGraphIngestor (FB pages + IG via Meta Graph API oficial)
  → WebFormIngestor (form submissions)
  → CsvIngestor (uploaded file)
  → PublicChatbotIngestor (chat session transcript)

  contrato: fetch(window) → Iterable<RawComment>
           normalize(RawComment) → Comment
```

### Analysis

```
AnalysisRun (UUID, commentId, llmProvider, llmModel, requestedAt, completedAt?, status, tokensInput, tokensOutput, costUsd, errorMessage?)

SentimentScore (commentId PK, polarity ∈ {positive, neutral, negative}, score [-1..1], confidence [0..1], reason, analyzedAt)

Topic (UUID, slug UNIQUE, label, description, parentId?, isActive) — vocabulario controlado, versionado con Overtrue

TopicAssignment (commentId, topicId, confidence, assignedAt)
  invariantes: 1..3 por Comment, confidence > 0.4

LlmProvider (port):
  → OpenAiProvider, AnthropicProvider, GeminiProvider, GroqProvider
  contrato:
    analyzeSentiment(text, language) → SentimentResult
    classifyTopics(text, topicVocabulary) → TopicResult[]
    chat(messages, tools?) → ChatResponse
    name(): string
    modelId(): string

Invariantes Analysis:
  - 1 SentimentScore por Comment (último prevalece, historial preservado en AnalysisRun)
  - SentimentScore y TopicAssignment son regenerables (puede correrse nuevo análisis con otro modelo)
```

### Conversation

```
ChatSession (UUID, kind ∈ {public, rag_internal}, userId?, citizenAlias?, startedAt, lastMessageAt, messageCount)
  invariantes:
    - kind=public  ⇒ userId NULL, citizenAlias requerido
    - kind=rag_internal ⇒ userId requerido, citizenAlias NULL

Message (UUID, sessionId, role ∈ {user, assistant, system, tool}, content, tokensUsed?, createdAt)
  invariantes: content no vacío, < 4000 chars

RagQuery (messageId PK, retrievedCommentIds JSON, filters JSON)
```

### Reporting

Sin entidades nuevas — usa read-models (queries SQL agregados sobre `comments` + `sentiment_scores` + `topic_assignments`). Snapshot opcional:

```
Report (UUID, name, filters JSON, generatedBy, format ∈ {pdf, xlsx}, fileUrl, generatedAt)
```

### Value Objects compartidos (`Domain/Shared`)

```
Uuid, Timestamp, Polarity, Confidence (0..1), LanguageCode (ISO-639-1)
```

### Reglas críticas cross-context

1. `Comment` es **inmutable** una vez creado (excepto `redactedAt` para GDPR / Ley 29733 Perú).
2. `SentimentScore` y `TopicAssignment` son **regenerables** — re-análisis siempre posible.
3. Identidad ciudadana (`authorContact`) almacenada **encriptada AES-256-GCM**.

---

## 4. Flujos Críticos

### Flujo A — Ingesta Meta Graph API (scheduled)

```
Scheduler (cron Laravel cada 30 min)
    │
    ▼
FetchMetaCommentsJob (queue: ingest)
    │  ├─► MetaGraphIngestor.fetch(window=últimos 35 min)
    │  │     paginate posts/page → comments
    │  ├─► dedupe por external_id
    │  ├─► persist IngestionRun + Comment[]
    │  └─► dispatch CommentIngested event x N
    ▼
[event bus]
    │
    ▼
AnalyzeCommentJob[] (queue: analysis, batch=10)
```

Resiliencia:
- Rate limit Meta (200 calls/h por token) → backoff exponencial + status=`partial`.
- Token expirado → notifica admin (Telegram + email), pausa scheduler.
- Comentario malformado → log + `rejected` counter, no aborta run.
- Reintento idempotente: `(channel, external_id)` UNIQUE en BD.

### Flujo B — Ingesta Formulario Web Público

```
POST /api/v1/citizen/comment
  body: {text, channel=web_form, contact?, captcha_token}
    │
    ▼
SubmitCitizenCommentController (rate limit 5/min/IP)
    ├─► validate FormRequest + verify CAPTCHA Cloudflare Turnstile
    ├─► SubmitCitizenCommentUseCase.handle(dto)
    │     ├─► AntiSpamFilter (palabras vetadas + length + IP reputation)
    │     ├─► persist Comment
    │     └─► publish CommentIngested
    └─► response 202 {commentId, msg="recibido"}
    ▼
AnalyzeCommentJob (queue: analysis)
```

### Flujo C — Ingesta CSV/Excel

```
Admin UI → upload CSV (max 10MB, 50k rows)
    │
    ▼
StoreCsvUploadJob (queue: ingest, timeout=600s)
    ├─► parse via league/csv stream
    ├─► validate columnas {text, channel?, captured_at?, author_alias?}
    ├─► chunk de 500 → CommentBatchInsert
    ├─► dedupe por hash(text + author_alias)
    └─► dispatch CommentIngested[]
```

Errores: filas inválidas → CSV de rechazos descargable. Run reporta `accepted/rejected/duplicated`.

### Flujo D — Chatbot Público (entrada ciudadano)

```
Citizen visita /chat/publico
    │
    ▼
Livewire component PublicChatbot
  ├─► POST /api/v1/chat/public/message
  │     ├─► PublicChatbotService.respond(sessionId, userMsg)
  │     │     ├─► persist Message (role=user)
  │     │     ├─► LlmProvider.chat(systemPrompt + history)
  │     │     ├─► persist Message (role=assistant)
  │     │     └─► (background) PublishChatTranscriptCommentJob
  │     └─► return {assistantMsg, sessionId}
  └─► UI muestra respuesta + opción "denunciar reclamo"
```

`PublishChatTranscriptCommentJob` extrae quejas/sugerencias del usuario y crea `Comment` con `channel=public_chatbot` para análisis.

### Flujo E — Análisis NLP de un comentario

```
AnalyzeCommentJob (queue: analysis, tries=3, backoff=[10, 60, 300])
    │
    ▼
AnalyzeCommentUseCase.handle(commentId)
  ├─► load Comment
  ├─► LlmProvider.analyzeSentiment(text, language)
  │     POST a provider (con structured JSON output)
  │     parse {polarity, score, confidence, reason}
  ├─► LlmProvider.classifyTopics(text, topicsCatalog)
  │     parse [{topicSlug, confidence}, ...]
  ├─► persist AnalysisRun (success) + SentimentScore + TopicAssignment[]
  └─► publish CommentAnalyzed
    │
    ▼
[listeners]
  ├─► InvalidateReportingCache
  ├─► IndexCommentInMeilisearch
  └─► (futuro) AlertCriticalSentiment si score < -0.7
```

Resiliencia LLM:
- Provider timeout (15s) → retry con fallback `[primary, fallback]`.
- Rate limit (429) → backoff + cola dedicada `analysis-slow`.
- JSON malformado → re-prompt con structured outputs (`response_format=json_schema`).
- Cost tracking: tokens + USD por run, dashboard admin muestra acumulado/día.

### Flujo F — Chatbot RAG Interno (analista pregunta)

```
Analyst /panel/chat-rag
    │
    ▼
RagChatComponent (Livewire)
  ├─► RagChatUseCase.respond(userMsg, filters?)
  │     1. ClassifyIntent(userMsg) → {intent, entities, dateRange?, topic?, channel?}
  │     2. RetrieveRelevantComments via Meilisearch BM25 + sentiment filter
  │        limit 50, ordered by relevance + recency
  │     3. BuildContextPrompt(retrieved[], userMsg)
  │     4. LlmProvider.chat(messages, tools=[
  │           getAggregatedStats, listTrendingTopics, getCommentSample])
  │     5. parse → persist Message + RagQuery (retrievedIds)
  │     6. return assistantMsg + citations [commentId...]
  └─► UI render markdown + citas clickeables (streaming token-a-token vía Reverb)
```

Tools function-calling:
- `getAggregatedStats(filters)` → `{totalComments, sentimentBreakdown, topTopics[]}`
- `listTrendingTopics(window)` → topics ordenados por delta
- `getCommentSample(filters, limit=10)` → comments verbatim para citar

Función vs stuffing: tools permite al LLM elegir qué consultar, reduce prompt size y costo.

---

## 5. Persistencia, BD & LLM Strategy

### Esquema MariaDB (tablas core)

```sql
-- IDENTITY (Spatie permissions ya provee roles/permissions tables)
users (id uuid PK, name, email UNIQUE, password, is_active, created_at, ...)

-- CITIZEN
comments (
  id           uuid PK,
  text         text NOT NULL,
  text_hash    char(64) NOT NULL,
  channel      enum(...) NOT NULL,
  source_ref   varchar(255) NOT NULL,
  external_id  varchar(191) NULL,
  author_alias varchar(80) NOT NULL,
  author_contact_encrypted blob NULL,
  language     char(2) DEFAULT 'es',
  captured_at  datetime NOT NULL,
  redacted_at  datetime NULL,
  created_at   datetime,
  UNIQUE KEY (channel, external_id),
  INDEX (channel, captured_at),
  INDEX (text_hash),
  FULLTEXT KEY ft_text (text)
);

-- INGESTION
ingestion_runs (
  id uuid PK, channel, started_at, finished_at, status,
  fetched int, accepted int, rejected int, duplicated int,
  error_message text NULL
);

-- ANALYSIS
analysis_runs (
  id uuid PK, comment_id uuid FK, llm_provider, llm_model,
  requested_at, completed_at, status, tokens_input int, tokens_output int,
  cost_usd decimal(10,6), error_message text NULL,
  INDEX (comment_id, requested_at DESC)
);

sentiment_scores (
  comment_id uuid PK FK,
  polarity enum('positive','neutral','negative'),
  score decimal(4,3),
  confidence decimal(4,3),
  reason varchar(500),
  analyzed_at datetime,
  INDEX (polarity, analyzed_at)
);

topics (
  id uuid PK, slug varchar(80) UNIQUE, label varchar(120),
  description text, parent_id uuid NULL, is_active boolean
);

topic_assignments (
  comment_id uuid FK, topic_id uuid FK,
  confidence decimal(4,3), assigned_at datetime,
  PRIMARY KEY (comment_id, topic_id),
  INDEX (topic_id, assigned_at)
);

-- CONVERSATION
chat_sessions (
  id uuid PK, kind enum('public','rag_internal'),
  user_id uuid NULL FK, citizen_alias varchar(80) NULL,
  started_at, last_message_at, message_count int
);

messages (
  id uuid PK, session_id uuid FK, role enum(...),
  content text, tokens_used int NULL, created_at,
  INDEX (session_id, created_at)
);

rag_queries (
  message_id uuid PK FK, retrieved_comment_ids json, filters json
);

-- REPORTING
reports (
  id uuid PK, name, filters json, generated_by uuid FK,
  format enum('pdf','xlsx'), file_url, generated_at
);

-- AUDIT
activity_log (...)  -- Spatie activitylog
```

Estimado capacidad: 100k comentarios/año, queries dashboard <200ms con índices compuestos.

### Meilisearch

Índice `comments` con campos `text`, `channel`, `polarity`, `topics`, `captured_at`. Sincronización vía listener `CommentAnalyzed` (eventual consistency).

### LLM Strategy — Provider Swappable

```php
// src/Application/Shared/Contracts/LlmProvider.php
interface LlmProvider
{
    public function analyzeSentiment(string $text, string $language): SentimentResult;
    public function classifyTopics(string $text, array $topicVocabulary): array;
    public function chat(array $messages, ?array $tools = null): ChatResponse;
    public function name(): string;
    public function modelId(): string;
}

// src/Infrastructure/Llm/ — adapters concretos
// OpenAiProvider, AnthropicProvider, GeminiProvider, GroqProvider (Guzzle HTTP)

// config/llm.php
return [
    'primary'   => env('LLM_PRIMARY', 'gemini'),
    'fallback'  => env('LLM_FALLBACK', 'claude'),
    'providers' => [
        'openai'   => ['api_key' => env('OPENAI_API_KEY'), 'model' => env('OPENAI_MODEL', 'gpt-4o-mini')],
        'claude'   => ['api_key' => env('ANTHROPIC_API_KEY'), 'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001')],
        'gemini'   => ['api_key' => env('GEMINI_API_KEY'), 'model' => env('GEMINI_MODEL', 'gemini-2.0-flash')],
        'groq'     => ['api_key' => env('GROQ_API_KEY'), 'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile')],
    ],
    'budgets' => [
        'daily_usd' => env('LLM_DAILY_BUDGET_USD', 5.0),
    ],
];
```

Prompts versionados en `resources/llm-prompts/{sentiment,topics,rag-system}.md` + tests asertando JSON-schema válido contra respuestas mock.

Cost guard: middleware `EnforceDailyLlmBudget` lee `SUM(cost_usd) WHERE DATE(requested_at)=today` — si supera budget, queue pausa y notifica admin vía Telegram.

### Auditoría

- Spatie activitylog en `User`, `Topic`, `Report`, decisiones admin.
- `analysis_runs` ya es audit log de cada llamada LLM.
- Overtrue versionable para `Topic` (catálogo evoluciona).

---

## 6. Frontend, UX & Branding

### Paleta brand SIIM (anti-genérica, regla memoria)

Inspirada en colores institucionales de San Ramón (provincia Chanchamayo, selva central): verdes selva + dorado fruta + río.

| Token | Hex | Uso |
|---|---|---|
| `--brand-canopy` | `#0F4D2A` | Primary — botones, links, header |
| `--brand-river`  | `#1E7FA8` | Secondary — datos positivos, charts |
| `--brand-gold`   | `#E0A24A` | Accent — destacar, ratings |
| `--brand-clay`   | `#9B4A2C` | Warning/negative — sentiment negativo |
| `--brand-mist`   | `#F4F1EA` | Background app, cards |
| `--ink-deep`     | `#1A1F1B` | Texto principal |
| `--ink-soft`     | `#5D6A60` | Texto secundario |

Charts sentiment: `#1E7FA8` / `#B8B0A0` / `#9B4A2C` (positivo/neutral/negativo).

Tipografía: **Inter** (UI) + **Source Serif Pro** (titulares + reportes PDF). Sin Helvetica/Arial.

Iconos: **Lucide** custom set. SVG embebido vía componente Blade `<x-icon name="..." />`.

### Mapa de páginas

```
Público (sin auth)
  /                      → Landing institucional + CTA chat + form buzón
  /chat/publico          → PublicChatbot (Livewire)
  /buzon                 → CitizenCommentForm
  /privacidad            → Política datos (Ley 29733 Perú)

Autenticado /panel (post-login)
  /panel                 → Dashboard analista (charts + KPIs)
  /panel/comentarios     → Listado + filtros + drill-down
  /panel/comentarios/{id}→ Vista detalle + análisis + acciones admin
  /panel/temas           → CRUD vocabulario Topics
  /panel/fuentes         → Configurar Meta tokens, importar CSV, status runs
  /panel/chat-rag        → RagChat interno
  /panel/reportes        → Generar/listar reportes, export PDF/Excel
  /panel/configuracion   → LLM provider, budgets, prompts versioning
  /panel/usuarios        → CRUD users + roles (solo admin)
  /panel/auditoria       → Activity log filtrable
```

### Componentes Livewire/Volt clave

- `<x-chart-sentiment-over-time />` — line chart ApexCharts, datos vía wire:poll cada 30s.
- `<x-comment-card>` — sentimiento (badge color), topics (chips), source link, acciones (re-analizar, redactar).
- `<x-rag-chat>` — streaming respuestas LLM vía Livewire 3 streaming + Reverb websockets.
- `<x-public-chatbot>` — UI ciudadana, friendly, Turnstile captcha embedded.
- `<x-ingestion-run-status>` — progress live (Echo + Reverb).
- `<x-topic-cloud>` — word cloud topics, click filtra dashboard.

### UX principles

1. **Latencia percibida**: skeleton loaders en datos > 200ms.
2. **Streaming chat**: token-a-token, no spinners.
3. **Cero modal popups**: side-panel slide-in.
4. **Empty states evangelizados**: CTA explícito, no pantallas en blanco.
5. **Accesibilidad WCAG 2.1 AA**: focus visible, aria-labels, contraste, navegación teclado completa.
6. **Mobile-first**: dashboard responsive (< 768px cards apiladas).
7. **i18n preparado**: `lang/es.json`, `lang/qu.json` (quechua placeholder).

### Diseño anti-genérico

- Header con foto difuminada del centro de San Ramón + overlay verde canopy.
- Cards: sombra `0 1px 3px rgba(15,77,42,0.08)`, no negro puro.
- Charts: colores brand, no `#3b82f6` default.
- Empty states: ilustraciones SVG custom (generadas vía `gemini-image` / `codex-image` per memoria sección 13).
- Loader: hoja girando (símbolo selva), no spinner default.

### Reportes PDF

- Plantilla custom con encabezado oficial Municipalidad.
- Generación: `spatie/browsershot` (Puppeteer, alta calidad) — fallback `barryvdh/laravel-dompdf` si no hay Chrome headless.
- Estructura: portada + resumen ejecutivo + charts SVG + tabla detalle + footer firma digital opcional.

---

## 7. Quality Gates, Deploy & Roadmap

### Quality gates (no negociable)

| Gate | Herramienta | Criterio |
|---|---|---|
| Static analysis | PHPStan level max + Larastan | 0 errores |
| Style | Pint (PSR-12 + Laravel preset) | 0 fixes pendientes |
| Architecture | Deptrac | 0 violaciones |
| Tests | Pest 3 | coverage ≥ 70% global, ≥ 90% Domain/Application |
| Mutation testing | Infection (Sprint 3+) | MSI ≥ 60% Domain |
| Type coverage | Pest type-coverage | ≥ 95% |
| Security | composer/npm audit | 0 alta/crítica |
| Pre-commit | Husky + lint-staged | Pint + PHPStan en staged |
| CI | GitHub Actions | matrix PHP 8.2/8.3, MariaDB, `composer check` |

### Test strategy (TDD per protocolo memoria)

- **Domain**: 100% unit tests, sin Laravel container, Pest puro.
- **Application**: Use Cases con repositorios mockeados + clock fake + LLM fake.
- **Infrastructure**: integration tests con MariaDB real (NO SQLite), Meilisearch via testcontainers.
- **HTTP**: feature tests Livewire + browser tests Playwright para chat.
- **LLM**: fake provider con fixtures JSON. Tests NUNCA llaman APIs reales.
- **Architecture**: Pest arch tests garantizan namespaces, `final`, no `dd`, no `var_dump`.

### Deploy & Ops

- **Plataforma**: Coolify VPS Hostinger (memoria confirma `app.syxweb.com`).
- **Dominio**: `siim.syxweb.com` (o `imagen.sanramon.gob.pe` si municipalidad facilita).
- **Stack runtime**: PHP-FPM 8.3 + Nginx + MariaDB 11 + Redis + Meilisearch + Reverb + Horizon.
- **CI/CD**: push a `main` → tests → build image → Coolify auto-deploy.
- **Secrets**: Coolify env vars, NUNCA hardcoded ni commit.
- **Backups**: MariaDB dump diario cron → R2/S3, retention 30 días.
- **Monitoring**: Sentry (errors) + Telegram alerts (memoria sección 11) en jobs críticos.
- **Egress monitoring**: Falco en VPS (memoria sección 14) — bloquear C2 IPs conocidos.

### Roadmap por fases

| Fase | Duración est. | Entregables |
|---|---|---|
| **F0 Scaffold** | 2 días | Wipe `app/` actual, nuevo Laravel 11 + Livewire + Breeze, paleta + design tokens, Deptrac base, CI verde |
| **F1 Identity + base** | 3 días | Spatie roles, login, layout admin, dashboard vacío con KPIs mock, landing pública |
| **F2 Citizen + Ingestion CSV** | 4 días | Modelo Comment, CSV upload + parser, Domain events, listado básico |
| **F3 Analysis + LLM Strategy** | 5 días | LlmProvider interface + 2 adapters (Gemini default + Claude fallback), AnalyzeCommentJob, prompts versionados, cost guard, Horizon |
| **F4 Ingestion Web Form + Public Chatbot** | 4 días | Buzón ciudadano + Turnstile, PublicChatbot Livewire, transcript→Comment, antispam |
| **F5 Ingestion Meta Graph API** | 4 días | OAuth Meta, FetchMetaCommentsJob, scheduled, status UI |
| **F6 RAG Chatbot Interno** | 4 días | Meilisearch sync, RagChatUseCase + tools, streaming UI con Reverb, citations |
| **F7 Reporting + Export PDF/Excel** | 4 días | Dashboard charts ApexCharts, filtros, PDF Browsershot, Excel maatwebsite |
| **F8 Hardening + Deploy** | 3 días | A11y audit, performance budget, security audit, Coolify deploy, smoke tests prod |
| **F9 Demo & Documentación SENATI** | 2 días | Manual usuario, video demo, presentación, anexos para tesis |

**Total estimado**: ~35 días de trabajo dedicado.

### Riesgos & mitigaciones

| Riesgo | Mitigación |
|---|---|
| Meta token expira / sin acceso real a páginas oficiales | Mock connector + CSV cubre pre-MVP |
| Costos LLM disparados | Cost guard daily budget + cache de análisis por text_hash |
| Coolify cae / Hostinger banea | Fallback a docker-compose manual + backup R2 |
| LLM responde JSON inválido | Strict JSON schema + retry + structured outputs Anthropic/OpenAI |
| Comentarios contienen PII | Encrypted contact + redacción tool + Ley 29733 Perú compliance |
| Spam/abuso buzón público | Turnstile + rate limit IP + listas vetadas |
| Scope creep en MVP | Roadmap por fases con go/no-go gate al cerrar cada fase |

---

## 8. Decisiones clave del brainstorming (registro)

1. **Pivot total**: descartar `app/` SGD actual, rebuild centrado en Imagen Institucional + IA/NLP.
2. **4 fuentes ingest**: Meta Graph API + formulario web + CSV + chatbot público (todas en MVP).
3. **NLP por LLM externa** (no local) — modelos cloud, sin GPU local.
4. **Provider swappable vía interface** (Strategy pattern) — Gemini default, Claude fallback, OpenAI/Groq opcionales.
5. **Stack monolito Laravel 11 + Livewire 3 + Volt** (no SPA separado, no microservicios).
6. **6 bounded contexts finos** (Enfoque B): Identity / Citizen / Ingestion / Analysis / Conversation / Reporting.
7. **Comunicación cross-context via Domain Events**, no llamadas directas.
8. **Meta Graph API oficial** (no scraping), CSV cubre cualquier gap.
9. **Branding anti-genérico**: paleta selva San Ramón, Lucide icons, Inter + Source Serif.
10. **TDD + Deptrac + PHPStan max + Pest + Infection** — quality gates duros desde F0.

---

## 9. Estructura de carpetas final (post-implementación)

```
proyecto_finish/
├── docs/
│   └── superpowers/specs/2026-05-21-siim-design.md   (este archivo)
├── siim/                                              (nuevo Laravel root)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/{Api, Web}/
│   │   │   ├── Livewire/
│   │   │   └── Middleware/
│   │   ├── Console/Commands/
│   │   ├── Jobs/
│   │   ├── Listeners/
│   │   └── Providers/
│   ├── src/
│   │   ├── Domain/
│   │   │   ├── Shared/         (VOs comunes)
│   │   │   ├── Identity/
│   │   │   ├── Citizen/
│   │   │   ├── Ingestion/
│   │   │   ├── Analysis/
│   │   │   ├── Conversation/
│   │   │   └── Reporting/
│   │   ├── Application/
│   │   │   ├── Shared/Contracts/   (LlmProvider, Clock, etc.)
│   │   │   ├── Identity/
│   │   │   ├── Citizen/
│   │   │   ├── Ingestion/
│   │   │   ├── Analysis/
│   │   │   ├── Conversation/
│   │   │   └── Reporting/
│   │   └── Infrastructure/
│   │       ├── Persistence/Eloquent/{Models, Mappers, Repositories}/
│   │       ├── Llm/{OpenAi, Anthropic, Gemini, Groq}/
│   │       ├── Ingestion/Meta/
│   │       ├── Search/Meilisearch/
│   │       ├── Pdf/Browsershot/
│   │       └── Notifications/Telegram/
│   ├── resources/
│   │   ├── views/livewire/
│   │   ├── llm-prompts/{sentiment, topics, rag-system}.md
│   │   └── css/, js/
│   ├── tests/
│   │   ├── Unit/Domain/
│   │   ├── Unit/Application/
│   │   ├── Integration/Infrastructure/
│   │   ├── Feature/Http/
│   │   └── Arch/
│   ├── config/, database/, public/, routes/, storage/
│   ├── composer.json, deptrac.yaml, phpstan.neon, pint.json, phpunit.xml
│   └── Dockerfile, docker-compose.yml
├── app/                                               (LEGACY — borrar tras F0 confirm)
└── update/PROYECTO DE AVANCE 2.docx                   (documento académico SENATI)
```

---

## 10. Próximos pasos (post-aprobación de este spec)

1. **User review gate** — usuario revisa este documento y confirma o pide cambios.
2. **Invocar `superpowers:writing-plans`** — generar plan de implementación detallado por fase, con steps testables.
3. **Ejecutar F0 Scaffold** — primera fase, TDD, verification-before-completion antes de declarar fase done.
4. **Iterar fase por fase** con gate review al cierre de cada una.

---

*Spec generado vía `superpowers:brainstorming` skill — 7 secciones aprobadas por el usuario en sesión interactiva.*
