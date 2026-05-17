<?php
include "../config/session.php";
include "../config/koneksi.php";

if (!isAdmin()) {
    header("Location: ../user/dashboard-user.php");
    exit;
}

if (isset($_POST['tambah'])) {
    $nama_ps = mysqli_real_escape_string($conn, $_POST['nama_ps']);
    $harga   = mysqli_real_escape_string($conn, $_POST['harga_per_jam']);
    mysqli_query($conn, "INSERT INTO playstations (nama_ps, harga_per_jam, status) VALUES ('$nama_ps', '$harga', 'tersedia')");
    echo "<script>alert('PS berhasil ditambahkan!');</script>";
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM playstations WHERE id='$id'");
    echo "<script>alert('PS berhasil dihapus!'); window.location='ps.php';</script>";
}

if (isset($_POST['edit'])) {
    $id      = (int)$_POST['edit_id'];
    $nama_ps = mysqli_real_escape_string($conn, $_POST['nama_ps']);
    $harga   = mysqli_real_escape_string($conn, $_POST['harga_per_jam']);
    $status  = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE playstations SET nama_ps='$nama_ps', harga_per_jam='$harga', status='$status' WHERE id='$id'");
    echo "<script>alert('PS berhasil diperbarui!'); window.location='ps.php';</script>";
}

