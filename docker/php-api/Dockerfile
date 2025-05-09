FROM php:8.3-apache

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

#this enables  rerouting
RUN a2enmod rewrite

RUN echo '<Directory /var/www/html>\nAllowOverride All\nRequire all granted\n</Directory>' >> /etc/apache2/apache2.conf


COPY . /var/www/html
