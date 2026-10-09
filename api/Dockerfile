# Image de production de l'API : FrankenPHP (PHP 8.4 + serveur web Caddy).
FROM dunglas/frankenphp:1-php8.4

RUN install-php-extensions pdo_sqlite intl opcache zip

# L'image donne au binaire frankenphp le droit d'écouter sur les ports < 1024.
# Render (comme beaucoup d'hébergeurs) interdit ce type de permission et refuse
# alors de lancer le serveur. On écoute sur un port > 1024, on la retire donc.
RUN setcap -r /usr/local/bin/frankenphp

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APP_ENV=prod \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PORT=8080

WORKDIR /app

# Les dépendances d'abord : cette couche reste en cache tant que
# composer.json et composer.lock ne changent pas.
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress

COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative \
    && mkdir -p var config/jwt

COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
