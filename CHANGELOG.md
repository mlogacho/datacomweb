# Changelog

Todos los cambios notables de este proyecto estarán documentados en este archivo.

## [Unreleased] - 2026-08-11

### Añadido
- **DAIA SMTP Nativo**: Se integraron las credenciales oficiales (`daia@datacom.ec`) directamente en el plugin DAIA a través del hook `phpmailer_init`, garantizando que todos los leads captados lleguen de forma confiable a `info@datacom.ec` (evitando filtros de spam o configuraciones SMTP globales).

### Modificado
- **Bot DAIA (Ajuste de Comportamiento)**: Se actualizaron las instrucciones del sistema (`system_prompt`) para restringir al bot de inventar información y prohibir rotundamente proporcionar datos sensibles de la empresa (correos internos, información de empleados, representante legal).
- **Bot DAIA (Enfoque B2B)**: Se añadió una regla para que el bot rechace cordialmente las consultas sobre "internet residencial" o planes para hogares, explicando que el enfoque exclusivo de DataCom es el sector corporativo y empresarial.

## [Anteriores] - 2026-08-06
- **Bot DAIA (Consentimiento LOPDP)**: Se añadió un checkbox de consentimiento y aviso de privacidad obligatorio antes de iniciar el chat con el bot DAIA.
- **Página y Footer LOPDP**: Implementación de un footer global con enlace a la "Política de Privacidad y Tratamiento de Datos Personales". La página se auto-aprovisiona en entornos de producción.
- **Menú Intranet**: Se agregó un enlace directo a `erp.datacom.ec` ("INTRANET") en el menú de navegación principal de Elementor y WordPress.

### Modificado
- **Estilos de Cabecera (Legacy)**: Se inyectó CSS personalizado mediante un mu-plugin para reemplazar el título de texto por el logo de Datacom (`Recurso 1.png`) y mejorar las proporciones y alineación del tagline, evitando dependencias estrictas de temas en la página LOPDP.
- **Bot DAIA (Seguridad)**: Se removió la validación estricta de Nonce (`X-WP-Nonce`) para la API REST pública, solucionando problemas de conexión en sistemas con caché activado.
- **Banner de Cookies**: Se resolvió una colisión visual (superposición) entre el banner de Cookie Notice y el footer global de LOPDP, aplicando un margen dinámico inferior para que el banner flote por encima del footer.

## [Anteriores] - 2026-08-05### Añadido
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

