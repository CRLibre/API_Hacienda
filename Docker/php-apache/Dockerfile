FROM php:7.2.1-apache
MAINTAINER hmadrigal <hfmad@hotmail.com>
LABEL version="1.0"
LABEL description="Apache 2 / PHP"
VOLUME [ "/data" ]
RUN apt-get update && apt-get -y install libpng-dev curl libcurl4-openssl-dev openssl
RUN docker-php-ext-install pdo pdo_mysql mysqli gd curl
# RUN a2enmod rewrite
RUN /bin/bash -c 'service apache2 restart && exec /tmp/setup.sh && service apache2 stop'

# Option 1 for running script
# ADD setup.sh /tmp/
# RUN  /bin/bash -c 'source /tmp/setup.sh'

# Option 2 for running script
# CMD /bin/bash -c 'apache2-foreground & exec /tmp/setup.sh'