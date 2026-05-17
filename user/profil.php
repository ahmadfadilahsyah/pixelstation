<?php
require_once dirname(__FILE__) . '/../config/session.php';
require_once dirname(__FILE__) . '/../config/koneksi.php';

global $conn;

$user_id = $_SESSION['id'];
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$data = mysqli_fetch_assoc($query_user);

$stat_booking = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM bookings WHERE user_id = '$user_id'"));
$jumlah_booking = $stat_booking['t'] ?? 0;
$status_member = $jumlah_booking >= 10 ? 'Elite Member' : ($jumlah_booking >= 3 ? 'Regular Member' : 'New Member');
$member_since = date('d M Y', strtotime($data['created_at'] ?? date('Y-m-d')));

$badge_icon  = $jumlah_booking >= 10 ? 'fa-gem' : ($jumlah_booking >= 3 ? 'fa-star' : 'fa-seedling');
$badge_color = $jumlah_booking >= 10 ? '#a78bfa,#7c3aed' : ($jumlah_booking >= 3 ? '#FFD166,#FFA500' : '#34d399,#059669');

$progress_to_next = 0;
$next_label = '';
if ($jumlah_booking < 3) {
    $progress_to_next = round(($jumlah_booking / 3) * 100);
    $next_label = "Regular Member";
} elseif ($jumlah_booking < 10) {
    $progress_to_next = round((($jumlah_booking - 3) / 7) * 100);
    $next_label = "Elite Member";
} else {
    $progress_to_next = 100;
    $next_label = "Max Level";
}

