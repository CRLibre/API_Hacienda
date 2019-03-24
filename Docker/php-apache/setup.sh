#!/bin/sh
echo "============== BEGIN: Computing: /var/www/html/settings.php ======================"

# while ! nc -z 127.0.0.1 8080; do echo sleeping; sleep 1; done;
# echo Connected!;
# jsonValue() { KEY=$1; num=$2; awk -F"[,:}]" '{for(i=1;i<=NF;i++){if($i~/'$KEY'\042/){print $(i+1)}}}' | tr -d '"' | sed -n ${num}p ;} 
# CRLIBRE_API_HACIENDA_CRYPTO_KEY=`curl "http://localhost:80/api.php?w=crypto&r=makeKey" | jsonValue resp`;

# Generating settings.php 
cat /var/www/html/settings.php.dist \
| sed "s/{dbName}/${CRLIBRE_API_HACIENDA_DB_NAME}/g" \
| sed "s/{dbPss}/${CRLIBRE_API_HACIENDA_DB_PASSWORD}/g" \
| sed "s/{dbUser}/${CRLIBRE_API_HACIENDA_DB_USER}/g" \
| sed "s/{dbHost}/${CRLIBRE_API_HACIENDA_DB_HOST}/g" \
| sed "s/{cryptoKey}/${CRLIBRE_API_HACIENDA_CRYPTO_KEY}/g" \
> /var/www/html/settings.php

# cat /var/www/html/settings.php.dist \
# | sed "s/{dbName}/${CRLIBRE_API_HACIENDA_DB_NAME}/g"  \
# | sed "s/{dbPss}/${CRLIBRE_API_HACIENDA_DB_PASSWORD}/g" \ 
# | sed "s/{dbUser}/${CRLIBRE_API_HACIENDA_DB_USER}/g" \
# | sed "s/{dbHost}/${CRLIBRE_API_HACIENDA_DB_HOST}/g" \
# | sed "s/{cryptoKey}/${CRLIBRE_API_HACIENDA_CRYPTO_KEY}/g" \
# > /var/www/html/settings.php
echo "============== END: Computing: /var/www/html/settings.php ======================"