# Restructuration du Système de Commission et Paiement

## Vue d'ensemble des changements

Le système a été restructuré pour séparer la gestion des commissions dues de celle des paiements effectifs. Les commissions restent tracées pour chaque course, tandis que les paiements sont enregistrés manuellement dans un nouveau module.

## Changements apportés

### 1. **Nouveau Modèle et Table `Payment`**

- **Fichier**: `app/Models/Payment.php`
- **Migration**: `database/migrations/2024_05_21_create_payments_table.php`
- **Colonnes**:
    - `id` (UUID)
    - `driver_id` (clé étrangère)
    - `amount` (montant du paiement)
    - `payment_method` (cash, bank_transfer, check, mobile_money, other)
    - `payment_date` (date du paiement)
    - `notes` (notes optionnelles)
    - `reference_number` (numéro de reçu ou référence)
    - timestamps

### 2. **Modèle `Commission` - Simplification**

- **Fichier**: `app/Models/Commission.php`
- **Changements**:
    - Suppression de la colonne `is_paid`
    - Simplifie le modèle à juste tracker les commissions dues
- **Migration**: `database/migrations/2024_05_21_remove_is_paid_from_commissions.php`

### 3. **Service `PaymentService`**

- **Fichier**: `app/Services/PaymentService.php`
- **Méthodes principales**:
    - `create()` - Créer un nouveau paiement
    - `getAllPayments()` - Récupérer les paiements avec filtres
    - `getPaymentStats()` - Statistiques globales des paiements
    - `getDriverPayments()` - Détails des paiements pour un conducteur
    - `getDriverDueCommissions()` - Récupérer les commissions dues d'un conducteur
    - `update()` et `delete()` - Modifier/supprimer des paiements

### 4. **Service `CommissionService` - Nettoyage**

- **Fichier**: `app/Services/CommissionService.php`
- **Changements**:
    - Suppression des méthodes `markAsPaid()` et `markAsUnpaid()`
    - Suppression du filtre `is_paid` dans `getAllCommissions()`
    - Simplification des stats (ne compte que les commissions)

### 5. **Contrôleur `PaymentController` - Nouveau**

- **Fichier**: `app/Http/Controllers/Admin/PaymentController.php`
- **Fonctionnalités**:
    - CRUD complet pour les paiements
    - `index()` - Liste des paiements avec filtres
    - `create()` / `store()` - Créer un paiement
    - `show()` - Afficher les détails d'un paiement
    - `edit()` / `update()` - Modifier un paiement
    - `destroy()` - Supprimer un paiement
    - `driverPaymentDetails()` - Détails de paiement pour un conducteur

### 6. **Contrôleur `CommissionController` - Simplification**

- **Fichier**: `app/Http/Controllers/Admin/CommissionController.php`
- **Changements**:
    - Suppression des méthodes `markAsPaid()` et `markAsUnpaid()`
    - Reste: `index()`, `show()`

### 7. **Vues Commissions**

- **Fichier**: `resources/views/pages/admin/commissions/index.blade.php`
- **Changements**:
    - Suppression du filtre de statut de paiement
    - Suppression du modal de paiement
    - Ajout d'un lien vers le module de paiement
    - Simplification des stats
    - Tableau simplifié sans colonnes de statut de paiement

### 8. **Vues Paiements - Nouvelles**

- **Dossier**: `resources/views/pages/admin/payments/`
- **Fichiers**:
    - `index.blade.php` - Liste des paiements avec stats et filtres
    - `create.blade.php` - Formulaire de création de paiement
    - `show.blade.php` - Détails d'un paiement
    - `edit.blade.php` - Formulaire de modification
    - `driver-details.blade.php` - Vue détaillée des paiements d'un conducteur

### 9. **Modèle `Driver` - Relation ajoutée**

- **Fichier**: `app/Models/Driver.php`
- **Ajout**: Relation `payments()` pour accéder aux paiements du conducteur

### 10. **Routes - Mises à jour**

- **Fichier**: `routes/admin.php`
- **Changements**:
    - Suppression des routes: `commissions.mark-paid`, `commissions.mark-unpaid`
    - Ajout du contrôleur `PaymentController` dans les imports
    - Nouvelles routes pour les paiements (CRUD complet)

## Flux de Travail

### Pour les Commissions

1. Les commissions sont créées automatiquement lors de chaque course terminée
2. Elles restent dans la section "Gestion des Commissions" pour référence
3. Aucun statut de paiement ne leur est associé

### Pour les Paiements

1. Rendez-vous dans **Gestion des Paiements**
2. Cliquez sur **Nouveau Paiement**
3. Sélectionnez l'agent
4. Entrez le montant payé
5. Choisissez la méthode de paiement
6. Enregistrez la date et la référence (optionnel)
7. Confirmez l'enregistrement

### Calcul Automatique du Solde

- **Total Dû** = Somme de toutes les commissions d'un agent
- **Total Payé** = Somme de tous les paiements enregistrés
- **Solde Dû** = Total Dû - Total Payé

## Avantages de cette Restructuration

1. **Séparation des responsabilités** - Commissions vs Paiements
2. **Flexibilité** - Un paiement peut couvrir plusieurs commissions ou une partie
3. **Transparence** - Traçabilité complète des paiements
4. **Historique** - Garde un enregistrement de tous les paiements
5. **Analyse** - Facilite le suivi des retards de paiement par agent

## Migrations à exécuter

Pour appliquer ces changements à la base de données:

```bash
php artisan migrate
```

Les migrations seront exécutées dans cet ordre:

1. Création de la table `payments`
2. Suppression de la colonne `is_paid` de `commissions`

## Notes supplémentaires

- Les paiements peuvent être filtrés par agent, méthode et période
- Les références de paiement sont uniques pour éviter les doublons
- Chaque paiement peut avoir des notes pour contextualiser
- L'interface affiche le solde dû en temps réel pour chaque conducteur
