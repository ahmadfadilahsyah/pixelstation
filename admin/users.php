<?php
include "../config/session.php";
include "../config/koneksi.php";

if (!isAdmin()) {
    header("Location: ../auth/login.php");
    exit;
}

// Proses hapus user
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if ($id != $_SESSION['id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    }
    header("Location: users.php");
    exit;
}

// Proses update role
if (isset($_POST['update_role'])) {
    $id   = $_POST['id'];
    $role = $_POST['role'];
    if ($id != $_SESSION['id'] || $role != 'admin') {
        mysqli_query($conn, "UPDATE users SET role = '$role' WHERE id = $id");
    }
    header("Location: users.php");
    exit;
}

// Statistik
$total_users  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"));
$total_admins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='admin'"));
$total_all    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"));

// Ambil semua data user
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Pengguna — PixelStation Admin</title>

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
            --card-bg:     rgba(255,255,255,.08);
            --border:      rgba(255,255,255,.14);
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

        body::before {
            content:''; position:fixed; inset:0; pointer-events:none; z-index:0;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            opacity:.25;
        }

        /* ─── LAYOUT ─── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            position:relative; z-index:1;
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
            letter-spacing:.06em; line-height:1; margin-bottom:6px;
        }
        .page-title span {
            background: linear-gradient(135deg, var(--pink) 0%, var(--yellow) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .page-sub { color:var(--muted); font-size:.88rem; }

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
            color:var(--muted); font-family:'Syne', sans-serif; font-weight:700;
            margin-bottom:12px;
        }
        .stat-num {
            font-family:'Bebas Neue', sans-serif;
            font-size:3rem; letter-spacing:.04em; line-height:1;
        }
        .stat-num.default { background: linear-gradient(135deg, var(--yellow-soft), var(--pink)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .stat-num.pink    { background: linear-gradient(135deg, var(--pink), #C41A5C); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .stat-num.green   { background: linear-gradient(135deg, #67EEA9, #00C97A); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }

        .stat-icon {
            position:absolute; top:22px; right:20px;
            width:46px; height:46px; border-radius:13px;
            background: rgba(255,62,128,.14);
            border: 1px solid rgba(255,62,128,.28);
            display:flex; align-items:center; justify-content:center;
            font-size:1.1rem; color:var(--pink-soft);
        }
        .stat-icon.yellow { background:rgba(255,209,102,.14); border-color:rgba(255,209,102,.30); color:var(--yellow); }
        .stat-icon.green  { background:rgba(96,211,148,.14);  border-color:rgba(96,211,148,.30);  color:#60D394; }

        /* ─── GLASS CARD ─── */
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
            margin-bottom:20px;
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

        /* ─── USER AVATAR ─── */
        .user-avatar {
            width:34px; height:34px; border-radius:10px;
            background: linear-gradient(135deg, rgba(255,62,128,.30), rgba(255,209,102,.20));
            border: 1px solid rgba(255,62,128,.30);
            display:inline-flex; align-items:center; justify-content:center;
            font-family:'Bebas Neue', sans-serif; font-size:.95rem;
            color: var(--pink-soft); flex-shrink:0;
            margin-right:10px;
        }
        .user-cell { display:flex; align-items:center; }

        /* ─── ROLE BADGES ─── */
        .role-badge {
            padding: 4px 13px; border-radius:20px;
            font-size:.68rem; font-family:'Syne', sans-serif; font-weight:700;
            letter-spacing:.09em; text-transform:uppercase;
        }
        .role-admin { background:rgba(255,62,128,.18); border:1px solid rgba(255,62,128,.35); color:var(--pink); }
        .role-user  { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.14); color:var(--muted); }

        /* ─── ROLE SELECT ─── */
        .role-select {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 9px;
            color: var(--text);
            font-family:'Syne', sans-serif; font-weight:600;
            font-size:.75rem; letter-spacing:.06em;
            padding: 6px 12px;
            appearance:none; -webkit-appearance:none;
            cursor:pointer;
            outline:none;
            transition: border-color .2s, box-shadow .2s;
        }
        .role-select:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px var(--pink-glow);
        }
        .role-select option { background:#160F30; }

        /* ─── DATE CELL ─── */
        .date-cell { font-size:.82rem; color:var(--muted); }

        /* ─── "ANDA" tag ─── */
        .self-tag {
            display:inline-flex; align-items:center; gap:6px;
            background: rgba(255,209,102,.10);
            border: 1px solid rgba(255,209,102,.25);
            color: var(--yellow-soft);
            font-family:'Syne', sans-serif; font-weight:700;
            font-size:.68rem; letter-spacing:.10em;
            padding: 4px 12px; border-radius:20px;
        }

        /* ─── ACTION BUTTONS ─── */
        .btn-action {
            width:32px; height:32px; border-radius:9px;
            display:inline-flex; align-items:center; justify-content:center;
            border:none; text-decoration:none;
            font-size:.8rem; transition:.2s;
        }
        .btn-action:hover { transform:translateY(-2px); text-decoration:none; }
        .btn-delete { background:rgba(255,62,128,.18); border:1px solid rgba(255,62,128,.30); color:var(--pink); }
        .btn-delete:hover { background:var(--pink); color:#fff; box-shadow:0 6px 20px var(--pink-glow); }

        /* ─── SCROLLBAR ─── */
        ::-webkit-scrollbar { width:5px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,.03); }
        ::-webkit-scrollbar-thumb { background: linear-gradient(var(--pink), var(--yellow)); border-radius:3px; }

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
            Manajemen Pengguna
        </div>
        <div class="topbar-user">
            <span><?= htmlspecialchars($_SESSION['nama']); ?></span>
            <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?></div>
        </div>
    </div>

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <div class="page-eyebrow">
            <span class="dot-live" style="width:6px;height:6px;"></span>
            Panel Admin
        </div>
        <div class="page-title">Kelola <span>Pengguna</span></div>
        <p class="page-sub">Manajemen akun member dan admin PixelStation.</p>
    </div>

    <!-- STAT CARDS -->
    <div class="section-title">Ringkasan Akun</div>
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-label">Total Akun</div>
                <div class="stat-num default"><?= $total_all['total']; ?></div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-user"></i></div>
                <div class="stat-label">Member</div>
                <div class="stat-num green"><?= $total_users['total']; ?></div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
                <div class="stat-label">Admin</div>
                <div class="stat-num pink"><?= $total_admins['total']; ?></div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="section-title">Daftar Pengguna</div>
    <div class="glass-card">
        <div class="card-heading">
            <div class="ch-icon"><i class="fas fa-users-cog"></i></div>
            Semua Akun Terdaftar
        </div>
        <div class="divider-line"></div>

        <div class="table-responsive">
            <table class="px-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pengguna</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><strong style="color:var(--muted)">#<?= $row['id']; ?></strong></td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar"><?= strtoupper(substr($row['nama'], 0, 1)); ?></div>
                                <strong><?= htmlspecialchars($row['nama']); ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td>
                            <?php if ($row['id'] == $_SESSION['id']): ?>
                                <span class="role-badge role-admin"><?= ucfirst($row['role']); ?></span>
                            <?php else: ?>
                                <form method="POST" class="d-inline m-0 p-0">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <input type="hidden" name="update_role" value="1">
                                    <select name="role" class="role-select" onchange="this.form.submit()">
                                        <option value="user"  <?= $row['role']=='user'  ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?= $row['role']=='admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="date-cell">
                                <i class="far fa-calendar me-1"></i>
                                <?= date('d M Y', strtotime($row['created_at'])); ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($row['id'] != $_SESSION['id']): ?>
                                <a href="users.php?hapus=<?= $row['id']; ?>" class="btn-action btn-delete" title="Hapus User"
                                   onclick="return confirm('Yakin ingin menghapus user ini?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php else: ?>
                                <span class="self-tag"><i class="fas fa-star fa-xs"></i> Anda</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /main-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>