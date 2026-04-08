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

Bien: $emergency_system_id = 911;

Eliminación de Residuos (Refactorización): Por cada 100 líneas de código nuevas, intenta refactorizar o eliminar 10 líneas de código antiguo u obsoleto.

Consistencia de Flujo: Usa siempre el mismo estándar de nomenclatura (ej. snake_case para bases de datos y camelCase para lógica de aplicación) para que el cerebro no pierda energía cambiando de contexto.

4. Reglas de Blindaje (Seguridad y Errores)
Validación en la Frontera: Valida todos los datos en el punto de entrada (API o formularios). Una vez que los datos entran al núcleo del sistema, deben ser "limpios" y confiables.

Falla con Elegancia: Un error no debe detener todo el sistema. Implementa bloques try-catch que registren el incidente pero mantengan la interfaz operativa para el usuario.

Integridad Referencial: Delega la seguridad de los datos a la base de datos siempre que sea posible mediante restricciones y tipos de datos estrictos, liberando al código de esa carga.

5. cada que se cree una datatable se cree un js en public/js/tablas para no saturar el codigo de vista