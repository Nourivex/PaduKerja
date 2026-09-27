@echo off
setlocal EnableExtensions EnableDelayedExpansion

REM ═════════════════════════════════════════════
REM Laravel Dev Helper
REM ═════════════════════════════════════════════

set "APP_NAME=Laravel Dev Helper"
set "APP_VERSION=1.0.0"
set "APP_AUTHOR=Nourivex Team"
set "APP_LICENSE=MIT"

REM ═════════════════════════════════════════════
REM ANSI Colors
REM ═════════════════════════════════════════════

for /F "delims=" %%E in ('echo prompt $E^| cmd') do set "ESC=%%E"

set "RESET=!ESC![0m"
set "BOLD=!ESC![1m"
set "GREEN=!ESC![0;32m"
set "YELLOW=!ESC![0;33m"
set "RED=!ESC![0;31m"
set "CYAN=!ESC![0;36m"
set "DIM=!ESC![2m"

REM ═════════════════════════════════════════════
REM Main
REM ═════════════════════════════════════════════

:main

if "%~1"=="" (
    call :show_help
    exit /b 0
)

if /I "%~1"=="-h" (
    call :show_help
    exit /b 0
)

if /I "%~1"=="--help" (
    call :show_help
    exit /b 0
)

if /I "%~1"=="-v" (
    call :show_version
    exit /b 0
)

if /I "%~1"=="--version" (
    call :show_version
    exit /b 0
)

if /I "%~1"=="--setup" (
    call :run_setup
    exit /b !ERRORLEVEL!
)

if /I "%~1"=="--doctor" (
    call :show_doctor
    exit /b !ERRORLEVEL!
)

if /I "%~1"=="--env" (
    call :show_env
    exit /b !ERRORLEVEL!
)

call :run_artisan %*
exit /b !ERRORLEVEL!


REM ═════════════════════════════════════════════
REM Help
REM ═════════════════════════════════════════════

:show_help

echo.
echo !BOLD!!CYAN!!APP_NAME!!RESET!
echo !DIM!Environment-aware Laravel Artisan launcher.!RESET!
echo.
echo !BOLD!Author!   !RESET!: !APP_AUTHOR!
echo !BOLD!Version!  !RESET!: !APP_VERSION!
echo !BOLD!License!  !RESET!: !APP_LICENSE!
echo.
echo !BOLD!!CYAN!Usage! !RESET!
echo   !GREEN!dev.bat ^<artisan-command^> [options]! !RESET!
echo.
echo !BOLD!!CYAN!Examples! !RESET!
echo   !GREEN!dev.bat make:model User -m! !RESET!
echo   !GREEN!dev.bat make:controller AuthController! !RESET!
echo   !GREEN!dev.bat make:migration create_users_table! !RESET!
echo   !GREEN!dev.bat migrate! !RESET!
echo   !GREEN!dev.bat migrate:fresh --seed! !RESET!
echo   !GREEN!dev.bat route:list! !RESET!
echo   !GREEN!dev.bat test! !RESET!
echo   !GREEN!dev.bat tinker! !RESET!
echo.
echo !BOLD!!CYAN!Helper options! !RESET!
echo   !YELLOW!-h, --help! !RESET!       Show this help message
echo   !YELLOW!-v, --version! !RESET!    Show helper version
echo       !YELLOW!--setup! !RESET!       Setup development environment
echo       !YELLOW!--doctor! !RESET!     Check development environment
echo       !YELLOW!--env! !RESET!        Show detected environment
echo.
echo !BOLD!!CYAN!Setup! !RESET!
echo   !GREEN!dev.bat --setup! !RESET!
echo.
echo   Installs Composer and Node dependencies,
echo   starts DDEV when available, prepares .env,
echo   generates the application key, and builds
echo   frontend assets.
echo.
echo !DIM!Database migrations are not executed automatically.!RESET!
echo.
echo !BOLD!!CYAN!Environment priority! !RESET!
echo   !GREEN!1.! !RESET!DDEV project
echo   !GREEN!2.! !RESET!Local PHP
echo   !GREEN!3.! !RESET!Error
echo.
echo !BOLD!!CYAN!Behavior! !RESET!
echo   If a DDEV project is detected, Artisan runs through DDEV.
echo   Otherwise, the helper falls back to local PHP.
echo.
echo !DIM!All arguments not recognized by this helper are passed! !RESET!
echo !DIM!directly to Laravel Artisan.! !RESET!
echo.

exit /b 0


REM ═════════════════════════════════════════════
REM Version
REM ═════════════════════════════════════════════

:show_version

echo !APP_NAME! v!APP_VERSION!
echo !APP_AUTHOR!

