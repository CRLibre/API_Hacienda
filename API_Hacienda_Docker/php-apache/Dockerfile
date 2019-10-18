FROM php:7.2.1-apache
LABEL version="1.0"
VOLUME [ "/data" ]
RUN apt-get update && apt-get -y install libpng-dev curl libcurl4-openssl-dev openssl netcat
RUN docker-php-ext-install pdo pdo_mysql mysqli gd curl
# RUN a2enmod rewrite
COPY docker-entrypoint.sh /usr/local/bin/
RUN ln -s /usr/local/bin/docker-entrypoint.sh / # backwards compat
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]

# ADD ./docker-setup-crypto-key.sh /usr/local/bin/
# ADD ./docker-api-hacienda-entrypoint.sh /usr/local/bin/
# RUN [ "/bin/bash", "-c", "/usr/local/bin/docker-api-hacienda-entrypoint.sh" ]
# CMD [ "/bin/bash", "-c", "/tmp/setup.sh" ]
# ENTRYPOINT [ "docker-php-entrypoint" ]

