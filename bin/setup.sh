#!/usr/bin/env bash
# First-time local setup for an alma project. Safe to re-run: every step checks before it acts.
#
# Usage: bin/setup.sh [--yes] [--with-woocommerce] [--help]
#
#   --yes               Accept every default without prompting (also used when there is no TTY).
#   --with-woocommerce  Install and activate WooCommerce (enables the optional Commerce module).
#
# Steps:
#   1. Checks PHP 8.4+, Composer, Node 20.19+/22.12+, npm and WP-CLI.
#   2. Writes .env from .env.example (database, WP_HOME), quoting values safely.
#   3. Generates the WordPress salts and APP_KEY locally (only where they are missing).
#   4. composer install (root and theme), npm install + npm run build (theme).
#   5. Creates the database, installs WordPress with a random admin password (printed once).
#   6. Activates the alma theme and the bundled plugins, sets /%postname%/ permalinks.
#   7. Creates the Home page from the alma/home pattern and makes it the front page.
#   8. Runs `wp acorn alma:deploy` (migrations, rewrite flush, view cache).
#
# Defaults can be preset with ALMA_SETUP_URL, ALMA_SETUP_DB_NAME, ALMA_SETUP_DB_USER,
# ALMA_SETUP_DB_PASSWORD, ALMA_SETUP_DB_HOST, ALMA_SETUP_TITLE, ALMA_SETUP_ADMIN_USER and
# ALMA_SETUP_ADMIN_EMAIL.
set -euo pipefail

# shellcheck source=bin/lib/common.sh
. "$(dirname "$0")/lib/common.sh"

ASSUME_YES=false
WITH_WOOCOMMERCE=false

for arg in "$@"; do
  case "$arg" in
    -h | --help) usage "$0" && exit 0 ;;
    -y | --yes) ASSUME_YES=true ;;
    --with-woocommerce) WITH_WOOCOMMERCE=true ;;
    *) die "Unknown option: $arg (see --help)" ;;
  esac
done

[[ -t 0 ]] || ASSUME_YES=true

cd "$ALMA_ROOT"

# ask <variable> <question> <default>
ask() {
  local answer=""

  if [[ "$ASSUME_YES" == false ]]; then
    read -r -p "$2 [$3]: " answer
  fi

  printf -v "$1" '%s' "${answer:-$3}"
}

# ask_secret <variable> <question> (empty default)
ask_secret() {
  local answer=""

  if [[ "$ASSUME_YES" == false ]]; then
    read -r -s -p "$2: " answer
    echo
  fi

  printf -v "$1" '%s' "$answer"
}

# confirm <question> <default y|n>
confirm() {
  local answer=""

  if [[ "$ASSUME_YES" == true ]]; then
    [[ "$2" == "y" ]]
    return
  fi

  local hint="y/N"
  [[ "$2" == "y" ]] && hint="Y/n"
  read -r -p "$1 [${hint}]: " answer
  answer="${answer:-$2}"
  [[ "$answer" =~ ^[Yy] ]]
}

folder="$(basename "$ALMA_ROOT")"
slug="$(printf '%s' "$folder" | tr -c '[:alnum:]\n' '_' | tr '[:upper:]' '[:lower:]')"

# 1. Prerequisites ---------------------------------------------------------------------------------

info "Checking prerequisites"
require_command php "Install PHP 8.4 or newer."
php -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' || die "PHP 8.4 or newer is required (found $(php -r 'echo PHP_VERSION;'))."
php -m | grep -qi '^pdo_mysql$' || die "The pdo_mysql PHP extension is required."
require_command composer "See https://getcomposer.org/download/."
require_command node "Install Node 22 LTS."
node -e 'const [a,b]=process.versions.node.split(".").map(Number); process.exit((a===20&&b>=19)||(a===22&&b>=12)||a>22?0:1)' \
  || die "Node 20.19+ or 22.12+ is required (found $(node --version))."
require_command npm "It ships with Node."
require_command wp "See https://wp-cli.org/#installing."
ok "PHP $(php -r 'echo PHP_VERSION;'), Composer, Node $(node --version), WP-CLI"

# 2-3. Environment --------------------------------------------------------------------------------

if [[ -f .env ]]; then
  ok ".env exists; keeping its values"
else
  info "Creating .env"
  ask site_url "Site URL" "${ALMA_SETUP_URL:-http://${folder}.test}"
  ask db_name "Database name" "${ALMA_SETUP_DB_NAME:-$slug}"
  ask db_user "Database user" "${ALMA_SETUP_DB_USER:-root}"
  if [[ -n "${ALMA_SETUP_DB_PASSWORD+set}" ]]; then
    db_password="$ALMA_SETUP_DB_PASSWORD"
  else
    ask_secret db_password "Database password (empty for none)"
  fi
  ask db_host "Database host" "${ALMA_SETUP_DB_HOST:-127.0.0.1}"

  php bin/lib/env.php init .env .env.example
  php bin/lib/env.php set .env \
    "WP_ENV=development" \
    "WP_HOME=${site_url%/}" \
    "DB_NAME=${db_name}" \
    "DB_USER=${db_user}" \
    "DB_PASSWORD=${db_password}" \
    "DB_HOST=${db_host}"
  ok ".env written (mode 600)"
