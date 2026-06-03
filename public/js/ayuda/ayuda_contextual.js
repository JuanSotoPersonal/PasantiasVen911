/**
 * ayuda_contextual.js
 * 
 * Gestiona el contenido del Manual Rápido (Offcanvas)
 * mostrando tips dinámicos y específicos según el módulo, la pestaña activa 
 * y el rol/permisos del usuario logueado en el sistema VEN 911.
 */

document.addEventListener('DOMContentLoaded', function() {
    const offcanvasAyuda = document.getElementById('offcanvasAyuda');
    if (!offcanvasAyuda) return;

    // Diccionario de Roles del Sistema
    const ROLES_NOMBRES = {
        1: 'Administrador',
        2: 'Operador',
        3: 'Despachador',
        4: 'Jefatura'
    };

    // Estilos de color para la insignia del Rol
    const ROLES_CLASES = {
        1: 'badge bg-danger',
        2: 'badge bg-primary',
        3: 'badge bg-success',
        4: 'badge bg-warning text-dark'
    };

    // Estructura de Ayuda Dinámica por Módulo -> Pestaña/Subvista -> Rol
    const TEXTOS_AYUDA = {
        'home': {
            titulo: 'Inicio - Panel de Control',
            seccion: 'Inicio / Resumen',
            roles: {
                1: { // Administrador
                    cuerpo: `
                        <p class="mb-2">Como <strong>Administrador</strong>, supervisa el estado general del sistema y de la sala.</p>
                        <ul class="ps-3 mb-0">
                            <li class="mb-2"><strong>Métricas de Sala:</strong> Revise los KPIs generales de fichas creadas, en proceso y resueltas.</li>
                            <li class="mb-2"><strong>Conexión en Vivo:</strong> Compruebe el estado del canal en vivo (debe mostrar Conectado en verde).</li>
                            <li><strong>Acceso Rápido:</strong> Puede crear fichas de emergencia directamente en caso de contingencia.</li>
                        </ul>
                    `
                },
                2: { // Operador
                    cuerpo: `
                        <p class="mb-2">Como <strong>Operador</strong>, su prioridad en esta pantalla es iniciar el registro de llamadas.</p>
                        <ul class="ps-3 mb-0">
                            <li class="mb-2"><strong>Nueva Ficha:</strong> Use el botón verde superior para iniciar el formulario de registro de llamadas inmediatamente.</li>
                            <li><strong>Mi Rendimiento:</strong> Monitoree el contador de fichas registradas por usted en el turno actual.</li>
                        </ul>
                    `
                },
                3: { // Despachador
                    cuerpo: `
                        <p class="mb-2">Como <strong>Despachador</strong>, controle la cola y sus despachos activos.</p>
                        <ul class="ps-3 mb-0">
                            <li class="mb-2"><strong>Cola de Espera:</strong> Revise el contador de "Fichas Pendientes". Si hay incidentes en cola, diríjase al Centro de Despacho.</li>
                            <li><strong>Mis Fichas:</strong> El contador muestra cuántos casos tiene asignados y en atención radial en este momento.</li>
                        </ul>
                    `
                },
                4: { // Jefatura
                    cuerpo: `
                        <p class="mb-2">Como <strong>Jefatura</strong>, supervise el rendimiento global y la carga en sala.</p>
                        <ul class="ps-3 mb-0">
                            <li class="mb-2"><strong>Supervisión en Vivo:</strong> Observe la cantidad de casos pendientes y activos de forma consolidada.</li>
                            <li><strong>Toma de Decisiones:</strong> Evalúe los indicadores principales de la sala sin interferir en el registro o despacho en caliente.</li>
                        </ul>
                    `
                }
            }
        },
        'ficha': {
            titulo: 'Gestión de Fichas de Emergencia',
            seccion: 'Fichas / Registro',
            tabs: {
                'todas': {
                    nombre: 'Todas las Fichas',
                    roles: {
                        1: { cuerpo: '<p>Historial general de todas las fichas. Como administrador, puede auditar los registros y buscar incidencias mediante el filtro de texto de la tabla.</p>' },
                        2: { cuerpo: '<p>Listado de todas las fichas registradas. Puede verificar los datos de las fichas que ha creado y ver su estatus actual en tiempo real.</p>' },
                        3: { cuerpo: '<p>Historial general. Útil para consultar llamadas o incidentes previos de un solicitante para verificar antecedentes o duplicidades radiales.</p>' },
                        4: { cuerpo: '<p>Vista histórica de lectura. Permite buscar y analizar fichas de emergencias pasadas filtrando por texto u otros campos.</p>' }
                    }
                },
                'pendientes': {
                    nombre: 'Fichas Pendientes',
                    roles: {
                        1: { cuerpo: '<p>Fichas en espera de ser atendidas por un despachador. Representa la cola de entrada del sistema.</p>' },
                        2: { cuerpo: '<p>Casos que acaba de registrar. Permanece aquí hasta que un despachador lo tome y asigne unidades radiales.</p>' },
                        3: { cuerpo: '<p class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>¡Atención Despachador! Fichas en cola. Diríjase a <strong>Centro de Despacho > Cola General</strong> para tomar un caso y asignarle unidades.</p>' },
                        4: { cuerpo: '<p>Visualice la cola general de llamadas entrantes sin despachar y evalúe la fluidez del proceso.</p>' }
                    }
                },
                'en_proceso': {
                    nombre: 'Fichas en Proceso',
                    roles: {
                        1: { cuerpo: '<p>Casos activos en sala. Muestra las emergencias que los despachadores están coordinando con las unidades en la calle.</p>' },
                        2: { cuerpo: '<p>Fichas que ya han sido tomadas por un despachador y están recibiendo asignación radial.</p>' },
                        3: { cuerpo: '<p>Muestra los casos activos en atención. Si tiene casos asignados, diríjase a <strong>Centro de Despacho > Mis Fichas</strong> para actualizarlos.</p>' },
                        4: { cuerpo: '<p>Monitoree las emergencias activas en el estado y observe qué despachadores las están atendiendo.</p>' }
                    }
                },
                'atendidos': {
                    nombre: 'Fichas Atendidas',
                    roles: {
                        all: { cuerpo: '<p>Historial de fichas resueltas exitosamente (casos atendidos). Muestra el informe final cargado por el despachador de guardia al cerrar la incidencia de forma definitiva. Estos registros son inalterables por auditoría ministerial.</p>' }
                    }
                },
                'cerradas': {
                    nombre: 'Fichas Canceladas',
                    roles: {
                        all: { cuerpo: '<p>Historial de fichas canceladas/descartadas. Muestra el <strong>Motivo de Cancelación</strong> obligatorio ingresado por el despachador de guardia para justificar el descarte del incidente (ej. llamadas falsas o repetidas).</p>' }
                    }
                },
                'configuracion': {
                    nombre: 'Configuración del Sistema',
                    roles: {
                        1: {
                            cuerpo: `
                                <p class="mb-2"><strong>Mantenimiento del Sistema</strong></p>
                                <ul class="ps-3 mb-0">
                                    <li class="mb-2"><strong>Geografía:</strong> Gestione la estructura jerárquica de Municipios, Parroquias, Comunas y Sectores.</li>
                                    <li class="mb-2"><strong>Emergencias:</strong> Configure la tipificación de incidentes (Tipos y Casos).</li>
                                    <li><strong>Organismos:</strong> Administre los entes de respuesta radial del estado.</li>
                                </ul>
                            `
                        },
                        default: { cuerpo: '<p class="text-danger">Esta sección es de acceso restringido exclusivo para Administradores de TI.</p>' }
                    }
                }
            }
        },
        'despacho': {
            titulo: 'Centro de Despacho Radial',
            seccion: 'Centro de Despacho',
            tabs: {
                'general': {
                    nombre: 'Cola General',
                    roles: {
                        3: { cuerpo: '<p><strong>Cola General de Espera:</strong> Muestra las fichas en estado <span class="badge bg-danger">Pendiente</span>. Seleccione el caso de mayor gravedad y presione el botón azul de la antena <strong>"Gestionar Despacho"</strong> para asignárselo y comenzar la coordinación radial.</p>' },
                        default: { cuerpo: '<p>Visualice las llamadas entrantes que se encuentran en espera de ser tomadas por un despachador de radio.</p>' }
                    }
                },
                'propias': {
                    nombre: 'Mis Fichas',
                    roles: {
                        3: {
                            cuerpo: `
                                <p class="mb-2"><strong>Coordinación Radial Activa:</strong></p>
                                <ol class="ps-3 mb-2">
                                    <li class="mb-2">Presione <strong>"Ver Detalles / Asignar"</strong> en el caso que desea despachar.</li>
                                    <li class="mb-2">En el panel derecho, asigne el Organismo y escriba la Unidad de Radio correspondiente (ej. <em>PC-01</em>).</li>
                                    <li class="mb-2">Actualice el estatus de las unidades a medida que informen por radio: <em>Asignado -> En Camino -> En Sitio -> Liberado</em>.</li>
                                    <li class="mb-2">Al arribar la primera unidad en sitio, la ficha pasará automáticamente a estado <strong>Atendido</strong>.</li>
                                    <li>Para finalizar, haga clic en <strong>"Finalizar Caso"</strong> y redacte el informe final (Atendido) o motivo de cancelación (Cancelada).</li>
                                </ol>
                            `
                        },
                        default: { cuerpo: '<p class="text-danger">Usted no tiene un rol con permisos de despacho asignado. Esta pestaña muestra despachos del funcionario logueado.</p>' }
                    }
                }
            }
        },
        'usuario': {
            titulo: 'Gestión de Usuarios y Seguridad',
            seccion: 'Módulo de Personal',
            roles: {
                1: {
                    cuerpo: `
                        <p class="mb-2"><strong>Controles de Acceso y Personal de Sala:</strong></p>
                        <ul class="ps-3 mb-0">
                            <li class="mb-2"><strong>Creación de Cuenta:</strong> Use "Nuevo Usuario" para dar de alta. Defina contraseña y asigne rol operativo.</li>
                            <li class="mb-2"><strong>Blanqueo de Clave:</strong> Si un funcionario olvida su clave o bloquea sus preguntas, use la <strong>llave de seguridad</strong> para darle una clave temporal.</li>
                            <li><strong>Suspensión:</strong> Use el botón de encendido/apagado para suspender a un funcionario. Sus fichas históricas permanecerán intactas.</li>
                        </ul>
                    `
                },
                default: { cuerpo: '<p class="text-danger">Módulo restringido. Solo administradores pueden gestionar cuentas de usuario.</p>' }
            }
        },
        'evento': {
            titulo: 'Auditoría Integral',
            seccion: 'Auditoría / Trazabilidad',
            tabs: {
                'sistema': {
                    nombre: 'Logs del Sistema',
                    roles: {
                        1: { cuerpo: '<p><strong>Auditoría Forense de Logs:</strong> Muestra las acciones CRUD. El botón de detalles (+) revela un objeto JSON con los valores anteriores y los nuevos de la fila afectada en la base de datos para investigar manipulaciones.</p>' },
                        default: { cuerpo: '<p class="text-danger">El acceso a los logs detallados del sistema es de uso exclusivo para Administradores de Seguridad.</p>' }
                    }
                },
                'ficha': {
                    nombre: 'Trazabilidad de Fichas',
                    roles: {
                        all: { cuerpo: '<p><strong>Trazabilidad Cronológica:</strong> Introduzca el número de ficha en la tabla para seguir el rastro exacto: quién tomó la llamada inicial, a qué hora, qué despachador tomó el caso, qué unidades radiales se enviaron, sus horas de llegada, liberación y quién cerró la ficha final.</p>' }
                    }
                }
            }
        },
        'reporte': {
            titulo: 'Inteligencia y Estadísticas',
            seccion: 'Reportes Estadísticos',
            roles: {
                all: {
                    cuerpo: `
                        <p class="mb-2"><strong>Generación de Estadísticas de Gestión:</strong></p>
                        <ol class="ps-3 mb-0">
                            <li class="mb-2">Seleccione de forma obligatoria el rango de <strong>Fecha de Inicio</strong> y <strong>Fecha de Fin</strong>.</li>
                            <li class="mb-2">Filtre opcionalmente por Municipio, Parroquia, Tipo de Emergencia o Caso específico.</li>
                            <li class="mb-2">Haga clic en <strong>"Generar Gráficos"</strong> para visualizar los reportes dinámicos de torta y barras.</li>
                            <li>Use los botones superiores para descargar el <strong>PDF Oficial Sellado</strong> o exportar los datos a <strong>Excel</strong>.</li>
                        </ol>
                    `
                }
            }
        },
        'notificacion': {
            titulo: 'Buzón de Notificaciones',
            seccion: 'Alertas en Sala',
            roles: {
                all: { cuerpo: '<p>Consola central de notificaciones en tiempo real del sistema. Muestra avisos sobre nuevas fichas, cambios de estado radial y eventos críticos de seguridad.</p>' }
            }
        },
        'ayuda': {
            titulo: 'Manual de Usuario',
            seccion: 'Soporte / Guías',
            roles: {
                all: { cuerpo: '<p>Se encuentra en el Manual de Usuario completo. Use el menú lateral de categorías para ver el protocolo cronológico detallado paso a paso para cada uno de los módulos del sistema.</p>' }
            }
        },
        'default': {
            titulo: 'Navegación General',
            seccion: 'Manual Rápido',
            roles: {
                all: { cuerpo: '<p>Use el menú lateral para navegar por las secciones autorizadas para su rol. Si necesita ayuda de un módulo, abra este manual rápido desde la sección activa.</p>' }
            }
        }
    };

    // Función para obtener el módulo/sección actual basándose en la URL
    function obtenerModuloActual() {
        const params = new URLSearchParams(window.location.search);
        const urlParam = params.get('url');
        
        if (!urlParam) return 'home'; // Por defecto es home/inicio
        
        const rutas = urlParam.split('/');
        const moduloBase = rutas[0].toLowerCase();
        
        return TEXTOS_AYUDA[moduloBase] ? moduloBase : 'default';
    }

    // Función para obtener la pestaña/subvista actual basándose en la URL
    function obtenerTabActual(modulo) {
        const params = new URLSearchParams(window.location.search);
        const t = params.get('t');
        if (t) return t;

        // Tabs por defecto de cada módulo principal
        const tabsPorDefecto = {
            'ficha': 'todas',
            'despacho': 'general',
            'usuario': 'todos',
            'evento': 'sistema'
        };
        return tabsPorDefecto[modulo] || 'default';
    }

    // Evento al abrir el offcanvas de Ayuda
    offcanvasAyuda.addEventListener('show.bs.offcanvas', function () {
        const rolId = window.USUARIO_ROL_ID || 0;
        const rolNombre = ROLES_NOMBRES[rolId] || 'Invitado';
        const rolClase = ROLES_CLASES[rolId] || 'badge bg-secondary';

        const modulo = obtenerModuloActual();
        const tab = obtenerTabActual(modulo);
        const infoModulo = TEXTOS_AYUDA[modulo];

        let ayudaTitulo = infoModulo.titulo;
        let ayudaSeccion = infoModulo.seccion;
        let ayudaCuerpo = '';

        // Si el módulo contiene pestañas y la pestaña actual existe en la configuración
        if (infoModulo.tabs && infoModulo.tabs[tab]) {
            const infoTab = infoModulo.tabs[tab];
            ayudaSeccion += ` (${infoTab.nombre})`;
            
            // Buscar texto específico por rol, de lo contrario usar 'all', 'default' o fallback
            if (infoTab.roles && infoTab.roles[rolId]) {
                ayudaCuerpo = infoTab.roles[rolId].cuerpo;
            } else if (infoTab.roles && infoTab.roles['all']) {
                ayudaCuerpo = infoTab.roles['all'].cuerpo;
            } else if (infoTab.roles && infoTab.roles['default']) {
                ayudaCuerpo = infoTab.roles['default'].cuerpo;
            } else {
                ayudaCuerpo = '<p>No hay ayuda específica disponible para esta combinación de pestaña y rol.</p>';
            }
        } else {
            // No tiene pestañas o es la vista por defecto
            if (infoModulo.roles && infoModulo.roles[rolId]) {
                ayudaCuerpo = infoModulo.roles[rolId].cuerpo;
            } else if (infoModulo.roles && infoModulo.roles['all']) {
                ayudaCuerpo = infoModulo.roles['all'].cuerpo;
            } else if (infoModulo.roles && infoModulo.roles['default']) {
                ayudaCuerpo = infoModulo.roles['default'].cuerpo;
            } else {
                ayudaCuerpo = '<p>No hay ayuda específica disponible para este módulo.</p>';
            }
        }

        // Actualizar los elementos en el offcanvas
        document.getElementById('ayuda-active-module').textContent = `Sección: ${ayudaSeccion}`;
        
        const badgeRol = document.getElementById('ayuda-rol-badge');
        badgeRol.className = rolClase;
        badgeRol.textContent = rolNombre;

        document.getElementById('ayuda-contexto-titulo').innerHTML = `<i class="bi bi-info-circle-fill me-2"></i>${ayudaTitulo}`;
        document.getElementById('ayuda-contexto-cuerpo').innerHTML = ayudaCuerpo;
    });
});
