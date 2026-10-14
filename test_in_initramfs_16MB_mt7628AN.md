# Test an ATEOS build in RAM (initramfs) on the MT7628AN 16MB board

Use this as a prompt for an agent (or as a checklist for an engineer) to boot a
fresh ATEOS image from RAM on the MT7628AN 16MB board, verify it, back up the
board, and only then flash it. Booting from RAM never touches flash: a power
cycle brings back whatever is installed.

## Context

| Item | Value |
|---|---|
| Board | ATEOS-MT7628AN-16MB (MT7628AN, 128MB DDR2, 16MB SPI NOR `xm25qh128c`) |
| OpenWrt profile | `ramips/mt76x8`, device `hiwifi_hc5661a` |
| Build config | `board_specific_configs/ATEOS-MT7628AN-16MB/board_config` (copy to `.config`) |
| Bootloader | **Breed** (HackPascal, 2018): web UI + `breed>` serial shell, network at `192.168.1.1` |
| Serial | USB-TTL (CP210x) on Windows **COM4**, **115200 8N1**, both Breed and Linux |
| Images | `bin/targets/ramips/mt76x8/openwrt-25.12.5-ramips-mt76x8-hiwifi_hc5661a-{initramfs-kernel,squashfs-sysupgrade}.bin` |
| Firmware LAN IP after boot | `192.168.16.1` (at-svelte `:80`/`:443`, LuCI `:90`/`:91`) |

Flash layout (identical in the stock HC5661A DTS and on the board):

```
u-boot   0x000000-0x030000  Breed, read-only     oem     0xfc0000-0xfe0000
hw_panic 0x030000-0x040000                       bdinfo  0xfe0000-0xff0000  (blank)
factory  0x040000-0x050000  MACs + WiFi cal      backup  0xff0000-0x1000000
firmware 0x050000-0xfc0000  image limit 15808k
```

MACs in `factory`: `0x04` WiFi, `0x28` LAN (used by Ethernet), `0x2e` WAN.

## Rules

- Never write anything except the `firmware` partition. Never use a
  "full flash" / "programmer firmware" / "bootloader" upload option.
- Take a full backup (step 6) before the first flash of a board.
- The HTTP server must serve a folder that contains **only** the image, never
  a home directory: it listens on every network adapter.
- Starting a network-facing server may need the user's approval; ask the user
  to start it if the agent is not allowed to.

## 1. Build

```sh
cd ~/ATEOS
cp board_specific_configs/ATEOS-MT7628AN-16MB/board_config .config
make defconfig
# Windows paths with spaces in WSL's PATH break `find -execdir` in the build
PATH=$(echo "$PATH" | tr ':' '\n' | grep -v '^/mnt/c' | paste -sd:) \
  taskset -c 0-5 nice -n 10 make -j6 V=s 2>&1 | tee build-mt7628-16mb.log
```

Check: `EXIT=0`, `sha256sum -c sha256sums` OK, sysupgrade image well under
15808k (about 6-7 MB), manifest contains `at-svelte`, `qtcm`,
`kmod-qtcm-kernel`, `kmod-mt7603` and **no** `kmod-usb-serial-qualcomm`.

## 2. Serve the initramfs image

```sh
mkdir -p /mnt/c/Users/<user>/breed
cp bin/targets/ramips/mt76x8/*-initramfs-kernel.bin /mnt/c/Users/<user>/breed/initramfs.bin
```

On Windows (user runs it), in that folder only:

```
cd C:\Users\<user>\breed
python -m http.server 8000
```

