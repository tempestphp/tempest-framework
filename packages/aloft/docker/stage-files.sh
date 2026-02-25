#!/usr/bin/env bash
# stage-files.sh
#
# Runs inside the frankenphp intermediate stage during docker build.
# Collects every runtime file needed in the final distroless image into
# /tmp/staging, preserving full filesystem paths so the Dockerfile can
# use a single COPY --from=frankenphp /tmp/staging/ /
#
# Steps:
#   1. lddtree --copy-to-tree for extensions, php, frankenphp
#   2. Resolve soname symlinks → copy versioned targets beside them
#   3. Normalise lib/ and lib64/ → usr/lib/ and usr/lib64/ (distroless symlink collision)
#   4. Explicit copies for dlopen'd plugins lddtree cannot discover
#   5. PHP config, ld config, mime types, frankenphp runtime files

set -euo pipefail

STAGING=/tmp/staging
ARCH=$(uname -m | sed 's/x86_64/x86_64-linux-gnu/;s/aarch64/aarch64-linux-gnu/')
DEB_DIR=/tmp/debs
DEB_EXTRACT=/tmp/deb-extract

mkdir -p "${STAGING}" "${DEB_DIR}"

# ---------------------------------------------------------------------------
# 1. lddtree — ELF dependency tree for all extensions + binaries
#
# This gives us the soname symlinks and the correct set of libraries needed.
# The versioned files behind those symlinks are fetched via apt in step 2.
# ---------------------------------------------------------------------------

find /usr/local/lib/php/extensions/ -name '*.so' -print0 \
  | xargs -0 -r lddtree --copy-to-tree "${STAGING}" 2>/dev/null || true

lddtree --copy-to-tree "${STAGING}" /usr/local/bin/php        2>/dev/null || true
lddtree --copy-to-tree "${STAGING}" /usr/local/bin/frankenphp 2>/dev/null || true
lddtree --copy-to-tree "${STAGING}" /usr/local/lib/libphp.so  2>/dev/null || true

# Normalise lib/ and lib64/ → usr/lib/ and usr/lib64/ before package resolution.
# lddtree follows the /lib -> usr/lib and /lib64 -> usr/lib64 symlinks on the
# source system and may write files under staging/lib/ or staging/lib64/.
# distroless has both as symlinks so COPY / would collide — merge into usr/.
if [ -d "${STAGING}/lib" ]; then
  mkdir -p "${STAGING}/usr/lib"
  cp -a "${STAGING}/lib/." "${STAGING}/usr/lib/"
  rm -rf "${STAGING}/lib"
fi

if [ -d "${STAGING}/lib64" ]; then
  mkdir -p "${STAGING}/usr/lib64"
  cp -a "${STAGING}/lib64/." "${STAGING}/usr/lib64/"
  rm -rf "${STAGING}/lib64"
fi

# ---------------------------------------------------------------------------
# 2. apt-get download + dpkg-deb extract
#
# lddtree creates relative soname symlinks inside staging, so the versioned
# file each symlink points to only exists on the source system, not in
# staging. Rather than trying to resolve paths manually, we find which
# Debian package owns each collected .so file, download that package, and
# extract it — giving us both the soname symlink and the versioned file
# with no path gymnastics required.
# ---------------------------------------------------------------------------

# Collect owning packages for all .so files lddtree placed in staging
find "${STAGING}/usr/lib" -name "*.so*" 2>/dev/null \
  | sed "s|${STAGING}||" \
  | xargs -r dpkg -S 2>/dev/null \
  | cut -d: -f1 \
  | sort -u > /tmp/pkgs-needed.txt

# Also add explicit packages for dlopen'd libs lddtree can't discover
# (kerberos plugins, sasl plugins, libjansson for FrankenPHP admin API)
cat >> /tmp/pkgs-needed.txt << 'EOF'
libjansson4
libkrb5-3
libsasl2-2
EOF

sort -u /tmp/pkgs-needed.txt > /tmp/pkgs-deduped.txt

# Download debs (failures are non-fatal — some names may vary by suite)
cd "${DEB_DIR}"
while read -r pkg; do
  apt-get download "${pkg}" 2>/dev/null || true
done < /tmp/pkgs-deduped.txt

