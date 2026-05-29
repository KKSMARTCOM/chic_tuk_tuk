# Guide de Déploiement - Restructuration Commission/Paiement

## 📋 Étapes de Déploiement

### Prérequis

- Accès à la ligne de commande du serveur
- Backup récent de la base de données
- PHP >= 8.0
- Laravel 10+

---

## 🚀 Processus de Déploiement

### Étape 1: Backup de la Base de Données (RECOMMANDÉ)

```bash
# Créez un backup avant de procéder
mysqldump -u root -p reservation > backup_before_restructuring_$(date +%Y%m%d_%H%M%S).sql
```

### Étape 2: Mettre à jour le Code

```bash
# Pullez les dernières modifications
git pull origin main
# ou téléchargez manuellement les fichiers
```

### Étape 3: Exécuter les Migrations

```bash
# Appliquez les migrations
php artisan migrate

# Ou migrez seulement les migrations en attente
php artisan migrate --step
```

**Ce que font les migrations:**

1. ✅ Crée la table `payments` avec UUID
2. ✅ Supprime la colonne `is_paid` de la table `commissions`

### Étape 4: Vérifier l'Installation

#### Vérifier les migrations

```bash
php artisan migrate:status
```

#### Vérifier que la table payments existe

```bash
php artisan tinker
>>> Schema::hasTable('payments')
=> true

>>> Schema::hasColumn('commissions', 'is_paid')
=> false  // Doit être false
```

#### Vérifier les modèles

```bash
php artisan tinker
>>> use App\Models\Payment;
>>> Payment::count()
=> 0  // Au départ, 0 paiements

>>> use App\Models\Driver;
>>> $driver = Driver::first();
>>> $driver->payments  // Doit fonctionner
```

### Étape 5: Effacer le Cache

```bash
# Effacez les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ou tout d'un coup
php artisan optimize:clear
```

### Étape 6: Vérifier l'Interface

1. Allez dans **Admin > Gestion des Commissions**
    - ✅ Doit fonctionner normalement
    - ✅ Pas de colonne "Statut Paiement"
    - ✅ Lien vers "Gestion des Paiements" visible

2. Allez dans **Admin > Gestion des Paiements**
    - ✅ Page doit charger
    - ✅ Tableau vide (aucun paiement enregistré)
    - ✅ Stats affichées correctement
    - ✅ Formulaire de création fonctionnel

---

## 🔄 Vérifications Post-Déploiement

### 1. Vérification des Commissions

```bash
php artisan tinker

use App\Models\Commission;
$commission = Commission::first();

# Vérifier que is_paid n'existe plus
$commission->is_paid  # ❌ Erreur - c'est bon, colonne n'existe plus
$commission->amount   # ✅ Doit fonctionner
$commission->driver   # ✅ Relation doit fonctionner
```

### 2. Vérification des Paiements

```bash
use App\Models\Payment;
Payment::count()  # Doit être 0 initialement

# Créer un paiement de test
Payment::create([
    'driver_id' => 1,
    'amount' => 10000,
    'payment_method' => 'cash',
    'payment_date' => now(),
])
```

### 3. Vérification des Routes

```bash
# Lister les routes pour paiements
php artisan route:list | grep payment

# Vous devriez voir:
# GET    /admin/payments
# GET    /admin/payments/create
# POST   /admin/payments
# GET    /admin/payments/{payment}
# GET    /admin/payments/{payment}/edit
# PUT    /admin/payments/{payment}
# DELETE /admin/payments/{payment}
# GET    /admin/payments/driver/{driverId}/details
```

### 4. Vérification des Vues

#### Commissions - Vue Index

- ✅ Statistiques affichées (Revenu Total, Total Commissions)
- ✅ Bouton "Gestion des Paiements" visible
- ✅ Pas de filtre "Statut Paiement"
- ✅ Pas de boutons d'action de paiement

#### Commissions - Vue Show

- ✅ Affiche les détails de la commission
- ✅ Bouton "Gérer Paiements" visible
- ✅ Pas de boutons "Marquer comme Payé/Non Payé"
- ✅ Pas de modal de paiement

#### Paiements - Vue Index

- ✅ Stats affichées (Total Payé, Total Dû, Solde, Payé ce mois)
- ✅ Filtres fonctionnels
- ✅ Bouton "Nouveau Paiement" visible
- ✅ Tableau vide ou avec paiements existants

#### Paiements - Vue Create

- ✅ Formulaire visible
- ✅ Tous les champs requis
- ✅ Validations fonctionnelles

---

## 📊 Tests de Fonctionnalité

