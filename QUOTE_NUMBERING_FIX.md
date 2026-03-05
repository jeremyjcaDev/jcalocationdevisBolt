# Correction du système de numérotation des devis

## Problème identifié

Le système de génération de numéros de devis causait des erreurs de "Duplicate entry" avec des numéros en notation scientifique (ex: `DEVIS9.2233720368548E+18`).

## Cause

L'ancienne méthode utilisait `CAST(SUBSTRING(...) AS UNSIGNED)` qui pouvait produire des nombres très grands en notation scientifique, causant des doublons dans la base de données.

## Solution implémentée

Le système de numérotation a été complètement refondu pour respecter la configuration du back-office :

### Paramètres respectés

1. **Préfixe** (`quote_number_prefix`) - Ex: "DEVIS", "QUOTE", "DEV"
2. **Séparateur** (`quote_number_separator`) - Ex: "-", "_", "/"
3. **Format année** (`quote_number_year_format`) - "YYYY" (2025), "YY" (25), ou vide
4. **Nombre de zéros** (`quote_number_padding`) - Ex: 3 pour "001", 4 pour "0001"
5. **Compteur** (`quote_number_counter`) - Numéro séquentiel automatique
6. **Reset annuel** (`quote_number_reset_yearly`) - Repart à 1 chaque année

### Exemple de numéros générés

Avec la configuration par défaut :
- Préfixe: "DEVIS"
- Séparateur: "-"
- Format année: "YYYY"
- Padding: 3
- Reset annuel: Activé

Résultat : `DEVIS-2025-001`, `DEVIS-2025-002`, etc.

## Scripts à exécuter

### 1. Nettoyer les doublons existants

```bash
mysql -u [user] -p [database] < cleanup_duplicate_quotes.sql
```

Ce script supprime tous les devis avec notation scientifique dans le numéro.

### 2. Vérifier/Initialiser les paramètres

```bash
mysql -u [user] -p [database] < check_quote_settings.sql
```

Ce script :
- Affiche la configuration actuelle
- Crée les paramètres par défaut si absents
- Initialise `quote_number_last_year` si nécessaire

### 3. Ajouter le champ manquant (si nécessaire)

```bash
mysql -u [user] -p [database] < migrations/add_quote_number_last_year.sql
```

Ajoute le champ `quote_number_last_year` pour le système de reset annuel.

## Fichiers modifiés

- `controllers/front/savedevis.php` - Nouvelle logique de génération de numéros

## Comportement

1. À chaque création de devis, le compteur s'incrémente automatiquement
2. Le compteur est sauvegardé dans `ps_jca_quote_settings`
3. Si le reset annuel est activé, le compteur repart à 1 au changement d'année
4. Le format du numéro suit exactement la configuration du back-office

## Tests recommandés

1. Créer un devis et vérifier le format du numéro
2. Créer plusieurs devis et vérifier l'incrémentation
3. Modifier la configuration dans le BO et créer un nouveau devis
4. Vérifier qu'il n'y a plus d'erreur de duplicate entry
