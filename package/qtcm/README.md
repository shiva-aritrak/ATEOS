# qtcm maintenance notes

This package combines Quectel vendor source with local OpenWrt packaging.
Keep those two layers separate when updating to a newer Quectel release.

## Source ownership

### Quectel vendor source

These directories should be refreshed from a future Quectel release:

- `quectel-CM/src/`
- `qmi-wwan-q/src/`

They contain the Quectel userspace application source and the Quectel kernel
driver source respectively.

### Local OpenWrt wrapper/integration

These files are maintained in this tree and should normally be preserved across
vendor updates:

- `Makefile`
- `README.md`
- `scripts/`
- `quectel-CM/files/`
- `qmi-wwan-q/Makefile`

The top-level `Makefile` is the OpenWrt packaging wrapper. It defines both:

- userspace package: `qtcm`
- kernel package: `kmod-qtcm-kernel`

`kmod-qtcm-kernel` is the current OpenWrt package name for the Quectel
`qmi_wwan_q.ko` module. That name is local packaging, not Quectel upstream
source naming.

The older `kmod-qmi-wwan-q` packaging has been consolidated into the current
top-level package layout. The legacy `qmi-wwan-q/Makefile` remains only as a
reference note.

## Selecting the package

In `make menuconfig`:

- select `qtcm` under `OpenWrt package -> Utils`
- `qtcm` depends on `kmod-qtcm-kernel`, so selecting `qtcm` should also select
  the bundled Quectel kernel driver package

## Updating from a future Quectel release

Use the helper script from the repository root:

```sh
package/qtcm/scripts/update-vendor-drop.sh /path/to/new/vendor/drop
```

By default, the script performs a dry run and reports what it would update. To
apply the refresh:

```sh
package/qtcm/scripts/update-vendor-drop.sh /path/to/new/vendor/drop --apply
```

The vendor drop is expected to contain:

```text
quectel-CM/src/
qmi-wwan-q/src/
```

After applying a new vendor drop, review:

1. whether the vendor release changed binary names or generated artifacts
2. whether `qmi_wwan_q.ko` still has the same module filename
3. whether new source files require build-rule changes
4. whether runtime config or init behavior needs adjustment
5. the final diff, to ensure local OpenWrt wrapper files were not unintentionally replaced

## Build examples

Build the package normally through OpenWrt:

```sh
make package/qtcm/{clean,prepare,compile} V=s
```