# Extract only usr/lib from each deb into staging
for deb in "${DEB_DIR}"/*.deb; do
  [ -f "${deb}" ] || continue
  rm -rf "${DEB_EXTRACT}"
  mkdir -p "${DEB_EXTRACT}"
  dpkg-deb -x "${deb}" "${DEB_EXTRACT}"
  if [ -d "${DEB_EXTRACT}/usr/lib" ]; then
    cp -a "${DEB_EXTRACT}/usr/lib/." "${STAGING}/usr/lib/"
  fi
done
rm -rf "${DEB_EXTRACT}" "${DEB_DIR}"

# ---------------------------------------------------------------------------
# 3. dlopen'd plugin directories
#
# These are subdirectories loaded at runtime via dlopen() — dpkg-deb extract
# above handles the files, but ensure the directories land correctly.
# ---------------------------------------------------------------------------

# Kerberos pre-authentication plugins
if [ -d "/usr/lib/${ARCH}/krb5" ]; then
  cp -a "/usr/lib/${ARCH}/krb5" "${STAGING}/usr/lib/${ARCH}/"
fi

# SASL mechanism plugins
if [ -d "/usr/lib/${ARCH}/sasl2" ]; then
  cp -a "/usr/lib/${ARCH}/sasl2" "${STAGING}/usr/lib/${ARCH}/"
  mkdir -p "${STAGING}/usr/lib/sasl2"
  cp -a "/usr/lib/${ARCH}/sasl2/." "${STAGING}/usr/lib/sasl2/"
fi

# libjansson — dlopen'd by FrankenPHP admin API, not in any DT_NEEDED chain.
# Copy directly from the source filesystem; apt-get download is unreliable
# here since libjansson4 may not be registered in the image's dpkg database.
find "/usr/lib/${ARCH}" -maxdepth 1 -name 'libjansson.so*' | while read -r f; do
  dest="${STAGING}/usr/lib/${ARCH}/$(basename "${f}")"
  [ -e "${dest}" ] && continue
  cp -a "${f}" "${dest}"
done

# ---------------------------------------------------------------------------
# 5. PHP runtime files
# ---------------------------------------------------------------------------

# Main PHP shared library (already collected via lddtree above, belt+suspenders)
mkdir -p "${STAGING}/usr/local/lib"
[ -f "${STAGING}/usr/local/lib/libphp.so" ] || \
  cp /usr/local/lib/libphp.so "${STAGING}/usr/local/lib/"

# libwatcher (FrankenPHP file watcher)
cp -a /usr/local/lib/libwatcher* "${STAGING}/usr/local/lib/"

# PHP extension .so files (already under staging from lddtree but confirm)
mkdir -p "${STAGING}/usr/local/lib/php/extensions"
cp -a /usr/local/lib/php/extensions/. "${STAGING}/usr/local/lib/php/extensions/"

# PHP configuration
mkdir -p "${STAGING}/usr/local/etc/php/conf.d"
cp -a /usr/local/etc/php/. "${STAGING}/usr/local/etc/php/"

# ---------------------------------------------------------------------------
# 6. Dynamic linker config
# ---------------------------------------------------------------------------

mkdir -p "${STAGING}/etc/ld.so.conf.d"
[ -f /etc/ld.so.conf ]   && cp /etc/ld.so.conf   "${STAGING}/etc/"
[ -f /etc/ld.so.cache ]  && cp /etc/ld.so.cache  "${STAGING}/etc/"
cp -a /etc/ld.so.conf.d/. "${STAGING}/etc/ld.so.conf.d/"

# ---------------------------------------------------------------------------
# 7. Misc runtime config
# ---------------------------------------------------------------------------

# MIME types (FrankenPHP/Caddy uses this for content-type detection)
[ -f /etc/mime.types ] && cp /etc/mime.types "${STAGING}/etc/"


FILE_COUNT=$(find "${STAGING}" -type f | wc -l)
LINK_COUNT=$(find "${STAGING}" -type l | wc -l)
echo "✓ Staging complete — ${FILE_COUNT} files, ${LINK_COUNT} symlinks collected"

# Fail fast if any symlinks are dangling — a versioned lib target missing
# from staging means the final image would have broken .so links at runtime
DANGLING=$(find "${STAGING}" -type l ! -exec test -e {} \; -print)
if [ -n "${DANGLING}" ]; then
  echo "✗ Dangling symlinks found in staging:" >&2
  echo "${DANGLING}" >&2
  exit 1
fi
