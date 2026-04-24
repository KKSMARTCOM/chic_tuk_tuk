# Gestion des Congés - Guide Complet

## Vue d'ensemble

Le système de gestion des congés a été complètement refondu pour implémenter un flux de demande/validation :

1. **Les conducteurs** soumettent des demandes de congé
2. **L'admin** examine et approuve/rejette les demandes
3. **Les jours approuvés** sont enregistrés comme jours utilisés

## Architecture

### Structure de données

#### Table `leave_requests`

```
- id (UUID)
- driver_id (UUID, FK)
- dates (JSON array) - Dates demandées
- status (enum: pending, approved, rejected)
- rejection_reason (text, nullable) - Motif du refus
- timestamps
```

#### Champs existants `drivers`

- `contract_type` - Durée du contrat en mois
- `start_date` - Date de début
- `leave_days_used` - Jours utilisés (total)
- `leave_dates` - JSON array des jours approuvés

### Modèles

**LeaveRequest**

- Relations : `belongsTo(Driver)`
- Casts: `dates` en array

**Driver** (mise à jour)

- Nouvelles relations : `hasMany(LeaveRequest)`
- Nouvelles méthodes :
    - `getPendingLeaveRequestsForCurrentMonth()`
    - `getApprovedLeaveRequestsForCurrentMonth()`
    - `removeLeaveDates(array $dates)`

## Flux utilisateur

### Côté Conducteur

#### 1. Voir l'état des congés (`/driver/leaves`)

- Affiche jours restants, utilisés, total
- Liste demandes en attente
- Liste congés approuvés
- Liste rejets avec motifs

#### 2. Demander un congé (`/driver/leaves/request`)

- Sélectionne dates (mois courant uniquement)
- Validation:
    - Dates en mois courant ✓
    - Dates non déjà demandées ✓
    - Dates non déjà approuvées ✓
    - Nombre de jours ≤ restants ✓
- Soumet demande en état `pending`

### Côté Admin

#### 1. Vue d'ensemble (`/admin/leaves`)

Tableau de tous les conducteurs avec:

- Durée du contrat (mois)
- Jours par mois : 2
- Total disponible : 2 × mois contrat
- Jours utilisés
- Jours restants
- Nombre demandes en attente
- Lien vers détails

#### 2. Demandes en attente (`/admin/leave-requests`)

Liste de toutes les demandes `pending`:

- Nom conducteur
- Jours restants / demandés
- Dates demandées
- Boutons: Approuver / Rejeter

#### 3. Détails conducteur (`/admin/leaves/{driver}`)

- Info contrat (dates, durée)
- Résumé congés
- **Demandes en attente** (section jaune)
    - Boutons Approuver/Rejeter
- **Congés approuvés ce mois** (section verte)
- **Tous les congés pris** (section rouge)
    - Bouton Révoquer par date

### Processus d'approbation

#### Approuver une demande

1. Admin clique "Approuver" sur demande
2. Système vérifie:
    - Assez de jours restants ✓
    - Pas de conflits ✓
    - Dates valides ✓
3. Si OK:
    - Status → `approved`
    - Dates ajoutées à `driver.leave_dates`
    - `driver.leave_days_used` incrémenté
4. Conducteur voit dans "Approuvés"

#### Rejeter une demande

1. Admin clique "Rejeter"
2. Modal: entre motif (min 5 caractères)
3. Enregistre:
    - Status → `rejected`
    - Motif sauvegardé
4. Conducteur voit dans "Rejetés"

#### Révoquer un congé

1. Admin clique "Révoquer" sur date spécifique
2. Confirmation
3. Si OK:
    - Date retirée de `leave_dates`
    - `leave_days_used` décrémenté

## Logique commerciale

### Allocation de jours

```
Jours par mois = 2 (fixé)
Durée contrat = contract_type (en mois)
Total disponible = 2 × durée contrat

Exemple:
- Contrat 24 mois → 48 jours total
- Contrat 12 mois → 24 jours total
```

### Cumul des jours

- Les jours s'accumulent sur la durée totale du contrat
- Pas de réinitialisation annuelle
- Jours restants = Total - Utilisés

### Restrictions

- Demande seulement pour le **mois courant**
- Une demande peut couvrir 1+ jours
- Les dates doivent être **uniques** (pas de doublons)
- Maximum = jours restants

## Routes

### Admin

```
GET    /admin/leaves                                    → index (conducteurs)
GET    /admin/leaves/{driver}                          → show (détails)
GET    /admin/leave-requests                           → requests (en attente)
POST   /admin/leave-requests/{leaveRequest}/approve    → approveRequest
POST   /admin/leave-requests/{leaveRequest}/reject     → rejectRequest
POST   /admin/leaves/{driver}/revoke                   → revokeLeave
```

### Driver

```
GET    /driver/leaves                                  → index (historique)
GET    /driver/leaves/request                          → create (formulaire)
POST   /driver/leaves                                  → store (enregistrer)
```

## Cas d'usage

### Scénario 1: Approbation simple

1. Conducteur demande 2 jours en mai
2. Admin voit demande, jours suffisants ✓
3. Admin clique "Approuver"
4. Jours ajoutés, restants diminuent
5. Conducteur voit dans "Approuvés"

### Scénario 2: Refus avec motif

1. Conducteur demande 3 jours
2. Admin clique "Rejeter"
3. Saisit motif: "Période de forte activité"
4. Conducteur voit rejet avec motif
5. Peut refaire une demande

### Scénario 3: Revocation

1. Admin approuve congé le 15/05
2. Plus tard, doit révoquer
3. Admin clique "Révoquer" sur date
4. Jour libéré, jours utilisés décrémentent

## Limitations actuelles

- La `contract_end_date` est calculée (affichée mais non stockée)
- Pas de notification automatique aux conducteurs
- Pas de restrictions sur jours spécifiques (dimanche, jours fériés)
- Pas d'exportation/rapport

## Validation côté serveur

Toutes les vérifications sont effectuées côté serveur:

- Validations Blade côté client pour UX
- Validations Laravel côté serveur pour sécurité
- Vérifications avant tout changement d'état

## Améliorations futures

- [ ] Notifications email
- [ ] Jours fériés/weekends
- [ ] Rapports d'absence
- [ ] Soldes de congés annuels
- [ ] Approbation en cascade
- [ ] Historique complet des modifications
