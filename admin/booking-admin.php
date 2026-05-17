<?php
include "../config/session.php";
include "../config/koneksi.php";

if (!isAdmin()) {
    header("Location: ../user/dashboard-user.php");
    exit;
}

// Update status booking
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id     = $_GET['id'];
    $status = $_GET['status'];
    $allowed = ['pending', 'confirmed', 'selesai', 'batal'];
    if (in_array($status, $allowed)) {
        mysqli_query($conn, "UPDATE bookings SET status='$status' WHERE id='$id'");
        echo "<script>alert('Status booking berhasil diupdate!'); window.location='booking-admin.php';</script>";
    }
}

// Hapus booking
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM bookings WHERE id='$id'");
    echo "<script>alert('Booking berhasil dihapus!'); window.location='booking-admin.php';</script>";
}

// Statistik
$total     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings"));
$pending   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='pending'"));
$confirmed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='confirmed'"));
$selesai   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='selesai'"));
$batal     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='batal'"));

// Data booking
$data = mysqli_query($conn, "
    SELECT bookings.*, users.nama as user_nama, playstations.nama_ps 
    FROM bookings 
    JOIN users ON bookings.user_id = users.id 
    JOIN playstations ON bookings.ps_id = playstations.id 
    ORDER BY bookings.tanggal DESC, bookings.jam_mulai ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Booking — PixelStation Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ─── ROOT TOKENS ─── */
        :root {
            --pink:        #FF3E80;
            --pink-soft:   #FF6FA3;
            --pink-glow:   rgba(255,62,128,.40);
            --yellow:      #FFD166;
            --yellow-soft: #FFE299;
            --yellow-glow: rgba(255,209,102,.35);
            --dark:        #0E0B20;
            --dark2:       #160F30;
            --card-bg:     rgba(255,255,255,.08);
            --glass:       rgba(255,255,255,.10);
            --border:      rgba(255,255,255,.14);
            --border-glow: rgba(255,62,128,.35);
            --text:        #F5F2FF;
            --muted:       rgba(220,215,255,.58);
            --sidebar-w:   260px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; }

        body {
            font-family:'DM Sans', sans-serif;
            color: var(--text);
            overflow-x: hidden;
            min-height: 100vh;
            background:
                radial-gradient(ellipse 90% 70% at 10% -5%,  rgba(120,60,220,.30) 0%, transparent 55%),
                radial-gradient(ellipse 70% 60% at 90% 110%, rgba(255,62,128,.18) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 55% 50%,  rgba(60,30,130,.50) 0%, transparent 70%),
                #0E0B20;
            background-attachment: fixed;
        }

        /* Noise grain overlay */
        body::before {
            content:''; position:fixed; inset:0; pointer-events:none; z-index:0;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            opacity:.25;
        }

        /* ─── LAYOUT ─── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            position: relative; z-index:1;
            padding: 28px 32px 56px;
            transition: margin .3s;
        }

        /* ─── TOP BAR ─── */
        .topbar {
            display:flex; justify-content:space-between; align-items:center;
            padding: 14px 22px;
            background: rgba(255,255,255,.08);
            border: 1px solid var(--border);
            border-radius: 16px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            margin-bottom: 26px;
        }
        .topbar-title {
            font-family:'Syne', sans-serif; font-weight:800;
            font-size:.95rem; letter-spacing:.07em;
            display:flex; align-items:center; gap:10px;
        }
        .dot-live {
            width:8px; height:8px; border-radius:50%;
            background: var(--pink);
            box-shadow: 0 0 10px var(--pink-glow);
            animation: blink 1.6s ease-in-out infinite;
            flex-shrink:0;
        }
        @keyframes blink { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.25;transform:scale(.7)} }

        .topbar-user {
            display:flex; align-items:center; gap:10px;
            font-size:.85rem; color:var(--muted);
        }
        .topbar-avatar {
            width:36px; height:36px; border-radius:10px;
            background: linear-gradient(135deg, var(--pink), var(--yellow));
            display:flex; align-items:center; justify-content:center;
            font-family:'Bebas Neue', sans-serif; font-size:1rem; color:#fff;
            box-shadow: 0 4px 16px var(--pink-glow);
        }

        /* ─── PAGE HEADER ─── */
        .page-header {
            display:flex; justify-content:space-between; align-items:flex-start;
            margin-bottom: 24px;
        }
        .page-title-wrap {}
        .page-eyebrow {
            display:inline-flex; align-items:center; gap:8px;
            background: rgba(255,62,128,.15); border:1px solid rgba(255,62,128,.35);
            color: var(--pink-soft); font-size:.70rem; letter-spacing:.16em;
            padding: 5px 14px; border-radius:30px;
            font-family:'Syne', sans-serif; font-weight:700;
            text-transform:uppercase; margin-bottom:10px;
        }
        .page-title {
            font-family:'Bebas Neue', sans-serif;
            font-size: clamp(2rem, 4vw, 2.8rem);
            letter-spacing:.06em; line-height:1;
            margin-bottom: 6px;
        }
        .page-title span {
            background: linear-gradient(135deg, var(--pink) 0%, var(--yellow) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .page-sub {
            color: var(--muted); font-size:.88rem;
        }

        /* ─── SECTION TITLE ─── */
        .section-title {
            font-family:'Bebas Neue', sans-serif;
            font-size:1rem; letter-spacing:.18em; text-transform:uppercase;
            color: rgba(220,215,255,.40);
            margin-bottom:14px;
            display:flex; align-items:center; gap:10px;
        }
        .section-title::after {
            content:''; flex:1; height:1px;
            background: linear-gradient(90deg, rgba(255,255,255,.08), transparent);
        }

        /* ─── STAT CARDS ─── */
        .stat-card {
            background: rgba(255,255,255,.08);
            border: 1px solid var(--border);
            border-radius: 20px; padding: 26px 22px;
            position:relative; overflow:hidden;
            transition: transform .3s ease, border-color .3s, box-shadow .3s;
            backdrop-filter: blur(16px);
        }
        .stat-card::after {
            content:''; position:absolute;
            top:0; left:0; right:0; height:1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
        }
        .stat-card:hover {
            transform: translateY(-6px);
            border-color: rgba(255,62,128,.40);
            box-shadow: 0 24px 60px rgba(0,0,0,.35), 0 0 40px var(--pink-glow);
        }
        .stat-label {
            font-size:.70rem; letter-spacing:.14em; text-transform:uppercase;
            color: var(--muted); font-family:'Syne', sans-serif; font-weight:700;
            margin-bottom: 12px;
        }
        .stat-num {
            font-family:'Bebas Neue', sans-serif;
            font-size: 3rem; letter-spacing:.04em; line-height:1;
        }
        .stat-num.default  { background: linear-gradient(135deg, var(--yellow-soft), var(--pink)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .stat-num.pending  { background: linear-gradient(135deg, var(--yellow), #F0A500); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .stat-num.green    { background: linear-gradient(135deg, #67EEA9, #00C97A); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .stat-num.danger   { background: linear-gradient(135deg, var(--pink), #C41A5C); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }

        .stat-icon {
            position:absolute; top:22px; right:20px;
            width:46px; height:46px; border-radius:13px;
            background: rgba(255,62,128,.14);
            border: 1px solid rgba(255,62,128,.28);
            display:flex; align-items:center; justify-content:center;
            font-size:1.1rem; color:var(--pink-soft);
        }
        .stat-icon.yellow { background: rgba(255,209,102,.14); border-color: rgba(255,209,102,.30); color:var(--yellow); }
        .stat-icon.green  { background: rgba(96,211,148,.14);  border-color: rgba(96,211,148,.30);  color:#60D394; }
        .stat-icon.red    { background: rgba(255,62,128,.14);  border-color: rgba(255,62,128,.30);  color:var(--pink); }

        /* ─── GLASS CARD (panel utama) ─── */
        .glass-card {
            background: rgba(255,255,255,.08);
            border: 1px solid var(--border);
            border-radius: 22px; padding: 28px 30px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            position:relative; overflow:hidden;
        }
        .glass-card::after {
            content:''; position:absolute;
            top:0; left:0; right:0; height:1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.28), transparent);
            pointer-events:none;
        }

        .card-heading {
            font-family:'Syne', sans-serif; font-weight:700;
            font-size:.88rem; letter-spacing:.07em; text-transform:uppercase;
            display:flex; align-items:center; gap:12px;
            margin-bottom: 20px;
        }
        .ch-icon {
            width:36px; height:36px; border-radius:10px;
            background: rgba(255,62,128,.16);
            border: 1px solid rgba(255,62,128,.28);
            display:flex; align-items:center; justify-content:center;
            font-size:.95rem; color:var(--pink);
            flex-shrink:0;
        }
        .divider-line {
            width:44px; height:2px;
            background: linear-gradient(90deg, var(--pink), var(--yellow));
            border-radius:2px; margin-bottom:20px;
        }

        /* ─── TABLE ─── */
        .px-table { width:100%; border-collapse:collapse; }
        .px-table thead tr { border-bottom:1px solid rgba(255,255,255,.10); }
        .px-table thead th {
            padding: 10px 14px;
            font-family:'Syne', sans-serif; font-weight:700;
            font-size:.68rem; letter-spacing:.14em; text-transform:uppercase;
            color: rgba(220,215,255,.50); text-align:left;
        }
        .px-table tbody tr {
            border-bottom: 1px solid rgba(255,255,255,.05);
            transition: background .2s;
        }
        .px-table tbody tr:hover { background: rgba(255,62,128,.05); }
        .px-table tbody td {
            padding: 14px 14px; font-size:.87rem; color:var(--muted);
            vertical-align: middle;
        }
        .px-table tbody td strong { color:var(--text); font-weight:500; }

        /* ─── STATUS BADGES ─── */
        .badge-px {
            padding: 4px 13px; border-radius:20px;
            font-size:.68rem; font-family:'Syne', sans-serif; font-weight:700;
            letter-spacing:.09em; text-transform:uppercase;
        }
        .badge-pending   { background:rgba(255,209,102,.16); border:1px solid rgba(255,209,102,.35); color:var(--yellow); }
        .badge-confirmed { background:rgba(96,211,148,.14);  border:1px solid rgba(96,211,148,.35);  color:#60D394; }
        .badge-selesai   { background:rgba(96,211,148,.14);  border:1px solid rgba(96,211,148,.35);  color:#60D394; }
        .badge-batal     { background:rgba(255,62,128,.14);  border:1px solid rgba(255,62,128,.35);  color:var(--pink); }

        /* ─── ACTION BUTTONS ─── */
        .btn-action {
            width:32px; height:32px; border-radius:9px;
            display:inline-flex; align-items:center; justify-content:center;
            border:none; text-decoration:none;
            font-size:.8rem; transition:.2s;
            margin-right:4px;
        }
        .btn-action:hover { transform:translateY(-2px); text-decoration:none; }
        .btn-confirm { background:rgba(96,211,148,.18);  border:1px solid rgba(96,211,148,.30);  color:#60D394; }
        .btn-confirm:hover { background:#60D394; color:#0E0B20; box-shadow:0 6px 20px rgba(96,211,148,.35); }
        .btn-done    { background:rgba(96,211,148,.18);  border:1px solid rgba(96,211,148,.30);  color:#60D394; }
        .btn-done:hover    { background:#60D394; color:#0E0B20; box-shadow:0 6px 20px rgba(96,211,148,.35); }
        .btn-cancel  { background:rgba(255,209,102,.18); border:1px solid rgba(255,209,102,.30); color:var(--yellow); }
        .btn-cancel:hover  { background:var(--yellow); color:#0E0B20; box-shadow:0 6px 20px var(--yellow-glow); }
        .btn-delete  { background:rgba(255,62,128,.18);  border:1px solid rgba(255,62,128,.30);  color:var(--pink); }
        .btn-delete:hover  { background:var(--pink); color:#fff; box-shadow:0 6px 20px var(--pink-glow); }

        /* ─── PS name color ─── */
        .ps-name-cell { color:var(--yellow-soft); }

        /* ─── Empty state ─── */
        .empty-state {
            text-align:center; padding:56px 0;
            color: var(--muted);
        }
        .empty-state i { font-size:2.5rem; margin-bottom:14px; opacity:.4; }
        .empty-state p { font-family:'Syne', sans-serif; font-size:.85rem; letter-spacing:.08em; }

        /* ─── SCROLLBAR ─── */
        ::-webkit-scrollbar { width:5px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,.03); }
        ::-webkit-scrollbar-thumb { background: linear-gradient(var(--pink), var(--yellow)); border-radius:3px; }

        /* ─── RESPONSIVE ─── */
        @media(max-width:768px) {
            .main-wrapper { margin-left:70px; padding:16px 14px; }
        }
    </style>
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main-wrapper">

    <!-- TOP BAR -->
    <div class="topbar mb-4">
        <div class="topbar-title">
            <span class="dot-live"></span>
            Manajemen Booking
        </div>
        <div class="topbar-user">
            <span><?= htmlspecialchars($_SESSION['nama']); ?></span>
            <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?></div>
        </div>
    </div>

    <!-- PAGE HEADER -->
    <div class="page-header mb-4">
        <div class="page-title-wrap">
            <div class="page-eyebrow">
                <span class="dot-live" style="width:6px;height:6px;"></span>
                Panel Admin
            </div>
            <div class="page-title">Kelola <span>Booking</span></div>
            <p class="page-sub">Kelola seluruh reservasi pelanggan PixelStation secara real-time.</p>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="section-title">Ringkasan Status</div>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-label">Total Booking</div>
                <div class="stat-num default"><?= $total['total']; ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-clock"></i></div>
                <div class="stat-label">Pending</div>
                <div class="stat-num pending"><?= $pending['total']; ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-label">Confirmed & Selesai</div>
                <div class="stat-num green"><?= ($confirmed['total'] + $selesai['total']); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-ban"></i></div>
                <div class="stat-label">Dibatalkan</div>
                <div class="stat-num danger"><?= $batal['total']; ?></div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="section-title">Data Reservasi</div>
    <div class="glass-card">
        <div class="card-heading">
            <div class="ch-icon"><i class="fas fa-calendar-check"></i></div>
            Semua Booking
        </div>
        <div class="divider-line"></div>

        <div class="table-responsive">
            <table class="px-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>PlayStation</th>
                        <th>Jadwal Main</th>
                        <th>Durasi</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($data) == 0): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-calendar-times d-block"></i>
                                <p>Belum ada data booking.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_array($data)):
                        $badgeClass = match($row['status']) {
                            'pending'   => 'badge-pending',
                            'confirmed' => 'badge-confirmed',
                            'selesai'   => 'badge-selesai',
                            'batal'     => 'badge-batal',
                            default     => ''
                        };
                    ?>
                    <tr>
                        <td><strong style="color:var(--muted)">#<?= $row['id']; ?></strong></td>
                        <td><strong><?= htmlspecialchars($row['user_nama']); ?></strong></td>
                        <td><span class="ps-name-cell"><?= htmlspecialchars($row['nama_ps']); ?></span></td>
                        <td>
                            <div><?= date('d M Y', strtotime($row['tanggal'])); ?></div>
                            <div style="font-size:.8rem; color:var(--muted)">
                                <i class="far fa-clock me-1"></i><?= date('H:i', strtotime($row['jam_mulai'])); ?> WIB
                            </div>
                        </td>
                        <td><?= $row['durasi']; ?> Jam</td>
                        <td><strong>Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></strong></td>
                        <td><span class="badge-px <?= $badgeClass; ?>"><?= ucfirst($row['status']); ?></span></td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="booking-admin.php?status=confirmed&id=<?= $row['id']; ?>" class="btn-action btn-confirm" title="Konfirmasi" onclick="return confirm('Konfirmasi booking ini?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="booking-admin.php?status=batal&id=<?= $row['id']; ?>" class="btn-action btn-cancel" title="Batalkan" onclick="return confirm('Batalkan booking ini?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php elseif ($row['status'] == 'confirmed'): ?>
                                    <a href="booking-admin.php?status=selesai&id=<?= $row['id']; ?>" class="btn-action btn-done" title="Tandai Selesai" onclick="return confirm('Tandai selesai?')">
                                        <i class="fas fa-flag-checkered"></i>
                                    </a>
                                    <a href="booking-admin.php?status=batal&id=<?= $row['id']; ?>" class="btn-action btn-cancel" title="Batalkan" onclick="return confirm('Batalkan booking ini?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="booking-admin.php?hapus=<?= $row['id']; ?>" class="btn-action btn-delete" title="Hapus Data" onclick="return confirm('Yakin hapus data booking ini secara permanen?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /main-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>