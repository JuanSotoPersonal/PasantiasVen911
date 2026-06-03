<?php
/**
 * footer.php - Pie de Página Institucional
 * 
 * Contiene el copyright, créditos del sistema y el botón flotante de asistencia técnica.
 */
?>

<!-- 1. BLOQUE DE DERECHOS DE AUTOR -->
<footer class="app-footer">
    <strong>
        Copyright &copy; 2026&nbsp;
        <a href="#" class="text-decoration-none text-success">Ven911</a>
    </strong>
    Todos los derechos reservados.
</footer>

<!-- 2. ACCESOS RÁPIDOS Y ASISTENCIA -->
<a href="#offcanvasAyuda" data-bs-toggle="offcanvas" class="btn-floating-help" title="Ayuda Rápida">
    <i class="bi bi-question-lg"></i>
</a>

<!-- 3. PANEL LATERAL DE AYUDA (OFFCANVAS) -->
<?php require __DIR__ . '/offcanvas_ayuda.php'; ?>
