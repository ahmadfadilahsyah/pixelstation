<?php
session_start();
include "../config/koneksi.php";

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard-admin.php");
    } else {
        header("Location: ../user/dashboard-user.php");
    }
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, strtolower(trim($_POST['email'])));
    $password = $_POST['password'];
    $user_captcha = trim($_POST['captcha'] ?? '');
    $session_captcha = $_SESSION['captcha'] ?? '';

    // Validasi captcha (case-insensitive)
    if (empty($user_captcha) || strcasecmp($user_captcha, $session_captcha) !== 0) {
        $error = "Kode captcha salah! Silakan coba lagi.";
        unset($_SESSION['captcha']); // hapus captcha yang gagal
    } else {
        // Captcha benar, hapus session captcha
        unset($_SESSION['captcha']);

        // Cari user berdasarkan email
        $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($query) == 1) {
            $data = mysqli_fetch_assoc($query);
            // Verifikasi password dengan bcrypt
            if (password_verify($password, $data['password'])) {
                $_SESSION['id']    = $data['id'];
                $_SESSION['nama']  = $data['nama'];
                $_SESSION['email'] = $data['email'];
                $_SESSION['role']  = $data['role'];

                // Redirect berdasarkan role
                if ($data['role'] == 'admin') {
                    header("Location: ../admin/dashboard-admin.php");
                } else {
                    header("Location: ../user/dashboard-user.php");
                }
                exit;
            }
        }
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — PixelStation</title>
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
      overflow: hidden;
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
      position:absolute; left:-200px; top:50%; transform:translateY(-50%);
      width:600px; height:600px;
      background:radial-gradient(circle, rgba(255,62,128,.18), transparent 70%);
    }
    .bg-glow-r {
      position:absolute; right:-200px; top:50%; transform:translateY(-50%);
      width:500px; height:500px;
      background:radial-gradient(circle, rgba(255,209,102,.12), transparent 70%);
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
      background:linear-gradient(160deg, rgba(255,62,128,.1) 0%, rgba(255,209,102,.04) 100%);
      border-right:1px solid var(--border);
      position:relative;
      overflow:hidden;
    }
    @media(min-width:992px){ .left-panel { display:flex; } }

    .left-panel::before {
      content:'';
      position:absolute; inset:0;
      background:url("https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=800&q=60") center/cover;
      opacity:.08;
    }

    .lp-brand {
      position:relative; z-index:1;
    }
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
      background:linear-gradient(135deg,var(--pink),var(--yellow));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    }
    .lp-desc {
      color:var(--muted); font-size:.95rem; line-height:1.7; max-width:340px;
    }

    .lp-stats {
      position:relative; z-index:1;
      display:flex; gap:28px;
      padding-top:28px; border-top:1px solid var(--border);
    }
    .lp-stat-num {
      font-family:'Bebas Neue',sans-serif; font-size:2rem;
      background:linear-gradient(135deg,var(--yellow),var(--pink));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    }
    .lp-stat-label { font-size:.7rem; color:var(--muted); letter-spacing:.08em; text-transform:uppercase; }

    /* ─── RIGHT PANEL (FORM) ─── */
    .right-panel {
      flex:1;
      display:flex; flex-direction:column;
      align-items:center; justify-content:center;
      padding:40px 24px;
      overflow-y:auto;
    }

    .form-card {
      width:100%; max-width:440px;
    }

    .form-eyebrow {
      display:inline-flex; align-items:center; gap:8px;
      background:rgba(255,62,128,.12); border:1px solid rgba(255,62,128,.3);
      color:var(--pink-soft); font-size:.72rem; letter-spacing:.15em;
      padding:5px 14px; border-radius:30px;
      font-family:'Syne',sans-serif; font-weight:600;
      margin-bottom:20px;
    }
    .form-eyebrow .dot {
      width:5px; height:5px; background:var(--pink); border-radius:50%;
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
      background:linear-gradient(135deg,var(--pink),var(--yellow));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    }
    .form-subtitle {
      color:var(--muted); font-size:.88rem; margin-bottom:32px; line-height:1.6;
    }

    /* ─── INPUTS ─── */
    .field-wrap { margin-bottom:18px; }
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

    /* eye toggle */
    .eye-btn {
      position:absolute; right:14px; top:50%; transform:translateY(-50%);
      background:none; border:none; color:var(--muted); cursor:none;
      font-size:1rem; padding:4px; transition:color .2s;
      line-height:1;
    }
    .eye-btn:hover { color:var(--pink); }

    /* ─── CAPTCHA ─── */
    .captcha-row {
      display:flex; gap:10px; align-items:center; margin-bottom:8px;
    }
    .captcha-img {
      border:1.5px solid var(--border); border-radius:10px;
      cursor:none; transition:.25s; flex-shrink:0;
      background:var(--glass);
    }
    .captcha-img:hover { border-color:rgba(255,62,128,.5); }
    .captcha-refresh {
      width:44px; height:44px; border-radius:10px; flex-shrink:0;
      background:var(--glass); border:1.5px solid var(--border);
      color:var(--muted); cursor:none; font-size:1rem;
      display:flex; align-items:center; justify-content:center;
      transition:all .25s;
    }
    .captcha-refresh:hover { border-color:rgba(255,62,128,.4); color:var(--pink); transform:rotate(180deg); }

    /* ─── BTN ─── */
    .btn-submit {
      width:100%;
      font-family:'Syne',sans-serif; font-weight:700;
      background:linear-gradient(135deg,var(--pink),#c41a5c);
      color:#fff; border:none;
      padding:14px; border-radius:12px;
      letter-spacing:.06em; font-size:.95rem;
      position:relative; overflow:hidden;
      transition:transform .2s, box-shadow .2s;
      cursor:none; margin-top:8px;
    }
    .btn-submit::before {
      content:''; position:absolute; inset:0;
      background:linear-gradient(135deg,var(--yellow),var(--pink));
      opacity:0; transition:.3s;
    }
    .btn-submit:hover { transform:translateY(-2px); box-shadow:0 12px 35px var(--pink-glow); }
    .btn-submit:hover::before { opacity:.15; }
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
      color:var(--pink-soft); font-weight:600; text-decoration:none;
      transition:color .2s;
    }
    .form-footer-link a:hover { color:var(--pink); text-decoration:underline; }

    /* ─── ERROR / ALERT ─── */
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

    /* ─── DEMO BOX ─── */
    .demo-box {
      background:rgba(255,209,102,.06);
      border:1px solid rgba(255,209,102,.2);
      border-radius:12px; padding:14px 16px;
      margin-top:20px;
      font-size:.78rem; color:var(--muted);
    }
    .demo-box strong { color:var(--yellow); }
    .demo-box .demo-row { display:flex; align-items:center; gap:6px; margin-top:6px; }

    /* ─── SCROLLBAR ─── */
    ::-webkit-scrollbar { width:4px; }
    ::-webkit-scrollbar-track { background:var(--dark); }
    ::-webkit-scrollbar-thumb { background:linear-gradient(var(--pink),var(--yellow)); border-radius:3px; }

    /* ─── ENTRANCE ANIM ─── */
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
      .form-card { max-width:100%; }
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
        WELCOME<br>
        <span class="g">BACK,</span><br>
        GAMER.
      </div>
      <p class="lp-desc">
        Login dan langsung booking konsol favoritmu. PS4, PS5, kursi premium, dan suasana yang bikin betah.
      </p>
    </div>

    <div class="lp-stats">
      <div>
        <div class="lp-stat-num">50+</div>
        <div class="lp-stat-label">Unit Aktif</div>
      </div>
      <div>
        <div class="lp-stat-num">4.9</div>
        <div class="lp-stat-label">Rating</div>
      </div>
      <div>
        <div class="lp-stat-num">10K+</div>
        <div class="lp-stat-label">Sesi</div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">
    <div class="form-card">

      <div class="form-eyebrow fade-up">
        <span class="dot"></span> Selamat Datang Kembali
      </div>

      <h1 class="form-title fade-up fade-up-d1">
        MASUK KE<br><span>AKUNMU</span>
      </h1>
      <p class="form-subtitle fade-up fade-up-d1">
        Isi email dan password untuk lanjut ke dashboard.
      </p>

      <?php if ($error): ?>
      <div class="alert-px fade-up">
        <span>⚠️</span> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="" autocomplete="off">

        <!-- EMAIL -->
        <div class="field-wrap fade-up fade-up-d2">
          <label class="field-label">Email</label>
          <div class="field-inner">
            <span class="field-icon">📧</span>
            <input type="email" name="email" class="field-input"
              placeholder="contoh@email.com" required
              value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
          </div>
        </div>

        <!-- PASSWORD -->
        <div class="field-wrap fade-up fade-up-d3">
          <label class="field-label">Password</label>
          <div class="field-inner">
            <span class="field-icon">🔒</span>
            <input type="password" name="password" id="pwdField" class="field-input"
              placeholder="Masukkan password" required style="padding-right:46px;">
            <button type="button" class="eye-btn" onclick="togglePwd()" id="eyeBtn" title="Tampilkan password">👁️</button>
          </div>
        </div>

        <!-- CAPTCHA -->
        <div class="field-wrap fade-up fade-up-d3">
          <label class="field-label">🛡️ Kode Keamanan</label>
          <div class="captcha-row">
            <img src="captcha.php" alt="Captcha" class="captcha-img" id="captchaImg"
              onclick="refreshCaptcha()" height="44">
            <button type="button" class="captcha-refresh" onclick="refreshCaptcha()" title="Refresh captcha">🔄</button>
          </div>
          <div class="field-inner">
            <span class="field-icon">🔑</span>
            <input type="text" name="captcha" class="field-input"
              placeholder="Masukkan kode di atas" required autocomplete="off">
          </div>
          <div style="font-size:.72rem;color:var(--muted);margin-top:5px;">Klik gambar atau tombol untuk refresh</div>
        </div>

        <!-- SUBMIT -->
        <button type="submit" name="login" class="btn-submit fade-up fade-up-d4">
          <span>⚡ Login Sekarang</span>
        </button>

      </form>

      <div class="or-divider fade-up fade-up-d4">atau</div>

      <div class="form-footer-link fade-up fade-up-d5">
        Belum punya akun? <a href="register.php">Daftar Gratis →</a>
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

  // CAPTCHA
  function refreshCaptcha() {
    document.getElementById('captchaImg').src = 'captcha.php?t=' + Date.now();
  }

  // TOGGLE PASSWORD
  let visible = false;
  function togglePwd() {
    visible = !visible;
    const f = document.getElementById('pwdField');
    const b = document.getElementById('eyeBtn');
    f.type = visible ? 'text' : 'password';
    b.textContent = visible ? '🙈' : '👁️';
  }
</script>
</body>
</html>