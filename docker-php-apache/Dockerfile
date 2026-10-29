# PHP 8.4.27 corrige PHP2Shell (CVE-2026-109618 / CVE-2026-109619).
# La base va fijada por digest para que un tag en caché no deje corriendo
# un PHP sin el parche.
FROM php:8.4.27-apache@sha256:be1326658a7d36dba6be79a71212d6ff9c5f854335b8ba77d714da1852c1b2ea
LABEL version="1.2"

# Piso de PHP: el build falla si la base trae un PHP sin el parche.
ARG PHP_MIN_VERSION=8.4.27
RUN php -r 'exit(version_compare(PHP_VERSION, getenv("PHP_MIN_VERSION"), ">=") ? 0 : 1);' \
    || { echo "PHP $(php -r 'echo PHP_VERSION;') < ${PHP_MIN_VERSION}: base sin el parche de seguridad" >&2; exit 1; }

# netcat-openbsd: lo usa docker-entrypoint.sh (en trixie "netcat" es un
# paquete virtual). curl y pdo ya vienen compilados en la imagen base.
RUN apt-get update \
    && apt-get -y install --no-install-recommends libpng-dev curl netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql mysqli gd

# OpenSSL 3 no lee los .p12 cifrados con algoritmos viejos (RC2-40) sin el
# provider "legacy" ("digital envelope routines::unsupported", ver #205).
RUN sed -i 's/^default = default_sect$/default = default_sect\nlegacy = legacy_sect/; s/^# activate = 1$/activate = 1\n\n[legacy_sect]\nactivate = 1/' /usr/lib/ssl/openssl.cnf \
    && openssl list -providers | grep -q 'OpenSSL Legacy Provider'

COPY ./docker-php-apache/php.ini /usr/local/etc/php/conf.d/zz-api-hacienda.ini
COPY ./docker-php-apache/apache-security.conf /etc/apache2/conf-available/zz-api-hacienda-security.conf
RUN a2enmod rewrite && a2enconf zz-api-hacienda-security

COPY ./docker-php-apache/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
COPY ./www/ /var/www/html
COPY ./api/ /var/www/api
# Solo www-data escribe en errors/, logs/ y files/ (antes eran 777).
RUN mkdir -p /var/www/api/errors /var/www/api/logs /var/www/api/files \
    && chmod -R 755 /var/www/ \
    && chown -R www-data:www-data /var/www/api/errors /var/www/api/logs /var/www/api/files \
    && chmod -R 770 /var/www/api/errors /var/www/api/logs /var/www/api/files
RUN ln -s /usr/local/bin/docker-entrypoint.sh / # backwards compat
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
