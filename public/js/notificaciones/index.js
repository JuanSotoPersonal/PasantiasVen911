/**
 * public/js/notificaciones/index.js
 * Script para gestionar el Buzón de Notificaciones (DataTables y Acciones)
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Configuración de Iconos y Colores según el Tipo
    const tipoConfig = {
        'info': { icon: 'bi-info-circle', color: 'text-primary', bg: 'bg-primary' },
        'alerta': { icon: 'bi-exclamation-triangle', color: 'text-warning', bg: 'bg-warning' },
        'critico': { icon: 'bi-x-octagon', color: 'text-danger', bg: 'bg-danger' },
        'cambio_estado': { icon: 'bi-arrow-left-right', color: 'text-success', bg: 'bg-success' },
        'default': { icon: 'bi-bell', color: 'text-secondary', bg: 'bg-secondary' }
    };

    // 2. Inicialización de DataTable (Server-Side)
    const tablaNotificaciones = $('#tablaNotificaciones').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: 'index.php?url=notificacion/obtenerPaginado',
            type: 'POST',
            error: function (xhr, error, thrown) {
                console.error("[DataTables] Error en AJAX Notificaciones:", error);
            }
        },
        language: window.Ven911DataTablesLang, // Idioma estandarizado
        order: [[5, 'desc']], // Ordenar por fecha_creacion descendente por defecto
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50],
        columns: [
            // [0] Estado (Leído/No Leído Icono)
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
            // [1] Tipo (Categoría)
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
                            <span class="fw-semibold">${window.escapeHTML(etiqueta)}</span>
                        </div>
                    `;
                }
            },
            // [2] Título
            {
                data: 'titulo',
                className: 'align-middle fw-bold',
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
                        return `<button class="btn btn-sm btn-outline-success btn-ir-ficha" data-ficha-id="${data}" data-notif-id="${row.id}">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>#${data}
                                </button>`;
                    }
                    return '<span class="text-muted small">N/A</span>';
                }
            },
            // [5] Fecha
            {
                data: 'fecha_creacion',
                className: 'text-end align-middle text-nowrap',
                orderable: true,
                render: function (data) {
                    return data;
                }
            }
        ],
        createdRow: function (row, data, dataIndex) {
            // Estilizar filas no leídas para destacarlas visualmente
            if (parseInt(data.leido) === 0) {
                $(row).addClass('notif-row-unread');
            } else {
                $(row).addClass('notif-row-read');
            }
            
            // Permitir que toda la fila sea clicable para marcar como leída si no lo está
            $(row).css('cursor', 'pointer');
            $(row).on('click', function(e) {
                // Evitar doble evento si se hace clic en el botón de la ficha
                if ($(e.target).closest('.btn-ir-ficha').length > 0) return;
                
                if (parseInt(data.leido) === 0) {
                    marcarNotificacionLeida(data.id, $(row));
                }
            });
        }
    });

    // 3. Manejo de clic en botón "Ir a Ficha"
    $('#tablaNotificaciones').on('click', '.btn-ir-ficha', function(e) {
        e.stopPropagation();
        const fichaId = $(this).data('ficha-id');
        const notifId = $(this).data('notif-id');
        
        // Marcar leída en segundo plano
        $.post('index.php?url=notificacion/marcarLeida', { id: notifId });
        
        // Redirigir a la ficha
        window.location.href = `index.php?url=ficha&accion=ver&id=${fichaId}`;
    });

    // 4. Marcar Notificación Individual como Leída
    function marcarNotificacionLeida(id, rowElement) {
        $.ajax({
            url: 'index.php?url=notificacion/marcarLeida',
            type: 'POST',
            data: { id: id },
            success: function (res) {
                if (res.success) {
                    // Actualizar UI localmente sin recargar tabla
                    rowElement.removeClass('notif-row-unread').addClass('notif-row-read');
                    rowElement.find('.bi-envelope-fill').removeClass('bi-envelope-fill text-success').addClass('bi-envelope-open text-muted').attr('title', 'Leída');
                    
                    // Actualizar el contador global de notificaciones si existe la función global
                    if (typeof window.cargarNotificacionesPendientes === 'function') {
                        window.cargarNotificacionesPendientes();
                    }
                }
            }
        });
    }

    // 5. Marcar Todas como Leídas
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
                    tablaNotificaciones.ajax.reload(null, false); // Recargar tabla manteniendo página actual
                    
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
});
