<?php
/**
 * index.php - Manual de Usuario Integral, Detallado y Dinámico (RBAC)
 * 
 * Muestra el flujo completo cronológico del sistema y guías paso a paso minuciosas
 * para cada acción, ordenadas de forma secuencial. Filtra automáticamente las
 * secciones visibles basándose en el rol y los permisos del usuario activo.
 */

$pageName = 'ayuda';
?>
<!doctype html>
<html lang="es">

<head>
    <title>Ven911 | Manual de Usuario Completo</title>
    
    <!-- CABECERA GLOBALES Y ESTILOS -->
    <?php require __DIR__ . '/../partials/head.php'; ?>
    <link rel="stylesheet" href="public/css/ayuda.css" />
    <style>
        .manual-step { margin-bottom: 2rem; padding-left: 1.5rem; border-left: 4px solid #16a34a; position: relative; }
        .manual-step-number { font-weight: 800; color: #16a34a; font-size: 1.15rem; margin-bottom: 0.5rem; display: flex; align-items: center; }
        .manual-step-number .badge { font-size: 0.8rem; padding: 0.35em 0.65em; margin-right: 0.75rem; }
        .manual-badge-rol { font-size: 0.75rem; vertical-align: middle; margin-left: 0.5rem; }
        .flow-diagram-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem; }
        .flow-step-arrow { font-size: 1.5rem; color: #64748b; margin: 0 0.5rem; }
        .workflow-indicator { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    
    <div class="app-wrapper">

        <!-- COMPONENTES DE INTERFAZ GLOBAL -->
        <?php require __DIR__ . '/../partials/navbar.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="app-main">
            
            <!-- Encabezado de la página -->
            <div class="app-content-header border-0 pb-0">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="home-hero-header mt-2 d-flex align-items-center">
                                <div class="bg-success text-white rounded p-3 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                                    <i class="bi bi-book-half" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h1 class="home-hero-title h2 mb-1">Manual de Operaciones y Usuario</h1>
                                    <p class="home-hero-subtitle fw-medium mb-0" style="color: #475569;">Protocolo paso a paso y flujos de secuencia del Sistema VEN 911 Carabobo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección del Manual -->
            <div class="app-content mt-4 mb-5">
                <div class="container-fluid">
                    <div class="row">
                        
                        <!-- Columna Izquierda: Menú de Navegación por Categorías (RBAC) -->
                        <div class="col-lg-3 mb-4">
                            <div class="card shadow-sm border-0 faq-nav-card">
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush rounded" id="list-tab" role="tablist">
                                        <!-- Mapa General de Flujo (Siempre visible) -->
                                        <a class="list-group-item list-group-item-action active fw-bold py-3" id="list-workflow-list" data-bs-toggle="list" href="#list-workflow" role="tab">
                                            <i class="bi bi-diagram-3 me-2 text-success"></i> Mapa General de Flujo
                                        </a>

                                        <!-- Acceso y Perfil (Siempre visible) -->
                                        <a class="list-group-item list-group-item-action fw-bold py-3" id="list-general-list" data-bs-toggle="list" href="#list-general" role="tab">
                                            <i class="bi bi-box-arrow-in-right me-2 text-success"></i> Acceso y Seguridad
                                        </a>
                                        
                                        <!-- Creación de Fichas (Filtro por Permiso) -->
                                        <?php if (tienePerm('fichas', 'ver')): ?>
                                        <a class="list-group-item list-group-item-action fw-bold py-3" id="list-fichas-list" data-bs-toggle="list" href="#list-fichas" role="tab">
                                            <i class="bi bi-file-earmark-medical me-2 text-success"></i> Creación de Fichas
                                        </a>
                                        <?php endif; ?>
                                        
                                        <!-- Centro de Despacho (Filtro por Permiso) -->
                                        <?php if (tienePerm('despachos', 'ver')): ?>
                                        <a class="list-group-item list-group-item-action fw-bold py-3" id="list-despacho-list" data-bs-toggle="list" href="#list-despacho" role="tab">
                                            <i class="bi bi-broadcast me-2 text-success"></i> Centro de Despacho
                                        </a>
                                        <?php endif; ?>
                                        
                                        <!-- Gestión de Usuarios (Filtro por Permiso) -->
                                        <?php if (tienePerm('usuarios', 'ver')): ?>
                                        <a class="list-group-item list-group-item-action fw-bold py-3" id="list-usuarios-list" data-bs-toggle="list" href="#list-usuarios" role="tab">
                                            <i class="bi bi-shield-lock me-2 text-success"></i> Gestión de Usuarios
                                        </a>
                                        <?php endif; ?>

                                        <!-- Reportes (Filtro por Permiso) -->
                                        <?php if (tienePerm('reportes', 'ver')): ?>
                                        <a class="list-group-item list-group-item-action fw-bold py-3" id="list-reportes-list" data-bs-toggle="list" href="#list-reportes" role="tab">
                                            <i class="bi bi-file-earmark-bar-graph me-2 text-success"></i> Reportes y Exportación
                                        </a>
                                        <?php endif; ?>

                                        <!-- Auditoría (Filtro por Permiso) -->
                                        <?php if (tienePerm('historial', 'ver')): ?>
                                        <a class="list-group-item list-group-item-action fw-bold py-3" id="list-auditoria-list" data-bs-toggle="list" href="#list-auditoria" role="tab">
                                            <i class="bi bi-activity me-2 text-success"></i> Auditoría Integral
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Contenido Detallado de Guías -->
                        <div class="col-lg-9">
                            <div class="tab-content" id="nav-tabContent">
                                
                                <!-- 0. CATEGORÍA: MAPA GENERAL DE FLUJO -->
                                <div class="tab-pane fade show active" id="list-workflow" role="tabpanel" aria-labelledby="list-workflow-list">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body p-4">
                                            <h4 class="mb-4 text-success fw-bold border-bottom pb-3">
                                                <i class="bi bi-diagram-3 me-2"></i>Flujo Cronológico y Ciclo del Sistema VEN 911
                                            </h4>
                                            <p class="text-secondary mb-4">
                                                El sistema VEN 911 opera como un flujo secuencial unificado de respuesta a incidentes. A continuación se ilustra el orden cronológico estricto de las operaciones y qué rol interviene en cada etapa del proceso:
                                            </p>

                                            <!-- Diagrama Visual de Flujo -->
                                            <div class="flow-diagram-box shadow-sm">
                                                <div class="row align-items-center text-center">
                                                    <div class="col-md-3 mb-3 mb-md-0">
                                                        <div class="p-2 border rounded bg-white shadow-sm">
                                                            <span class="workflow-indicator d-block mb-1">Fase 1: Llamada</span>
                                                            <span class="badge bg-danger mb-2">OPERADOR</span>
                                                            <p class="small text-secondary mb-0">Recepción telefónica y creación de la Ficha.</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1 mb-3 mb-md-0">
                                                        <i class="bi bi-arrow-right-circle-fill flow-step-arrow"></i>
                                                    </div>
                                                    <div class="col-md-3 mb-3 mb-md-0">
                                                        <div class="p-2 border rounded bg-white shadow-sm">
                                                            <span class="workflow-indicator d-block mb-1">Fase 2: Despacho</span>
                                                            <span class="badge bg-primary mb-2">DESPACHADOR</span>
                                                            <p class="small text-secondary mb-0">Toma el caso, asigna organismos y unidades radiales.</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1 mb-3 mb-md-0">
                                                        <i class="bi bi-arrow-right-circle-fill flow-step-arrow"></i>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="p-2 border rounded bg-white shadow-sm">
                                                            <span class="workflow-indicator d-block mb-1">Fase 3: Cierre y Reporte</span>
                                                            <span class="badge bg-success mb-1">DESPACHADOR</span>
                                                            <span class="badge bg-dark mb-1">ADMIN</span>
                                                            <span class="badge bg-secondary mb-1">JEFATURA</span>
                                                            <p class="small text-secondary mt-1 mb-0">Resolución radial del caso, almacenamiento en auditoría y exportación estadística.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <h5 class="fw-bold mb-3"><i class="bi bi-check2-circle text-success me-2"></i>Secuencia Cronológica de Eventos en Sala</h5>
                                            <ol class="text-secondary lh-lg mb-4">
                                                <li><strong>Ingreso del Caso:</strong> El ciudadano se comunica vía telefónica al 911. El <strong>Operador</strong> atiende la llamada y abre el panel de registro.</li>
                                                <li><strong>Registro en Caliente:</strong> El Operador valida la cédula, geolocaliza el incidente y tipifica el caso. Al guardar, el estatus se establece en <span class="badge bg-danger">Pendiente</span>.</li>
                                                <li><strong>Transmisión Inmediata (WebSockets):</strong> La base de datos registra la ficha y el servidor notifica por WebSockets a toda la sala de Despacho con alertas sonoras en tiempo real.</li>
                                                <li><strong>Asignación Táctica:</strong> El <strong>Despachador</strong> toma la ficha de la Cola General (cambiando su estado a <span class="badge bg-primary">En Proceso</span>) y realiza la asignación radial de unidades.</li>
                                                <li><strong>Monitoreo Radial en Vivo:</strong> El Despachador actualiza el estado de las unidades según sus reportes radiales en sitio. Al arribar la primera unidad, la ficha pasa a <span class="badge bg-info">Atendido</span>.</li>
                                                <li><strong>Cierre Operativo:</strong> Al liberarse las unidades, el Despachador finaliza el caso y la ficha pasa a <span class="badge bg-success">Atendido</span> (estatus terminal exitoso). Si el caso fue una falsa alarma o requiere ser descartado, se <span class="badge bg-danger">Cancela</span> obligatoriamente con un Motivo de Cancelación.</li>
                                                <li><strong>Registro Inalterable:</strong> El motor del sistema registra el log del suceso en la bitácora de auditoría, guardando los valores previos y nuevos para auditorías ministeriales.</li>
                                                <li><strong>Evaluación de Inteligencia:</strong> La <strong>Jefatura</strong> o el <strong>Administrador</strong> ingresa al módulo de Reportes para extraer métricas de rendimiento y mapas de calor territoriales.</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>

                                <!-- 1. CATEGORÍA: ACCESO Y SEGURIDAD -->
                                <div class="tab-pane fade" id="list-general" role="tabpanel" aria-labelledby="list-general-list">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body p-4">
                                            <h4 class="mb-4 text-success fw-bold border-bottom pb-3">
                                                <i class="bi bi-box-arrow-in-right me-2"></i>Protocolo de Acceso Seguro al Sistema
                                            </h4>

                                            <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-success me-2"></i>Acción 1: Inicio de Sesión Ordinario</h5>
                                            <p class="text-secondary mb-3">Siga esta secuencia estricta para acceder a su consola operativa:</p>
                                            
                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">1</span> Introducir Credenciales</div>
                                                <p class="text-secondary mb-0">Escriba su Cédula de Identidad en el campo "Usuario" con formato de letra mayúscula inicial (ej: <code>V12345678</code>). Escriba su contraseña en el campo respectivo.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">2</span> Verificar Contraseña Escrita</div>
                                                <p class="text-secondary mb-0">Si desea validar que no ha cometido errores ortográficos, haga clic en el ícono del <strong>ojo</strong> ubicado en el extremo derecho del input para revelar los caracteres en texto plano.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">3</span> Enviar y Acceder</div>
                                                <p class="text-secondary mb-0">Haga clic en <strong>"Ingresar al Sistema"</strong>. El botón se inhabilitará y mostrará una animación de carga para evitar doble envío. Al ser aprobado por el servidor, se cargará su perfil y permisos de inmediato.</p>
                                            </div>

                                            <?php if ((int)$_SESSION['user_rol_id'] === 1): ?>
                                                <h5 class="fw-bold mb-3 mt-5"><i class="bi bi-stars text-success me-2"></i>Acción 2: Primer Inicio de Sesión (Inicialización de Seguridad)</h5>
                                                <p class="text-secondary mb-3">Este procedimiento se ejecuta de forma obligatoria la primera vez que inicia sesión con una cuenta recién creada:</p>

                                                <div class="manual-step">
                                                    <div class="manual-step-number"><span class="badge bg-success">1</span> Selección de Preguntas</div>
                                                    <p class="text-secondary mb-0">El sistema desplegará dos menús de selección. Seleccione dos preguntas de seguridad diferentes del listado del catálogo (ej: <i>¿Nombre de su primera mascota?</i> y <i>¿Ciudad de nacimiento de su madre?</i>).</p>
                                                </div>

                                                <div class="manual-step">
                                                    <div class="manual-step-number"><span class="badge bg-success">2</span> Registro de Respuestas</div>
                                                    <p class="text-secondary mb-0">Escriba sus respuestas secretas. El sistema las procesará automáticamente en minúsculas y las hasheará con algoritmos criptográficos robustos. Guarde los datos para activar su cuenta definitiva.</p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ((int)$_SESSION['user_rol_id'] === 1): ?>
                                                <h5 class="fw-bold mb-3 mt-5"><i class="bi bi-shield-lock-fill text-success me-2"></i>Acción 3: Asistente de Recuperación (Bypass con Clave de Activación)</h5>
                                                <p class="text-secondary mb-3">Procedimiento reservado en exclusiva para el Administrador del Sistema en caso de olvido de credenciales:</p>
                                                
                                                <div class="manual-step">
                                                    <div class="manual-step-number"><span class="badge bg-success">1</span> Iniciar el Asistente de Recuperación</div>
                                                    <p class="text-secondary mb-0">Haga clic en <strong>"¿Olvidó su contraseña? (Solo Administrador)"</strong> en la parte inferior del login. Introduzca su nombre de usuario en la primera pantalla del modal SweetAlert2.</p>
                                                </div>

                                                <div class="manual-step">
                                                    <div class="manual-step-number"><span class="badge bg-success">2</span> Activar el Bypass de Contingencia</div>
                                                    <p class="text-secondary mb-0">Si no recuerda las respuestas a las preguntas secretas mostradas en el Paso 2, haga clic en el enlace verde inferior: <strong>"¿No recuerda las respuestas? Restablecer preguntas con Código de Activación"</strong>.</p>
                                                </div>

                                                <div class="manual-step">
                                                    <div class="manual-step-number"><span class="badge bg-success">3</span> Validar el Código de Activación (Código de Fábrica)</div>
                                                    <p class="text-secondary mb-0">Ingrese la Clave de Activación del Sistema del servidor. Si es correcta, el sistema limpiará su configuración anterior y le permitirá elegir dos preguntas nuevas de forma inmediata.</p>
                                                </div>

                                                <div class="manual-step">
                                                    <div class="manual-step-number"><span class="badge bg-success">4</span> Guardar Nuevos Parámetros y Cambiar Contraseña</div>
                                                    <p class="text-secondary mb-0">Establezca las respuestas. El sistema le redirigirá directamente al formulario de contraseña. Ingrese su nueva clave segura. Puede hacer clic en el ícono del **ojo** para confirmar los caracteres ingresados.</p>
                                                </div>
                                            <?php else: ?>
                                                <h5 class="fw-bold mb-3 mt-5"><i class="bi bi-shield-exclamation text-success me-2"></i>Acción 3: Solicitud de Restablecimiento de Credenciales</h5>
                                                <p class="text-secondary mb-3">Procedimiento para el personal de sala (Operadores, Despachadores, Jefatura) en caso de extravío de contraseña o bloqueo de cuenta:</p>
                                                
                                                <div class="manual-step" style="border-left-color: #ca8a04;">
                                                    <div class="manual-step-number" style="color: #ca8a04;"><span class="badge bg-warning text-dark">1</span> Contactar al Administrador de Sistema</div>
                                                    <p class="text-secondary mb-0">Notifique de inmediato al Administrador de guardia en la sala técnica o de TI sobre el bloqueo de sus credenciales.</p>
                                                </div>

                                                <div class="manual-step" style="border-left-color: #ca8a04;">
                                                    <div class="manual-step-number" style="color: #ca8a04;"><span class="badge bg-warning text-dark">2</span> Restablecimiento Técnico (Blanqueo)</div>
                                                    <p class="text-secondary mb-0">El Administrador ubicará su usuario en el panel de control y realizará un <strong>"Blanqueo Técnico de Clave"</strong>, asignándole una contraseña temporal de inicio rápido.</p>
                                                </div>

                                                <div class="manual-step" style="border-left-color: #ca8a04;">
                                                    <div class="manual-step-number" style="color: #ca8a04;"><span class="badge bg-warning text-dark">3</span> Primer Ingreso y Configuración Obligatoria</div>
                                                    <p class="text-secondary mb-0">Inicie sesión con la clave temporal. El sistema le solicitará automáticamente registrar dos preguntas y respuestas de seguridad de su exclusiva autoría.</p>
                                                </div>

                                                <div class="manual-step" style="border-left-color: #ca8a04;">
                                                    <div class="manual-step-number" style="color: #ca8a04;"><span class="badge bg-warning text-dark">4</span> Personalización de Contraseña</div>
                                                    <p class="text-secondary mb-0">Finalmente, el asistente le guiará para cambiar la clave temporal por una contraseña definitiva y segura. Recuerde usar el ícono del <strong>ojo</strong> para confirmar los caracteres ingresados.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 2. CATEGORÍA: CREACIÓN DE FICHAS -->
                                <?php if (tienePerm('fichas', 'ver')): ?>
                                <div class="tab-pane fade" id="list-fichas" role="tabpanel" aria-labelledby="list-fichas-list">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body p-4">
                                            <h4 class="mb-4 text-success fw-bold border-bottom pb-3">
                                                <i class="bi bi-file-earmark-medical me-2"></i>Creación Sistemática de Fichas de Emergencia
                                            </h4>

                                            <h5 class="fw-bold mb-3"><i class="bi bi-telephone-inbound text-success me-2"></i>Flujo Secuencial Obligatorio para Registrar Incidentes</h5>
                                            <p class="text-secondary mb-3">Siga este orden riguroso durante la llamada ciudadana para garantizar la veracidad de la información:</p>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">1</span> Abrir Formulario y Validar Identidad del Solicitante</div>
                                                <p class="text-secondary mb-1">Navegue al menú lateral "Fichas" y presione el botón verde <strong>"Nueva Ficha"</strong>.</p>
                                                <p class="text-secondary mb-0">Escriba la cédula de la persona que reporta y haga clic fuera del campo. El sistema consultará el padrón del servidor para cargar automáticamente el nombre y evitar duplicidades.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">2</span> Registrar Números de Contacto</div>
                                                <p class="text-secondary mb-0">Ingrese el teléfono celular principal. Si la persona llama de un número alternativo, regístrelo en el campo "Teléfono Alternativo". **Este paso es obligatorio** para la trazabilidad y llamadas de verificación en sitio.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">3</span> Localizar el Incidente (Georreferencia Jerárquica)</div>
                                                <p class="text-secondary mb-1">Seleccione obligatoriamente en este orden:</p>
                                                <p class="text-secondary mb-1"><code>Municipio → Parroquia → Comuna → Sector</code></p>
                                                <p class="text-secondary mb-0">Escriba un punto de referencia claro y visible (ej. <i>"Frente a la panadería Carabobo, casa de fachada azul con portón blanco"</i>). Si dispone de coordenadas UTM, regístrelas en las observaciones.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">4</span> Tipificar la Emergencia</div>
                                                <p class="text-secondary mb-0">Seleccione el Tipo de Emergencia de la lista principal (ej: <i>Rescate</i>) y el Caso correspondiente (ej: <i>Accidente de Tránsito con lesionados</i>). Esto priorizará visualmente la ficha ante el despachador.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">5</span> Redactar la Descripción Operativa y Guardar</div>
                                                <p class="text-secondary mb-1">Escriba una síntesis técnica y precisa. Responda brevemente a: ¿Qué ocurre?, ¿Quiénes están afectados?, ¿Hay heridos o riesgos activos?.</p>
                                                <p class="text-secondary mb-0">Presione <strong>"Guardar Ficha"</strong>. El botón se inhabilitará temporalmente para proteger el servidor de peticiones dobles. La ficha se registrará como <span class="badge bg-danger">Pendiente</span> en la Cola General de Despacho de forma instantánea.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- 3. CATEGORÍA: CENTRO DE DESPACHO -->
                                <?php if (tienePerm('despachos', 'ver')): ?>
                                <div class="tab-pane fade" id="list-despacho" role="tabpanel" aria-labelledby="list-despacho-list">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body p-4">
                                            <h4 class="mb-4 text-success fw-bold border-bottom pb-3">
                                                <i class="bi bi-broadcast me-2"></i>Operaciones de Despacho, Asignación y Cierre de Casos
                                            </h4>

                                            <h5 class="fw-bold mb-3"><i class="bi bi-activity text-success me-2"></i>Secuencia de Acción Táctica Radial</h5>
                                            <p class="text-secondary mb-3">El Despachador en sala debe guiar su labor operativa bajo el siguiente orden cronológico estricto:</p>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">1</span> Tomar Caso de la Cola General</div>
                                                <p class="text-secondary mb-1">Acceda al módulo "Despachos" y revise la pestaña "Cola General".</p>
                                                <p class="text-secondary mb-0">Haga clic en el botón azul (icono de antena) <strong>"Gestionar Despacho"</strong> de la incidencia de mayor gravedad. Esto cambia el estatus de la ficha a "En Proceso" y la vincula de forma exclusiva a su usuario para evitar asignaciones duplicadas de otros despachadores.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">2</span> Realizar Asignación Radial de Organismos</div>
                                                <p class="text-secondary mb-1">1. Navegue a la pestaña <strong>"Mis Fichas"</strong> y haga clic en "Ver Detalles / Asignar".</p>
                                                <p class="text-secondary mb-1">2. En el panel lateral de gestión de despachos, seleccione el Organismo a despachar (ej. <i>Policía del Estado, Protección Civil, PNB, Bomberos</i>).</p>
                                                <p class="text-secondary mb-1">3. Escriba la identificación exacta de la unidad enviada (ej: <i>Ambulancia-410</i>) y el operador de radio receptor.</p>
                                                <p class="text-secondary mb-0">4. Haga clic en <strong>"Asignar Organismo"</strong>. Se creará la línea operativa en estado "Asignado".</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">3</span> Actualizar Estatus Operativo (Rastreo en Sitio)</div>
                                                <p class="text-secondary mb-1">A medida que las unidades reportan avances por radio, haga clic en los botones de estatus en la tabla de despachos de la ficha en este orden secuencial:</p>
                                                <p class="text-secondary mb-1"><code>Asignado (Despacho inicial) → En Camino (Unidad en ruta) → En Sitio (Unidad en el lugar de los hechos)</code></p>
                                                <p class="text-secondary mb-0"><strong>Nota de Automatización:</strong> Cuando marque la primera unidad como <strong>"En Sitio"</strong>, el estatus global de la Ficha cambiará automáticamente a <span class="badge bg-info">Atendido</span> en todas las consolas del sistema.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">4</span> Liberar Unidades e Iniciar el Protocolo de Cierre</div>
                                                <p class="text-secondary mb-1">Una vez controlada la situación, actualice las unidades despachadas a estatus <strong>"Liberado"</strong> (si resolvieron el caso con éxito) o <strong>"Cancelado"</strong> (si fue falsa alarma, requiriendo justificar la cancelación de forma obligatoria).</p>
                                                <p class="text-secondary mb-0">Haga clic en el botón superior <strong>"Finalizar Caso"</strong>. El sistema abrirá un modal de texto.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">5</span> Redactar Informe Final de Cierre (Cierre Definitivo)</div>
                                                <p class="text-secondary mb-1">Escriba detalladamente la justificación final y el informe técnico del cierre (ej: <i>"Se constató colisión simple, trasladando a 2 heridos leves al Hospital Central mediante ambulancia PC-02. Unidades liberadas sin novedad."</i>).</p>
                                                <p class="text-secondary mb-0">Guarde los datos. El caso pasará a estatus <span class="badge bg-success">Atendido</span> (finalizado exitosamente) o <span class="badge bg-danger">Cancelada</span> (descartado con motivo), archivándose históricamente y bloqueándose para cualquier modificación posterior por razones legales.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- 4. CATEGORÍA: GESTIÓN DE USUARIOS -->
                                <?php if (tienePerm('usuarios', 'ver')): ?>
                                <div class="tab-pane fade" id="list-usuarios" role="tabpanel" aria-labelledby="list-usuarios-list">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body p-4">
                                            <h4 class="mb-4 text-success fw-bold border-bottom pb-3">
                                                <i class="bi bi-shield-lock me-2"></i>Gestión de Personal, Seguridad y Permisos
                                            </h4>

                                            <h5 class="fw-bold mb-3"><i class="bi bi-person-plus-fill text-success me-2"></i>Acción 1: Registro de un Nuevo Usuario (Flujo de Alta)</h5>
                                            <p class="text-secondary mb-3">Siga este orden para dar de alta a un funcionario en el sistema:</p>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">1</span> Llenado de Datos Personales</div>
                                                <p class="text-secondary mb-0">Navegue al módulo "Usuarios" en el sidebar y presione el botón <strong>"Nuevo Usuario"</strong>. Complete la Cédula de Identidad, Nombres y Apellidos del funcionario.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">2</span> Definir Datos de Acceso</div>
                                                <p class="text-secondary mb-0">Escriba el correo institucional y teléfono de contacto. Defina el Nombre de Usuario único y una contraseña inicial segura (mínimo 8 caracteres, 1 mayúscula y 1 número).</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">3</span> Asignación del Rol de Sala</div>
                                                <p class="text-secondary mb-1">Seleccione el rol de la lista desplegable basándose estrictamente en su asignación física en sala:</p>
                                                <ul class="text-secondary mb-0">
                                                    <li><strong>Operador:</strong> Recepción telefónica rápida de reportes y creación inicial de fichas.</li>
                                                    <li><strong>Despachador:</strong> Sala de despacho y transmisiones radiales. Gestión de unidades móviles y cierre de casos.</li>
                                                    <li><strong>Jefatura:</strong> Visualización estática (Dashboard, Fichas, Mapas) y generación de reportes generales.</li>
                                                    <li><strong>Administrador:</strong> Control técnico completo (mantenimiento de usuarios, blanqueos, auditoría).</li>
                                                </ul>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">4</span> Guardar e Inicializar Cuenta</div>
                                                <p class="text-secondary mb-0">Presione Guardar. El sistema creará el registro y habilitará la cuenta en estado activo. El nuevo usuario deberá configurar sus preguntas de seguridad en su primer inicio de sesión.</p>
                                            </div>

                                            <h5 class="fw-bold mb-3 mt-5"><i class="bi bi-shield-exclamation text-success me-2"></i>Acción 2: Controles Técnicos Directos</h5>
                                            <p class="text-secondary mb-3">Acciones de mantenimiento de cuentas ejecutadas desde la tabla principal de usuarios:</p>

                                            <div class="manual-step">
                                                <div class="manual-step-number">Cambio de Contraseña (Blanqueo Técnico)</div>
                                                <p class="text-secondary mb-0">Si un operador olvida su clave y bloquea sus preguntas secretas, ubique su registro en la tabla de usuarios, haga clic en el ícono de la <strong>llave</strong>, defina una nueva contraseña temporal y entréguesela al funcionario para que pueda iniciar sesión y actualizarla.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number">Suspensión de Cuentas (Activación/Inactivación)</div>
                                                <p class="text-secondary mb-0">Si un funcionario se retira de la institución, haga clic en el botón de <strong>encendido/apagado</strong> al final de su fila. El estatus cambiará a "Inactivo" de forma inmediata, denegándole el acceso al sistema pero **resguardando históricamente** todas las fichas y despachos que registró bajo su firma para fines legales.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- 5. CATEGORÍA: REPORTES Y ESTADÍSTICAS -->
                                <?php if (tienePerm('reportes', 'ver')): ?>
                                <div class="tab-pane fade" id="list-reportes" role="tabpanel" aria-labelledby="list-reportes-list">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body p-4">
                                            <h4 class="mb-4 text-success fw-bold border-bottom pb-3">
                                                <i class="bi bi-file-earmark-bar-graph me-2"></i>Módulo de Inteligencia y Reportes Estadísticos
                                            </h4>

                                            <h5 class="fw-bold mb-3"><i class="bi bi-filter-square text-success me-2"></i>Flujo Secuencial para Extraer Reportes Legales</h5>
                                            <p class="text-secondary mb-3">Siga este procedimiento para filtrar, visualizar y exportar la data acumulada:</p>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">1</span> Ingresar Parámetros Temporales</div>
                                                <p class="text-secondary mb-0">Acceda a "Reportes" en el sidebar. Seleccione obligatoriamente la **Fecha de Inicio** y la **Fecha de Fin** en el panel de control de búsqueda para delimitar el universo de datos.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">2</span> Aplicar Filtros Territoriales u Operativos (Opcional)</div>
                                                <p class="text-secondary mb-0">Si requiere delimitar el análisis, seleccione un municipio o parroquia específicos de Carabobo, o elija un tipo de emergencia (ej: <i>Salud</i>) o un caso concreto.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">3</span> Generar Telemetría y Gráficos</div>
                                                <p class="text-secondary mb-0">Haga clic en <strong>"Generar Gráficos"</strong>. El servidor procesará la base de datos y renderizará gráficos de torta y barras mostrando densidad de casos, organismos más asignados y promedios de respuesta.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">4</span> Descargar Documentos Sellados</div>
                                                <p class="text-secondary mb-0">Presione <strong>"Descargar PDF"</strong> para obtener el informe formal e imprimible del sistema (incluye firma digital, estadísticas consolidadas y logotipos ministeriales). Presione <strong>"Exportar a Excel"</strong> para descargar la base de datos completa de incidencias y filtrados para procesamiento estadístico externo.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- 6. CATEGORÍA: AUDITORÍA INTEGRAL -->
                                <?php if (tienePerm('historial', 'ver')): ?>
                                <div class="tab-pane fade" id="list-auditoria" role="tabpanel" aria-labelledby="list-auditoria-list">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body p-4">
                                            <h4 class="mb-4 text-success fw-bold border-bottom pb-3">
                                                <i class="bi bi-activity me-2"></i>Auditoría Integral y Traceabilidad Forense
                                            </h4>

                                            <h5 class="fw-bold mb-3"><i class="bi bi-hdd-network text-success me-2"></i>Flujo de Inspección y Análisis de Logs</h5>
                                            <p class="text-secondary mb-3">Protocolo para investigar modificaciones de datos y auditorías de seguridad:</p>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">1</span> Acceder a la Tabla de Trazabilidad</div>
                                                <p class="text-secondary mb-0">Navegue a "Auditoría Integral" y seleccione la pestaña <strong>"Trazabilidad de Fichas"</strong> o <strong>"Logs del Sistema"</strong> según el tipo de investigación técnica que requiera ejecutar.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">2</span> Rastrear Ciclo de Ficha por Código</div>
                                                <p class="text-secondary mb-0">Escriba el número de la ficha en el buscador de la tabla de Trazabilidad. Podrá ver de forma ordenada y cronológica quién atendió la llamada, a qué hora se asignó en despacho, qué unidades intervinieron y quién cerró el caso final.</p>
                                            </div>

                                            <div class="manual-step">
                                                <div class="manual-step-number"><span class="badge bg-success">3</span> Examinar Cambios Internos (JSON Comparativo)</div>
                                                <p class="text-secondary mb-0">En la pestaña de Logs, haga clic en el botón de detalles de cualquier fila de modificación (<code>UPDATE</code>). El sistema desplegará un modal detallando en formato JSON estructurado el **Valor Viejo** y el **Valor Nuevo** de la fila en la base de datos, identificando cambios de estatus o manipulación no autorizada de campos.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Pie de página Institucional -->
        <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>

    <!-- CARGA DE ASSETS JAVASCRIPT -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
