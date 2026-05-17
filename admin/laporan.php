<?php
include "../config/session.php";
include "../config/koneksi.php";

if (!isAdmin()) {
    header("Location: ../user/dashboard-user.php");
    exit;
}

$filter     = $_GET['filter']     ?? 'hari';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date   = $_GET['end_date']   ?? date('Y-m-d');

switch ($filter) {
    case 'hari':
        $date_condition = "DATE(tanggal) = CURDATE()";
        $period_label   = "Hari Ini — " . date('d F Y');
        break;
    case 'minggu':
        $date_condition = "YEARWEEK(tanggal) = YEARWEEK(CURDATE())";
        $period_label   = "Minggu Ini";
        break;
    case 'bulan':
        $date_condition = "MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
        $period_label   = date('F Y');
        break;
    case 'tahun':
        $date_condition = "YEAR(tanggal) = YEAR(CURDATE())";
        $period_label   = "Tahun " . date('Y');
        break;
    case 'custom':
        $sd = mysqli_real_escape_string($conn, $start_date);
        $ed = mysqli_real_escape_string($conn, $end_date);
        $date_condition = "tanggal BETWEEN '$sd' AND '$ed'";
        $period_label   = date('d M Y', strtotime($start_date)) . " — " . date('d M Y', strtotime($end_date));
        break;
    default:
        $date_condition = "DATE(tanggal) = CURDATE()";
        $period_label   = "Hari Ini";
}

$query = "
    SELECT bookings.*, users.nama AS user_nama, playstations.nama_ps
    FROM bookings
    JOIN users       ON bookings.user_id = users.id
    JOIN playstations ON bookings.ps_id  = playstations.id
    WHERE $date_condition
    ORDER BY bookings.tanggal DESC, bookings.jam_mulai ASC
";
$result = mysqli_query($conn, $query);
$rows   = [];
$total_pendapatan  = 0;
$total_durasi      = 0;
$status_count      = ['confirmed'=>0,'selesai'=>0,'pending'=>0,'batal'=>0];

while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    if (in_array($row['status'], ['confirmed','selesai'])) {
        $total_pendapatan += $row['total_harga'];
    }
    $total_durasi += $row['durasi'] ?? 0;
    if (isset($status_count[$row['status']])) $status_count[$row['status']]++;
}

$total_booking = count($rows);

