FROM php:7.2-cli
ADD . /code
WORKDIR /code
# composer deps not installed yet
RUN docker-php-ext-install -j$(nproc) pdo_mysql
CMD ["php", "bin/console", "server:run", "0.0.0.0:8000"]
