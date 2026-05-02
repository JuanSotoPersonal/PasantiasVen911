<?php
/**
 * _configuracion.php - Centro de Gestión Referencial del Sistema
 * 
 * Este componente centraliza la administración de los catálogos maestros:
 * Tipos de Emergencia, Casos, Municipios, Parroquias y Organismos.
 * Solo accesible para usuarios con privilegios de gestión.
 */
?>

<div class="row">

    <!-- 1. BLOQUE: NAVEGACIÓN TÁCTICA (TABS) -->
    <div class="col-12 mb-3">
        <ul class="nav nav-tabs nav-tabs-ven shadow-sm rounded-top" id="tabsConfiguracion" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab-tipos-btn" data-bs-toggle="tab" data-bs-target="#tab-tipos" type="button">
                    <i class="bi bi-lightning-charge-fill me-1"></i>Tipos de Emergencia
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-casos-btn" data-bs-toggle="tab" data-bs-target="#tab-casos" type="button">
                    <i class="bi bi-list-ul me-1"></i>Casos
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-municipios-btn" data-bs-toggle="tab" data-bs-target="#tab-municipios" type="button">
                    <i class="bi bi-map-fill me-1"></i>Municipios
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-parroquias-btn" data-bs-toggle="tab" data-bs-target="#tab-parroquias" type="button">
                    <i class="bi bi-geo-alt-fill me-1"></i>Parroquias
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-organismos-btn" data-bs-toggle="tab" data-bs-target="#tab-organismos" type="button">
                    <i class="bi bi-building-fill me-1"></i>Organismos
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-motivos-btn" data-bs-toggle="tab" data-bs-target="#tab-motivos" type="button">
                    <i class="bi bi-x-circle-fill me-1"></i>Motivos de Cierre
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-motivos-organismo-btn" data-bs-toggle="tab" data-bs-target="#tab-motivos-organismo" type="button">
                    <i class="bi bi-slash-circle-fill me-1"></i>Motivos de Cancelación
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-inactivos-btn" data-bs-toggle="tab" data-bs-target="#tab-inactivos" type="button">
                    <i class="bi bi-trash-fill me-1 text-danger"></i>Inhabilitados
                </button>
            </li>
        </ul>
    </div>

    <!-- 2. BLOQUE: CONTENEDORES DE CATÁLOGO (PANES) -->
    <div class="col-12 tab-content" id="contenidoConfiguracion">

        <!-- TAB: TIPOS DE EMERGENCIA -->
        <div class="tab-pane fade show active" id="tab-tipos" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-bottom">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold text-success"><i class="bi bi-lightning-charge-fill me-2"></i>Tipos de Emergencia</h5>
                    <div class="card-tools ms-auto">
                        <button class="btn btn-ven-primary btn-sm rounded-pill px-3" id="btnNuevoTipo">
                            <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Tipo
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaTipos" class="table table-bordered table-striped table-hover align-middle w-100">
                            <thead class="table-dark">
                                <tr><th width="60">#</th><th>Nombre</th><th>Descripción</th><th width="100">Estado</th><th width="110" class="text-center">Acciones</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: CASOS -->
        <div class="tab-pane fade" id="tab-casos" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-bottom">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold text-success"><i class="bi bi-list-ul me-2"></i>Catálogo de Casos por Incidente</h5>
                    <div class="card-tools ms-auto">
                        <button class="btn btn-ven-primary btn-sm rounded-pill px-3" id="btnNuevoCaso">
                            <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Caso
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center gap-3">
                        <label for="filtroCasoTipo" class="form-label fw-semibold mb-0">Filtrar por Incidente:</label>
                        <select id="filtroCasoTipo" class="form-select form-select-sm w-auto border-2 shadow-sm">
                            <option value="">— Ver Todos los Tipos —</option>
                            <?php foreach ($tiposEmergencia as $tipo): ?>
                                <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table id="tablaCasos" class="table table-bordered table-striped table-hover align-middle w-100">
                            <thead class="table-dark">
                                <tr><th width="60">#</th><th>Caso</th><th class="text-nowrap">Tipo de Emergencia</th><th>Descripción</th><th width="100">Estado</th><th width="110" class="text-center">Acciones</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: MUNICIPIOS -->
        <div class="tab-pane fade" id="tab-municipios" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-bottom">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold text-success"><i class="bi bi-map-fill me-2"></i>Municipios Administrativos</h5>
                    <div class="card-tools ms-auto">
                        <button class="btn btn-ven-primary btn-sm rounded-pill px-3" id="btnNuevoMunicipio">
                            <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Municipio
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaMunicipios" class="table table-bordered table-striped table-hover align-middle w-100">
                            <thead class="table-dark">
                                <tr><th width="60">#</th><th class="text-nowrap">Nombre del Municipio</th><th>Descripción</th><th width="100">Estado</th><th width="110" class="text-center text-white">Acciones</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: PARROQUIAS -->
        <div class="tab-pane fade" id="tab-parroquias" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-bottom">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold text-success"><i class="bi bi-geo-alt-fill me-2"></i>Parroquias Locales</h5>
                    <div class="card-tools ms-auto">
                        <button class="btn btn-ven-primary btn-sm rounded-pill px-3" id="btnNuevaParroquia">
                            <i class="bi bi-plus-circle-fill me-1"></i>Nueva Parroquia
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center gap-3">
                        <label for="filtroParroquiaMunicipio" class="form-label fw-semibold mb-0">Localizar por Municipio:</label>
                        <select id="filtroParroquiaMunicipio" class="form-select form-select-sm w-auto border-2 shadow-sm">
                            <option value="">— Ver Todos los Municipios —</option>
                            <?php foreach ($municipios as $municipio): ?>
                                <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nombre_municipio']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table id="tablaParroquias" class="table table-bordered table-striped table-hover align-middle w-100">
                            <thead class="table-dark">
                                <tr><th width="60">#</th><th>Parroquia</th><th>Municipio</th><th>Descripción</th><th width="100">Estado</th><th width="110" class="text-center text-white">Acciones</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: ORGANISMOS -->
        <div class="tab-pane fade" id="tab-organismos" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-bottom">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold text-success"><i class="bi bi-building-fill me-2"></i>Organismos de Respuesta Inmediata</h5>
                    <div class="card-tools ms-auto">
                        <button class="btn btn-ven-primary btn-sm rounded-pill px-3" id="btnNuevoOrganismo">
                            <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Organismo
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaOrganismos" class="table table-bordered table-striped table-hover align-middle w-100">
                            <thead class="table-dark">
                                <tr><th width="60">#</th><th class="text-nowrap">Nombre del Organismo</th><th>Descripción</th><th width="100">Estado</th><th width="110" class="text-center text-white">Acciones</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: MOTIVOS DE CIERRE (FICHA) -->
        <div class="tab-pane fade" id="tab-motivos" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-bottom">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold text-success">
                        <i class="bi bi-x-circle-fill me-2"></i>Motivos de Cierre
                        <span class="badge bg-success-subtle text-success ms-2" style="font-size:0.65rem;">FICHA</span>
                    </h5>
                    <div class="card-tools ms-auto">
                        <button class="btn btn-ven-primary btn-sm rounded-pill px-3" id="btnNuevoMotivo">
                            <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Motivo
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaMotivos" class="table table-bordered table-striped table-hover align-middle w-100">
                            <thead class="table-dark">
                                <tr><th width="60">#</th><th class="text-nowrap">Nombre del Motivo</th><th>Descripción</th><th width="100">Estado</th><th width="110" class="text-center text-white">Acciones</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: MOTIVOS DE CANCELACIÓN (ORGANISMO) -->
        <div class="tab-pane fade" id="tab-motivos-organismo" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-bottom">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold text-success">
                        <i class="bi bi-slash-circle-fill me-2"></i>Motivos de Cancelación de Organismo
                        <span class="badge bg-success-subtle text-success ms-2" style="font-size:0.65rem;">ORGANISMO</span>
                    </h5>
                    <div class="card-tools ms-auto">
                        <button class="btn btn-ven-primary btn-sm rounded-pill px-3" id="btnNuevoMotivoOrganismo">
                            <i class="bi bi-plus-circle-fill me-1"></i>Nuevo Motivo
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaMotivosOrganismo" class="table table-bordered table-striped table-hover align-middle w-100">
                            <thead class="table-dark">
                                <tr><th width="60">#</th><th class="text-nowrap">Nombre del Motivo</th><th>Descripción</th><th width="100">Estado</th><th width="110" class="text-center text-white">Acciones</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. BLOQUE: EL COFRE DE INACTIVOS (PAPELERA TÉCNICA) -->
        <div class="tab-pane fade" id="tab-inactivos" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-bottom overflow-hidden">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold text-danger">
                        <i class="bi bi-archive-fill me-2"></i>Gestión de Registros Inhabilitados
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Mini-navegación para catálogos inactivos -->
                    <ul class="nav nav-pills nav-pills-ven mb-4 gap-2" id="pills-inactivos" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-2 p-3 shadow-sm d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#inactivos-tipos">
                                Tipos <span class="badge bg-white text-dark ms-2 shadow-sm" id="count-inactivos-tipos">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 p-3 shadow-sm d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#inactivos-casos">
                                Casos <span class="badge bg-white text-dark ms-2 shadow-sm" id="count-inactivos-casos">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 p-3 shadow-sm d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#inactivos-municipios">
                                Municipios <span class="badge bg-white text-dark ms-2 shadow-sm" id="count-inactivos-municipios">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 p-3 shadow-sm d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#inactivos-parroquias">
                                Parroquias <span class="badge bg-white text-dark ms-2 shadow-sm" id="count-inactivos-parroquias">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 p-3 shadow-sm d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#inactivos-organismos">
                                Organismos <span class="badge bg-white text-dark ms-2 shadow-sm" id="count-inactivos-organismos">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 p-3 shadow-sm d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#inactivos-motivos">
                                Motivos Ficha <span class="badge bg-white text-dark ms-2 shadow-sm" id="count-inactivos-motivos">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 p-3 shadow-sm d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#inactivos-motivos-org">
                                Motivos Organismo <span class="badge bg-white text-dark ms-2 shadow-sm" id="count-inactivos-motivos-org">0</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-inactivos-contenido">
                        <div class="tab-pane fade show active" id="inactivos-tipos">
                            <table id="tablaTiposInactivos" class="table table-sm table-bordered table-striped table-hover w-100">
                                <thead class="table-dark"><tr><th>#</th><th>Nombre</th><th>Estado</th><th>Acciones</th></tr></thead>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="inactivos-casos">
                            <table id="tablaCasosInactivos" class="table table-sm table-bordered table-striped table-hover w-100">
                                <thead class="table-dark"><tr><th>#</th><th>Caso</th><th>Tipo</th><th>Estado</th><th>Acciones</th></tr></thead>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="inactivos-municipios">
                            <table id="tablaMunicipiosInactivos" class="table table-sm table-bordered table-striped table-hover w-100">
                                <thead class="table-dark"><tr><th>#</th><th>Municipio</th><th>Estado</th><th>Acciones</th></tr></thead>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="inactivos-parroquias">
                            <table id="tablaParroquiasInactivos" class="table table-sm table-bordered table-striped table-hover w-100">
                                <thead class="table-dark"><tr><th>#</th><th>Parroquia</th><th>Municipio</th><th>Estado</th><th>Acciones</th></tr></thead>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="inactivos-organismos">
                            <table id="tablaOrganismosInactivos" class="table table-sm table-bordered table-striped table-hover w-100">
                                <thead class="table-dark"><tr><th>#</th><th>Organismo</th><th>Estado</th><th>Acciones</th></tr></thead>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="inactivos-motivos">
                            <table id="tablaMotivosInactivos" class="table table-sm table-bordered table-striped table-hover w-100">
                                <thead class="table-dark"><tr><th>#</th><th>Motivo (Cierre Ficha)</th><th>Estado</th><th>Acciones</th></tr></thead>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="inactivos-motivos-org">
                            <table id="tablaMotivosOrganismoInactivos" class="table table-sm table-bordered table-striped table-hover w-100">
                                <thead class="table-dark"><tr><th>#</th><th>Motivo (Cancelación Org.)</th><th>Estado</th><th>Acciones</th></tr></thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- fin tab-content -->
