<?php
// Deteksi halaman aktif untuk menu highlight
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
  :root {
    --pink:        #FF3E80;
    --pink-soft:   #FF6FA3;
    --pink-glow:   rgba(255,62,128,0.25);
    --yellow:      #FFD166;
    --yellow-glow: rgba(255,209,102,0.2);
    --dark:        #0E0B20;
    --dark2:       #130F28;
    --dark3:       #1C1640;
    --card-bg:     rgba(255,255,255,0.08);
    --glass:       rgba(255,255,255,0.10);
    --border:      rgba(255,255,255,0.12);
    --text:        #F5F2FF;
    --muted:       rgba(220,215,255,0.55);
    --sidebar-w:          270px;
    --sidebar-w-collapsed: 72px;
  }

  /* ─── SIDEBAR WRAP ─── */
  .admin-sidebar {
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
    width: var(--sidebar-w);
    background: linear-gradient(180deg, #160F30 0%, #0E0B20 100%);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    z-index: 1000;
    transition: width .35s cubic-bezier(.4,0,.2,1);
    overflow: hidden;
  }

  /* Top glow accent */
  .admin-sidebar::after {
    content: '';
    position: absolute; top: -80px; left: -40px;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(255,62,128,.18), transparent 65%);
    pointer-events: none; z-index: 0;
  }

  /* Bottom glow accent */
  .admin-sidebar::before {
    content: '';
    position: absolute; bottom: -60px; right: -60px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(130,60,220,.15), transparent 65%);
    pointer-events: none; z-index: 0;
  }

  .admin-sidebar * { position: relative; z-index: 1; }

  /* Highlight line kanan sidebar */
  .sb-right-line {
    position: absolute;
    top: 0; right: 0;
    width: 1px; height: 100%;
    background: linear-gradient(180deg,
      transparent 0%,
      rgba(255,62,128,.5) 25%,
      rgba(255,209,102,.3) 60%,
      transparent 100%);
    pointer-events: none; z-index: 2;
  }

  /* ─── COLLAPSED STATE ─── */
  .admin-sidebar.collapsed { width: var(--sidebar-w-collapsed); }

  .admin-sidebar.collapsed .sb-label,
  .admin-sidebar.collapsed .sb-brand-text,
  .admin-sidebar.collapsed .sb-admin-info,
  .admin-sidebar.collapsed .sb-section-title,
  .admin-sidebar.collapsed .sb-badge {
    opacity: 0; pointer-events: none; width: 0; overflow: hidden;
  }
  .admin-sidebar.collapsed .sb-menu-item {
    justify-content: center; padding: 12px 0;
  }
  .admin-sidebar.collapsed .sb-menu-item .sb-icon { margin: 0; }
  .admin-sidebar.collapsed .sb-header {
    padding: 20px 0; justify-content: center;
  }
  .admin-sidebar.collapsed .sb-footer-inner { opacity: 0; pointer-events: none; }

  /* ─── HEADER ─── */
  .sb-header {
    display: flex; align-items: center; gap: 12px;
    padding: 22px 20px 18px;
    border-bottom: 1px solid rgba(255,255,255,.10);
    flex-shrink: 0;
    transition: padding .35s;
  }
  .sb-logo-icon {
    width: 40px; height: 40px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--pink), #C41A5C);
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 4px 18px var(--pink-glow);
  }
  .sb-brand-text { transition: opacity .25s; white-space: nowrap; overflow: hidden; }
  .sb-brand-name {
    font-family: 'Bebas Neue', 'Impact', sans-serif;
    font-size: 1.3rem; letter-spacing: .1em;
    background: linear-gradient(135deg, var(--pink), var(--yellow));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    line-height: 1;
  }
  .sb-brand-sub {
    font-size: .62rem; letter-spacing: .16em; text-transform: uppercase;
    color: var(--muted); margin-top: 2px;
  }

  /* ─── TOGGLE BTN ─── */
  .sb-toggle-btn {
    position: absolute; top: 22px; right: -14px;
    width: 28px; height: 28px;
    background: #1C1640; border: 1px solid rgba(255,255,255,.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .72rem; color: var(--muted);
    transition: all .25s; z-index: 10;
    box-shadow: 0 2px 10px rgba(0,0,0,.50);
  }
  .sb-toggle-btn:hover {
    background: var(--pink); border-color: var(--pink);
    color: #fff; box-shadow: 0 4px 16px var(--pink-glow);
  }

  /* ─── ADMIN INFO ─── */
  .sb-admin-info {
    margin: 14px 14px 0;
    padding: 16px;
    background: linear-gradient(135deg, rgba(255,62,128,.14), rgba(255,209,102,.07));
    border: 1px solid rgba(255,62,128,.22);
    border-radius: 14px;
    transition: opacity .25s; flex-shrink: 0;
  }
  .sb-admin-avatar {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, var(--pink), #C41A5C);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; box-shadow: 0 4px 14px var(--pink-glow); flex-shrink: 0;
  }
  .sb-admin-name {
    font-family: 'Syne', 'Segoe UI', sans-serif;
    font-weight: 700; font-size: .88rem; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  .sb-admin-role {
    font-size: .66rem; letter-spacing: .13em; text-transform: uppercase;
    color: var(--pink-soft); margin-top: 2px;
  }
  .sb-admin-status {
    display: flex; align-items: center; gap: 6px;
    font-size: .70rem; color: var(--muted); margin-top: 10px;
  }
  .sb-status-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #7BFF9A; box-shadow: 0 0 8px rgba(123,255,154,.7);
    animation: pulse-dot 2s infinite; flex-shrink: 0;
  }
  @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.3} }

  /* ─── SCROLL AREA ─── */
  .sb-scroll {
    flex: 1; overflow-y: auto; overflow-x: hidden;
    padding: 12px 10px 20px;
  }
  .sb-scroll::-webkit-scrollbar { width: 3px; }
  .sb-scroll::-webkit-scrollbar-track { background: transparent; }
  .sb-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(var(--pink), var(--yellow)); border-radius: 3px;
  }

  /* ─── SECTION TITLE ─── */
  .sb-section-title {
    font-size: .60rem; letter-spacing: .20em; text-transform: uppercase;
    color: rgba(220,215,255,.35); padding: 16px 12px 6px;
    white-space: nowrap; overflow: hidden; transition: opacity .25s;
    font-family: 'Syne', 'Segoe UI', sans-serif; font-weight: 700;
  }

  /* ─── DIVIDER ─── */
  .sb-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.10), transparent);
    margin: 8px 6px;
  }

  /* ─── MENU ITEM ─── */
  .sb-menu-item {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px; border-radius: 11px; margin: 2px 0;
    color: var(--muted); text-decoration: none;
    font-size: .875rem; font-weight: 500;
    transition: all .25s; position: relative;
    white-space: nowrap; overflow: hidden;
    border: 1px solid transparent;
  }
  .sb-menu-item:hover {
    background: rgba(255,255,255,.07);
    color: var(--text); transform: translateX(3px);
    text-decoration: none;
  }
  .sb-menu-item.active {
    background: linear-gradient(135deg, rgba(255,62,128,.18), rgba(255,209,102,.07));
    color: var(--text);
    border: 1px solid rgba(255,62,128,.28);
    box-shadow: 0 4px 18px rgba(0,0,0,.25);
    text-decoration: none;
  }
  .sb-menu-item.active .sb-icon {
    color: var(--pink);
    filter: drop-shadow(0 0 5px var(--pink-glow));
  }
  /* Garis aksen kiri saat active */
  .sb-menu-item.active::before {
    content: '';
    position: absolute; left: 0; top: 20%; bottom: 20%;
    width: 3px; border-radius: 0 3px 3px 0;
    background: linear-gradient(180deg, var(--pink), var(--yellow));
    box-shadow: 0 0 10px var(--pink-glow);
  }

  /* ─── ICON ─── */
  .sb-icon {
    width: 20px; height: 20px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; transition: color .25s;
  }
  .sb-menu-item:hover .sb-icon { color: var(--pink-soft); }

  /* ─── LABEL ─── */
  .sb-label {
    flex: 1;
    font-family: 'Syne', 'Segoe UI', sans-serif;
    font-weight: 600; font-size: .82rem; letter-spacing: .02em;
    transition: opacity .25s; white-space: nowrap; overflow: hidden;
  }

  /* ─── BADGE ─── */
  .sb-badge {
    font-size: .60rem; font-weight: 700; letter-spacing: .06em;
    padding: 2px 9px; border-radius: 20px; flex-shrink: 0;
    transition: opacity .25s;
  }
  .sb-badge.pink   { background:rgba(255,62,128,.18); color:var(--pink-soft); border:1px solid rgba(255,62,128,.30); }
  .sb-badge.yellow { background:rgba(255,209,102,.14); color:var(--yellow);    border:1px solid rgba(255,209,102,.28); }
  .sb-badge.green  { background:rgba(123,255,154,.12); color:#7BFF9A;          border:1px solid rgba(123,255,154,.25); }

  /* ─── LOGOUT ─── */
  .sb-menu-item.logout { color:rgba(255,62,128,.65); margin-top:4px; }
  .sb-menu-item.logout:hover {
    background: rgba(255,62,128,.12); color: var(--pink); transform: translateX(3px);
  }
  .sb-menu-item.logout .sb-icon  { color: rgba(255,62,128,.65); }
  .sb-menu-item.logout:hover .sb-icon { color: var(--pink); }

  /* ─── FOOTER ─── */
  .sb-footer { padding: 14px; border-top: 1px solid rgba(255,255,255,.09); flex-shrink: 0; }
  .sb-footer-inner {
    background: rgba(255,209,102,.07);
    border: 1px solid rgba(255,209,102,.18);
    border-radius: 11px; padding: 12px 14px;
    transition: opacity .25s;
  }
  .sb-footer-title {
    font-size: .66rem; letter-spacing: .10em; text-transform: uppercase;
    color: var(--yellow); font-weight: 700;
    font-family: 'Syne','Segoe UI',sans-serif;
    margin-bottom: 4px;
  }
  .sb-footer-val {
    font-family: 'Bebas Neue', 'Impact', sans-serif;
    font-size: 1.3rem; letter-spacing: .08em;
    background: linear-gradient(135deg, var(--yellow), var(--pink));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  }
  .sb-footer-sub { font-size: .68rem; color: var(--muted); margin-top: 2px; }
  .admin-sidebar.collapsed .sb-footer-inner { opacity: 0; pointer-events: none; }

  /* ─── TOOLTIP (collapsed mode) ─── */
  .admin-sidebar.collapsed .sb-menu-item[data-tip]:hover::after {
    content: attr(data-tip);
    position: absolute; left: calc(100% + 14px); top: 50%;
    transform: translateY(-50%);
    background: #1C1640; border: 1px solid rgba(255,255,255,.14);
    color: var(--text); font-size: .78rem;
    font-family: 'Syne','Segoe UI',sans-serif; font-weight: 600;
    padding: 6px 13px; border-radius: 9px;
    white-space: nowrap; pointer-events: none;
    box-shadow: 0 8px 28px rgba(0,0,0,.50); z-index: 9999;
  }

  /* ─── MAIN CONTENT OFFSET ─── */
  .admin-main-content {
    margin-left: var(--sidebar-w); min-height: 100vh;
    transition: margin-left .35s cubic-bezier(.4,0,.2,1);
  }
  .admin-main-content.collapsed { margin-left: var(--sidebar-w-collapsed); }

  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap');
</style>

<!-- ══════════════════ SIDEBAR HTML ══════════════════ -->
<div class="admin-sidebar" id="adminSidebar">

  <!-- Garis highlight kanan -->
  <div class="sb-right-line"></div>

  <!-- TOGGLE -->
  <div class="sb-toggle-btn" onclick="toggleSidebar()" id="sbToggle" title="Toggle sidebar">
    <span id="sbToggleIcon">◀</span>
  </div>

  <!-- HEADER / BRAND -->
  <div class="sb-header">
    <div class="sb-logo-icon">🎮</div>
    <div class="sb-brand-text">
      <div class="sb-brand-name">PixelStation</div>
      <div class="sb-brand-sub">Admin Panel</div>
    </div>
  </div>

  <!-- ADMIN INFO -->
  <div class="sb-admin-info">
    <div style="display:flex;align-items:center;gap:12px;">
      <div class="sb-admin-avatar">👑</div>
      <div style="overflow:hidden;">
        <div class="sb-admin-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Administrator') ?></div>
        <div class="sb-admin-role">Super Admin</div>
      </div>
    </div>
    <div class="sb-admin-status">
      <div class="sb-status-dot"></div>
      <span>Online · <?= date('H:i') ?> WIB</span>
    </div>
  </div>

  <!-- MENU SCROLL -->
  <div class="sb-scroll">

    <!-- ── MAIN ── -->
    <div class="sb-section-title">Main Menu</div>

    <a href="dashboard-admin.php"
       class="sb-menu-item <?= $current_page === 'dashboard-admin.php' ? 'active' : '' ?>"
       data-tip="Dashboard">
      <div class="sb-icon">🏠</div>
      <div class="sb-label">Dashboard</div>
    </a>

    <div class="sb-divider"></div>

    <!-- ── MANAGEMENT ── -->
    <div class="sb-section-title">Management</div>

    <a href="ps.php"
       class="sb-menu-item <?= $current_page === 'ps.php' ? 'active' : '' ?>"
       data-tip="PlayStation">
      <div class="sb-icon">🎮</div>
      <div class="sb-label">PlayStation</div>
    </a>

    <a href="game.php"
       class="sb-menu-item <?= $current_page === 'game.php' ? 'active' : '' ?>"
       data-tip="Game">
      <div class="sb-icon">🕹️</div>
      <div class="sb-label">Game</div>
    </a>

    <a href="booking-admin.php"
       class="sb-menu-item <?= $current_page === 'booking-admin.php' ? 'active' : '' ?>"
       data-tip="Booking">
      <div class="sb-icon">📅</div>
      <div class="sb-label">Booking</div>
      <span class="sb-badge pink">Baru</span>
    </a>

    <a href="users.php"
       class="sb-menu-item <?= $current_page === 'users.php' ? 'active' : '' ?>"
       data-tip="Pengguna">
      <div class="sb-icon">👥</div>
      <div class="sb-label">Pengguna</div>
    </a>

    <div class="sb-divider"></div>

    <!-- ── REPORT ── -->
    <div class="sb-section-title">Report</div>

    <a href="laporan.php"
       class="sb-menu-item <?= $current_page === 'laporan.php' ? 'active' : '' ?>"
       data-tip="Laporan">
      <div class="sb-icon">📋</div>
      <div class="sb-label">Laporan</div>
      <span class="sb-badge yellow">PDF</span>
    </a>

    <div class="sb-divider"></div>

    <!-- ── ACCOUNT ── -->
    <div class="sb-section-title">Account</div>

    <a href="../logout.php" class="sb-menu-item logout" data-tip="Logout">
      <div class="sb-icon">🚪</div>
      <div class="sb-label">Logout</div>
    </a>

  </div><!-- /sb-scroll -->

  <!-- FOOTER STAT -->
  <div class="sb-footer">
    <div class="sb-footer-inner">
      <div class="sb-footer-title">⚡ Sesi Aktif Hari Ini</div>
      <div class="sb-footer-val" id="sbActiveSesi">—</div>
      <div class="sb-footer-sub">Live dari sistem booking</div>
    </div>
  </div>

</div><!-- /admin-sidebar -->

<script>
  // ─── GOOGLE FONTS ───
  if (!document.getElementById('sbFonts')) {
    const lnk = document.createElement('link');
    lnk.id   = 'sbFonts';
    lnk.rel  = 'stylesheet';
    lnk.href = 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap';
    document.head.appendChild(lnk);
  }

  // ─── TOGGLE ───
  let sidebarCollapsed = false;

  function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const main    = document.querySelector('.main-wrapper');
    const icon    = document.getElementById('sbToggleIcon');
    sidebarCollapsed = !sidebarCollapsed;
    sidebar.classList.toggle('collapsed', sidebarCollapsed);
    if (main) {
      main.style.marginLeft = sidebarCollapsed
        ? 'var(--sidebar-w-collapsed)'
        : 'var(--sidebar-w)';
    }
    icon.textContent = sidebarCollapsed ? '▶' : '◀';
    localStorage.setItem('sbCollapsed', sidebarCollapsed ? '1' : '0');
  }

  // ─── RESTORE STATE ───
  (function () {
    if (localStorage.getItem('sbCollapsed') === '1') {
      const sidebar = document.getElementById('adminSidebar');
      const main    = document.querySelector('.main-wrapper');
      const icon    = document.getElementById('sbToggleIcon');
      sidebarCollapsed = true;
      sidebar.classList.add('collapsed');
      if (main) main.style.marginLeft = 'var(--sidebar-w-collapsed)';
      if (icon) icon.textContent = '▶';
    }
  })();

  // ─── ACTIVE LINK ───
  (function () {
    const path = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sb-menu-item').forEach(el => {
      const href = (el.getAttribute('href') || '').split('#')[0].split('/').pop();
      if (href && href === path) el.classList.add('active');
    });
  })();

  // ─── SWIPE (mobile) ───
  let touchStartX = 0;
  document.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
  document.addEventListener('touchend',   e => {
    const dx = e.changedTouches[0].clientX - touchStartX;
    if (dx < -60 && !sidebarCollapsed) toggleSidebar();
    if (dx >  60 &&  sidebarCollapsed) toggleSidebar();
  }, { passive: true });

  // ─── LIVE CLOCK ───
  setInterval(() => {
    const now = new Date();
    const hh  = String(now.getHours()).padStart(2, '0');
    const mm  = String(now.getMinutes()).padStart(2, '0');
    document.querySelectorAll('.sb-admin-status span').forEach(el => {
      el.textContent = `Online · ${hh}:${mm} WIB`;
    });
  }, 30000);
</script>