<?php
    //--------------------------------------------------------------------
    // Partial: Head y Librerías CSS globales
    // Utiliza la variable $pageName para cargar de forma condicional estilos específicos
    //--------------------------------------------------------------------
    $pageName = $pageName ?? 'home';
?>
<!--inicio::Encabezado global-->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />

<!-- Bootstrap Icons (Común a todas las vistas) -->
<link rel="stylesheet" href="public/libs/bootstrap-icons/bootstrap-icons.min.css" />
<!-- SweetAlert2 (Para notificaciones comunes) -->
<link rel="stylesheet" href="public/libs/sweetalert2/sweetalert2.min.css" />

<?php if ($pageName === 'login'): ?>
  <!-- Estilos Exclusivos para Login -->
  <link href="public/libs/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="public/libs/inter/index.css" rel="stylesheet">
  <link rel="stylesheet" href="public/css/login.css">
<?php else: ?>
  <!-- Estilos Exclusivos para el Dashboard / Sistema -->
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#16a34a" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#064e3b" media="(prefers-color-scheme: dark)" />
  <meta name="supported-color-schemes" content="light dark" />

  <link rel="stylesheet" href="public/libs/source-sans-3/index.css" />
  <link rel="stylesheet" href="public/libs/overlayscrollbars/overlayscrollbars.min.css" />
  
  <link rel="preload" href="public/css/adminlte.css" as="style" />
  <link rel="stylesheet" href="public/css/adminlte.css" />
  <link rel="stylesheet" href="public/css/home.css" />
<?php endif; ?>
<!--fin::Encabezado global-->