$avatar_letter = strtoupper(substr($data['nama'] ?? '?', 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | PixelStation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,500;0,9..40,700;1,9..40,300&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg:        #080810;
            --bg2:       #0e0e1a;
            --pink:      #FF3E80;
            --yellow:    #FFD166;
            --purple:    #a78bfa;
            --glass:     rgba(255,255,255,0.04);
            --glass-b:   rgba(255,255,255,0.08);
            --text:      #EDE9F8;
            --muted:     rgba(237,233,248,0.45);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ─── animated background noise ─── */
        body::before {
            content: '';
            position: fixed; inset: 0; z-index: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
            opacity: 0.03;
            pointer-events: none;
        }

        /* ─── radial glow blobs ─── */
        .blob {
            position: fixed; border-radius: 50%;
            filter: blur(90px); pointer-events: none; z-index: 0;
            animation: drift 12s ease-in-out infinite alternate;
        }
        .blob-1 { width:500px; height:500px; background: rgba(255,62,128,.13); top:-120px; left:-100px; animation-delay: 0s; }
        .blob-2 { width:400px; height:400px; background: rgba(167,139,250,.1); bottom:-80px; right:-100px; animation-delay:-5s; }
        .blob-3 { width:300px; height:300px; background: rgba(255,209,102,.07); top:40%; left:40%; animation-delay:-8s; }

        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(30px,20px) scale(1.08); }
        }

        /* ─── navbar ─── */
        .navbar {
            background: rgba(8,8,16,.75);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,.05);
            padding: 14px 0;
            position: fixed; top:0; width:100%; z-index:100;
        }
        .navbar-brand {
            font-family: 'Bebas Neue';
            font-size: 1.9rem;
            color: var(--text) !important;
            letter-spacing: 2px;
        }
        .navbar-brand span { color: var(--pink); }
        .btn-back {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--muted);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 8px;
            padding: 8px 20px;
            text-decoration: none;
            transition: .25s;
        }
        .btn-back:hover { color: var(--text); border-color: rgba(255,255,255,.3); background: rgba(255,255,255,.04); }

        /* ─── page wrapper ─── */
        .page { position: relative; z-index: 1; padding-top: 80px; }

        /* ─── hero strip ─── */
        .hero-strip {
            padding: 60px 0 0;
            text-align: center;
            animation: fadeUp .6s ease both;
        }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ─── avatar ─── */
        .avatar-wrap {
            position: relative;
            display: inline-block;
            margin-bottom: 24px;
        }
        .avatar-ring {
            position: absolute; inset: -6px;
            border-radius: 50%;
            background: conic-gradient(var(--pink), var(--yellow), var(--purple), var(--pink));
            animation: spin 4s linear infinite;
            z-index: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .avatar-ring::after {
            content:'';
            position:absolute; inset: 5px;
            border-radius:50%;
            background: var(--bg);
        }
        .avatar-circle {
            position: relative; z-index: 1;
            width: 110px; height: 110px;
            background: linear-gradient(135deg, var(--pink), var(--yellow));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.8rem;
            color: #fff;
            font-family: 'Bebas Neue';
            box-shadow: 0 0 40px rgba(255,62,128,.35);
        }

        /* ─── name & badge ─── */
        .hero-name {
            font-family: 'Bebas Neue';
            font-size: clamp(2rem, 5vw, 3rem);
            letter-spacing: 3px;
            line-height: 1;
            margin-bottom: 12px;
        }
        .badge-pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(90deg, <?= $badge_color ?>);
            color: #000;
            font-family: 'Syne'; font-weight: 800;
            font-size: .8rem; letter-spacing: 1px; text-transform: uppercase;
            padding: 7px 20px;
            border-radius: 50px;
            box-shadow: 0 6px 20px rgba(0,0,0,.35);
            margin-bottom: 32px;
        }

        /* ─── divider line ─── */
        .divider-line {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,62,128,.4), rgba(255,209,102,.3), transparent);
            margin: 0 auto 40px;
            max-width: 600px;
        }

        /* ─── stats row ─── */
        .stats-row {
            display: flex; justify-content: center; gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 40px;
            animation: fadeUp .6s .15s ease both;
        }
        .stat-chip {
            background: var(--glass);
            border: 1px solid var(--glass-b);
            border-radius: 14px;
            padding: 18px 28px;
            text-align: center;
            min-width: 130px;
            transition: .25s;
        }
        .stat-chip:hover { background: rgba(255,255,255,.07); transform: translateY(-3px); }
        .stat-num {
            font-family: 'Bebas Neue';
            font-size: 2.2rem;
            line-height: 1;
            color: var(--yellow);
        }
        .stat-lbl { font-size: .72rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--muted); margin-top: 4px; }

        /* ─── main card ─── */
        .main-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-b);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 24px;
            animation: fadeUp .6s .25s ease both;
        }

        .section-title {
            font-family: 'Syne';
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--pink);
            margin-bottom: 24px;
            display: flex; align-items: center; gap: 10px;
        }
        .section-title::after {
            content:''; flex:1; height:1px;
            background: linear-gradient(90deg, rgba(255,62,128,.3), transparent);
        }

        /* ─── info grid ─── */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }

        @media(max-width:576px) { .info-grid { grid-template-columns: 1fr; } }

        .info-item {
            padding: 18px 0;
            border-bottom: 1px solid rgba(255,255,255,.04);
        }
        .info-item:nth-child(odd)  { padding-right: 24px; border-right: 1px solid rgba(255,255,255,.04); }
        .info-item:nth-child(even) { padding-left: 24px; }
        .info-item:nth-last-child(-n+2) { border-bottom: none; }

        .info-label {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--pink);
            font-family: 'Syne';
            margin-bottom: 6px;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text);
        }
        .info-value .highlight { color: var(--yellow); }

        /* ─── progress card ─── */
        .progress-card {
            background: rgba(255,62,128,.06);
            border: 1px solid rgba(255,62,128,.15);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 24px;
            animation: fadeUp .6s .35s ease both;
        }
        .progress-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .progress-title { font-family: 'Syne'; font-size: .78rem; letter-spacing: 1.5px; text-transform: uppercase; color: var(--yellow); }
        .progress-pct { font-family: 'Bebas Neue'; font-size: 1.6rem; color: var(--yellow); }

        .prog-bar-track {
            height: 8px; border-radius: 100px;
            background: rgba(255,255,255,.07);
            overflow: hidden;
            margin-bottom: 10px;
        }
        .prog-bar-fill {
            height: 100%; border-radius: 100px;
            background: linear-gradient(90deg, var(--pink), var(--yellow));
            width: <?= $progress_to_next ?>%;
            transition: width 1.2s cubic-bezier(.4,0,.2,1);
            box-shadow: 0 0 12px rgba(255,62,128,.5);
        }
        .prog-labels { display: flex; justify-content: space-between; }
        .prog-label-sm { font-size: .7rem; color: var(--muted); letter-spacing: .5px; }

        /* ─── perks card ─── */
        .perks-card {
            background: var(--glass);
            border: 1px solid var(--glass-b);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 32px;
            animation: fadeUp .6s .45s ease both;
        }
        .perk-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .perk-list li {
            display: flex; align-items: center; gap: 12px;
            font-size: .9rem; color: var(--muted);
        }
        .perk-list li .perk-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: rgba(255,62,128,.12);
            display: flex; align-items: center; justify-content: center;
            color: var(--pink); font-size: .8rem; flex-shrink: 0;
        }
        .perk-list li span b { color: var(--text); font-weight: 600; }

        /* ─── edit button ─── */
        .btn-edit-profile {
            display: inline-flex; align-items: center; gap: 10px;
            background: transparent;
            border: 1.5px solid rgba(255,62,128,.5);
            color: var(--pink);
            border-radius: 12px;
            padding: 12px 32px;
            font-family: 'Syne'; font-weight: 800;
            font-size: .8rem; letter-spacing: 1.5px; text-transform: uppercase;
            cursor: pointer;
            transition: .3s;
            position: relative; overflow: hidden;
        }
        .btn-edit-profile::before {
            content:'';
            position:absolute; inset:0;
            background: linear-gradient(135deg, var(--pink), #c02060);
            opacity:0; transition: .3s;
        }
        .btn-edit-profile:hover::before { opacity:1; }
        .btn-edit-profile:hover { color:#fff; border-color: var(--pink); box-shadow: 0 8px 24px rgba(255,62,128,.25); }
        .btn-edit-profile span, .btn-edit-profile i { position:relative; z-index:1; }

        /* ─── scrollbar ─── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: rgba(255,62,128,.4); border-radius: 3px; }
    </style>
</head>
<body>

<!-- blobs -->
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<!-- navbar -->
<nav class="navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="dashboard-user.php">🎮 PIXEL<span>STATION</span></a>
        <a href="dashboard-user.php" class="btn-back"><i class="fa fa-arrow-left me-2"></i>Dashboard</a>
    </div>
</nav>

<div class="page">
    <div class="container" style="max-width:680px;">

        <!-- ── Hero ── -->
        <div class="hero-strip">
            <div class="avatar-wrap">
                <div class="avatar-ring"></div>
                <div class="avatar-circle"><?= $avatar_letter ?></div>
            </div>
            <div class="hero-name"><?= htmlspecialchars($data['nama'] ?? '-') ?></div>
            <div class="badge-pill">
                <i class="fa-solid <?= $badge_icon ?>"></i>
                <?= $status_member ?>
            </div>
        </div>

        <div class="divider-line"></div>

        <!-- ── Stats chips ── -->
        <div class="stats-row">
            <div class="stat-chip">
                <div class="stat-num"><?= $jumlah_booking ?></div>
                <div class="stat-lbl">Total Sesi</div>
            </div>
            <div class="stat-chip">
                <div class="stat-num"><?= $progress_to_next ?>%</div>
                <div class="stat-lbl">Level Progress</div>
            </div>
            <div class="stat-chip">
                <div class="stat-num"><?= date('Y') - date('Y', strtotime($data['created_at'] ?? 'now')) ?: '<1' ?></div>
                <div class="stat-lbl">Tahun Bergabung</div>
            </div>
        </div>

        <!-- ── Info Card ── -->
        <div class="main-card">
            <div class="section-title">Informasi Akun</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-value"><?= htmlspecialchars($data['nama'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?= htmlspecialchars($data['email'] ?? 'Belum diatur') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status Keanggotaan</div>
                    <div class="info-value"><span class="highlight"><?= $status_member ?></span></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Member Sejak</div>
                    <div class="info-value"><?= $member_since ?></div>
                </div>
            </div>
        </div>

        <!-- ── Progress Card ── -->
        <div class="progress-card">
            <div class="progress-header">
                <div class="progress-title">
                    <i class="fa-solid fa-chart-line me-2"></i>Progress ke <?= $next_label ?>
                </div>
                <div class="progress-pct"><?= $progress_to_next ?>%</div>
            </div>
            <div class="prog-bar-track">
                <div class="prog-bar-fill"></div>
            </div>
            <div class="prog-labels">
                <span class="prog-label-sm"><?= $status_member ?></span>
                <span class="prog-label-sm"><?= $next_label ?></span>
            </div>
        </div>

        <!-- ── Perks Card ── -->
        <div class="perks-card">
            <div class="section-title">Keuntungan Kamu</div>
            <ul class="perk-list">
                <li>
                    <div class="perk-icon"><i class="fa-solid fa-tag"></i></div>
                    <span><?php if ($jumlah_booking >= 3): ?><b>Diskon 20%</b> otomatis untuk setiap booking<?php else: ?>Raih <b>Regular Member</b> untuk diskon 20%<?php endif; ?></span>
                </li>
                <li>
                    <div class="perk-icon"><i class="fa-solid fa-bolt"></i></div>
                    <span><?php if ($jumlah_booking >= 3): ?><b>Prioritas booking</b> di jam-jam peak<?php else: ?>Butuh <b><?= 3 - $jumlah_booking ?> sesi lagi</b> untuk prioritas booking<?php endif; ?></span>
                </li>
                <li>
                    <div class="perk-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <span>Status diperbarui <b>otomatis</b> setiap bulan berdasarkan aktivitas bermain</span>
                </li>
            </ul>
        </div>

        <!-- ── Edit button ── -->
        <div class="text-center pb-5">
            <button class="btn-edit-profile">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Profil</span>
            </button>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // animate progress bar on load
    document.addEventListener('DOMContentLoaded', () => {
        const fill = document.querySelector('.prog-bar-fill');
        const target = fill.style.width;
        fill.style.width = '0';
        requestAnimationFrame(() => { fill.style.width = target; });
    });
</script>
</body>
</html>