// Build export query string
$export_params = http_build_query([
    'filter'     => $filter,
    'start_date' => $start_date,
    'end_date'   => $end_date,
]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan — PixelStation</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --pink:        #FF3E80;
            --pink-soft:   #FF6FA3;
            --pink-glow:   rgba(255,62,128,.40);
            --yellow:      #FFD166;
            --yellow-soft: #FFE299;
            --yellow-glow: rgba(255,209,102,.35);
            --border:      rgba(255,255,255,.14);
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
            background:
                radial-gradient(ellipse 90% 70% at 10% -5%,  rgba(120,60,220,.30) 0%, transparent 55%),
                radial-gradient(ellipse 70% 60% at 90% 110%, rgba(255,62,128,.18) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 55% 50%,  rgba(60,30,130,.50) 0%, transparent 70%),
                #0E0B20;
            background-attachment:fixed;
        }
        body::before {
            content:''; position:fixed; inset:0; pointer-events:none; z-index:0;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            opacity:.25;
        }

        .main-wrapper {
            margin-left:var(--sidebar-w);
            min-height:100vh;
            position:relative; z-index:1;
            padding:28px 32px 56px;
        }

        /* ─── TOPBAR ─── */
        .topbar {
            display:flex; justify-content:space-between; align-items:center;
            padding:14px 22px;
            background:rgba(255,255,255,.08);
            border:1px solid var(--border);
            border-radius:16px;
            backdrop-filter:blur(24px);
            margin-bottom:26px;
        }
        .topbar-title {
            font-family:'Syne',sans-serif; font-weight:800;
            font-size:.95rem; letter-spacing:.07em;
            display:flex; align-items:center; gap:10px;
        }
        .dot-live {
            width:8px; height:8px; border-radius:50%;
            background:var(--pink); box-shadow:0 0 10px var(--pink-glow);
            animation:blink 1.6s ease-in-out infinite; flex-shrink:0;
        }
        @keyframes blink { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.25;transform:scale(.7)} }
        .topbar-user { display:flex; align-items:center; gap:10px; font-size:.85rem; color:var(--muted); }
        .topbar-avatar {
            width:36px; height:36px; border-radius:10px;
            background:linear-gradient(135deg,var(--pink),var(--yellow));
            display:flex; align-items:center; justify-content:center;
            font-family:'Bebas Neue',sans-serif; font-size:1rem; color:#fff;
            box-shadow:0 4px 16px var(--pink-glow);
        }

        /* ─── PAGE HEADER ─── */
        .page-header {
            position:relative; overflow:hidden;
            background:linear-gradient(135deg,rgba(255,255,255,.11),rgba(255,255,255,.06));
            border:1px solid rgba(255,255,255,.18);
            border-radius:22px; padding:36px 44px;
            margin-bottom:26px; backdrop-filter:blur(20px);
        }
        .page-header::before {
            content:''; position:absolute; inset:0; pointer-events:none;
            background:
                radial-gradient(ellipse 65% 140% at 95% 50%, rgba(255,62,128,.18), transparent 65%),
                radial-gradient(ellipse 50% 80% at 5%  80%, rgba(255,209,102,.10), transparent 60%);
        }
        .page-grid {
            position:absolute; inset:0; pointer-events:none;
            background-image:linear-gradient(rgba(255,62,128,.07) 1px,transparent 1px),linear-gradient(90deg,rgba(255,62,128,.07) 1px,transparent 1px);
            background-size:44px 44px;
            mask-image:radial-gradient(ellipse 80% 100% at 80% 50%,black,transparent);
        }
        .page-orb {
            position:absolute; right:-40px; top:50%; transform:translateY(-50%);
            width:220px; height:220px; border-radius:50%;
            background:radial-gradient(circle,rgba(255,62,128,.20) 0%,transparent 70%);
            pointer-events:none;
        }
        .page-label {
            display:inline-flex; align-items:center; gap:8px;
            background:rgba(255,62,128,.15); border:1px solid rgba(255,62,128,.35);
            color:var(--pink-soft); font-size:.70rem; letter-spacing:.16em;
            padding:5px 14px; border-radius:30px;
            font-family:'Syne',sans-serif; font-weight:700;
            text-transform:uppercase; margin-bottom:14px;
        }
        .page-h {
            font-family:'Bebas Neue',sans-serif;
            font-size:clamp(2rem,4vw,2.8rem); letter-spacing:.06em; line-height:1; margin-bottom:8px;
        }
        .page-h span { background:linear-gradient(135deg,var(--pink),var(--yellow)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .page-sub { color:rgba(220,215,255,.65); font-size:.88rem; line-height:1.7; }

        /* ─── FILTER CARD ─── */
        .glass-card {
            background:rgba(255,255,255,.08);
            border:1px solid var(--border);
            border-radius:22px; padding:28px 30px;
            backdrop-filter:blur(20px); position:relative; overflow:hidden;
        }
        .glass-card::after {
            content:''; position:absolute; top:0; left:0; right:0; height:1px;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.28),transparent);
            pointer-events:none;
        }
        .card-heading {
            font-family:'Syne',sans-serif; font-weight:700;
            font-size:.88rem; letter-spacing:.07em; text-transform:uppercase;
            display:flex; align-items:center; gap:12px; margin-bottom:20px;
        }
        .ch-icon {
            width:36px; height:36px; border-radius:10px;
            background:rgba(255,62,128,.16); border:1px solid rgba(255,62,128,.28);
            display:flex; align-items:center; justify-content:center;
            font-size:.95rem; color:var(--pink); flex-shrink:0;
        }
        .divider-line {
            width:44px; height:2px;
            background:linear-gradient(90deg,var(--pink),var(--yellow));
            border-radius:2px; margin-bottom:22px;
        }

        /* ─── FILTER TABS ─── */
        .filter-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
        .filter-tab {
            font-family:'Syne',sans-serif; font-weight:700;
            font-size:.75rem; letter-spacing:.07em; text-transform:uppercase;
            padding:8px 18px; border-radius:10px; cursor:pointer;
            border:1px solid rgba(255,255,255,.14);
            background:rgba(255,255,255,.06);
            color:var(--muted); text-decoration:none;
            transition:.25s;
        }
        .filter-tab:hover { border-color:rgba(255,62,128,.35); color:var(--text); background:rgba(255,62,128,.08); text-decoration:none; }
        .filter-tab.active {
            background:linear-gradient(135deg,rgba(255,62,128,.25),rgba(255,209,102,.15));
            border-color:rgba(255,62,128,.50); color:var(--text);
            box-shadow:0 4px 16px rgba(255,62,128,.20);
        }

        /* ─── FORM ELEMENTS ─── */
        .form-label-px {
            font-family:'Syne',sans-serif; font-weight:700;
            font-size:.72rem; letter-spacing:.12em; text-transform:uppercase;
            color:var(--muted); margin-bottom:8px; display:block;
        }
        .form-control-px {
            width:100%; background:rgba(255,255,255,.07);
            border:1px solid rgba(255,255,255,.14); border-radius:12px;
            padding:10px 14px; color:var(--text);
            font-family:'DM Sans',sans-serif; font-size:.88rem;
            outline:none; transition:.25s;
        }
        .form-control-px:focus {
            border-color:rgba(255,62,128,.50); background:rgba(255,255,255,.10);
            box-shadow:0 0 0 3px rgba(255,62,128,.12);
        }
        input[type="date"]::-webkit-calendar-picker-indicator { filter:invert(1) opacity(.5); cursor:pointer; }

        /* ─── BUTTONS ─── */
        .btn-px {
            font-family:'Syne',sans-serif; font-weight:700;
            background:linear-gradient(135deg,var(--pink),#C41A5C);
            color:#fff; border:none; padding:11px 24px; border-radius:12px;
            letter-spacing:.05em; font-size:.82rem;
            text-decoration:none; display:inline-flex; align-items:center; gap:8px;
            transition:.2s; box-shadow:0 4px 20px var(--pink-glow); cursor:pointer;
        }
        .btn-px:hover { transform:translateY(-2px); box-shadow:0 10px 32px var(--pink-glow); color:#fff; text-decoration:none; }
        .btn-px-green {
            font-family:'Syne',sans-serif; font-weight:700;
            background:linear-gradient(135deg,#00C97A,#007A4B);
            color:#fff; border:none; padding:11px 24px; border-radius:12px;
            letter-spacing:.05em; font-size:.82rem;
            text-decoration:none; display:inline-flex; align-items:center; gap:8px;
            transition:.2s; box-shadow:0 4px 20px rgba(0,201,122,.30); cursor:pointer;
        }
        .btn-px-green:hover { transform:translateY(-2px); box-shadow:0 10px 32px rgba(0,201,122,.40); color:#fff; text-decoration:none; }
        .btn-px-red {
            font-family:'Syne',sans-serif; font-weight:700;
            background:linear-gradient(135deg,#FF4444,#B01A1A);
            color:#fff; border:none; padding:11px 24px; border-radius:12px;
            letter-spacing:.05em; font-size:.82rem;
            text-decoration:none; display:inline-flex; align-items:center; gap:8px;
            transition:.2s; box-shadow:0 4px 20px rgba(255,68,68,.30); cursor:pointer;
        }
        .btn-px-red:hover { transform:translateY(-2px); box-shadow:0 10px 32px rgba(255,68,68,.45); color:#fff; text-decoration:none; }
        .btn-px-outline {
            font-family:'Syne',sans-serif; font-weight:700;
            background:transparent; color:var(--muted);
            border:1px solid rgba(255,255,255,.18);
            padding:11px 24px; border-radius:12px;
            letter-spacing:.05em; font-size:.82rem;
            text-decoration:none; display:inline-flex; align-items:center; gap:8px;
            transition:.25s; cursor:pointer;
        }
        .btn-px-outline:hover { border-color:rgba(255,62,128,.45); color:var(--text); background:rgba(255,62,128,.08); text-decoration:none; }

        /* ─── STAT CARDS ─── */
        .stat-card {
            background:rgba(255,255,255,.08); border:1px solid var(--border);
            border-radius:20px; padding:22px 20px;
            position:relative; overflow:hidden;
            transition:.3s; backdrop-filter:blur(16px);
        }
        .stat-card::after {
            content:''; position:absolute; top:0; left:0; right:0; height:1px;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.25),transparent);
        }
        .stat-card:hover { transform:translateY(-5px); border-color:rgba(255,62,128,.40); box-shadow:0 20px 50px rgba(0,0,0,.30),0 0 30px var(--pink-glow); }
        .stat-label { font-size:.70rem; letter-spacing:.14em; text-transform:uppercase; color:var(--muted); font-family:'Syne',sans-serif; font-weight:700; margin-bottom:8px; }
        .stat-num {
            font-family:'Bebas Neue',sans-serif; font-size:2.4rem; letter-spacing:.04em; line-height:1;
            background:linear-gradient(135deg,var(--yellow-soft),var(--pink)); -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .stat-val { font-family:'Syne',sans-serif; font-weight:800; font-size:1.1rem; background:linear-gradient(135deg,var(--yellow-soft),var(--pink)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .stat-icon {
            position:absolute; top:18px; right:16px;
            width:42px; height:42px; border-radius:12px;
            background:rgba(255,62,128,.14); border:1px solid rgba(255,62,128,.28);
            display:flex; align-items:center; justify-content:center;
            font-size:.95rem; color:var(--pink-soft);
        }
        .stat-card.green .stat-icon { background:rgba(96,211,148,.14); border-color:rgba(96,211,148,.28); color:#60D394; }
        .stat-card.green .stat-num,.stat-card.green .stat-val { background:linear-gradient(135deg,#67EEA9,#00C97A); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .stat-card.yellow .stat-icon { background:rgba(255,209,102,.14); border-color:rgba(255,209,102,.28); color:var(--yellow); }

        /* ─── SECTION TITLE ─── */
        .section-title {
            font-family:'Bebas Neue',sans-serif; font-size:1rem; letter-spacing:.18em; text-transform:uppercase;
            color:rgba(220,215,255,.40); margin-bottom:14px;
            display:flex; align-items:center; gap:10px;
        }
        .section-title::after { content:''; flex:1; height:1px; background:linear-gradient(90deg,rgba(255,255,255,.08),transparent); }

        /* ─── TABLE ─── */
        .px-table { width:100%; border-collapse:collapse; }
        .px-table thead tr { border-bottom:1px solid rgba(255,255,255,.10); }
        .px-table thead th {
            padding:10px 14px; font-family:'Syne',sans-serif; font-weight:700;
            font-size:.68rem; letter-spacing:.14em; text-transform:uppercase;
            color:rgba(220,215,255,.50); text-align:left;
        }
        .px-table tbody tr { border-bottom:1px solid rgba(255,255,255,.05); transition:.2s; }
        .px-table tbody tr:hover { background:rgba(255,62,128,.05); }
        .px-table tbody td { padding:13px 14px; font-size:.87rem; color:var(--muted); }
        .px-table tbody td strong { color:var(--text); font-weight:500; }
        .px-table tfoot tr { border-top:1px solid rgba(255,255,255,.14); }
        .px-table tfoot td {
            padding:14px 14px; font-family:'Syne',sans-serif; font-weight:700;
            font-size:.82rem; letter-spacing:.04em;
            background:rgba(255,209,102,.06);
        }

        /* ─── BADGES ─── */
        .badge-px { padding:4px 13px; border-radius:20px; font-size:.68rem; font-family:'Syne',sans-serif; font-weight:700; letter-spacing:.09em; text-transform:uppercase; }
        .badge-pending   { background:rgba(255,209,102,.16); border:1px solid rgba(255,209,102,.35); color:var(--yellow); }
        .badge-confirmed { background:rgba(96,211,148,.14);  border:1px solid rgba(96,211,148,.35);  color:#60D394; }
        .badge-selesai   { background:rgba(96,211,148,.14);  border:1px solid rgba(96,211,148,.35);  color:#60D394; }
        .badge-batal     { background:rgba(255,62,128,.14);  border:1px solid rgba(255,62,128,.35);  color:var(--pink); }

        /* ─── NO BADGE ─── */
        .no-badge {
            width:30px; height:30px; border-radius:8px;
            background:rgba(255,62,128,.12); border:1px solid rgba(255,62,128,.24);
            display:inline-flex; align-items:center; justify-content:center;
            font-family:'Bebas Neue',sans-serif; font-size:.85rem; color:var(--pink);
        }

        /* ─── EMPTY STATE ─── */
        .empty-state { text-align:center; padding:60px 24px; color:var(--muted); }
        .empty-icon { width:72px; height:72px; border-radius:20px; background:rgba(255,62,128,.10); border:1px solid rgba(255,62,128,.20); display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:rgba(255,62,128,.50); margin:0 auto 18px; }
        .empty-title { font-family:'Syne',sans-serif; font-weight:700; font-size:.92rem; margin-bottom:6px; color:var(--text); }

        ::-webkit-scrollbar { width:5px; }
        ::-webkit-scrollbar-track { background:rgba(255,255,255,.03); }
        ::-webkit-scrollbar-thumb { background:linear-gradient(var(--pink),var(--yellow)); border-radius:3px; }

        @media(max-width:768px) { .main-wrapper { margin-left:70px; padding:16px 14px; } .page-header { padding:28px 24px; } }
    </style>
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main-wrapper">

    <!-- TOPBAR -->
    <div class="topbar mb-4">
        <div class="topbar-title">
            <span class="dot-live"></span>
            Laporan & Ekspor
        </div>
        <div class="topbar-user">
            <span><?= htmlspecialchars($_SESSION['nama']); ?></span>
            <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?></div>
        </div>
    </div>

    <!-- PAGE HEADER -->
    <div class="page-header mb-4">
        <div class="page-grid"></div>
        <div class="page-orb"></div>
        <div class="row align-items-center gy-3">
            <div class="col-lg-8" style="position:relative;z-index:1;">
                <div class="page-label">
                    <span class="dot-live" style="width:6px;height:6px;"></span>
                    Rekap Data Bisnis
                </div>
                <div class="page-h">Laporan <span>Booking</span></div>
                <p class="page-sub">Pantau pendapatan dan rekap transaksi. Filter berdasarkan periode dan ekspor ke Excel atau PDF.</p>
            </div>
            <div class="col-lg-4 text-lg-end" style="position:relative;z-index:1;">
                <a href="dashboard-admin.php" class="btn-px-outline">
                    <i class="fas fa-arrow-left fa-sm"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="section-title">Filter Periode</div>
    <div class="glass-card mb-4">
        <div class="card-heading">
            <div class="ch-icon"><i class="fas fa-filter"></i></div>
            Pilih Periode Laporan
        </div>
        <div class="divider-line"></div>

        <!-- Tab Filter -->
        <div class="filter-tabs">
            <?php
            $tabs = ['hari'=>'Hari Ini','minggu'=>'Minggu Ini','bulan'=>'Bulan Ini','tahun'=>'Tahun Ini','custom'=>'Kustom'];
            foreach ($tabs as $k => $v):
            ?>
            <a href="?filter=<?= $k; ?><?= $k==='custom' ? '&start_date='.$start_date.'&end_date='.$end_date : ''; ?>"
               class="filter-tab <?= $filter===$k ? 'active' : ''; ?>">
                <?= $v; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Custom Date Range -->
        <?php if ($filter === 'custom'): ?>
        <form method="GET" class="row g-3 align-items-end mt-1">
            <input type="hidden" name="filter" value="custom">
            <div class="col-md-4">
                <label class="form-label-px">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control-px" value="<?= htmlspecialchars($start_date); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label-px">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control-px" value="<?= htmlspecialchars($end_date); ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn-px w-100">
                    <i class="fas fa-search fa-sm"></i> Tampilkan
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <!-- STAT SUMMARY -->
    <div class="section-title">Ringkasan Periode</div>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-label">Total Booking</div>
                <div class="stat-num"><?= $total_booking; ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-label">Pendapatan</div>
                <div class="stat-val mt-1">Rp <?= number_format($total_pendapatan); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card yellow">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-label">Total Durasi</div>
                <div class="stat-num"><?= $total_durasi; ?><span style="font-size:1rem;font-family:'Syne',sans-serif;font-weight:700;-webkit-text-fill-color:var(--yellow);margin-left:4px;">jam</span></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock-rotate-left"></i></div>
                <div class="stat-label">Pending</div>
                <div class="stat-num"><?= $status_count['pending']; ?></div>
            </div>
        </div>
    </div>

    <!-- TABLE + EXPORT -->
    <div class="section-title">Data Booking</div>
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
            <div class="card-heading mb-0">
                <div class="ch-icon"><i class="fas fa-table"></i></div>
                <div>
                    Booking — <span style="color:var(--yellow);font-weight:800;"><?= $period_label; ?></span>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="export-excel.php?<?= $export_params; ?>" class="btn-px-green">
                    <i class="fas fa-file-excel fa-sm"></i> Export Excel
                </a>
                <a href="export-pdf.php?<?= $export_params; ?>" class="btn-px-red">
                    <i class="fas fa-file-pdf fa-sm"></i> Export PDF
                </a>
            </div>
        </div>
        <div class="divider-line"></div>

        <?php if (empty($rows)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
            <div class="empty-title">Tidak Ada Data</div>
            <p style="font-size:.83rem;">Tidak ada booking pada periode yang dipilih.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="px-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jam Mulai</th>
                        <th>Pelanggan</th>
                        <th>PlayStation</th>
                        <th>Durasi</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; foreach ($rows as $row):
                    $badgeClass = match($row['status']) {
                        'pending'   => 'badge-pending',
                        'confirmed' => 'badge-confirmed',
                        'selesai'   => 'badge-selesai',
                        'batal'     => 'badge-batal',
                        default     => ''
                    };
                ?>
                <tr>
                    <td><span class="no-badge"><?= $no++; ?></span></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td><?= htmlspecialchars($row['jam_mulai']); ?></td>
                    <td><strong><?= htmlspecialchars($row['user_nama']); ?></strong></td>
                    <td><?= htmlspecialchars($row['nama_ps']); ?></td>
                    <td><strong><?= $row['durasi']; ?></strong> <span style="font-size:.78rem;color:rgba(220,215,255,.35);">jam</span></td>
                    <td><strong>Rp <?= number_format($row['total_harga']); ?></strong></td>
                    <td><span class="badge-px <?= $badgeClass; ?>"><?= ucfirst($row['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="color:var(--yellow);letter-spacing:.10em;text-transform:uppercase;">
                            <i class="fas fa-calculator fa-sm me-2"></i>Total Keseluruhan
                        </td>
                        <td style="color:var(--text);"><strong><?= $total_durasi; ?> jam</strong></td>
                        <td style="color:var(--yellow);font-size:.95rem;">
                            <strong>Rp <?= number_format($total_pendapatan); ?></strong>
                        </td>
                        <td>
                            <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:.72rem;color:var(--muted);">
                                <?= $total_booking; ?> transaksi
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>