<?php
// Filtrar para el rol activo si se pasó desde el controlador
$rolActivoId = $rolActivoId ?? 0;

$iconosPorRol = [
    'Administrador' => 'bi-shield-fill',
    'Despachador'   => 'bi-broadcast',
    'Operador'      => 'bi-headset',
    'Jefe'          => 'bi-star-fill',
    'Jefatura'      => 'bi-star-fill',
];

foreach ($roles as $rol):

    
    // Si $rolActivoId es mayor a 0, solo renderizar ese rol
    if ($rolActivoId > 0 && $rol['id'] != $rolActivoId) continue;

    $rolId     = (int)$rol['id'];
    $rolNombre = htmlspecialchars($rol['nombre']);
    $tablaId   = "tablaRol{$rolId}";
    $icono     = $iconosPorRol[$rolNombre] ?? 'bi-person-badge';
?>
<div class="card card-usuarios mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">
      <i class="bi <?php echo $icono; ?> me-2"></i><?php echo $rolNombre; ?>s Registrados
    </h3>
    <div class="card-tools d-flex align-items-center gap-2">
       <span class="badge bg-success fs-6" id="badge-count-<?php echo $rolId; ?>">0 usuarios</span>
       <button
        type="button"
        class="btn btn-ven-primary btn-sm ms-3"
        data-bs-toggle="modal"
        data-bs-target="#modalCrearUsuario"
      >
        <i class="bi bi-person-plus-fill me-1"></i> Agregar
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table
        id="<?= $tablaId ?>"
        class="table table-bordered table-striped table-hover align-middle table-usuarios-full w-100"
        data-rol-id="<?= $rolId ?>"
        data-rol-nombre="<?= $rolNombre ?>"
      >
        <thead class="table-dark">
           <tr>
             <th>#</th>
             <th>Nombre Completo</th>
             <th>Usuario</th>
             <th>Cédula</th>
             <th>Estado</th>
             <th class="text-center">Acciones</th>
           </tr>
        </thead>
        <tbody><!-- DataTables AJAX --></tbody>
      </table>
    </div>
  </div>
</div>
<?php endforeach; ?>
