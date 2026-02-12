#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="${1:-.}"
SRC_CONTRACTS="${ROOT_DIR}/docs/contracts"
DST_DATA="${ROOT_DIR}/python-api/python_api/data"

mkdir -p "${DST_DATA}"

cp "${SRC_CONTRACTS}/routes.tsv" "${DST_DATA}/routes.tsv"
cp "${SRC_CONTRACTS}/params.tsv" "${DST_DATA}/params.tsv"
cp "${SRC_CONTRACTS}/migration-tracker.tsv" "${DST_DATA}/migration-tracker.tsv"

echo "Synced:"
echo "  ${DST_DATA}/routes.tsv"
echo "  ${DST_DATA}/params.tsv"
echo "  ${DST_DATA}/migration-tracker.tsv"
