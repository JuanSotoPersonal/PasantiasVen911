<!-- MODAL DETALLE FICHA -->
<div class="modal fade" id="modalDetalleFicha" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header modal-header-ven">
        <h5 class="modal-title text-white">
          <i class="bi bi-card-text me-2"></i>Detalle Ficha <span id="detalleFichaIdLabel">#</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contenidoDetalleFicha">
        <div class="text-center py-4">
          <div class="spinner-border text-success" role="status"></div>
          <p class="mt-2 text-muted">Cargando datos...</p>
        </div>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between">
        <div id="contenedorCambioEstado">
          <!-- Los botones de estado se insertan dinámicamente por JS según el estado actual y el rol del usuario -->
        </div>
        <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
