FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
COPY . /var/www/html/
RUN echo '<Directory /var/www/html/>\n\tOptions Indexes FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n</Directory>' > /etc/apache2/conf-available/override.conf
RUN a2conf override
EXPOSE 80
