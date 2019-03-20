FROM php:7.2.1-apache
MAINTAINER hmadrigal <hfmad@hotmail.com>
LABEL version="1.0"
LABEL description="Apache 2 / PHP"
RUN apt-get update && apt-get -y install libpng-dev curl libcurl4-openssl-dev openssl 
RUN docker-php-ext-install pdo pdo_mysql mysqli gd curl
RUN a2enmod rewrite
RUN service apache2 restart