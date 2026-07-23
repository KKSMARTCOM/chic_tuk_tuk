#!/bin/sh
set -e

echo "==> Démarrage de l'application chictuktuk..."

# Attendre que la base de données soit prête
echo "==> Vérification de la connexion à la base de données..."
until php /var/www/html/artisan db:monitor --databases=pgsql 2>/dev/null; do
    echo "Base de données non disponible, attente 2s..."
    sleep 2
done
echo "==> Base de données disponible !"

# Migrations
echo "==> Exécution des migrations..."
php /var/www/html/artisan migrate --force

# Nettoyage des caches au démarrage
echo "==> Optimisation..."
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Créer le lien storage si nécessaire
php /var/www/html/artisan storage:link --force 2>/dev/null || true

echo "==> Lancement des services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf