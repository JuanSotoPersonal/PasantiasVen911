# Sistema de Seguimiento de Incidentes - VEN 911 🚑

Este proyecto es una plataforma web robusta diseñada para la gestión y seguimiento de incidentes del sistema de emergencias **VEN 911**. El sistema permite el registro de fichas de incidentes, gestión de organismos, usuarios y reportes detallados, bajo estándares de alta disponibilidad y rendimiento.

---

## 🚀 Instalación y Configuración

Sigue estos pasos para poner en marcha el sistema en un entorno local:

### 1. Requisitos Previos
*   **XAMPP** (Versión con PHP 8.1 o superior).
*   **MySQL/MariaDB**.
*   **Git** (para control de versiones).

### 2. Clonación del Proyecto
Clona el repositorio en tu carpeta `htdocs`:
```bash
cd C:\xampp\htdocs
git clone https://github.com/JuanSotoPersonal/PasantiasVen911.git
```

### 3. Configuración de la Base de Datos
1.  Inicia **Apache** y **MySQL** desde el Panel de Control de XAMPP.
2.  Accede a [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3.  Crea una base de datos llamada `ficha_ven_911`.
4.  Importa el archivo SQL ubicado en la raíz del proyecto: `ficha_ven_911.sql`.

### 4. Configuración del Sistema
Asegúrate de que los parámetros de conexión en `app/Config/Database.php` coincidan con tu entorno local:
```php
// Ejemplo de configuración típica
'host' => 'localhost',
'user' => 'root',
'pass' => '',
'db'   => 'ficha_ven_911'
```

### 5. Acceso al Sistema
Abre tu navegador y dirígete a:
`http://localhost/PasantiasVen911`

---

## 📁 Estructura del Proyecto

El código se organiza siguiendo un patrón **MVC (Modelo-Vista-Controlador)** con un enfoque en la modularidad y autonomía:

*   📂 `app/`: Corazón de la aplicación.
    *   📂 `Config/`: Configuraciones globales y base de datos.
    *   📂 `controladores/`: Lógica de negocio y manejo de peticiones.
    *   📂 `modelos/`: Interacción directa con la base de datos.
    *   📂 `vista/`: Interfaces de usuario modularizadas.
    *   📂 `Helpers/`: Clases de utilidad (Ej. `Validador.php`).
*   📂 `public/`: Recursos accesibles públicamente.
    *   📂 `js/`: Scripts de lógica frontend divididos por módulos.
    *   📂 `css/`: Estilos del sistema.
    *   📂 `libs/`: Dependencias locales (Bootstrap, DataTables, SweetAlert2) para funcionamiento **offline**.
*   📄 `index.php`: Enrutador principal del sistema.

---

## 🛠️ Cómo Funciona el Sistema

### 1. Arquitectura de Vistas
Para evitar archivos masivos, cada módulo (ej. Usuarios) tiene un `index.php` que actúa como contenedor y delega el contenido a componentes individuales en una carpeta `componentes/` (ej. `_modal_crear.php`, `_tabla_principal.php`).

### 2. Procesamiento de Datos (Server-Side)
Todas las tablas del sistema utilizan **DataTables** con procesamiento desde el servidor. Esto significa que la búsqueda, paginación y ordenamiento se realizan en la base de datos, garantizando un rendimiento óptimo incluso con millones de registros.

### 3. Seguridad y Validación
*   **Backend First:** Las validaciones se realizan estrictamente en el servidor usando la clase `App\Helpers\Validador`.
*   **Protección XSS:** Se utiliza `window.escapeHTML` en el frontend para sanitizar datos.
*   **Contraseñas:** Encriptación mediante `password_hash()`.

### 4. Estándares de Código
*   **Nomenclatura:** `snake_case` para la base de datos y `camelCase` para la lógica PHP/JS.
*   **Idioma:** Todo el código fuente está escrito rigurosamente en **Español**.
*   **Offline:** El sistema no depende de CDNs externos; todas las librerías se cargan localmente.

---

## ✍️ Autor
*   **Juan Soto** - Desarrollo y Arquitectura.
*   **Proyecto de Pasantías / Tesis** - VEN 911.

---

## 📄 Licencia
Este proyecto es de uso exclusivo para el VEN 911 y fines académicos relacionados.
