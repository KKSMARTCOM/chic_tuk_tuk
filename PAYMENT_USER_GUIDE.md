# Guide d'Utilisation - Gestion des Commissions et Paiements

## 📋 Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Module Commissions](#module-commissions)
3. [Module Paiements](#module-paiements)
4. [Suivi des Soldes](#suivi-des-soldes)
5. [Exemples Pratiques](#exemples-pratiques)

---

## Vue d'ensemble

Le système a été séparé en deux modules distincts :

### **Module Commissions** 📝

- Affiche toutes les commissions dues par chaque course
- Vue statique des revenus générés
- Pas d'actions de paiement directes

### **Module Paiements** 💰

- Enregistrement des paiements effectués
- Suivi du solde dû par agent
- Historique complet des transactions

---

## Module Commissions

### Accéder aux Commissions

1. Allez dans **Admin > Gestion des Commissions**
2. Vous verrez l'écran avec les statistiques globales

### Statistiques Affichées

- **Revenu Total** : Somme de toutes les commissions
- **Total Commissions** : Nombre total de commissions enregistrées
- **Gestion des Paiements** : Raccourci vers le module de paiement

### Actions Disponibles

#### Rechercher et Filtrer

- **Recherche** : Filtrez par nom d'agent ou numéro de course
- **Réinitialiser** : Effacez tous les filtres

#### Voir les Détails

- Cliquez sur l'icône **👁️ (oeil)** pour voir les détails complets d'une commission

### Détails d'une Commission

En cliquant sur une commission, vous verrez :

- **Revenue Agent** : Ce que l'agent a gagné sur cette course
- **Montant** : La commission dus à l'agent
- **Date** : Quand la commission a été créée

**Actions disponibles** :

- **Gérer Paiements** : Allez à la page de paiement pour cet agent
- **Voir Agent** : Affiche le profil complet du conducteur
- **Voir Course** : Affiche les détails de la réservation

---

## Module Paiements

### Accéder aux Paiements

1. Allez dans **Admin > Gestion des Paiements**
2. Ou cliquez sur **Gérer Paiements** depuis n'importe quelle commission

### Tableau de Bord - Statistiques

#### Total Payé (Vert) 💚

La somme totale de tous les paiements enregistrés dans le système

#### Total Dû (Rouge) ❤️

La somme totale de toutes les commissions (somme des commissions dues)

#### Solde Dû (Orange) 🧡

**Solde Dû = Total Dû - Total Payé**

C'est le montant qui reste à verser aux agents

#### Payé Ce Mois (Bleu) 💙

Les paiements enregistrés pendant le mois en cours

### Filtrer les Paiements

| Filtre                | Description                               |
| --------------------- | ----------------------------------------- |
| **Agent**             | Sélectionnez un agent spécifique          |
| **Moyen de Paiement** | Espèces, Virement, Chèque, Mobile Money   |
| **Recherche**         | Recherchez par nom ou numéro de référence |
| **Du / Au**           | Filtrez par plage de dates                |

### Enregistrer un Paiement

#### Étape 1 : Cliquer sur "Nouveau Paiement"

![Bouton Nouveau Paiement]
Situé en haut à droite

#### Étape 2 : Remplir le Formulaire

| Champ                   | Description                        | Obligatoire |
| ----------------------- | ---------------------------------- | ----------- |
| **Agent**               | Sélectionnez l'agent destinataire  | ✅          |
| **Montant**             | Le montant payé en FCFA            | ✅          |
| **Moyen de Paiement**   | Comment le paiement a été effectué | ✅          |
| **Date de Paiement**    | La date effective du paiement      | ✅          |
| **Numéro de Référence** | N° de reçu, de chèque, etc.        | ❌          |
| **Notes**               | Informations supplémentaires       | ❌          |

#### Étape 3 : Confirmer

Cliquez sur **Enregistrer** pour valider le paiement

### Moyens de Paiement Disponibles

- 💵 **Espèces** - Paiement en cash
- 🏦 **Virement Bancaire** - Transfert bancaire
- 📄 **Chèque** - Paiement par chèque
- 📱 **Mobile Money** - Via service de paiement mobile
- 🔄 **Autre** - Autres méthodes

### Afficher les Détails d'un Paiement

1. Cliquez sur l'icône **👁️ (oeil)** dans le tableau
2. Vous verrez :
    - Les informations du paiement
    - Le conducteur destinataire
    - **Résumé de l'Agent** (colonne droite)

### Résumé de l'Agent

Dans la vue détails d'un paiement, vous verrez :

- **Total Dû** : Toutes les commissions de cet agent
- **Total Payé** : Tous les paiements reçus de cet agent
- **Solde Dû** : Ce qui reste à payer
- **Nombre de paiements** : Combien de paiements ont été enregistrés
- **Nombre de commissions** : Combien de courses l'agent a effectuées

### Modifier un Paiement

1. Cliquez sur l'icône **✏️ (éditer)** dans le tableau
2. Modifiez les informations
3. Cliquez **Mettre à jour**

### Supprimer un Paiement

1. Cliquez sur l'icône **🗑️ (poubelle)**
2. Confirmez la suppression
3. Le paiement est retiré et la commission redevient due

---

## Suivi des Soldes

### Vue Générale

**Admin > Gestion des Paiements**

- Visualisez en un coup d'œil les statistiques globales
- Identifiez immédiatement le montant total dû

### Par Conducteur

**Admin > Gestion des Paiements > [Détails du Conducteur]**

- Voir tous les paiements d'un agent
- Voir toutes les commissions dues
- Calculer automatiquement le solde

### Calcul du Solde

```
SOLDE DÛ = TOTAL DES COMMISSIONS - TOTAL DES PAIEMENTS
```

**Exemples:**

- Commissions: 500 000 FCFA, Paiements: 300 000 FCFA → **Solde: 200 000 FCFA**
- Commissions: 500 000 FCFA, Paiements: 500 000 FCFA → **Solde: 0 FCFA** (Payé)
- Commissions: 500 000 FCFA, Paiements: 550 000 FCFA → **Solde: -50 000 FCFA** (Acompte)

---

## Exemples Pratiques

### Exemple 1 : Paiement Unique

**Situation :**

- Agent: Kwame Mensah
- Commission sur courses: 750 000 FCFA
- L'agent se présente et reçoit un paiement en espèces

**Action :**

1. Allez dans **Gestion des Paiements**
2. Cliquez **Nouveau Paiement**
3. Sélectionnez: Kwame Mensah
4. Montant: 750000
5. Moyen: Espèces
6. Date: Aujourd'hui
7. Référence: RC001 (optionnel)
8. Cliquez **Enregistrer**

**Résultat :**

- Total Payé: 750 000 FCFA ✅
- Solde Dû: 0 FCFA

---

### Exemple 2 : Paiements Partiels

**Situation :**

- Agent: Ama Boateng
- Commission totale: 1 200 000 FCFA
- Paiement 1 (Semaine 1): 400 000 FCFA en espèces
- Paiement 2 (Semaine 2): 500 000 FCFA par virement

**Action :**

_Paiement 1:_

1. Nouveau Paiement > Ama Boateng
2. Montant: 400000, Méthode: Espèces
3. Enregistrer

_Paiement 2:_

1. Nouveau Paiement > Ama Boateng
2. Montant: 500000, Méthode: Virement Bancaire
3. Référence: VIREMENT001
4. Enregistrer

**Résultat :**

- Commission: 1 200 000 FCFA
- Paiements: 900 000 FCFA (400 + 500)
- **Solde Dû: 300 000 FCFA** ⚠️

---

### Exemple 3 : Suivi par Agent

**Pour voir les détails complets d'un agent:**

1. Allez dans **Gestion des Paiements**
2. Trouvez un paiement du conducteur
3. Cliquez **Voir** > **Voir les détails de paiement complets**
4. Vous verrez:
    - Tous ses paiements enregistrés
    - Toutes ses commissions dues
    - Son solde dû en temps réel

---

## 🔑 Points Clés à Retenir

✅ **Commissions** = Revenus générés par les courses
✅ **Paiements** = Argent effectivement versé
✅ **Solde Dû** = Ce qui reste à payer

⚠️ Les commissions sont créées **automatiquement**
⚠️ Les paiements doivent être enregistrés **manuellement**
⚠️ Les paiements peuvent être **partiels** ou **échelonnés**

---

## ❓ Questions Fréquentes

**Q: Un paiement doit-il correspondre à une seule commission?**
A: Non! Un paiement peut couvrir plusieurs commissions ou être un paiement partiel.

**Q: Que se passe-t-il si je supprime un paiement?**
A: Le montant est retiré et le solde dû augmente automatiquement.

**Q: Comment puis-je voir l'historique complet d'un agent?**
A: Allez dans Détails de Paiement de cet agent, vous verrez tous ses paiements et commissions.

**Q: Les références de paiement peuvent-elles être doublées?**
A: Non, chaque référence est unique pour éviter les doublons.

**Q: Quel est le meilleur moyen de paiement à enregistrer?**
A: Enregistrez la **méthode réelle** utilisée (espèces, chèque, virement, etc.) pour la transparence.

---

_Dernière mise à jour: 21 mai 2026_
