#!/usr/bin/env bash
# Point `current` back at an earlier release. Runs ON the server.
#
# Usage: bash <deploy root>/current/bin/remote/rollback.sh --path <deploy root> [--release <name>]
#
# Without --release, goes back to the release just before the active one. Only the symlink
# moves: migrations are not reversed, so a rollback across a schema change must be planned
# (migrations should stay backward compatible with the previous release for exactly this).
# Lists the available releases when the target cannot be found.
set -euo pipefail

DEPLOY_PATH=""
TARGET=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --path) DEPLOY_PATH="${2%/}" && shift 2 ;;
    --release) TARGET="$2" && shift 2 ;;
    -h | --help) sed -n '2,/^[^#]/{/^#/!d;s/^# \{0,1\}//;p;}' "$0" && exit 0 ;;
    *) echo "Unknown option: $1" >&2 && exit 2 ;;
  esac
done

[[ -n "$DEPLOY_PATH" ]] || { echo "✗ --path is required." >&2; exit 1; }
cd "$DEPLOY_PATH"
[[ -L current ]] || { echo "✗ ${DEPLOY_PATH}/current is not a symlink; nothing to roll back." >&2; exit 1; }

active="$(basename "$(readlink current)")"

if [[ -z "$TARGET" ]]; then
  # The newest release older than the active one (names start with a UTC timestamp, and globs
  # expand in sorted order).
  previous=""
  for dir in releases/*/; do
    dir="$(basename "$dir")"
    if [[ "$dir" == "$active" ]]; then
      TARGET="$previous"
      break
    fi
    previous="$dir"
  done
fi

if [[ -z "$TARGET" || ! -d "releases/${TARGET}" ]]; then
  echo "✗ No release to roll back to. Available releases (oldest first):" >&2
  for dir in releases/*/; do basename "$dir" >&2; done
  exit 1
fi

ln -sfn "releases/${TARGET}" current.next
if mv -T current.next current 2>/dev/null; then
  :
else
  rm -f current.next
  ln -sfn "releases/${TARGET}" current
fi

echo "✓ current -> releases/${TARGET} (was ${active})"
