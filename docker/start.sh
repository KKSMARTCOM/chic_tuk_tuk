#!/bin/sh
set -e

echo "==> Démarrage de l'application ChicTukTuk..."

# Attendre que la base de données soit accessible (nc = netcat)
echo "==> Vérification de la connexion à la base de données..."
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
RETRIES=40

echo "==> Connexion vers ${DB_HOST}:${DB_PORT}..."
until nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null; do
    if [ $RETRIES -eq 0 ]; then
        echo "❌ Base de données inaccessible après 80s. Abandon."
        exit 1
    fi
    echo "En attente de ${DB_HOST}:${DB_PORT}... ($RETRIES restants)"
    sleep 2
    RETRIES=$((RETRIES - 1))
done
echo "==> Base de données disponible !"

# Migrations
echo "==> Exécution des migrations..."
php /var/www/html/artisan migrate --force

# Vider les anciens caches avant de reconstruire
echo "==> Optimisation..."
php /var/www/html/artisan config:clear
php /var/www/html/artisan route:clear
php /var/www/html/artisan view:clear
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Storage link
php /var/www/html/artisan storage:link --force 2>/dev/null || true

# Permissions (sécurité si volume monté)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "==> Lancement des services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf