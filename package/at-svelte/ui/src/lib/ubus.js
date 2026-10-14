// Minimal ubus JSON-RPC client for uhttpd's /ubus endpoint.
// Sessions come from rpcd (session.login) and are kept per browser tab.

const NULL_SID = '00000000000000000000000000000000';
const SID_KEY = 'at_svelte_sid';

let requestId = 0;

export class UbusError extends Error {
  constructor(code, message) {
    super(message);
    this.code = code;
  }
}

// ubus status codes, see libubus.h
const UBUS_STATUS = {
  2: 'Invalid argument',
  3: 'Method not found',
  4: 'Not found',
  5: 'No data',
  6: 'Permission denied',
  7: 'Timeout'
};
export const PERMISSION_DENIED = 6;

function storedSid() {
  try {
    return sessionStorage.getItem(SID_KEY);
  } catch {
    return null;
  }
}

function storeSid(sid) {
  try {
    if (sid) sessionStorage.setItem(SID_KEY, sid);
    else sessionStorage.removeItem(SID_KEY);
  } catch {
    // storage unavailable (private mode); session lasts until reload
  }
}

let sid = storedSid() || NULL_SID;

export function isLoggedIn() {
  return sid !== NULL_SID;
}

export async function call(object, method, params = {}) {
  const res = await fetch('/ubus', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      jsonrpc: '2.0',
      id: ++requestId,
      method: 'call',
      params: [sid, object, method, params]
    })
  });

  if (!res.ok) throw new UbusError(-1, `HTTP ${res.status}`);

  const data = await res.json();
  if (data.error) {
    const code = data.error.code === -32002 ? PERMISSION_DENIED : -1;
    throw new UbusError(code, data.error.message);
  }

  const [code, result] = data.result;
  if (code !== 0) throw new UbusError(code, UBUS_STATUS[code] || `ubus error ${code}`);
  return result ?? {};
}

export async function login(username, password) {
  sid = NULL_SID;
  const result = await call('session', 'login', { username, password });
  sid = result.ubus_rpc_session;
  storeSid(sid);
}

export async function logout() {
  try {
    await call('session', 'destroy', {});
  } finally {
    sid = NULL_SID;
    storeSid(null);
  }
}

export function forgetSession() {
  sid = NULL_SID;
  storeSid(null);
}
