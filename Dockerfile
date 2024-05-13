FROM webdevops/php-nginx:8.3-alpine

# Installation dans votre Image du minimum pour que Docker fonctionne
# Install additional dependencies
RUN apk add oniguruma-dev libxml2-dev \
    && docker-php-ext-install \
        bcmath \
        ctype \
        fileinfo \
        mbstring \
        pdo_mysql \
        xml \
    && apk add nodejs npm

# Installation dans votre image de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installation dans votre image de NodeJS
RUN apk add nodejs npm

ENV WEB_DOCUMENT_ROOT /app/public
# ENV APP_ENV local
WORKDIR /app
COPY . .

# Copy environment file
COPY .env /app/.env

# Install Composer dependencies and optimize autoloader
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Generate application key and cache configuration
RUN php artisan key:generate \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan migrate \
    && composer require laravel/horizon

# Expose port 80
RUN chown -R application:application .

# Install Supervisor
RUN apk --no-cache add supervisor

# Copy Supervisor configuration file
COPY supervisord.conf /etc/supervisor/supervisord.conf

# Set entrypoint to start Supervisor
ENTRYPOINT ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]

# Copy entrypoint script
# COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# Set execute permissions for entrypoint script
# RUN chmod +x /usr/local/bin/entrypoint.sh

# Set entrypoint
# ENTRYPOINT ["entrypoint.sh"]