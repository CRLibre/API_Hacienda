#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
MIGRATION_ROOT="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
ROOT_DIR="${1:-${MIGRATION_ROOT}}"
ROUTES_FILE="${ROOT_DIR}/docs/contracts/routes.tsv"
OUT_FILE="${ROOT_DIR}/docs/contracts/migration-tracker.tsv"

if [[ ! -f "${ROUTES_FILE}" ]]; then
  echo "Missing routes file: ${ROUTES_FILE}" >&2
  echo "Run ./migration/scripts/generate_api_contract.sh first." >&2
  exit 1
fi

wave_for_module() {
  local module="$1"
  case "${module}" in
    users|clave|token|consultar|send|genXML|firmarXML|db|crypto|files|cala)
      echo "W1_CORE"
      ;;
    facturador|crlibreall|fileUploader|sendMail|signXML|makeQR)
      echo "W2_BUSINESS"
      ;;
    wirez|geoloc|XmlToBase64|makeJson|callback|check|version|ejemplo|baseModule)
      echo "W3_SUPPORT"
      ;;
    *)
      echo "W3_SUPPORT"
      ;;
  esac
}

priority_for_module() {
  local module="$1"
  case "${module}" in
    users|genXML|token|send|consultar|clave|firmarXML)
      echo "P0"
      ;;
    facturador|crlibreall|files|db)
      echo "P1"
      ;;
    *)
      echo "P2"
      ;;
  esac
}

{
  echo -e "w\tr\tmodule_file\twave\tpriority\tstatus\tpython_handler\tparity_fixture\tparity_status\tcutover_status\towner\tnotes"
  tail -n +2 "${ROUTES_FILE}" | while IFS=$'\t' read -r w r module_file; do
    wave="$(wave_for_module "${w}")"
    priority="$(priority_for_module "${w}")"
    python_handler="python_api.handlers.${w}.${r}"
    parity_fixture="tests/fixtures/parity/${w}/${r}"
    echo -e "${w}\t${r}\t${module_file}\t${wave}\t${priority}\tnot_started\t${python_handler}\t${parity_fixture}\tnot_captured\tnot_cutover\tunassigned\t"
  done
} > "${OUT_FILE}"

echo "Generated: ${OUT_FILE}"
