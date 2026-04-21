<div class="card card-usuarios-inactivos">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">
      <i class="bi bi-person-x-fill me-2"></i>Usuarios Inactivos
    </h3>
    <div class="card-tools d-flex align-items-center gap-2">
       <span class="badge fs-6" id="badge-count-inactivos">0 usuarios</span>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table
        id="tablaInactivos"
        class="table table-bordered table-striped table-hover align-middle table-usuarios-full w-100"
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
        <tbody id="tbody-inactivos">
           <!-- DataTables AJAX -->
        </tbody>
      </table>
    </div>
  </div>
</div>
