FROM php:8.2-apache

# Activer la réécriture d'URL d'Apache
RUN a2enmod rewrite

# Autoriser les fichiers de configuration (.htaccess)
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copier les fichiers du projet
COPY . /var/www/html/

EXPOSE 80