exit /b 0


REM ═════════════════════════════════════════════
REM Command detection
REM ═════════════════════════════════════════════

:has_php

where php >nul 2>&1
exit /b %ERRORLEVEL%


:has_composer

where composer >nul 2>&1
exit /b %ERRORLEVEL%


:has_npm

where npm >nul 2>&1
exit /b %ERRORLEVEL%


:has_ddev

where ddev >nul 2>&1
exit /b %ERRORLEVEL%


:has_ddev_project

if exist ".ddev\config.yaml" (
    exit /b 0
)

exit /b 1


:is_laravel_project

if exist "artisan" (
    exit /b 0
)

exit /b 1


:has_env_file

if exist ".env" (
    exit /b 0
)

exit /b 1


:has_env_example

if exist ".env.example" (
    exit /b 0
)

exit /b 1


:has_package_json

if exist "package.json" (
    exit /b 0
)

exit /b 1


REM ═════════════════════════════════════════════
REM Environment detection
REM ═════════════════════════════════════════════

:detect_environment

call :has_ddev
if not errorlevel 1 (
    call :has_ddev_project
    if not errorlevel 1 (
        set "ENVIRONMENT=ddev"
        exit /b 0
    )
)

call :has_php
if not errorlevel 1 (
    set "ENVIRONMENT=php"
    exit /b 0
)

set "ENVIRONMENT=none"

exit /b 0


REM ═════════════════════════════════════════════
REM PHP version
REM ═════════════════════════════════════════════

:get_php_version

set "PHP_VERSION="

for /f "delims=" %%V in ('php -r "echo PHP_VERSION;" 2^>nul') do (
    set "PHP_VERSION=%%V"
)

if not defined PHP_VERSION (
    set "PHP_VERSION=unknown"
)

exit /b 0


REM ═════════════════════════════════════════════
REM DDEV version
REM ═════════════════════════════════════════════

:get_ddev_version

set "DDEV_VERSION=unknown"

for /f "delims=" %%V in ('ddev version 2^>nul') do (
    if "!DDEV_VERSION!"=="unknown" (
        set "DDEV_VERSION=%%V"
    )
)

exit /b 0


REM ═════════════════════════════════════════════
REM Laravel version
REM ═════════════════════════════════════════════

:get_laravel_version

set "LARAVEL_VERSION=unknown"

call :is_laravel_project
if errorlevel 1 (
    set "LARAVEL_VERSION=not a Laravel project"
    exit /b 0
)

call :detect_environment

if "!ENVIRONMENT!"=="ddev" (
    for /f "delims=" %%V in ('ddev artisan --version 2^>nul') do (
        set "LARAVEL_VERSION=%%V"
    )
)

if "!ENVIRONMENT!"=="php" (
    for /f "delims=" %%V in ('php artisan --version 2^>nul') do (
        set "LARAVEL_VERSION=%%V"
    )
)

exit /b 0


REM ═════════════════════════════════════════════
REM Environment information
REM ═════════════════════════════════════════════

:show_env

call :detect_environment
call :get_laravel_version

echo.
echo !APP_NAME!
echo ────────────────────────────────────────

echo Author         !APP_AUTHOR!
echo Version        !APP_VERSION!

echo.
echo Project
echo ────────────────────────────────────────

echo Laravel        !LARAVEL_VERSION!

echo.
echo Environment
echo ────────────────────────────────────────

call :has_php
if not errorlevel 1 (
    call :get_php_version
    echo PHP            !PHP_VERSION!
) else (
    echo PHP            not found
)

call :has_ddev
if not errorlevel 1 (
    call :get_ddev_version
    echo DDEV           !DDEV_VERSION!
) else (
    echo DDEV           not found
)

echo.
echo Backend
echo ────────────────────────────────────────

if "!ENVIRONMENT!"=="ddev" (
    echo Selected       DDEV
    echo Reason         DDEV project detected
)

if "!ENVIRONMENT!"=="php" (
    echo Selected       Local PHP
    echo Reason         DDEV unavailable
)

if "!ENVIRONMENT!"=="none" (
    echo Selected       None
    echo Reason         Neither DDEV nor PHP found
)

echo.

exit /b 0


REM ═════════════════════════════════════════════
REM Doctor
REM ═════════════════════════════════════════════

:show_doctor

set "FAILED=0"

echo.
echo !APP_NAME! — Doctor
echo ────────────────────────────────────────

call :is_laravel_project
if not errorlevel 1 (
    echo !GREEN!✓!RESET! Laravel project      artisan found
) else (
    echo !RED!✗!RESET! Laravel project      artisan not found
    set "FAILED=1"
)

