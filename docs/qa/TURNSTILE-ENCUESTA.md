# Turnstile para la encuesta pública

Cloudflare Turnstile no está habilitado actualmente. La encuesta usa un honeypot, un tiempo mínimo de cinco segundos, límite de cinco intentos por minuto mediante una clave HMAC de la IP y deduplicación diaria.

Para habilitar Turnstile de forma segura:

1. Crear el widget para el dominio de producción y guardar `TURNSTILE_SITE_KEY` y `TURNSTILE_SECRET` únicamente como secretos del entorno.
2. Renderizar el widget en el formulario y enviar su token con la acción Livewire.
3. Validar el token en el servidor contra `https://challenges.cloudflare.com/turnstile/v0/siteverify`, incluyendo la IP remota solo en tránsito; nunca persistirla.
4. Usar timeout corto, impedir redirecciones y fallar cerrado si la verificación no responde o no devuelve `success=true` para el hostname esperado.
5. Agregar pruebas con respuestas HTTP simuladas para token válido, inválido, expirado, hostname incorrecto y fallo de red antes de activar las claves.

No se deben agregar claves vacías ni una interfaz inerte: la protección se considera activa únicamente cuando la verificación del servidor está desplegada y probada.
