FROM php:7.2.1-apache
MAINTAINER hmadrigal <hfmad@hotmail.com>
LABEL version="1.0"
LABEL description="Apache 2 / PHP"
VOLUME [ "/data" ]
RUN apt-get update && apt-get -y install libpng-dev curl libcurl4-openssl-dev openssl
RUN docker-php-ext-install pdo pdo_mysql mysqli gd curl
# RUN a2enmod rewrite
# RUN service apache2 restart
ADD setup.sh /tmp/
# CMD [ "apache2-foreground && /bin/bash -c 'source /tmp/setup.sh'"]
# CMD  /bin/bash -c 'source /tmp/setup.sh'
# CMD [ "/bin/sh","/tmp/setup.sh" ]