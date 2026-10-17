# shellcheck shell=bash
#
# Shared helpers for the bin/ scripts. Source it; do not run it.
#
# Written for bash 3.2 (macOS' /bin/bash): no ${var^^}, no associative arrays, no mapfile.

ALMA_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ALMA_THEME="${ALMA_ROOT}/web/app/themes/alma"
export ALMA_ROOT ALMA_THEME

if [[ -t 1 ]]; then
  _bold=$'\033[1m' _red=$'\033[31m' _green=$'\033[32m' _yellow=$'\033[33m' _reset=$'\033[0m'
else
  _bold='' _red='' _green='' _yellow='' _reset=''
fi

info() { printf '%s==>%s %s\n' "$_bold" "$_reset" "$*"; }
ok() { printf '%s✓%s %s\n' "$_green" "$_reset" "$*"; }
warn() { printf '%s!%s %s\n' "$_yellow" "$_reset" "$*" >&2; }
die() {
  printf '%s✗ %s%s\n' "$_red" "$*" "$_reset" >&2
  exit 1
}

# Print the comment block at the top of a script (everything after the shebang up to the first
# non-comment line), without the leading "# ". Every script's --help is its own header.
usage() {
  sed -n '2,/^[^#]/{/^#/!d;s/^# \{0,1\}//;p;}' "$1"
}

require_command() {
  command -v "$1" >/dev/null 2>&1 || die "$1 is required but was not found on the PATH. $2"
}

upper() { printf '%s' "$1" | tr '[:lower:]-' '[:upper:]_'; }

# Read a key from a .env file exactly as Bedrock parses it.
env_get() {
  php "${ALMA_ROOT}/bin/lib/env.php" get "$1" "$2"
}

# Load the SSH target for an environment name (staging, production, ...).
#
# Values come from the environment first (CI sets DEPLOY_SSH_HOST and friends from secrets),
# then from .env.deploy at the repository root:
#
#   STAGING_SSH_HOST=example.com        PRODUCTION_SSH_HOST=example.com
#   STAGING_SSH_USER=alma               ...
#   STAGING_SSH_PORT=22
#   STAGING_PATH=/home/alma/staging     the deploy root (releases/, shared/, current)
#   STAGING_URL=https://staging.example.com
#   STAGING_WP=wp                       optional: the WP-CLI binary on the server
#
# Sets TARGET_ENV, TARGET_HOST, TARGET_USER, TARGET_PORT, TARGET_PATH, TARGET_URL, TARGET_WP and
# SSH_OPTS (one string; word-split on purpose when used as `ssh $SSH_OPTS`).
load_target() {
  local env="$1" prefix value key

  [[ -n "$env" ]] || die "Name an environment, e.g. staging or production."
  [[ "$env" =~ ^[a-z][a-z0-9-]*$ ]] || die "Invalid environment name: $env"

  if [[ -f "${ALMA_ROOT}/.env.deploy" ]]; then
    # shellcheck disable=SC1091
    set -a && . "${ALMA_ROOT}/.env.deploy" && set +a
  fi

  prefix="$(upper "$env")"
  TARGET_ENV="$env"

  for key in SSH_HOST SSH_USER SSH_PORT PATH URL WP; do
    value="DEPLOY_${key}"
    value="${!value:-}"

    if [[ -z "$value" ]]; then
      value="${prefix}_${key}"
      value="${!value:-}"
    fi

    case "$key" in
      SSH_HOST) TARGET_HOST="$value" ;;
      SSH_USER) TARGET_USER="$value" ;;
      SSH_PORT) TARGET_PORT="${value:-22}" ;;
      PATH) TARGET_PATH="${value%/}" ;;
      URL) TARGET_URL="${value%/}" ;;
      WP) TARGET_WP="${value:-wp}" ;;
    esac
  done

  [[ -n "$TARGET_HOST" ]] || die "No SSH host for '$env'. Set ${prefix}_SSH_HOST in .env.deploy (see .env.deploy.example)."
  [[ -n "$TARGET_USER" ]] || die "No SSH user for '$env'. Set ${prefix}_SSH_USER in .env.deploy."
  [[ -n "$TARGET_PATH" ]] || die "No deploy path for '$env'. Set ${prefix}_PATH in .env.deploy."

  SSH_OPTS="-p ${TARGET_PORT} -o BatchMode=yes -o ConnectTimeout=15"

  if [[ -n "${DEPLOY_SSH_KEY_FILE:-}" ]]; then
    SSH_OPTS="${SSH_OPTS} -i ${DEPLOY_SSH_KEY_FILE} -o IdentitiesOnly=yes"
  fi

  export TARGET_ENV TARGET_HOST TARGET_USER TARGET_PORT TARGET_PATH TARGET_URL TARGET_WP SSH_OPTS
}

# Run a command on the target over SSH. Arguments are joined into one remote command line, so
# quote anything that needs it with remote_quote.
remote() {
  # shellcheck disable=SC2086
  ssh $SSH_OPTS "${TARGET_USER}@${TARGET_HOST}" "$*"
}

# Quote a value for safe use inside a remote command line.
remote_quote() {
  printf "'%s'" "$(printf '%s' "$1" | sed "s/'/'\\\\''/g")"
}

# Refuse to continue when the local install is a production one.
refuse_local_production() {
  local env_file="${ALMA_ROOT}/.env" wp_env

  [[ -f "$env_file" ]] || die "No .env at ${env_file}. Run bin/setup.sh first."
  wp_env="$(env_get "$env_file" WP_ENV)"

  if [[ "${wp_env:-production}" == "production" ]]; then
    die "The local WP_ENV is '${wp_env:-unset (production)}'. Refusing to overwrite a production database."
  fi
}

timestamp() { date -u +%Y%m%d%H%M%S; }
