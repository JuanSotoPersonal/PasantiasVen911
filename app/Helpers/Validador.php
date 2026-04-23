<?php

namespace App\Helpers;

class Validador {

    /**
     * Valida el nombre de usuario.
     * @param string $usuario
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarUsuario(string $usuario): array {
        $usuario = trim($usuario);
        
        if (empty($usuario)) {
            return ['valido' => false, 'mensaje' => 'El usuario es obligatorio.'];
        }

        if (strlen($usuario) < 7) {
            return ['valido' => false, 'mensaje' => 'El usuario debe tener al menos 7 caracteres.'];
        }
        
        if (strlen($usuario) > 32) {
            return ['valido' => false, 'mensaje' => 'El usuario no puede exceder los 32 caracteres.'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) {
            return ['valido' => false, 'mensaje' => 'El usuario solo puede contener letras y números, sin signos especiales.'];
        }
        
        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Valida la contraseña.
     * @param string $contrasena
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarContrasena(string $contrasena): array {
        if (empty($contrasena)) {
            return ['valido' => false, 'mensaje' => 'La contraseña es obligatoria.'];
        }

        if (strlen($contrasena) < 8) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres.'];
        }
        
        if (strlen($contrasena) > 128) {
            return ['valido' => false, 'mensaje' => 'La contraseña no puede exceder los 128 caracteres.'];
        }
        
        if (!preg_match('/[A-Z]/', $contrasena) || !preg_match('/[0-9]/', $contrasena)) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe contener al menos una mayúscula y un número.'];
        }
        
        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Valida el nombre completo.
     * @param string $nombreCompleto
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarNombreCompleto(string $nombreCompleto): array {
        $nombreCompleto = trim($nombreCompleto);
        
        if (empty($nombreCompleto)) {
            return ['valido' => false, 'mensaje' => 'El nombre completo es obligatorio.'];
        }

        if (strlen($nombreCompleto) > 128) {
            return ['valido' => false, 'mensaje' => 'El nombre completo no puede exceder los 128 caracteres.'];
        }
        
        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Valida la cédula (Opcional en algunos contextos, pero si viene debe ser válida).
     * @param string|null $cedula
     * @param bool $obligatoria
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarCedula(?string $cedula, bool $obligatoria = true): array {
        $cedula = trim((string)($cedula ?? ''));
        
        // Atrapa vacíos literales o strings "null"/"undefined" inyectados por FormData en JS
        if ($cedula === '' || strtolower($cedula) === 'null' || strtolower($cedula) === 'undefined') {
            if ($obligatoria) {
                return ['valido' => false, 'mensaje' => 'La cédula es obligatoria.'];
            }
            return ['valido' => true, 'mensaje' => ''];
        }

        if (strlen($cedula) < 6 || strlen($cedula) > 8) {
            return ['valido' => false, 'mensaje' => 'La cédula debe tener entre 6 y 8 caracteres.'];
        }
        
        if (!ctype_digit($cedula)) {
            return ['valido' => false, 'mensaje' => 'La cédula debe contener solo números.'];
        }
        
        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Valida una respuesta de seguridad genérica.
     * @param string $respuesta
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarRespuestaSeguridad(string $respuesta): array {
        $respuesta = trim($respuesta);
        
        if (empty($respuesta)) {
            return ['valido' => false, 'mensaje' => 'La respuesta de seguridad es obligatoria.'];
        }

        if (strlen($respuesta) > 128) {
            return ['valido' => false, 'mensaje' => 'Las respuestas de seguridad no pueden exceder los 128 caracteres.'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ]+$/', $respuesta)) {
            return ['valido' => false, 'mensaje' => 'Las respuestas de seguridad solo pueden contener letras, números y espacios.'];
        }
        
        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Valida un número de teléfono (Formatos venezolanos).
     */
    public static function validarTelefono(?string $telefono, bool $obligatorio = true): array {
        $telefono = trim($telefono ?? '');
        if (empty($telefono)) {
            return $obligatorio ? ['valido' => false, 'mensaje' => 'El teléfono es obligatorio.'] : ['valido' => true, 'mensaje' => ''];
        }
        // Expresión regular flexible para 04121234567, 0412-1234567, +58...
        $limpio = str_replace([' ', '-', '(', ')', '+'], '', $telefono);
        if (!preg_match('/^(58|0)?(412|414|424|416|426|212|241|243|244|245|246)[0-9]{7}$/', $limpio)) {
            return ['valido' => false, 'mensaje' => 'El formato de teléfono es inválido (Ej: 04121234567).'];
        }
        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Valida texto libre (direcciones, descripciones).
     */
    public static function validarTextoLibre(?string $texto, string $nombreCampo, int $min = 5, int $max = 500): array {
        $texto = trim($texto ?? '');
        if (empty($texto)) {
            return ($min === 0) ? ['valido' => true, 'mensaje' => ''] : ['valido' => false, 'mensaje' => "El campo '{$nombreCampo}' es obligatorio."];
        }
        if (mb_strlen($texto) < $min) {
            return ['valido' => false, 'mensaje' => "El campo '{$nombreCampo}' es demasiado corto (mínimo {$min} caracteres)."];
        }
        if (mb_strlen($texto) > $max) {
            return ['valido' => false, 'mensaje' => "El campo '{$nombreCampo}' excede el límite de {$max} caracteres."];
        }
        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Valida un ID numérico.
     */
    public static function validarId(?int $id, string $nombreCampo): array {
        if (!$id || $id <= 0) {
            return ['valido' => false, 'mensaje' => "Debe seleccionar un valor válido para '{$nombreCampo}'."];
        }
        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Valida nombres de catálogos (letras, números, espacios y signos básicos).
     */
    public static function validarNombreCatalogo(?string $nombre, string $nombreCampo): array {
        $nombre = trim($nombre ?? '');
        if (empty($nombre)) {
            return ['valido' => false, 'mensaje' => "El nombre de '{$nombreCampo}' es obligatorio."];
        }
        if (mb_strlen($nombre) < 1) {
            return ['valido' => false, 'mensaje' => "El nombre de '{$nombreCampo}' no puede estar vacío."];
        }
        if (mb_strlen($nombre) > 100) {
            return ['valido' => false, 'mensaje' => "El nombre de '{$nombreCampo}' no puede exceder los 100 caracteres."];
        }
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\-\(\)]+$/', $nombre)) {
            return ['valido' => false, 'mensaje' => "El campo '{$nombreCampo}' contiene caracteres no permitidos."];
        }
        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Valida nombres estrictamente alfabéticos (solo letras y espacios).
     */
    public static function validarNombreAlfabetico(?string $nombre, string $nombreCampo, int $max = 60): array {
        $nombre = trim($nombre ?? '');
        if (empty($nombre)) {
            return ['valido' => false, 'mensaje' => "El nombre de '{$nombreCampo}' es obligatorio."];
        }
        if (mb_strlen($nombre) > $max) {
            return ['valido' => false, 'mensaje' => "El nombre de '{$nombreCampo}' no puede exceder los {$max} caracteres."];
        }
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
            return ['valido' => false, 'mensaje' => "El campo '{$nombreCampo}' solo puede contener letras y espacios."];
        }
        return ['valido' => true, 'mensaje' => ''];
    }

}
