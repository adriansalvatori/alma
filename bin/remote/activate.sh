#!/usr/bin/env bash
# Activate an uploaded release on the server. Runs ON the server, from inside the release.
#
# Usage: bash releases/<release>/bin/remote/activate.sh --path <deploy root> --release <name>
#        [--version <APP_VERSION>] [--keep 5] [--wp <wp-cli binary>]
#
# Server layout (the deploy root is <path>):
#   releases/<release>/   one directory per deploy (uploaded by rsync)
#   shared/.env           the environment file (create it once, by hand; never commit it)
#   shared/uploads/       wp-content uploads, shared by every release
#   shared/logs/          Acorn's logs (web/app/cache/acorn/logs), kept across releases
#   current -> releases/<release>   the web server's document root is current/web
#
# Steps, in order. Nothing touches `current` until the last step, so a failure leaves the
# previous release serving traffic:
#   1. Link shared/.env, shared/uploads and shared/logs into the release.
#   2. Write the release's APP_VERSION into <release>/.env.local and <release>/VERSION.
#      Bedrock loads .env.local over .env, so the release's value wins and a rollback brings
#      its own version with it.
#   3. Run `wp acorn alma:deploy` (migrations, module tasks, rewrite flush, view cache).
#      A non-zero exit stops here.
#   4. Point `current` at the release atomically.
#   5. Delete all but the newest <keep> releases (never the one `current` points at).
set -euo pipefail

DEPLOY_PATH=""
RELEASE=""
VERSION=""
KEEP=5
WP="${WP_CLI:-wp}"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --path) DEPLOY_PATH="${2%/}" && shift 2 ;;
    --release) RELEASE="$2" && shift 2 ;;
    --version) VERSION="$2" && shift 2 ;;
    --keep) KEEP="$2" && shift 2 ;;
    --wp) WP="$2" && shift 2 ;;
    -h | --help) sed -n '2,/^[^#]/{/^#/!d;s/^# \{0,1\}//;p;}' "$0" && exit 0 ;;
    *) echo "Unknown option: $1" >&2 && exit 2 ;;
  esac
done

fail() {
  echo "✗ $*" >&2
  exit 1
}

[[ -n "$DEPLOY_PATH" && -n "$RELEASE" ]] || fail "--path and --release are required."
[[ "$RELEASE" =~ ^[A-Za-z0-9._-]+$ ]] || fail "Invalid release name: $RELEASE"
[[ "$KEEP" =~ ^[0-9]+$ && "$KEEP" -ge 2 ]] || fail "--keep must be 2 or more (rollback needs a previous release)."

RELEASE_DIR="${DEPLOY_PATH}/releases/${RELEASE}"
SHARED="${DEPLOY_PATH}/shared"

[[ -d "$RELEASE_DIR" ]] || fail "No release at ${RELEASE_DIR}."
[[ -f "${SHARED}/.env" ]] || fail "No ${SHARED}/.env. Create it once from .env.example (see docs/deployment.md)."
command -v "$WP" >/dev/null 2>&1 || fail "WP-CLI ('$WP') is not on the server's PATH. Pass --wp /path/to/wp."

echo "==> Activating ${RELEASE}"

# 1. Shared files. A directory the release shipped is replaced by the link; uploads are never
#    in the artifact (.deployignore), but an empty directory may be.
mkdir -p "${SHARED}/uploads" "${SHARED}/logs" "${RELEASE_DIR}/web/app/cache/acorn"
# The uploads .htaccess (no PHP execution) ships with the code; the symlink below would hide it.
if [[ -f "${RELEASE_DIR}/web/app/uploads/.htaccess" ]]; then
  cp "${RELEASE_DIR}/web/app/uploads/.htaccess" "${SHARED}/uploads/.htaccess"
fi
ln -sfn "${SHARED}/.env" "${RELEASE_DIR}/.env"
rm -rf "${RELEASE_DIR}/web/app/uploads" "${RELEASE_DIR}/web/app/cache/acorn/logs"
ln -sfn "${SHARED}/uploads" "${RELEASE_DIR}/web/app/uploads"
ln -sfn "${SHARED}/logs" "${RELEASE_DIR}/web/app/cache/acorn/logs"

# 2. Release version. Bedrock loads .env.local over .env, so this value wins over anything in
#    shared/.env, and a rollback brings its own .env.local with it.
if [[ -n "$VERSION" ]]; then
  printf "APP_VERSION='%s'\n" "${VERSION//\'/}" >"${RELEASE_DIR}/.env.local"
  printf '%s\n' "$VERSION" >"${RELEASE_DIR}/VERSION"
fi

# 3. Post-deploy tasks, against the release about to go live.
echo "==> wp acorn alma:deploy"
(cd "$RELEASE_DIR" && "$WP" acorn alma:deploy --no-interaction) || fail "alma:deploy failed; 'current' was not changed."

# 4. Switch atomically: build the new link beside the old one, then rename it over the old one.
#    rename(2) is atomic; `mv -T` (GNU) does exactly that. Elsewhere fall back to ln -sfn.
cd "$DEPLOY_PATH"
ln -sfn "releases/${RELEASE}" current.next
if mv -T current.next current 2>/dev/null; then
  :
else
  rm -f current.next
  ln -sfn "releases/${RELEASE}" current
fi
echo "==> current -> releases/${RELEASE}"

# 5. Prune. Release names start with a UTC timestamp, so a name sort is a date sort.
active="$(basename "$(readlink current)")"
releases=(releases/*/)
kept=0
for ((i = ${#releases[@]} - 1; i >= 0; i--)); do
  dir="$(basename "${releases[$i]}")"
  kept=$((kept + 1))
  if [[ "$kept" -gt "$KEEP" && "$dir" != "$active" ]]; then
    rm -rf "releases/${dir}"
    echo "    pruned ${dir}"
  fi
done

echo "✓ Live: ${active}${VERSION:+ (APP_VERSION ${VERSION})}"
