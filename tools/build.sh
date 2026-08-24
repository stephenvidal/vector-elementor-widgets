#!/usr/bin/env bash
# Vector Elementor Widgets — build script.
#
# Produces a versioned, installable WordPress plugin ZIP.
#
# Layout produced:
#   builds/vector-elementor-widgets-<version>.zip
#   with the contents being the plugin's source tree rooted at
#   `vector-elementor-widgets/` (so WP sees the standard plugin folder).
#
# Excludes:
#   vendor/                  — rebuilt production-only for dist; omitted from source checkpoints
#   builds/                  — no recursive archive
#   dist/                    — previously cut release artifacts
#   reports/                 — diagnostics output, not part of the plugin
#   .phpunit.cache/          — PHPUnit cache
#   tools/                   — dev tooling, not part of the plugin
#   .git/, .github/          — VCS metadata
#   *.log, *.bak, *~         — transient files
#   .env                     — secrets
#
# Usage:
#   bash tools/build.sh [phase-label]
#   bash tools/build.sh phase-0
#   bash tools/build.sh dist
#   bash tools/build.sh release

set -euo pipefail

# Phase label is the first arg, default phase-0.
PHASE="${1:-phase-0}"

# Resolve plugin root.
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PLUGIN_ROOT="$( cd "${SCRIPT_DIR}/.." && pwd )"
cd "${PLUGIN_ROOT}"

BUILDS_DIR="${PLUGIN_ROOT}/builds"
DIST_DIR="${PLUGIN_ROOT}/dist"
mkdir -p "${BUILDS_DIR}"

# The version comes from the plugin header, which is the single source of
# truth WordPress itself reads.
VERSION="$(sed -n 's/^ \* Version:[[:space:]]*\(.*\)$/\1/p' "${PLUGIN_ROOT}/vector-elementor-widgets.php" | head -n1 | tr -d '[:space:]')"
if [[ -z "${VERSION}" ]]; then
  echo "build: ERROR — could not read Version from the plugin header" >&2
  exit 1
fi

# `release` cuts the full set: the installable package, a source archive, and
# checksums covering both. It re-invokes this script rather than duplicating
# the staging logic, so the two archives are built by exactly the same code.
if [[ "${PHASE}" == "release" ]]; then
  mkdir -p "${DIST_DIR}"

  bash "${BASH_SOURCE[0]}" dist
  bash "${BASH_SOURCE[0]}" source

  mv -f "${BUILDS_DIR}/vector-elementor-widgets-${VERSION}.zip" "${DIST_DIR}/"
  mv -f "${BUILDS_DIR}/vector-elementor-widgets-source-source.zip" \
        "${DIST_DIR}/vector-elementor-widgets-${VERSION}-source.zip"

  ( cd "${DIST_DIR}" && sha256sum \
      "vector-elementor-widgets-${VERSION}.zip" \
      "vector-elementor-widgets-${VERSION}-source.zip" > checksums.txt )

  echo ""
  echo "build: release artifacts for ${VERSION}"
  ( cd "${DIST_DIR}" && ls -l vector-elementor-widgets-${VERSION}*.zip checksums.txt )
  echo ""
  cat "${DIST_DIR}/checksums.txt"
  echo ""
  echo "build: OK"
  exit 0
fi

if [[ "${PHASE}" == "dist" ]]; then
  ZIP_NAME="vector-elementor-widgets-${VERSION}.zip"
else
  ZIP_NAME="vector-elementor-widgets-${PHASE}-source.zip"
fi

ZIP_PATH="${BUILDS_DIR}/${ZIP_NAME}"
TMP_DIR=$(mktemp -d)
trap 'rm -rf "${TMP_DIR}"' EXIT

# Stage the plugin into a directory named vector-elementor-widgets/.
STAGE="${TMP_DIR}/vector-elementor-widgets"
mkdir -p "${STAGE}"

# Use rsync for fine-grained include/exclude control.
RSYNC_EXCLUDES=(
  --exclude='vendor/'
  --exclude='builds/'
  --exclude='dist/'
  --exclude='reports/'
  --exclude='.phpunit.cache/'
  --exclude='tools/'
  --exclude='.git/'
  --exclude='.github/'
  --exclude='.gitignore'
  --exclude='.hermes/'
  --exclude='.env'
  --exclude='.env.example'
  --exclude='*.log'
  --exclude='*.bak'
  --exclude='*~'
  --exclude='*.swp'
  --exclude='tmp-*'
  --exclude='__pycache__/'
  --exclude='node_modules/'
  --exclude='package-lock.json'
)

rsync -a "${RSYNC_EXCLUDES[@]}" "${PLUGIN_ROOT}/" "${STAGE}/"

# Production packages must be self-contained and reproducible from the lock file.
if [[ "${PHASE}" == "dist" ]]; then
  rm -rf "${STAGE}/tests"
  COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --working-dir="${STAGE}" \
    --no-dev \
    --no-interaction \
    --classmap-authoritative
  if [[ -d "${STAGE}/tests" ]]; then
    echo "build: ERROR — test tree leaked into dist staging" >&2
    exit 1
  fi
fi

# Verify the bootstrap file made it in.
if [[ ! -f "${STAGE}/vector-elementor-widgets.php" ]]; then
  echo "build: ERROR — bootstrap file vector-elementor-widgets.php missing from staging" >&2
  exit 1
fi

# Build the zip. `zip` ADDS to an existing archive rather than replacing it,
# so remove the target first so the artifact reflects only this build.
rm -f "${ZIP_PATH}"
( cd "${TMP_DIR}" && zip -qr "${ZIP_PATH}" vector-elementor-widgets )

echo "build: ${ZIP_PATH}"
ls -l "${ZIP_PATH}"
echo "build: OK"