$data       = mysqli_query($conn, "SELECT * FROM playstations ORDER BY id DESC");
$total_ps   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM playstations"));
$tersedia   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM playstations WHERE status='tersedia'"));
$terpakai   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM playstations WHERE status='terpakai'"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola PlayStation — PixelStation</title>

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
            --dark3:       #1C1540;

            --card-bg:     rgba(255,255,255,.08);
            --card-hover:  rgba(255,255,255,.12);
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

        /* ─── PAGE HEADER ─── */
        .page-header {
            position:relative; overflow:hidden;
            background:linear-gradient(135deg,
                rgba(255,255,255,.11) 0%,
                rgba(255,255,255,.06) 100%);
            border:1px solid rgba(255,255,255,.18);
            border-radius:22px;
            padding:36px 44px;
            margin-bottom:26px;
            backdrop-filter:blur(20px);
        }
        .page-header::before {
            content:''; position:absolute; inset:0; pointer-events:none;
            background:
                radial-gradient(ellipse 65% 140% at 95% 50%, rgba(255,62,128,.18), transparent 65%),
                radial-gradient(ellipse 50% 80% at 5%  80%, rgba(255,209,102,.10), transparent 60%);
        }
        .page-grid {
            position:absolute; inset:0; pointer-events:none;
            background-image:
                linear-gradient(rgba(255,62,128,.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,62,128,.07) 1px, transparent 1px);
            background-size:44px 44px;
            mask-image:radial-gradient(ellipse 80% 100% at 80% 50%, black, transparent);
        }
        .page-orb {
            position:absolute; right:-40px; top:50%; transform:translateY(-50%);
            width:220px; height:220px; border-radius:50%;
            background:radial-gradient(circle, rgba(255,62,128,.20) 0%, transparent 70%);
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
            font-size:clamp(2rem,4vw,2.8rem); letter-spacing:.06em; line-height:1;
            margin-bottom:8px;
        }
        .page-h span {
            background:linear-gradient(135deg,var(--pink) 0%,var(--yellow) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .page-sub {
            color:rgba(220,215,255,.65); font-size:.88rem; line-height:1.7;
        }
        .page-header-actions {
            position:relative; z-index:1;
        }

        /* ─── STAT CARDS ─── */
        .stat-card {
            background:rgba(255,255,255,.08);
            border:1px solid var(--border);
            border-radius:20px; padding:24px 20px;
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
            transform:translateY(-5px);
            border-color:rgba(255,62,128,.40);
            box-shadow:0 20px 50px rgba(0,0,0,.30), 0 0 30px var(--pink-glow);
        }
        .stat-label {
            font-size:.70rem; letter-spacing:.14em; text-transform:uppercase;
            color:var(--muted); font-family:'Syne',sans-serif; font-weight:700;
            margin-bottom:10px;
        }
        .stat-num {
            font-family:'Bebas Neue',sans-serif;
            font-size:2.8rem; letter-spacing:.04em; line-height:1;
            background:linear-gradient(135deg,var(--yellow-soft),var(--pink));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .stat-icon {
            position:absolute; top:20px; right:18px;
            width:44px; height:44px; border-radius:12px;
            background:rgba(255,62,128,.14);
            border:1px solid rgba(255,62,128,.28);
            display:flex; align-items:center; justify-content:center;
            font-size:1rem; color:var(--pink-soft);
        }
        .stat-card.success .stat-num {
            background:linear-gradient(135deg,#67EEA9,#00C97A);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .stat-card.success .stat-icon {
            background:rgba(96,211,148,.14); border-color:rgba(96,211,148,.30); color:#60D394;
        }
        .stat-card.danger .stat-num {
            background:linear-gradient(135deg,var(--pink),#C41A5C);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .stat-card.danger .stat-icon {
            background:rgba(255,62,128,.14); border-color:rgba(255,62,128,.30); color:var(--pink-soft);
        }

        /* ─── GLASS CARD ─── */
        .glass-card {
            background:rgba(255,255,255,.08);
            border:1px solid var(--border);
            border-radius:22px; padding:28px 30px;
            backdrop-filter:blur(20px);
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
            border-radius:2px; margin-bottom:22px;
        }

        /* ─── FORM ─── */
        .form-label-px {
            font-family:'Syne',sans-serif; font-weight:700;
            font-size:.72rem; letter-spacing:.12em; text-transform:uppercase;
            color:var(--muted); margin-bottom:8px; display:block;
        }
        .form-control-px {
            width:100%;
            background:rgba(255,255,255,.07);
            border:1px solid rgba(255,255,255,.14);
            border-radius:12px;
            padding:12px 16px;
            color:var(--text);
            font-family:'DM Sans',sans-serif; font-size:.9rem;
            outline:none;
            transition:border-color .25s, box-shadow .25s, background .25s;
        }
        .form-control-px::placeholder { color:rgba(220,215,255,.35); }
        .form-control-px:focus {
            border-color:rgba(255,62,128,.50);
            background:rgba(255,255,255,.10);
            box-shadow:0 0 0 3px rgba(255,62,128,.12);
        }
        select.form-control-px option {
            background:#160F30; color:var(--text);
        }

        /* ─── BUTTONS ─── */
        .btn-px {
            font-family:'Syne',sans-serif; font-weight:700;
            background:linear-gradient(135deg,var(--pink) 0%,#C41A5C 100%);
            color:#fff; border:none;
            padding:11px 24px; border-radius:12px;
            letter-spacing:.05em; font-size:.82rem;
            text-decoration:none; display:inline-flex; align-items:center; gap:8px;
            transition:transform .2s, box-shadow .2s;
            box-shadow:0 4px 20px var(--pink-glow);
            cursor:pointer;
        }
        .btn-px:hover {
            transform:translateY(-2px);
            box-shadow:0 10px 32px var(--pink-glow);
            color:#fff; text-decoration:none;
        }
        .btn-px-outline {
            font-family:'Syne',sans-serif; font-weight:700;
            background:transparent;
            color:var(--muted); border:1px solid rgba(255,255,255,.18);
            padding:11px 24px; border-radius:12px;
            letter-spacing:.05em; font-size:.82rem;
            text-decoration:none; display:inline-flex; align-items:center; gap:8px;
            transition:.25s; cursor:pointer;
        }
        .btn-px-outline:hover {
            border-color:rgba(255,62,128,.45);
            color:var(--text); text-decoration:none;
            background:rgba(255,62,128,.08);
        }
        .btn-icon {
            width:34px; height:34px; border-radius:9px;
            display:inline-flex; align-items:center; justify-content:center;
            font-size:.85rem; border:none; cursor:pointer;
            transition:transform .2s, box-shadow .2s;
        }
        .btn-edit {
            background:rgba(255,209,102,.16);
            border:1px solid rgba(255,209,102,.30);
            color:var(--yellow);
        }
        .btn-edit:hover {
            background:rgba(255,209,102,.28);
            transform:translateY(-2px);
            box-shadow:0 6px 16px var(--yellow-glow);
        }
        .btn-delete {
            background:rgba(255,62,128,.14);
            border:1px solid rgba(255,62,128,.28);
            color:var(--pink);
        }
        .btn-delete:hover {
            background:rgba(255,62,128,.26);
            transform:translateY(-2px);
            box-shadow:0 6px 16px var(--pink-glow);
        }

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
        .badge-tersedia { background:rgba(96,211,148,.14); border:1px solid rgba(96,211,148,.35); color:#60D394; }
        .badge-terpakai { background:rgba(255,62,128,.14); border:1px solid rgba(255,62,128,.35); color:var(--pink); }

        /* ─── PS ID TAG ─── */
        .ps-id-tag {
            display:inline-flex; align-items:center; justify-content:center;
            width:32px; height:32px; border-radius:9px;
            background:rgba(255,62,128,.14); border:1px solid rgba(255,62,128,.28);
            font-family:'Bebas Neue',sans-serif; font-size:.9rem; color:var(--pink);
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

        /* ─── MODAL ─── */
        .modal-px .modal-content {
            background:#160F30;
            border:1px solid rgba(255,62,128,.30);
            border-radius:20px;
            backdrop-filter:blur(24px);
        }
        .modal-px .modal-header {
            border-bottom:1px solid rgba(255,255,255,.08);
            padding:20px 24px 16px;
        }
        .modal-px .modal-title {
            font-family:'Syne',sans-serif; font-weight:800;
            font-size:.95rem; letter-spacing:.07em;
            display:flex; align-items:center; gap:10px;
        }
        .modal-px .modal-body  { padding:24px; }
        .modal-px .modal-footer {
            border-top:1px solid rgba(255,255,255,.08);
            padding:16px 24px;
        }
        .btn-close-px {
            width:30px; height:30px; border-radius:8px;
            background:rgba(255,62,128,.14); border:1px solid rgba(255,62,128,.28);
            color:var(--pink); display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:.2s;
        }
        .btn-close-px:hover { background:rgba(255,62,128,.28); }

        /* ─── EMPTY STATE ─── */
        .empty-state {
            text-align:center; padding:56px 24px;
            color:var(--muted);
        }
        .empty-icon {
            width:72px; height:72px; border-radius:20px;
            background:rgba(255,62,128,.10); border:1px solid rgba(255,62,128,.20);
            display:flex; align-items:center; justify-content:center;
            font-size:1.8rem; color:rgba(255,62,128,.50);
            margin:0 auto 18px;
        }
        .empty-title {
            font-family:'Syne',sans-serif; font-weight:700;
            font-size:.92rem; margin-bottom:6px; color:var(--text);
        }
        .empty-sub { font-size:.83rem; }

        /* ─── SCROLLBAR ─── */
        ::-webkit-scrollbar { width:5px; }
        ::-webkit-scrollbar-track { background:rgba(255,255,255,.03); }
        ::-webkit-scrollbar-thumb { background:linear-gradient(var(--pink),var(--yellow)); border-radius:3px; }

        /* ─── RESPONSIVE ─── */
        @media(max-width:768px) {
            .main-wrapper { margin-left:70px; padding:16px 14px; }
            .page-header  { padding:28px 24px; }
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
            Kelola PlayStation
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
                    Manajemen Unit
                </div>
                <div class="page-h">
                    <span>PlayStation</span> Center
                </div>
                <p class="page-sub">Tambah, edit, dan kelola semua unit PlayStation yang tersedia di PixelStation.</p>
            </div>
            <div class="col-lg-4 text-lg-end page-header-actions">
                <button class="btn-px" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fas fa-plus fa-sm"></i> Tambah PS Baru
                </button>
                <a href="dashboard-admin.php" class="btn-px-outline ms-2">
                    <i class="fas fa-arrow-left fa-sm"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="section-title">Ringkasan Unit</div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-gamepad"></i></div>
                <div class="stat-label">Total Unit</div>
                <div class="stat-num"><?= $total_ps['total']; ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card success">
                <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
                <div class="stat-label">Tersedia</div>
                <div class="stat-num"><?= $tersedia['total']; ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card danger">
                <div class="stat-icon"><i class="fas fa-circle-xmark"></i></div>
                <div class="stat-label">Sedang Terpakai</div>
                <div class="stat-num"><?= $terpakai['total']; ?></div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="section-title">Daftar Unit PlayStation</div>
    <div class="glass-card">
        <div class="card-heading">
            <div class="ch-icon"><i class="fas fa-list"></i></div>
            Semua Unit PlayStation
        </div>
        <div class="divider-line"></div>

        <?php
        // Reset pointer hasil query
        mysqli_data_seek($data, 0);
        $count = mysqli_num_rows($data);
        ?>

        <?php if ($count === 0): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-gamepad"></i></div>
            <div class="empty-title">Belum Ada Unit PlayStation</div>
            <p class="empty-sub">Klik "Tambah PS Baru" untuk menambahkan unit pertama.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="px-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Unit</th>
                        <th>Harga / Jam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($data)): ?>
                <tr>
                    <td><span class="ps-id-tag"><?= $row['id']; ?></span></td>
                    <td><strong><?= htmlspecialchars($row['nama_ps']); ?></strong></td>
                    <td><strong>Rp <?= number_format($row['harga_per_jam']); ?> <span style="font-weight:400;color:var(--muted);font-size:.78rem;">/ jam</span></strong></td>
                    <td>
                        <?php
                        $badgeClass = $row['status'] === 'tersedia' ? 'badge-tersedia' : 'badge-terpakai';
                        $statusLabel = ucfirst($row['status']);
                        ?>
                        <span class="badge-px <?= $badgeClass; ?>"><?= $statusLabel; ?></span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <!-- Tombol Edit -->
                            <button class="btn-icon btn-edit"
                                title="Edit"
                                onclick="openEdit(
                                    <?= $row['id']; ?>,
                                    '<?= htmlspecialchars($row['nama_ps'], ENT_QUOTES); ?>',
                                    <?= $row['harga_per_jam']; ?>,
                                    '<?= $row['status']; ?>'
                                )">
                                <i class="fas fa-pen fa-xs"></i>
                            </button>
                            <!-- Tombol Hapus -->
                            <a href="ps.php?hapus=<?= $row['id']; ?>"
                                class="btn-icon btn-delete"
                                title="Hapus"
                                onclick="return confirm('Yakin ingin menghapus unit ini?')">
                                <i class="fas fa-trash-can fa-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /main-wrapper -->

<!-- ══ MODAL TAMBAH ══ -->
<div class="modal fade modal-px" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <div class="ch-icon"><i class="fas fa-plus"></i></div>
                    Tambah PlayStation Baru
                </div>
                <button type="button" class="btn-close-px" data-bs-dismiss="modal">
                    <i class="fas fa-xmark fa-sm"></i>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label-px">Nama Unit PlayStation</label>
                        <input type="text" name="nama_ps" class="form-control-px"
                            placeholder="Contoh: PS5 — Unit A" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label-px">Harga per Jam (Rp)</label>
                        <input type="number" name="harga_per_jam" class="form-control-px"
                            placeholder="Contoh: 15000" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-px-outline" data-bs-dismiss="modal">
                        <i class="fas fa-xmark fa-sm"></i> Batal
                    </button>
                    <button type="submit" name="tambah" class="btn-px">
                        <i class="fas fa-plus fa-sm"></i> Tambah Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══ MODAL EDIT ══ -->
<div class="modal fade modal-px" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <div class="ch-icon" style="background:rgba(255,209,102,.16);border-color:rgba(255,209,102,.30);color:var(--yellow);">
                        <i class="fas fa-pen"></i>
                    </div>
                    Edit PlayStation
                </div>
                <button type="button" class="btn-close-px" data-bs-dismiss="modal">
                    <i class="fas fa-xmark fa-sm"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="edit_id" id="editId">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label-px">Nama Unit PlayStation</label>
                        <input type="text" name="nama_ps" id="editNama" class="form-control-px" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label-px">Harga per Jam (Rp)</label>
                        <input type="number" name="harga_per_jam" id="editHarga" class="form-control-px" min="0" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label-px">Status Unit</label>
                        <select name="status" id="editStatus" class="form-control-px">
                            <option value="tersedia">Tersedia</option>
                            <option value="terpakai">Terpakai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-px-outline" data-bs-dismiss="modal">
                        <i class="fas fa-xmark fa-sm"></i> Batal
                    </button>
                    <button type="submit" name="edit" class="btn-px" style="background:linear-gradient(135deg,var(--yellow),#D9960A);">
                        <i class="fas fa-floppy-disk fa-sm"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEdit(id, nama, harga, status) {
    document.getElementById('editId').value    = id;
    document.getElementById('editNama').value  = nama;
    document.getElementById('editHarga').value = harga;
    document.getElementById('editStatus').value = status;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>

</body>
</html>