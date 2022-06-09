FROM php:7.2.1-apache
LABEL version="1.0"
# VOLUME [ "/var/www/html" ]
# VOLUME [ "/var/www/api" ]
RUN apt-get update && apt-get -y install libpng-dev curl libcurl4-openssl-dev openssl netcat
RUN docker-php-ext-install pdo pdo_mysql mysqli gd curl
RUN a2enmod rewrite
COPY ./docker-php-apache/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
COPY ./www/ /var/www/html
COPY ./api/ /var/www/api
RUN mkdir -p /var/www/api/errors
RUN mkdir -p /var/www/api/logs
RUN chmod -R 755 /var/www/
RUN chmod -R 777 /var/www/api/errors
RUN chmod -R 777 /var/www/api/logs
RUN ln -s /usr/local/bin/docker-entrypoint.sh / # backwards compat
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
