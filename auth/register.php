<?php
session_start();
include "../config/koneksi.php";

if (isset($_SESSION['id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard-admin.php");
    } else {
        header("Location: ../user/dashboard-user.php");
    }
    exit;
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email    = mysqli_real_escape_string($conn, strtolower(trim($_POST['email'])));
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // Hash password dengan bcrypt
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Email sudah terdaftar! Gunakan email lain.";
        } else {
            $insert = mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password_hashed', 'user')");
            if ($insert) {
                echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
                exit;
            } else {
                $error = "Registrasi gagal! Coba lagi. Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar — PixelStation</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --pink: #FF3E80;
      --pink-soft: #FF6FA3;
      --pink-glow: rgba(255,62,128,0.35);
      --yellow: #FFD166;
      --yellow-glow: rgba(255,209,102,0.3);
      --dark: #0A0A0F;
      --dark2: #111118;
      --dark3: #17171F;
      --card-bg: rgba(255,255,255,0.04);
      --glass: rgba(255,255,255,0.06);
      --border: rgba(255,255,255,0.08);
      --text: #F0EDF5;
      --muted: rgba(240,237,245,0.55);
    }

    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--dark);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: none;
    }

    /* ─── CURSOR ─── */
    .cursor-dot {
      position:fixed; width:10px; height:10px;
      background:var(--pink); border-radius:50%;
      pointer-events:none; z-index:99999;
      transform:translate(-50%,-50%);
      transition:transform .08s;
    }
    .cursor-ring {
      position:fixed; width:36px; height:36px;
      border:2px solid var(--pink-soft); border-radius:50%;
      pointer-events:none; z-index:99998;
      transform:translate(-50%,-50%);
      transition:all .18s ease;
      opacity:.7;
    }

    /* ─── BACKGROUND ─── */
    .bg-wrap {
      position:fixed; inset:0; z-index:0; overflow:hidden;
    }
    .bg-grid {
      position:absolute; inset:0;
      background-image:
        linear-gradient(rgba(255,62,128,.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,62,128,.05) 1px, transparent 1px);
      background-size:60px 60px;
    }
    .bg-glow-l {
      position:absolute; left:-200px; top:30%; 
      width:550px; height:550px;
      background:radial-gradient(circle, rgba(255,209,102,.12), transparent 70%);
    }
    .bg-glow-r {
      position:absolute; right:-200px; bottom:20%;
      width:550px; height:550px;
      background:radial-gradient(circle, rgba(255,62,128,.18), transparent 70%);
    }
    .bg-noise {
      position:absolute; inset:0;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
      opacity:.3; pointer-events:none;
    }

    /* ─── MAIN LAYOUT ─── */
    .page-wrap {
      position:relative; z-index:1;
      width:100%; min-height:100vh;
      display:flex; align-items:stretch;
    }

    /* ─── LEFT PANEL ─── */
    .left-panel {
      display:none;
      width:45%;
      flex-direction:column;
      justify-content:space-between;
      padding:48px;
      background:linear-gradient(160deg, rgba(255,209,102,.08) 0%, rgba(255,62,128,.08) 100%);
      border-right:1px solid var(--border);
      position:relative; overflow:hidden;
    }
    @media(min-width:992px){ .left-panel { display:flex; } }

    .left-panel::before {
      content:'';
      position:absolute; inset:0;
      background:url("https://images.unsplash.com/photo-1612287230202-1ff1d85d1bdf?auto=format&fit=crop&w=800&q=60") center/cover;
      opacity:.08;
    }

    .lp-brand { position:relative; z-index:1; }
    .lp-logo {
      font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:.1em;
      background:linear-gradient(135deg,var(--pink),var(--yellow));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
      text-decoration:none;
    }

    .lp-hero { position:relative; z-index:1; }
    .lp-heading {
      font-family:'Bebas Neue',sans-serif;
      font-size:clamp(2.8rem,5vw,4.5rem);
      letter-spacing:.04em; line-height:.92;
      margin-bottom:20px;
    }
    .lp-heading .g {
      background:linear-gradient(135deg,var(--yellow),var(--pink));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    }
    .lp-desc {
      color:var(--muted); font-size:.95rem; line-height:1.7; max-width:340px;
    }

    /* benefit list */
    .lp-perks {
      position:relative; z-index:1;
      display:flex; flex-direction:column; gap:12px;
    }
    .lp-perk {
      display:flex; align-items:center; gap:12px;
      padding:14px 16px; border-radius:12px;
      background:rgba(255,255,255,.04); border:1px solid var(--border);
      font-size:.875rem; color:var(--muted);
      transition:.25s;
    }
    .lp-perk:hover { border-color:rgba(255,62,128,.3); color:var(--text); }
    .lp-perk-icon { font-size:1.3rem; flex-shrink:0; }
    .lp-perk-title { font-family:'Syne',sans-serif; font-weight:600; font-size:.85rem; color:var(--text); }
    .lp-perk-sub { font-size:.75rem; color:var(--muted); }

    /* ─── RIGHT PANEL ─── */
    .right-panel {
      flex:1;
      display:flex; flex-direction:column;
      align-items:center; justify-content:center;
      padding:48px 24px;
      overflow-y:auto;
    }

    .form-card { width:100%; max-width:460px; }

    .form-eyebrow {
      display:inline-flex; align-items:center; gap:8px;
      background:rgba(255,209,102,.1); border:1px solid rgba(255,209,102,.3);
      color:#f5c842; font-size:.72rem; letter-spacing:.15em;
      padding:5px 14px; border-radius:30px;
      font-family:'Syne',sans-serif; font-weight:600;
      margin-bottom:20px;
    }
    .form-eyebrow .dot {
      width:5px; height:5px; background:var(--yellow); border-radius:50%;
      animation:blink 1.5s infinite;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }

    .form-title {
      font-family:'Bebas Neue',sans-serif;
      font-size:clamp(2.2rem,5vw,3rem);
      letter-spacing:.06em; line-height:.95;
      margin-bottom:8px;
    }
    .form-title span {
      background:linear-gradient(135deg,var(--yellow),var(--pink));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    }
    .form-subtitle {
      color:var(--muted); font-size:.88rem; margin-bottom:28px; line-height:1.6;
    }

    /* ─── INPUTS ─── */
    .field-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:18px; }
    .field-row.full { grid-template-columns:1fr; }
    .field-wrap { /* individual */ }
    .field-label {
      display:block; font-family:'Syne',sans-serif; font-weight:600;
      font-size:.72rem; letter-spacing:.1em; text-transform:uppercase;
      color:var(--muted); margin-bottom:8px;
    }
    .field-inner { position:relative; }
    .field-icon {
      position:absolute; left:16px; top:50%; transform:translateY(-50%);
      font-size:.95rem; pointer-events:none; z-index:1;
    }
    .field-input {
      width:100%;
      background:var(--glass);
      border:1.5px solid var(--border);
      border-radius:12px;
      padding:13px 16px 13px 46px;
      color:var(--text);
      font-family:'DM Sans',sans-serif;
      font-size:.9rem;
      outline:none;
      transition:border-color .25s, box-shadow .25s, background .25s;
    }
    .field-input::placeholder { color:var(--muted); }
    .field-input:focus {
      border-color:var(--pink);
      box-shadow:0 0 0 3px var(--pink-glow);
      background:rgba(255,62,128,.06);
    }
    .eye-btn {
      position:absolute; right:14px; top:50%; transform:translateY(-50%);
      background:none; border:none; color:var(--muted); cursor:none;
      font-size:1rem; padding:4px; transition:color .2s; line-height:1;
    }
    .eye-btn:hover { color:var(--pink); }

    /* strength meter */
    .pwd-strength { margin-top:8px; }
    .pwd-bar-wrap { display:flex; gap:4px; margin-bottom:4px; }
    .pwd-bar {
      flex:1; height:3px; border-radius:3px;
      background:var(--border); transition:background .3s;
    }
    .pwd-bar.weak  { background:#ff5252; }
    .pwd-bar.fair  { background:var(--yellow); }
    .pwd-bar.good  { background:#7BFF9A; }
    .pwd-label { font-size:.7rem; color:var(--muted); }

    /* terms checkbox */
    .check-wrap {
      display:flex; align-items:flex-start; gap:10px;
      margin-bottom:18px; cursor:none;
    }
    .check-input {
      width:18px; height:18px; border-radius:5px; flex-shrink:0;
      background:var(--glass); border:1.5px solid var(--border);
      appearance:none; cursor:none; transition:.2s; margin-top:1px;
      position:relative;
    }
    .check-input:checked {
      background:var(--pink); border-color:var(--pink);
    }
    .check-input:checked::after {
      content:'✓'; position:absolute; top:50%; left:50%;
      transform:translate(-50%,-50%);
      color:#fff; font-size:.7rem; font-weight:700;
    }
    .check-label { font-size:.82rem; color:var(--muted); line-height:1.5; }
    .check-label a { color:var(--pink-soft); text-decoration:none; font-weight:600; }
    .check-label a:hover { color:var(--pink); text-decoration:underline; }

    /* ─── BTN ─── */
    .btn-submit {
      width:100%;
      font-family:'Syne',sans-serif; font-weight:700;
      background:linear-gradient(135deg,var(--yellow),#f0a500);
      color:#111; border:none;
      padding:14px; border-radius:12px;
      letter-spacing:.06em; font-size:.95rem;
      position:relative; overflow:hidden;
      transition:transform .2s, box-shadow .2s;
      cursor:none;
    }
    .btn-submit::before {
      content:''; position:absolute; inset:0;
      background:linear-gradient(135deg,var(--pink),var(--yellow));
      opacity:0; transition:.3s;
    }
    .btn-submit:hover { transform:translateY(-2px); box-shadow:0 12px 35px var(--yellow-glow); }
    .btn-submit:hover::before { opacity:.1; }
    .btn-submit span { position:relative; z-index:1; display:flex; align-items:center; justify-content:center; gap:8px; }

    /* ─── DIVIDER ─── */
    .or-divider {
      display:flex; align-items:center; gap:14px;
      margin:22px 0;
      color:var(--muted); font-size:.78rem; letter-spacing:.08em; text-transform:uppercase;
    }
    .or-divider::before, .or-divider::after {
      content:''; flex:1; height:1px; background:var(--border);
    }

    /* ─── FOOTER LINKS ─── */
    .form-footer-link {
      text-align:center; color:var(--muted); font-size:.875rem;
    }
    .form-footer-link a {
      color:var(--pink-soft); font-weight:600; text-decoration:none; transition:color .2s;
    }
    .form-footer-link a:hover { color:var(--pink); text-decoration:underline; }

    /* ─── ALERT ─── */
    .alert-px {
      background:rgba(255,62,128,.1);
      border:1px solid rgba(255,62,128,.35);
      border-radius:12px; padding:12px 16px;
      font-size:.875rem; color:var(--pink-soft);
      display:flex; align-items:center; gap:10px;
      margin-bottom:20px;
      animation:slideIn .3s ease;
    }
    @keyframes slideIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }

    /* ─── SCROLLBAR ─── */
    ::-webkit-scrollbar { width:4px; }
    ::-webkit-scrollbar-track { background:var(--dark); }
    ::-webkit-scrollbar-thumb { background:linear-gradient(var(--pink),var(--yellow)); border-radius:3px; }

    /* ─── ENTRANCE ─── */
    .fade-up {
      opacity:0; transform:translateY(28px);
      animation:fadeUp .65s ease forwards;
    }
    .fade-up-d1 { animation-delay:.1s; }
    .fade-up-d2 { animation-delay:.2s; }
    .fade-up-d3 { animation-delay:.3s; }
    .fade-up-d4 { animation-delay:.4s; }
    .fade-up-d5 { animation-delay:.5s; }
    @keyframes fadeUp { to{opacity:1;transform:translateY(0)} }

    @media(max-width:480px){
      .right-panel { padding:32px 20px; }
      .field-row { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>

<!-- CURSOR -->
<div class="cursor-dot" id="cdot"></div>
<div class="cursor-ring" id="cring"></div>

<!-- BACKGROUND -->
<div class="bg-wrap">
  <div class="bg-grid"></div>
  <div class="bg-glow-l"></div>
  <div class="bg-glow-r"></div>
  <div class="bg-noise"></div>
</div>

<!-- PAGE -->
<div class="page-wrap">

  <!-- LEFT PANEL -->
  <div class="left-panel">
    <div class="lp-brand">
      <a href="../index.php" class="lp-logo">🎮 PixelStation</a>
    </div>

    <div class="lp-hero">
      <div class="lp-heading">
        GABUNG<br>
        <span class="g">MEMBER</span><br>
        ELITE.
      </div>
      <p class="lp-desc">
        Daftar gratis dan langsung dapat akses ke semua benefit eksklusif member PixelStation.
      </p>
    </div>

    <div class="lp-perks">
      <div class="lp-perk">
        <div class="lp-perk-icon">💎</div>
        <div>
          <div class="lp-perk-title">Diskon Booking 20%</div>
          <div class="lp-perk-sub">Berlaku setiap hari untuk semua paket</div>
        </div>
      </div>
      <div class="lp-perk">
        <div class="lp-perk-icon">⭐</div>
        <div>
          <div class="lp-perk-title">Bonus 1 Jam / 10x Main</div>
          <div class="lp-perk-sub">Poin otomatis terakumulasi tiap sesi</div>
        </div>
      </div>
      <div class="lp-perk">
        <div class="lp-perk-icon">🏆</div>
        <div>
          <div class="lp-perk-title">Turnamen Eksklusif</div>
          <div class="lp-perk-sub">Daftar gratis ke kompetisi bulanan</div>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">
    <div class="form-card">

      <div class="form-eyebrow fade-up">
        <span class="dot"></span> Gratis Selamanya
      </div>

      <h1 class="form-title fade-up fade-up-d1">
        BUAT<br><span>AKUNMU</span>
      </h1>
      <p class="form-subtitle fade-up fade-up-d1">
        Daftar dalam 30 detik dan langsung nikmati benefit member.
      </p>

      <?php if (!empty($error)): ?>
      <div class="alert-px fade-up">
        <span>⚠️</span> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="" autocomplete="off" id="regForm">

        <!-- NAMA -->
        <div class="field-row full fade-up fade-up-d2">
          <div class="field-wrap">
            <label class="field-label">Nama Lengkap</label>
            <div class="field-inner">
              <span class="field-icon">👤</span>
              <input type="text" name="nama" class="field-input"
                placeholder="Nama lengkap kamu" required
                value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
            </div>
          </div>
        </div>

        <!-- EMAIL -->
        <div class="field-row full fade-up fade-up-d2">
          <div class="field-wrap">
            <label class="field-label">Email</label>
            <div class="field-inner">
              <span class="field-icon">📧</span>
              <input type="email" name="email" class="field-input"
                placeholder="contoh@email.com" required
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
          </div>
        </div>

        <!-- PASSWORD + KONFIRMASI -->
        <div class="field-row fade-up fade-up-d3">
          <div class="field-wrap">
            <label class="field-label">Password</label>
            <div class="field-inner">
              <span class="field-icon">🔒</span>
              <input type="password" name="password" id="pwd1" class="field-input"
                placeholder="Min. 6 karakter" required
                oninput="checkStrength(this.value)"
                style="padding-right:46px;">
              <button type="button" class="eye-btn" onclick="togglePwd('pwd1','eye1')" id="eye1">👁️</button>
            </div>
            <!-- strength bar -->
            <div class="pwd-strength" id="strengthWrap" style="display:none;">
              <div class="pwd-bar-wrap">
                <div class="pwd-bar" id="bar1"></div>
                <div class="pwd-bar" id="bar2"></div>
                <div class="pwd-bar" id="bar3"></div>
              </div>
              <div class="pwd-label" id="strengthLabel">Lemah</div>
            </div>
          </div>
          <div class="field-wrap">
            <label class="field-label">Konfirmasi Password</label>
            <div class="field-inner">
              <span class="field-icon">🔏</span>
              <input type="password" name="confirm_password" id="pwd2" class="field-input"
                placeholder="Ulangi password" required
                oninput="checkMatch()"
                style="padding-right:46px;">
              <button type="button" class="eye-btn" onclick="togglePwd('pwd2','eye2')" id="eye2">👁️</button>
            </div>
            <div class="pwd-label" id="matchLabel" style="margin-top:8px;display:none;"></div>
          </div>
        </div>

        <!-- TERMS -->
        <div class="check-wrap fade-up fade-up-d3">
          <input type="checkbox" class="check-input" id="terms" required>
          <label for="terms" class="check-label">
            Saya menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a> PixelStation
          </label>
        </div>

        <!-- SUBMIT -->
        <button type="submit" name="register" class="btn-submit fade-up fade-up-d4">
          <span>🎮 Daftar Sekarang — Gratis!</span>
        </button>

      </form>

      <div class="or-divider fade-up fade-up-d4">sudah punya akun?</div>

      <div class="form-footer-link fade-up fade-up-d5">
        <a href="login.php">← Login ke PixelStation</a>
      </div>

    </div>
  </div>

</div><!-- /page-wrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // CURSOR
  const cdot = document.getElementById('cdot');
  const cring = document.getElementById('cring');
  document.addEventListener('mousemove', e => {
    cdot.style.left = e.clientX + 'px'; cdot.style.top = e.clientY + 'px';
    cring.style.left = e.clientX + 'px'; cring.style.top = e.clientY + 'px';
  });
  document.querySelectorAll('a,button,input').forEach(el => {
    el.addEventListener('mouseenter', () => { cring.style.transform='translate(-50%,-50%) scale(1.6)'; cring.style.opacity='1'; });
    el.addEventListener('mouseleave', () => { cring.style.transform='translate(-50%,-50%) scale(1)'; cring.style.opacity='.7'; });
  });

  // TOGGLE PASSWORD
  function togglePwd(fieldId, btnId) {
    const f = document.getElementById(fieldId);
    const b = document.getElementById(btnId);
    const v = f.type === 'password';
    f.type = v ? 'text' : 'password';
    b.textContent = v ? '🙈' : '👁️';
  }

  // PASSWORD STRENGTH
  function checkStrength(val) {
    const wrap = document.getElementById('strengthWrap');
    wrap.style.display = val.length ? 'block' : 'none';
    const b1 = document.getElementById('bar1');
    const b2 = document.getElementById('bar2');
    const b3 = document.getElementById('bar3');
    const lbl = document.getElementById('strengthLabel');
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
    const cls   = ['','weak','fair','good'];
    const names = ['','Lemah 🔴','Cukup 🟡','Kuat 🟢'];
    [b1,b2,b3].forEach((b,i) => { b.className = 'pwd-bar' + (i < score ? ' '+cls[score] : ''); });
    lbl.textContent = names[score] || '';
  }

  // CONFIRM MATCH
  function checkMatch() {
    const p1 = document.getElementById('pwd1').value;
    const p2 = document.getElementById('pwd2').value;
    const lbl = document.getElementById('matchLabel');
    if (!p2) { lbl.style.display='none'; return; }
    lbl.style.display = 'block';
    if (p1 === p2) {
      lbl.textContent = '✓ Password cocok';
      lbl.style.color = '#7BFF9A';
    } else {
      lbl.textContent = '✗ Password tidak cocok';
      lbl.style.color = 'var(--pink-soft)';
    }
  }
</script>
</body>
</html>