### Test 1: Créer un Paiement

1. Allez dans **Gestion des Paiements > Nouveau Paiement**
2. Remplissez le formulaire:
    - Agent: [Sélectionner un agent]
    - Montant: 100000
    - Méthode: Espèces
    - Date: Aujourd'hui
3. Cliquez **Enregistrer**
4. ✅ Doit rediriger vers la liste des paiements avec le message de succès

### Test 2: Voir Détails du Paiement

1. Dans la liste des paiements, cliquez sur l'icône 👁️
2. ✅ Doit afficher:
    - Infos du paiement
    - Infos de l'agent
    - Résumé (Total Dû, Payé, Solde)

### Test 3: Modifier un Paiement

1. Cliquez sur l'icône ✏️
2. Modifiez le montant (ex: 120000)
3. Cliquez **Mettre à jour**
4. ✅ Doit rediriger avec le message de succès

### Test 4: Supprimer un Paiement

1. Sur la page de détails, cliquez **Supprimer**
2. Confirmez la suppression
3. ✅ Doit rediriger vers la liste

### Test 5: Filtrer les Paiements

1. Filtrez par agent
2. Filtrez par méthode de paiement
3. Recherchez par référence
4. ✅ Les résultats doivent être corrects

### Test 6: Voir les Commissions

1. Allez dans **Gestion des Commissions**
2. ✅ Doit voir la liste des commissions
3. ✅ Pas d'erreur d'affichage

### Test 7: Détails Conducteur

1. Dans **Gestion des Paiements**, trouvez un paiement
2. Cliquez sur l'icône 👁️
3. Cliquez sur **Voir les détails de paiement complets**
4. ✅ Doit afficher les paiements ET commissions du conducteur

---

## ⚠️ Problèmes Courants et Solutions

### Problème: "Unknown column 'is_paid' in 'where clause'"

**Cause:** Les migrations n'ont pas été exécutées
**Solution:**

```bash
php artisan migrate
php artisan cache:clear
```

### Problème: La vue de paiement affiche une erreur

**Cause:** Le contrôleur n'a pas accès aux fichiers de vue
**Solution:**

```bash
php artisan view:clear
php artisan config:cache
```

### Problème: Les routes de paiement ne fonctionnent pas

**Cause:** Les routes n'ont pas été rechargées
**Solution:**

```bash
php artisan route:cache
php artisan optimize:clear
```

### Problème: Les IDs UUID ne sont pas générés

**Cause:** Le trait HasUuid n'est pas appliqué
**Solution:**
Vérifiez que Payment utilise bien `use HasUuid;`

### Problème: Erreur 404 sur /admin/payments

**Cause:** Les routes ne sont pas enregistrées
**Solution:**

1. Vérifiez que PaymentController est importé dans routes/admin.php
2. Vérifiez que les routes sont bien écrites
3. Exécutez `php artisan route:list | grep payment`

---

## 🔐 Sécurité

### Points de Sécurité Vérifiés

- ✅ Validations de formulaire
- ✅ Autorisation d'accès admin requise
- ✅ Protection CSRF
- ✅ Références uniques pour les paiements
- ✅ Soft deletes possibles (si configurés)

### Recommandations

1. 🔒 Limitez l'accès aux paiements aux admins uniquement
2. 📝 Gardez un log des modifications de paiements
3. 💾 Faites des backups réguliers
4. 🔍 Auditez régulièrement les paiements

---

## 📈 Monitoring Post-Déploiement

### Logs à Vérifier

```bash
tail -f storage/logs/laravel.log
```

### Points à Surveiller

- ❌ Pas d'erreurs de migration
- ❌ Pas d'erreurs d'accès aux données
- ❌ Pas de requêtes N+1 optimisables
- ❌ Performance acceptable

---

## 🎯 Checklist Finale

- [ ] Backup créé
- [ ] Migrations exécutées
- [ ] Cache effacé
- [ ] Interface Commissions fonctionne
- [ ] Interface Paiements fonctionne
- [ ] Formulaire de paiement fonctionne
- [ ] Filtres fonctionnent
- [ ] Pas d'erreurs dans les logs
- [ ] Tests de fonctionnalité réussis
- [ ] Utilisateurs informés du changement

---

## 📞 Support

En cas de problème:

1. Vérifiez les logs: `tail storage/logs/laravel.log`
2. Nettoyez le cache: `php artisan optimize:clear`
3. Réexécutez les migrations: `php artisan migrate`
4. Consultez la documentation: `PAYMENT_RESTRUCTURING_SUMMARY.md`

---

_Document créé: 21 mai 2026_
