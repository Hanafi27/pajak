<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Sistem PBB BAPENDA' }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell is-collapsed" id="appShell">
  <aside class="sidebar flex flex-col">
    <div class="brand">
      <div class="brand-left">
        <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-10 w-10 rounded-full object-cover">
        <div class="brand-copy">
          <h2 class="brand-title">BAPENDA</h2>
          <p class="brand-subtitle">Bandung Bedas</p>
        </div>
      </div>
      <button type="button" id="sidebarToggle" class="hamburger-btn" aria-label="Toggle sidebar">
        <span class="hamburger-icon">
          <span class="hamburger-line"></span>
          <span class="hamburger-line"></span>
          <span class="hamburger-line"></span>
        </span>
      </button>
    </div>

    <nav class="flex-1">
      <a class="sidebar-nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}" href="{{ route('dashboard.index') }}"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 13h8V3H3v10Zm10 8h8v-6h-8v6Zm0-8h8V3h-8v10ZM3 21h8v-6H3v6Z"/></svg><span class="sidebar-label">Dashboard</span></a>
      @if(session('auth_user.role') === 'petugas')
      <a class="sidebar-nav-link {{ request()->routeIs('wajib-pajak.*') ? 'active' : '' }}" href="{{ route('wajib-pajak.index') }}"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0Zm-10 9a6 6 0 0 1 12 0"/><path d="M20 7v6m3-3h-6"/></svg><span class="sidebar-label">Data Wajib Pajak</span></a>
      <a class="sidebar-nav-link {{ request()->routeIs('objek-pajak.*') ? 'active' : '' }}" href="{{ route('objek-pajak.index') }}"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="m3 10 9-6 9 6v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/></svg><span class="sidebar-label">Data Objek Pajak</span></a>
      <a class="sidebar-nav-link {{ request()->routeIs('pbb.*') ? 'active' : '' }}" href="{{ route('pbb.index') }}"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M4 20V10m6 10V4m6 16v-7m6 7V7"/></svg><span class="sidebar-label">Pengolahan PBB</span></a>
      @endif
      <a class="sidebar-nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 3h18v18H3z"/><path d="M8 14h8M8 10h8M8 18h5"/></svg><span class="sidebar-label">Laporan</span></a>
    </nav>

    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-center">
      <p class="sidebar-label text-[11px] text-slate-500">Waktu Server</p>
      <p id="clockText" class="text-sm font-semibold text-teal-800">--:--:--</p>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <div><strong>{{ session('auth_user.name') }}</strong><div class="text-xs text-slate-500">{{ strtoupper(session('auth_user.role')) }}</div></div>
      <form method="post" action="{{ route('logout') }}">@csrf <button class="logout-btn" type="submit">Logout</button></form>
    </div>

    @yield('content')
  </main>
</div>

<div id="dialogBackdrop" class="dialog-backdrop" style="display:none;">
  <div class="dialog-card">
    <h3 id="dialogTitle" class="dialog-title">Konfirmasi</h3>
    <p id="dialogText" class="dialog-text">Apakah Anda yakin?</p>
    <div class="dialog-actions">
      <button id="dialogCancel" type="button" class="btn-muted">Batal</button>
      <button id="dialogOk" type="button" class="btn">Lanjutkan</button>
    </div>
  </div>
</div>
<div id="toastStack" class="toast-stack"></div>

<script>
  const appShell = document.getElementById('appShell');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const savedMode = localStorage.getItem('sidebar_mode');
  const clockText = document.getElementById('clockText');

  if (savedMode === 'expanded') appShell.classList.remove('is-collapsed');

  sidebarToggle.addEventListener('click', () => {
    appShell.classList.toggle('is-collapsed');
    localStorage.setItem('sidebar_mode', appShell.classList.contains('is-collapsed') ? 'collapsed' : 'expanded');
  });

  function updateClock(){
    const now = new Date();
    clockText.textContent = now.toLocaleTimeString('id-ID', { hour12:false });
  }
  updateClock();
  setInterval(updateClock, 1000);

  const dialogBackdrop = document.getElementById('dialogBackdrop');
  const dialogTitle = document.getElementById('dialogTitle');
  const dialogText = document.getElementById('dialogText');
  const dialogOk = document.getElementById('dialogOk');
  const dialogCancel = document.getElementById('dialogCancel');
  const toastStack = document.getElementById('toastStack');

  function showToast(message, type = 'info') {
    const el = document.createElement('div');
    el.className = `toast-item toast-${type}`;
    el.textContent = message;
    toastStack.appendChild(el);
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(-6px)';
      setTimeout(() => el.remove(), 220);
    }, 2600);
  }

  function showDialog({ title, message, okText = 'Lanjutkan', cancelText = 'Batal', withCancel = true }) {
    return new Promise((resolve) => {
      dialogTitle.textContent = title;
      dialogText.textContent = message;
      dialogOk.textContent = okText;
      dialogCancel.textContent = cancelText;
      dialogCancel.style.display = withCancel ? '' : 'none';
      dialogBackdrop.style.display = 'flex';

      const cleanup = () => {
        dialogBackdrop.style.display = 'none';
        dialogOk.onclick = null;
        dialogCancel.onclick = null;
        dialogBackdrop.onclick = null;
      };

      dialogOk.onclick = () => { cleanup(); resolve(true); };
      dialogCancel.onclick = () => { cleanup(); resolve(false); };
      dialogBackdrop.onclick = (e) => {
        if (e.target === dialogBackdrop && withCancel) {
          cleanup();
          resolve(false);
        }
      };
    });
  }

  window.uiDialog = {
    confirm(message, title = 'Konfirmasi') {
      return showDialog({ title, message, withCancel: true });
    },
    alert(message, title = 'Perhatian') {
      return showDialog({ title, message, okText: 'Tutup', withCancel: false });
    },
    success(message) {
      showToast(message, 'ok');
    },
    error(message) {
      showToast(message, 'err');
    },
    info(message) {
      showToast(message, 'info');
    }
  };

  @if(session('success'))
  window.uiDialog.success(@json(session('success')));
  @endif
  @if(session('error'))
  window.uiDialog.error(@json(session('error')));
  @endif
  @if($errors->any())
  window.uiDialog.error(@json($errors->first()));
  @endif
</script>
</body>
</html>
