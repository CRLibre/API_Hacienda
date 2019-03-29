#!/bin/sh
SETTINGS_FILE_PATH=/var/www/html/settings.php
if [ ! -e "${SETTINGS_FILE_PATH}" ]; then
    echo "Computng ${SETTINGS_FILE_PATH}";
    echo "Starting Apache"
    service apache2 restart;
    jsonValue() { KEY=$1; num=$2; awk -F"[,:}]" '{for(i=1;i<=NF;i++){if($i~/'$KEY'\042/){print $(i+1)}}}' | tr -d '"' | sed -n ${num}p ;} 
    echo "Retrieving CRYPTO KEY"
    CRLIBRE_API_HACIENDA_CRYPTO_KEY=`curl "http://localhost:80/api.php?w=crypto&r=makeKey" | jsonValue resp`;
    echo "Stopping Apache"
    service apache2 stop;

    # Generating settings.php 
    echo "Generating settings.php"
    cat /var/www/html/settings.php.dist \
    | sed "s/{dbName}/${CRLIBRE_API_HACIENDA_DB_NAME}/g" \
    | sed "s/{dbPss}/${CRLIBRE_API_HACIENDA_DB_PASSWORD}/g" \
    | sed "s/{dbUser}/${CRLIBRE_API_HACIENDA_DB_USER}/g" \
    | sed "s/{dbHost}/${CRLIBRE_API_HACIENDA_DB_HOST}/g" \
    | sed "s/{cryptoKey}/${CRLIBRE_API_HACIENDA_CRYPTO_KEY}/g" \
    > "${SETTINGS_FILE_PATH}"
else
    echo "Found ${SETTINGS_FILE_PATH}, skipping its computation";
fi
echo "Calling: apache2 foreground"
/usr/local/bin/apache2-foreground 