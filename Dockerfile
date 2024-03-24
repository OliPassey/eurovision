# Use the official PHP 8.1 image with Apache.
FROM php:8.1-apache

# Install the PHP extensions we need and some utilities including unzip for Composer
RUN apt-get update && apt-get install -y unzip libpng-dev libjpeg-dev libfreetype6-dev libssl-dev libzip-dev wget gnupg && \
    docker-php-ext-install pdo pdo_mysql gd zip && \
    pecl install mongodb && docker-php-ext-enable mongodb

# Add MongoDB repo key and source list
RUN wget -qO - https://www.mongodb.org/static/pgp/server-4.4.asc | gpg --dearmor -o /usr/share/keyrings/mongodb-archive-keyring.gpg && \
    echo "deb [signed-by=/usr/share/keyrings/mongodb-archive-keyring.gpg] http://repo.mongodb.org/apt/debian buster/mongodb-org/4.4 main" | tee /etc/apt/sources.list.d/mongodb-org-4.4.list

# Set COMPOSER_ALLOW_SUPERUSER to 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy the application content to the container
COPY . .

# Install the PHP dependencies with Composer
RUN composer install --no-interaction


# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Expose port 80
EXPOSE 80

# Command to run Apache in the foreground
CMD ["apache2-foreground"]