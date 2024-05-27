FROM php:8.1.26-apache

LABEL "br.dev.filipebezerra.product"="DL Delivery"
LABEL "br.dev.filipebezerra.version"="1.0-rc1.1"
LABEL "org.opencontainers.image.title"="DL Delivery"
LABEL "org.opencontainers.image.version"="1.0-rc1.1"
LABEL "org.opencontainers.image.authors"="Filipe Bezerra :: https://filipebezerra.dev.br"
LABEL "org.opencontainers.image.description"="DL Delivery is an auxiliary delivery system developed for Drogaria Litorânea Inc. Due to poor quality map information in some countryside cities, this software is used as a GPS coordinates database for clients, improving delivery times."
LABEL "org.opencontainers.image.ref.name"="php:8.1.26-apache"

# Install dependencies to build PDO Drivers
RUN apt update && apt install -y build-essential unixodbc-dev gnupg \
    && docker-php-ext-install pdo \
    && docker-php-ext-enable pdo

# Compiling MSSQL's PDO Drivers
RUN pecl install sqlsrv \
    && pecl install pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# Install ODBC Drivers
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl https://packages.microsoft.com/config/debian/12/prod.list | tee /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 \
    && rm -rf /var/lib/apt/lists/*

# Copy php.ini
COPY php.ini-production /usr/local/etc/php/php.ini

# Copy project folder to container
COPY /DLD_Web /var/www/html

# Ajust permissions to upload picture's folder
RUN chmod -R 777 /var/www/html/imgs/houses
