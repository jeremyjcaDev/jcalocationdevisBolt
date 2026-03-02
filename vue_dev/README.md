# JCA Location Devis - Frontend Vue.js

Module de gestion de devis et location pour PrestaShop avec support dual : PrestaShop et Supabase.

## 🚀 Installation rapide

```bash
npm install
```

## 🔄 Basculer entre les modes

### Méthode simple (recommandée)

**Linux/Mac :**
```bash
./switch-mode.sh
```

**Windows :**
```bash
switch-mode.bat
```

### Méthode manuelle

Editez le fichier `.env.local` et changez la variable :

```env
# Pour PrestaShop
VITE_API_MODE=prestashop

# OU pour Supabase
VITE_API_MODE=supabase
```

## 📦 Commandes disponibles

### Développement (mode Supabase)
```bash
npm run dev
```
Ouvre http://localhost:8080

### Build pour PrestaShop
```bash
npm run build
```
Les fichiers sont générés dans `../views/js/`

### Linter
```bash
npm run lint
```

## 🎯 Deux modes de fonctionnement

### Mode PrestaShop (Production)

**Quand utiliser ?**
- Sur votre site PrestaShop réel
- Pour la production

**Comment ça marche ?**
1. Changez le mode : `VITE_API_MODE=prestashop`
2. Buildez : `npm run build`
3. Les fichiers générés dans `../views/js/` sont prêts pour PrestaShop
4. L'API PHP PrestaShop (`src/Controller/AdminJcaLocationdevisController.php`) est utilisée
5. Base de données MySQL PrestaShop

**Configuration automatique**
- Le token et l'URL sont fournis par PrestaShop
- Aucune configuration manuelle nécessaire

---

### Mode Supabase (Développement)

**Quand utiliser ?**
- Pour développer ici
- Pour tester sans PrestaShop

**Comment ça marche ?**
1. Changez le mode : `VITE_API_MODE=supabase`
2. Lancez : `npm run dev`
3. L'Edge Function Supabase est utilisée (`supabase/functions/quote-api`)
4. Base de données PostgreSQL Supabase

**Configuration**
Les credentials Supabase sont déjà dans `.env.local` :
```env
VITE_SUPABASE_URL=https://zjgbzlqxypuwuprrlspp.supabase.co
VITE_SUPABASE_ANON_KEY=eyJhbGc...
```

## 🗂 Structure du projet

```
vue_dev/
├── src/
│   ├── App.vue                 # App principale
│   ├── main.js                 # Point d'entrée
│   ├── components/             # Composants Vue
│   │   ├── ConfigDevis.vue
│   │   ├── ConfigLocation.vue
│   │   ├── ProductRentalConfig.vue
│   │   └── QuoteBuilder.vue
│   ├── views/                  # Pages
│   │   ├── AdminConfig.vue
│   │   ├── CreateQuote.vue
│   │   ├── EditQuote.vue
│   │   └── QuotesList.vue
│   ├── services/               # Services API
│   │   ├── api.js              # Service principal (gère les 2 modes)
│   │   ├── quoteService.js
│   │   ├── customerService.js
│   │   └── rentalConfigService.js
│   └── router/
│       └── index.js            # Routes Vue Router
├── .env.local                  # 🔧 FICHIER À MODIFIER
├── .env.example                # Template
├── switch-mode.sh              # Script de bascule (Linux/Mac)
├── switch-mode.bat             # Script de bascule (Windows)
└── package.json
```

## 🔍 Vérifier le mode actuel

Ouvrez la console du navigateur, vous verrez :
```
API Mode: supabase
API URL: https://zjgbzlqxypuwuprrlspp.supabase.co/functions/v1/quote-api
```

ou

```
API Mode: prestashop
API URL: /modules/jca_locationdevis/api
```

## 🐛 Dépannage

### Le mode ne change pas
1. Vérifiez que vous avez modifié `.env.local` (pas `.env.example`)
2. Redémarrez le serveur : `Ctrl+C` puis `npm run dev`

### Erreur "Cannot connect to server"
- **Mode PrestaShop** : Vérifiez que votre PrestaShop est démarré
- **Mode Supabase** : Vérifiez les credentials dans `.env.local`

### Erreur 404 sur l'API
- **Mode PrestaShop** : Vérifiez l'URL dans la config PrestaShop
- **Mode Supabase** : Vérifiez que l'Edge Function est bien déployée

## 📚 Documentation complète

Voir le fichier `../CONFIGURATION.md` à la racine du projet pour plus de détails.

## ⚙️ Technologies utilisées

- Vue.js 2.7
- Vue Router
- Axios
- Vite (via Vue CLI)
- Mode dual : PrestaShop PHP + Supabase Edge Functions
