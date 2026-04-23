<?php
/**
 * head.php - Metadatos y Recursos CSS Globales
 * 
 * Gestiona de forma centralizada los estilos, meta-tags de accesibilidad
 * y librerías externas para todos los módulos del sistema.
 */

// 1. DETERMINACIÓN DEL CONTEXTO DE PÁGINA
$pageName = $pageName ?? 'home';
?>

<!-- 2. METADATOS DE RENDERIZADO Y ACCESIBILIDAD -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />

<!-- 3. LIBRERÍAS DE ICONOGRAFÍA Y UI (Offline) -->
<link rel="stylesheet" href="public/libs/bootstrap-icons/bootstrap-icons.min.css" />
<link rel="stylesheet" href="public/libs/sweetalert2/sweetalert2.min.css" />

<!-- 4. CARGA CONDICIONAL SEGÚN MÓDULO (Regla 7) -->
<?php if (in_array($pageName, ['login', 'setup'])): ?>
    <!-- Recursos Exclusivos para Autenticación -->
    <link rel="stylesheet" href="public/libs/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="public/libs/inter/index.css">
    <link rel="stylesheet" href="public/css/login.css">
    
    <?php if ($pageName === 'setup'): ?>
        <link rel="stylesheet" href="public/css/setup.css">
    <?php endif; ?>

<?php else: ?>
    <!-- Recursos Exclusivos para el Dashboard Institucional -->
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#16a34a" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#064e3b" media="(prefers-color-scheme: dark)" />
    <meta name="supported-color-schemes" content="light dark" />

    <link rel="stylesheet" href="public/libs/source-sans-3/index.css" />
    <link rel="stylesheet" href="public/libs/overlayscrollbars/overlayscrollbars.min.css" />
    
    <!-- Componentes de Búsqueda (Select2) -->
    <link rel="stylesheet" href="public/libs/select2/select2.min.css" />
    <link rel="stylesheet" href="public/libs/select2/select2-bootstrap-5-theme.min.css" />

    <!-- Estilos Nucleares (AdminLTE + Personalización Ven911) -->
    <link rel="preload" href="public/css/adminlte.css" as="style" />
    <link rel="stylesheet" href="public/css/adminlte.css" />
    <link rel="stylesheet" href="public/css/home.css" />
<?php endif; ?>

