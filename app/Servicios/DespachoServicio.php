<?php
/**
 * SERVICIO: DespachoServicio
 * Propósito: Centralizar la lógica de negocio del flujo de despacho,
 * desacoplando la persistencia y las notificaciones de los controladores.
 */

namespace App\Servicios;

use App\modelos\DespachoModelo;
use App\modelos\FichaModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;
use App\Helpers\Notificador;
use Exception;

require_once 'app/modelos/DespachoModelo.php';
require_once 'app/modelos/FichaModelo.php';
require_once 'app/modelos/EventoModelo.php';
require_once 'app/Helpers/Validador.php';
require_once 'app/Helpers/Notificador.php';

class DespachoServicio {

    private DespachoModelo $modelo;
    private FichaModelo    $modeloFicha;
    private EventoModelo   $modeloEvento;

    public function __construct() {
        $this->modelo       = new DespachoModelo();
        $this->modeloFicha  = new FichaModelo();
        $this->modeloEvento = new EventoModelo();
    }

    /**
     * El despachador asume la responsabilidad de una ficha.
     */
    public function tomarFicha(int $fichaId, int $usuarioId, string $usuarioNombre): array {
        $infoFicha = $this->modelo->obtenerInfoFicha($fichaId);
        if (!$infoFicha) {
            return ['success' => false, 'message' => 'Ficha no encontrada.'];
        }

        if (!in_array($infoFicha['estado_ficha'], ['Pendiente', 'En Proceso'], true)) {
            return ['success' => false, 'message' => "La ficha está en estado '{$infoFicha['estado_ficha']}' y no puede ser tomada."];
        }

        $estadoAnterior = $infoFicha['estado_ficha'];
        $exito = $this->modelo->tomarFicha($fichaId, $usuarioId);

        if ($exito) {
            // Auditoría
            $this->modeloEvento->registrarEventoFicha(
                $fichaId, $usuarioId, 'CAMBIO_ESTADO',
                $estadoAnterior, 'En Proceso', null,
                ['id_owner' => $usuarioId],
                "Ficha tomada por despachador. Estado: '{$estadoAnterior}' → 'En Proceso'."
            );

            // Notificaciones
            if (!empty($infoFicha['id_user'])) {
                Notificador::enviarAUsuario(
                    (int)$infoFicha['id_user'], 'info', 'Ficha en Proceso',
                    "Tu Ficha #{$fichaId} ha pasado a 'En Proceso' y está siendo atendida por {$usuarioNombre}.",
                    $fichaId
                );
            }
            Notificador::enviarPorRol(4, 'info', 'Ficha Tomada: Inicio de Gestión', "El despachador {$usuarioNombre} ha tomado la Ficha #{$fichaId}.", $fichaId);
            Notificador::enviarPorRol(1, 'info', 'Auditoría: Ficha Tomada', "Ficha #{$fichaId} tomada por {$usuarioNombre}.", $fichaId);

            return ['success' => true, 'message' => "Ficha #{$fichaId} tomada correctamente."];
        }

        return ['success' => false, 'message' => 'No se pudo tomar la ficha.'];
    }

    /**
     * Asigna un organismo a una ficha.
     */
    public function asignarOrganismo(array $datos, int $usuarioId): array {
        $fichaId = (int)($datos['ficha_id'] ?? 0);
        $infoFicha = $this->modelo->obtenerInfoFicha($fichaId);

        if (!$infoFicha || $infoFicha['estado_ficha'] !== 'En Proceso') {
            return ['success' => false, 'message' => "Solo se pueden asignar organismos a fichas En Proceso."];
        }

        $despachoId = $this->modelo->crear([
            'ficha_id'         => $fichaId,
            'organismo_id'     => $datos['organismo_id'],
            'unidad_designada'  => $datos['unidad_designada'],
            'mando_acargo'     => $datos['mando_acargo'],
            'persona_atiende'  => $datos['persona_atiende'] ?: null,
            'despachador_id'   => $usuarioId,
        ]);

        if ($despachoId) {
            $this->modeloEvento->registrarEventoFicha(
                $fichaId, $usuarioId, 'DESPACHO', null, null, null,
                ['despacho_id' => $despachoId, 'organismo_id' => $datos['organismo_id']],
                "Despacho #{$despachoId}: Organismo asignado."
            );

            if (!empty($infoFicha['id_user'])) {
                Notificador::enviarAUsuario((int)$infoFicha['id_user'], 'info', 'Organismo Despachado', "Se ha despachado un organismo a tu Ficha #{$fichaId}.", $fichaId);
            }
            Notificador::enviarPorRol(4, 'alerta', 'Nuevo Despacho', "Organismo asignado a la Ficha #{$fichaId}.", $fichaId);

            return ['success' => true, 'message' => "Despacho registrado correctamente.", 'id' => $despachoId];
        }

        return ['success' => false, 'message' => 'Error al registrar el despacho.'];
    }

