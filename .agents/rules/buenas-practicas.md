---
trigger: always_on
---

1. Reglas de Inercia Cero (Simplicidad)
Minimalismo Funcional: No implementes una función "por si acaso" la necesitas en el futuro. Si no resuelve un problema hoy, es peso muerto.

KISS 2.0: Si no puedes explicar la lógica de una función a alguien que no programa en menos de 30 segundos, es demasiado compleja. Divídela.

Abstracción Justa: No crees interfaces para clases que solo tendrán una implementación. La sobre-abstracción genera una "gravedad" que ralentiza el desarrollo.

2. Reglas de Estructura Orbital (Organización)
Modularidad de Componentes: Cada módulo debe ser como un satélite: capaz de operar de forma independiente con interfaces de comunicación claras.

Estandarización de Tipos: Todos los IDs y llaves foráneas deben usar tipos de datos consistentes (como INT UNSIGNED) para evitar fricción en las uniones de tablas y cálculos.

Jerarquía de Carpetas Plana: Mantén la profundidad de directorios al mínimo. Si tienes que navegar 7 niveles de carpetas para encontrar un archivo, la arquitectura es pesada.

3. Reglas de Propulsión (Rendimiento y Mantenimiento)
Código Autodocumentado: El nombre de las variables debe ser tan claro que los comentarios sean redundantes.
Mal: $d = 911;
Bien: $sistema_emergencia_id = 911;

Eliminación de Residuos (Refactorización): Por cada 100 líneas de código nuevas, intenta refactorizar o eliminar 10 líneas de código antiguo u obsoleto. Centraliza código repetitivo (Ej. configuraciones visuales o de DataTables).

Consistencia de Flujo: Usa siempre el mismo estándar de nomenclatura (ej. snake_case para bases de datos y camelCase para lógica de aplicación) para que el cerebro no pierda energía cambiando de contexto.

4. Reglas de Blindaje (Validaciones y Errores)
Validación Centralizada: Siempre utiliza el archivo `App\Helpers\Validador` en los Controladores para validar cadenas de texto, cédulas, usuarios y contraseñas.
NUNCA utilices etiquetas restrictivas HTML (`minlength`, `maxlength`, `required`) como primera línea de defensa, el Frontend debe estar despojado y el control debe recaer absolutamente en el Backend mediante el Validador.

Falla con Elegancia: Un error no debe detener todo el sistema. Implementa bloques try-catch que devuelvan JSON amigable (`success: false`).

5. Reglas de Estandarización Frontend (DataTables)
Cada vez que se cree un DataTable, se debe crear un `.js` modular en `public/js/tablas`. 
IMPORTANTE: Para evitar duplicidad de código (Inercia Cero), nunca copies el bloque de configuración de idioma ni la función de seguridad global; asegúrate de importar el script `datatables_config.js` (`window.Ven911DataTablesLang` y `window.escapeHTML`) en la vista y usar sus variables. Mantén el mismo estilo visual (botones y layout) de los módulos existentes.

6. Regla de Idioma
Todo nombramiento de variables, clases, métodos y funciones debe estar rigurosamente en ESPAÑOL, limitando el inglés exclusivamente a palabras clave de los lenguajes, frameworks o convenciones estándar irremplazables.