call :has_php
if not errorlevel 1 (
    call :get_php_version
    echo !GREEN!✓!RESET! PHP                  !PHP_VERSION!
) else (
    echo !YELLOW!⚠!RESET! PHP                  not found
)

call :has_composer
if not errorlevel 1 (
    echo !GREEN!✓!RESET! Composer             installed
) else (
    echo !YELLOW!⚠!RESET! Composer             not found
)

call :has_npm
if not errorlevel 1 (
    echo !GREEN!✓!RESET! Node/npm             installed
) else (
    echo !YELLOW!⚠!RESET! Node/npm             not found
)

call :has_ddev
if not errorlevel 1 (
    echo !GREEN!✓!RESET! DDEV                 installed
) else (
    echo !YELLOW!⚠!RESET! DDEV                 not found
)

call :has_ddev_project
if not errorlevel 1 (
    echo !GREEN!✓!RESET! .ddev/config.yaml    found
) else (
    echo !YELLOW!⚠!RESET! .ddev/config.yaml    not found
)

call :detect_environment

echo.
echo Environment
echo ────────────────────────────────────────

if "!ENVIRONMENT!"=="ddev" (
    echo !GREEN!✓!RESET! Backend              DDEV
    echo !GREEN!✓!RESET! Status               READY
)

if "!ENVIRONMENT!"=="php" (
    echo !GREEN!✓!RESET! Backend              Local PHP
    echo !GREEN!✓!RESET! Status               READY
)

if "!ENVIRONMENT!"=="none" (
    echo !RED!✗!RESET! Backend              unavailable
    echo !RED!✗!RESET! Status               NOT READY
    set "FAILED=1"
)

echo.

if "!FAILED!"=="0" (
    echo !GREEN!✓!RESET! Environment check passed.
) else (
    echo !RED!✗!RESET! Environment check failed.
)

echo.

exit /b !FAILED!


REM ═════════════════════════════════════════════
REM Development setup
REM ═════════════════════════════════════════════

:run_setup

call :is_laravel_project
if errorlevel 1 (
    echo !RED!✗!RESET! Laravel project not detected.
    echo !RED!✗!RESET! Run this command from the Laravel project root.
    exit /b 1
)

call :detect_environment

echo.
echo !BOLD!!CYAN!Laravel Dev Helper — Setup! !RESET!
echo ────────────────────────────────────────
echo.

REM ═══════════════════════════════════════════
REM DDEV setup
REM ═══════════════════════════════════════════

if "!ENVIRONMENT!"=="ddev" (

    echo !CYAN![dev]!RESET! Starting DDEV...

    ddev start
    if errorlevel 1 (
        echo !RED!✗!RESET! Failed to start DDEV.
        exit /b 1
    )

    echo !GREEN!✓!RESET! DDEV started
    echo.

    REM Composer
    echo !CYAN![dev]!RESET! Installing Composer dependencies...

    ddev composer install
    if errorlevel 1 (
        echo !RED!✗!RESET! Composer install failed.
        exit /b 1
    )

    echo !GREEN!✓!RESET! Composer dependencies installed
    echo.

    REM Environment
    call :has_env_file

    if not errorlevel 1 (
        echo !GREEN!✓!RESET! .env already exists
    ) else (
        call :has_env_example

        if not errorlevel 1 (
            echo !CYAN![dev]!RESET! Creating .env from .env.example...

            copy /Y ".env.example" ".env" >nul

            if errorlevel 1 (
                echo !RED!✗!RESET! Failed to create .env.
                exit /b 1
            )

            echo !GREEN!✓!RESET! .env created
        ) else (
            echo !YELLOW!⚠!RESET! .env.example not found
        )
    )

    echo.

    REM Application key
    call :has_env_file

    if not errorlevel 1 (
        echo !CYAN![dev]!RESET! Generating application key...

        ddev artisan key:generate --force
        if errorlevel 1 (
            echo !RED!✗!RESET! Failed to generate application key.
            exit /b 1
        )

        echo !GREEN!✓!RESET! Application key generated
    )

    echo.

    REM Node / frontend
    call :has_package_json

    if not errorlevel 1 (

        echo !CYAN![dev]!RESET! Installing Node dependencies...

        ddev npm install
        if errorlevel 1 (
            echo !RED!✗!RESET! npm install failed.
            exit /b 1
        )

        echo !GREEN!✓!RESET! Node dependencies installed
        echo.

        echo !CYAN![dev]!RESET! Building frontend assets...

        ddev npm run build
        if errorlevel 1 (
            echo !RED!✗!RESET! Frontend build failed.
            exit /b 1
        )

        echo !GREEN!✓!RESET! Frontend assets built
        echo.

    ) else (
        echo !YELLOW!⚠!RESET! package.json not found — frontend setup skipped
        echo.
    )

    echo !GREEN!✓!RESET! Development environment is ready.
    echo.

    echo !CYAN![dev]!RESET! Application:
    ddev describe | findstr /C:"https://"

    echo.
    echo !YELLOW!⚠!RESET! Database migrations were not executed.
    echo   Run: !GREEN!dev.bat migrate! !RESET!
    echo.

    exit /b 0
)


