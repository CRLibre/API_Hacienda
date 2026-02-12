#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
MIGRATION_ROOT="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
ROOT_DIR="${1:-${MIGRATION_ROOT}}"
FIXTURE_DIR="${ROOT_DIR}/tests/fixtures/parity"
MANIFEST="${FIXTURE_DIR}/manifest.json"
API_BASE_URL="${API_BASE_URL:-http://127.0.0.1:8080}"
W_FILTER="${2:-${W_FILTER:-}}"
R_FILTER="${3:-${R_FILTER:-}}"

if [[ ! -f "${MANIFEST}" ]]; then
  echo "Manifest not found: ${MANIFEST}" >&2
  exit 1
fi

tmp_headers="$(mktemp)"
tmp_body="$(mktemp)"
trap 'rm -f "${tmp_headers}" "${tmp_body}"' EXIT

capture_one() {
  local request_file="$1"
  local response_file="$2"

  local method path params_json
  method="$(jq -r '.method // "POST"' "${request_file}")"
  path="$(jq -r '.path // "/api.php"' "${request_file}")"
  params_json="$(jq -c '.params // {}' "${request_file}")"

  local url="${API_BASE_URL}${path}"
  : > "${tmp_headers}"
  : > "${tmp_body}"

  local -a data_args=()
  while IFS= read -r kv; do
    data_args+=(--data-urlencode "${kv}")
  done < <(jq -r 'to_entries[] | "\(.key)=\(.value)"' <<< "${params_json}")

  if [[ "${method}" == "GET" ]]; then
    curl -sS -G -X GET -D "${tmp_headers}" -o "${tmp_body}" \
      "${url}" "${data_args[@]}"
  else
    curl -sS -X "${method}" -D "${tmp_headers}" -o "${tmp_body}" \
      "${url}" "${data_args[@]}"
  fi

  local status_code
  status_code="$(awk 'NR==1 {print $2}' "${tmp_headers}")"
  local headers_json
  headers_json="$(awk -F': ' 'BEGIN{print "{"} /^[A-Za-z-]+: / {gsub("\r","",$2); printf "\"%s\":\"%s\",",$1,$2} END{print "\"_\":\"_\"}"}' "${tmp_headers}" | jq 'del(._)')"
  local body_json
  body_json="$(jq -Rs '.' < "${tmp_body}")"

  jq -n \
    --argjson status_code "${status_code:-0}" \
    --argjson headers "${headers_json}" \
    --argjson body "${body_json}" \
    '{
      captured: true,
      status_code: $status_code,
      headers: $headers,
      body: $body
    }' > "${response_file}"
}

jq -c '.[]' "${MANIFEST}" | while IFS= read -r route; do
  w="$(jq -r '.w' <<< "${route}")"
  r="$(jq -r '.r' <<< "${route}")"

  if [[ -n "${W_FILTER}" && "${w}" != "${W_FILTER}" ]]; then
    continue
  fi
  if [[ -n "${R_FILTER}" && "${r}" != "${R_FILTER}" ]]; then
    continue
  fi

  for scenario in success validation_error auth_error malformed; do
    request_file="$(jq -r ".scenarios.${scenario}.request" <<< "${route}")"
    response_file="$(jq -r ".scenarios.${scenario}.response" <<< "${route}")"
    if [[ -f "${request_file}" ]]; then
      capture_one "${request_file}" "${response_file}"
    fi
  done
done

echo "Golden fixture capture complete."
