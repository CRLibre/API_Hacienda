FROM php:7.2.1-apache
LABEL version="1.0"
VOLUME [ "/data" ]
RUN apt-get update && apt-get -y install libpng-dev curl libcurl4-openssl-dev openssl netcat
RUN docker-php-ext-install pdo pdo_mysql mysqli gd curl
# RUN a2enmod rewrite
ADD ./setup.sh /tmp/
CMD [ "/bin/bash", "-c", "/tmp/setup.sh" ]
