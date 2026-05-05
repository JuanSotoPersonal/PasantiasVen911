# Documentación Técnica: Arquitectura de Notificaciones en Tiempo Real (WebSockets)

Este documento detalla la implementación y el funcionamiento del nuevo sistema de notificaciones del Proyecto VEN 911, basado en una arquitectura de publicación/suscripción (Pub/Sub) usando **WebSockets** nativos con **Ratchet** y **ReactPHP**.

---

## 1. Visión General y Justificación

El sistema ha migrado de un modelo ineficiente de sondeo (polling) a un bus de mensajes en memoria. La arquitectura actual permite que el servidor "empuje" información a los clientes interesados en milisegundos, eliminando la carga sobre MySQL y Apache.

---

## 2. El Motor de Emisión: `Notificador.php`

Para mantener el código limpio (Inercia Cero), se ha centralizado toda la lógica de emisión en el helper `App\Helpers\Notificador`. Este componente se encarga de dos tareas críticas simultáneamente:

1.  **Persistencia (MySQL):** Guarda la alerta en la tabla `notificaciones` para que no se pierda si el usuario está offline.
2.  **Difusión (WebSocket):** Envía un pálpito HTTP al puerto 8081 del demonio para que este retransmita el mensaje a los navegadores activos.

### Métodos Principales:
- `enviarPorRol(rol_id, tipo, titulo, mensaje, ficha_id)`: Notifica a toda una jerarquía (ej. todos los Despachadores).
- `enviarAUsuario(usuario_id, tipo, titulo, mensaje, ficha_id)`: Notifica a una persona específica (ej. feedback directo al Operador).

---

## 3. Enrutamiento Inteligente por Roles

El sistema no satura a todos los usuarios con todas las alertas. Existe un filtrado quirúrgico basado en la lógica de negocio del VEN 911:

| Rol | Evento Disparador | Propósito de la Notificación |
| :--- | :--- | :--- |
| **Despacho (3)** | Creación de Ficha Nueva | Alerta inmediata para iniciar el despacho de unidades. |
| **Jefatura (4)** | Cualquier cambio en Fichas | Auditoría y supervisión del flujo operativo en tiempo real. |
| **Administrador (1)** | Gestión de Usuarios | Alertas de seguridad sobre creación o bloqueo de cuentas. |
| **Operador (2)** | Cambio de estado en *su* ficha | Feedback sobre el progreso de la emergencia que él reportó. |

---

## 4. Flujo de Trabajo y Filtrado

1.  **Backend (Trigger):** Un controlador (ej. `FichaControlador`) invoca al `Notificador`.
2.  **Demonio (Broadcast):** El servidor Ratchet recibe el pálpito y emite un JSON a **todos** los clientes conectados. El paquete incluye los campos `rol_id` o `usuario_id` del destinatario.
3.  **Frontend (Filtro):** El script `notificaciones.js` compara los IDs del paquete con las variables globales `window.USUARIO_ID` y `window.USUARIO_ROL_ID` (inyectadas en la sesión). Si no coinciden, el mensaje se descarta silenciosamente; si coinciden, se muestra el popup y se actualiza la campana.

---

## 5. Resiliencia y Fallo con Elegancia

-   **Carga Inicial:** Al abrir el sistema, el frontend realiza un `fetch` a `notificacion/obtenerPendientes` para recuperar las alertas que ocurrieron mientras el usuario estaba desconectado.
-   **Timeout de Seguridad:** Todas las comunicaciones cURL internas tienen un timeout de 1 segundo. Si el demonio WebSocket se apaga, el resto del sistema web (guardar fichas, etc.) seguirá funcionando sin bloqueos.
-   **Auto-Reconexión:** El cliente JavaScript intentará reconectarse al WebSocket automáticamente cada 8 segundos en caso de caída de red.

---

## 6. Instrucciones de Arranque

Para que el sistema de notificaciones funcione, el Demonio debe estar en ejecución continua:
```cmd
C:\xampp\php\php.exe C:\xampp\htdocs\ProyectoFicha\app\bin\servidor_ws.php
```
