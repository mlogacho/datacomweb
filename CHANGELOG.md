# Changelog

Todos los cambios notables de este proyecto estarán documentados en este archivo.

## [Unreleased] - 2026-08-05

### Añadido
- **Política de Cookies (LOPDP)**: Implementación de la página oficial de política de cookies requerida por la normativa de Ecuador.
- **Banner de Cookies**: Integración del plugin `cookie-notice` con los colores corporativos, bloqueando cookies no esenciales por defecto hasta el consentimiento del usuario.
- **Forzado de Visualización (Fallback JS)**: Creación de un `mu-plugin` (`force-cookie-notice.php`) para garantizar la aparición del banner en caso de caché estricta o bloqueo de scripts.

### Modificado
- **Bot DAIA (OpenAI)**: 
  - Actualizado el prompt principal en `class-daia-openai.php` para otorgar un tono más amigable, empático y puramente comercial, instando al cliente a compartir sus requerimientos y proyectos de conectividad.
  - Refinamiento de estilos CSS (`daia-chat.css`) y estructura del script JS (`daia-chat.js`) para capturar correctamente mensajes y fallos de API.
- **Entorno Local**: 
  - Limpieza de `.htaccess` y regeneración de enlaces permanentes (`Permalinks`) que impedían el funcionamiento de las peticiones REST (`wp-json`) del bot.
  - Actualizado `docker-compose.yml` (se recomienda eliminar el atributo `version` que está obsoleto en Docker Compose v2).

### Arreglado
- **Redirección Infinita y Errores de Logueo**: Solucionado el conflicto con el plugin de seguridad que ocultaba la URL de login, restaurando acceso normal a `/wp-admin`.
- **Rutas de Plugins**: Sincronización de `site_files/mu-plugins` con el volumen mapeado `datacom-ec/mu-plugins` de Docker para garantizar la ejecución de código custom.

