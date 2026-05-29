# Pages Profil et Paramètres

## Pages Créées

### 1. Page Profil (`/profile`)

- **Route**: `GET /profile` (nommée `profile`)
- **Contrôleur**: `SettingsController@profile`
- **Vue**: `pages/settings/profile.blade.php`
- **Description**: Affiche les informations complètes du profil utilisateur de manière consultative

#### Fonctionnalités:

- Affichage de la photo de profil
- Affichage des informations personnelles (nom, email, téléphone, adresse)
- Affichage du rôle et du statut (actif/inactif)
- Date d'adhésion
- Bouton pour accéder à la page de modification

### 2. Page Paramètres (`/settings`)

- **Route**: `GET /settings` (nommée `settings.settings`)
- **Contrôleur**: `SettingsController@settings`
- **Vue**: `pages/settings/index.blade.php`
- **Description**: Page complète de gestion des paramètres utilisateur

#### Sections:

1. **Informations Personnelles**
    - Modification du nom complet
    - Modification de l'email
    - Modification du téléphone
    - Modification de l'adresse
    - Validation en temps réel

2. **Photo de Profil**
    - Aperçu de la photo actuelle
    - Upload de nouvelle photo avec drag & drop
    - Validation (max 2MB, formats: JPEG, PNG, JPG, GIF)

3. **Préférences de Notification**
    - Activer/Désactiver notifications par email
    - Activer/Désactiver notifications push web
    - Stockage en base de données (colonne JSON)

4. **Sécurité - Modification du Mot de Passe**
    - Vérification du mot de passe actuel
    - Nouvelle password avec confirmation
    - Minimum 8 caractères

## Routes POST (Actions)

### Mise à jour du Profil

- **Route**: `POST /profile/update` (nommée `profile.update`)
- **Contrôleur**: `SettingsController@updateProfile`
- **Paramètres**:
    - `name` (requis)
    - `email` (requis, unique)
    - `phone` (requis, unique)
    - `adresse` (optionnel)
    - `profile_photo` (optionnel, image max 2MB)

### Mise à jour des Paramètres de Notification

- **Route**: `POST /settings/notifications` (nommée `settings.notifications`)
- **Contrôleur**: `SettingsController@updateNotificationSettings`
- **Paramètres**:
    - `email_notifications` (boolean)
    - `push_notifications` (boolean)

### Modification du Mot de Passe

- **Route**: `POST /settings/password` (nommée `settings.password`)
- **Contrôleur**: `SettingsController@changePassword`
- **Paramètres**:
    - `current_password` (requis)
    - `password` (requis, min 8 caractères)
    - `password_confirmation` (requis)

## Modifications au Modèle User

- Ajout de la colonne `notification_preferences` (JSON)
- Ajout dans les `fillable`: `notification_preferences`
- Ajout dans les `casts`: `notification_preferences` → `array`

## Migration

- Fichier: `database/migrations/2026_05_13_132631_add_notification_preferences_to_users_table.php`
- Ajoute la colonne `notification_preferences` à la table `users`

## Lien dans le Header

Le bouton "Mon profil" dans le header (`inc/backend/header.blade.php`) pointe maintenant vers `route('profile')`

## Design et UX

- Design moderne avec Tailwind CSS
- Dégradés de couleurs pour chaque section
- Navigation latérale dans la page des paramètres
- Animations fluides et transitions
- Messages de succès/erreur
- Validation côté serveur
- Drag & drop pour les uploads d'images
- Preview d'image avant upload
- Responsive design (mobile-first)

## Sécurité

- Authentification requise (middleware `auth:admin,driver,client`)
- Validation stricte des entrées
- Protection CSRF avec `@csrf`
- Vérification des mots de passe forts
- Stockage sécurisé des photos via Laravel Storage
- Suppression des anciennes photos lors de la mise à jour

## Notes

- Les utilisateurs ne peuvent modifier que leurs propres informations
- Les informations de contrat et de véhicule pour les chauffeurs ne sont pas modifiables via ces pages
- Les notifications push web nécessitent une configuration supplémentaire du Service Worker
- Les photos sont stockées dans `storage/app/public/profile-photos/`
