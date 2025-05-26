FROM php:8.4-fpm

# Install Composer (and extra PHP extensions if needed)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer (using Composer's installer)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node (and npm) (using NodeSource's setup script)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# (Optional: Install extra PHP extensions or tools if needed)

# Set the working directory (matching docker-compose.yml)
WORKDIR /var/www/html

# (Optional: Copy a custom php.ini or extra configs if needed)

# (Optional: Run "composer install" or "npm install" here if you don't want to do it in docker-compose's command)

# (Optional: Expose a port if you're not using docker-compose's "ports" mapping)

# (Optional: Use a non-root user if desired)

# (Optional: Add a healthcheck or extra CMD if needed) 