fi

generated="$(php bin/lib/env.php secrets .env)"
if [[ "$generated" == "0" ]]; then
  ok "Salts and APP_KEY already set"
else
  ok "Generated ${generated} salts/keys"
fi

site_url="$(env_get .env WP_HOME)"
[[ -n "$site_url" ]] || die "WP_HOME is empty in .env."

# 4. Dependencies ---------------------------------------------------------------------------------

acf_required() { grep -q '"wpengine/advanced-custom-fields-pro"' composer.json; }
acf_auth_present() {
  [[ -f auth.json ]] || [[ -n "${COMPOSER_AUTH:-}" ]] \
    || composer config --global http-basic.connect.advancedcustomfields.com >/dev/null 2>&1
}

if acf_required && ! acf_auth_present; then
  warn "ACF Pro needs your license key before Composer can download it."
  warn "  cp auth.json.example auth.json   # then put your key and site URL in it"
  warn "  composer update wpengine/advanced-custom-fields-pro"
  warn "Continuing without it; blocks stay unavailable until ACF Pro is installed."
fi

info "Installing Composer dependencies (root)"
composer install --no-interaction --no-progress
info "Installing Composer dependencies (theme)"
composer install --no-interaction --no-progress --working-dir="$ALMA_THEME"

info "Installing and building front-end assets"
npm install --prefix "$ALMA_THEME" --no-audit --no-fund
npm run build --prefix "$ALMA_THEME"

# 5. Database and WordPress -----------------------------------------------------------------------

info "Preparing the database"
php bin/lib/database.php create .env

admin_password=""

if wp core is-installed >/dev/null 2>&1; then
  ok "WordPress is already installed"
else
  host="$(printf '%s' "$site_url" | sed -E 's#^[a-z]+://##; s#[:/].*$##')"
  ask title "Site title" "${ALMA_SETUP_TITLE:-$folder}"
  ask admin_user "Admin username" "${ALMA_SETUP_ADMIN_USER:-admin}"
  ask admin_email "Admin email" "${ALMA_SETUP_ADMIN_EMAIL:-admin@${host}}"
  admin_password="$(php -r 'echo rtrim(strtr(base64_encode(random_bytes(18)), "+/", "-_"), "=");')"

  wp core install --url="$site_url" --title="$title" --admin_user="$admin_user" \
    --admin_email="$admin_email" --admin_password="$admin_password" --skip-email
  ok "WordPress installed"
fi

# 6. Theme, plugins, permalinks -------------------------------------------------------------------

wp theme activate alma

for plugin in autodescription advanced-custom-fields-pro mcp-adapter; do
  if wp plugin is-installed "$plugin" >/dev/null 2>&1; then
    wp plugin activate "$plugin" --quiet
    ok "Plugin active: $plugin"
  else
    warn "Plugin not installed: $plugin"
  fi
done

if [[ "$WITH_WOOCOMMERCE" == false ]] && ! wp plugin is-installed woocommerce >/dev/null 2>&1; then
  confirm "Install WooCommerce (the optional Commerce module)?" n && WITH_WOOCOMMERCE=true
fi

if [[ "$WITH_WOOCOMMERCE" == true ]]; then
  if ! wp plugin is-installed woocommerce >/dev/null 2>&1; then
    info "Adding WooCommerce through Composer"
    composer require wpackagist-plugin/woocommerce --no-interaction --no-progress
  fi
  wp plugin activate woocommerce --quiet
  ok "WooCommerce active"
fi

wp rewrite structure '/%postname%/' --quiet
ok "Permalinks: /%postname%/"

# 7. Home page ------------------------------------------------------------------------------------

home_id="$(wp post list --post_type=page --name=home --post_status=any --format=ids)"

if [[ -z "$home_id" ]]; then
  home_id="$(wp post create --post_type=page --post_title=Home --post_name=home --post_status=publish \
    --post_content='<!-- wp:pattern {"slug":"alma/home"} /-->' --porcelain)"
  ok "Created the Home page (#${home_id}) from the alma/home pattern"
fi

wp option update show_on_front page --quiet
wp option update page_on_front "$home_id" --quiet

# 8. Post-deploy tasks ----------------------------------------------------------------------------

info "Running wp acorn alma:deploy"
wp acorn alma:deploy

echo
ok "Alma is ready at ${site_url}"
if [[ -n "$admin_password" ]]; then
  printf '\n  Admin:    %s/wp/wp-admin/\n  Username: %s\n  Password: %s\n\n' "$site_url" "$admin_user" "$admin_password"
  warn "This password is shown once. Store it in your password manager now."
fi
echo "  Next: npm run dev (Vite with hot reload), composer check (everything CI runs)."
