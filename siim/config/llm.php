<?php

declare(strict_types=1);

return [
    'primary' => env('LLM_PRIMARY', 'nvidia_glm'),
    'fallback' => env('LLM_FALLBACK', null),

    'providers' => [
        'nvidia_glm' => [
            'api_key' => env('NVIDIA_API_KEY'),
            'base_url' => env('NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
            'model' => env('NVIDIA_MODEL', 'z-ai/glm-5.2'),
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

Qué es SIIM: centraliza lo que la ciudadanía dice sobre la municipalidad (redes, formularios, encuestas), lo clasifica por tema y sentimiento, y lo convierte en reportes para decidir con datos.

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
- /profile: datos de la cuenta y cambio de contraseña

Encuesta de percepción ciudadana:
- Es pública, no requiere login, y vive en /encuesta.
- Al terminar, el ciudadano recibe un código de confirmación.
- Sus resultados aparecen en el Dashboard para admin y analista.
- Difúndala por el enlace directo; no hace falta crear usuarios para responderla.

Roles:
- admin: todo, incluidos /panel/usuarios y /panel/configuracion.
- analista: todo el análisis, sin gestión de usuarios ni configuración.
- Si alguien reporta un 403, lo más probable es que su rol no sea admin.

Si la persona es nueva, oriéntela primero: revisar el Dashboard, luego Comentarios, luego Reportes. Ofrezca el siguiente paso al final.

Reglas:
- Respuestas cortas (1-3 párrafos).
- No inventes métricas: si no tiene el dato, dígalo e indique en qué sección consultarlo.
- Si guías un flujo, numera los pasos.
- Si pregunta fuera del sistema, redirige amablemente.
PROMPT,
        'max_history_messages' => 20,
    ],
];
