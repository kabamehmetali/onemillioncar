#!/usr/bin/env bash

set -Eeuo pipefail

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
DEPLOY_DIR="${1:-${HOME:-}/public_html}"

fail() {
    printf 'Deployment failed: %s\n' "$1" >&2
    exit 1
}

[[ -n "${HOME:-}" ]] || fail 'HOME is not set.'
[[ -n "$DEPLOY_DIR" ]] || fail 'No deployment directory was supplied.'
[[ "$DEPLOY_DIR" != "/" && "$DEPLOY_DIR" != "$HOME" ]] || fail 'Refusing to deploy to an unsafe path.'

case "$DEPLOY_DIR/" in
    "$HOME"/*/) ;;
    *) fail 'The deployment directory must be inside the cPanel account home directory.' ;;
esac

command -v rsync >/dev/null 2>&1 || fail 'rsync is required but is not available.'
command -v php >/dev/null 2>&1 || fail 'PHP CLI is required but is not available.'

mkdir -p "$DEPLOY_DIR"
DEPLOY_DIR="$(cd "$DEPLOY_DIR" && pwd -P)"

case "$DEPLOY_DIR/" in
    "$HOME"/*/) ;;
    *) fail 'The resolved deployment directory is outside the cPanel account home directory.' ;;
esac

[[ "$SOURCE_DIR" != "$DEPLOY_DIR" ]] || fail 'The cPanel repository and document root must be different directories.'

printf 'Validating PHP files...\n'
while IFS= read -r -d '' php_file; do
    php -l "$php_file" >/dev/null
done < <(
    find "$SOURCE_DIR" \
        -path "$SOURCE_DIR/.git" -prune -o \
        -path "$SOURCE_DIR/images" -prune -o \
        -path "$SOURCE_DIR/tools" -prune -o \
        -path "$SOURCE_DIR/uploads" -prune -o \
        -type f -name '*.php' -print0
)

printf 'Deploying application to %s...\n' "$DEPLOY_DIR"
mkdir -p \
    "$DEPLOY_DIR/admin" \
    "$DEPLOY_DIR/assets" \
    "$DEPLOY_DIR/includes" \
    "$DEPLOY_DIR/uploads/vehicles"

# Mirror version-controlled application directories. The production database
# config and customer-uploaded vehicle photos are deliberately preserved.
rsync -a --delete "$SOURCE_DIR/admin/" "$DEPLOY_DIR/admin/"
rsync -a --delete "$SOURCE_DIR/assets/" "$DEPLOY_DIR/assets/"
rsync -a --delete --exclude='config.php' "$SOURCE_DIR/includes/" "$DEPLOY_DIR/includes/"

find "$SOURCE_DIR" -maxdepth 1 -type f -name '*.php' -exec cp -p {} "$DEPLOY_DIR/" \;
cp -p "$SOURCE_DIR/.htaccess" "$DEPLOY_DIR/.htaccess"
cp -p "$SOURCE_DIR/uploads/.htaccess" "$DEPLOY_DIR/uploads/.htaccess"

printf 'Deployment complete: %s\n' "$(git -C "$SOURCE_DIR" rev-parse --short HEAD 2>/dev/null || printf 'unknown revision')"
