FROM ubuntu:22.04

# Prevent interactive prompts
ENV DEBIAN_FRONTEND=noninteractive

# Install prerequisites and add Ondrej's PPA
# Combined prerequisites, PPA, and installation into a single layer
RUN apt-get update && apt-get install -y \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    curl \
    git \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update && apt-get install -y \
    apache2 \
    php8.2 \
    php8.2-cli \
    php8.2-common \
    php8.2-mysql \
    php8.2-pdo \
    php8.2-sqlite3 \
    php8.2-mbstring \
    php8.2-intl \
    php8.2-xml \
    php8.2-gd \
    php8.2-curl \
    php8.2-zip \
    php8.2-solr \
    php8.2-xdebug \
    php8.2-memcached \
    php8.2-phalcon5 \
    libapache2-mod-php8.2 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers ssl

# Copy custom php.ini or xdebug.ini if needed
COPY docker/conf/web.conf /etc/apache2/sites-available/000-default.conf
# COPY docker/conf/xdebug.ini /etc/php/8.2/cli/conf.d/20-xdebug.ini
COPY docker/conf/xdebug.ini /etc/php/8.2/apache2/conf.d/20-xdebug.ini
COPY docker/ssl/ca.crt /etc/ssl/certs/localhost.crt
COPY docker/ssl/ca.key /etc/ssl/private/localhost.key

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

WORKDIR /var/www/html
EXPOSE 80

# Start Apache
CMD ["apache2ctl", "-D", "FOREGROUND"]