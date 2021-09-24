FROM php:7.4-cli

# get apt-get lists for the first time
RUN apt-get update

## install zip extension using debian buster repo (which is now available)
## we need zip-1.14 or higher and libzip 1.2 or higher for ZIP encryption support
RUN apt-get update && apt-get install -y zlib1g-dev libzip-dev \
    && pecl install zip \
    # && docker-php-ext-configure zip --with-libzip \ # not required for PHP 7.4+
    && docker-php-ext-install zip

## configure and install php-intl extension (and dependencies)
## also needs zlib1g-dev previously installed
RUN apt-get update && apt-get install -y libicu-dev \
    && docker-php-ext-install intl

# install some php extensions
RUN docker-php-ext-install pdo pdo_mysql opcache bcmath

# install gmp
RUN apt-get -y install libgmp-dev && \
  docker-php-ext-install gmp

# install calendar (for usage of holiday determination functions)
RUN docker-php-ext-install calendar

#
# install libmemcached and the php extension
#
RUN apt-get update && apt-get install -y \
    libz-dev \
    libmemcached-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    curl

RUN pecl install memcached-3.1.5 \
    && docker-php-ext-enable memcached

# install ssh2 ext and deps
# see https://github.com/docker-library/php/issues/767
RUN apt-get update \
  && apt-get install -y libssh2-1-dev libssh2-1 \
  && pecl install ssh2-1.3.1 \
  && docker-php-ext-enable ssh2

# RUN  pecl install xdebug \
#   && docker-php-ext-enable xdebug

RUN  pecl install pcov-1.0.9 \
  && docker-php-ext-enable pcov

# Programmatically install composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
