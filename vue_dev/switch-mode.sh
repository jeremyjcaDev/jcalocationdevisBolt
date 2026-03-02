#!/bin/bash

# Script pour basculer facilement entre les modes PrestaShop et Supabase

echo "==================================="
echo "   JCA Location Devis - Switch API Mode"
echo "==================================="
echo ""
echo "Quel mode voulez-vous utiliser ?"
echo "1) PrestaShop (votre site production)"
echo "2) Supabase (développement ici)"
echo ""
read -p "Votre choix (1 ou 2): " choice

if [ "$choice" = "1" ]; then
    echo ""
    echo "Configuration pour PrestaShop..."
    cat > .env.local << 'EOF'
# Configuration API - Mode PrestaShop
VITE_API_MODE=prestashop

# Les variables PrestaShop seront fournies automatiquement par le module
EOF
    echo "✅ Mode PrestaShop activé !"
    echo ""
    echo "Prochaines étapes :"
    echo "1. Lancez 'npm run build'"
    echo "2. Les fichiers seront générés dans ../views/js/"
    echo "3. Utilisez ces fichiers sur votre PrestaShop"

elif [ "$choice" = "2" ]; then
    echo ""
    echo "Configuration pour Supabase..."
    cat > .env.local << 'EOF'
# Configuration API - Mode Supabase
VITE_API_MODE=supabase

# Configuration Supabase (mode développement)
VITE_SUPABASE_URL=https://zjgbzlqxypuwuprrlspp.supabase.co
VITE_SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InpqZ2J6bHF4eXB1d3VwcnJsc3BwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjUzOTQwNjYsImV4cCI6MjA4MDk3MDA2Nn0.BRLKBCnZf3XtVZ7Gyv3oZpz078j1lVrBWLHH20DBeBA
EOF
    echo "✅ Mode Supabase activé !"
    echo ""
    echo "Prochaines étapes :"
    echo "1. Lancez 'npm run dev' pour développer"
    echo "2. Ouvrez http://localhost:8080 dans votre navigateur"

else
    echo "❌ Choix invalide. Veuillez relancer le script."
    exit 1
fi

echo ""
echo "==================================="
