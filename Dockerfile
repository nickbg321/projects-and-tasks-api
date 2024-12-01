FROM dunglas/frankenphp:1.3.3-php8.2.26

ARG UID=1000
ARG GID=1000
ARG USER=app

ENV SERVER_NAME=projects-and-tasks.local

# Install Composer
COPY --from=composer:2.8.3 /usr/bin/composer /usr/local/bin/composer

# Create dedicated app user instead of using root directly
RUN \
  groupadd -g "${GID}" app; \
  useradd --create-home --no-log-init -u "${UID}" -g "${GID}" app; \
  setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
  chown -R ${USER}:${USER} /data/caddy && chown -R ${USER}:${USER} /config/caddy

# Install extensions
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
    bcmath \
    xdebug # move this to dev env only

# Copy PHP config
COPY ./docker/app/etc/php.ini /usr/local/etc/php/php.ini
COPY ./docker/app/etc/docker-php-ext-opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# xdebug config should be copied only on dev env
COPY ./docker/app/etc/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install additional software, should only be ran on dev env
RUN apt-get update && apt-get install -y \
    git \
    vim \
    htop

USER ${USER}
