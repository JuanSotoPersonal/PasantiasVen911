<?php
/**
 * SERVICIO: UsuarioServicio
 * Propósito: Gestionar la lógica de negocio del ciclo de vida de los usuarios,
 * incluyendo validaciones de seguridad, roles y auditoría.
 */

namespace App\Servicios;

use App\modelos\UsuarioModelo;
use App\modelos\RegistroModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;
use App\Helpers\Notificador;
use Exception;

require_once 'app/modelos/UsuarioModelo.php';
require_once 'app/modelos/RegistroModelo.php';
require_once 'app/modelos/EventoModelo.php';
require_once 'app/Helpers/Validador.php';
require_once 'app/Helpers/Notificador.php';

class UsuarioServicio {

    private UsuarioModelo  $modelo;
    private EventoModelo   $log;
    private RegistroModelo $modeloRegistro;

    public function __construct() {
        $this->modelo         = new UsuarioModelo();
        $this->log            = new EventoModelo();
        $this->modeloRegistro = new RegistroModelo();
    }

    /**
     * Crea un nuevo usuario validando roles y duplicidad.
     */
    public function crearUsuario(array $datos, int $adminId): array {
        $usuario        = $datos['usuario'];
        $cedula         = $datos['cedula'];
        $rolId          = (int)$datos['rol_id'];

        // Verificaciones de duplicidad
        if ($this->modelo->existeUsuario($usuario)) {
            return ['success' => false, 'message' => "El usuario '{$usuario}' ya está registrado."];
        }
        if (!empty($cedula) && $this->modelo->existeCedula($cedula)) {
            return ['success' => false, 'message' => "La cédula 'V-{$cedula}' ya está registrada."];
        }

        // Restricción SuperAdmin Único
        if ($rolId === 1) {
            return ['success' => false, 'message' => 'Solo puede existir un SuperAdministrador en el sistema.'];
        }

        $datosPersistencia = [
            'usuario'         => $usuario,
            'password'        => password_hash($datos['password'], PASSWORD_DEFAULT),
            'nombre_completo' => $datos['nombre_completo'],
            'cedula'          => $cedula ?: null,
            'rol_id'          => $rolId,
            'estado'          => 'activo',
            'pregunta_1_id'   => null,
            'pregunta_2_id'   => null,
            'respuesta_1'     => null,
            'respuesta_2'     => null,
        ];

        if ($this->modelo->crear($datosPersistencia)) {
            $this->log->registrarEvento($adminId, 'INSERT', 'usuarios', null, null, [
                'usuario' => $usuario, 'rol_id' => $rolId
            ], "Usuario '{$usuario}' creado.");

            Notificador::enviarPorRol(1, 'info', 'Seguridad: Nuevo Usuario', "Usuario '{$usuario}' registrado.", null);
            return ['success' => true, 'message' => 'Usuario creado correctamente.'];
        }

        return ['success' => false, 'message' => 'Error al crear el usuario.'];
    }

    /**
     * Actualiza información básica de usuario con restricciones de rol.
     */
    public function actualizarUsuario(int $id, array $datos, int $adminId): array {
        if ($this->modelo->existeUsuario($datos['usuario'], $id)) {
            return ['success' => false, 'message' => "El usuario '{$datos['usuario']}' ya está en uso."];
        }
        if (!empty($datos['cedula']) && $this->modelo->existeCedula($datos['cedula'], $id)) {
            return ['success' => false, 'message' => "La cédula ya está en uso."];
        }

        $usuarioAnterior = $this->modelo->obtenerPorId($id);
        if ($usuarioAnterior) {
            $oldRol = (int)$usuarioAnterior['rol_id'];
            $rolId  = (int)$datos['rol_id'];
            if ($rolId === 1 && $oldRol !== 1) {
                return ['success' => false, 'message' => 'No se puede promover a SuperAdministrador.'];
            }
            if ($oldRol === 1 && $rolId !== 1) {
                return ['success' => false, 'message' => 'El SuperAdministrador no puede cambiar su propio rol.'];
            }
        }

        if ($this->modelo->actualizarInformacion($id, $datos)) {
            $this->log->registrarEvento($adminId, 'UPDATE', 'usuarios', $id, null, $datos, "Usuario ID {$id} editado.");
            return ['success' => true, 'message' => 'Usuario actualizado correctamente.'];
        }

        return ['success' => false, 'message' => 'Error al actualizar.'];
    }

