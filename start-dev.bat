@echo off
title Laravel Onboarding - Dev Environment
cd C:\Users\angie\dev\projects\proyecto_laboratorio_software\onboarding.github.io-main

echo ============================================
echo  Iniciando Ambiente de Desarrollo Laravel
echo ============================================
echo.
echo Iniciando servidor web...
start "Laravel Server" cmd /k "php artisan serve"

timeout /t 2 /nobreak

echo Iniciando queue listener para emails...
start "Queue Listener" cmd /k "php artisan queue:listen --tries=1 --timeout=60"

echo.
echo ============================================
echo  ✅ Ambiente iniciado correctamente
echo ============================================
echo.
echo 🌐 Servidor web: http://127.0.0.1:8000
echo 📧 Queue listener: activo (emails funcionando)
echo.
echo Presiona cualquier tecla para cerrar esta ventana...
pause >nul
