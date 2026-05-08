<?php
/**
 * Componente: Formulario de Filtros de Búsqueda
 */
?>
<div class="card shadow-sm border-0 rounded-4 mb-4 sticky-filters">
    <div class="card-header bg-white p-3 border-bottom">
        <h5 class="card-title fw-bold mb-0">
            <i class="bi bi-funnel-fill text-primary me-1"></i> Filtros
        </h5>
    </div>
    <div class="card-body p-3">
        <form id="formFiltrosReporte">
            <!-- Rango de Fechas -->
            <div class="mb-3">
                <label class="form-label fw-medium small">Desde:</label>
                <input type="date" class="form-control" name="desde" id="filtro_desde" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium small">Hasta:</label>
                <input type="date" class="form-control" name="hasta" id="filtro_hasta" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <hr class="my-3 opacity-10">

            <!-- Municipio -->
            <div class="mb-3">
                <label class="form-label fw-medium small">Municipio:</label>
                <select class="form-select select2" name="municipio_id" id="filtro_municipio">
                    <option value="">Todos los municipios</option>
                    <?php foreach ($datos['municipios'] as $m): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo $m['nombre_municipio']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Emergencia -->
            <div class="mb-3">
                <label class="form-label fw-medium small">Tipo de Emergencia:</label>
                <select class="form-select select2" name="tipo_emergencia_id" id="filtro_emergencia">
                    <option value="">Todas las emergencias</option>
                    <?php foreach ($datos['tipos_emergencia'] as $e): ?>
                        <option value="<?php echo $e['id']; ?>"><?php echo $e['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Operador -->
            <div class="mb-3">
                <label class="form-label fw-medium small">Operador:</label>
                <select class="form-select select2" name="usuario_id" id="filtro_operador">
                    <option value="">Todos los operadores</option>
                    <?php foreach ($datos['operadores'] as $o): ?>
                        <option value="<?php echo $o['id']; ?>"><?php echo $o['nombre_completo']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Estado -->
            <div class="mb-3">
                <label class="form-label fw-medium small">Estado de Ficha:</label>
                <select class="form-select" name="estado" id="filtro_estado">
                    <option value="">Todos los estados</option>
                    <option value="Pendiente">Pendientes</option>
                    <option value="Atendido">Atendidos</option>
                    <option value="Cerrado">Cerrados</option>
                </select>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary" id="btnFiltrar">
                    <i class="bi bi-search me-1"></i> Generar Búsqueda
                </button>
                <button type="button" class="btn btn-light" id="btnLimpiarFiltros">
                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                </button>
            </div>
        </form>
    </div>
</div>
