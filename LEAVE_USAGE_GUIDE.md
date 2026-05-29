# Gestion des Pauses - Guide d'utilisation

## Pour les Agents

### Accéder au système

- URL: `/driver/leaves`
- Nécessite authentification avec rôle `driver`

### 1️⃣ Voir mes Pauses

```
/driver/leaves

Affiche:
- Jours par mois (2)
- Total disponible (2 × mois contrat)
- Jours utilisés
- Jours restants

+ Historique:
  - En attente (demandes soumises)
  - Approuvés (Pauses acceptés)
  - Rejetés (avec motifs)
```

### 2️⃣ Demander un Pause

```
/driver/leaves/request

Étapes:
1. Voir jours restants
2. Sélectionner dates (calendrier)
   ✓ Mois courant uniquement
   ✓ Pas de doublons
   ✓ Max = jours restants
3. Ajouter date à la liste
4. Soumettre demande

Confirmation:
✓ "Demande soumise avec succès"
- En attente de validation admin
```

### 3️⃣ Suivre les demandes

Chaque demande a un statut:

| Statut     | Couleur  | Signification                | Action possible |
| ---------- | -------- | ---------------------------- | --------------- |
| En attente | 🟡 Jaune | Admin n'a pas encore examiné | Attendre        |
| Approuvé   | 🟢 Vert  | Pause accepté et enregistré  | Aucune          |
| Rejeté     | 🔴 Rouge | Pause refusé avec motif      | Refaire demande |

---

## Pour l'Admin

### Accéder au système

- URL: `/admin/leaves`
- Nécessite authentification avec rôle `admin`

### 1️⃣ Dashboard - Vue d'ensemble

```
/admin/leaves

Tableau avec tous les Agents:
- Nom
- Durée contrat (mois)
- Jours par mois (2)
- Total disponible
- Jours utilisés
- Jours restants (💚 vert si positif, 💔 rouge si négatif)
- Demandes en attente (badge rouge si > 0)
- Lien "Voir détails"

💡 Bouton bleu en haut-droit:
   "Demandes en attente" + compteur
```

### 2️⃣ Demandes en attente

```
/admin/leave-requests

Liste toutes les demandes pending:
- Agent
- Jours restants / jours demandés
- Statut (✓ Suffisant / ✗ Insuffisant)
- Dates demandées avec jours de semaine
- Boutons: [Approuver] [Rejeter]

Approuver:
  → Validation auto
  → Jours ajoutés
  → Email Agent (futur)

Rejeter:
  → Modal avec champ motif
  → Min 5 caractères
  → Enregistré
```

### 3️⃣ Détails Agent

```
/admin/leaves/{driver}

Sections:

📅 INFO CONTRAT
  - Date début
  - Durée (mois)
  - Date fin (calculée)

📊 RÉSUMÉ PauseS
  - 2 jours/mois
  - Total disponible
  - Jours utilisés
  - Jours restants

⏳ DEMANDES EN ATTENTE
  (fond jaune)
  - Date soumise
  - Dates demandées (pastilles jaunes)
  - [Approuver] [Rejeter]

✅ PauseS APPROUVÉS CE MOIS
  (fond vert)
  - Liste des dates approuvées
  - Jour de la semaine

📋 TOUS LES JOURS DE Pause
  (fond rouge)
  - Toutes les dates approuvées (historique)
  - [Révoquer] pour chaque date
```

### 4️⃣ Actions disponibles

#### Approuver une demande

```
1. Cliquer "Approuver"
2. Système vérifie:
   ✓ Assez de jours
   ✓ Pas de conflits
   ✓ Dates valides
3. Si OK:
   - Status → "approved"
   - Dates ajoutées
   - Jours utilisés +N
4. Message: "Approuvée avec succès"
5. Agent voit Pauses dans "Approuvés"
```

#### Rejeter une demande

```
1. Cliquer "Rejeter"
2. Modal: Entrer motif (min 5 caractères)
3. Cliquer "Rejeter"
4. Status → "rejected"
5. Motif enregistré
6. Agent voit dans "Rejetés"
```

#### Révoquer un Pause

```
1. Voir date approuvée
2. Cliquer "Révoquer"
3. Confirmation: "Êtes-vous sûr ?"
4. Si OK:
   - Date supprimée
   - Jours utilisés -1
   - Agent récupère 1 jour
5. Message: "Pause révoqué"
```

---

## Exemples concrets

### Exemple 1: Demande et approbation simple

**Jour 1 - Agent:**

1. Va à `/driver/leaves/request`
2. Voit: 12 jours restants
3. Sélectionne: 10/05, 11/05 (2 jours)
4. Soumet demande
5. Voit demande "En attente"

**Jour 1 - Admin:**

1. Va à `/admin/leave-requests`
2. Voit demande du Agent
3. Voit: "12 jours restants" et "2 jours demandés" ✓
4. Clique "Approuver"
5. Demande disparaît

**Jour 1 - Agent (après):**

1. Va à `/driver/leaves`
2. Voit maintenant:
    - Jours restants: 10 (au lieu de 12)
    - Jours utilisés: 2 (augmenté de 2)
    - Pauses approuvés: 10/05 et 11/05

### Exemple 2: Demande rejetée

**Agent:**

1. Demande 5 jours
2. Voit: "Demande en attente"

**Admin:**

1. Voit demande
2. Jours restants: 3, demandés: 5 ✗
3. Clique "Rejeter"
4. Entre motif: "Pas assez de jours disponibles"
5. Clique "Rejeter"

**Agent:**

1. Voit demande: "Rejetés"
2. Lit motif: "Pas assez de jours disponibles"
3. Peut refaire avec moins de jours

### Exemple 3: Révocation d'urgence

**Admin:**

1. Agent avait Pause approuvé 15/05
2. Urgence: besoin Agent 15/05
3. Va à `/admin/leaves/{Agent}`
4. Voit 15/05 dans "Tous les Pauses"
5. Clique "Révoquer"
6. Confirme
7. 15/05 disparu, jours utilisés: -1

**Agent:**

1. Voir disparaît de la liste
2. Jours restants augmente de 1
3. Peut en redemander

---

## Validations et sécurité

### Côté Agent

✅ **Validations implémentées:**

- Dates en mois courant (date input limité)
- Pas de doublons (vérification JS + serveur)
- Pas de date déjà approuvée (serveur)
- Pas de date en attente (serveur)
- Max jours = restants (serveur)

### Côté Admin

✅ **Validations implémentées:**

- Jours suffisants (serveur)
- Pas de conflits (serveur)
- Dates en mois courant (serveur)
- Motif min 5 caractères (serveur)
- Permissions admin (middleware)

---

## Foire aux questions

**Q: Où voir mon quota annuel?**
A: En haut de la page, colonne "Total disponible" = 2 × mois contrat

**Q: Puis-je demander pour le mois prochain?**
A: Non, demandes seulement pour le mois courant

**Q: Comment ajouter plusieurs dates?**
A: Sélectionnez une date, cliquez "+Ajouter", répétez

**Q: Les jours s'accumulent?**
A: Oui, sur la durée entière du contrat (pas de réinitialisation)

**Q: Peux-je modifier ma demande?**
A: Pas encore, contactez l'admin pour rejeter/redemander

**Q: Notification email?**
A: À implémenter (pas encore)

---

## Support

Pour tout problème, contactez l'administrateur via:

- Admin: `/admin/leave-requests`
