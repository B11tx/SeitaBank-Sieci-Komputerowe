FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    a2enmod rewrite headers remoteip

RUN echo "RemoteIPHeader X-Forwarded-For" > /etc/apache2/conf-available/remoteip.conf && \
    echo "RemoteIPInternalProxy 172.29.1.0/24" >> /etc/apache2/conf-available/remoteip.conf && \
    a2enconf remoteip

WORKDIR /var/www/html
COPY src/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html
