<script>
  import { call, login, logout, forgetSession, isLoggedIn, PERMISSION_DENIED } from './lib/ubus.js';

  const REFRESH_MS = 5000;

  let loggedIn = $state(isLoggedIn());
  let username = $state('root');
  let password = $state('');
  let busy = $state(false);
  let error = $state('');
  let status = $state(null);
  let timer;

  // LuCI runs on its own uhttpd instance: 90 (http) / 91 (https)
  const luciUrl = `${location.protocol}//${location.hostname}:${
    location.protocol === 'https:' ? 91 : 90
  }/cgi-bin/luci/`;

  function formatUptime(seconds) {
    const d = Math.floor(seconds / 86400);
    const h = Math.floor((seconds % 86400) / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    return `${d ? d + 'd ' : ''}${h}h ${m}m`;
  }

  async function refresh() {
    try {
      status = await call('at-svelte', 'status');
      error = '';
    } catch (e) {
      if (e.code === PERMISSION_DENIED) {
        forgetSession();
        loggedIn = false;
        error = 'Session expired, please log in again.';
      } else {
        error = e.message;
      }
    }
  }

  async function onLogin(event) {
    event.preventDefault();
    busy = true;
    error = '';
    try {
      await login(username, password);
      password = '';
      loggedIn = true;
    } catch (e) {
      error = e.code === PERMISSION_DENIED ? 'Invalid username or password.' : e.message;
    } finally {
      busy = false;
    }
  }

  async function onLogout() {
    await logout().catch(() => {});
    loggedIn = false;
    status = null;
  }

  $effect(() => {
    clearInterval(timer);
    if (!loggedIn) return;
    refresh();
    timer = setInterval(refresh, REFRESH_MS);
    return () => clearInterval(timer);
  });

</script>

<header>
  <h1>ATEOS</h1>
  <nav>
    <a href={luciUrl}>Advanced settings (LuCI)</a>
    {#if loggedIn}
      <button type="button" class="link" onclick={onLogout}>Log out</button>
    {/if}
  </nav>
</header>

<main>
  {#if !loggedIn}
    <form class="card login" onsubmit={onLogin}>
      <h2>Log in</h2>
      <label>
        Username
        <input bind:value={username} autocomplete="username" required />
      </label>
      <label>
        Password
        <input type="password" bind:value={password} autocomplete="current-password" />
      </label>
      {#if error}<p class="error">{error}</p>{/if}
      <button type="submit" disabled={busy}>{busy ? 'Logging in…' : 'Log in'}</button>
    </form>
  {:else}
    <section class="card">
      <h2>Status</h2>
      {#if error}<p class="error">{error}</p>{/if}
      {#if status}
        <dl>
          <dt>Product</dt><dd>{status.product}</dd>
          <dt>Hostname</dt><dd>{status.hostname}</dd>
          <dt>Model</dt><dd>{status.model}</dd>
          <dt>Firmware</dt><dd>{status.firmware}</dd>
          <dt>UI version</dt><dd>{status.ui_version}</dd>
          <dt>Uptime</dt><dd>{formatUptime(status.uptime)}</dd>
          <dt>Load</dt><dd>{status.load}</dd>
          <dt>LAN address</dt><dd>{status.lan_ip}</dd>
        </dl>
      {:else if !error}
        <p class="muted">Loading…</p>
      {/if}
    </section>
  {/if}
</main>

<footer>ATEOS UI v{__APP_VERSION__}</footer>
