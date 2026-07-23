#!/bin/sh
# docker/start.sh
set -e

echo "==> Démarrage de l'application chictuktuk..."

# Attendre que la base de données soit prête
echo "==> Vérification de la connexion à la base de données..."
RETRIES=30
until php -r "
    \$dsn = 'pgsql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 5432) . ';dbname=' . getenv('DB_DATABASE');
    try {
        new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null || [ $RETRIES -eq 0 ]; do
    echo "Base de données non disponible, attente 2s... ($RETRIES restants)"
    sleep 2
    RETRIES=$((RETRIES - 1))
done

if [ $RETRIES -eq 0 ]; then
    echo "❌ Base de données inaccessible après 60s. Abandon."
    exit 1
fi
echo "==> Base de données disponible !"

# Migrations
echo "==> Exécution des migrations..."
php /var/www/html/artisan migrate --force

# Nettoyage + optimisation
echo "==> Optimisation..."
php /var/www/html/artisan config:clear
php /var/www/html/artisan route:clear
php /var/www/html/artisan view:clear
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Storage link
php /var/www/html/artisan storage:link --force 2>/dev/null || true

# Permissions (au cas où les volumes montent les fichiers avec de mauvais droits)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "==> Lancement des services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf