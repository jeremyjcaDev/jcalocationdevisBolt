@echo off
REM Script pour basculer facilement entre les modes PrestaShop et Supabase (Windows)

echo ===================================
echo    JCA Location Devis - Switch API Mode
echo ===================================
echo.
echo Quel mode voulez-vous utiliser ?
echo 1) PrestaShop (votre site production)
echo 2) Supabase (developpement ici)
echo.
set /p choice="Votre choix (1 ou 2): "

if "%choice%"=="1" (
    echo.
    echo Configuration pour PrestaShop...
    (
        echo # Configuration API - Mode PrestaShop
        echo VITE_API_MODE=prestashop
        echo.
        echo # Les variables PrestaShop seront fournies automatiquement par le module
    ) > .env.local
    echo ✓ Mode PrestaShop active !
    echo.
    echo Prochaines etapes :
    echo 1. Lancez 'npm run build'
    echo 2. Les fichiers seront generes dans ../views/js/
    echo 3. Utilisez ces fichiers sur votre PrestaShop
) else if "%choice%"=="2" (
    echo.
    echo Configuration pour Supabase...
    (
        echo # Configuration API - Mode Supabase
        echo VITE_API_MODE=supabase
        echo.
        echo # Configuration Supabase ^(mode developpement^)
        echo VITE_SUPABASE_URL=https://zjgbzlqxypuwuprrlspp.supabase.co
        echo VITE_SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InpqZ2J6bHF4eXB1d3VwcnJsc3BwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjUzOTQwNjYsImV4cCI6MjA4MDk3MDA2Nn0.BRLKBCnZf3XtVZ7Gyv3oZpz078j1lVrBWLHH20DBeBA
    ) > .env.local
    echo ✓ Mode Supabase active !
    echo.
    echo Prochaines etapes :
    echo 1. Lancez 'npm run dev' pour developper
    echo 2. Ouvrez http://localhost:8080 dans votre navigateur
) else (
    echo X Choix invalide. Veuillez relancer le script.
    exit /b 1
)

echo.
echo ===================================
pause
