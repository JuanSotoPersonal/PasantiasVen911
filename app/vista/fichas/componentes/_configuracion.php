<!-- _configuracion.php: Gestión de configuración (referenciales) embebidos en Fichas (solo Admin) -->
<div class="row">
  <!-- === TABS DE NAVEGACIÓN === -->
  <div class="col-12 mb-3">
    <ul class="nav nav-tabs nav-tabs-ven" id="tabsConfiguracion" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-tipos-btn" data-bs-toggle="tab" data-bs-target="#tab-tipos" type="button">
          <i class="bi bi-lightning-charge-fill me-1"></i>Tipos de Emergencia
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-casos-btn" data-bs-toggle="tab" data-bs-target="#tab-casos" type="button">
          <i class="bi bi-list-ul me-1"></i>Casos
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-municipios-btn" data-bs-toggle="tab" data-bs-target="#tab-municipios" type="button">
          <i class="bi bi-map-fill me-1"></i>Municipios
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-parroquias-btn" data-bs-toggle="tab" data-bs-target="#tab-parroquias" type="button">
          <i class="bi bi-geo-alt-fill me-1"></i>Parroquias
        </button>
      </li>
    </ul>
  </div>

  <div class="col-12 tab-content" id="contenidoConfiguracion">

    <!-- ================================================ -->
    <!-- TAB: TIPOS DE EMERGENCIA -->
    <!-- ================================================ -->
    <div class="tab-pane fade show active" id="tab-tipos" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0"><i class="bi bi-lightning-charge-fill me-2"></i>Tipos de Emergencia</h5>
          <button class="btn btn-ven-primary btn-sm" id="btnNuevoTipo">
            <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Tipo
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="tablaTipos" class="table table-bordered table-striped table-hover align-middle w-100">
              <thead class="table-dark">
                <tr>
                  <th width="60">#</th>
                  <th>Nombre</th>
                  <th width="110" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ================================================ -->
    <!-- TAB: CASOS -->
    <!-- ================================================ -->
    <div class="tab-pane fade" id="tab-casos" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0"><i class="bi bi-list-ul me-2"></i>Casos por Tipo</h5>
          <button class="btn btn-ven-primary btn-sm" id="btnNuevoCaso">
            <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Caso
          </button>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="filtroCasoTipo" class="form-label fw-semibold">Filtrar por tipo:</label>
            <select id="filtroCasoTipo" class="form-select form-select-sm w-auto d-inline-block ms-2">
              <option value="">— Todos los tipos —</option>
              <?php foreach ($tiposEmergencia as $tipo): ?>
                <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="table-responsive">
            <table id="tablaCasos" class="table table-bordered table-striped table-hover align-middle w-100">
              <thead class="table-dark">
                <tr>
                  <th width="60">#</th>
                  <th>Caso</th>
                  <th>Tipo de Emergencia</th>
                  <th>Descripción</th>
                  <th width="110" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ================================================ -->
    <!-- TAB: MUNICIPIOS -->
    <!-- ================================================ -->
    <div class="tab-pane fade" id="tab-municipios" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0"><i class="bi bi-map-fill me-2"></i>Municipios</h5>
          <button class="btn btn-ven-primary btn-sm" id="btnNuevoMunicipio">
            <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Municipio
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="tablaMunicipios" class="table table-bordered table-striped table-hover align-middle w-100">
              <thead class="table-dark">
                <tr>
                  <th width="60">#</th>
                  <th>Nombre del Municipio</th>
                  <th width="110" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ================================================ -->
    <!-- TAB: PARROQUIAS -->
    <!-- ================================================ -->
    <div class="tab-pane fade" id="tab-parroquias" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Parroquias</h5>
          <button class="btn btn-ven-primary btn-sm" id="btnNuevaParroquia">
            <i class="bi bi-plus-circle-fill me-1"></i>Nueva Parroquia
          </button>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="filtroParroquiaMunicipio" class="form-label fw-semibold">Filtrar por municipio:</label>
            <select id="filtroParroquiaMunicipio" class="form-select form-select-sm w-auto d-inline-block ms-2">
              <option value="">— Todos los municipios —</option>
              <?php foreach ($municipios as $municipio): ?>
                <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nombre_municipio']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="table-responsive">
            <table id="tablaParroquias" class="table table-bordered table-striped table-hover align-middle w-100">
              <thead class="table-dark">
                <tr>
                  <th width="60">#</th>
                  <th>Parroquia</th>
                  <th>Municipio</th>
                  <th width="110" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div><!-- fin tab-content -->
</div>

<!-- ================================================ -->
<!-- MODALES DE CONFIGURACIÓN -->
<!-- ================================================ -->

<!-- Modal genérico de 1 campo (Tipo / Municipio) -->
<div class="modal fade" id="modalCatalogoSimple" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header modal-header-ven">
        <h5 class="modal-title text-white" id="modalCatalogoSimpleTitulo">Nuevo Registro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formCatalogoSimple" novalidate>
          <input type="hidden" id="cat_simple_catalogo" name="catalogo">
          <input type="hidden" id="cat_simple_accion"   name="accion">
          <input type="hidden" id="cat_simple_id"       name="id">
          <div class="mb-3">
            <label id="cat_simple_label" for="cat_simple_valor" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="cat_simple_valor" name="nombre" autocomplete="off">
          </div>
        </form>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-ven-primary" id="btnGuardarCatSimple">
          <i class="bi bi-save-fill me-1"></i>Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Caso -->
<div class="modal fade" id="modalCaso" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header modal-header-ven">
        <h5 class="modal-title text-white" id="modalCasoTitulo">Nuevo Caso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formCaso" novalidate>
          <input type="hidden" name="catalogo" value="caso">
          <input type="hidden" id="caso_accion" name="accion" value="crear">
          <input type="hidden" id="caso_id"     name="id"     value="0">
          <div class="mb-3">
            <label for="caso_tipo_id" class="form-label">Tipo de Emergencia <span class="text-danger">*</span></label>
            <select class="form-select" id="caso_tipo_id" name="tipo_emergencia_id">
              <option value="">-- Seleccione --</option>
              <?php foreach ($tiposEmergencia as $tipo): ?>
                <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="caso_nombre" class="form-label">Nombre del Caso <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="caso_nombre" name="nombre_caso" autocomplete="off">
          </div>
          <div class="mb-3">
            <label for="caso_descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="caso_descripcion" name="descripcion" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-ven-primary" id="btnGuardarCaso">
          <i class="bi bi-save-fill me-1"></i>Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Parroquia -->
<div class="modal fade" id="modalParroquia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header modal-header-ven">
        <h5 class="modal-title text-white" id="modalParroquiaTitulo">Nueva Parroquia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formParroquia" novalidate>
          <input type="hidden" name="catalogo" value="parroquia">
          <input type="hidden" id="parroquia_accion" name="accion" value="crear">
          <input type="hidden" id="parroquia_id"     name="id"     value="0">
          <div class="mb-3">
            <label for="parroquia_municipio_id" class="form-label">Municipio <span class="text-danger">*</span></label>
            <select class="form-select" id="parroquia_municipio_id" name="municipio_id">
              <option value="">-- Seleccione --</option>
              <?php foreach ($municipios as $municipio): ?>
                <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nombre_municipio']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="parroquia_nombre" class="form-label">Nombre de la Parroquia <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="parroquia_nombre" name="nombre_parroquia" autocomplete="off">
          </div>
        </form>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-ven-primary" id="btnGuardarParroquia">
          <i class="bi bi-save-fill me-1"></i>Guardar
        </button>
      </div>
    </div>
  </div>
</div>
