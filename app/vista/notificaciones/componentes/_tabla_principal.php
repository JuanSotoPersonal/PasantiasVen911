<?php
/**
 * COMPONENTE: _tabla_principal.php
 * Propósito: Define la estructura HTML del DataTable de Notificaciones.
 */
?>
<div class="table-responsive">
    <table id="tablaNotificaciones" class="table table-hover align-middle w-100">
        <thead class="table-light">
            <tr>
                <th width="5%" class="text-center"><i class="bi bi-envelope"></i></th>
                <th width="15%">Categoría</th>
                <th width="20%">Asunto</th>
                <th width="35%">Mensaje</th>
                <th width="10%" class="text-center">Ficha</th>
                <th width="15%" class="text-end">Fecha</th>
            </tr>
        </thead>
        <tbody>
            <!-- Poblado vía AJAX por DataTables -->
        </tbody>
    </table>
</div>
