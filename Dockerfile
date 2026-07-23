# ================================
# Stage 1 : Build des assets
# ================================
FROM php:8.2-fpm-alpine AS builder

RUN apk add --no-cache \
    git curl zip unzip \
    nodejs npm \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    libzip-dev oniguruma-dev postgresql-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo pdo_pgsql pgsql \
        mbstring exif pcntl bcmath gd zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Dépendances PHP d'abord (cache layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Dépendances JS (cache layer)
COPY package.json package-lock.json* ./
RUN npm ci

# Copier le reste et builder
COPY . .
RUN npm run build

# Optimiser Laravel (sans APP_KEY ici, les vraies valeurs viennent des env Coolify)
RUN composer dump-autoload --optimize --no-dev 2>/dev/null || true

# ================================
# Stage 2 : Image de production
# ================================
FROM php:8.2-fpm-alpine AS production

# Dépendances runtime
RUN apk add --no-cache \
    nginx supervisor curl \
    libpng libjpeg-turbo libwebp freetype \
    libzip oniguruma postgresql-libs

# Extensions PHP (recompile dans ce stage)
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    libzip-dev oniguruma-dev postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo pdo_pgsql pgsql \
        mbstring exif pcntl bcmath gd zip opcache \
    && apk del .build-deps

# OPcache
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.validate_timestamps=0"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# PHP-FPM : écouter sur 127.0.0.1:9000
RUN sed -i 's|listen = /var/run/php-fpm.sock|listen = 127.0.0.1:9000|' \
    /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true

WORKDIR /var/www/html

# Copier l'application depuis le builder
COPY --from=builder /app .

# Configs Docker
COPY docker/nginx.conf       /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh         /start.sh
RUN chmod +x /start.sh

# Créer les répertoires temporaires nginx accessibles par www-data
RUN mkdir -p /tmp/client_body /tmp/proxy /tmp/fastcgi /tmp/uwsgi /tmp/scgi \
    && chown -R www-data:www-data \
        /tmp/client_body /tmp/proxy /tmp/fastcgi /tmp/uwsgi /tmp/scgi \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache \
    && chmod -R 775 \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/start.sh"]