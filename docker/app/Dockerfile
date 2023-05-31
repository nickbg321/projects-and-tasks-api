FROM php:8.2-fpm

ARG UID=1000
ARG GID=1000

# Install extensions
RUN apt-get update && apt-get install -y \
    git \
    vim \
    htop \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zip  \
    libzip-dev \
    libicu-dev \
    libbz2-dev \
    libxml2-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql zip intl bcmath bz2 soap opcache \
    && pecl install xdebug \
    && pecl install redis \
    && pecl install apcu \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-enable redis \
    && docker-php-ext-enable apcu

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create dedicated app user instead of using root directly
RUN groupadd -g "${GID}" app \
  && useradd --create-home --no-log-init -u "${UID}" -g "${GID}" app

USER app

WORKDIR /var/www/app
