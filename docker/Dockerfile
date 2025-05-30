FROM php:8.4-fpm

# Install Composer (and extra PHP extensions if needed)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer (using Composer's installer)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node (and npm) (using NodeSource's setup script)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install NPM globally
RUN npm install -g npm@latest

# Ensure Vite is available
RUN npm install -g vite

# Set the working directory (matching docker-compose.yml)
WORKDIR /var/www/html

