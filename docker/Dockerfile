FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    git \ 
    curl \
    vim \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
RUN sed -ri -e '/var/www/html!${APACHE_DOCUMENT_ROOT}' /etc/apache2/sites-available/*.conf
RUN sed -ri -e '/var/www/!${APACHE_DOCUMENT_ROOT}' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

COPY . /var/www/html/

WORKDIR /var/www/html/

RUN curl -sS https://getcomposer.org/installer | php \
    && php composer.phar install \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html/var

EXPOSE 80

CMD ["apache2-foreground"]