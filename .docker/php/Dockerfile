FROM php:8.2-fpm-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN set -ex \
  && apk --no-cache add \
    postgresql-dev

RUN set -eux; \
    install-php-extensions pdo pdo_pgsql;

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:2.2.6 /usr/bin/composer /usr/bin/composer

COPY ./composer.* .

RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

COPY ./ .

RUN composer dump-autoload --optimize

ENV XDEBUG_MODE=off

COPY ./.docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN set -eux; \
    install-php-extensions xdebug