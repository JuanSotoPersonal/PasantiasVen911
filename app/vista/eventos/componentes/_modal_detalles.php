      <!-- MODAL PARA VER DETALLES (ESTILO VEN911) -->
      <div class="modal fade" id="modalDetallesEvento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg">
            <div class="modal-header modal-header-ven">
              <h5 class="modal-title text-white">
                <i class="bi bi-journal-text me-2"></i>Detalle de la Operación
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                   <h6 class="fw-bold text-success"><i class="bi bi-arrow-down-circle-fill me-2"></i>Valor Anterior</h6>
                   <div id="contentValorAnterior" class="p-3 bg-light border rounded json-container">
                     <!-- JSON formateado -->
                   </div>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                   <h6 class="fw-bold text-primary"><i class="bi bi-arrow-up-circle-fill me-2"></i>Valor Nuevo</h6>
                   <div id="contentValorNuevo" class="p-3 bg-light border rounded json-container">
                     <!-- JSON formateado -->
                   </div>
                </div>
              </div>
              <div class="mt-4">
                 <h6 class="fw-bold text-secondary"><i class="bi bi-info-circle-fill me-2"></i>Descripción Adicional</h6>
                 <div id="contentDetalles" class="p-3 bg-light border-start border-4 border-success rounded-end mt-1">
                    <!-- Texto de detalles -->
                 </div>
              </div>
            </div>
            <div class="modal-footer border-0">
               <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
