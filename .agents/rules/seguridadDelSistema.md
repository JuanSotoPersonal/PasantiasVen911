---
trigger: glob
---

1. Control de Acceso y Autenticación
Principio de Menor Privilegio: Todo usuario o proceso debe tener únicamente los permisos mínimos necesarios para realizar su función (Validar siempre sesión y rol mediante RBAC).

Denegación por Defecto: Configura el sistema para que bloquee todo acceso a menos que exista una regla específica que lo permita.

Gestión de Sesiones Segura: Genera IDs de sesión complejos, usa `session_regenerate_id(true)` tras el login y registra los eventos de auditoría (LOGIN/LOGOUT).

💉 2. Validación y Manejo de Datos (Frontera Estricta)
Validación en el Servidor (INERCIA CERO): Todo el peso de las validaciones recae absolutamente sobre el lado del Servidor. NUNCA apliques validaciones nativas obstructivas en el Frontend (como atributos HTML `minlength`, `maxlength` o `required`). 

Uso de Helpers Centralizados: Delegar todas las comprobaciones de integridad (usuario, cédula, contraseñas) al ayudante estático `App\Helpers\Validador` antes de procesar los datos contra Modelo.

Consultas Parametrizadas: Para prevenir SQL Injection (Inyección SQL), utiliza siempre sentencias preparadas de PDO (`$stmt->prepare()` y `bindParam()`).

Codificación de Salida Visual (XSS): Antes de mostrar cualquier campo de la base de datos en las vistas o en las tablas de JSON (DataTables), asegúrate SIEMPRE de pasarlo por `window.escapeHTML()` para inactivar posibles inyecciones (Cross-Site Scripting).

🔒 3. Protección de la Información
Cifrado en Reposo y Tránsito: Usa protocolos modernos para datos en movimiento y algoritmos de cifrado fuertes.

Haseo de Contraseñas: Nunca guardes contraseñas ni preguntas de seguridad en texto plano. Utiliza SIEMPRE `password_hash()` con PASSWORD_DEFAULT.

No Exponer Metadatos: Configura el servidor para que no revele versiones de software, tecnologías usadas o errores detallados de la base de datos a los usuarios finales (control mediante Try-Catch retornando JSON neutral).

⚙️ 4. Configuración y Mantenimiento
Seguridad por Diseño: La seguridad no debe ser un parche final, sino una parte integral desde la fase de arquitectura del proyecto (Estandarización, Cero Residuos).

Hardening del Sistema: Asegura que el acceso de rol SuperAdministrador sea único e irreemplazable, blindando su modificación u omisión en la BD.

📝 5. Monitoreo y Respuesta
Logging Detallado de Auditoría: Registra de inmediato inserciones, actualizaciones y borrados usando el `EventoModelo` del sistema, insertando siempre el valor viejo y el valor nuevo.

Eventos Limpios: Nunca guardes fragmentos de código, contraseñas, o las respuestas de seguridad cifradas dentro las trazas del Historial de Auditoría.