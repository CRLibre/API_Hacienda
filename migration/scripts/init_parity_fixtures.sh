#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
MIGRATION_ROOT="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
ROOT_DIR="${1:-${MIGRATION_ROOT}}"
FIXTURE_DIR="${ROOT_DIR}/tests/fixtures/parity"

mkdir -p "${FIXTURE_DIR}"

routes=(
  "users:users_register"
  "users:users_get_list"
  "users:users_log_me_in"
  "users:users_log_me_out"
  "users:users_avatar_get"
  "users:users_avatar_upload"
  "users:users_get_my_details"
  "users:users_recover_pwd"
  "users:users_update_profile"
  "users:users_personal_bg_upload"
  "users:users_personal_bg_get"
  "users:login_auto"
  "users:users_confirm_session_vilidity"
  "clave:clave"
  "genXML:gen_xml_mr"
  "genXML:gen_xml_fe"
  "genXML:gen_xml_nc"
  "genXML:gen_xml_nd"
  "genXML:gen_xml_te"
  "genXML:gen_xml_fec"
  "genXML:gen_xml_fee"
  "genXML:test"
  "firmarXML:firmar"
  "token:gettoken"
  "token:refresh"
  "send:json"
  "send:sendMensaje"
  "send:sendTE"
  "consultar:consultarCom"
)

manifest='[]'

for entry in "${routes[@]}"; do
  w="${entry%%:*}"
  r="${entry##*:}"
  route_dir="${FIXTURE_DIR}/${w}/${r}"
  mkdir -p "${route_dir}"

  for scenario in success validation_error auth_error malformed; do
    request_path="${route_dir}/${scenario}.request.json"
    response_path="${route_dir}/${scenario}.response.json"

    if [[ ! -f "${request_path}" ]]; then
      cat > "${request_path}" <<EOF
{
  "method": "POST",
  "path": "/api.php",
  "params": {
    "w": "${w}",
    "r": "${r}"
  },
  "notes": "Fill route-specific fields before capture."
}
EOF
    fi

    if [[ ! -f "${response_path}" ]]; then
      cat > "${response_path}" <<EOF
{
  "captured": false,
  "status_code": null,
  "headers": {},
  "body": null
}
EOF
    fi
  done

  manifest="$(
    jq \
      --arg w "${w}" \
      --arg r "${r}" \
      --arg base "${route_dir}" \
      '. + [{
        "w": $w,
        "r": $r,
        "scenarios": {
          "success": {
            "request": ($base + "/success.request.json"),
            "response": ($base + "/success.response.json")
          },
          "validation_error": {
            "request": ($base + "/validation_error.request.json"),
            "response": ($base + "/validation_error.response.json")
          },
          "auth_error": {
            "request": ($base + "/auth_error.request.json"),
            "response": ($base + "/auth_error.response.json")
          },
          "malformed": {
            "request": ($base + "/malformed.request.json"),
            "response": ($base + "/malformed.response.json")
          }
        }
      }]' <<< "${manifest}"
  )"
done

echo "${manifest}" | jq '.' > "${FIXTURE_DIR}/manifest.json"

echo "Initialized parity fixture templates:"
echo "  ${FIXTURE_DIR}/manifest.json"
