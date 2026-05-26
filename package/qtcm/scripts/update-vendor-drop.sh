#!/bin/sh

set -eu

usage() {
	cat <<'EOF'
Usage:
  update-vendor-drop.sh /path/to/vendor/drop [--apply]

Expected vendor drop layout:
  quectel-CM/src/
  qmi-wwan-q/src/

Without --apply, this script only shows what it would update.
With --apply, it refreshes only the vendor-owned source directories and leaves
the local OpenWrt wrapper/integration files untouched.
EOF
}

if [ "$#" -lt 1 ] || [ "$#" -gt 2 ]; then
	usage
	exit 1
fi

vendor_root=$1
mode=${2:-}

if [ "$mode" != "" ] && [ "$mode" != "--apply" ]; then
	usage
	exit 1
fi

if [ ! -d "$vendor_root/quectel-CM/src" ]; then
	echo "Missing expected directory: $vendor_root/quectel-CM/src" >&2
	exit 1
fi

if [ ! -d "$vendor_root/qmi-wwan-q/src" ]; then
	echo "Missing expected directory: $vendor_root/qmi-wwan-q/src" >&2
	exit 1
fi

script_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
package_dir=$(CDPATH= cd -- "$script_dir/.." && pwd)
vendor_dir=$(CDPATH= cd -- "$vendor_root" && pwd)

if [ "$vendor_dir" = "$package_dir" ]; then
	echo "Refusing to use the current package directory as the vendor drop." >&2
	exit 1
fi

echo "Vendor drop: $vendor_root"
echo "Package dir: $package_dir"
echo
echo "Vendor-owned directories to refresh:"
echo "  quectel-CM/src/"
echo "  qmi-wwan-q/src/"
echo
echo "Local wrapper/integration files intentionally preserved:"
echo "  Makefile"
echo "  README.md"
echo "  scripts/"
echo "  quectel-CM/files/"
echo "  qmi-wwan-q/Makefile"

if [ "$mode" != "--apply" ]; then
	echo
	echo "Dry run only. Re-run with --apply to replace the vendor-owned source directories."
	exit 0
fi

tmp_dir=$(mktemp -d "${TMPDIR:-/tmp}/qtcm-vendor-drop.XXXXXX")
trap 'rm -rf "$tmp_dir"' EXIT HUP INT TERM

mkdir -p "$tmp_dir/quectel-CM" "$tmp_dir/qmi-wwan-q"
cp -a "$vendor_root/quectel-CM/src" "$tmp_dir/quectel-CM/"
cp -a "$vendor_root/qmi-wwan-q/src" "$tmp_dir/qmi-wwan-q/"

rm -rf "$package_dir/quectel-CM/src" "$package_dir/qmi-wwan-q/src"
cp -a "$tmp_dir/quectel-CM/src" "$package_dir/quectel-CM/"
cp -a "$tmp_dir/qmi-wwan-q/src" "$package_dir/qmi-wwan-q/"

echo
echo "Vendor source refreshed."
echo "Next review steps:"
echo "  1. inspect git diff"
echo "  2. confirm binary/module names still match package/qtcm/Makefile"
echo "  3. build with: make package/qtcm/{clean,prepare,compile} V=s"
