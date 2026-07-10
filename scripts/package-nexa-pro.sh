#!/bin/sh

set -u

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
THEME_DIR="$ROOT_DIR/themes/nexa-pro"
DIST_DIR="$ROOT_DIR/dist"
VERSION=$(awk -F': *' '/^Version:/ {print $2; exit}' "$THEME_DIR/style.css")
ZIP_NAME="nexa-pro-$VERSION.zip"
ZIP_PATH="$DIST_DIR/$ZIP_NAME"
TMP_DIR=$(mktemp -d "${TMPDIR:-/tmp}/nexa-pro-package.XXXXXX")

cleanup() {
	rm -rf "$TMP_DIR"
}
trap cleanup EXIT INT TERM

printf 'Running Nexa Pro validation before packaging...\n'
"$ROOT_DIR/scripts/validate-theme.sh"

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

mkdir -p "$DIST_DIR"
mkdir -p "$TMP_DIR/nexa-pro"

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
	"$THEME_DIR/" "$TMP_DIR/nexa-pro/"

rm -f "$ZIP_PATH"

( cd "$TMP_DIR" && zip -rq "$ZIP_PATH" nexa-pro )

if [ ! -f "$ZIP_PATH" ]; then
	printf 'FAIL: package was not created.\n'
	exit 1
fi

if unzip -Z1 "$ZIP_PATH" | awk 'index($0, "nexa-pro/") != 1 { bad = 1 } END { exit bad }'; then
	printf 'PASS: ZIP root is nexa-pro/.\n'
else
	printf 'FAIL: ZIP contains files outside the nexa-pro/ root.\n'
	exit 1
fi

for REQUIRED in \
	"nexa-pro/style.css" \
	"nexa-pro/functions.php" \
	"nexa-pro/index.php" \
	"nexa-pro/header.php" \
	"nexa-pro/footer.php" \
	"nexa-pro/theme.json" \
	"nexa-pro/README.md" \
	"nexa-pro/CHANGELOG.md" \
	"nexa-pro/LICENSE" \
	"nexa-pro/CREDITS.md"; do
	if unzip -Z1 "$ZIP_PATH" | grep -qx "$REQUIRED"; then
		printf 'PASS: packaged %s\n' "$REQUIRED"
	else
		printf 'FAIL: missing required package file %s\n' "$REQUIRED"
		exit 1
	fi
done

if [ -f "$THEME_DIR/screenshot.png" ]; then
	if unzip -Z1 "$ZIP_PATH" | grep -qx 'nexa-pro/screenshot.png'; then
		printf 'PASS: packaged screenshot.png\n'
	else
		printf 'FAIL: screenshot.png exists but was not packaged.\n'
		exit 1
	fi
else
	printf 'WARN: screenshot.png is missing; documented release blocker remains.\n'
fi

printf 'Created %s\n' "${ZIP_PATH#$ROOT_DIR/}"
printf 'Inspect with: unzip -l %s\n' "${ZIP_PATH#$ROOT_DIR/}"
