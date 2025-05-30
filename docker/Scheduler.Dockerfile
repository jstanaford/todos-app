FROM php:8.4-fpm

# Install required PHP extensions and tools
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create the log file 
RUN touch /var/log/cron.log && chmod 0777 /var/log/cron.log

# Set environment variables
ENV PATH="/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin"
ENV COMPOSER_ALLOW_SUPERUSER=1

# Create the entrypoint script
COPY scheduler/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Run the command on container startup
CMD ["/entrypoint.sh"] 