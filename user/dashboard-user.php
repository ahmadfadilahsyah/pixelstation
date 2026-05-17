<?php
require_once dirname(__FILE__) . '/../config/session.php';
require_once dirname(__FILE__) . '/../config/koneksi.php';

global $conn;

$user_id   = $_SESSION['id']   ?? null;
$user_name = $_SESSION['nama'] ?? 'Member';
$user_email = $_SESSION['email'] ?? '';

if (!$user_id) {
    header("Location: ../index.php");
    exit();
}

// Riwayat booking (5 terakhir)
$query_riwayat = mysqli_query($conn,
    "SELECT b.*, p.nama_ps AS nama_unit
     FROM bookings b
     JOIN playstations p ON b.ps_id = p.id
     WHERE b.user_id = '$user_id'
     ORDER BY b.created_at DESC LIMIT 5"
);

// Semua unit PS tersedia
$query_ps = mysqli_query($conn, "SELECT * FROM playstations WHERE status = 'tersedia' ORDER BY nama_ps ASC");

// Statistik user
$stat_total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM bookings WHERE user_id='$user_id'"));
$stat_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM bookings WHERE user_id='$user_id' AND status='selesai'"));
$stat_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM bookings WHERE user_id='$user_id' AND status='pending'"));
$stat_spend   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) AS t FROM bookings WHERE user_id='$user_id' AND status IN ('confirmed','selesai')"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — PixelStation</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ══════════════════════════════════════════
           ROOT TOKENS
        ══════════════════════════════════════════ */
        :root {
            --pink:       #FF3E80;
            --pink-soft:  #FF6FA3;
            --pink-glow:  rgba(255,62,128,.40);
            --yellow:     #FFD166;
            --yellow-s:   #FFE299;
            --yellow-g:   rgba(255,209,102,.35);
            --green:      #00D97E;
            --green-g:    rgba(0,217,126,.30);
            --purple:     #9B72FF;

            --bg:         #0E0B20;
            --surface:    rgba(255,255,255,.07);
            --surface2:   rgba(255,255,255,.11);
            --border:     rgba(255,255,255,.12);
            --border2:    rgba(255,255,255,.20);
            --text:       #F0EDFF;
            --muted:      rgba(220,215,255,.55);
            --nav-h:      68px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; }

        body {
            font-family:'DM Sans', sans-serif;
            color:var(--text);
            background:
                radial-gradient(ellipse 80% 60% at 15%  -10%, rgba(130,60,230,.28) 0%, transparent 55%),
                radial-gradient(ellipse 70% 55% at 88%  105%, rgba(255,62,128,.18) 0%, transparent 55%),
                radial-gradient(ellipse 55% 50% at 52%   52%, rgba(50,25,120,.45) 0%, transparent 68%),
                var(--bg);
            background-attachment:fixed;
            min-height:100vh;
            overflow-x:hidden;
        }

        /* grain */
        body::before {
            content:''; position:fixed; inset:0; pointer-events:none; z-index:0;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
            opacity:.30;
        }

        /* ══════════════════════════════════════════
           NAV
        ══════════════════════════════════════════ */
        .px-nav {
            position:fixed; top:0; left:0; right:0; z-index:1000;
            height:var(--nav-h);
            background:rgba(14,11,32,.82);
            backdrop-filter:blur(24px);
            border-bottom:1px solid var(--border);
            display:flex; align-items:center;
            padding:0 28px;
            gap:16px;
        }

        .nav-brand {
            font-family:'Bebas Neue', sans-serif;
            font-size:1.4rem; letter-spacing:.08em;
            color:var(--text); text-decoration:none;
            display:flex; align-items:center; gap:10px;
        }
        .nav-brand-icon {
            width:34px; height:34px; border-radius:9px;
            background:linear-gradient(135deg, var(--pink), var(--yellow));
            display:flex; align-items:center; justify-content:center;
            font-size:.95rem;
            box-shadow:0 4px 14px var(--pink-glow);
        }
        .nav-brand:hover { text-decoration:none; color:var(--text); }

        .nav-spacer { flex:1; }

        .nav-pill {
            display:flex; align-items:center; gap:8px;
            background:var(--surface); border:1px solid var(--border);
            border-radius:30px; padding:6px 14px 6px 8px;
            font-size:.82rem; color:var(--muted);
            transition:.25s;
        }
        .nav-pill:hover { border-color:var(--pink-soft); color:var(--text); text-decoration:none; }
        .nav-avatar {
            width:30px; height:30px; border-radius:50%;
            background:linear-gradient(135deg, var(--pink), var(--yellow));
            display:flex; align-items:center; justify-content:center;
            font-family:'Bebas Neue', sans-serif; font-size:.85rem; color:#fff;
            flex-shrink:0;
        }

        .btn-nav-logout {
            font-family:'Syne', sans-serif; font-weight:700;
            font-size:.72rem; letter-spacing:.07em; text-transform:uppercase;
            background:transparent; border:1px solid rgba(255,62,128,.35);
            color:var(--pink-soft); padding:7px 16px; border-radius:8px;
            text-decoration:none; transition:.25s;
        }
        .btn-nav-logout:hover { background:rgba(255,62,128,.14); border-color:var(--pink); color:var(--pink); text-decoration:none; }

        /* ══════════════════════════════════════════
           LAYOUT
        ══════════════════════════════════════════ */
        .main-wrap {
            position:relative; z-index:1;
            padding:calc(var(--nav-h) + 28px) 20px 56px;
            max-width:1180px; margin:0 auto;
        }

        /* ══════════════════════════════════════════
           HERO BANNER
        ══════════════════════════════════════════ */
        .hero-banner {
            position:relative; overflow:hidden;
            background:linear-gradient(135deg, rgba(255,255,255,.10), rgba(255,255,255,.05));
            border:1px solid rgba(255,255,255,.16);
            border-radius:24px;
            padding:36px 44px;
            margin-bottom:28px;
        }
        .hero-grid {
            position:absolute; inset:0; pointer-events:none;
            background-image:
                linear-gradient(rgba(255,62,128,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,62,128,.06) 1px, transparent 1px);
            background-size:40px 40px;
            mask-image:radial-gradient(ellipse 85% 100% at 80% 50%, black, transparent);
        }
        .hero-orb {
            position:absolute; right:-60px; top:50%; transform:translateY(-50%);
            width:300px; height:300px; border-radius:50%;
            background:radial-gradient(circle, rgba(255,62,128,.18) 0%, transparent 70%);
            pointer-events:none;
        }
        .hero-orb2 {
            position:absolute; left:-30px; bottom:-40px;
            width:200px; height:200px; border-radius:50%;
            background:radial-gradient(circle, rgba(155,114,255,.14) 0%, transparent 70%);
            pointer-events:none;
        }

        .hero-tag {
            display:inline-flex; align-items:center; gap:7px;
            background:rgba(255,62,128,.14); border:1px solid rgba(255,62,128,.32);
            color:var(--pink-soft); font-size:.68rem; font-weight:700; letter-spacing:.16em;
            text-transform:uppercase; padding:4px 13px; border-radius:30px;
            font-family:'Syne', sans-serif; margin-bottom:14px;
        }
        .dot-live {
            width:7px; height:7px; border-radius:50%;
            background:var(--pink); box-shadow:0 0 8px var(--pink-glow);
            animation:blink 1.6s ease-in-out infinite;
        }
        @keyframes blink { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.2;transform:scale(.65)} }

        .hero-title {
            font-family:'Bebas Neue', sans-serif;
            font-size:clamp(2.2rem, 5vw, 3.4rem); letter-spacing:.06em; line-height:1;
            margin-bottom:8px; position:relative; z-index:1;
        }
        .hero-title span {
            background:linear-gradient(135deg, var(--pink), var(--yellow));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .hero-sub {
            color:var(--muted); font-size:.9rem; line-height:1.7;
            max-width:440px; position:relative; z-index:1;
        }

        /* ══════════════════════════════════════════
           STAT CARDS
        ══════════════════════════════════════════ */
        .stat-card {
            background:var(--surface);
            border:1px solid var(--border);
            border-radius:18px; padding:20px 18px;
            position:relative; overflow:hidden;
            transition:transform .3s, border-color .3s, box-shadow .3s;
            backdrop-filter:blur(14px);
        }
        .stat-card::after {
            content:''; position:absolute; top:0; left:0; right:0; height:1px;
            background:linear-gradient(90deg, transparent, rgba(255,255,255,.22), transparent);
        }
        .stat-card:hover {
            transform:translateY(-5px);
            border-color:rgba(255,62,128,.38);
            box-shadow:0 18px 48px rgba(0,0,0,.28), 0 0 30px var(--pink-glow);
        }
        .sc-icon {
            width:42px; height:42px; border-radius:11px;
            display:flex; align-items:center; justify-content:center;
            font-size:1rem; margin-bottom:14px;
        }
        .sc-icon.pink   { background:rgba(255,62,128,.16); border:1px solid rgba(255,62,128,.28); color:var(--pink-soft); }
        .sc-icon.yellow { background:rgba(255,209,102,.14); border:1px solid rgba(255,209,102,.28); color:var(--yellow); }
        .sc-icon.green  { background:rgba(0,217,126,.14); border:1px solid rgba(0,217,126,.28); color:var(--green); }
        .sc-icon.purple { background:rgba(155,114,255,.14); border:1px solid rgba(155,114,255,.28); color:var(--purple); }
        .sc-label {
            font-size:.68rem; font-weight:700; letter-spacing:.13em; text-transform:uppercase;
            color:var(--muted); font-family:'Syne', sans-serif; margin-bottom:6px;
        }
        .sc-num {
            font-family:'Bebas Neue', sans-serif;
            font-size:2.5rem; letter-spacing:.04em; line-height:1;
            background:linear-gradient(135deg, var(--yellow-s), var(--pink));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .sc-num.is-green  { background:linear-gradient(135deg, #67EEA9, var(--green)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .sc-num.is-yellow { background:linear-gradient(135deg, var(--yellow-s), #E09000); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .sc-val {
            font-family:'Syne', sans-serif; font-weight:800; font-size:1rem;
            background:linear-gradient(135deg, var(--yellow-s), var(--pink));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }

        /* ══════════════════════════════════════════
           SECTION TITLE
        ══════════════════════════════════════════ */
        .section-title {
            font-family:'Bebas Neue', sans-serif; font-size:1rem;
            letter-spacing:.18em; text-transform:uppercase;
            color:rgba(220,215,255,.38); margin-bottom:14px;
            display:flex; align-items:center; gap:10px;
        }
        .section-title::after {
            content:''; flex:1; height:1px;
            background:linear-gradient(90deg, rgba(255,255,255,.08), transparent);
        }

        /* ══════════════════════════════════════════
           GLASS PANEL
        ══════════════════════════════════════════ */
        .glass-panel {
            background:var(--surface);
            border:1px solid var(--border);
            border-radius:22px; padding:26px 28px;
            backdrop-filter:blur(20px);
            position:relative; overflow:hidden;
            height:100%;
        }
        .glass-panel::after {
            content:''; position:absolute; top:0; left:0; right:0; height:1px;
            background:linear-gradient(90deg, transparent, rgba(255,255,255,.26), transparent);
            pointer-events:none;
        }
        .panel-heading {
            font-family:'Syne', sans-serif; font-weight:700;
            font-size:.86rem; letter-spacing:.07em; text-transform:uppercase;
            display:flex; align-items:center; gap:11px; margin-bottom:18px;
        }
        .ph-icon {
            width:34px; height:34px; border-radius:9px;
            background:rgba(255,62,128,.16); border:1px solid rgba(255,62,128,.28);
            display:flex; align-items:center; justify-content:center;
            font-size:.88rem; color:var(--pink); flex-shrink:0;
        }
        .ph-icon.yellow { background:rgba(255,209,102,.16); border-color:rgba(255,209,102,.28); color:var(--yellow); }
        .divider-px {
            width:40px; height:2px;
            background:linear-gradient(90deg, var(--pink), var(--yellow));
            border-radius:2px; margin-bottom:20px;
        }

        /* ══════════════════════════════════════════
           BOOKING FORM
        ══════════════════════════════════════════ */
        .form-label-px {
            font-family:'Syne', sans-serif; font-weight:700;
            font-size:.68rem; letter-spacing:.13em; text-transform:uppercase;
            color:var(--muted); margin-bottom:7px; display:block;
        }
        .form-ctrl-px {
            width:100%;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.12);
            border-radius:11px; padding:11px 14px;
            color:var(--text);
            font-family:'DM Sans', sans-serif; font-size:.88rem;
            outline:none; transition:.25s;
            appearance:none; -webkit-appearance:none;
        }
        .form-ctrl-px::placeholder { color:rgba(220,215,255,.28); }
        .form-ctrl-px:focus {
            border-color:rgba(255,62,128,.50);
            background:rgba(255,255,255,.09);
            box-shadow:0 0 0 3px rgba(255,62,128,.11);
        }
        select.form-ctrl-px { cursor:pointer; }
        select.form-ctrl-px option { background:#160F30; color:var(--text); }
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator { filter:invert(1) opacity(.45); cursor:pointer; }

        /* estimasi box */
        .estimasi-box {
            background:rgba(255,209,102,.08);
            border:1px dashed rgba(255,209,102,.35);
            border-radius:14px; padding:16px 18px;
            display:flex; justify-content:space-between; align-items:center;
        }
        .estimasi-label { font-size:.78rem; color:var(--muted); }
        .estimasi-val {
            font-family:'Syne', sans-serif; font-weight:800; font-size:1.2rem;
            background:linear-gradient(135deg, var(--yellow), #D9960A);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }

        /* submit btn */
        .btn-submit-px {
            font-family:'Syne', sans-serif; font-weight:800;
            letter-spacing:.07em; text-transform:uppercase; font-size:.84rem;
            background:linear-gradient(135deg, var(--pink), #C41A5C);
            color:#fff; border:none; width:100%; padding:14px;
            border-radius:13px; cursor:pointer;
            transition:transform .2s, box-shadow .2s;
            box-shadow:0 6px 22px var(--pink-glow);
            display:flex; align-items:center; justify-content:center; gap:9px;
        }
        .btn-submit-px:hover {
            transform:translateY(-3px);
            box-shadow:0 14px 36px var(--pink-glow);
        }
        .btn-submit-px:active { transform:translateY(0); }

        /* unit card grid */
        .unit-cards { display:flex; flex-direction:column; gap:8px; }
        .unit-card-item {
            display:flex; align-items:center; gap:12px;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.10);
            border-radius:12px; padding:12px 14px;
            cursor:pointer; transition:.25s;
        }
        .unit-card-item:hover, .unit-card-item.selected {
            border-color:rgba(255,62,128,.45);
            background:rgba(255,62,128,.07);
        }
        .unit-card-item.selected { box-shadow:0 4px 16px rgba(255,62,128,.18); }
        .unit-radio { display:none; }
        .unit-dot {
            width:16px; height:16px; border-radius:50%;
            border:2px solid rgba(255,255,255,.25);
            flex-shrink:0; transition:.2s;
            display:flex; align-items:center; justify-content:center;
        }
        .unit-card-item.selected .unit-dot {
            border-color:var(--pink);
            background:var(--pink);
            box-shadow:0 0 8px var(--pink-glow);
        }
        .unit-card-item.selected .unit-dot::after {
            content:''; width:6px; height:6px; border-radius:50%; background:#fff;
        }
        .unit-name { font-weight:600; font-size:.88rem; flex:1; }
        .unit-price {
            font-family:'Syne', sans-serif; font-weight:700; font-size:.78rem;
            color:var(--yellow);
        }
        .unit-badge {
            font-size:.62rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
            padding:3px 9px; border-radius:20px;
            background:rgba(0,217,126,.14); border:1px solid rgba(0,217,126,.28); color:var(--green);
        }

        /* ══════════════════════════════════════════
           RIWAYAT TABLE
        ══════════════════════════════════════════ */
        .px-table { width:100%; border-collapse:collapse; }
        .px-table thead tr { border-bottom:1px solid rgba(255,255,255,.09); }
        .px-table thead th {
            padding:9px 13px; font-family:'Syne', sans-serif; font-weight:700;
            font-size:.65rem; letter-spacing:.14em; text-transform:uppercase;
            color:rgba(220,215,255,.42); text-align:left;
        }
        .px-table tbody tr { border-bottom:1px solid rgba(255,255,255,.05); transition:.2s; }
        .px-table tbody tr:hover { background:rgba(255,62,128,.04); }
        .px-table tbody tr:last-child { border:none; }
        .px-table tbody td { padding:12px 13px; font-size:.84rem; color:var(--muted); }
        .px-table tbody td strong { color:var(--text); font-weight:500; }

        /* status badge */
        .sbadge {
            padding:3px 11px; border-radius:20px;
            font-size:.64rem; font-family:'Syne', sans-serif; font-weight:700;
            letter-spacing:.08em; text-transform:uppercase;
        }
        .sb-pending   { background:rgba(255,209,102,.16); border:1px solid rgba(255,209,102,.32); color:var(--yellow); }
        .sb-confirmed { background:rgba(0,217,126,.13);   border:1px solid rgba(0,217,126,.30);   color:var(--green); }
        .sb-selesai   { background:rgba(0,217,126,.13);   border:1px solid rgba(0,217,126,.30);   color:var(--green); }
        .sb-batal     { background:rgba(255,62,128,.13);  border:1px solid rgba(255,62,128,.30);  color:var(--pink); }

        /* empty state */
        .empty-state { text-align:center; padding:44px 16px; color:var(--muted); }
        .empty-icon {
            width:60px; height:60px; border-radius:16px;
            background:rgba(255,62,128,.09); border:1px solid rgba(255,62,128,.18);
            display:flex; align-items:center; justify-content:center;
            font-size:1.5rem; color:rgba(255,62,128,.45);
            margin:0 auto 14px;
        }
        .empty-title { font-family:'Syne', sans-serif; font-weight:700; font-size:.88rem; color:var(--text); margin-bottom:5px; }

        /* view all link */
        .link-all {
            font-family:'Syne', sans-serif; font-weight:700;
            font-size:.72rem; letter-spacing:.07em; text-transform:uppercase;
            color:var(--pink-soft); text-decoration:none;
            display:inline-flex; align-items:center; gap:6px;
            transition:.2s;
        }
        .link-all:hover { color:var(--yellow); gap:9px; text-decoration:none; }

        /* ══════════════════════════════════════════
           TOAST NOTIF
        ══════════════════════════════════════════ */
        .toast-px {
            position:fixed; bottom:24px; right:24px; z-index:9999;
            background:rgba(22,15,48,.96);
            border:1px solid rgba(255,62,128,.35);
            border-radius:14px; padding:14px 20px;
            display:flex; align-items:center; gap:12px;
            font-size:.85rem; color:var(--text);
            box-shadow:0 16px 40px rgba(0,0,0,.40);
            transform:translateY(80px); opacity:0;
            transition:transform .4s cubic-bezier(.16,1,.3,1), opacity .4s;
            pointer-events:none; min-width:240px;
        }
        .toast-px.show { transform:translateY(0); opacity:1; pointer-events:auto; }
        .toast-icon { font-size:1.1rem; }

        /* ══════════════════════════════════════════
           LOADER
        ══════════════════════════════════════════ */
        #px-loader {
            position:fixed; inset:0; z-index:10000;
            background:var(--bg);
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            gap:16px; transition:opacity .5s;
        }
        .loader-logo {
            font-family:'Bebas Neue', sans-serif;
            font-size:2.4rem; letter-spacing:.12em;
            background:linear-gradient(135deg, var(--pink), var(--yellow));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .loader-bar-wrap {
            width:160px; height:2px;
            background:rgba(255,255,255,.08); border-radius:2px; overflow:hidden;
        }
        .loader-bar {
            height:100%; width:0%;
            background:linear-gradient(90deg, var(--pink), var(--yellow));
            border-radius:2px; transition:width 1s ease;
        }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        @media(max-width:768px) {
            .hero-banner { padding:26px 22px; }
            .main-wrap   { padding:calc(var(--nav-h) + 18px) 14px 44px; }
        }
    </style>
</head>
<body>

<!-- LOADER -->
<div id="px-loader">
    <div class="loader-logo">PixelStation</div>
    <div class="loader-bar-wrap">
        <div class="loader-bar" id="lbar"></div>
    </div>
</div>

<!-- ══ NAVBAR ══════════════════════════════════════════════════════════════ -->
<nav class="px-nav">
    <a href="#" class="nav-brand">
        <div class="nav-brand-icon">🎮</div>
        PIXELSTATION
    </a>
    <div class="nav-spacer"></div>
    <a href="profil.php" class="nav-pill text-decoration-none">
        <div class="nav-avatar"><?= strtoupper(substr($user_name, 0, 1)); ?></div>
        <span><?= htmlspecialchars($user_name); ?></span>
        <i class="fas fa-chevron-right fa-xs" style="opacity:.4;"></i>
    </a>
    <a href="../logout.php" class="btn-nav-logout ms-2">
        <i class="fas fa-right-from-bracket fa-xs me-1"></i> Logout
    </a>
</nav>

<!-- ══ MAIN ════════════════════════════════════════════════════════════════ -->
<div class="main-wrap">

    <!-- HERO BANNER -->
    <div class="hero-banner mb-4">
        <div class="hero-grid"></div>
        <div class="hero-orb"></div>
        <div class="hero-orb2"></div>
        <div class="hero-tag">
            <span class="dot-live"></span>
            Member Dashboard
        </div>
        <div class="hero-title">
            Halo, <span><?= htmlspecialchars($user_name); ?></span>!
        </div>
        <p class="hero-sub">Selamat datang kembali. Yuk booking unit PlayStation favoritmu sekarang.</p>
    </div>

    <!-- STAT CARDS -->
    <div class="section-title">Statistik Kamu</div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="sc-icon pink"><i class="fas fa-calendar-check"></i></div>
                <div class="sc-label">Total Booking</div>
                <div class="sc-num"><?= $stat_total['t'] ?? 0; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="sc-icon green"><i class="fas fa-circle-check"></i></div>
                <div class="sc-label">Selesai</div>
                <div class="sc-num is-green"><?= $stat_selesai['t'] ?? 0; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="sc-icon yellow"><i class="fas fa-clock"></i></div>
                <div class="sc-label">Menunggu</div>
                <div class="sc-num is-yellow"><?= $stat_pending['t'] ?? 0; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="sc-icon purple"><i class="fas fa-wallet"></i></div>
                <div class="sc-label">Total Spend</div>
                <div class="sc-val mt-1">Rp <?= number_format($stat_spend['t'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <!-- BOOKING FORM + RIWAYAT -->
    <div class="section-title">Aktivitas</div>
    <div class="row g-4">

        <!-- ── FORM BOOKING ── -->
        <div class="col-lg-5">
            <div class="glass-panel">
                <div class="panel-heading">
                    <div class="ph-icon yellow"><i class="fas fa-gamepad"></i></div>
                    Booking PlayStation
                </div>
                <div class="divider-px"></div>

                <form action="proses-booking.php" method="POST" id="bookingForm">

                    <!-- Pilih Unit -->
                    <div class="mb-3">
                        <label class="form-label-px">Pilih Unit PlayStation</label>
                        <?php if (mysqli_num_rows($query_ps) > 0): ?>
                        <div class="unit-cards" id="unitCards">
                            <?php
                            $first = true;
                            while ($ps = mysqli_fetch_assoc($query_ps)):
                            ?>
                            <label class="unit-card-item <?= $first ? 'selected' : ''; ?>" id="label_<?= $ps['id']; ?>">
                                <input type="radio" class="unit-radio" name="ps_id"
                                    value="<?= $ps['id']; ?>"
                                    data-harga="<?= $ps['harga_per_jam']; ?>"
                                    <?= $first ? 'checked' : ''; ?>>
                                <div class="unit-dot"></div>
                                <span class="unit-name"><?= htmlspecialchars($ps['nama_ps']); ?></span>
                                <span class="unit-price">Rp<?= number_format($ps['harga_per_jam']); ?>/jam</span>
                                <span class="unit-badge">Tersedia</span>
                            </label>
                            <?php $first = false; endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div style="text-align:center;padding:24px;color:var(--muted);font-size:.84rem;">
                            <i class="fas fa-gamepad" style="font-size:1.5rem;opacity:.3;display:block;margin-bottom:8px;"></i>
                            Tidak ada unit tersedia saat ini.
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal & Jam -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-px">Tanggal</label>
                            <input type="date" name="tanggal" id="fTanggal"
                                class="form-ctrl-px" required min="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label-px">Jam Mulai</label>
                            <input type="time" name="jam_mulai" id="fJam"
                                class="form-ctrl-px" required>
                        </div>
                    </div>

                    <!-- Durasi -->
                    <div class="mb-4">
                        <label class="form-label-px">Durasi Sewa</label>
                        <select name="durasi" id="fDurasi" class="form-ctrl-px">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i; ?>"><?= $i; ?> Jam</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Estimasi -->
                    <div class="estimasi-box mb-4">
                        <div>
                            <div class="estimasi-label">Estimasi Total Bayar</div>
                            <div style="font-size:.72rem;color:rgba(220,215,255,.35);margin-top:2px;">Harga × Durasi</div>
                        </div>
                        <div class="estimasi-val" id="estimasiVal">Rp 0</div>
                    </div>

                    <input type="hidden" name="total_harga" id="fTotal" value="0">

                    <button type="submit" class="btn-submit-px">
                        <i class="fas fa-bolt fa-sm"></i> Booking Sekarang
                    </button>
                </form>
            </div>
        </div>

        <!-- ── RIWAYAT ── -->
        <div class="col-lg-7">
            <div class="glass-panel">
                <div class="d-flex justify-content-between align-items-center mb-0">
                    <div class="panel-heading mb-0">
                        <div class="ph-icon"><i class="fas fa-clock-rotate-left"></i></div>
                        Riwayat Booking
                    </div>
                    <a href="riwayat.php" class="link-all">
                        Lihat Semua <i class="fas fa-arrow-right fa-xs"></i>
                    </a>
                </div>
                <div class="divider-px mt-3"></div>

                <?php
                $riwayat_rows = [];
                if ($query_riwayat) {
                    while ($r = mysqli_fetch_assoc($query_riwayat)) $riwayat_rows[] = $r;
                }
                ?>

                <?php if (empty($riwayat_rows)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-calendar-xmark"></i></div>
                    <div class="empty-title">Belum Ada Riwayat</div>
                    <p style="font-size:.82rem;">Yuk lakukan booking pertamamu!</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="px-table">
                        <thead>
                            <tr>
                                <th>Unit PlayStation</th>
                                <th>Jadwal</th>
                                <th>Durasi</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($riwayat_rows as $row):
                            $sc = match($row['status']) {
                                'pending'   => 'sb-pending',
                                'confirmed' => 'sb-confirmed',
                                'selesai'   => 'sb-selesai',
                                'batal'     => 'sb-batal',
                                default     => ''
                            };
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['nama_unit']); ?></strong></td>
                            <td>
                                <span style="font-size:.80rem;">
                                    <?= date('d M Y', strtotime($row['tanggal'])); ?>
                                </span><br>
                                <span style="font-size:.76rem;color:rgba(220,215,255,.35);">
                                    <i class="fas fa-clock fa-xs"></i>
                                    <?= htmlspecialchars($row['jam_mulai']); ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <strong><?= $row['durasi']; ?></strong>
                                <span style="font-size:.76rem;color:rgba(220,215,255,.30);"> jam</span>
                            </td>
                            <td><strong>Rp <?= number_format($row['total_harga']); ?></strong></td>
                            <td><span class="sbadge <?= $sc; ?>"><?= ucfirst($row['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Profil shortcut -->
                <div style="margin-top:20px;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);">
                    <a href="profil.php" style="
                        display:flex; align-items:center; gap:14px; text-decoration:none;
                        background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.10);
                        border-radius:14px; padding:14px 16px; transition:.25s;
                    " onmouseover="this.style.borderColor='rgba(255,62,128,.38)'"
                       onmouseout="this.style.borderColor='rgba(255,255,255,.10)'">
                        <div style="
                            width:40px;height:40px;border-radius:11px;
                            background:linear-gradient(135deg,var(--pink),var(--yellow));
                            display:flex;align-items:center;justify-content:center;
                            font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:#fff;
                            flex-shrink:0;box-shadow:0 4px 14px var(--pink-glow);
                        "><?= strtoupper(substr($user_name, 0, 1)); ?></div>
                        <div style="flex:1;">
                            <div style="font-weight:600;font-size:.88rem;color:var(--text);"><?= htmlspecialchars($user_name); ?></div>
                            <div style="font-size:.74rem;color:var(--muted);">Lihat & edit profil saya</div>
                        </div>
                        <i class="fas fa-chevron-right fa-xs" style="color:var(--muted);"></i>
                    </a>
                </div>

            </div><!-- /glass-panel -->
        </div>

    </div><!-- /row -->
</div><!-- /main-wrap -->

<!-- TOAST -->
<div class="toast-px" id="toastNotif">
    <span class="toast-icon">✅</span>
    <span id="toastMsg">Booking berhasil dikirim!</span>
</div>

<!-- ══ SCRIPTS ═══════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Loader ── */
window.addEventListener('load', () => {
    document.getElementById('lbar').style.width = '100%';
    setTimeout(() => {
        const loader = document.getElementById('px-loader');
        loader.style.opacity = '0';
        setTimeout(() => loader.remove(), 500);
    }, 900);
});

/* ── Unit card selection ── */
document.querySelectorAll('.unit-card-item').forEach(label => {
    label.addEventListener('click', () => {
        document.querySelectorAll('.unit-card-item').forEach(l => l.classList.remove('selected'));
        label.classList.add('selected');
        label.querySelector('.unit-radio').checked = true;
        calcEstimasi();
    });
});

/* ── Estimasi ── */
function calcEstimasi() {
    const radio  = document.querySelector('input[name="ps_id"]:checked');
    const harga  = radio ? parseInt(radio.getAttribute('data-harga')) || 0 : 0;
    const durasi = parseInt(document.getElementById('fDurasi').value) || 1;
    const total  = harga * durasi;

    document.getElementById('estimasiVal').textContent =
        'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    document.getElementById('fTotal').value = total;
}

document.getElementById('fDurasi').addEventListener('change', calcEstimasi);
calcEstimasi();

/* ── Form submit — toast ── */
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const tanggal = document.getElementById('fTanggal').value;
    const jam     = document.getElementById('fJam').value;
    if (!tanggal || !jam) return; // biarkan HTML5 validation handle

    const toast = document.getElementById('toastNotif');
    document.getElementById('toastMsg').textContent = 'Memproses booking kamu…';
    toast.classList.add('show');
});

/* ── Tampilkan toast jika dari URL ── */
const params = new URLSearchParams(window.location.search);
if (params.get('success') === '1') {
    const toast = document.getElementById('toastNotif');
    document.getElementById('toastMsg').textContent = '✅ Booking berhasil dikirim!';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}
if (params.get('error') === '1') {
    const toast = document.getElementById('toastNotif');
    document.getElementById('toastMsg').textContent = '❌ Booking gagal. Coba lagi.';
    toast.style.borderColor = 'rgba(255,62,128,.55)';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}
</script>
</body>
</html>