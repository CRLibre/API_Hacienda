#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="${1:-.}"
OUT_DIR="${2:-${ROOT_DIR}/docs/contracts}"

mkdir -p "${OUT_DIR}"

ROUTES_TSV="${OUT_DIR}/routes.tsv"
PARAMS_TSV="${OUT_DIR}/params.tsv"

MODULE_FILES=()
while IFS= read -r file; do
  MODULE_FILES+=("${file}")
done < <(find "${ROOT_DIR}/api/modules" "${ROOT_DIR}/api/contrib" -name module.php | sort)

perl -e '
use strict;
use warnings;

for my $file (@ARGV) {
    my @parts = split(m{/}, $file);
    my $w = $parts[-2];
    open(my $fh, "<", $file) or next;
    my $route = q{};
    while (my $line = <$fh>) {
        if ($line =~ /'\''r'\''\s*=>\s*'\''([^'\'']+)'\''/) {
            $route = $1;
            print join("\t", "ROUTE", $w, $route, $file) . "\n";
            next;
        }
        if ($route ne q{} && $line =~ /array\("key"\s*=>\s*"([^"]+)",\s*"def"\s*=>\s*"([^"]*)",\s*"req"\s*=>\s*(true|false)\)/) {
            my ($key, $def, $req) = ($1, $2, $3);
            print join("\t", "PARAM", $w, $route, $key, $def, $req, $file) . "\n";
        }
    }
    close($fh);
}
' "${MODULE_FILES[@]}" \
| awk -F'\t' '
    $1=="ROUTE"{ print $2 "\t" $3 "\t" $4 > "'"${ROUTES_TSV}"'"; next }
    $1=="PARAM"{ print $2 "\t" $3 "\t" $4 "\t" $5 "\t" $6 "\t" $7 > "'"${PARAMS_TSV}"'" }
'

{
  echo -e "w\tr\tmodule_file"
  sort "${ROUTES_TSV}"
} > "${ROUTES_TSV}.tmp" && mv "${ROUTES_TSV}.tmp" "${ROUTES_TSV}"

{
  echo -e "w\tr\tparam_key\tdefault\trequired\tmodule_file"
  sort "${PARAMS_TSV}"
} > "${PARAMS_TSV}.tmp" && mv "${PARAMS_TSV}.tmp" "${PARAMS_TSV}"

echo "Generated:"
echo "  ${ROUTES_TSV}"
echo "  ${PARAMS_TSV}"
