# Instalación y Configuración

Sigue estos pasos para poner en marcha el sistema en un entorno local:

# 1. Requisitos Previos
*   **XAMPP** (Versión con PHP 8.1 o superior).
*   **MySQL/MariaDB**.
*   **Git** (para control de versiones).

# 2. Clonación del Proyecto
Clona el repositorio en tu carpeta htdocs:
```bash
cd C:\xampp\htdocs
git clone https://github.com/JuanSotoPersonal/PasantiasVen911.git
```

### 3. Configuración de la Base de Datos
1.  Inicia Apache y MySQL desde el Panel de Control de XAMPP.
2.  Accede a [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3.  Crea una base de datos llamada ficha_ven_911.
4.  Importa el archivo SQL ubicado en la raíz del proyecto: ficha_ven_911.sql.

### 4. Configuración del Sistema
Asegúrate de que los parámetros de conexión en app/Config/Database.php coincidan con tu entorno local:
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

# Estructura del Proyecto

El código se organiza siguiendo un patrón **MVC (Modelo-Vista-Controlador)** con un enfoque en la modularidad y autonomía:

*   app/: Corazón de la aplicación.
    *   Config/: Configuraciones globales y base de datos.
    *   controladores/: Lógica de negocio y manejo de peticiones.
    *   modelos/: Interacción directa con la base de datos.
    *   vista/: Interfaces de usuario modularizadas.
    *   Helpers/: Clases de utilidad.
*   public/: Recursos accesibles públicamente.
    *   js/: Scripts de lógica frontend divididos por módulos.
    *   css/: Estilos del sistema.
    *   libs/: Dependencias locales (Bootstrap, DataTables, SweetAlert2) para funcionamiento offline.
*   index.php: Enrutador principal del sistema.

