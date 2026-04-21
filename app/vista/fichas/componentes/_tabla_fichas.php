<div class="card shadow-sm mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">
      <?php if ($tabActiva === 'configuracion'): ?>
        <i class="bi bi-gear-fill me-2"></i>Configuración del Sistema
      <?php else: ?>
        <i class="bi bi-file-earmark-medical-fill me-2"></i>
        <?php
          $titulos = [
            'todas'      => 'Todas las Fichas',
            'pendientes' => 'Fichas Pendientes',
            'en_proceso' => 'Fichas En Proceso',
            'cerradas'   => 'Fichas Cerradas',
          ];
          echo $titulos[$tabActiva] ?? 'Fichas de Emergencia';
        ?>
      <?php endif; ?>
    </h3>
    <?php if (tienePerm('fichas', 'crear') && $tabActiva !== 'catalogos'): ?>
    <button class="btn btn-ven-primary btn-sm" id="btnNuevaFicha">
      <i class="bi bi-plus-circle-fill me-1"></i> Nueva Ficha
    </button>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="tablaFichas" class="table table-bordered table-striped table-hover align-middle w-100"
             data-estado="<?= htmlspecialchars($estadoFiltro) ?>">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Solicitante</th>
            <th>Caso</th>
            <th>Parroquia</th>
            <th>Estado</th>
            <th>Fecha Creación</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody><!-- Cargado por AJAX --></tbody>
      </table>
    </div>
  </div>
</div>
