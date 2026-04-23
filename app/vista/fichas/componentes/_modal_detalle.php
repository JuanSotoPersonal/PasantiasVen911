<?php
/**
 * _modal_detalle.php - Visualizador de Trazabilidad de Emergencias
 * 
 * Interfaz modal para la consulta profunda de datos de una ficha.
 * Actúa como contenedor dinámico para la información procesada por JS y el
 * hub de control para cambios de estado (Despacho/Cierre).
 */
?>

<div class="modal fade" id="modalDetalleFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            
            <!-- 1. BLOQUE: CABECERA DE TRAZABILIDAD -->
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white">
                    <i class="bi bi-file-earmark-text-fill me-2"></i>Monitoreo de Ficha <span id="detalleFichaIdLabel" class="badge bg-white text-success">#</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- 2. BLOQUE: CONTENEDOR DE DATOS DINÁMICOS -->
            <div class="modal-body p-4" id="contenidoDetalleFicha">
                <!-- Estado Cero: Spinner de Carga -->
                <div class="text-center py-5">
                    <div class="spinner-border text-success shadow-sm" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Procesando...</span>
                    </div>
                    <p class="mt-3 text-secondary fw-semibold">Sincronizando información de la emergencia...</p>
                </div>
            </div>

            <!-- 3. BLOQUE: GESTIÓN DE ESTADOS Y ACCIONES -->
            <div class="modal-footer bg-light border-0 py-3 d-flex justify-content-between">
                <div id="contenedorCambioEstado" class="d-flex gap-2">
                    <!-- Los disparadores de estado se inyectan dinámicamente según RBAC y Status Logic -->
                </div>
                <button type="button" class="btn btn-ven-cancel px-4" data-bs-dismiss="modal">Finalizar Vista</button>
            </div>

        </div>
    </div>
</div>

