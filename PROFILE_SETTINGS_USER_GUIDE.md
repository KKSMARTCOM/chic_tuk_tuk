# Guide d'Utilisation - Pages Profil et Paramètres

## 🎯 Accès aux Pages

### Option 1: Directement par URL

- Page Profil: `http://votre-domaine/profile`
- Page Paramètres: `http://votre-domaine/settings`

### Option 2: Par le menu Header

1. Connectez-vous avec un compte (admin, chauffeur ou client)
2. Cliquez sur votre avatar en haut à droite
3. Sélectionnez "Mon profil" → va à `/profile`
4. Depuis la page profil, cliquez sur "Modifier le profil" → va à `/settings`

## 📖 Fonctionnalités Détaillées

### 1️⃣ Page Profil (`/profile`)

#### Affichage

- **Photo de profil**: Affichage de votre photo personnalisée ou avatar par défaut
- **Nom complet**: Votre nom complet
- **Rôle**: Administrateur, Chauffeur ou Client (avec badge coloré)
- **Statut**: Actif ✅ ou Inactif ❌
- **Email**: Votre adresse email
- **Téléphone**: Votre numéro de téléphone
- **Adresse**: Votre adresse personnelle
- **Membre depuis**: Date de création du compte

#### Actions

- Cliquez sur "Modifier le profil" pour aller à la page des paramètres

---

### 2️⃣ Page Paramètres (`/settings`)

#### Section 1: Informations Personnelles

```
📝 Modifier vos infos personnelles
```

- **Nom complet** (requis)
- **Email** (requis, doit être unique)
- **Téléphone** (requis, doit être unique)
- **Adresse** (optionnel)

**Validations**:

- Email: Format valide et unique
- Téléphone: Unique dans le système
- Tous les champs requis

---

#### Section 2: Photo de Profil

```
🖼️ Changer votre photo de profil
```

- **Aperçu**: Visualisez votre photo actuelle
- **Upload**: Glissez-déposez ou cliquez pour sélectionner une nouvelle photo
    - Formats acceptés: JPEG, PNG, JPG, GIF
    - Taille maximale: 2 MB
    - La nouvelle photo remplace l'ancienne (l'ancienne est supprimée)

**Astuces**:

- Drag & drop fonctionne
- Le prévisualisation s'affiche avant d'enregistrer
- Les photos anciennes sont automatiquement supprimées

---

#### Section 3: Préférences de Notification

```
🔔 Gérer vos notifications
```

**Notifications par Email** ✉️

- Recevez des emails pour les mises à jour importantes
- Activé/Désactivé via checkbox

**Notifications Push Web** 🔊

- Recevez des notifications en temps réel dans votre navigateur
- Nécessite une première autorisation du navigateur
- Activé/Désactivé via checkbox

**Note**: Une fois activées, les notifications pop-up peuvent être refusées au niveau du navigateur

---

#### Section 4: Sécurité - Modifier le Mot de Passe

```
🔐 Changer votre mot de passe
```

**Champs**:

1. **Mot de passe actuel** (requis) - Pour vérifier que c'est bien vous
2. **Nouveau mot de passe** (requis) - Minimum 8 caractères
3. **Confirmer le mot de passe** (requis) - Doit correspondre au nouveau

**Validations**:

- Mot de passe actuel doit être correct
- Nouveau mot de passe doit faire au minimum 8 caractères
- Les deux nouveaux mots de passe doivent correspondre

**Sécurité**:

- Votre mot de passe est hashé avant stockage
- Ne partagez jamais votre mot de passe

---

## 💾 Enregistrement des Modifications

Chaque section a son propre bouton "Enregistrer":

- 💚 **Vert**: Informations personnelles
- 🔵 **Bleu**: Photo de profil
- 🟡 **Jaune**: Notifications
- ❤️ **Rouge**: Mot de passe

### ✅ Message de Succès

```
✓ [Message de succès]
```

Apparaît en haut de la page après chaque action réussie

### ❌ Message d'Erreur

```
✗ [Message d'erreur avec détails]
```

Affiche les erreurs de validation directement sous les champs concernés

---

## 🔧 Cas d'Utilisation Courants

### Cas 1: Changer ma photo de profil

1. Aller à `/settings`
2. Scroller à la section "Photo de profil"
3. Cliquer sur la zone de dépôt ou drag-drop une image
4. Cliquer "Mettre à jour la photo"
5. Message de succès ✅

### Cas 2: Mettre à jour mon email

