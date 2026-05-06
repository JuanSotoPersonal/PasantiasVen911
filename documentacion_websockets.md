# Documentación Técnica: Sistema de Notificaciones WebSockets - VEN 911

Esta documentación detalla la arquitectura, implementación y correcciones aplicadas al sistema de notificaciones en tiempo real del proyecto ProyectoFicha (VEN 911).

---

## 1. Arquitectura del Sistema

El sistema utiliza un modelo de **Bus de Eventos Híbrido**:
1.  **Emisor (Backend PHP)**: Invocado desde los controladores mediante el helper `Notificador`.
2.  **Mensajero (Demonio WebSocket)**: Un proceso independiente ejecutando Ratchet/ReactPHP que escucha eventos HTTP (puerto 8081) y los retransmite vía WebSockets (puerto 8080).
3.  **Receptor (Cliente JS)**: `notificaciones.js` mantiene una conexión persistente y filtra los mensajes según el rol y ID del usuario.

---

## 2. Cambios en la Capa de Datos (MySQL)

Se identificó que las notificaciones no persistían el título de la alerta, lo que impedía mostrar encabezados claros en el frontend tras recargar la página.

**Cambio aplicado:**
```sql
ALTER TABLE notificaciones 
ADD COLUMN titulo VARCHAR(150) NOT NULL DEFAULT 'Notificación' 
AFTER tipo;
```

**Actualización en `NotificacionModelo.php`:**
Se actualizaron los métodos `crear()` y `obtenerNoLeidas()` para incluir este nuevo campo, asegurando que la persistencia sea completa.

---

## 3. Emisión de Notificaciones (`Notificador.php`)

El helper `Notificador` actúa como el puente entre la lógica de negocio y el servidor de sockets.

**Implementación del envío:**
```php
public static function emitirSocket(array $payload): bool {
    $url = "http://127.0.0.1:8081"; // Puerto del receptor interno del demonio
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    // ... configuraciones de timeout y cabeceras
    $result = curl_exec($ch);
    return ($result !== false);
}
```

**Corrección aplicada:** Se cambió el `require_once` de rutas relativas a rutas absolutas usando `__DIR__` para evitar errores de inclusión cuando el controlador es invocado desde diferentes contextos de ruteo.

---

## 4. Lógica de Enrutamiento y Privacidad (JS)

El cliente `notificaciones.js` fue auditado para corregir fugas de privacidad. Anteriormente, el Administrador recibía notificaciones privadas de otros usuarios.

**Filtro de Seguridad Corregido:**
```javascript
// notificaciones.js
const esParaMiUsuario = notif.usuario_id && parseInt(notif.usuario_id) === window.USUARIO_ID;
const esParaMiRol     = notif.rol_id && parseInt(notif.rol_id) === window.USUARIO_ROL_ID;
const esPublico       = !notif.rol_id && !notif.usuario_id;

// El Admin ya NO recibe notificaciones privadas de terceros
if (!esParaMiUsuario && !esParaMiRol && !esPublico) {
    return; // Ignorar mensaje
}
```

---

## 5. Integración Global y Renderizado

Para que las notificaciones funcionen en todo el Dashboard (incluyendo el Home), se centralizaron las dependencias en `scripts.php`.

**Solución al error de renderizado:**
Se definió `window.escapeHTML` de forma global para que `notificaciones.js` no falle cuando `datatables_config.js` no está presente.

```javascript
// scripts.php
window.escapeHTML = function (str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
};
```

---

## 6. Automatización y Monitoreo

### Auto-arranque (`iniciar_ws.bat`)
Se implementó un script de Windows para gestionar el demonio de forma sencilla:
1. Verifica si `php.exe` ya está ejecutando `servidor_ws.php`.
2. Si no, inicia una nueva instancia minimizada.
3. Redirige la salida a `app/bin/servidor_ws.log`.

### Widget de Estado (Administrador)
El Administrador ahora dispone de un widget en tiempo real en su Dashboard que indica la salud del sistema mediante un endpoint en el `NotificacionControlador`:

```php
public function estadoServidor(): void {
    $conexion = @fsockopen('127.0.0.1', 8081, $errno, $errstr, 1);
    // Retorna JSON con activo: true/false y latencia en ms
}
```

---

## 7. Notificaciones por Rol de Usuario

Se configuraron los siguientes disparadores (triggers) en el sistema:

1.  **Operador (Rol 2)**:
    - Recibe alerta cuando un Despachador **toma** su ficha.
    - Recibe alerta cuando se **asigna un organismo** a su ficha.
    - Recibe alerta cuando la ficha **cambia de estado** operativo.

2.  **Despachador (Rol 3)**:
    - Recibe alerta global cuando un Operador **crea una nueva ficha**.

3.  **Jefatura (Rol 4)**:
    - Recibe alertas de **todas las actualizaciones** operativas (asignaciones, cambios de estatus de unidades, cancelaciones).

4.  **Administrador (Rol 1)**:
    - Recibe trazas de auditoría técnica de todos los eventos del bus.

---

## 8. Resumen de Fixes Aplicados

1.  **Eliminación de Redundancia**: Se quitó la carga doble de `notificaciones.js` en `home.php`.
2.  **Prevención de Duplicados**: Se implementó bloqueo de botones con spinner en todos los formularios para evitar múltiples peticiones concurrentes.
3.  **Focus Fix en Modales**: Se corrigió el problema de SweetAlert2 donde los campos de texto en la cancelación de organismos no permitían escritura.
