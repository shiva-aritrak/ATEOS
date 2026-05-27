#!/bin/sh

SERVER="$1"
TIME="${2:-60}"
PARALLEL="${3:-20}"

# Change these to your real WAN interface names
IFACES="wan wan2 usb0"

if [ -z "$SERVER" ]; then
    echo "Usage: $0 <iperf_server_ip> [time_seconds] [parallel_streams]"
    echo "Example: $0 1.2.3.4 60 20"
    exit 1
fi

echo "=== MWAN3 Load Balance Test ==="
echo "Server   : $SERVER"
echo "Duration : ${TIME}s"
echo "Streams  : $PARALLEL"
echo "IFACES   : $IFACES"
echo

TMP="/tmp/mwan3_test.$$"
mkdir -p "$TMP"

get_bytes() {
    IF="$1"
    RX=$(cat /sys/class/net/$IF/statistics/rx_bytes 2>/dev/null || echo 0)
    TX=$(cat /sys/class/net/$IF/statistics/tx_bytes 2>/dev/null || echo 0)
    echo "$RX $TX"
}

echo "Checking ping..."
ping -c 5 "$SERVER"
PING_STATUS=$?

if [ "$PING_STATUS" != "0" ]; then
    echo "Ping failed. Stopping test."
    rm -rf "$TMP"
    exit 1
fi

echo
echo "Saving start counters..."
for IF in $IFACES; do
    get_bytes "$IF" > "$TMP/$IF.start"
done

echo
echo "Running iperf3..."
iperf3 -c "$SERVER" -P "$PARALLEL" -t "$TIME"

echo
echo "Saving end counters..."
for IF in $IFACES; do
    get_bytes "$IF" > "$TMP/$IF.end"
done

echo
echo "=== Interface Traffic Totals ==="

TOTAL_RX=0
TOTAL_TX=0

for IF in $IFACES; do
    read RX1 TX1 < "$TMP/$IF.start"
    read RX2 TX2 < "$TMP/$IF.end"

    DRX=$((RX2 - RX1))
    DTX=$((TX2 - TX1))

    echo "$DRX" > "$TMP/$IF.rxdelta"
    echo "$DTX" > "$TMP/$IF.txdelta"

    TOTAL_RX=$((TOTAL_RX + DRX))
    TOTAL_TX=$((TOTAL_TX + DTX))
done

TOTAL=$((TOTAL_RX + TOTAL_TX))

printf "%-10s %-15s %-15s %-15s %-8s\n" "IFACE" "RX_BYTES" "TX_BYTES" "TOTAL_BYTES" "SHARE"

for IF in $IFACES; do
    DRX=$(cat "$TMP/$IF.rxdelta")
    DTX=$(cat "$TMP/$IF.txdelta")
    DTOTAL=$((DRX + DTX))

    if [ "$TOTAL" -gt 0 ]; then
        SHARE=$((DTOTAL * 100 / TOTAL))
    else
        SHARE=0
    fi

    printf "%-10s %-15s %-15s %-15s %s%%\n" "$IF" "$DRX" "$DTX" "$DTOTAL" "$SHARE"
done

echo
echo "TOTAL RX : $TOTAL_RX bytes"
echo "TOTAL TX : $TOTAL_TX bytes"
echo "TOTAL    : $TOTAL bytes"
