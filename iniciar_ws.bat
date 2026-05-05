@echo off
:: ============================================================
:: iniciar_ws.bat - Autoarranque del Demonio WebSocket VEN 911
:: ============================================================
:: Inicia el servidor de notificaciones en tiempo real.
:: Verificar si ya esta en ejecucion antes de lanzar una nueva instancia.

setlocal

set PHP_EXE=C:\xampp\php\php.exe
set SCRIPT=C:\xampp\htdocs\ProyectoFicha\app\bin\servidor_ws.php
set LOG=C:\xampp\htdocs\ProyectoFicha\app\bin\servidor_ws.log

:: Verificar si el proceso ya esta corriendo (busca php.exe con el script)
tasklist /fi "imagename eq php.exe" /fo csv 2>nul | find /i "php.exe" >nul
if errorlevel 1 goto INICIAR

:: Si PHP corre, verificar si es nuestro script especifico
wmic process where "name='php.exe'" get CommandLine 2>nul | find /i "servidor_ws.php" >nul
if not errorlevel 1 (
    echo [VEN 911] El servidor WebSocket ya esta en ejecucion. No se inicia una segunda instancia.
    exit /b 0
)

:INICIAR
echo [VEN 911] Iniciando Servidor WebSocket de Notificaciones...
echo Fecha de inicio: %date% %time%
echo Puerto WS  : 8080 (WebSockets para navegadores^)
echo Puerto HTTP: 8081 (Receptor interno de palipitos^)
echo.

:: Iniciar en segundo plano con ventana minimizada, redirigiendo salida al log
start "VEN 911 - Servidor WS" /min "%PHP_EXE%" "%SCRIPT%" >> "%LOG%" 2>&1

echo [VEN 911] Servidor iniciado correctamente. Logs en: %LOG%
endlocal
