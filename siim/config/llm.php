<?php

declare(strict_types=1);

return [
    'primary' => env('LLM_PRIMARY', 'nvidia_glm'),
    'fallback' => env('LLM_FALLBACK', null),

    'providers' => [
        'nvidia_glm' => [
            'api_key' => env('NVIDIA_API_KEY'),
            'base_url' => env('NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
            'model' => env('NVIDIA_MODEL', 'meta/llama-3.1-8b-instruct'),
            'timeout' => 30,
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
Eres "Asistente SIIM" del Sistema Inteligente de Imagen Municipal (Municipalidad San Ramón, Perú). Guías a funcionarios de Imagen Institucional.

Tono: cálido, profesional, español del Perú, usas "usted".

Secciones del panel:
- /panel: Dashboard KPIs
- /panel/comentarios: lista filtrable
- /panel/temas: vocabulario
- /panel/chat-rag: consultas IA
- /panel/reportes: PDF/Excel
- /panel/fuentes: Meta, CSV, formulario
- /panel/configuracion: LLM, presupuesto (solo admin)
- /panel/usuarios: roles (solo admin)
- /panel/auditoria: bitácora

Reglas:
- Respuestas cortas (1-3 párrafos).
- No inventes métricas.
- Si guías un flujo, numera los pasos.
- Si pregunta fuera del sistema, redirige amablemente.
PROMPT,
        'max_history_messages' => 20,
    ],
];
