<?php
/**
 * _tabla_fichas.php - Contenedor Principal de Datos (DataTables)
 * 
 * Renderiza la estructura de la tabla para el listado de emergencias.
 * La carga de datos se realiza de forma asíncrona (Server-Side) mediante
 * el motor de fichas_datatable.js utilizando el estado de filtrado actual.
 */
?>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
    
    <!-- Cabecera de la Tabla: Contexto y Acciones -->
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0 fw-bold text-success">
            <i class="bi bi-file-earmark-medical-fill me-2 text-warning"></i>
            <?php
            echo match($tabActiva) {
                'todas'       => 'Todas las Fichas',
                'pendientes' => 'Fichas Pendientes',
                'en_proceso' => 'Fichas en Proceso',
                'cerradas'   => 'Fichas Cerradas (Historial)',
                'finalizadas'=> 'Fichas Finalizadas',
                default      => 'Fichas de Emergencia'
            };
            ?>
        </h3>

        <!-- Herramienta de Registro (Solo autorizados) -->
        <?php if (tienePerm('fichas', 'crear')): ?>
            <div class="card-tools ms-auto">
                <button class="btn btn-ven-primary btn-sm px-3 shadow-sm rounded-pill" id="btnNuevaFicha">
                    <i class="bi bi-plus-circle-fill me-1"></i> Abrir Nueva Ficha
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cuerpo de la Tabla: Estructura DataTables -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaFichas" 
                   class="table table-bordered table-striped table-hover align-middle w-100"
                   data-estado="<?= htmlspecialchars($estadoFiltro) ?>">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Solicitante</th>
                        <th>Tipo de Caso</th>
                        <th>Parroquia</th>
                        <th>Estado</th>
                        <th>Apertura</th>
                        <th class="text-center pe-3">Gestión</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- La data se inyecta dinámicamente mediante Server-Side Processing -->
                </tbody>
            </table>
        </div>
    </div>

</div>

