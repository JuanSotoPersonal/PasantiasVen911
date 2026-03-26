---
trigger: always_on
---

# Guía de Buenas Prácticas para Mensajes de Commit

Esta guía establece las reglas para mantener un historial de Git legible, escaneable y profesional.

## 1. Reglas de Formato Esenciales

* **Usa el modo imperativo:** El mensaje debe redactarse como una instrucción (p. ej., `Add`, `Fix`, `Change`, `Remove`). Una buena forma de validarlo es completar la frase: *"Si aplico este commit, este commit va a..."*.
* **Sin puntuación final:** No utilices puntos (.) ni puntos suspensivos (...) al final del asunto del commit. Los títulos no llevan punto.
* **Límite de 50 caracteres:** El resumen (primera línea) debe ser corto y directo. Si necesitas explicar más, usa el cuerpo del mensaje.
* **Cuerpo para el contexto:** Si el cambio es complejo, añade una línea en blanco después del resumen y desarrolla los detalles necesarios en el cuerpo del mensaje.

## 2. Commits Semánticos (Estructura)

Se recomienda seguir la convención de commits semánticos para facilitar la generación de changelogs y la legibilidad:

` <tipo>[scope]: <descripción> `

### Tipos de Commit
| Tipo | Descripción |
| :--- | :--- |
| `feat` | Nueva característica para el usuario. |
| `fix` | Corrección de un error o bug. |
| `perf` | Cambios que mejoran el rendimiento. |
| `refactor` | Cambios en el código que no añaden funciones ni corrigen bugs (limpieza). |
| `docs` | Cambios exclusivamente en la documentación. |
| `style` | Cambios de formato (espacios, puntos y coma) que no afectan la lógica. |
| `test` | Añadir o corregir pruebas existentes. |
| `build` | Cambios que afectan el sistema de construcción o dependencias externas. |
| `ci` | Cambios en los archivos y scripts de configuración de CI/CD. |

## 3. Ejemplos de Aplicación

| Estado | Ejemplo |
| :--- | :--- |
| ❌ Incorrecto | `Fixed the bug in the login screen.` (Pasado y con punto) |
| ❌ Incorrecto | `Add search feature...` (Puntos suspensivos) |
| ✅ Correcto | `feat: add search functionality` |
| ✅ Correcto | `fix(auth): resolve login timeout issue` |

## 4. Herramientas Recomendadas

Para automatizar y validar estas reglas, se sugiere el uso de:
* **Husky:** Para ejecutar validaciones en los hooks de git (pre-commit, commit-msg).
* **Commitlint:** Para asegurar que los mensajes sigan la estructura semántica.
* **Commitizen:** Para generar mensajes mediante una interfaz de línea de comandos asistida.

---
*Basado en las recomendaciones de midudev.*