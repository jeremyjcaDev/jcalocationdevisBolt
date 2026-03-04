# Fonctionnalité Email Statut Devis

## Description
Les emails sont maintenant envoyés automatiquement lorsqu'un devis change de statut (validé ou refusé).

## Fonctionnement

### 1. Email de création de devis
- Envoyé automatiquement lors de la création d'un nouveau devis
- Contient les détails du devis et la liste des produits
- Peut être activé/désactivé dans la configuration

### 2. Email de validation de devis ✨ NOUVEAU
- Envoyé automatiquement lorsqu'un devis passe au statut "validated"
- Message de confirmation avec référence du devis
- Couleur verte pour indiquer le succès

### 3. Email de refus de devis ✨ NOUVEAU
- Envoyé automatiquement lorsqu'un devis passe au statut "refused"
- Message d'information avec référence du devis
- Couleur rouge pour indiquer le refus

## Configuration

Les emails peuvent être activés/désactivés dans la configuration du module :

1. **Email notifications activées** : Active/désactive tous les emails
2. **Email à la création** : Active l'email lors de la création
3. **Email à la validation** : Active l'email lors de la validation ✨ NOUVEAU
4. **Email au refus** : Active l'email lors du refus ✨ NOUVEAU

## Paramètres d'email personnalisables

- **Nom de l'expéditeur** : Le nom qui apparaît dans l'email
- **Email de l'expéditeur** : L'adresse email d'envoi
- **Email de réponse** : L'adresse pour les réponses

## Test

Pour tester l'envoi des emails de statut, accédez à :
```
http://votre-site.com/modules/jca_locationdevis/test_quote_status_email.php
```

Ce script :
- Récupère le dernier devis créé
- Teste l'envoi d'un email de validation
- Teste l'envoi d'un email de refus
- Affiche les résultats et diagnostics

## Prérequis

1. **Configuration email PrestaShop**
   - Allez dans : Paramètres avancés > Email
   - Configurez SMTP (recommandé) ou PHP mail()
   - Testez l'envoi avec le bouton "Envoyer un email de test"

2. **Email de la boutique**
   - Allez dans : Paramètres de la boutique > Contact
   - Configurez un email valide dans "Coordonnées de la boutique"

## Comportement

- Les emails sont envoyés **uniquement si le statut change**
- Si le statut est déjà "validated" ou "refused", aucun email n'est renvoyé
- Les emails respectent les paramètres de configuration du module
- Les erreurs d'envoi sont loggées dans les logs PHP

## Logs

Pour voir les logs d'email, consultez :
```
http://votre-site.com/modules/jca_locationdevis/view_logs.php
```

Ou vérifiez directement les logs d'erreur PHP de votre serveur.
