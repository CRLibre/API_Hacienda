# API Contract Baseline (Phase 1)

## Contract entrypoint
- HTTP entrypoint: `./www/api.php`
- Dispatch contract: `w=<module>&r=<route>`
- Request extraction precedence in `./www/api.php`:
- query string (`$_GET`)
- form (`$_POST`)
- raw JSON body (`php://input`, when JSON object)
- CLI `key=value` args

## Canonical response envelope
`./api/core/tools.php` returns a JSON envelope by default:

```json
{
  "status": "ok|error",
  "resp": "<payload>"
}
```

Observed status behavior in `tools_reply()`:
- Business/auth constants are translated to HTTP codes and error text.
- Generic successful calls return HTTP 200 and `status=ok`.
- `replyType=json` is default and used as contract baseline.

## Full route manifest and parameter map
This repo now includes machine-generated contract files from `module.php` definitions:
- Full route manifest (all `w`+`r`): `./docs/contracts/routes.tsv` (115 entries, header included).
- Param/default/required matrix: `./docs/contracts/params.tsv` (903 entries, header included).

Generation script:
- `./scripts/generate_api_contract.sh`

## Migration scope (Phase 2 target)
Target modules/routes for strict compatibility migration:
- `users`, `clave`, `genXML`, `firmarXML`, `token`, `send`, `consultar`.
- Total in-scope routes: 29.

### In-scope route list
| w | r | module file |
|---|---|---|
| `users` | `users_register` | `./api/modules/users/module.php` |
| `users` | `users_get_list` | `./api/modules/users/module.php` |
| `users` | `users_log_me_in` | `./api/modules/users/module.php` |
| `users` | `users_log_me_out` | `./api/modules/users/module.php` |
| `users` | `users_avatar_get` | `./api/modules/users/module.php` |
| `users` | `users_avatar_upload` | `./api/modules/users/module.php` |
| `users` | `users_get_my_details` | `./api/modules/users/module.php` |
| `users` | `users_recover_pwd` | `./api/modules/users/module.php` |
| `users` | `users_update_profile` | `./api/modules/users/module.php` |
| `users` | `users_personal_bg_upload` | `./api/modules/users/module.php` |
| `users` | `users_personal_bg_get` | `./api/modules/users/module.php` |
| `users` | `login_auto` | `./api/modules/users/module.php` |
| `users` | `users_confirm_session_vilidity` | `./api/modules/users/module.php` |
| `clave` | `clave` | `./api/contrib/clave/module.php` |
| `genXML` | `gen_xml_mr` | `./api/contrib/genXML/module.php` |
| `genXML` | `gen_xml_fe` | `./api/contrib/genXML/module.php` |
| `genXML` | `gen_xml_nc` | `./api/contrib/genXML/module.php` |
| `genXML` | `gen_xml_nd` | `./api/contrib/genXML/module.php` |
| `genXML` | `gen_xml_te` | `./api/contrib/genXML/module.php` |
| `genXML` | `gen_xml_fec` | `./api/contrib/genXML/module.php` |
| `genXML` | `gen_xml_fee` | `./api/contrib/genXML/module.php` |
| `genXML` | `test` | `./api/contrib/genXML/module.php` |
| `firmarXML` | `firmar` | `./api/contrib/firmarXML/module.php` |
| `token` | `gettoken` | `./api/contrib/token/module.php` |
| `token` | `refresh` | `./api/contrib/token/module.php` |
| `send` | `json` | `./api/contrib/send/module.php` |
| `send` | `sendMensaje` | `./api/contrib/send/module.php` |
| `send` | `sendTE` | `./api/contrib/send/module.php` |
| `consultar` | `consultarCom` | `./api/contrib/consultar/module.php` |

### In-scope param volume summary
Extracted from `./docs/contracts/params.tsv`:

| w | r | required | optional | total |
|---|---|---:|---:|---:|
| `users` | `users_register` | 6 | 0 | 6 |
| `users` | `users_get_list` | 0 | 1 | 1 |
| `users` | `users_log_me_in` | 1 | 0 | 1 |
| `users` | `users_avatar_get` | 1 | 1 | 2 |
| `users` | `users_recover_pwd` | 1 | 0 | 1 |
| `users` | `login_auto` | 2 | 0 | 2 |
| `clave` | `clave` | 6 | 3 | 9 |
| `genXML` | `gen_xml_mr` | 8 | 2 | 10 |
| `genXML` | `gen_xml_fe` | 24 | 39 | 63 |
| `genXML` | `gen_xml_nc` | 22 | 43 | 65 |
| `genXML` | `gen_xml_nd` | 22 | 43 | 65 |
| `genXML` | `gen_xml_te` | 21 | 43 | 64 |
| `genXML` | `gen_xml_fec` | 25 | 38 | 63 |
| `genXML` | `gen_xml_fee` | 22 | 28 | 50 |
| `firmarXML` | `firmar` | 2 | 1 | 3 |
| `token` | `gettoken` | 4 | 1 | 5 |
| `token` | `refresh` | 3 | 1 | 4 |
| `send` | `json` | 7 | 3 | 10 |
| `send` | `sendMensaje` | 10 | 1 | 11 |
| `send` | `sendTE` | 7 | 1 | 8 |
| `consultar` | `consultarCom` | 3 | 0 | 3 |

For exact required/default values per parameter, use:
- `./docs/contracts/params.tsv` (`param_key`, `default`, `required` columns).