</div>

<!-- 4. BLOQUE: CAPA DE GESTIÓN (MODALES DE CONFIGURACIÓN) -->

<!-- Modal: Catálogo Simple (Nombre/Descripción) -->
<div class="modal fade" id="modalCatalogoSimple" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white" id="modalCatalogoSimpleTitulo">Gestión de Catálogo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formCatalogoSimple" novalidate>
                    <input type="hidden" id="cat_simple_catalogo" name="catalogo">
                    <input type="hidden" id="cat_simple_accion"   name="accion">
                    <input type="hidden" id="cat_simple_id"       name="id">
                    <input type="hidden" id="cat_simple_contexto" name="contexto" value="ficha">
                    <div class="mb-3">
                        <label id="cat_simple_label" for="cat_simple_valor" class="form-label fw-bold small text-secondary text-uppercase">Nombre del Registro</label>
                        <input type="text" class="form-control shadow-sm border-2" id="cat_simple_valor" name="nombre" autocomplete="off">
                        <div class="form-text mt-1" style="font-size: 0.75rem;">Nombre único para el registro.</div>
                    </div>
                    <div class="mb-3">
                        <label for="cat_simple_descripcion" class="form-label fw-bold small text-secondary text-uppercase">Breve Descripción</label>
                        <textarea class="form-control shadow-sm border-2" id="cat_simple_descripcion" name="descripcion" rows="2" placeholder="Información adicional..."></textarea>
                        <div class="form-text mt-1" style="font-size: 0.75rem;">Breve contexto sobre el uso de este registro.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-ven-cancel px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-ven-primary px-4 shadow-sm" id="btnGuardarCatSimple">
                    <i class="bi bi-save-fill me-2"></i>Consolidar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Caso Específico -->
