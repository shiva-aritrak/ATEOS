# at-svelte

Svelte web UI for ATEOS, served on the default web ports. LuCI keeps
running alongside it on its own ports.

| uhttpd instance | HTTP | HTTPS | Document root | Serves |
|---|---|---|---|---|
| `at_svelte` | 80 | 443 | `/www-at` | this UI |
| `main` | 90 | 91 | `/www` | LuCI (`/cgi-bin/luci/`) |

The ports are set on first boot by `/etc/uci-defaults/90-at-svelte-uhttpd`.
If an `at_svelte` section already exists, the script leaves the uhttpd config
alone, so ports changed on a device survive sysupgrade. LuCI must keep its own
HTTPS port, because its login page redirects to `uhttpd.main`'s HTTPS ports.

## Layout

```
package/at-svelte/
├── Makefile                  OpenWrt package (runs npm ci + vite build)
├── files/
│   ├── at-svelte.uci-defaults   uhttpd: LuCI -> 90/91, at_svelte -> 80/443
│   ├── at-svelte.rpcd           backend, ubus object "at-svelte"
│   └── at-svelte.acl.json       rpcd ACL for the backend methods
└── ui/                       Svelte 5 + Vite frontend
    ├── src/App.svelte           login + status page
    └── src/lib/ubus.js          ubus JSON-RPC client (/ubus)
```

## How the frontend talks to the router

The UI calls ubus over HTTP at `/ubus`, the same way LuCI does:

1. `session.login` with a router account (for example `root`) returns a session id.
2. Each call sends that id: `call('at-svelte', 'status')`, `call('system', 'board')`, ...

Accounts and permissions come from rpcd (`/etc/config/rpcd` and
`/usr/share/rpcd/acl.d/*.json`). `root` has full access by default.

## Adding a backend method

1. In `files/at-svelte.rpcd`, add the method to the `list` output and handle
   it under `call`. Arguments arrive as JSON on stdin, and the reply is a
   JSON object on stdout (use `jshn`).
2. Add the method name to `files/at-svelte.acl.json`.
3. Call it from the UI: `await call('at-svelte', 'my_method', { arg: 1 })`.

On a device, `ubus -v list at-svelte` and `ubus call at-svelte status` test the
backend directly. After changing the rpcd script or ACL, run
`/etc/init.d/rpcd reload`.

## Developing the UI

```
cd package/at-svelte/ui
npm install
ROUTER=http://192.168.27.1 npm run dev
```

The dev server proxies `/ubus` to `ROUTER`, so the UI runs on your PC against
a real device.

## Building

The package build runs `npm ci && npm run build` and installs `ui/dist` to
`/www-at`, so the build host needs Node.js 18 or later and npm, plus network
access to the npm registry. Commit `package-lock.json` whenever dependencies
change.
