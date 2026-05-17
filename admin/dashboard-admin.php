<?php
include "../config/session.php";
include "../config/koneksi.php";

if (!isAdmin()) {
    header("Location: ../user/dashboard-user.php");
    exit;
}

// Statistik Utama
$total_users    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"));
$total_admins   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='admin'"));
$total_ps       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM playstations"));
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings"));
$pending        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='pending'"));
$confirmed      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='confirmed'"));
$selesai        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='selesai'"));
$batal          = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='batal'"));

// Pendapatan
$pendapatan_hari  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM bookings WHERE DATE(tanggal) = CURDATE() AND status IN ('confirmed','selesai')"));
$pendapatan_bulan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM bookings WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE()) AND status IN ('confirmed','selesai')"));
$pendapatan_tahun = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM bookings WHERE YEAR(tanggal)=YEAR(CURDATE()) AND status IN ('confirmed','selesai')"));

// Booking terbaru
$recent_bookings = mysqli_query($conn, "
    SELECT bookings.*, users.nama as user_nama, playstations.nama_ps
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    JOIN playstations ON bookings.ps_id = playstations.id
    ORDER BY bookings.id DESC LIMIT 5
");

// PS populer
$ps_populer = mysqli_query($conn, "
    SELECT playstations.nama_ps, COUNT(bookings.id) as total_booking
    FROM bookings
    JOIN playstations ON bookings.ps_id = playstations.id
    GROUP BY bookings.ps_id
    ORDER BY total_booking DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard — PixelStation</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ─── ROOT TOKENS ─── */
        :root {
            --pink:        #FF3E80;
            --pink-soft:   #FF6FA3;
            --pink-glow:   rgba(255,62,128,.40);
            --yellow:      #FFD166;
            --yellow-soft: #FFE299;
            --yellow-glow: rgba(255,209,102,.35);

            /* Base navy-indigo — BUKAN hitam */
            --dark:        #0E0B20;
            --dark2:       #160F30;
            --dark3:       #1C1540;

            /* Card & surface — lebih terang */
            --card-bg:     rgba(255,255,255,.08);
            --card-hover:  rgba(255,255,255,.12);
            --glass:       rgba(255,255,255,.10);
            --glass-deep:  rgba(20,14,55,.70);

            /* Border lebih kuat */
            --border:      rgba(255,255,255,.14);
            --border-glow: rgba(255,62,128,.35);

            --text:        #F5F2FF;
            --muted:       rgba(220,215,255,.58);
            --sidebar-w:   260px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; }

        body {
            font-family:'DM Sans',sans-serif;
            color:var(--text);
            overflow-x:hidden;
            min-height:100vh;
            /* Multi-layer gradient — navy-indigo dgn aksen warna */
            background:
                radial-gradient(ellipse 90% 70% at 10% -5%,  rgba(120,60,220,.30) 0%, transparent 55%),
                radial-gradient(ellipse 70% 60% at 90% 110%, rgba(255,62,128,.18) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 55% 50%,  rgba(60,30,130,.50) 0%, transparent 70%),
                #0E0B20;
            background-attachment:fixed;
        }

        /* Noise grain overlay */
        body::before {
            content:''; position:fixed; inset:0; pointer-events:none; z-index:0;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            opacity:.25;
        }

        /* ─── LAYOUT ─── */
        .main-wrapper {
            margin-left:var(--sidebar-w);
            min-height:100vh;
            position:relative; z-index:1;
            padding:28px 32px 56px;
            transition:margin .3s;
        }

        /* ─── TOP BAR ─── */
        .topbar {
            display:flex; justify-content:space-between; align-items:center;
            padding:14px 22px;
            background:rgba(255,255,255,.08);
            border:1px solid var(--border);
            border-radius:16px;
            backdrop-filter:blur(24px);
            -webkit-backdrop-filter:blur(24px);
            margin-bottom:26px;
        }
        .topbar-title {
            font-family:'Syne',sans-serif; font-weight:800;
            font-size:.95rem; letter-spacing:.07em;
            display:flex; align-items:center; gap:10px;
        }
        .dot-live {
            width:8px; height:8px; border-radius:50%;
            background:var(--pink);
            box-shadow:0 0 10px var(--pink-glow);
            animation:blink 1.6s ease-in-out infinite;
            flex-shrink:0;
        }
        @keyframes blink { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.25;transform:scale(.7)} }

        .topbar-user {
            display:flex; align-items:center; gap:10px;
            font-size:.85rem; color:var(--muted);
        }
        .topbar-avatar {
            width:36px; height:36px; border-radius:10px;
            background:linear-gradient(135deg,var(--pink),var(--yellow));
            display:flex; align-items:center; justify-content:center;
            font-family:'Bebas Neue',sans-serif; font-size:1rem; color:#fff;
            box-shadow:0 4px 16px var(--pink-glow);
        }

        /* ─── WELCOME BANNER ─── */
        .welcome-banner {
            position:relative; overflow:hidden;
            background:linear-gradient(135deg,
                rgba(255,255,255,.11) 0%,
                rgba(255,255,255,.06) 100%);
            border:1px solid rgba(255,255,255,.18);
            border-radius:22px;
            padding:38px 44px;
            margin-bottom:26px;
            backdrop-filter:blur(20px);
        }
        .welcome-banner::before {
            content:''; position:absolute; inset:0; pointer-events:none;
            background:
                radial-gradient(ellipse 65% 140% at 95% 50%, rgba(255,62,128,.18), transparent 65%),
                radial-gradient(ellipse 50% 80% at 5%  80%, rgba(255,209,102,.10), transparent 60%);
        }
        /* Grid lines dekoratif */
        .welcome-grid {
            position:absolute; inset:0; pointer-events:none;
            background-image:
                linear-gradient(rgba(255,62,128,.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,62,128,.07) 1px, transparent 1px);
            background-size:44px 44px;
            mask-image:radial-gradient(ellipse 80% 100% at 80% 50%, black, transparent);
        }
        /* Glow orb kanan */
        .welcome-orb {
            position:absolute; right:-40px; top:50%; transform:translateY(-50%);
            width:260px; height:260px; border-radius:50%;
            background:radial-gradient(circle, rgba(255,62,128,.20) 0%, transparent 70%);
            pointer-events:none;
        }

        .welcome-label {
            display:inline-flex; align-items:center; gap:8px;
            background:rgba(255,62,128,.15); border:1px solid rgba(255,62,128,.35);
            color:var(--pink-soft); font-size:.70rem; letter-spacing:.16em;
            padding:5px 14px; border-radius:30px;
            font-family:'Syne',sans-serif; font-weight:700;
            text-transform:uppercase; margin-bottom:16px;
        }
        .welcome-h {
            font-family:'Bebas Neue',sans-serif;
            font-size:clamp(2.2rem,4vw,3.2rem); letter-spacing:.06em; line-height:1;
            margin-bottom:10px;
        }
        .welcome-h span {
            background:linear-gradient(135deg,var(--pink) 0%,var(--yellow) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .welcome-sub {
            color:rgba(220,215,255,.65); font-size:.9rem; line-height:1.7;
            max-width:480px;
        }

        /* ─── STAT CARDS ─── */
        .stat-card {
            background:rgba(255,255,255,.08);
            border:1px solid var(--border);
            border-radius:20px; padding:26px 22px;
            position:relative; overflow:hidden;
            transition:transform .3s ease, border-color .3s, box-shadow .3s;
            backdrop-filter:blur(16px);
        }
        .stat-card::after {
            content:''; position:absolute;
            top:0; left:0; right:0; height:1px;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.25),transparent);
        }
        .stat-card:hover {
            transform:translateY(-6px);
            border-color:rgba(255,62,128,.40);
            box-shadow:0 24px 60px rgba(0,0,0,.35), 0 0 40px var(--pink-glow);
        }
        .stat-label {
            font-size:.70rem; letter-spacing:.14em; text-transform:uppercase;
            color:var(--muted); font-family:'Syne',sans-serif; font-weight:700;
            margin-bottom:12px;
        }
        .stat-num {
            font-family:'Bebas Neue',sans-serif;
            font-size:3rem; letter-spacing:.04em; line-height:1;
            background:linear-gradient(135deg,var(--yellow-soft),var(--pink));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .stat-icon {
            position:absolute; top:22px; right:20px;
            width:46px; height:46px; border-radius:13px;
            background:rgba(255,62,128,.14);
            border:1px solid rgba(255,62,128,.28);
            display:flex; align-items:center; justify-content:center;
            font-size:1.1rem; color:var(--pink-soft);
        }
        .stat-card.warning .stat-num {
            background:linear-gradient(135deg,var(--yellow),#F0A500);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .stat-card.warning .stat-icon {
            background:rgba(255,209,102,.14); border-color:rgba(255,209,102,.30);
            color:var(--yellow);
        }
        .stat-card.success .stat-num {
            background:linear-gradient(135deg,#67EEA9,#00C97A);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .stat-card.success .stat-icon {
            background:rgba(96,211,148,.14); border-color:rgba(96,211,148,.30);
            color:#60D394;
        }

        /* ─── INCOME CARDS ─── */
        .income-card {
            background:rgba(255,255,255,.08);
            border:1px solid var(--border);
            border-radius:20px; padding:26px 24px;
            position:relative; overflow:hidden;
            transition:transform .3s, border-color .3s, box-shadow .3s;
            backdrop-filter:blur(16px);
        }
        .income-card::after {
            content:''; position:absolute;
            top:0; left:0; right:0; height:1px;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.22),transparent);
        }
        .income-card:hover {
            transform:translateY(-5px);
            border-color:rgba(255,209,102,.35);
            box-shadow:0 20px 50px rgba(0,0,0,.30), 0 0 30px var(--yellow-glow);
        }
        .income-label {
            font-size:.70rem; letter-spacing:.14em; text-transform:uppercase;
            color:var(--muted); font-family:'Syne',sans-serif; font-weight:700;
            margin-bottom:10px;
        }
        .income-val {
            font-family:'Syne',sans-serif; font-weight:800; font-size:1.45rem;
            background:linear-gradient(135deg,var(--yellow),var(--pink));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .income-icon {
            position:absolute; top:18px; right:20px;
            font-size:2rem; opacity:.18;
        }
        /* Garis aksen kiri */
        .income-card::before {
            content:''; position:absolute;
            left:0; top:20%; bottom:20%; width:3px;
            background:linear-gradient(180deg,var(--pink),var(--yellow));
            border-radius:0 4px 4px 0;
        }

        /* ─── GLASS CARD (panel utama) ─── */
        .glass-card {
            background:rgba(255,255,255,.08);
            border:1px solid var(--border);
            border-radius:22px; padding:28px 30px;
            backdrop-filter:blur(20px);
            -webkit-backdrop-filter:blur(20px);
            position:relative; overflow:hidden;
        }
        .glass-card::after {
            content:''; position:absolute;
            top:0; left:0; right:0; height:1px;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.28),transparent);
            pointer-events:none;
        }

        .card-heading {
            font-family:'Syne',sans-serif; font-weight:700;
            font-size:.88rem; letter-spacing:.07em; text-transform:uppercase;
            display:flex; align-items:center; gap:12px;
            margin-bottom:20px;
        }
        .ch-icon {
            width:36px; height:36px; border-radius:10px;
            background:rgba(255,62,128,.16);
            border:1px solid rgba(255,62,128,.28);
            display:flex; align-items:center; justify-content:center;
            font-size:.95rem; color:var(--pink);
            flex-shrink:0;
        }
        .divider-line {
            width:44px; height:2px;
            background:linear-gradient(90deg,var(--pink),var(--yellow));
            border-radius:2px; margin-bottom:20px;
        }

        /* ─── PS POPULER ─── */
        .ps-item {
            display:flex; justify-content:space-between; align-items:center;
            padding:13px 0; border-bottom:1px solid rgba(255,255,255,.08);
        }
        .ps-item:last-child { border:none; padding-bottom:0; }
        .ps-name {
            font-size:.88rem; color:var(--text);
            display:flex; align-items:center; gap:10px;
        }
        .ps-rank {
            width:24px; height:24px; border-radius:7px;
            background:rgba(255,62,128,.18); border:1px solid rgba(255,62,128,.30);
            display:flex; align-items:center; justify-content:center;
            font-family:'Bebas Neue',sans-serif; font-size:.85rem; color:var(--pink);
            flex-shrink:0;
        }
        .ps-rank.gold   { background:rgba(255,209,102,.20); border-color:rgba(255,209,102,.40); color:var(--yellow); }
        .ps-rank.silver { background:rgba(200,200,220,.14); border-color:rgba(200,200,220,.30); color:#CCCCDD; }
        .ps-rank.bronze { background:rgba(205,127,50,.14);  border-color:rgba(205,127,50,.30);  color:#CD7F32; }
        .ps-badge {
            background:rgba(255,209,102,.14);
            border:1px solid rgba(255,209,102,.28);
            color:var(--yellow);
            font-family:'Syne',sans-serif; font-weight:700;
            font-size:.70rem; letter-spacing:.08em;
            padding:4px 12px; border-radius:20px;
        }

        /* ─── STATUS SUMMARY ─── */
        .status-summary {
            display:grid; grid-template-columns:repeat(4,1fr); gap:16px;
        }
        .ss-item {
            text-align:center; padding:24px 12px;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.12);
            border-radius:16px; transition:.25s;
            position:relative; overflow:hidden;
        }
        .ss-item::after {
            content:''; position:absolute;
            top:0; left:0; right:0; height:1px;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.22),transparent);
        }
        .ss-item:hover { transform:translateY(-4px); }
        .ss-num {
            font-family:'Bebas Neue',sans-serif;
            font-size:2.6rem; letter-spacing:.04em; line-height:1;
        }
        .ss-label {
            font-size:.70rem; text-transform:uppercase;
            letter-spacing:.12em; color:var(--muted);
            margin-top:5px; font-family:'Syne',sans-serif; font-weight:600;
        }
        .ss-pending  .ss-num  { color:var(--yellow);  text-shadow:0 0 20px var(--yellow-glow); }
        .ss-confirmed .ss-num { color:#60D394; text-shadow:0 0 20px rgba(96,211,148,.40); }
        .ss-selesai  .ss-num  { color:#60D394; text-shadow:0 0 20px rgba(96,211,148,.40); }
        .ss-batal    .ss-num  { color:var(--pink);   text-shadow:0 0 20px var(--pink-glow); }

        .ss-pending  { border-top:2px solid rgba(255,209,102,.45); }
        .ss-confirmed { border-top:2px solid rgba(96,211,148,.45); }
        .ss-selesai  { border-top:2px solid rgba(96,211,148,.45); }
        .ss-batal    { border-top:2px solid rgba(255,62,128,.45);  }

        /* ─── TABLE ─── */
        .px-table { width:100%; border-collapse:collapse; }
        .px-table thead tr { border-bottom:1px solid rgba(255,255,255,.10); }
        .px-table thead th {
            padding:10px 14px;
            font-family:'Syne',sans-serif; font-weight:700;
            font-size:.68rem; letter-spacing:.14em; text-transform:uppercase;
            color:rgba(220,215,255,.50); text-align:left;
        }
        .px-table tbody tr {
            border-bottom:1px solid rgba(255,255,255,.05);
            transition:background .2s;
        }
        .px-table tbody tr:hover { background:rgba(255,62,128,.05); }
        .px-table tbody td {
            padding:14px 14px; font-size:.87rem; color:var(--muted);
        }
        .px-table tbody td strong { color:var(--text); font-weight:500; }

        /* ─── STATUS BADGES ─── */
        .badge-px {
            padding:4px 13px; border-radius:20px;
            font-size:.68rem; font-family:'Syne',sans-serif; font-weight:700;
            letter-spacing:.09em; text-transform:uppercase;
        }
        .badge-pending   { background:rgba(255,209,102,.16); border:1px solid rgba(255,209,102,.35); color:var(--yellow); }
        .badge-confirmed { background:rgba(96,211,148,.14); border:1px solid rgba(96,211,148,.35); color:#60D394; }
        .badge-selesai   { background:rgba(96,211,148,.14); border:1px solid rgba(96,211,148,.35); color:#60D394; }
        .badge-batal     { background:rgba(255,62,128,.14); border:1px solid rgba(255,62,128,.35); color:var(--pink); }

        /* ─── QUICK MENU ─── */
        .quick-card {
            background:rgba(255,255,255,.08);
            border:1px solid var(--border);
            border-radius:18px; padding:30px 18px;
            text-align:center; text-decoration:none;
            display:block; transition:.35s ease;
            position:relative; overflow:hidden;
            backdrop-filter:blur(14px);
        }
        .quick-card::after {
            content:''; position:absolute;
            top:0; left:0; right:0; height:1px;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.25),transparent);
        }
        .quick-card::before {
            content:''; position:absolute; inset:0;
            background:linear-gradient(135deg,rgba(255,62,128,.10),rgba(255,209,102,.06));
            opacity:0; transition:.35s;
        }
        .quick-card:hover {
            border-color:rgba(255,62,128,.45);
            transform:translateY(-6px);
            box-shadow:0 24px 60px rgba(0,0,0,.30), 0 0 40px var(--pink-glow);
            text-decoration:none;
        }
        .quick-card:hover::before { opacity:1; }
        .quick-icon {
            width:54px; height:54px; border-radius:15px;
            background:linear-gradient(135deg,rgba(255,62,128,.22),rgba(255,209,102,.14));
            border:1px solid rgba(255,62,128,.30);
            display:flex; align-items:center; justify-content:center;
            font-size:1.35rem; color:var(--yellow);
            margin:0 auto 16px; transition:.3s;
            position:relative; z-index:1;
        }
        .quick-card:hover .quick-icon {
            background:linear-gradient(135deg,rgba(255,62,128,.36),rgba(255,209,102,.24));
            box-shadow:0 8px 24px var(--pink-glow);
        }
        .quick-title {
            font-family:'Syne',sans-serif; font-weight:700;
            font-size:.82rem; color:var(--text); letter-spacing:.05em;
            position:relative; z-index:1;
        }

        /* ─── BUTTON ─── */
        .btn-px {
            font-family:'Syne',sans-serif; font-weight:700;
            background:linear-gradient(135deg,var(--pink) 0%,#C41A5C 100%);
            color:#fff; border:none;
            padding:9px 22px; border-radius:10px;
            letter-spacing:.05em; font-size:.78rem;
            text-decoration:none; display:inline-flex; align-items:center; gap:8px;
            transition:transform .2s, box-shadow .2s;
            box-shadow:0 4px 20px var(--pink-glow);
        }
        .btn-px:hover {
            transform:translateY(-2px);
            box-shadow:0 10px 32px var(--pink-glow);
            color:#fff; text-decoration:none;
        }

        /* ─── SECTION TITLE ─── */
        .section-title {
            font-family:'Bebas Neue',sans-serif;
            font-size:1rem; letter-spacing:.18em; text-transform:uppercase;
            color:rgba(220,215,255,.40);
            margin-bottom:14px;
            display:flex; align-items:center; gap:10px;
        }
        .section-title::after {
            content:''; flex:1; height:1px;
            background:linear-gradient(90deg,rgba(255,255,255,.08),transparent);
        }

        /* ─── SCROLLBAR ─── */
        ::-webkit-scrollbar { width:5px; }
        ::-webkit-scrollbar-track { background:rgba(255,255,255,.03); }
        ::-webkit-scrollbar-thumb { background:linear-gradient(var(--pink),var(--yellow)); border-radius:3px; }

        /* ─── RESPONSIVE ─── */
        @media(max-width:991px) {
            .status-summary { grid-template-columns:repeat(2,1fr); }
        }
        @media(max-width:768px) {
            .main-wrapper { margin-left:70px; padding:16px 14px; }
            .welcome-banner { padding:28px 24px; }
            .status-summary { grid-template-columns:repeat(2,1fr); }
        }
    </style>
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<!-- ══════════════════════ MAIN CONTENT ══════════════════════ -->
<div class="main-wrapper">

    <!-- TOP BAR -->
    <div class="topbar mb-4">
        <div class="topbar-title">
            <span class="dot-live"></span>
            Admin Dashboard
        </div>
        <div class="topbar-user">
            <span><?= htmlspecialchars($_SESSION['nama']); ?></span>
            <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?></div>
        </div>
    </div>

    <!-- WELCOME BANNER -->
    <div class="welcome-banner mb-4">
        <div class="welcome-grid"></div>
        <div class="welcome-orb"></div>
        <div class="welcome-label">
            <span class="dot-live" style="width:6px;height:6px;"></span>
            Panel Kontrol
        </div>
        <div class="welcome-h">
            Selamat Datang, <span><?= htmlspecialchars($_SESSION['nama']); ?></span>!
        </div>
        <p class="welcome-sub">
            Kelola PlayStation, Game, Booking, dan pantau statistik bisnis Anda secara real-time dari satu tempat.
        </p>
    </div>

    <!-- ── STATISTIK UTAMA ── -->
    <div class="section-title">Statistik Utama</div>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-label">Total Member</div>
                <div class="stat-num"><?= $total_users['total']; ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-gamepad"></i></div>
                <div class="stat-label">Unit PlayStation</div>
                <div class="stat-num"><?= $total_ps['total']; ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card success">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-label">Total Booking</div>
                <div class="stat-num"><?= $total_bookings['total']; ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card warning">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-label">Menunggu</div>
                <div class="stat-num"><?= $pending['total']; ?></div>
            </div>
        </div>
    </div>

    <!-- ── PENDAPATAN ── -->
    <div class="section-title">Pendapatan</div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="income-card">
                <div class="income-icon">📅</div>
                <div class="income-label">Pendapatan Hari Ini</div>
                <div class="income-val">Rp <?= number_format($pendapatan_hari['total'] ?? 0); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="income-card">
                <div class="income-icon">🗓️</div>
                <div class="income-label">Pendapatan Bulan Ini</div>
                <div class="income-val">Rp <?= number_format($pendapatan_bulan['total'] ?? 0); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="income-card">
                <div class="income-icon">📊</div>
                <div class="income-label">Pendapatan Tahun Ini</div>
                <div class="income-val">Rp <?= number_format($pendapatan_tahun['total'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <!-- ── CHART & PS POPULER ── -->
    <div class="section-title">Analitik</div>
    <div class="row g-3 mb-4">
        <!-- Chart -->
        <div class="col-lg-7">
            <div class="glass-card h-100">
                <div class="card-heading">
                    <div class="ch-icon"><i class="fas fa-chart-bar"></i></div>
                    Statistik Booking
                </div>
                <div class="divider-line"></div>
                <canvas id="bookingChart" height="210"></canvas>
            </div>
        </div>
        <!-- PS Populer -->
        <div class="col-lg-5">
            <div class="glass-card h-100">
                <div class="card-heading">
                    <div class="ch-icon"><i class="fas fa-trophy"></i></div>
                    PlayStation Terpopuler
                </div>
                <div class="divider-line"></div>
                <?php
                $rank = 1;
                while ($ps = mysqli_fetch_array($ps_populer)) {
                    $rankClass = match($rank) { 1=>'gold', 2=>'silver', 3=>'bronze', default=>'' };
                ?>
                <div class="ps-item">
                    <div class="ps-name">
                        <div class="ps-rank <?= $rankClass; ?>"><?= $rank; ?></div>
                        <?= htmlspecialchars($ps['nama_ps']); ?>
                    </div>
                    <span class="ps-badge"><?= $ps['total_booking']; ?>× booking</span>
                </div>
                <?php $rank++; } ?>
            </div>
        </div>
    </div>

    <!-- ── STATUS BOOKING ── -->
    <div class="section-title">Ringkasan Status</div>
    <div class="glass-card mb-4">
        <div class="card-heading">
            <div class="ch-icon"><i class="fas fa-chart-pie"></i></div>
            Status Semua Booking
        </div>
        <div class="divider-line"></div>
        <div class="status-summary">
            <div class="ss-item ss-pending">
                <div class="ss-num"><?= $pending['total']; ?></div>
                <div class="ss-label">Pending</div>
            </div>
            <div class="ss-item ss-confirmed">
                <div class="ss-num"><?= $confirmed['total']; ?></div>
                <div class="ss-label">Confirmed</div>
            </div>
            <div class="ss-item ss-selesai">
                <div class="ss-num"><?= $selesai['total']; ?></div>
                <div class="ss-label">Selesai</div>
            </div>
            <div class="ss-item ss-batal">
                <div class="ss-num"><?= $batal['total']; ?></div>
                <div class="ss-label">Dibatalkan</div>
            </div>
        </div>
    </div>

    <!-- ── BOOKING TERBARU ── -->
    <div class="section-title">Aktivitas Terkini</div>
    <div class="glass-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="card-heading mb-0">
                <div class="ch-icon"><i class="fas fa-bolt"></i></div>
                Booking Terbaru
            </div>
            <a href="booking-admin.php" class="btn-px">
                <i class="fas fa-arrow-right fa-sm"></i> Lihat Semua
            </a>
        </div>
        <div class="divider-line"></div>
        <div class="table-responsive">
            <table class="px-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Mulai</th>
                        <th>Pelanggan</th>
                        <th>PlayStation</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_array($recent_bookings)):
                    $badgeClass = match($row['status']) {
                        'pending'   => 'badge-pending',
                        'confirmed' => 'badge-confirmed',
                        'selesai'   => 'badge-selesai',
                        'batal'     => 'badge-batal',
                        default     => ''
                    };
                ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td><?= htmlspecialchars($row['jam_mulai']); ?></td>
                    <td><strong><?= htmlspecialchars($row['user_nama']); ?></strong></td>
                    <td><?= htmlspecialchars($row['nama_ps']); ?></td>
                    <td><strong>Rp <?= number_format($row['total_harga']); ?></strong></td>
                    <td><span class="badge-px <?= $badgeClass; ?>"><?= ucfirst($row['status']); ?></span></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── MENU CEPAT ── -->
    <div class="section-title">Aksi Cepat</div>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="ps.php" class="quick-card">
                <div class="quick-icon"><i class="fas fa-plus-circle"></i></div>
                <div class="quick-title">Tambah PlayStation</div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="game.php" class="quick-card">
                <div class="quick-icon"><i class="fas fa-dice-d6"></i></div>
                <div class="quick-title">Tambah Game</div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="laporan.php" class="quick-card">
                <div class="quick-icon"><i class="fas fa-print"></i></div>
                <div class="quick-title">Cetak Laporan</div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="users.php" class="quick-card">
                <div class="quick-icon"><i class="fas fa-user-cog"></i></div>
                <div class="quick-title">Kelola Pengguna</div>
            </a>
        </div>
    </div>

</div><!-- /main-wrapper -->

<!-- ── CHART SCRIPT ── -->
<script>
(function() {
    const ctx = document.getElementById('bookingChart').getContext('2d');

    const gradient1 = ctx.createLinearGradient(0, 0, 0, 260);
    gradient1.addColorStop(0,   'rgba(255,209,102,.45)');
    gradient1.addColorStop(1,   'rgba(255,209,102,.05)');

    const gradient2 = ctx.createLinearGradient(0, 0, 0, 260);
    gradient2.addColorStop(0,   'rgba(96,211,148,.45)');
    gradient2.addColorStop(1,   'rgba(96,211,148,.05)');

    const gradient3 = ctx.createLinearGradient(0, 0, 0, 260);
    gradient3.addColorStop(0,   'rgba(96,211,148,.45)');
    gradient3.addColorStop(1,   'rgba(96,211,148,.05)');

    const gradient4 = ctx.createLinearGradient(0, 0, 0, 260);
    gradient4.addColorStop(0,   'rgba(255,62,128,.45)');
    gradient4.addColorStop(1,   'rgba(255,62,128,.05)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Confirmed', 'Selesai', 'Batal'],
            datasets: [{
                label: 'Jumlah Booking',
                data: [
                    <?= (int)$pending['total']; ?>,
                    <?= (int)$confirmed['total']; ?>,
                    <?= (int)$selesai['total']; ?>,
                    <?= (int)$batal['total']; ?>
                ],
                backgroundColor:  [gradient1, gradient2, gradient3, gradient4],
                borderColor: [
                    'rgba(255,209,102,.90)',
                    'rgba(96,211,148,.90)',
                    'rgba(96,211,148,.90)',
                    'rgba(255,62,128,.90)'
                ],
                borderWidth: 1.5,
                borderRadius: 10,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(14,11,32,.96)',
                    borderColor: 'rgba(255,62,128,.35)',
                    borderWidth: 1,
                    titleColor: '#F5F2FF',
                    bodyColor: 'rgba(220,215,255,.65)',
                    padding: 14,
                    cornerRadius: 12,
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y + ' booking'
                    }
                }
            },
            scales: {
                x: {
                    grid: { color:'rgba(255,255,255,.06)', drawBorder:false },
                    ticks: {
                        color:'rgba(220,215,255,.55)',
                        font:{ family:"'Syne', sans-serif", size:11, weight:'600' }
                    }
                },
                y: {
                    grid: { color:'rgba(255,255,255,.06)', drawBorder:false },
                    ticks: {
                        color:'rgba(220,215,255,.55)',
                        font:{ family:"'Syne', sans-serif", size:11 },
                        stepSize:1
                    },
                    beginAtZero:true
                }
            },
            animation: {
                duration: 900,
                easing: 'easeOutQuart'
            }
        }
    });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>