1. Aller à `/settings`
2. Modifier le champ "Email" dans "Informations personnelles"
3. Cliquer "Enregistrer les modifications"
4. Message de succès ✅

### Cas 3: Changer mon mot de passe

1. Aller à `/settings`
2. Scroller à la section "Sécurité"
3. Entrer votre mot de passe actuel
4. Entrer votre nouveau mot de passe (min 8 caractères)
5. Confirmer le nouveau mot de passe
6. Cliquer "Modifier le mot de passe"
7. Message de succès ✅

### Cas 4: Activer les notifications

1. Aller à `/settings`
2. Scroller à la section "Notifications"
3. Cocher "Notifications par email" et/ou "Notifications push web"
4. Cliquer "Enregistrer les préférences"
5. Message de succès ✅

---

## ⚠️ Points Importants

### Restrictions

- ❌ Vous **ne pouvez pas** modifier les informations de votre contrat
- ❌ Vous **ne pouvez pas** modifier les informations de votre véhicule (pour les chauffeurs)
- ❌ Vous **ne pouvez pas** changer votre rôle (admin, chauffeur, client)

### Validation des Données

- L'email doit être valide et unique dans le système
- Le téléphone doit être unique dans le système
- Tous les champs marqués "requis" doivent être remplis
- Les formats de fichiers images doivent être acceptés

### Sauvegardes

- Toutes les modifications sont enregistrées immédiatement en base de données
- Pas de brouillon auto-sauvegardé
- Les anciennes photos sont supprimées automatiquement

---

## 🎨 Design et Navigation

### Barre Latérale (Desktop)

```
- Informations personnelles  [✓ Actif]
- Photo de profil            [Cliquez pour naviguer]
- Notifications              [Cliquez pour naviguer]
- Sécurité                   [Cliquez pour naviguer]
```

### Mobile

Les sections s'empilent verticalement et peuvent être atteintes par scroll

### Couleurs

- 🟢 **Vert**: Actions positives et section active
- 🔵 **Bleu**: Sections informations
- 🟡 **Jaune**: Notifications
- ❤️ **Rouge**: Sécurité et actions sensibles

---

## 📞 Troubleshooting

### Problème: Ma photo ne s'affiche pas

**Solution**: Vérifiez que:

- Le fichier fait moins de 2 MB
- Le format est JPEG, PNG, JPG ou GIF
- Votre navigateur cache n'est pas le problème (Ctrl+F5)

### Problème: "Email déjà utilisé"

**Solution**: Cet email est déjà utilisé par un autre compte. Utilisez un autre email ou contactez l'admin.

### Problème: "Mot de passe incorrect"

**Solution**: Vérifiez que vous entrez le bon mot de passe actuel (attention aux majuscules/minuscules).

### Problème: Les notifications ne s'affichent pas

**Solution**:

- Vérifiez que vous avez autorisé les notifications dans le navigateur
- Vérifiez les paramètres de notification de votre système d'exploitation

### Problème: Erreur 404 en accédant aux pages

**Solution**:

- Assurez-vous d'être connecté
- Rafraîchissez la page (Ctrl+F5)
- Vérifiez que les routes sont bien enregistrées: `php artisan route:list`

---

## 🔗 Routes Disponibles

| Méthode | Chemin                    | Nom Route                | Description                |
| ------- | ------------------------- | ------------------------ | -------------------------- |
| GET     | `/profile`                | `profile`                | Voir mon profil            |
| GET     | `/settings`               | `settings.settings`      | Gérer les paramètres       |
| POST    | `/profile/update`         | `profile.update`         | Mettre à jour le profil    |
| POST    | `/settings/notifications` | `settings.notifications` | Modifier les notifications |
| POST    | `/settings/password`      | `settings.password`      | Changer le mot de passe    |

---

## 💡 Conseils et Bonnes Pratiques

1. **Sécurité du mot de passe**
    - Utilisez un mot de passe fort (mixture de majuscules, minuscules, chiffres, caractères spéciaux)
    - Évitez les mots du dictionnaire
    - N'utilisez pas le même mot de passe sur plusieurs sites

2. **Photo de profil**
    - Utilisez une photo claire et récente
    - Taille carré pour un meilleur rendu
    - Pas d'informations sensibles en arrière-plan

3. **Notifications**
    - Gardez les activées pour ne pas manquer les mises à jour importantes
    - Vous pouvez les désactiver si vous recevez trop de notifications

4. **Email**
    - Utilisez un email que vous contrôlez toujours
    - Assurez-vous que vous recevez les confirmations

---

Besoin d'aide? Contactez l'administrateur système!
