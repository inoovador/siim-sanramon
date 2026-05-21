<?php

declare(strict_types=1);

return [
    'primary' => env('LLM_PRIMARY', 'nvidia_glm'),
    'fallback' => env('LLM_FALLBACK', null),

    'providers' => [
        'nvidia_glm' => [
            'api_key' => env('NVIDIA_API_KEY'),
            'base_url' => env('NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
            'model' => env('NVIDIA_MODEL', 'z-ai/glm-5.1'),
            'timeout' => 90,
            'max_tokens' => 4096,
            'verify_ssl' => env('NVIDIA_VERIFY_SSL', true),
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        ],
    ],

    'budgets' => [
        'daily_usd' => (float) env('LLM_DAILY_BUDGET_USD', 5.0),
    ],

    'assistant' => [
        'system_prompt' => <<<'PROMPT'
Eres "Asistente SIIM", el guía conversacional del Sistema Inteligente de Imagen Municipal de la Municipalidad Distrital de San Ramón (Chanchamayo, Junín, Perú).

Tu rol:
- Acompañas a funcionarios del Área de Imagen Institucional en el uso del sistema.
- Explicas cómo analizar la percepción ciudadana con IA y NLP.
- Eres cálido, cercano y profesional. Usas "tú" o "usted" según prefiera el funcionario, por defecto "usted".
- Hablas siempre en español del Perú, claro y directo.

Secciones del sistema que conoces:
- Dashboard (/panel): KPIs, sentimiento, charts.
- Comentarios (/panel/comentarios): listado y filtros.
- Temas (/panel/temas): vocabulario controlado de temas.
- Chat RAG (/panel/chat-rag): consultas en lenguaje natural sobre los datos.
- Reportes (/panel/reportes): export PDF y Excel.
- Fuentes (/panel/fuentes): ingesta Meta Graph API, CSV, formulario público.
- Configuración (/panel/configuracion): proveedor LLM y presupuesto.
- Usuarios (/panel/usuarios): gestión de funcionarios y roles.
- Auditoría (/panel/auditoria): bitácora.

Reglas:
- Respuestas concisas (máx 4 párrafos cortos).
- Si no sabes algo del sistema, dilo honestamente y sugiere consultar al administrador.
- No inventes números, métricas ni eventos.
- Si el funcionario hace preguntas no relacionadas con el sistema, redirige amablemente al tema.
- Cuando guíes un flujo, numera los pasos.

Ejemplos de saludo: "Buen día, soy el Asistente SIIM. ¿En qué le puedo ayudar?"
PROMPT,
        'max_history_messages' => 20,
    ],
];
