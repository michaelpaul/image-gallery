FROM php:7.2-cli
ADD . /code
WORKDIR /code
# NOTE: composer deps are not installed at build time
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
# system deps
RUN apt-get update && apt-get install -y git zlib1g-dev unzip
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip

RUN echo "upload_max_filesize=10M" > /usr/local/etc/php/conf.d/app.ini

CMD ["php", "bin/console", "server:run", "0.0.0.0:8000"]
