#!/bin/bash
set -e

echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Starting API Hacienda Configuration"

setUpCryptoKey() {
    SETTINGS_FILE_PATH=/var/www/html/settings.php
    SETTINGS_TEMPLATE_FILE_PATH=/var/www/html/settings.php.dist
    LOCALHOSTNAME=localhost
    CRLIBRE_API_HACIENDA_CRYPTO_KEY="non-set"

    if [ -e "${SETTINGS_FILE_PATH}" ]; then
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] *** Found ${SETTINGS_FILE_PATH}, skipping its computation ***";
    else
        jsonValue() { KEY=$1; num=$2; awk -F"[,:}]" '{for(i=1;i<=NF;i++){if($i~/'$KEY'\042/){print $(i+1)}}}' | tr -d '"' | sed -n ${num}p ;} 
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Computing  ${SETTINGS_FILE_PATH}";

        # Generating settings.php 
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Creating settings.php without CryptoKey"
        cat "${SETTINGS_TEMPLATE_FILE_PATH}" \
        | sed "s/{dbName}/${CRLIBRE_API_HACIENDA_DB_NAME}/g" \
        | sed "s/{dbPss}/${CRLIBRE_API_HACIENDA_DB_PASSWORD}/g" \
        | sed "s/{dbUser}/${CRLIBRE_API_HACIENDA_DB_USER}/g" \
        | sed "s/{dbHost}/${CRLIBRE_API_HACIENDA_DB_HOST}/g" \
        > "${SETTINGS_FILE_PATH}"

        echo "Waiting on Database Server to be ready"
        # echo "Waiting on Web API server to be ready"
        while ! nc -z ${CRLIBRE_API_HACIENDA_DB_HOST} 3306; do echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Sleeping 1 sec.. waiting on MySQL"; sleep 1; done;
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] MySQL Is Up";
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Waiting on Web Server to be ready"
        while ! nc -z ${LOCALHOSTNAME} 80; do echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Sleeping 1 sec... waiting on Apache"; sleep 1; done;
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Apache is Up";

        sleep 2;
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Trying to retrieve CrytoKey"
        CRLIBRE_API_HACIENDA_CRYPTO_KEY_JSON=`curl -s "http://${LOCALHOSTNAME}:80/api.php?w=crypto&r=makeKey" -o /var/www/html/cryptoKey.json`
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Retrieved JSON: ${CRLIBRE_API_HACIENDA_CRYPTO_KEY_JSON}"
        CRLIBRE_API_HACIENDA_CRYPTO_KEY=` cat /var/www/html/cryptoKey.json | jsonValue resp`
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] CryptoKey set to: ${CRLIBRE_API_HACIENDA_CRYPTO_KEY}"    

        # Generating settings.php 
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Creating settings.php with CryptoKey"
        cat "${SETTINGS_TEMPLATE_FILE_PATH}" \
        | sed "s/{dbName}/${CRLIBRE_API_HACIENDA_DB_NAME}/g" \
        | sed "s/{dbPss}/${CRLIBRE_API_HACIENDA_DB_PASSWORD}/g" \
        | sed "s/{dbUser}/${CRLIBRE_API_HACIENDA_DB_USER}/g" \
        | sed "s/{dbHost}/${CRLIBRE_API_HACIENDA_DB_HOST}/g" \
        | sed "s/{cryptoKey}/${CRLIBRE_API_HACIENDA_CRYPTO_KEY}/g" \
        > "${SETTINGS_FILE_PATH}"
        
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Created ${SETTINGS_FILE_PATH} ***";    
    fi
}

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
        echo "[$(date -u +%d-%m-%Y_%H-%S-%N)][${0}] Starting Apache Web Server"
        set -- apache2-foreground "$@"
fi

setUpCryptoKey &

exec "$@"
