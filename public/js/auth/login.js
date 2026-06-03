/**
 * login.js - Gestión de Autenticación (Ven911)
 * 
 * Controla la interacción del formulario de acceso, validaciones en cliente,
 * visibilidad de credenciales y comunicación asíncrona con el servidor.
 */

document.addEventListener("DOMContentLoaded", () => {
    
    // 1. REFERENCIAS AL DOM Y VARIABLES NUCLEARES
    const loginForm      = document.getElementById("loginForm");
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput  = document.getElementById("password");

    // 2. INTERACCIÓN DE UI: VISIBILIDAD DE CONTRASEÑA
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener("click", function () {
            // Alternar el atributo type del input
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);

            // Alternar el estado visual del icono (ojo abierto/cerrado)
            this.classList.toggle("bi-eye");
            this.classList.toggle("bi-eye-slash");
        });
    }

    // 3. GESTIÓN DE AUTENTICACIÓN (LOGIN ASÍNCRONO)
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const usuario  = document.getElementById("usuario").value;
        const password = document.getElementById("password").value;

        // 3.1 Validaciones tácticas en el cliente (Inercia Cero)
        if (usuario.trim() === '' || password.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Campos Requeridos',
                text: 'Por favor, ingrese usuario y contraseña.',
                buttonsStyling: false,
                customClass: { confirmButton: 'btn btn-login' }
            });
            return;
        }

        // 3.2 Feedback visual: Pantalla de carga (Wait UI)
        Swal.fire({
            title: 'Autenticando',
            html: 'Iniciando conexión segura...',
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            // 3.3 Petición HTTP al backend (Controlador de Autenticación)
            const response = await fetch('index.php?url=auth/authenticate', {
                method: 'POST',
                body: new FormData(loginForm)
            });

            const data = await response.json();

            // 3.4 Evaluación de respuesta exitosa
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Acceso Autorizado',
                    text: data.message || 'Bienvenido al sistema VEN 911.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Redirección al Dashboard Principal tras sesión válida
                    window.location.href = 'index.php?url=home';
                });
            } else {
                // Falla lógica (credenciales incorrectas)
                throw new Error(data.message || 'Credenciales inválidas. Intente nuevamente.');
            }

        } catch (error) {
            // 3.5 Gestión de excepciones y errores de red
            Swal.fire({
                icon: 'error',
                title: 'Acceso Denegado',
                text: error.message || 'Ocurrió un error en la conexión.',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-login'
                }
            });
        }
    });

    // 4. ASISTENTE DE RECUPERACIÓN DE CONTRASEÑA (SOLO ADMINISTRADOR)
    const btnRecuperar = document.getElementById("btnRecuperarPassword");
    if (btnRecuperar) {
        btnRecuperar.addEventListener("click", async (e) => {
            e.preventDefault();

            // Paso 1: Pedir el nombre de usuario (Cédula) del Administrador
            const { value: usuario } = await Swal.fire({
                title: 'Recuperar Contraseña',
                text: 'Ingrese su usuario de Administrador para validar su identidad:',
                input: 'text',
                inputPlaceholder: 'Ej. V12345678',
                showCancelButton: true,
                confirmButtonText: 'Siguiente <i class="bi bi-arrow-right-short"></i>',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-success me-2 px-4',
                    cancelButton: 'btn btn-ven-cancel px-4'
                },
                inputValidator: (value) => {
                    if (!value || value.trim() === '') {
                        return 'Debe ingresar su nombre de usuario.';
                    }
                }
            });

            if (!usuario) return; // Cancelado

            // Enviar Paso 1 al Servidor
            Swal.showLoading();
            try {
                const formData1 = new FormData();
                formData1.append('usuario', usuario.trim());

                const res1 = await fetch('index.php?url=auth/recuperarPaso1', {
                    method: 'POST',
                    body: formData1
                });
                const data1 = await res1.json();

                if (!data1.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso Denegado',
                        text: data1.message,
                        buttonsStyling: false,
                        customClass: { confirmButton: 'btn btn-success px-4' }
                    });
                    return;
                }

                const userId = data1.user_id;
                const preguntas = data1.preguntas;

                const escapeHTML = (str) => {
                    if (str === null || str === undefined) return '';
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                };

                // Función reutilizable para el Paso 3 (cambio de contraseña)
                const ejecutarPaso3 = async (targetUserId) => {
                    const { value: passwords } = await Swal.fire({
                        title: 'Restablecer Contraseña',
                        html: `
                            <p class="text-muted small mb-3">Ingrese una nueva contraseña segura para su usuario.</p>
                            <div class="text-start mb-3">
                                <label class="form-label fw-bold text-dark mb-1">Nueva Contraseña</label>
                                <div class="input-group-custom">
                                    <input type="password" id="swal-pass" class="form-control" placeholder="Mínimo 8 caracteres, 1 mayúscula y 1 número" style="padding-right: 3rem;">
                                    <i class="bi bi-eye-slash toggle-password" id="swal-toggle-pass1"></i>
                                </div>
                            </div>
                            <div class="text-start mb-3">
                                <label class="form-label fw-bold text-dark mb-1">Confirmar Contraseña</label>
                                <div class="input-group-custom">
                                    <input type="password" id="swal-pass-confirm" class="form-control" placeholder="Repita la contraseña" style="padding-right: 3rem;">
                                    <i class="bi bi-eye-slash toggle-password" id="swal-toggle-pass2"></i>
                                </div>
                            </div>
                        `,
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Restablecer <i class="bi bi-check-circle"></i>',
                        cancelButtonText: 'Cancelar',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-success me-2 px-4',
                            cancelButton: 'btn btn-ven-cancel px-4'
                        },
                        didOpen: () => {
                            const btn1 = document.getElementById('swal-toggle-pass1');
                            const btn2 = document.getElementById('swal-toggle-pass2');

                            if (btn1) {
                                btn1.addEventListener('click', function() {
                                    const passInput = document.getElementById('swal-pass');
                                    if (passInput) {
                                        const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
                                        passInput.setAttribute('type', type);
                                        this.classList.toggle('bi-eye');
                                        this.classList.toggle('bi-eye-slash');
                                    }
                                });
                            }

                            if (btn2) {
                                btn2.addEventListener('click', function() {
                                    const passInputConfirm = document.getElementById('swal-pass-confirm');
                                    if (passInputConfirm) {
                                        const type = passInputConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
                                        passInputConfirm.setAttribute('type', type);
                                        this.classList.toggle('bi-eye');
                                        this.classList.toggle('bi-eye-slash');
                                    }
                                });
                            }
                        },
                        preConfirm: () => {
                            const p = document.getElementById('swal-pass').value;
                            const pc = document.getElementById('swal-pass-confirm').value;
                            if (!p || !pc) {
                                Swal.showValidationMessage('Todos los campos son obligatorios.');
                                return false;
                            }
                            if (p !== pc) {
                                Swal.showValidationMessage('Las contraseñas no coinciden.');
                                return false;
                            }
                            if (p.length < 8) {
                                Swal.showValidationMessage('La contraseña debe tener al menos 8 caracteres.');
                                return false;
                            }
                            return { p, pc };
                        }
                    });

                    if (!passwords) return; // Cancelado

                    Swal.showLoading();
                    const formData3 = new FormData();
                    formData3.append('user_id', targetUserId);
                    formData3.append('nueva_password', passwords.p);
                    formData3.append('confirmar_password', passwords.pc);

                    const res3 = await fetch('index.php?url=auth/recuperarPaso3', {
                        method: 'POST',
                        body: formData3
                    });
                    const data3 = await res3.json();

                    if (data3.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Proceso Exitoso',
                            text: data3.message,
                            buttonsStyling: false,
                            customClass: { confirmButton: 'btn btn-success px-4' }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Servidor',
                            text: data3.message,
                            buttonsStyling: false,
                            customClass: { confirmButton: 'btn btn-success px-4' }
                        });
                    }
                };

                // Asistente de Bypass (Restablecimiento con Código de Activación)
                const launchBypassWizard = async (targetUserId) => {
                    const { value: factoryCode } = await Swal.fire({
                        title: 'Código de Activación',
                        text: 'Ingrese la Clave de Activación del Sistema para restablecer sus preguntas:',
                        input: 'password',
                        inputPlaceholder: 'Ingrese el Código de Fábrica...',
                        showCancelButton: true,
                        confirmButtonText: 'Validar Clave <i class="bi bi-arrow-right-short"></i>',
                        cancelButtonText: 'Cancelar',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-success me-2 px-4',
                            cancelButton: 'btn btn-ven-cancel px-4'
                        },
                        inputValidator: (value) => {
                            if (!value || value.trim() === '') {
                                return 'Debe ingresar el código de activación.';
                            }
                        }
                    });

                    if (!factoryCode) return; // Cancelado

                    Swal.showLoading();
                    try {
                        const formDataPreguntas = new FormData();
                        formDataPreguntas.append('user_id', targetUserId);

                        const resPreguntas = await fetch('index.php?url=auth/obtenerTodasPreguntas', {
                            method: 'POST',
                            body: formDataPreguntas
                        });
                        const dataPreguntas = await resPreguntas.json();

                        if (!dataPreguntas.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de Servidor',
                                text: dataPreguntas.message || 'No se pudieron cargar las preguntas.',
                                buttonsStyling: false,
                                customClass: { confirmButton: 'btn btn-success px-4' }
                            });
                            return;
                        }

                        const listaPreguntas = dataPreguntas.preguntas;

                        let opcionesHtml = `<option value="" disabled selected>Seleccione una pregunta...</option>`;
                        listaPreguntas.forEach(p => {
                            opcionesHtml += `<option value="${p.id}">${escapeHTML(p.pregunta)}</option>`;
                        });

                        const { value: nuevosDatosSeguridad } = await Swal.fire({
                            title: 'Configurar Nueva Seguridad',
                            html: `
                                <p class="text-muted small mb-3">Establezca sus nuevas preguntas y respuestas secretas de recuperación.</p>
                                <div class="text-start mb-3">
                                    <label class="form-label fw-bold text-dark mb-1">Nueva Pregunta 1</label>
                                    <select id="swal-new-p1" class="form-select mb-2">${opcionesHtml}</select>
                                    <input type="text" id="swal-new-r1" class="form-control" placeholder="Respuesta 1...">
                                </div>
                                <div class="text-start mb-3">
                                    <label class="form-label fw-bold text-dark mb-1">Nueva Pregunta 2</label>
                                    <select id="swal-new-p2" class="form-select mb-2">${opcionesHtml}</select>
                                    <input type="text" id="swal-new-r2" class="form-control" placeholder="Respuesta 2...">
                                </div>
                            `,
                            focusConfirm: false,
                            showCancelButton: true,
                            confirmButtonText: 'Guardar y Continuar <i class="bi bi-shield-check"></i>',
                            cancelButtonText: 'Cancelar',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-success me-2 px-4',
                                cancelButton: 'btn btn-ven-cancel px-4'
                            },
                            preConfirm: () => {
                                const p1 = document.getElementById('swal-new-p1').value;
                                const r1 = document.getElementById('swal-new-r1').value;
                                const p2 = document.getElementById('swal-new-p2').value;
                                const r2 = document.getElementById('swal-new-r2').value;

                                if (!p1 || !r1.trim() || !p2 || !r2.trim()) {
                                    Swal.showValidationMessage('Todos los campos son obligatorios.');
                                    return false;
                                }
                                if (p1 === p2) {
                                    Swal.showValidationMessage('Debe seleccionar preguntas diferentes.');
                                    return false;
                                }
                                return { p1, r1: r1.trim(), p2, r2: r2.trim() };
                            }
                        });

                        if (!nuevosDatosSeguridad) return; // Cancelado

                        Swal.showLoading();
                        const formDataBypass = new FormData();
                        formDataBypass.append('user_id', targetUserId);
                        formDataBypass.append('factory_code', factoryCode.trim());
                        formDataBypass.append('pregunta_1', nuevosDatosSeguridad.p1);
                        formDataBypass.append('respuesta_1', nuevosDatosSeguridad.r1);
                        formDataBypass.append('pregunta_2', nuevosDatosSeguridad.p2);
                        formDataBypass.append('respuesta_2', nuevosDatosSeguridad.r2);

                        const resBypass = await fetch('index.php?url=auth/restablecerPreguntasConLlave', {
                            method: 'POST',
                            body: formDataBypass
                        });
                        const dataBypass = await resBypass.json();

                        if (!dataBypass.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Bypass Fallido',
                                text: dataBypass.message,
                                buttonsStyling: false,
                                customClass: { confirmButton: 'btn btn-success px-4' }
                            });
                            return;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Seguridad Restablecida',
                            text: dataBypass.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            ejecutarPaso3(targetUserId);
                        });

                    } catch (err) {
                        console.error(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Comunicación',
                            text: 'Ocurrió un error al intentar procesar el bypass.',
                            buttonsStyling: false,
                            customClass: { confirmButton: 'btn btn-success px-4' }
                        });
                    }
                };

                // Paso 2: Mostrar las preguntas secretas y pedir respuestas
                const { value: respuestas } = await Swal.fire({
                    title: 'Preguntas de Seguridad',
                    html: `
                        <p class="text-muted small mb-3">Responda correctamente sus preguntas de seguridad de recuperación.</p>
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold text-dark mb-1">1. ${escapeHTML(preguntas.p1_texto)}</label>
                            <input type="text" id="swal-ans1" class="form-control" placeholder="Escriba su respuesta...">
                        </div>
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold text-dark mb-1">2. ${escapeHTML(preguntas.p2_texto)}</label>
                            <input type="text" id="swal-ans2" class="form-control" placeholder="Escriba su respuesta...">
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" id="lnk-resetear-preguntas" class="text-success text-decoration-none fw-bold small">
                                <i class="bi bi-key-fill"></i> ¿No recuerda las respuestas? Restablecer preguntas con Código de Activación
                            </a>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Validar <i class="bi bi-shield-check"></i>',
                    cancelButtonText: 'Cancelar',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success me-2 px-4',
                        cancelButton: 'btn btn-ven-cancel px-4'
                    },
                    didOpen: () => {
                        const lnkReset = document.getElementById('lnk-resetear-preguntas');
                        if (lnkReset) {
                            lnkReset.addEventListener('click', (ev) => {
                                ev.preventDefault();
                                Swal.close();
                                launchBypassWizard(userId);
                            });
                        }
                    },
                    preConfirm: () => {
                        const r1 = document.getElementById('swal-ans1').value;
                        const r2 = document.getElementById('swal-ans2').value;
                        if (!r1.trim() || !r2.trim()) {
                            Swal.showValidationMessage('Debe contestar ambas preguntas.');
                        }
                        return { r1: r1.trim(), r2: r2.trim() };
                    }
                });

                if (!respuestas) return; // Cancelado

                // Enviar Paso 2 al Servidor
                Swal.showLoading();
                const formData2 = new FormData();
                formData2.append('user_id', userId);
                formData2.append('respuesta_1', respuestas.r1);
                formData2.append('respuesta_2', respuestas.r2);

                const res2 = await fetch('index.php?url=auth/recuperarPaso2', {
                    method: 'POST',
                    body: formData2
                });
                const data2 = await res2.json();

                if (!data2.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validación Fallida',
                        text: data2.message,
                        buttonsStyling: false,
                        customClass: { confirmButton: 'btn btn-success px-4' }
                    });
                    return;
                }

                // Si fue exitoso, ejecutar el Paso 3 directamente
                ejecutarPaso3(userId);

            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Comunicación',
                    text: 'Ocurrió un error inesperado al procesar la petición.',
                    buttonsStyling: false,
                    customClass: { confirmButton: 'btn btn-success px-4' }
                });
            }
        });
    }
});
