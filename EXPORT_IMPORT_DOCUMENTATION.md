# Fonctionnalité d'Export et Import de Conducteurs

## 📋 Résumé

La fonctionnalité d'export et import permet de gérer les conducteurs en masse via des fichiers Excel ou CSV.

## 🚀 Fonctionnalités

### 1. **Export en Excel**

- Exporte la liste des conducteurs filtrée
- Formats supportés : XLSX
- Inclut tous les champs pertinents
- Mise en forme automatique (en-têtes colorés)

### 2. **Import en masse**

- Importe les conducteurs depuis un fichier Excel ou CSV
- Validation automatique des données
- Gestion des erreurs avec rapport détaillé
- Crée automatiquement les comptes utilisateur et profils driver

### 3. **Template téléchargeable**

- Fichier template avec exemples de données
- Colonnes pré-configurées avec les bonnes en-têtes
- Téléchargement direct depuis l'interface

## 📁 Fichiers Créés

```
app/
├── Exports/
│   └── DriversExport.php          # Classe d'export Excel
├── Imports/
│   └── DriversImport.php          # Classe d'import Excel
└── Http/
    └── Controllers/
        └── Admin/
            └── DriverController.php # Méthodes ajoutées

resources/views/pages/admin/drivers/
└── import.blade.php               # Vue d'import

routes/
└── admin.php                       # Routes d'export/import
```

## 🎯 Colonnes Requises pour l'Import

| Colonne         | Type  | Obligatoire | Notes                       |
| --------------- | ----- | ----------- | --------------------------- |
| nom             | texte | ✓           | Nom du conducteur           |
| email           | email | ✗           | Email valide                |
| telephone       | texte | ✓           | Doit être unique            |
| adresse         | texte | ✗           | Adresse du conducteur       |
| numero_permis   | texte | ✓           | Doit être unique            |
| numero_vehicule | texte | ✓           | Immatriculation du véhicule |
| type_vehicule   | texte | ✓           | moto, tricycle ou car       |
| disponible      | texte | ✗           | Oui ou Non                  |
| actif           | texte | ✗           | Oui ou Non                  |

## 🔗 Routes

```php
GET    /admin/drivers/export/excel          # Export en Excel
GET    /admin/drivers/import/form           # Formulaire d'import
POST   /admin/drivers/import                # Traiter l'import
GET    /admin/drivers/template/download     # Télécharger le template
```

## 🛠️ Utilisation

### Export

1. Allez sur la page de gestion des conducteurs
2. Cliquez sur le menu déroulant "Importer/Exporter"
3. Cliquez sur "Exporter en Excel"
4. Le fichier sera téléchargé automatiquement

### Import

1. Allez sur la page de gestion des conducteurs
2. Cliquez sur le menu déroulant "Importer/Exporter"
3. Cliquez sur "Importer des Conducteurs"
4. Téléchargez le template ou utilisez votre fichier
5. Remplissez les données
6. Sélectionnez le fichier et cliquez "Importer"

### Template

1. Cliquez sur "Télécharger le Template"
2. Un fichier Excel avec les en-têtes corrects sera téléchargé
3. Remplissez avec vos données
4. Utilisez ce fichier pour l'import

## ✨ Fonctionnalités Avancées

- **Validation automatique** : Chaque ligne est validée avant l'import
- **Rapport d'erreurs** : Les erreurs d'import sont affichées avec détails
- **Drag & Drop** : Interface de glisser-déposer pour les fichiers
- **Filtres appliqués** : L'export respecte les filtres de recherche actuels
- **Mot de passe temporaire** : Généré automatiquement à partir du téléphone

## 📊 Données Exportées

- ID utilisateur
- Nom
- Email
- Téléphone
- Adresse
- Numéro de permis
- Numéro de véhicule
- Type de véhicule
- Disponibilité
- Statut actif/inactif
- Nombre de courses
- Date de création