<div class="modal fade" id="modalCaso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white" id="modalCasoTitulo">Nuevo Caso de Emergencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formCaso" novalidate>
                    <input type="hidden" name="catalogo" value="caso">
                    <input type="hidden" id="caso_accion" name="accion" value="crear">
                    <input type="hidden" id="caso_id"     name="id"     value="0">
                    <div class="mb-3">
                        <label for="caso_tipo_id" class="form-label fw-bold small text-secondary text-uppercase">Vincular a Tipo de Incidente <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm border-2" id="caso_tipo_id" name="tipo_emergencia_id">
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($tiposEmergencia as $tipo): ?>
                                <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text mt-1" style="font-size: 0.75rem;">El caso dependerá de esta categoría superior.</div>
                    </div>
                    <div class="mb-3">
                        <label for="caso_nombre" class="form-label fw-bold small text-secondary text-uppercase">Nombre del Caso <span class="text-danger">*</span></label>
                        <input type="text" class="form-control shadow-sm border-2" id="caso_nombre" name="nombre_caso" placeholder="Ej: Choque con heridos">
                        <div class="form-text mt-1" style="font-size: 0.75rem;">Denominación técnica del evento.</div>
                    </div>
                    <div class="mb-3">
                        <label for="caso_descripcion" class="form-label fw-bold small text-secondary text-uppercase">Descripción del Escenario</label>
                        <textarea class="form-control shadow-sm border-2" id="caso_descripcion" name="descripcion" rows="2"></textarea>
                        <div class="form-text mt-1" style="font-size: 0.75rem;">Detalle qué situaciones abarca este caso.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-ven-cancel px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-ven-primary px-4 shadow-sm" id="btnGuardarCaso">
                    <i class="bi bi-save-fill me-2"></i>Registrar Caso
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Parroquia Geográfica -->
<div class="modal fade" id="modalParroquia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white" id="modalParroquiaTitulo">Nueva Localidad Parroquial</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formParroquia" novalidate>
                    <input type="hidden" name="catalogo" value="parroquia">
                    <input type="hidden" id="parroquia_accion" name="accion" value="crear">
                    <input type="hidden" id="parroquia_id"     name="id"     value="0">
                    <div class="mb-3">
                        <label for="parroquia_municipio_id" class="form-label fw-bold small text-secondary text-uppercase">Pertenece al Municipio <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm border-2" id="parroquia_municipio_id" name="municipio_id">
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($municipios as $municipio): ?>
                                <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nombre_municipio']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text mt-1" style="font-size: 0.75rem;">Vincular parroquia a su municipio correspondiente.</div>
                    </div>
                    <div class="mb-3">
                        <label for="parroquia_nombre" class="form-label fw-bold small text-secondary text-uppercase">Nombre de la Parroquia <span class="text-danger">*</span></label>
                        <input type="text" class="form-control shadow-sm border-2" id="parroquia_nombre" name="nombre_parroquia">
                        <div class="form-text mt-1" style="font-size: 0.75rem;">Nombre oficial de la parroquia local.</div>
                    </div>
                    <div class="mb-3">
                        <label for="parroquia_descripcion" class="form-label fw-bold small text-secondary text-uppercase">Información Geográfica</label>
                        <textarea class="form-control shadow-sm border-2" id="parroquia_descripcion" name="descripcion" rows="2"></textarea>
                        <div class="form-text mt-1" style="font-size: 0.75rem;">Notas sobre límites o sectores principales.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-ven-cancel px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-ven-primary px-4 shadow-sm" id="btnGuardarParroquia">
                    <i class="bi bi-save-fill me-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

