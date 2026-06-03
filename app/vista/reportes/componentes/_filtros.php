<?php
/**
 * Componente: Formulario de Filtros de Búsqueda de Reportes Mejorado
 * Consolida filtros mediante selectores y agrupa opciones avanzadas en acordeón.
 */
?>
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white p-3 border-bottom">
        <h5 class="card-title fw-bold mb-0 text-success">
            <i class="bi bi-funnel-fill me-1"></i> Configurar Reporte
        </h5>
    </div>
    <div class="card-body p-3">
        <form id="formFiltrosReporte" method="POST">
            
            <!-- 1. TIPO DE REPORTE -->
            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">Tipo de Reporte:</label>
                <select class="form-select fw-medium" name="tipo_reporte" id="report_type">
                    <option value="preview" selected>🖥️ Vista Previa (En Pantalla)</option>
                    <option value="pdf">📄 Reporte Operativo (PDF)</option>
                    <option value="csv">📊 Reporte Operativo (CSV)</option>
                    <option value="xlsx">📈 Acumulado Mensual (Excel XLSX)</option>
                </select>
            </div>

            <!-- 2. PERÍODO / RANGO DE FECHAS -->
            <div id="section_date_presets" class="mb-3">
                <label class="form-label fw-semibold small text-muted">Período:</label>
                <select class="form-select" id="date_range_preset">
                    <option value="today" selected>Hoy</option>
                    <option value="yesterday">Ayer</option>
                    <option value="this_week">Esta Semana</option>
                    <option value="this_month">Este Mes</option>
                    <option value="custom">Rango Personalizado...</option>
                </select>
            </div>

            <!-- Campos ocultos reales para enviar 'desde' y 'hasta' al backend en buscar y descargas de PDF/CSV -->
            <input type="hidden" name="desde" id="filtro_desde" value="<?php echo date('Y-m-d'); ?>">
            <input type="hidden" name="hasta" id="filtro_hasta" value="<?php echo date('Y-m-d'); ?>">

            <!-- Inputs de Fecha Personalizados (se muestran solo si se elige 'custom') -->
            <div id="section_custom_dates" class="mb-3 d-none border rounded p-2 bg-light">
                <div class="mb-2">
                    <label class="form-label fw-medium small">Desde:</label>
                    <input type="date" class="form-control form-control-sm" id="input_custom_desde" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div>
                    <label class="form-label fw-medium small">Hasta:</label>
                    <input type="date" class="form-control form-control-sm" id="input_custom_hasta" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- Selector de Mes y Año para el Acumulado (se muestra solo si se elige 'xlsx') -->
            <div id="section_month_select" class="mb-3 d-none border rounded p-2 bg-light">
                <div class="row g-2">
                    <div class="col-7">
                        <label class="form-label fw-medium small">Mes:</label>
                        <select class="form-select form-select-sm" id="select_acumulado_mes">
                            <?php
                            $meses = [
                                '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
                                '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
                                '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                            ];
                            $mesActual = date('m');
                            foreach ($meses as $num => $nombre):
                                $selected = ($num === $mesActual) ? 'selected' : '';
                                echo "<option value=\"{$num}\" {$selected}>{$nombre}</option>";
                            endforeach;
                            ?>
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="form-label fw-medium small">Año:</label>
                        <select class="form-select form-select-sm" id="select_acumulado_anio">
                            <?php
                            $anioActual = (int)date('Y');
                            for ($a = $anioActual - 2; $a <= $anioActual; $a++):
                                $selected = ($a === $anioActual) ? 'selected' : '';
                                echo "<option value=\"{$a}\" {$selected}>{$a}</option>";
                            endfor;
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 3. FILTROS AVANZADOS / OPERATIVOS (Colapsables para limpiar visualmente la interfaz) -->
            <div id="section_advanced_filters">
                <div class="accordion border-0" id="accordionFiltros">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingAdvanced">
                            <button class="accordion-button collapsed px-0 py-2 bg-transparent fw-bold text-secondary small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvanced" aria-expanded="false" aria-controls="collapseAdvanced">
                                <i class="bi bi-sliders me-1 text-success"></i> Filtros Opcionales
                            </button>
                        </h2>
                        <div id="collapseAdvanced" class="accordion-collapse collapse" aria-labelledby="headingAdvanced" data-bs-parent="#accordionFiltros">
                            <div class="accordion-body px-0 py-2">
                                
                                <!-- Municipio -->
                                <div class="mb-3">
                                    <label class="form-label fw-medium small text-muted">Municipio:</label>
                                    <select class="form-select select2" name="municipio_id" id="filtro_municipio">
                                        <option value="">Todos los municipios</option>
                                        <?php foreach ($datos['municipios'] as $m): ?>
                                            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre_municipio']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Tipo de Emergencia -->
                                <div class="mb-3">
                                    <label class="form-label fw-medium small text-muted">Tipo de Emergencia:</label>
                                    <select class="form-select select2" name="tipo_emergencia_id" id="filtro_emergencia">
                                        <option value="">Todas las emergencias</option>
                                        <?php foreach ($datos['tipos_emergencia'] as $e): ?>
                                            <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Tipo de Caso -->
                                <div class="mb-3">
                                    <label class="form-label fw-medium small text-muted">Tipo de Caso:</label>
                                    <select class="form-select select2" name="caso_id" id="filtro_caso">
                                        <option value="">Todos los casos</option>
                                        <?php foreach ($datos['casos'] as $c): ?>
                                            <option value="<?php echo $c['id']; ?>" data-tipo="<?php echo $c['tipo_emergencia_id']; ?>">
                                                <?php echo htmlspecialchars($c['nombre_caso']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Operador -->
                                <div class="mb-3">
                                    <label class="form-label fw-medium small text-muted">Operador:</label>
                                    <select class="form-select select2" name="usuario_id" id="filtro_operador">
                                        <option value="">Todos los operadores</option>
                                        <?php foreach ($datos['operadores'] as $o): ?>
                                            <option value="<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['nombre_completo']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Estado -->
                                <div class="mb-3">
                                    <label class="form-label fw-medium small text-muted">Estado de Ficha:</label>
                                    <select class="form-select" name="estado" id="filtro_estado">
                                        <option value="">Todos los estados</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="En Proceso">En Proceso</option>
                                        <option value="Atendido">Atendido</option>
                                        <option value="Cancelada">Cancelada</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción unificados -->
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-success" id="btnFiltrar">
                    <i class="bi bi-search me-1"></i> Generar Búsqueda
                </button>
                <button type="button" class="btn btn-light" id="btnLimpiarFiltros">
                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar Filtros
                </button>
            </div>
        </form>
    </div>
</div>