PC Ethernet: static `192.168.1.100/24` (Breed's network). Allow the port
(Administrator PowerShell), and remove the rule after the test:

```
New-NetFirewallRule -DisplayName "Breed HTTP 8000" -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow
Remove-NetFirewallRule -DisplayName "Breed HTTP 8000"
```

## 3. Talk to the serial console from WSL

WSL2 cannot see USB serial devices; drive the Windows COM port through
PowerShell. Use Latin-1 encoding so no bytes get mangled into `?`.

```sh
powershell.exe -NoProfile -Command '[System.IO.Ports.SerialPort]::GetPortNames()'
```

Send a command and read the reply (adjust the command list and sleep):

```sh
powershell.exe -NoProfile -Command '
$p = New-Object System.IO.Ports.SerialPort "COM4",115200,"None",8,"One"
$p.Encoding = [System.Text.Encoding]::GetEncoding(28591); $p.Open(); $out=""
foreach ($c in "sysinfo") { $p.Write("$c`r"); Start-Sleep 2; $out += $p.ReadExisting() }
$p.Close(); $out' | tr -d '\r'
```

Notes:
- Readable text needs a common GND; garbage made of `0x00` and single-bit
  bytes means a wiring/ground problem, not a wrong baud rate.
- In zsh, write `${p}` (not `$p[...]`) inside strings and use `${=LIST}` to
  split a space-separated list.

## 4. Stop in Breed

Breed autoboots after a 4 s countdown. Keep sending a key while the user
power-cycles the board, then stop when the `breed>` prompt shows:

```sh
powershell.exe -NoProfile -Command '
$p = New-Object System.IO.Ports.SerialPort "COM4",115200,"None",8,"One"
$p.Encoding = [System.Text.Encoding]::GetEncoding(28591); $p.Open()
$out=""; $end=(Get-Date).AddSeconds(60)
while ((Get-Date) -lt $end) { $p.Write(" "); Start-Sleep -Milliseconds 80; $out += $p.ReadExisting()
  if ($out -match "breed>") { break } }
$p.Write("`r"); Start-Sleep 1; $out += $p.ReadExisting(); $p.Close(); $out' | tr -d '\r'
```

Breed shell basics: `help` lists commands but `help <cmd>` does not show usage;
run the command without arguments instead (`boot`, `flash`, `mem`, `wget`).

## 5. Load into RAM and boot

```
wget http://192.168.1.100:8000/initramfs.bin
```

Breed prints `Saving to address 0x80000000` and the length. The kernel unpacks
to `0x80000000` too, so move the image up before booting (use the length Breed
printed, in hex):

```
mem copy 0x82000000 0x80000000 <length-hex>
boot mem 0x82000000
```

Capture about 120 s of console after `boot mem` and check the log for:

- `Linux version 6.12.x`, `machine is HiWiFi HC5661A`
- the 7 MTD partitions listed above
- `mtk_soc_eth ... eth0` with **no** `generated random MAC address`
- `mt76_wmac ... firmware init done` (WiFi)
- `ehci-platform` / `ohci-platform` registered (USB host for the modem)
- no flood of `mtk-msdc` errors
- `procd: - init -` and `Please press Enter to activate this console.`

## 6. Verify from the PC and back up the board

PC Ethernet: DHCP, or static `192.168.16.100/24`.

```
http://192.168.16.1         at-svelte: title "ATEOS", status shows
                            Product ATEOS-MT7628AN-16MB, UI version, firmware
http://192.168.16.1:90      LuCI (its login page returns HTTP 403, that is normal)
https://192.168.16.1        at-svelte over TLS (self-signed)
https://192.168.16.1:91     LuCI over TLS
```

Over SSH (`root`, no password on a fresh image):

```sh
S="ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o LogLevel=ERROR root@192.168.16.1"
$S 'cat /etc/openwrt_release /etc/device_info; cat /sys/class/net/eth0/address'
$S 'uci show uhttpd | grep listen; ubus call at-svelte status'
$S 'lsusb 2>/dev/null; ls /dev/ttyUSB* /dev/cdc-wdm* 2>/dev/null; logread | grep -iE "qmi|usb|qtcm" | tail -20'
```

Expected: eth0 MAC `48:e6:63:40:30:90` (the board's factory 0x28 value), uhttpd
`main` on 90/91 and `at_svelte` on 80/443, the modem enumerated on USB with
`qmi_wwan_q`.

Full backup (first time per board), read-only:

```sh
B=~/ATEOS-backups/mt7628-16mb-$(date +%Y%m%d); mkdir -p $B
$S 'cat /proc/mtd' > $B/proc_mtd.txt
for m in 0:u-boot 1:hw_panic 2:factory 3:firmware 7:oem 8:bdinfo 9:backup; do
  $S "cat /dev/mtd${m%%:*}ro" > $B/mtd${m%%:*}_${m#*:}.bin
done
cat $B/mtd0_u-boot.bin $B/mtd1_hw_panic.bin $B/mtd2_factory.bin $B/mtd3_firmware.bin \
    $B/mtd7_oem.bin $B/mtd8_bdinfo.bin $B/mtd9_backup.bin > $B/full_flash_16MB.bin
(cd $B && sha256sum *.bin > SHA256SUMS)    # full_flash_16MB.bin must be 16777216 bytes
```

## 7. Flash (only after steps 5-6 pass)

From the running initramfs (preferred, writes only `firmware`):

```sh
scp -O bin/targets/ramips/mt76x8/*-squashfs-sysupgrade.bin root@192.168.16.1:/tmp/fw.bin
$S 'sysupgrade -n /tmp/fw.bin'
```

Or through Breed's web UI at `http://192.168.1.1` (PC on `192.168.1.x`): the
firmware update page, firmware type only, with the sysupgrade image.

After the reboot the board comes up on `192.168.16.1`. Repeat the checks in
step 6 against the flashed system.

## Recovery

- Bad image: interrupt Breed (step 4) and flash a known-good image through
  Breed's web UI, or RAM-boot one (step 5).
- Restore the original firmware: write `mtd3_firmware.bin` from the backup to
  the firmware partition through Breed. Breed itself (`u-boot`) and `factory`
  are never rewritten.
