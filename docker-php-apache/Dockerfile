FROM php:7.4.9-apache
LABEL version="1.1"
# VOLUME [ "/var/www/html" ]
# VOLUME [ "/var/www/api" ]
#RUN apt-get update && apt-get -y install libpng-dev curl libcurl4-openssl-dev openssl netcat
# Update apt sources to use archive.debian.org for EOL Debian Buster
RUN sed -i 's/deb.debian.org/archive.debian.org/g' /etc/apt/sources.list \
 && sed -i 's/security.debian.org\/debian-security/archive.debian.org\/debian-security/g' /etc/apt/sources.list \
 && sed -i '/buster-updates/d' /etc/apt/sources.list \
 && apt-get update -o Acquire::Check-Valid-Until=false \
 && apt-get -y install libpng-dev curl libcurl4-openssl-dev openssl netcat

RUN docker-php-ext-install pdo pdo_mysql mysqli gd curl
RUN a2enmod rewrite
COPY ./docker-php-apache/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
COPY ./www/ /var/www/html
COPY ./api/ /var/www/api
RUN mkdir -p /var/www/api/errors
RUN mkdir -p /var/www/api/logs
RUN mkdir -p /var/www/api/files
# Set permissions on the /var/www/api directories
RUN chmod -R 755 /var/www/
RUN chmod -R 777 /var/www/api/errors
RUN chmod -R 777 /var/www/api/logs
RUN chmod -R 777 /var/www/api/files
RUN ln -s /usr/local/bin/docker-entrypoint.sh / # backwards compat
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
