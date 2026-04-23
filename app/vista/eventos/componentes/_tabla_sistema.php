<!-- 
/**
 * COMPONENTE: TABLA DE AUDITORÍA DEL SISTEMA
 * Propósito: Renderizar la estructura de la tabla DataTable para el historial 
 * general de acciones y auditoría del sistema.
 */
-->

<!-- 1. CONTENEDOR PRINCIPAL: TARJETA DE AUDITORÍA -->
<div class="card card-ven shadow-sm mb-4 border-start border-4 border-success">
    
    <!-- 2. CABECERA DE LA TARJETA -->
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
        <h3 class="card-title mb-0">
            <i class="bi bi-activity me-2 text-success"></i>Logs de Actividad del Sistema
        </h3>
    </div>

    <!-- 3. CUERPO DE LA TARJETA: ESTRUCTURA DE LA TABLA -->
    <div class="card-body">
        <div class="table-responsive">
            <!-- La tabla se inicializa y puebla mediante DataTables (Server-side) -->
            <table id="tablaEventos" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-dark">
                    <tr>
                        <th>Acción</th>
                        <th>Tabla</th>
                        <th>Registro ID</th>
                        <th>Administrador</th>
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
