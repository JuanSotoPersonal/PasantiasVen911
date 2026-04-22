<div class="card card-usuarios">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">
      <i class="bi bi-people-fill me-2"></i>Listado General de Usuarios
    </h3>
    <div class="card-tools ms-auto d-flex align-items-center">
      <span class="badge bg-success fs-6 me-3" id="badge-count-total">— usuarios</span>
      <?php if (tienePerm('usuarios', 'crear')): ?>
        <button
          type="button"
          class="btn btn-ven-primary btn-sm"
          id="btn-abrir-modal-crear"
          data-bs-toggle="modal"
          data-bs-target="#modalCrearUsuario"
        >
          <i class="bi bi-person-plus-fill me-1"></i> Agregar Usuario
        </button>
      <?php endif; ?>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table
        id="tablaUsuarios"
        class="table table-bordered table-striped table-hover align-middle table-usuarios-full"
      >
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Nombre Completo</th>
            <th>Usuario</th>
            <th>Cédula</th>
            <th>Rol</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody-usuarios">
          <!-- DataTables lo llena via AJAX -->
        </tbody>
      </table>
    </div>
  </div>
</div>
