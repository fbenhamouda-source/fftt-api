FROM php:8.2-apache

RUN a2enmod rewrite

# Forcer Apache à envoyer toutes les requêtes vers index.php (solution magique anti-404)
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    FallbackResource /index.php\n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/
EXPOSE 80
