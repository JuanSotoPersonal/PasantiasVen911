---
description: Guía de diseño visual — Mantener el tono de colores del login en todo el sistema
---

# 🎨 Guía de Tema Visual — Sistema VEN 911

Todo nuevo módulo, vista o componente **DEBE** seguir esta guía para mantener coherencia visual con la pantalla de Login.

---

## 1. Paleta de Colores Oficial

Todos estos valores están derivados de `login.css` y son la única fuente de verdad.

| Token                 | Valor HEX / RGB                    | Uso principal                              |
|-----------------------|------------------------------------|--------------------------------------------|
| `--ven-green-primary` | `#16a34a`                          | Botones primarios, elementos activos       |
| `--ven-green-dark`    | `#15803d`                          | Hover de botones primarios                 |
| `--ven-green-darker`  | `#14532d`                          | Hover profundo / active state              |
| `--ven-green-deepest` | `#064e3b`                          | Títulos, texto importante, sidebar brand   |
| `--ven-green-accent`  | `#22c55e`                          | Focus rings, acentos brillantes            |
| `--ven-green-light`   | `#f0fdf4`                          | Fondos de inputs, cancelar, bg suave       |
| `--ven-green-border`  | `#bbf7d0`                          | Bordes de cards, modales, SweetAlert       |
| `--ven-green-shadow`  | `rgba(22, 163, 74, 0.4)`           | Sombras de botones                         |
| `--ven-green-glow`    | `rgba(34, 197, 94, 0.2)`           | Focus glow (box-shadow de inputs)          |
| `--ven-text-dark`     | `#072513`                          | Texto de inputs y placeholders             |
| Blanco base           | `#ffffff`                          | Fondo de tarjetas, modales                 |
| Fondo degradado body  | `linear-gradient(135deg, #28695a 0%, #718aaa 100%)` | Solo en páginas standalone (login) |

---

## 2. Variables CSS — Dónde están definidas

Las variables `:root` canónicas se declaran en **`public/css/usuarios.css`**.

```css
:root {
    --ven-green-primary:  #16a34a;
    --ven-green-dark:     #15803d;
    --ven-green-darker:   #14532d;
    --ven-green-deepest:  #064e3b;
    --ven-green-accent:   #22c55e;
    --ven-green-light:    #f0fdf4;
    --ven-green-border:   #bbf7d0;
    --ven-green-shadow:   rgba(22, 163, 74, 0.4);
    --ven-green-glow:     rgba(34, 197, 94, 0.2);
    --ven-text-dark:      #072513;
}
```

> IMPORTANTE: Nunca escribas colores hardcoded en un nuevo CSS. Usa siempre las variables `var(--ven-*)`.

---

## 3. Clases Reutilizables ya Creadas

Antes de crear estilos nuevos, verifica si ya existe una clase en `usuarios.css`:

| Clase                 | Descripción                                              |
|-----------------------|----------------------------------------------------------|
| `.btn-ven-primary`    | Botón verde principal (Agregar, Guardar)                 |
| `.btn-ven-edit`       | Botón de editar (verde oscuro)                           |
| `.btn-ven-password`   | Botón acento brillante (cambiar contraseña)              |
| `.btn-ven-cancel`     | Botón neutro verde clarito (Cancelar)                    |
| `.modal-header-ven`   | Cabecera de modal con degradado verde                    |
| `.badge-activo`       | Badge de estado activo (verde)                           |
| `.badge-inactivo`     | Badge de estado inactivo (gris)                          |
| `.badge-estado`       | Base de badges de estado                                 |
| `.btn-accion`         | Botón cuadrado de acción en tabla (32x32 px)             |
| `.btn-toggle-estado`  | Botón transparent para toggle de estado                  |
| `.password-wrapper`   | Contenedor de input contraseña con ojo toggle            |
| `.card-usuarios`      | Card con cabecera alineada flex                          |

---

