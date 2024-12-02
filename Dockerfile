FROM dunglas/frankenphp:1.3.3-php8.2.26 AS base

ARG UID=1000
ARG GID=1000
ARG USER=app

ENV SERVER_NAME=projects-and-tasks.local

# Install Composer
COPY --from=composer:2.8.3 /usr/bin/composer /usr/local/bin/composer

# Setup dedicated user for the container
RUN \
  groupadd -g "${GID}" app; \
  useradd --create-home --no-log-init -u "${UID}" -g "${GID}" app; \
  setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
  chown -R ${USER}:${USER} /data/caddy && chown -R ${USER}:${USER} /config/caddy

# Install and configure PHP extensions
RUN install-php-extensions \
    mbstring \
    opcache \
    apcu \
    pdo_pgsql \
    redis \
    amqp \
    intl \
    zip \
    gd \
    bcmath

COPY ./docker/app/etc/php.ini /usr/local/etc/php/php.ini
COPY ./docker/app/etc/docker-php-ext-opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

FROM base AS prod

# Enable PHP production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy project source files to image
COPY . /app

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
RUN composer dump-env prod

USER ${USER}

FROM base AS dev

# Install and configure dev environment specific PHP extensions
RUN install-php-extensions \
    xdebug

COPY ./docker/app/etc/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install additional software for dev environment
RUN apt-get update && apt-get install -y \
    git \
    vim \
    htop

USER ${USER}