REM ═══════════════════════════════════════════
REM Local PHP setup
REM ═══════════════════════════════════════════

if "!ENVIRONMENT!"=="php" (

    echo !YELLOW!⚠!RESET! DDEV project is not available.
    echo !CYAN![dev]!RESET! Using local PHP environment.
    echo.

    REM Composer
    call :has_composer

    if errorlevel 1 (
        echo !RED!✗!RESET! Composer not found.
        echo !RED!✗!RESET! Install Composer before running setup.
        exit /b 1
    )

    echo !CYAN![dev]!RESET! Installing Composer dependencies...

    composer install
    if errorlevel 1 (
        echo !RED!✗!RESET! Composer install failed.
        exit /b 1
    )

    echo !GREEN!✓!RESET! Composer dependencies installed
    echo.

    REM Environment
    call :has_env_file

    if not errorlevel 1 (
        echo !GREEN!✓!RESET! .env already exists
    ) else (
        call :has_env_example

        if not errorlevel 1 (
            echo !CYAN![dev]!RESET! Creating .env from .env.example...

            copy /Y ".env.example" ".env" >nul

            if errorlevel 1 (
                echo !RED!✗!RESET! Failed to create .env.
                exit /b 1
            )

            echo !GREEN!✓!RESET! .env created
        ) else (
            echo !YELLOW!⚠!RESET! .env.example not found
        )
    )

    echo.

    REM Application key
    call :has_env_file

    if not errorlevel 1 (
        echo !CYAN![dev]!RESET! Generating application key...

        php artisan key:generate --force
        if errorlevel 1 (
            echo !RED!✗!RESET! Failed to generate application key.
            exit /b 1
        )

        echo !GREEN!✓!RESET! Application key generated
    )

    echo.

    REM Node / frontend
    call :has_package_json

    if not errorlevel 1 (

        call :has_npm

        if errorlevel 1 (
            echo !RED!✗!RESET! npm not found.
            echo !RED!✗!RESET! Install Node.js before running frontend setup.
            exit /b 1
        )

        echo !CYAN![dev]!RESET! Installing Node dependencies...

        npm install
        if errorlevel 1 (
            echo !RED!✗!RESET! npm install failed.
            exit /b 1
        )

        echo !GREEN!✓!RESET! Node dependencies installed
        echo.

        echo !CYAN![dev]!RESET! Building frontend assets...

        npm run build
        if errorlevel 1 (
            echo !RED!✗!RESET! Frontend build failed.
            exit /b 1
        )

        echo !GREEN!✓!RESET! Frontend assets built
        echo.

    ) else (
        echo !YELLOW!⚠!RESET! package.json not found — frontend setup skipped
        echo.
    )

    echo !GREEN!✓!RESET! Local development environment is ready.
    echo.

    echo !YELLOW!⚠!RESET! Database migrations were not executed.
    echo   Run: !GREEN!dev.bat migrate! !RESET!
    echo.

    exit /b 0
)


REM ═══════════════════════════════════════════
REM No environment
REM ═══════════════════════════════════════════

echo !RED!✗!RESET! No supported development environment found.
echo.
echo Install one of the following:
echo   • DDEV
echo   • PHP + Composer + Node.js
echo.

exit /b 1


REM ═════════════════════════════════════════════
REM Run Artisan
REM ═════════════════════════════════════════════

:run_artisan

call :is_laravel_project
if errorlevel 1 (
    echo !RED!✗!RESET! Laravel project not detected.
    echo !RED!✗!RESET! Run this command from the Laravel project root.
    exit /b 1
)

call :detect_environment

if "!ENVIRONMENT!"=="ddev" (
    echo !CYAN![dev]!RESET! Using DDEV
    ddev artisan %*
    exit /b !ERRORLEVEL!
)

if "!ENVIRONMENT!"=="php" (
    echo !CYAN![dev]!RESET! Using local PHP
    php artisan %*
    exit /b !ERRORLEVEL!
)

echo !RED!✗!RESET! Cannot run Laravel Artisan.
echo.
echo Neither DDEV nor PHP was found.
echo.
echo Install one of the following:
echo   • DDEV
echo   • PHP
echo.

exit /b 1
