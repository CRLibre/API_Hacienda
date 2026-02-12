from __future__ import annotations

import csv
from dataclasses import dataclass
from pathlib import Path


@dataclass(frozen=True, slots=True)
class ParamSpec:
    key: str
    default: str
    required: bool


def _params_data_file() -> Path:
    return Path(__file__).resolve().parent / "data" / "params.tsv"


def _load_params() -> dict[tuple[str, str], list[ParamSpec]]:
    data_file = _params_data_file()
    if not data_file.exists():
        return {}

    routes: dict[tuple[str, str], list[ParamSpec]] = {}
    with data_file.open("r", encoding="utf-8", newline="") as fh:
        reader = csv.DictReader(fh, delimiter="\t")
        for row in reader:
            w = (row.get("w") or "").strip()
            r = (row.get("r") or "").strip()
            key = (row.get("param_key") or "").strip()
            default = row.get("default") or ""
            required = (row.get("required") or "").strip().lower() == "true"
            if not w or not r or not key:
                continue
            routes.setdefault((w, r), []).append(ParamSpec(key=key, default=default, required=required))
    return routes


PARAMS_BY_ROUTE = _load_params()

# Preserve PHP module parameter declaration order for routes where generated
# params.tsv order diverges and changes first-missing required-param behavior.
PARAM_ORDER_OVERRIDES: dict[tuple[str, str], tuple[str, ...]] = {
    ("check", "checkxml"): ("tipoDocumento", "formato", "xml"),
    ("clave", "clave"): (
        "tipoDocumento",
        "tipoCedula",
        "cedula",
        "codigoPais",
        "consecutivo",
        "situacion",
        "terminal",
        "sucursal",
        "codigoSeguridad",
    ),
    ("token", "gettoken"): ("grant_type", "client_id", "client_secret", "username", "password"),
    ("token", "refresh"): ("grant_type", "client_id", "client_secret", "refresh_token"),
    ("send", "json"): (
        "token",
        "clave",
        "fecha",
        "emi_tipoIdentificacion",
        "emi_numeroIdentificacion",
        "recp_tipoIdentificacion",
        "recp_numeroIdentificacion",
        "comprobanteXml",
        "callbackUrl",
        "client_id",
    ),
    ("send", "sendMensaje"): (
        "token",
        "clave",
        "fecha",
        "emi_tipoIdentificacion",
        "emi_numeroIdentificacion",
        "recp_tipoIdentificacion",
        "recp_numeroIdentificacion",
        "consecutivoReceptor",
        "comprobanteXml",
        "callbackUrl",
        "client_id",
    ),
    ("send", "sendTE"): (
        "token",
        "clave",
        "fecha",
        "emi_tipoIdentificacion",
        "emi_numeroIdentificacion",
        "comprobanteXml",
        "callbackUrl",
        "client_id",
    ),
    ("users", "users_register"): ("fullName", "userName", "email", "about", "country", "pwd"),
    ("crlibreall", "FE"): (
        "tipoDocumento",
        "tipoCedula",
        "cedula",
        "codigoPais",
        "consecutivo",
        "situacion",
        "terminal",
        "sucursal",
        "codigoSeguridad",
    ),
    ("crlibreall", "NC"): (
        "tipoDocumento",
        "tipoCedula",
        "cedula",
        "codigoPais",
        "consecutivo",
        "situacion",
        "terminal",
        "sucursal",
        "codigoSeguridad",
    ),
    ("crlibreall", "ND"): (
        "tipoDocumento",
        "tipoCedula",
        "cedula",
        "codigoPais",
        "consecutivo",
        "situacion",
        "terminal",
        "sucursal",
        "codigoSeguridad",
    ),
    ("ejemplo", "un_usuario"): ("nombre", "apellido"),
    ("sendMail", "sendmail"): ("xmlEnvia", "facturaPDF", "xmlHacienda", "clave"),
    ("signXML", "signFE"): ("p12Url", "pinP12", "inXml"),
    # facturador: preserve legacy module.php declaration order for required-parameter parity
    ("facturador", "addSucursales"): ("numeroSucursal", "nombreSucursal"),
    ("facturador", "add_companny_reciver"): (
        "idMasterUser",
        "nombreCliente",
        "numeroCedula",
        "tipoCedula",
        "telefono",
        "idProvincia",
        "idCanton",
        "idDistrito",
        "idBarrio",
        "otrasSenas",
        "nombreComercial",
        "correoPrincipal",
        "copiasCorreo",
        "numeroFax",
    ),
    ("facturador", "add_terminal"): ("numeroTerminal", "nombreTerminal", "idSucursal"),
    ("facturador", "compannyUpdateInformation"): (
        "idMasterUser",
        "nombre",
        "nombreComercial",
        "email",
        "codigoPais",
        "fax",
        "tipoCedula",
        "cedula",
        "telefono",
    ),
    ("facturador", "compannyUpdateLocation"): (
        "idMasterUser",
        "idProvincia",
        "idCanton",
        "idDistrito",
        "idBarrio",
        "sennas",
    ),
    ("facturador", "companny_add_voucher"): (
        "idMasterUser",
        "clave",
        "consecutivo",
        "estado",
        "xmlEnviadoBase64",
        "tipoDocumento",
        "respuestaMHBase64",
        "idReceptor",
        "env",
    ),
    ("facturador", "companny_getMyConsecutive"): ("idMasterUser", "tipoComprobante", "env"),
    ("facturador", "companny_updateConsecutive"): ("idMasterUser", "tipoDocumento", "env"),
    ("facturador", "companny_users_recover_pwd"): ("userName", "idMasterUser"),
    ("facturador", "companny_users_register"): (
        "fullName",
        "userName",
        "email",
        "about",
        "country",
        "pwd",
        "idMasterUser",
    ),
    ("facturador", "company_prod_users"): ("userName", "password", "pinCerti", "downloadCode"),
    ("facturador", "company_stag_users"): ("userName", "password", "pinCerti", "downloadCode"),
    ("facturador", "getCompannyLocationInformation"): (
        "idProvincia",
        "idCanton",
        "idDistrito",
        "idBarrio",
    ),
    ("facturador", "get_district"): ("idProvince", "idCanton"),
    ("facturador", "get_neighborhood"): ("idProvince", "idCanton", "idDistrito"),
    ("facturador", "get_receiver_by_id"): ("idReceptor", "idMasterUser"),
}


def _ordered_specs(w: str, r: str, specs: list[ParamSpec]) -> list[ParamSpec]:
    ordered_keys = PARAM_ORDER_OVERRIDES.get((w, r))
    if not ordered_keys:
        return specs

    by_key = {spec.key: spec for spec in specs}
    out: list[ParamSpec] = []
    seen: set[str] = set()
    for key in ordered_keys:
        spec = by_key.get(key)
        if spec is not None:
            out.append(spec)
            seen.add(key)
    for spec in specs:
        if spec.key not in seen:
            out.append(spec)
    return out


def apply_defaults_and_validate(params: dict[str, str], w: str, r: str) -> tuple[dict[str, str], str | None]:
    specs = _ordered_specs(w, r, PARAMS_BY_ROUTE.get((w, r), []))
    enriched = dict(params)

    for spec in specs:
        current = enriched.get(spec.key, "")
        if str(current).strip() == "":
            if spec.required:
                return enriched, spec.key
            enriched[spec.key] = spec.default
    return enriched, None
