#!/bin/sh

set -u

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
PLUGIN_DIR="$ROOT_DIR/plugins/nexa-pro-core"
DIST_DIR="$ROOT_DIR/dist"
VERSION=$(awk -F': *' '/^ \* Version:/ {print $2; exit}' "$PLUGIN_DIR/nexa-pro-core.php")
ZIP_NAME="nexa-pro-core-$VERSION.zip"
ZIP_PATH="$DIST_DIR/$ZIP_NAME"
TMP_DIR=$(mktemp -d "${TMPDIR:-/tmp}/nexa-pro-core-package.XXXXXX")

cleanup() {
	rm -rf "$TMP_DIR"
}
trap cleanup EXIT INT TERM

if [ -z "$VERSION" ]; then
	printf 'FAIL: plugin version could not be detected.\n'
	exit 1
fi

if ! command -v php >/dev/null 2>&1; then
	printf 'FAIL: php command is required for packaging.\n'
	exit 1
fi

if ! command -v zip >/dev/null 2>&1; then
	printf 'FAIL: zip command is required for packaging.\n'
	exit 1
fi

if ! command -v unzip >/dev/null 2>&1; then
	printf 'FAIL: unzip command is required for ZIP verification.\n'
	exit 1
fi

if ! command -v rsync >/dev/null 2>&1; then
	printf 'FAIL: rsync command is required for packaging.\n'
	exit 1
fi

printf 'Running Nexa Pro Core syntax validation before packaging...\n'
for FILE in $(find "$PLUGIN_DIR" -type f -name '*.php' | sort); do
	if php -l "$FILE" >/dev/null; then
		printf 'PASS: PHP syntax %s\n' "${FILE#$ROOT_DIR/}"
	else
		php -l "$FILE"
		printf 'FAIL: PHP syntax %s\n' "${FILE#$ROOT_DIR/}"
		exit 1
	fi
done

mkdir -p "$DIST_DIR"
mkdir -p "$TMP_DIR/nexa-pro-core"

rsync -a \
	--exclude '.DS_Store' \
	--exclude '.git' \
	--exclude '.git*' \
	--exclude 'node_modules/' \
	--exclude 'tests/' \
	--exclude '*.map' \
	--exclude '*.log' \
	--exclude '*.tmp' \
	--exclude '*.swp' \
	--exclude 'tmp/' \
	--exclude 'cache/' \
	"$PLUGIN_DIR/" "$TMP_DIR/nexa-pro-core/"

rm -f "$ZIP_PATH"

( cd "$TMP_DIR" && zip -rq "$ZIP_PATH" nexa-pro-core )

if [ ! -f "$ZIP_PATH" ]; then
	printf 'FAIL: package was not created.\n'
	exit 1
fi

if unzip -Z1 "$ZIP_PATH" | awk 'index($0, "nexa-pro-core/") != 1 { bad = 1 } END { exit bad }'; then
	printf 'PASS: ZIP root is nexa-pro-core/.\n'
else
	printf 'FAIL: ZIP contains files outside the nexa-pro-core/ root.\n'
	exit 1
fi

for REQUIRED in \
	"nexa-pro-core/nexa-pro-core.php" \
	"nexa-pro-core/README.md" \
	"nexa-pro-core/includes/class-storage.php" \
	"nexa-pro-core/admin/class-builder-admin.php"; do
	if unzip -Z1 "$ZIP_PATH" | grep -qx "$REQUIRED"; then
		printf 'PASS: packaged %s\n' "$REQUIRED"
	else
		printf 'FAIL: missing required package file %s\n' "$REQUIRED"
		exit 1
	fi
done

if unzip -Z1 "$ZIP_PATH" | grep -q '^nexa-pro-core/tests/'; then
	printf 'FAIL: tests should not be included in the plugin package.\n'
	exit 1
fi

printf 'Created %s\n' "${ZIP_PATH#$ROOT_DIR/}"
printf 'Inspect with: unzip -l %s\n' "${ZIP_PATH#$ROOT_DIR/}"