## 4. Reglas para Crear un Nuevo Módulo

### 4.1 Archivo CSS del módulo

1. Crea `public/css/<modulo>.css`.
2. No redecles `:root` — las variables ya están disponibles globalmente desde `usuarios.css` (cargado en el layout principal). Si el nuevo CSS se carga antes, copia únicamente el bloque `:root` al inicio de tu archivo.
3. Usa **solo** las variables `var(--ven-*)` definidas en la sección 1.
4. Para gradientes, sigue el patrón:
   ```css
   background: linear-gradient(135deg, var(--ven-green-primary) 0%, var(--ven-green-dark) 100%);
   ```

### 4.2 Botones

| Acción           | Clase a usar          | Nota                                  |
|------------------|-----------------------|---------------------------------------|
| Crear / Guardar  | `.btn-ven-primary`    | —                                     |
| Editar           | `.btn-ven-edit`       | —                                     |
| Contraseña       | `.btn-ven-password`   | —                                     |
| Cancelar / Reset | `.btn-ven-cancel`     | —                                     |
| Peligro / Borrar | Bootstrap `btn-danger`| Mantener rojo Bootstrap, no inventar  |

### 4.3 Modales

- Header: usa **siempre** `.modal-header-ven` para el fondo degradado verde.
- Backdrop: el estilo glassmorphism ya está en `usuarios.css` (`.modal-backdrop`, `.modal-content`). No necesitas reescribirlo.
- Inputs dentro de modales: ya están estilizados en `.modal .form-control:focus` y `.modal .form-label`.

### 4.4 Alertas (SweetAlert2)

Los estilos de SweetAlert2 ya están en `usuarios.css`. Reutiliza el mismo CSS; no crees variantes de colores.

- Confirmar → degradado verde primario (automático).
- Cancelar → fondo `--ven-green-light` (automático).

### 4.5 Sidebar y Navbar

Definidos en `home.css`. No tocar esos colores en los módulos individuales.

### 4.6 Tipografía

- Fuente: **Inter** (cargada en el layout principal desde local).
- Títulos de módulo: `color: var(--ven-green-deepest)` / `font-weight: 700`.
- Labels de formularios: `color: var(--ven-green-deepest)` / `font-weight: 600`.

---

## 5. Prohibiciones

- NO uses `#28a745`, `#198754` ni otras variantes Bootstrap verdes — rompería la coherencia.
- NO uses `color: green` hardcoded.
- NO cambies el color de la barra lateral (sidebar) ni del navbar por módulo.
- NO uses degradados de otros colores (azul, morado, rojo) en elementos positivos/primarios.
- NO uses fondos negros o muy oscuros en modales — mantener glassmorphism claro.

---

## 6. Checklist de Consistencia Visual

Antes de hacer commit de una vista nueva, verifica:

- [ ] Los botones primarios usan `.btn-ven-primary` o `var(--ven-green-primary)`.
- [ ] Los headers de los modales usan `.modal-header-ven`.
- [ ] Los inputs tienen focus ring con `var(--ven-green-glow)`.
- [ ] Los labels de formulario usan `color: var(--ven-green-deepest)`.
- [ ] Las tarjetas tienen `border-radius: 0.75rem` y sombra suave.
- [ ] SweetAlert2 no tiene estilos inline que sobrescriban el tema verde.
- [ ] No hay colores hardcoded en el nuevo CSS (excepto blanco `#ffffff`).

---

## 7. Referencia Rápida de Archivos

| Archivo                    | Contenido                                              |
|----------------------------|--------------------------------------------------------|
| `public/css/login.css`     | Fuente de verdad — paleta original del sistema         |
| `public/css/home.css`      | Variables base + sidebar / navbar / footer             |
| `public/css/usuarios.css`  | Variables completas + todas las clases reutilizables   |
| `public/css/<modulo>.css`  | Estilos específicos del módulo (sin redeclarar `:root`)|
