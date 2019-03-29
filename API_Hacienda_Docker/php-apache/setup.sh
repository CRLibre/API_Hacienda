#!/bin/bash
echo "Setting up API_Hacienda"
set -m
SETTINGS_FILE_PATH=/var/www/html/settings.php
SETTINGS_TEMPLATE_FILE_PATH=/var/www/html/settings.php.dist
LOCALHOSTNAME=localhost
CRLIBRE_API_HACIENDA_CRYPTO_KEY="non-set"

if [ ! -e "${SETTINGS_FILE_PATH}" ]; then
    jsonValue() { KEY=$1; num=$2; awk -F"[,:}]" '{for(i=1;i<=NF;i++){if($i~/'$KEY'\042/){print $(i+1)}}}' | tr -d '"' | sed -n ${num}p ;} 
    echo "Computing  ${SETTINGS_FILE_PATH}";

    # Generating settings.php 
    echo "Creating settings.php without CryptoKey"
    cat "${SETTINGS_TEMPLATE_FILE_PATH}" \
    | sed "s/{dbName}/${CRLIBRE_API_HACIENDA_DB_NAME}/g" \
    | sed "s/{dbPss}/${CRLIBRE_API_HACIENDA_DB_PASSWORD}/g" \
    | sed "s/{dbUser}/${CRLIBRE_API_HACIENDA_DB_USER}/g" \
    | sed "s/{dbHost}/${CRLIBRE_API_HACIENDA_DB_HOST}/g" \
    > "${SETTINGS_FILE_PATH}"

    echo "Starting Web API Server"
    /usr/local/bin/apache2-foreground &
    # echo "Waiting on Web API server to be ready"
    while ! nc -z ${CRLIBRE_API_HACIENDA_DB_HOST} 3306; do echo "Sleeping 1 sec.. waiting on MySQL"; sleep 1; done;
    echo "MySQL Is Up";
    while ! nc -z ${LOCALHOSTNAME} 80; do echo "Sleeping 1 sec... waiting on Apache"; sleep 1; done;
    echo "Apache is Up";

    sleep 2;
    echo "Trying to retrieve CrytoKey"
    CRLIBRE_API_HACIENDA_CRYPTO_KEY_JSON=`curl -s "http://${LOCALHOSTNAME}:80/api.php?w=crypto&r=makeKey" -o /var/www/html/cryptoKey.json`
    echo "JSON: ${CRLIBRE_API_HACIENDA_CRYPTO_KEY_JSON}"
    CRLIBRE_API_HACIENDA_CRYPTO_KEY=` cat /var/www/html/cryptoKey.json | jsonValue resp`
    echo "CryptoKey set to: ${CRLIBRE_API_HACIENDA_CRYPTO_KEY}"    

    # Generating settings.php 
    echo "Creating settings.php with CryptoKey"
    cat "${SETTINGS_TEMPLATE_FILE_PATH}" \
    | sed "s/{dbName}/${CRLIBRE_API_HACIENDA_DB_NAME}/g" \
    | sed "s/{dbPss}/${CRLIBRE_API_HACIENDA_DB_PASSWORD}/g" \
    | sed "s/{dbUser}/${CRLIBRE_API_HACIENDA_DB_USER}/g" \
    | sed "s/{dbHost}/${CRLIBRE_API_HACIENDA_DB_HOST}/g" \
    | sed "s/{cryptoKey}/${CRLIBRE_API_HACIENDA_CRYPTO_KEY}/g" \
    > "${SETTINGS_FILE_PATH}"
else
    echo "Found ${SETTINGS_FILE_PATH}, skipping its computation";
fi
echo "Foreground set to: apache2-foreground to foreground"
fg %1
echo " Done."
exit 0
