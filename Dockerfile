FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application code
COPY . .

# Install PHP dependencies if composer.json exists
RUN if [ -f composer.json ]; then \
    composer install --no-dev --no-scripts && \
    composer dump-autoload --optimize; \
    fi

# Set proper permissions
RUN chown -R www-data:www-data /app

# Switch to non-root user
USER www-data

# Default command
CMD ["php", "-a"]