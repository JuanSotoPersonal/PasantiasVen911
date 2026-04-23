<!-- 
/**
 * COMPONENTE: TABLA DE HISTORIAL DE FICHAS
 * Propósito: Renderizar la estructura de la tabla DataTable para el historial 
 * de eventos relacionados con las fichas de emergencia.
 */
-->

<!-- 1. CONTENEDOR PRINCIPAL: TARJETA DE INFORMACIÓN -->
<div class="card card-ven shadow-sm mb-4 border-start border-4 border-success">
    
    <!-- 2. CABECERA DE LA TARJETA -->
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
        <h3 class="card-title mb-0">
            <i class="bi bi-clock-history me-2 text-success"></i>Trazabilidad de Fichas Operativas
        </h3>
    </div>

    <!-- 3. CUERPO DE LA TARJETA: ESTRUCTURA DE LA TABLA -->
    <div class="card-body">
        <div class="table-responsive">
            <!-- La tabla se inicializa y puebla mediante DataTables (Server-side) -->
            <table id="tablaEventosFichas" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-dark">
                    <tr>
                        <th>Evento</th>
                        <th>Ficha ID</th>
                        <th>Estado Ant.</th>
                        <th>Estado Nuevo</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th class="text-center">Cambios</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- El contenido es cargado dinámicamente vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>

</div>