    /**
     * Cambia el estatus de un despacho (Asignado -> En Camino -> etc).
     */
    public function cambiarEstadoDespacho(int $despachoId, string $nuevoEstado, int $usuarioId): array {
        $anterior = $this->modelo->obtenerPorId($despachoId);
        if (!$anterior) return ['success' => false, 'message' => 'Despacho no encontrado.'];

        if ($anterior['estatus_despacho'] === 'Liberado') {
            return ['success' => false, 'message' => 'Este despacho ya fue Liberado.'];
        }

        if ($this->modelo->cambiarEstado($despachoId, $nuevoEstado)) {
            $this->modeloEvento->registrarEventoFicha(
                (int)$anterior['ficha_id'], $usuarioId, 'DESPACHO',
                $anterior['estatus_despacho'], $nuevoEstado,
                null, null, "Despacho #{$despachoId}: '{$anterior['estatus_despacho']}' → '{$nuevoEstado}'."
            );
            return ['success' => true, 'message' => "Estatus actualizado.", 'nuevo_estado' => $nuevoEstado];
        }

        return ['success' => false, 'message' => 'Error al actualizar estatus.'];
    }

    /**
     * Cancela un despacho de organismo.
     */
    public function cancelarDespacho(int $despachoId, string $tipoMotivo, string $descripcion, int $usuarioId): array {
        $despacho = $this->modelo->obtenerPorId($despachoId);
        if (!$despacho) return ['success' => false, 'message' => 'Despacho no encontrado.'];

        if (in_array($despacho['estatus_despacho'], ['Liberado', 'Cancelado'], true)) {
            return ['success' => false, 'message' => 'El despacho ya está en estado terminal.'];
        }

        if ($this->modelo->cancelar($despachoId, $tipoMotivo, $descripcion)) {
            $this->modeloEvento->registrarEventoFicha(
                (int)$despacho['ficha_id'], $usuarioId, 'DESPACHO',
                $despacho['estatus_despacho'], 'Cancelado', null,
                ['motivo' => $tipoMotivo],
                "Despacho #{$despachoId} cancelado. Motivo: {$tipoMotivo}."
            );
            return ['success' => true, 'message' => "Despacho cancelado correctamente."];
        }

        return ['success' => false, 'message' => 'Error al cancelar despacho.'];
    }

    /**
     * Cambia el estado de una ficha (Atendido/Cerrado) con validación de integridad.
     */
    public function cambiarEstadoFicha(int $fichaId, string $nuevoEstado, int $usuarioId, string $usuarioNombre, string $motivo = '', string $tipoMotivo = ''): array {
        $infoFicha = $this->modelo->obtenerInfoFicha($fichaId);
        if (!$infoFicha) return ['success' => false, 'message' => 'Ficha no encontrada.'];

        if (in_array($infoFicha['estado_ficha'], ['Cerrado', 'Atendido'], true)) {
            return ['success' => false, 'message' => 'La ficha ya está en estado terminal.'];
        }

        // Integridad: No cerrar si hay organismos activos
        if (in_array($nuevoEstado, ['Cerrado', 'Atendido'])) {
            $despachosActivos = $this->modelo->contarDespachosActivos($fichaId);
            if ($despachosActivos > 0) {
                return [
                    'success' => false,
                    'message' => "No se puede marcar como {$nuevoEstado}: hay {$despachosActivos} organismo(s) en curso."
                ];
            }
        }

        $estadoAnterior = $infoFicha['estado_ficha'];
        $exito = $this->modeloFicha->cambiarEstado($fichaId, $nuevoEstado, $usuarioId, $motivo, $tipoMotivo);

        if ($exito) {
            $this->modeloEvento->registrarEventoFicha(
                $fichaId, $usuarioId, 'CAMBIO_ESTADO',
                $estadoAnterior, $nuevoEstado, null,
                ['motivo' => $motivo],
                "Ficha #{$fichaId} actualizada a '{$nuevoEstado}' por {$usuarioNombre}."
            );

            if (!empty($infoFicha['id_user'])) {
                Notificador::enviarAUsuario((int)$infoFicha['id_user'], 'cambio_estado', 'Estado de Ficha Actualizado', "Tu Ficha #{$fichaId} cambió a '{$nuevoEstado}'.", $fichaId);
            }
            Notificador::enviarPorRol(4, 'info', 'Actualización de Emergencia', "Ficha #{$fichaId} actualizada a '{$nuevoEstado}'.", $fichaId);

            return ['success' => true, 'message' => "Estado actualizado.", 'nuevo_estado' => $nuevoEstado];
        }

        return ['success' => false, 'message' => 'Error al actualizar estado de la ficha.'];
    }
}
