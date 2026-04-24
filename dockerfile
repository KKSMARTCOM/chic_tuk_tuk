FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libpq-dev \
    cron \
    nginx \
    supervisor \
    nodejs npm \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd pdo pdo_pgsql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy files
COPY . .

# Install PHP deps
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Build frontend (si Vite)
RUN npm install && npm run build

# Laravel optimizations
RUN php artisan config:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy configs
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/cron /etc/cron.d/laravel

# Setup cron
RUN chmod 0644 /etc/cron.d/laravel \
    && crontab /etc/cron.d/laravel

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n"]