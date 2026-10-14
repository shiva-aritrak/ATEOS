#!/bin/sh

USERNAME="$1"
PASSWORD="$2"

if [ -z "$USERNAME" ] || [ -z "$PASSWORD" ]; then
    echo "Usage: $0 <username> <password>"
    exit 1
fi

# check if user already exists
if grep -q "^${USERNAME}:" /etc/passwd; then
    echo "User '$USERNAME' already exists"
else
    useradd -M -s /bin/false "$USERNAME" || exit 1
    echo "${USERNAME}:${PASSWORD}" | chpasswd || exit 1
    echo "Linux user created"
fi

# remove old rpcd entry for same username if exists
uci show rpcd | grep "username='$USERNAME'" >/dev/null 2>&1
if [ $? -eq 0 ]; then
    IDX=$(uci show rpcd | grep "username='$USERNAME'" | head -n1 | cut -d. -f2 | cut -d= -f1)
    [ -n "$IDX" ] && uci delete rpcd.$IDX
fi

# add rpcd login section
SECTION=$(uci add rpcd login)
uci set rpcd.$SECTION.username="$USERNAME"
uci set rpcd.$SECTION.password="\$p\$$USERNAME"
uci add_list rpcd.$SECTION.read='*'
uci add_list rpcd.$SECTION.write='*'

uci commit rpcd
/etc/init.d/rpcd restart

echo "LuCI user '$USERNAME' added successfully"
echo "Shell disabled, LuCI login enabled"
