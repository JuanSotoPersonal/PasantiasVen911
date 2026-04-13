---
trigger: glob
---

1. Control de Acceso y Autenticación
Principio de Menor Privilegio: Todo usuario o proceso debe tener únicamente los permisos mínimos necesarios para realizar su función.

Denegación por Defecto: Configura el sistema para que bloquee todo acceso a menos que exista una regla específica que lo permita.

Autenticación Multifactor (MFA): Implementa siempre un segundo factor de verificación para accesos administrativos o sensibles.

Gestión de Sesiones Segura: Genera IDs de sesión complejos, cámbialos tras el login y asegúrate de que expiren tras un periodo de inactividad.

💉 2. Validación y Manejo de Datos
Validar en el Servidor: Nunca confíes en las validaciones hechas solo en el lado del cliente (frontend).

Consultas Parametrizadas: Para prevenir SQL Injection, utiliza siempre sentencias preparadas o un ORM que maneje la limpieza de datos automáticamente.

Codificación de Salida: Antes de mostrar datos proporcionados por usuarios en el navegador, asegúrate de codificarlos para prevenir ataques XSS (Cross-Site Scripting).

🔒 3. Protección de la Información
Cifrado en Reposo y Tránsito: Usa protocolos modernos como TLS 1.3 para datos en movimiento y algoritmos de cifrado fuertes (como AES-256) para datos guardados.

Haseo de Contraseñas: Nunca guardes contraseñas en texto plano. Utiliza algoritmos de hash diseñados para ello, como Argon2 o Bcrypt, con una "sal" (salt) única.

No Exponer Metadatos: Configura el servidor para que no revele versiones de software, tecnologías usadas o errores detallados de la base de datos al usuario final.

⚙️ 4. Configuración y Mantenimiento
Actualización de Dependencias: Revisa regularmente que las librerías, frameworks y el propio servidor no tengan vulnerabilidades conocidas (CVEs).

Seguridad por Diseño: La seguridad no debe ser un parche final, sino una parte integral desde la fase de arquitectura del proyecto.

Hardening del Servidor: Desactiva servicios innecesarios, cambia las credenciales por defecto y cierra los puertos que no se utilicen.

📝 5. Monitoreo y Respuesta
Logging Detallado: Registra fallos de autenticación, cambios de permisos y accesos a datos críticos, pero nunca guardes contraseñas o datos sensibles en los logs.

Alertas en Tiempo Real: Configura notificaciones para actividades sospechosas, como múltiples intentos fallidos de acceso desde una misma IP.

Plan de Respuesta: Ten claridad sobre qué pasos seguir si se detecta una brecha de seguridad para minimizar el impacto.