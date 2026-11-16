@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0.."

echo =============================
echo       Windows Dev Setup      
echo =============================
echo.


set "PHP_CMD=php"
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP_CMD=C:\xampp\php\php.exe"
        echo [INFO] System PHP Not Found in PATH. Using XAMPP PHP in C:\xampp\php\php.exe!
    ) else (
        echo [ERROR] PHP Executable Not Found in either PATH or C:\xampp\php\php.exe!
        echo "Please Install PHP or XAMPP first."
        pause
        exit /b 1
    )
)

set "COMPOSER_CMD="
where composer >nul 2>nul
if %errorlevel% equ 0 (
    set "COMPOSER_CMD=composer"
    echo [INFO] Using global Composer.
) else if exist "composer.phar" (
    set "COMPOSER_CMD="%PHP_CMD%" composer.phar"
    echo [INFO] Using local composer.phar.
) else (
    echo [ERROR] Composer is not installed!
    set /p choice=[OPTION] Do you want to download it from this link? https://getcomposer.org/composer-stable.phar [Y/n]: 
    if "!choice!"=="" set "choice=Y"
    if /i "!choice!"=="Y" (
        echo [INFO] Downloading from link..
        powershell -Command "Invoke-WebRequest -Uri https://getcomposer.org/composer-stable.phar -OutFile composer.phar"
        if exist "composer.phar" (
            set "COMPOSER_CMD="%PHP_CMD%" composer.phar"
            echo [SUCCESS] composer.phar downloaded successfully.
        ) else (
            echo [ERROR] Failed to download composer.phar. Either download it manually or check internet connection.
            pause
            exit /b 1
        )
    ) else (
        echo [INFO] Download composer to proceed to the setup.
        pause
        exit /b 1
    )
)

if not exist "vendor" (
    echo.
    set /p dependencies_choice=[OPTION] Vendor Directiory is missing. Do you want to download dependencies? Note: 7zip is needed for composer to unzip the dependencies [Y/n]: 
    if "!dependencies_choice!"=="" set "dependencies_choice=Y"
    if /i "!dependencies_choice!"=="Y" (
        echo [INFO] Installing Project Dependencies...
        %COMPOSER_CMD% install
        %COMPOSER_CMD% require vlucas/phpdotenv
        if !errorlevel! neq 0 (
            echo [ERROR] Composer dependencies installation failed!
            pause
            exit /b 1
        ) else (
            echo [SUCCESS] Composer dependencies installed!
        )
    ) else (
        echo [INFO] For manual setup run the following commands in terminal:
        echo composer install
        echo composer require vlucas/phpdotenv
    )
)

echo.
echo [INFO] Migration Setup, .env must be configured to have credentials that are to be used inside MySQL. MySQL also has to be running before proceeding.
set /p init_choice=[OPTION] Do you want to automatically create user and database listed in .env file. [Y/n]: 
if "!init_choice!"=="" set "init_choice=Y"
if /i "!init_choice!"=="Y" (
    echo [INFO] Creating database and user...
    "!PHP_CMD!" src/migrate.php --init_db
) else (
    "!PHP_CMD!" src/migrate.php
)

echo =============================
echo        Setup Finished        
echo =============================
echo.
pause