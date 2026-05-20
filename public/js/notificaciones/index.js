/**
 * public/js/notificaciones/index.js
 * Script para gestionar el Buzón de Notificaciones (DataTables y Acciones)
 * Rediseño Split-Pane con filtros interactivos reactivos.
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Estado de Filtrado
    let currentFilterType = 'estado'; // 'estado' o 'tipo'
    let currentFilterVal = 'todos';   // valor correspondiente

    // Configuración de Iconos y Colores por tipo de categoría
    const tipoConfig = {
        'info': { icon: 'bi-info-circle-fill', color: 'text-primary', bg: 'bg-primary' },
        'alerta': { icon: 'bi-exclamation-triangle-fill', color: 'text-warning', bg: 'bg-warning' },
        'critico': { icon: 'bi-x-octagon-fill', color: 'text-danger', bg: 'bg-danger' },
        'cambio_estado': { icon: 'bi-arrow-repeat', color: 'text-success', bg: 'bg-success' },
        'default': { icon: 'bi-bell-fill', color: 'text-secondary', bg: 'bg-secondary' }
    };

    // 2. Inicialización de DataTable (Server-Side)
    const tablaNotificaciones = $('#tablaNotificaciones').DataTable({
        serverSide: true,
        processing: true,
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'>>t<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        ajax: {
            url: 'index.php?url=notificacion/obtenerPaginado',
            type: 'POST',
            data: function (d) {
                // Inyectar parámetros de filtrado adicionales en la petición POST
                if (currentFilterType === 'estado') {
                    d.estado_filtro = currentFilterVal;
                } else if (currentFilterType === 'tipo') {
                    d.tipo_filtro = currentFilterVal;
                }
            },
            error: function (xhr, error, thrown) {
                console.error("[DataTables] Error en AJAX Notificaciones:", error);
            }
        },
        language: window.Ven911DataTablesLang, // Configuración de idioma centralizada
        order: [[5, 'desc']], // Ordenar por fecha de creación desc
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50],
        columns: [
            // [0] Estado de Lectura
            {
                data: 'leido',
                className: 'text-center align-middle',
                orderable: true,
                render: function (data) {
                    const isRead = parseInt(data) === 1;
                    if (isRead) {
                        return '<i class="bi bi-envelope-open text-muted fs-5" title="Leída"></i>';
                    } else {
                        return '<i class="bi bi-envelope-fill text-success fs-5" title="No Leída"></i>';
                    }
                }
            },
            // [1] Categoría (Tipo)
            {
                data: 'tipo',
                className: 'align-middle',
                orderable: true,
                render: function (data) {
                    const tipoStr = data ? data.toLowerCase() : 'default';
                    const conf = tipoConfig[tipoStr] || tipoConfig['default'];
                    const etiqueta = data.charAt(0).toUpperCase() + data.slice(1).replace('_', ' ');
                    return `
                        <div class="d-flex align-items-center">
                            <span class="notif-icon-circle ${conf.bg} bg-opacity-10 ${conf.color} me-2">
                                <i class="bi ${conf.icon}"></i>
                            </span>
                            <span class="fw-semibold text-dark">${window.escapeHTML(etiqueta)}</span>
                        </div>
                    `;
                }
            },
            // [2] Título
            {
                data: 'titulo',
                className: 'align-middle fw-bold text-dark',
                orderable: true,
                render: function (data) {
                    return window.escapeHTML(data);
                }
            },
            // [3] Mensaje
            {
                data: 'mensaje',
                className: 'align-middle text-muted',
                orderable: true,
                render: function (data) {
                    return window.escapeHTML(data);
                }
            },
            // [4] Ficha Vinculada
            {
                data: 'ficha_id',
                className: 'text-center align-middle',
                orderable: true,
                render: function (data, type, row) {
                    if (data) {
                        return `<button class="btn btn-sm btn-ven-cancel btn-ir-ficha shadow-sm" data-ficha-id="${data}" data-notif-id="${row.id}">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>#${data}
                                </button>`;
                    }
                    return '<span class="text-muted small">N/A</span>';
                }
            },
            // [5] Fecha
            {
                data: 'fecha_creacion',
                className: 'text-end align-middle text-nowrap text-muted small',
                orderable: true,
                render: function (data) {
                    return data;
                }
            }
        ],
        createdRow: function (row, data, dataIndex) {
            // Asignar clase de no leído
            if (parseInt(data.leido) === 0) {
                $(row).addClass('notif-row-unread');
            } else {
                $(row).addClass('notif-row-read');
            }

            $(row).css('cursor', 'pointer');
            
            // Evento para marcar leído al hacer clic sobre la fila
            $(row).on('click', function(e) {
                if ($(e.target).closest('.btn-ir-ficha').length > 0) return;
                
                if (parseInt(data.leido) === 0) {
                    marcarNotificacionLeida(data.id, $(row));
                }
            });
        },
        drawCallback: function (settings) {
            const api = this.api();
            const json = api.ajax.json();
            if (json) {
                // Actualizar contador de "Todas" en el sidebar izquierdo
                $('#cnt-todas').text(json.recordsTotal);
            }
        }
    });

    // 3. Conexión de Buscador Reactivo Personalizado
    $('#buscador-notif').on('keyup', function () {
        tablaNotificaciones.search(this.value).draw();
    });

    // 4. Gestión de Clic en Enlaces de Filtro del Panel Izquierdo
    $('.notif-filter-item').on('click', function (e) {
        e.preventDefault();
        $('.notif-filter-item').removeClass('active');
        $(this).addClass('active');

        currentFilterType = $(this).data('filter');
        currentFilterVal = $(this).data('val');

        // Efecto de carga suave (opacity transition)
        $('.card-notif-list').css('opacity', 0.5);
        tablaNotificaciones.ajax.reload(function() {
            $('.card-notif-list').css('opacity', 1);
        }, true);
    });

    // 5. Clic en "Ir a Ficha"
    $('#tablaNotificaciones').on('click', '.btn-ir-ficha', function(e) {
        e.stopPropagation();
        const fichaId = $(this).data('ficha-id');
        const notifId = $(this).data('notif-id');
        
        // Marcar leída en segundo plano
        $.post('index.php?url=notificacion/marcarLeida', { id: notifId });
        
        // Redirigir a la ficha
        window.location.href = `index.php?url=ficha&accion=ver&id=${fichaId}`;
    });

    // 6. Marcar Notificación Individual como Leída
    function marcarNotificacionLeida(id, rowElement) {
        $.ajax({
            url: 'index.php?url=notificacion/marcarLeida',
            type: 'POST',
            data: { id: id },
            success: function (res) {
                if (res.success) {
                    rowElement.removeClass('notif-row-unread').addClass('notif-row-read');
                    rowElement.find('.bi-envelope-fill').removeClass('bi-envelope-fill text-success').addClass('bi-envelope-open text-muted').attr('title', 'Leída');
                    
                    // Actualizar contadores
                    actualizarConteosFiltros();
                    
                    if (typeof window.cargarNotificacionesPendientes === 'function') {
                        window.cargarNotificacionesPendientes();
                    }
                }
            }
        });
    }

    // 7. Marcar Todas como Leídas
    $('#btn-marcar-todas-buzon').on('click', function () {
        if (!confirm('¿Seguro que deseas marcar todas las notificaciones como leídas?')) return;

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');

        $.ajax({
            url: 'index.php?url=notificacion/marcarTodas',
            type: 'POST',
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Historial marcado como leído',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    tablaNotificaciones.ajax.reload(null, false);
                    actualizarConteosFiltros();
                    
                    if (typeof window.cargarNotificacionesPendientes === 'function') {
                        window.cargarNotificacionesPendientes();
                    }
                } else {
                    Swal.fire('Error', 'No se pudieron actualizar las notificaciones.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Fallo de comunicación con el servidor.', 'error');
            },
            complete: function() {
                $('#btn-marcar-todas-buzon').prop('disabled', false).html('<i class="bi bi-check2-all me-1"></i>Marcar todas como leídas');
            }
        });
    });

    // 8. Actualización Reactiva de Contadores
    function actualizarConteosFiltros() {
        fetch('index.php?url=notificacion/obtenerPendientes')
            .then(res => res.json())
            .then(res => {
                if (res.success && Array.isArray(res.data)) {
                    $('#cnt-no-leidas').text(res.data.length);
                }
            })
            .catch(() => {});
    }

    // Cargar conteos iniciales
    actualizarConteosFiltros();
});
