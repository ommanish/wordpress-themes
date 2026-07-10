#!/bin/sh

set -u

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
THEME_DIR="$ROOT_DIR/themes/nexa-pro"
FAILED=0

pass() {
	printf 'PASS: %s\n' "$1"
}

warn() {
	printf 'WARN: %s\n' "$1"
}

fail() {
	printf 'FAIL: %s\n' "$1"
	FAILED=1
}

require_command() {
	if command -v "$1" >/dev/null 2>&1; then
		return 0
	fi

	warn "$1 not available; skipping related check"
	return 1
}

check_file() {
	if [ -f "$1" ]; then
		pass "found ${1#$ROOT_DIR/}"
	else
		fail "missing ${1#$ROOT_DIR/}"
	fi
}

printf 'Validating Nexa Pro theme...\n'

check_file "$THEME_DIR/style.css"
check_file "$THEME_DIR/functions.php"
check_file "$THEME_DIR/index.php"
check_file "$THEME_DIR/header.php"
check_file "$THEME_DIR/footer.php"
check_file "$THEME_DIR/theme.json"

if [ -f "$THEME_DIR/screenshot.png" ]; then
	if command -v sips >/dev/null 2>&1; then
		WIDTH=$(sips -g pixelWidth "$THEME_DIR/screenshot.png" 2>/dev/null | awk '/pixelWidth/ {print $2}')
		HEIGHT=$(sips -g pixelHeight "$THEME_DIR/screenshot.png" 2>/dev/null | awk '/pixelHeight/ {print $2}')

		if [ "$WIDTH" = "1200" ] && [ "$HEIGHT" = "900" ]; then
			pass "screenshot.png dimensions are 1200 x 900"
		else
			fail "screenshot.png dimensions are ${WIDTH:-unknown} x ${HEIGHT:-unknown}; expected 1200 x 900"
		fi
	else
		warn "screenshot.png exists, but sips is unavailable for dimension validation"
	fi
else
	if grep -Eiq 'screenshot\.png.*(not present|missing|release blocker)' "$THEME_DIR/README.md" 2>/dev/null; then
		warn "screenshot.png is missing and documented as a release blocker"
	else
		fail "screenshot.png is missing and no documented release blocker was found"
	fi
fi

for FIELD in "Theme Name" "Theme URI" "Author" "Author URI" "Description" "Version" "Requires at least" "Tested up to" "Requires PHP" "License" "License URI" "Text Domain" "Tags"; do
	if grep -q "^$FIELD:" "$THEME_DIR/style.css"; then
		pass "style.css includes $FIELD"
	else
		fail "style.css missing $FIELD"
	fi
done

STYLE_VERSION=$(awk -F': *' '/^Version:/ {print $2; exit}' "$THEME_DIR/style.css")
PHP_VERSION=$(sed -n "s/^define( 'NEXA_PRO_VERSION', '\([^']*\)' );/\1/p" "$THEME_DIR/functions.php")

if [ "$STYLE_VERSION" = "$PHP_VERSION" ] && [ -n "$STYLE_VERSION" ]; then
	pass "theme version is consistent: $STYLE_VERSION"
else
	fail "theme version mismatch: style.css=${STYLE_VERSION:-missing}, functions.php=${PHP_VERSION:-missing}"
fi

if require_command php; then
	for FILE in $(find "$THEME_DIR" -type f -name '*.php' | sort); do
		if php -l "$FILE" >/dev/null; then
			pass "PHP syntax ${FILE#$ROOT_DIR/}"
		else
			php -l "$FILE"
			fail "PHP syntax ${FILE#$ROOT_DIR/}"
		fi
	done
fi

if require_command node; then
	for FILE in $(find "$THEME_DIR/assets/js" -type f -name '*.js' | sort); do
		if node --check "$FILE" >/dev/null; then
			pass "JavaScript syntax ${FILE#$ROOT_DIR/}"
		else
			node --check "$FILE"
			fail "JavaScript syntax ${FILE#$ROOT_DIR/}"
		fi
	done

	if node -e "JSON.parse(require('fs').readFileSync(process.argv[1], 'utf8'))" "$THEME_DIR/theme.json" >/dev/null; then
		pass "theme.json is valid JSON"
	else
		fail "theme.json is not valid JSON"
	fi
fi

if require_command rg; then
	LEGACY_PATTERN='Con''nexa|con''nexa_|legacy-''runtime|nexapro''\.example''\.com|placeholder production ''URLs'

	if rg -n "$LEGACY_PATTERN" \
		"$THEME_DIR"; then
		fail "forbidden legacy strings found"
	else
		pass "forbidden legacy string scan"
	fi

	if rg -n 'eval\(|unserialize\(' "$THEME_DIR"; then
		fail "unsafe function scan found matches"
	else
		pass "unsafe function scan"
	fi

	if rg -n 'fonts\.(googleapis|gstatic)|@font-face|\.woff|\.woff2|\.ttf|\.otf' "$THEME_DIR"; then
		fail "external or bundled font scan found matches"
	else
		pass "external font URL and bundled font scan"
	fi

	if rg -n 'get_option\(' "$THEME_DIR"/*.php "$THEME_DIR/template-parts"; then
		fail "direct template get_option() scan found matches"
	else
		pass "direct template get_option() scan"
	fi

	STATIC_IDS=$(mktemp "${TMPDIR:-/tmp}/nexa-pro-ids.XXXXXX")
	rg -o 'id="[^"<\?]+"' "$THEME_DIR" --glob '*.php' | sed 's/.*id="//;s/"$//' | sort > "$STATIC_IDS"
	DUPLICATE_IDS=$(uniq -d "$STATIC_IDS")
	rm -f "$STATIC_IDS"

	if [ -n "$DUPLICATE_IDS" ]; then
		printf '%s\n' "$DUPLICATE_IDS"
		fail "duplicate static HTML IDs found"
	else
		pass "duplicate static HTML ID scan"
	fi
fi

if command -v git >/dev/null 2>&1; then
	if ( cd "$ROOT_DIR" && git diff --check ); then
		pass "git diff --check"
	else
		fail "git diff --check"
	fi
fi

if [ "$FAILED" -eq 0 ]; then
	printf 'Nexa Pro validation completed successfully.\n'
	exit 0
fi

printf 'Nexa Pro validation failed.\n'
exit 1