    /**
     * Cambia la contraseña con desafío de seguridad para SuperAdmin.
     */
    public function cambiarContrasena(int $id, string $nuevaPass, int $adminId, array $securityAnswers = []): array {
        $usuarioAnterior = $this->modelo->obtenerPorId($id);
        if (!$usuarioAnterior) return ['success' => false, 'message' => 'Usuario no encontrado.'];

        // Desafío SuperAdmin
        if ((int)$usuarioAnterior['rol_id'] === 1) {
            $ans1 = $securityAnswers['ans_1'] ?? '';
            $ans2 = $securityAnswers['ans_2'] ?? '';
            if (empty($ans1) || empty($ans2)) {
                return ['success' => false, 'message' => 'Debes responder las preguntas de seguridad.'];
            }
            if (!$this->modelo->verificarRespuestasSeguridad($id, $ans1, $ans2)) {
                return ['success' => false, 'message' => 'Respuestas de seguridad incorrectas.'];
            }
        }

        if ($this->modelo->actualizarContrasena($id, password_hash($nuevaPass, PASSWORD_DEFAULT))) {
            $this->log->registrarEvento($adminId, 'UPDATE', 'usuarios', $id, null, null, "Contraseña actualizada.");
            return ['success' => true, 'message' => 'Contraseña actualizada correctamente.'];
        }

        return ['success' => false, 'message' => 'Error al actualizar contraseña.'];
    }

    /**
     * Alterna estado con bloqueos de seguridad.
     */
    public function alternarEstado(int $id, int $adminId, string $adminNombre): array {
        if ($id === $adminId) {
            return ['success' => false, 'message' => 'No puedes cambiar tu propio estado.'];
        }

        $usuarioAfectado = $this->modelo->obtenerPorId($id);
        if ($usuarioAfectado && (int)$usuarioAfectado['rol_id'] === 1) {
            return ['success' => false, 'message' => 'El SuperAdministrador no puede ser desactivado.'];
        }

        $resultado = $this->modelo->alternarEstado($id);
        if ($resultado !== false) {
            $nuevoEstado = $resultado['nuevo_estado'];
            $this->log->registrarEvento($adminId, 'CAMBIO_ESTADO', 'usuarios', $id, null, ['estado' => $nuevoEstado], "Estado cambiado a {$nuevoEstado}.");
            
            $txtAccion = ($nuevoEstado === 'activo') ? 'activado' : 'deshabilitado';
            Notificador::enviarPorRol(1, 'alerta', 'Seguridad: Estado de Usuario', "Usuario '{$usuarioAfectado['usuario']}' {$txtAccion} por {$adminNombre}.", null);

            return ['success' => true, 'message' => 'Estado actualizado.', 'nuevo_estado' => $nuevoEstado];
        }

        return ['success' => false, 'message' => 'Error al cambiar estado.'];
    }

    /**
     * Actualiza preguntas de seguridad con código de fábrica.
     */
    public function actualizarPreguntas(int $id, array $datos, string $codigoFabrica, int $adminId): array {
        if (!$this->modeloRegistro->validarLlaveActivacion($codigoFabrica)) {
            return ['success' => false, 'message' => 'Código de Fábrica inválido.'];
        }

        $datosPersistencia = [
            'pregunta_1_id' => (int)$datos['p1'],
            'pregunta_2_id' => (int)$datos['p2'],
            'respuesta_1'   => password_hash(strtolower($datos['r1']), PASSWORD_DEFAULT),
            'respuesta_2'   => password_hash(strtolower($datos['r2']), PASSWORD_DEFAULT)
        ];

        if ($this->modelo->actualizarCamposSeguridad($id, $datosPersistencia)) {
            $this->log->registrarEvento($adminId, 'UPDATE', 'usuarios', $id, null, null, "Preguntas de seguridad actualizadas.");
            return ['success' => true, 'message' => 'Preguntas actualizadas.'];
        }

        return ['success' => false, 'message' => 'Error al actualizar preguntas.'];
    }
}
