FROM php:7-fpm
COPY --from=composer:2.2.18 /usr/bin/composer /usr/local/bin/composer
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        git \
        chromium \
    && docker-php-ext-install -j$(nproc) pgsql zip pdo_pgsql
SHELL ["/bin/bash", "-c"]
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - &&\
    apt-get install -y nodejs

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV CHROME_PATH=/usr/bin/chromium
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

VOLUME /code/var/cache
VOLUME /code/var/logs
VOLUME /code/var/sessions
VOLUME /code/vendor

WORKDIR /code

RUN mkdir -p /code/var/cache && chown www-data:www-data /code/var/cache
RUN mkdir -p /code/var/logs && chown www-data:www-data /code/var/logs
RUN mkdir -p /code/var/sessions && chown www-data:www-data /code/var/sessions
RUN mkdir -p /code/vendor && chown www-data:www-data /code/vendor

RUN chown www-data:www-data /var/www

USER www-data
