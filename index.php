<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PixelStation — Premium Gaming Lounge</title>
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

    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--dark);
      color: var(--text);
      overflow-x: hidden;
      cursor: none;
    }

    /* ─── CURSOR ─── */
    .cursor-dot {
      position: fixed; width:10px; height:10px;
      background: var(--pink); border-radius:50%;
      pointer-events:none; z-index:99999;
      transform:translate(-50%,-50%);
      transition: transform .08s;
    }
    .cursor-ring {
      position: fixed; width:36px; height:36px;
      border:2px solid var(--pink-soft); border-radius:50%;
      pointer-events:none; z-index:99998;
      transform:translate(-50%,-50%);
      transition: all .18s ease;
      opacity:.7;
    }
    a:hover ~ .cursor-ring, button:hover ~ .cursor-ring { transform:translate(-50%,-50%) scale(1.6); }

    /* ─── LOADER ─── */
    .loader-wrap {
      position:fixed; inset:0; background:var(--dark);
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      z-index:9999; transition:opacity .6s .3s, visibility .6s .3s;
    }
    .loader-wrap.done { opacity:0; visibility:hidden; }
    .loader-logo {
      font-family:'Bebas Neue',sans-serif; font-size:3rem;
      background:linear-gradient(135deg, var(--pink), var(--yellow));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
      letter-spacing:.12em;
      animation: loaderPulse 1.2s ease-in-out infinite;
    }
    @keyframes loaderPulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    .loader-bar { width:160px; height:2px; background:var(--border); border-radius:2px; margin-top:20px; overflow:hidden; }
    .loader-bar-fill { height:100%; background:linear-gradient(90deg,var(--pink),var(--yellow)); animation:loadBar 1.2s ease forwards; }
    @keyframes loadBar { 0%{width:0} 100%{width:100%} }

    /* ─── NOISE OVERLAY ─── */
    body::before {
      content:''; position:fixed; inset:0;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
      opacity:.35; pointer-events:none; z-index:0;
    }

    /* ─── NAVBAR ─── */
    .navbar {
      position:fixed; top:0; width:100%; z-index:1000;
      padding:18px 0;
      background:transparent;
      transition: all .4s ease;
    }
    .navbar.scrolled {
      background:rgba(10,10,15,.85) !important;
      backdrop-filter:blur(20px);
      padding:12px 0;
      border-bottom:1px solid var(--border);
    }
    .navbar-brand {
      font-family:'Bebas Neue',sans-serif;
      font-size:1.7rem;
      letter-spacing:.1em;
      background:linear-gradient(135deg, var(--pink), var(--yellow));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    }
    .nav-link {
      color:var(--muted) !important;
      font-size:.875rem;
      font-weight:500;
      letter-spacing:.04em;
      padding:6px 14px !important;
      transition: color .25s;
      position:relative;
    }
    .nav-link::after {
      content:''; position:absolute; bottom:0; left:14px; right:14px; height:1px;
      background:var(--pink); transform:scaleX(0); transition:.3s;
    }
    .nav-link:hover { color:var(--text) !important; }
    .nav-link:hover::after { transform:scaleX(1); }

    /* ─── AUTH BUTTONS ─── */
    .btn-nav-auth {
      display: inline-flex; align-items: center; gap: 6px;
      font-family: 'Syne', sans-serif; font-weight: 600;
      font-size: .8rem; letter-spacing: .04em;
      padding: 8px 16px; border-radius: 8px;
      text-decoration: none; transition: all .25s;
      white-space: nowrap;
    }
    .btn-nav-login {
      background: var(--glass);
      border: 1.5px solid var(--border);
      color: var(--muted);
    }
    .btn-nav-login:hover {
      border-color: rgba(255,62,128,.5);
      color: var(--pink);
      background: rgba(255,62,128,.08);
      box-shadow: 0 0 18px var(--pink-glow);
    }
    .btn-nav-register {
      background: linear-gradient(135deg, var(--pink), #c41a5c);
      border: 1.5px solid transparent;
      color: #fff;
      box-shadow: 0 4px 16px var(--pink-glow);
    }
    .btn-nav-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 28px var(--pink-glow);
      color: #fff;
    }

    /* Mobile auth buttons */
    @media(max-width:991px) {
      .btn-nav-auth { width: 100%; justify-content: center; padding: 11px 16px; }
      .nav-item.ms-lg-3 { flex-direction: column; gap: 8px; margin-top: 8px; padding: 0 8px; }
    }

    /* ─── BTN ─── */
    .btn-px {
      font-family:'Syne',sans-serif; font-weight:700;
      background:linear-gradient(135deg, var(--pink) 0%, #c41a5c 100%);
      color:#fff; border:none;
      padding:12px 28px; border-radius:6px;
      letter-spacing:.05em; font-size:.9rem;
      position:relative; overflow:hidden;
      transition: transform .2s, box-shadow .2s;
    }
    .btn-px::before {
      content:''; position:absolute; inset:0;
      background:linear-gradient(135deg,var(--yellow),var(--pink));
      opacity:0; transition:opacity .3s;
    }
    .btn-px:hover { transform:translateY(-2px); box-shadow:0 12px 35px var(--pink-glow); color:#fff; }
    .btn-px:hover::before { opacity:.15; }
    .btn-px span { position:relative; z-index:1; }

    .btn-px-outline {
      font-family:'Syne',sans-serif; font-weight:600;
      background:transparent; color:var(--text);
      border:1.5px solid var(--border); padding:11px 26px;
      border-radius:6px; letter-spacing:.05em; font-size:.9rem;
      transition: all .25s;
    }
    .btn-px-outline:hover { border-color:var(--pink); color:var(--pink); box-shadow:0 0 20px var(--pink-glow); }

    /* ─── HERO ─── */
    #beranda {
      min-height:100vh; padding:0;
      position:relative; display:flex; align-items:center;
      overflow:hidden;
    }
    .hero-bg {
      position:absolute; inset:0; z-index:0;
    }
    .hero-bg-img {
      width:100%; height:100%; object-fit:cover; opacity:.18;
    }
    .hero-gradient {
      position:absolute; inset:0;
      background:
        radial-gradient(ellipse 80% 60% at 75% 50%, rgba(255,62,128,.18) 0%, transparent 70%),
        radial-gradient(ellipse 60% 80% at 20% 80%, rgba(255,209,102,.1) 0%, transparent 60%),
        linear-gradient(180deg, var(--dark) 0%, transparent 30%, transparent 70%, var(--dark) 100%);
    }
    .hero-grid {
      position:absolute; inset:0;
      background-image:
        linear-gradient(rgba(255,62,128,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,62,128,.06) 1px, transparent 1px);
      background-size:60px 60px;
      mask-image:radial-gradient(ellipse 80% 70% at 60% 50%, black, transparent);
    }
    .hero-content { position:relative; z-index:1; padding-top:100px; }
    .hero-eyebrow {
      display:inline-flex; align-items:center; gap:8px;
      background:rgba(255,62,128,.12); border:1px solid rgba(255,62,128,.3);
      color:var(--pink-soft); font-size:.78rem; letter-spacing:.15em;
      padding:6px 16px; border-radius:30px;
      font-family:'Syne',sans-serif; font-weight:600;
      margin-bottom:24px;
    }
    .hero-eyebrow .dot {
      width:6px; height:6px; background:var(--pink); border-radius:50%;
      animation:blink 1.5s infinite;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }
    .hero-h1 {
      font-family:'Bebas Neue',sans-serif;
      font-size:clamp(3.5rem,9vw,7.5rem);
      letter-spacing:.04em;
      line-height:.9;
      margin-bottom:24px;
    }
    .hero-h1 .line1 { display:block; color:var(--text); }
    .hero-h1 .line2 {
      display:block;
      background:linear-gradient(135deg, var(--pink) 0%, var(--yellow) 60%);
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    }
    .hero-desc {
      color:var(--muted); font-size:1.05rem; line-height:1.7;
      max-width:480px; margin-bottom:36px;
    }
    .hero-stats {
      display:flex; gap:36px; margin-top:48px;
      padding-top:36px; border-top:1px solid var(--border);
    }
    .hero-stat-num {
      font-family:'Bebas Neue',sans-serif; font-size:2.4rem;
      background:linear-gradient(135deg, var(--yellow), var(--pink));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
      letter-spacing:.06em;
    }
    .hero-stat-label { font-size:.78rem; color:var(--muted); letter-spacing:.08em; font-weight:500; text-transform:uppercase; }
    .hero-visual { position:relative; z-index:1; }
    .hero-img-wrap {
      position:relative;
      border-radius:20px; overflow:hidden;
      box-shadow: 0 0 0 1px var(--border), 0 40px 80px rgba(0,0,0,.6), 0 0 80px var(--pink-glow);
    }
    .hero-img-wrap img { width:100%; height:480px; object-fit:cover; display:block; }
    .hero-img-badge {
      position:absolute; bottom:20px; left:20px;
      background:rgba(10,10,15,.85); backdrop-filter:blur(20px);
      border:1px solid var(--border); border-radius:12px;
      padding:14px 18px; display:flex; align-items:center; gap:12px;
    }
    .hero-img-badge .badge-icon { font-size:1.5rem; }
    .hero-img-badge .badge-text { font-size:.75rem; color:var(--muted); }
    .hero-img-badge .badge-num { font-family:'Syne',sans-serif; font-weight:700; color:var(--yellow); font-size:1rem; }
    .hero-float-tag {
      position:absolute; top:24px; right:-16px;
      background:linear-gradient(135deg,var(--pink),#c41a5c);
      color:#fff; padding:8px 16px; border-radius:8px;
      font-family:'Syne',sans-serif; font-weight:700; font-size:.78rem;
      letter-spacing:.08em; box-shadow:0 8px 25px var(--pink-glow);
      transform:rotate(3deg);
    }

    /* ─── SECTION ─── */
    section { padding:100px 0; }
    .s-label {
      font-family:'Syne',sans-serif; font-weight:700;
      font-size:.72rem; letter-spacing:.2em; text-transform:uppercase;
      color:var(--pink); margin-bottom:12px;
    }
    .s-title {
      font-family:'Bebas Neue',sans-serif;
      font-size:clamp(2rem,5vw,3.5rem); letter-spacing:.06em;
      color:var(--text);
    }
    .s-title span { color:var(--pink); }
    .s-sub { color:var(--muted); font-size:1rem; line-height:1.7; max-width:520px; }

    /* ─── TENTANG: FEATURE CARDS ─── */
    .feat-card {
      background:var(--card-bg);
      border:1px solid var(--border);
      border-radius:16px; padding:32px 28px;
      position:relative; overflow:hidden;
      transition: transform .3s, border-color .3s, box-shadow .3s;
    }
    .feat-card::before {
      content:''; position:absolute; inset:0;
      background:linear-gradient(135deg, rgba(255,62,128,.06), transparent);
      opacity:0; transition:.3s;
    }
    .feat-card:hover { transform:translateY(-6px); border-color:rgba(255,62,128,.4); box-shadow:0 20px 60px rgba(0,0,0,.4), 0 0 30px var(--pink-glow); }
    .feat-card:hover::before { opacity:1; }
    .feat-icon {
      width:52px; height:52px; border-radius:12px;
      background:linear-gradient(135deg, rgba(255,62,128,.2), rgba(255,209,102,.1));
      border:1px solid rgba(255,62,128,.25);
      display:flex; align-items:center; justify-content:center;
      font-size:1.4rem; margin-bottom:20px;
    }
    .feat-title { font-family:'Syne',sans-serif; font-weight:700; font-size:1rem; margin-bottom:8px; }
    .feat-desc { color:var(--muted); font-size:.875rem; line-height:1.65; }

    /* ─── PAKET ─── */
    #paket { background: linear-gradient(180deg, var(--dark) 0%, var(--dark2) 50%, var(--dark) 100%); }
    .tab-switcher {
      display:inline-flex; background:var(--card-bg);
      border:1px solid var(--border); border-radius:10px; padding:5px; gap:4px;
    }
    .tab-btn {
      font-family:'Syne',sans-serif; font-weight:700;
      font-size:.85rem; letter-spacing:.06em;
      padding:10px 28px; border-radius:7px; border:none;
      background:transparent; color:var(--muted);
      transition: all .3s; cursor:none;
    }
    .tab-btn.active { background:linear-gradient(135deg,var(--pink),#c41a5c); color:#fff; box-shadow:0 6px 20px var(--pink-glow); }

    .price-card {
      background:var(--card-bg); border:1px solid var(--border);
      border-radius:20px; padding:36px 28px;
      position:relative; overflow:hidden;
      transition: all .35s; text-align:center;
    }
    .price-card.featured {
      border-color:rgba(255,62,128,.5);
      background:linear-gradient(160deg, rgba(255,62,128,.1) 0%, var(--card-bg) 60%);
    }
    .price-card:hover { transform:translateY(-8px); box-shadow:0 30px 70px rgba(0,0,0,.5), 0 0 40px var(--pink-glow); }
    .price-card.featured:hover { box-shadow:0 30px 70px rgba(0,0,0,.5), 0 0 60px var(--pink-glow); }
    .price-badge {
      position:absolute; top:18px; right:18px;
      background:linear-gradient(135deg,var(--yellow),#f0a500);
      color:#111; font-family:'Syne',sans-serif; font-weight:700;
      font-size:.68rem; letter-spacing:.1em; padding:4px 12px; border-radius:20px;
      text-transform:uppercase;
    }
    .price-plan { font-size:.75rem; letter-spacing:.15em; text-transform:uppercase; color:var(--muted); font-weight:500; margin-bottom:10px; }
    .price-num {
      font-family:'Bebas Neue',sans-serif; font-size:3.5rem; letter-spacing:.04em;
      background:linear-gradient(135deg, var(--yellow), var(--pink));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
      line-height:1;
    }
    .price-dur { color:var(--muted); font-size:.82rem; margin-bottom:28px; margin-top:4px; }
    .price-features { list-style:none; margin-bottom:28px; }
    .price-features li {
      padding:8px 0; border-bottom:1px solid var(--border);
      font-size:.875rem; color:var(--muted);
      display:flex; align-items:center; gap:10px;
    }
    .price-features li:last-child { border:none; }
    .price-features li .chk { color:var(--pink); font-size:1rem; }

    /* ─── PROMO ─── */
    #promo {
      position:relative; overflow:hidden;
      background:var(--dark2);
    }
    .promo-glow-l {
      position:absolute; left:-100px; top:50%; transform:translateY(-50%);
      width:400px; height:400px;
      background:radial-gradient(circle, rgba(255,62,128,.25), transparent 70%);
      pointer-events:none;
    }
    .promo-glow-r {
      position:absolute; right:-100px; top:50%; transform:translateY(-50%);
      width:400px; height:400px;
      background:radial-gradient(circle, rgba(255,209,102,.15), transparent 70%);
      pointer-events:none;
    }
    .promo-inner {
      position:relative; z-index:1;
      border:1px solid rgba(255,62,128,.3);
      border-radius:24px; padding:64px 48px;
      background:rgba(255,62,128,.05);
      backdrop-filter:blur(10px);
    }
    .promo-pct {
      font-family:'Bebas Neue',sans-serif;
      font-size:clamp(5rem,15vw,11rem);
      letter-spacing:.02em; line-height:.85;
      background:linear-gradient(135deg, var(--pink) 0%, var(--yellow) 60%);
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
      animation:glowPct 2s ease-in-out infinite;
    }
    @keyframes glowPct {
      0%,100% { filter:drop-shadow(0 0 20px var(--pink-glow)); }
      50% { filter:drop-shadow(0 0 45px rgba(255,62,128,.6)); }
    }
    .promo-tag {
      display:inline-block; background:linear-gradient(135deg,var(--yellow),#f0a500);
      color:#111; font-family:'Syne',sans-serif; font-weight:700;
      font-size:.72rem; letter-spacing:.15em; padding:6px 18px; border-radius:30px;
      text-transform:uppercase; margin-bottom:18px;
    }
    .promo-deal {
      display:flex; flex-direction:column; gap:14px;
    }
    .promo-deal-item {
      display:flex; align-items:center; gap:14px;
      padding:14px 20px; border-radius:12px;
      background:rgba(255,255,255,.04); border:1px solid var(--border);
      font-size:.9rem; color:var(--muted);
      transition:.25s;
    }
    .promo-deal-item:hover { border-color:rgba(255,62,128,.3); color:var(--text); }
    .promo-deal-item .pd-icon { font-size:1.4rem; flex-shrink:0; }

    /* ─── MEMBER ─── */
    .member-card {
      background:var(--card-bg); border:1px solid var(--border);
      border-radius:20px; padding:40px;
    }
    .member-benefit {
      display:flex; align-items:flex-start; gap:14px; padding:14px 0;
      border-bottom:1px solid var(--border);
    }
    .member-benefit:last-child { border:none; }
    .member-benefit-icon {
      width:36px; height:36px; border-radius:8px;
      background:rgba(255,62,128,.15); border:1px solid rgba(255,62,128,.25);
      display:flex; align-items:center; justify-content:center; font-size:.95rem;
      flex-shrink:0;
    }
    .member-benefit-text { font-size:.88rem; color:var(--muted); }
    .member-benefit-title { font-family:'Syne',sans-serif; font-weight:600; font-size:.9rem; color:var(--text); margin-bottom:2px; }
    .form-input {
      width:100%; background:var(--glass); border:1px solid var(--border);
      border-radius:10px; padding:14px 18px; color:var(--text);
      font-family:'DM Sans',sans-serif; font-size:.9rem;
      outline:none; transition:border-color .25s, box-shadow .25s;
      margin-bottom:14px;
    }
    .form-input::placeholder { color:var(--muted); }
    .form-input:focus { border-color:var(--pink); box-shadow:0 0 0 3px var(--pink-glow); }

    /* ─── TESTIMONIAL ─── */
    #testimoni { background:var(--dark2); }
    .testi-card {
      background:var(--card-bg); border:1px solid var(--border);
      border-radius:20px; padding:32px;
      transition:.3s; position:relative;
    }
    .testi-card::before {
      content:'❝';
      position:absolute; top:18px; right:24px;
      font-size:3rem; color:var(--pink); opacity:.15;
      font-family:Georgia,serif; line-height:1;
    }
    .testi-card:hover { border-color:rgba(255,62,128,.3); transform:translateY(-4px); }
    .testi-stars { color:var(--yellow); letter-spacing:.1em; font-size:.9rem; margin-bottom:14px; }
    .testi-text { font-size:.92rem; color:var(--muted); line-height:1.7; margin-bottom:20px; font-style:italic; }
    .testi-author { display:flex; align-items:center; gap:12px; }
    .testi-avatar {
      width:42px; height:42px; border-radius:50%;
      background:linear-gradient(135deg,var(--pink),var(--yellow));
      display:flex; align-items:center; justify-content:center;
      font-family:'Bebas Neue',sans-serif; font-size:1.1rem; color:#fff;
      flex-shrink:0;
    }
    .testi-name { font-family:'Syne',sans-serif; font-weight:600; font-size:.88rem; }
    .testi-role { font-size:.75rem; color:var(--muted); }

    /* ─── KONTAK ─── */
    .kontak-card {
      background:var(--card-bg); border:1px solid var(--border);
      border-radius:16px; padding:28px; display:flex; gap:18px; align-items:flex-start;
      transition:.3s;
    }
    .kontak-card:hover { border-color:rgba(255,62,128,.3); }
    .kontak-icon {
      width:48px; height:48px; border-radius:12px; flex-shrink:0;
      background:linear-gradient(135deg,rgba(255,62,128,.2),rgba(255,209,102,.1));
      border:1px solid rgba(255,62,128,.25);
      display:flex; align-items:center; justify-content:center; font-size:1.3rem;
    }
    .kontak-label { font-size:.72rem; letter-spacing:.12em; text-transform:uppercase; color:var(--muted); margin-bottom:4px; }
    .kontak-val { font-family:'Syne',sans-serif; font-weight:600; font-size:.95rem; }
    .map-wrap { border-radius:20px; overflow:hidden; border:1px solid var(--border); }

    /* ─── FOOTER ─── */
    footer {
      background:var(--dark2); border-top:1px solid var(--border);
      padding:60px 0 30px;
    }
    .footer-brand {
      font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:.1em;
      background:linear-gradient(135deg,var(--pink),var(--yellow));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    }
    .footer-desc { color:var(--muted); font-size:.88rem; line-height:1.7; max-width:300px; margin-top:12px; }
    .footer-heading { font-family:'Syne',sans-serif; font-weight:700; font-size:.78rem; letter-spacing:.14em; text-transform:uppercase; color:var(--muted); margin-bottom:18px; }
    .footer-link { display:block; color:var(--muted); font-size:.875rem; margin-bottom:10px; text-decoration:none; transition:color .2s; }
    .footer-link:hover { color:var(--pink); }
    .footer-bottom { border-top:1px solid var(--border); padding-top:24px; margin-top:48px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; }
    .footer-copy { font-size:.8rem; color:var(--muted); }
    .footer-socials { display:flex; gap:12px; }
    .footer-social-btn {
      width:36px; height:36px; border-radius:8px;
      background:var(--glass); border:1px solid var(--border);
      display:flex; align-items:center; justify-content:center;
      font-size:.95rem; text-decoration:none; transition:all .25s;
    }
    .footer-social-btn:hover { background:rgba(255,62,128,.2); border-color:rgba(255,62,128,.4); transform:translateY(-2px); }
    .footer-social-labeled { width:auto; padding:0 14px; gap:7px; font-family:'Syne',sans-serif; font-size:.78rem; font-weight:600; }
    .footer-social-name { color:var(--muted); transition:color .25s; }
    .footer-social-labeled:hover .footer-social-name { color:var(--pink); }

    /* ─── WA FLOAT ─── */
    .wa-btn {
      position:fixed; bottom:28px; right:28px; z-index:999;
      display:flex; align-items:center; gap:10px;
      background:linear-gradient(135deg,#25D366,#1aab52);
      color:#fff; font-family:'Syne',sans-serif; font-weight:700;
      font-size:.82rem; letter-spacing:.04em;
      padding:12px 20px; border-radius:50px;
      text-decoration:none;
      box-shadow:0 8px 30px rgba(37,211,102,.35);
      transition: transform .25s, box-shadow .25s;
    }
    .wa-btn:hover { transform:translateY(-3px); box-shadow:0 14px 40px rgba(37,211,102,.5); color:#fff; }

    /* ─── MODAL ─── */
    .modal-content {
      background:var(--dark3); border:1px solid var(--border);
      border-radius:20px; color:var(--text);
    }
    .modal-header { border-color:var(--border); }
    .modal-title { font-family:'Syne',sans-serif; font-weight:700; }
    .btn-close { filter:invert(1) brightness(.5); }

    /* ─── REVEAL ─── */
    .reveal { opacity:0; transform:translateY(40px); transition: opacity .7s ease, transform .7s ease; }
    .reveal.up { opacity:1; transform:translateY(0); }
    .reveal-d1 { transition-delay:.1s; }
    .reveal-d2 { transition-delay:.2s; }
    .reveal-d3 { transition-delay:.3s; }
    .reveal-d4 { transition-delay:.4s; }

    /* ─── DIVIDER ─── */
    .section-divider {
      width:60px; height:3px;
      background:linear-gradient(90deg,var(--pink),var(--yellow));
      border-radius:3px; margin:16px 0 24px;
    }

    /* ─── SCROLLBAR ─── */
    ::-webkit-scrollbar { width:6px; }
    ::-webkit-scrollbar-track { background:var(--dark); }
    ::-webkit-scrollbar-thumb { background:linear-gradient(var(--pink),var(--yellow)); border-radius:3px; }

    @media(max-width:768px) {
      .hero-stats { gap:20px; flex-wrap:wrap; }
      .promo-inner { padding:36px 24px; }
      .hero-float-tag { display:none; }
    }
  </style>
</head>
<body>

<!-- CURSOR -->
<div class="cursor-dot" id="cdot"></div>
<div class="cursor-ring" id="cring"></div>

<!-- LOADER -->
<div class="loader-wrap" id="loader">
  <div class="loader-logo">PixelStation</div>
  <div class="loader-bar"><div class="loader-bar-fill"></div></div>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg" id="navbar">
  <div class="container">
    <a class="navbar-brand" href="#">🎮 PixelStation</a>
    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#menu" style="color:var(--muted);">
      <span style="font-size:1.4rem;">☰</span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link" href="#beranda">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
        <li class="nav-item"><a class="nav-link" href="#paket">Paket</a></li>
        <li class="nav-item"><a class="nav-link" href="#promo">Promo</a></li>
        <li class="nav-item"><a class="nav-link" href="#member">Member</a></li>
        <li class="nav-item"><a class="nav-link" href="#testimoni">Testimoni</a></li>
        <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
        <li class="nav-item ms-lg-3 d-flex align-items-center gap-2">
          <a href="auth/login.php" class="btn-nav-auth btn-nav-login">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Login
          </a>
          <a href="auth/register.php" class="btn-nav-auth btn-nav-register">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            Daftar
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ═══ HERO ═══ -->
<section id="beranda">
  <div class="hero-bg">
    <img class="hero-bg-img" src="https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1800&q=80" alt="">
    <div class="hero-gradient"></div>
    <div class="hero-grid"></div>
  </div>
  <div class="container hero-content">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-eyebrow reveal">
          <span class="dot"></span> Premium Gaming Lounge · Cirebon
        </div>
        <h1 class="hero-h1 reveal reveal-d1">
          <span class="line1">LEVEL UP</span>
          <span class="line2">YOUR GAME</span>
        </h1>
        <p class="hero-desc reveal reveal-d2">
          Nikmati pengalaman gaming PS4 & PS5 terbaik dengan konsol premium, ruangan estetik, dan suasana yang bikin betah main berjam-jam.
        </p>
        <div class="d-flex flex-wrap gap-3 reveal reveal-d3">
          <button class="btn-px" onclick="openBookingModal(1)"><span>⚡ Booking Instan</span></button>
          <a href="#paket" class="btn-px-outline">Lihat Paket Harga</a>
        </div>
        <div class="hero-stats reveal reveal-d4">
          <div>
            <div class="hero-stat-num">50+</div>
            <div class="hero-stat-label">Unit Aktif</div>
          </div>
          <div>
            <div class="hero-stat-num">4.9</div>
            <div class="hero-stat-label">Rating Pengguna</div>
          </div>
          <div>
            <div class="hero-stat-num">10K+</div>
            <div class="hero-stat-label">Sesi Dimainkan</div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 hero-visual reveal reveal-d2">
        <div class="hero-img-wrap">
          <div class="hero-float-tag">🔥 NOW OPEN</div>
          <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3500">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=900&q=80" class="d-block w-100" style="height:460px;object-fit:cover;">
              </div>
              <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1621259182978-fbf93132d53d?auto=format&fit=crop&w=900&q=80" class="d-block w-100" style="height:460px;object-fit:cover;">
              </div>
              <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1612287230202-1ff1d85d1bdf?auto=format&fit=crop&w=900&q=80" class="d-block w-100" style="height:460px;object-fit:cover;">
              </div>
            </div>
          </div>
          <div class="hero-img-badge">
            <div class="badge-icon">🎮</div>
            <div>
              <div class="badge-num">PS4 & PS5</div>
              <div class="badge-text">Konsol terbaru tersedia</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ TENTANG ═══ -->
<section id="tentang">
  <div class="container">
    <div class="row align-items-end mb-5">
      <div class="col-lg-6">
        <div class="s-label reveal">Tentang Kami</div>
        <div class="section-divider reveal"></div>
        <h2 class="s-title reveal">MENGAPA <span>PIXELSTATION?</span></h2>
      </div>
      <div class="col-lg-6">
        <p class="s-sub reveal">PixelStation hadir sebagai gaming lounge premium di Cirebon — tempat terbaik untuk mabar, santai, atau turnamen. Semua di satu tempat yang keren.</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-md-4 col-sm-6 reveal reveal-d1">
        <div class="feat-card h-100">
          <div class="feat-icon">🎮</div>
          <div class="feat-title">Console Terbaru</div>
          <div class="feat-desc">PS4 dan PS5 performa maksimal dengan game-game populer terlengkap. Update rutin setiap bulan.</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 reveal reveal-d2">
        <div class="feat-card h-100">
          <div class="feat-icon">💺</div>
          <div class="feat-title">Ruangan Premium</div>
          <div class="feat-desc">Kursi gaming ergonomis, AC dingin, dan pencahayaan ambiance. Betah main sampai tutup.</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 reveal reveal-d3">
        <div class="feat-card h-100">
          <div class="feat-icon">⚡</div>
          <div class="feat-title">Booking Super Cepat</div>
          <div class="feat-desc">Pesan slot dalam 30 detik via website atau WhatsApp. Konfirmasi otomatis langsung.</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 reveal reveal-d1">
        <div class="feat-card h-100">
          <div class="feat-icon">💸</div>
          <div class="feat-title">Harga Transparan</div>
          <div class="feat-desc">Tidak ada biaya tersembunyi. Paket fleksibel dari 1 hingga 6 jam dengan promo rutin.</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 reveal reveal-d2">
        <div class="feat-card h-100">
          <div class="feat-icon">👥</div>
          <div class="feat-title">Zona Mabar Luas</div>
          <div class="feat-desc">Area dedicated untuk squad dengan koneksi jaringan stabil dan setup multi-screen.</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 reveal reveal-d3">
        <div class="feat-card h-100">
          <div class="feat-icon">⭐</div>
          <div class="feat-title">Pelayanan 24/7</div>
          <div class="feat-desc">Staff profesional siap membantu. Rating kepuasan pelanggan konsisten 4.9/5.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ PAKET ═══ -->
<section id="paket">
  <div class="container">
    <div class="text-center mb-5">
      <div class="s-label reveal">Paket & Harga</div>
      <div class="section-divider reveal mx-auto"></div>
      <h2 class="s-title reveal">PILIH <span>PAKETMU</span></h2>
      <p class="s-sub mx-auto mt-3 reveal">Transparan, fleksibel, dan worth every rupiah.</p>
    </div>

    <div class="text-center mb-5 reveal">
      <div class="tab-switcher" id="tabSwitch">
        <button class="tab-btn active" onclick="switchTab('ps4',this)">🎮 PlayStation 4</button>
        <button class="tab-btn" onclick="switchTab('ps5',this)">🚀 PlayStation 5</button>
      </div>
    </div>

    <!-- PS4 -->
    <div id="pane-ps4">
      <div class="row g-4 justify-content-center">
        <div class="col-md-4 reveal reveal-d1">
          <div class="price-card">
            <div class="price-plan">Basic</div>
            <div class="price-num">15K</div>
            <div class="price-dur">per jam</div>
            <ul class="price-features text-start">
              <li><span class="chk">✦</span> 1 Jam Penuh</li>
              <li><span class="chk">✦</span> Akses Semua Game</li>
              <li><span class="chk">✦</span> 1 Controller</li>
              <li><span class="chk">✦</span> WiFi Gratis</li>
            </ul>
            <button class="btn-px w-100" onclick="pilihPaket('Basic PS4 - 1 Jam','15000')"><span>Pilih Paket</span></button>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-d2">
          <div class="price-card featured">
            <div class="price-badge">POPULER</div>
            <div class="price-plan">Pro</div>
            <div class="price-num">40K</div>
            <div class="price-dur">3 jam · hemat 11%</div>
            <ul class="price-features text-start">
              <li><span class="chk">✦</span> 3 Jam Nonstop</li>
              <li><span class="chk">✦</span> Akses Semua Game</li>
              <li><span class="chk">✦</span> 2 Controller</li>
              <li><span class="chk">✦</span> WiFi Gratis</li>
              <li><span class="chk">✦</span> Snack Ringan</li>
            </ul>
            <button class="btn-px w-100" onclick="pilihPaket('Pro PS4 - 3 Jam','40000')"><span>Pilih Paket</span></button>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-d3">
          <div class="price-card">
            <div class="price-plan">VIP</div>
            <div class="price-num">70K</div>
            <div class="price-dur">6 jam · hemat 22%</div>
            <ul class="price-features text-start">
              <li><span class="chk">✦</span> 6 Jam Full Access</li>
              <li><span class="chk">✦</span> Akses Semua Game</li>
              <li><span class="chk">✦</span> 2 Controller</li>
              <li><span class="chk">✦</span> WiFi Gratis</li>
              <li><span class="chk">✦</span> Minuman + Snack</li>
              <li><span class="chk">✦</span> Prioritas Kursi</li>
            </ul>
            <button class="btn-px w-100" onclick="pilihPaket('VIP PS4 - 6 Jam','70000')"><span>Pilih Paket</span></button>
          </div>
        </div>
      </div>
    </div>

    <!-- PS5 -->
    <div id="pane-ps5" style="display:none;">
      <div class="row g-4 justify-content-center">
        <div class="col-md-4 reveal reveal-d1">
          <div class="price-card">
            <div class="price-plan">Basic</div>
            <div class="price-num">25K</div>
            <div class="price-dur">per jam</div>
            <ul class="price-features text-start">
              <li><span class="chk">✦</span> 1 Jam Penuh</li>
              <li><span class="chk">✦</span> Semua Game PS5</li>
              <li><span class="chk">✦</span> DualSense Controller</li>
              <li><span class="chk">✦</span> WiFi Gratis</li>
            </ul>
            <button class="btn-px w-100" onclick="pilihPaket('Basic PS5 - 1 Jam','25000')"><span>Pilih Paket</span></button>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-d2">
          <div class="price-card featured">
            <div class="price-badge">POPULER</div>
            <div class="price-plan">Pro</div>
            <div class="price-num">65K</div>
            <div class="price-dur">3 jam · hemat 13%</div>
            <ul class="price-features text-start">
              <li><span class="chk">✦</span> 3 Jam Nonstop</li>
              <li><span class="chk">✦</span> Semua Game PS5</li>
              <li><span class="chk">✦</span> 2 DualSense</li>
              <li><span class="chk">✦</span> WiFi Gratis</li>
              <li><span class="chk">✦</span> Snack Ringan</li>
            </ul>
            <button class="btn-px w-100" onclick="pilihPaket('Pro PS5 - 3 Jam','65000')"><span>Pilih Paket</span></button>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-d3">
          <div class="price-card">
            <div class="price-plan">VIP</div>
            <div class="price-num">120K</div>
            <div class="price-dur">6 jam · hemat 20%</div>
            <ul class="price-features text-start">
              <li><span class="chk">✦</span> 6 Jam Full Access</li>
              <li><span class="chk">✦</span> Semua Game PS5</li>
              <li><span class="chk">✦</span> 2 DualSense</li>
              <li><span class="chk">✦</span> WiFi Gratis</li>
              <li><span class="chk">✦</span> Minuman + Snack</li>
              <li><span class="chk">✦</span> Prioritas Kursi VIP</li>
            </ul>
            <button class="btn-px w-100" onclick="pilihPaket('VIP PS5 - 6 Jam','120000')"><span>Pilih Paket</span></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ PROMO ═══ -->
<section id="promo">
  <div class="promo-glow-l"></div>
  <div class="promo-glow-r"></div>
  <div class="container">
    <div class="promo-inner reveal">
      <div class="row align-items-center g-5">
        <div class="col-lg-5 text-center text-lg-start">
          <div class="promo-tag">🔥 Promo Terbatas</div>
          <div class="promo-pct">20%<br>OFF</div>
          <p class="mt-3" style="color:var(--muted);font-size:.95rem;">Untuk pelajar & mahasiswa setiap Senin – Kamis. Tunjukkan KTM/kartu pelajar.</p>
          <button class="btn-px mt-4" onclick="openBookingModal(1)"><span>Klaim Promo →</span></button>
        </div>
        <div class="col-lg-7">
          <div class="promo-deal">
            <div class="promo-deal-item">
              <div class="pd-icon">🎮</div>
              <div>
                <div style="font-family:'Syne',sans-serif;font-weight:600;color:var(--text);margin-bottom:3px;">Main 3 Jam, Gratis 30 Menit</div>
                <div style="font-size:.82rem;">Bonus waktu otomatis di setiap booking 3 jam</div>
              </div>
            </div>
            <div class="promo-deal-item">
              <div class="pd-icon">👥</div>
              <div>
                <div style="font-family:'Syne',sans-serif;font-weight:600;color:var(--text);margin-bottom:3px;">Paket Mabar Hemat Mulai 50K</div>
                <div style="font-size:.82rem;">Sewa bareng lebih murah, cocok untuk squad 4–6 orang</div>
              </div>
            </div>
            <div class="promo-deal-item">
              <div class="pd-icon">⭐</div>
              <div>
                <div style="font-family:'Syne',sans-serif;font-weight:600;color:var(--text);margin-bottom:3px;">Member: Bonus 1 Jam tiap 10x Main</div>
                <div style="font-size:.82rem;">Poin otomatis terakumulasi di setiap sesi</div>
              </div>
            </div>
            <div class="promo-deal-item">
              <div class="pd-icon">🏆</div>
              <div>
                <div style="font-family:'Syne',sans-serif;font-weight:600;color:var(--text);margin-bottom:3px;">Turnamen Bulanan Berhadiah</div>
                <div style="font-size:.82rem;">Daftar gratis untuk member aktif PixelStation</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ MEMBER ═══ -->
<section id="member">
  <div class="container">
    <div class="text-center mb-5">
      <div class="s-label reveal">Membership</div>
      <div class="section-divider mx-auto reveal"></div>
      <h2 class="s-title reveal">JADI <span>MEMBER ELITE</span></h2>
      <p class="s-sub mx-auto mt-3 reveal">Unlock keuntungan eksklusif yang tidak tersedia untuk pengunjung biasa.</p>
    </div>
    <div class="row g-4 align-items-start">
      <div class="col-lg-5 reveal">
        <div class="member-card">
          <div class="s-label mb-4">Keuntungan Member</div>
          <div class="member-benefit">
            <div class="member-benefit-icon">💎</div>
            <div>
              <div class="member-benefit-title">Diskon Booking 20%</div>
              <div class="member-benefit-text">Berlaku untuk semua paket PS4 dan PS5 setiap hari.</div>
            </div>
          </div>
          <div class="member-benefit">
            <div class="member-benefit-icon">⏱️</div>
            <div>
              <div class="member-benefit-title">Bonus 1 Jam / 10x Main</div>
              <div class="member-benefit-text">Poin terkumpul otomatis, bisa ditukar kapan saja.</div>
            </div>
          </div>
          <div class="member-benefit">
            <div class="member-benefit-icon">🎯</div>
            <div>
              <div class="member-benefit-title">Prioritas Slot Booking</div>
              <div class="member-benefit-text">Antrean member diproses lebih cepat saat peak hour.</div>
            </div>
          </div>
          <div class="member-benefit">
            <div class="member-benefit-icon">🏆</div>
            <div>
              <div class="member-benefit-title">Event Turnamen Eksklusif</div>
              <div class="member-benefit-text">Akses gratis ke kompetisi bulanan berhadiah menarik.</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7 reveal reveal-d2">
        <div class="member-card">
          <div class="s-label mb-4">Daftar Sekarang — Gratis!</div>
          <div class="row g-3">
            <div class="col-sm-6">
              <input class="form-input" id="namaMember" placeholder="Nama Lengkap">
            </div>
            <div class="col-sm-6">
              <input class="form-input" id="waMember" placeholder="Nomor WhatsApp">
            </div>
            <div class="col-12">
              <input class="form-input" id="emailMember" placeholder="Alamat Email">
            </div>
            <div class="col-12">
              <button class="btn-px w-100" onclick="daftarMember()"><span>🎮 Gabung Member Sekarang</span></button>
            </div>
          </div>
          <p style="font-size:.78rem;color:var(--muted);margin-top:14px;text-align:center;">Gratis selamanya · Tidak ada biaya pendaftaran · Langsung aktif</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ TESTIMONI ═══ -->
<section id="testimoni">
  <div class="container">
    <div class="text-center mb-5">
      <div class="s-label reveal">Testimoni</div>
      <div class="section-divider mx-auto reveal"></div>
      <h2 class="s-title reveal">APA KATA <span>MEREKA</span></h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4 reveal reveal-d1">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"Tempat paling keren di Cirebon buat main PS! Kursinya nyaman, ACnya dingin, dan staffnya ramah banget. Wajib balik lagi."</p>
          <div class="testi-author">
            <div class="testi-avatar">R</div>
            <div>
              <div class="testi-name">Rizki Pratama</div>
              <div class="testi-role">Member Aktif · 45 sesi</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-d2">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"PS5 di sini lancar banget! FIFA sama GTA gak ada lag sama sekali. Paket VIP 6 jam itu worth it banget buat mabar sama temen."</p>
          <div class="testi-author">
            <div class="testi-avatar">A</div>
            <div>
              <div class="testi-name">Aliya Rahma</div>
              <div class="testi-role">Pelanggan · PS5 Enthusiast</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-d3">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"Booking via website gampang banget, langsung konfirmasi WhatsApp. Diskon studentnya lumayan banget buat anak kos kayak saya!"</p>
          <div class="testi-author">
            <div class="testi-avatar">F</div>
            <div>
              <div class="testi-name">Fadhil Maulana</div>
              <div class="testi-role">Mahasiswa · Member Elite</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-d1">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"Turnamen bulanannya seru banget! Hadiahnya juga lumayan. Komunitas gamernya solid dan kompetitif. Highly recommended!"</p>
          <div class="testi-author">
            <div class="testi-avatar">D</div>
            <div>
              <div class="testi-name">Dika Anwar</div>
              <div class="testi-role">Juara Turnamen Maret</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-d2">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"Suka banget sama ambiance-nya, gelap tapi stylish. Kayak gaming lounge di Jakarta tapi ada di Cirebon. Bangga!"</p>
          <div class="testi-author">
            <div class="testi-avatar">S</div>
            <div>
              <div class="testi-name">Sinta Dewi</div>
              <div class="testi-role">Pelanggan Setia</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-d3">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"Bonus jam member-nya beneran dikasih! Udah 3x dapat bonus jam gratis. Sistemnya jujur dan transparan. 10/10."</p>
          <div class="testi-author">
            <div class="testi-avatar">H</div>
            <div>
              <div class="testi-name">Hendra S.</div>
              <div class="testi-role">Member Platinum · 90 sesi</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ KONTAK ═══ -->
<section id="kontak">
  <div class="container">
    <div class="row align-items-end mb-5">
      <div class="col-lg-6">
        <div class="s-label reveal">Kontak & Lokasi</div>
        <div class="section-divider reveal"></div>
        <h2 class="s-title reveal">TEMUKAN <span>KAMI</span></h2>
      </div>
      <div class="col-lg-6">
        <p class="s-sub reveal">Kami berlokasi di pusat Kota Cirebon. Datang langsung atau booking terlebih dahulu untuk memastikan slot tersedia.</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="d-flex flex-column gap-3">
          <div class="kontak-card reveal reveal-d1">
            <div class="kontak-icon">📍</div>
            <div>
              <div class="kontak-label">Alamat</div>
              <div class="kontak-val">Kec. Kesambi, Kota Cirebon, Jawa Barat</div>
            </div>
          </div>
          <div class="kontak-card reveal reveal-d2">
            <div class="kontak-icon">📞</div>
            <div>
              <div class="kontak-label">Telepon / WhatsApp</div>
              <div class="kontak-val">085871435748</div>
            </div>
          </div>
          <div class="kontak-card reveal reveal-d4">
            <div class="kontak-icon">🕒</div>
            <div>
              <div class="kontak-label">Jam Operasional</div>
              <div class="kontak-val">Setiap Hari, 10:00 – 23:00</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-8 reveal reveal-d2">
        <div class="map-wrap" style="height:360px;">
          <iframe src="https://maps.google.com/maps?q=7G7J%2B4JJ+Karyamulya+Kesambi+Cirebon&t=&z=13&ie=UTF8&iwloc=&output=embed" width="100%" height="100%" style="border:0;display:block;" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ FOOTER ═══ -->
<footer>
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <div class="footer-brand">🎮 PixelStation</div>
        <p class="footer-desc">Premium Gaming Lounge di Cirebon. Tempatnya gamer sejati yang ingin pengalaman terbaik tanpa harus keluar kota.</p>
      </div>
      <div class="col-lg-2 col-sm-4">
        <div class="footer-heading">Menu</div>
        <a class="footer-link" href="#beranda">Beranda</a>
        <a class="footer-link" href="#tentang">Tentang</a>
        <a class="footer-link" href="#paket">Paket</a>
        <a class="footer-link" href="#promo">Promo</a>
      </div>
      <div class="col-lg-2 col-sm-4">
        <div class="footer-heading">Layanan</div>
        <a class="footer-link" href="#member">Membership</a>
        <a class="footer-link" href="#testimoni">Testimoni</a>
        <a class="footer-link" href="#kontak">Kontak</a>
        <a class="footer-link" href="#">Turnamen</a>
      </div>
      <div class="col-lg-4 col-sm-4">
        <div class="footer-heading">Jam Buka</div>
        <p class="footer-link" style="cursor:default;">Senin – Jumat: 10:00 – 23:00</p>
        <p class="footer-link" style="cursor:default;">Sabtu – Minggu: 09:00 – 24:00</p>
        <p class="footer-link" style="cursor:default;">Libur Nasional: 10:00 – 22:00</p>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="footer-copy">© 2026 PixelStation. All Rights Reserved.</div>
      <div class="footer-socials">
        <a class="footer-social-btn footer-social-labeled" href="https://instagram.com/abstactbanget" target="_blank" title="Instagram">
          <span>📸</span><span class="footer-social-name">@abstactbanget</span>
        </a>
        <a class="footer-social-btn footer-social-labeled" href="https://tiktok.com/@abstactbanget" target="_blank" title="TikTok">
          <span>🎵</span><span class="footer-social-name">@abstactbanget</span>
        </a>
        <a class="footer-social-btn" href="https://wa.me/6285871435748" target="_blank" title="WhatsApp">💬</a>
      </div>
    </div>
  </div>
</footer>

<!-- WA FLOAT -->
<a class="wa-btn" href="https://wa.me/6285871435748" target="_blank">
  💬 Chat WhatsApp
</a>

<!-- MODAL BOOKING WIZARD -->
<div class="modal fade" id="bookingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="background:var(--dark3);border:1px solid var(--border);border-radius:24px;overflow:hidden;">

      <!-- HEADER -->
      <div style="padding:24px 28px 0;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:20px;">
        <div>
          <div style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;letter-spacing:.08em;background:linear-gradient(135deg,var(--pink),var(--yellow));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">⚡ BOOKING INSTAN</div>
          <div id="stepLabel" style="font-size:.78rem;color:var(--muted);margin-top:2px;letter-spacing:.06em;">LANGKAH 1 DARI 3 — PILIH KONSOL</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1) brightness(.5);"></button>
      </div>

      <!-- STEPPER -->
      <div style="padding:16px 28px;display:flex;align-items:center;gap:0;border-bottom:1px solid var(--border);">
        <div class="bk-step active" id="bkstep1">
          <div class="bk-step-circle">1</div>
          <div class="bk-step-name">Konsol</div>
        </div>
        <div class="bk-step-line" id="bkline1"></div>
        <div class="bk-step" id="bkstep2">
          <div class="bk-step-circle">2</div>
          <div class="bk-step-name">Paket</div>
        </div>
        <div class="bk-step-line" id="bkline2"></div>
        <div class="bk-step" id="bkstep3">
          <div class="bk-step-circle">3</div>
          <div class="bk-step-name">Detail</div>
        </div>
      </div>

      <!-- BODY -->
      <div style="padding:28px;">

        <!-- ═══ STEP 1: PILIH KONSOL ═══ -->
        <div id="step1">
          <p style="color:var(--muted);font-size:.88rem;margin-bottom:20px;">Pilih console yang ingin kamu sewa:</p>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="bk-console-card" onclick="pilihKonsol('PS4')" id="cardPS4">
              <div style="font-size:2.5rem;margin-bottom:12px;">🎮</div>
              <div style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;letter-spacing:.08em;color:var(--text);">PS4</div>
              <div style="font-size:.8rem;color:var(--muted);margin-top:6px;">Mulai dari <span style="color:var(--yellow);font-weight:600;">Rp15.000</span> / jam</div>
              <div style="font-size:.75rem;color:var(--muted);margin-top:4px;">Koleksi game lengkap</div>
            </div>
            <div class="bk-console-card" onclick="pilihKonsol('PS5')" id="cardPS5">
              <div style="font-size:2.5rem;margin-bottom:12px;">🚀</div>
              <div style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;letter-spacing:.08em;color:var(--text);">PS5</div>
              <div style="font-size:.8rem;color:var(--muted);margin-top:6px;">Mulai dari <span style="color:var(--yellow);font-weight:600;">Rp25.000</span> / jam</div>
              <div style="font-size:.75rem;color:var(--muted);margin-top:4px;">Next-gen experience</div>
            </div>
          </div>
        </div>

        <!-- ═══ STEP 2: PILIH PAKET ═══ -->
        <div id="step2" style="display:none;">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
            <button onclick="goStep(1)" style="background:var(--glass);border:1px solid var(--border);border-radius:8px;padding:6px 14px;color:var(--muted);font-size:.8rem;cursor:none;transition:.2s;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">← Kembali</button>
            <div id="konsolLabel" style="font-size:.82rem;color:var(--muted);">Paket untuk <span style="color:var(--pink);font-weight:600;"></span></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;" id="paketGrid">
            <!-- diisi JS -->
          </div>
        </div>

        <!-- ═══ STEP 3: DETAIL ═══ -->
        <div id="step3" style="display:none;">
          <!-- Ringkasan Paket -->
          <div id="bkSummary" style="display:flex;align-items:center;gap:16px;padding:16px 20px;background:rgba(255,62,128,.08);border:1px solid rgba(255,62,128,.25);border-radius:14px;margin-bottom:22px;">
            <div id="bkSumIcon" style="font-size:2rem;"></div>
            <div style="flex:1;">
              <div id="bkSumNama" style="font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;"></div>
              <div id="bkSumDur" style="font-size:.8rem;color:var(--muted);margin-top:2px;"></div>
            </div>
            <div>
              <div id="bkSumHarga" style="font-family:'Bebas Neue',sans-serif;font-size:1.6rem;letter-spacing:.06em;background:linear-gradient(135deg,var(--yellow),var(--pink));-webkit-background-clip:text;-webkit-text-fill-color:transparent;"></div>
              <button onclick="goStep(2)" style="font-size:.72rem;color:var(--pink);background:none;border:none;cursor:none;text-decoration:underline;padding:0;">Ganti Paket</button>
            </div>
          </div>

          <!-- Form -->
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div style="grid-column:1/-1;">
              <label style="font-size:.75rem;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Nama Lengkap *</label>
              <input class="form-input mb-0" id="namaBooking" placeholder="Contoh: Rizki Pratama">
            </div>
            <div style="grid-column:1/-1;">
              <label style="font-size:.75rem;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Nomor WhatsApp *</label>
              <input class="form-input mb-0" id="waBooking" placeholder="Contoh: 085871435748" type="tel">
            </div>
            <div>
              <label style="font-size:.75rem;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Tanggal Main *</label>
              <input type="date" class="form-input mb-0" id="tanggalBooking">
            </div>
            <div>
              <label style="font-size:.75rem;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Jam Mulai *</label>
              <input type="time" class="form-input mb-0" id="jamBooking" min="10:00" max="23:00">
            </div>
            <div style="grid-column:1/-1;">
              <label style="font-size:.75rem;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Catatan (opsional)</label>
              <input class="form-input mb-0" id="catatanBooking" placeholder="Contoh: minta kursi pojok, bawa squad 4 orang, dll.">
            </div>
          </div>

          <div style="margin-top:20px;">
            <button class="btn-px w-100" onclick="kirimWA()"><span>📲 Konfirmasi & Kirim ke WhatsApp</span></button>
            <p style="font-size:.74rem;color:var(--muted);text-align:center;margin-top:10px;">WhatsApp PixelStation akan merespons dalam &lt;5 menit pada jam operasional.</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
  /* ─── BOOKING WIZARD STYLES ─── */
  .bk-step { display:flex; flex-direction:column; align-items:center; gap:4px; }
  .bk-step-circle {
    width:32px; height:32px; border-radius:50%;
    background:var(--glass); border:1.5px solid var(--border);
    display:flex; align-items:center; justify-content:center;
    font-family:'Syne',sans-serif; font-weight:700; font-size:.8rem;
    color:var(--muted); transition:.4s;
  }
  .bk-step-name { font-size:.68rem; color:var(--muted); letter-spacing:.1em; text-transform:uppercase; transition:.3s; }
  .bk-step.active .bk-step-circle { background:linear-gradient(135deg,var(--pink),#c41a5c); border-color:var(--pink); color:#fff; box-shadow:0 4px 16px var(--pink-glow); }
  .bk-step.active .bk-step-name { color:var(--pink); }
  .bk-step.done .bk-step-circle { background:rgba(255,62,128,.2); border-color:rgba(255,62,128,.5); color:var(--pink); }
  .bk-step.done .bk-step-name { color:var(--muted); }
  .bk-step-line { flex:1; height:1.5px; background:var(--border); margin:0 8px; margin-bottom:18px; transition:background .4s; }
  .bk-step-line.done { background:linear-gradient(90deg,var(--pink),rgba(255,62,128,.3)); }

  .bk-console-card {
    border:1.5px solid var(--border);
    border-radius:18px; padding:28px 20px;
    text-align:center; cursor:none;
    transition: all .3s;
    background:var(--card-bg);
    position:relative; overflow:hidden;
  }
  .bk-console-card:hover {
    border-color:rgba(255,62,128,.5);
    background:rgba(255,62,128,.07);
    transform:translateY(-4px);
    box-shadow:0 16px 40px rgba(0,0,0,.4), 0 0 30px var(--pink-glow);
  }
  .bk-console-card.selected {
    border-color:var(--pink);
    background:rgba(255,62,128,.12);
    box-shadow:0 0 0 3px var(--pink-glow), 0 16px 40px rgba(0,0,0,.4);
  }

  .bk-paket-card {
    border:1.5px solid var(--border);
    border-radius:14px; padding:20px 16px;
    text-align:center; cursor:none;
    transition: all .3s;
    background:var(--card-bg);
    position:relative;
  }
  .bk-paket-card:hover {
    border-color:rgba(255,62,128,.5);
    transform:translateY(-3px);
    box-shadow:0 12px 35px rgba(0,0,0,.4), 0 0 20px var(--pink-glow);
  }
  .bk-paket-card.featured { border-color:rgba(255,62,128,.4); background:rgba(255,62,128,.07); }
  .bk-paket-name { font-family:'Syne',sans-serif; font-weight:700; font-size:.85rem; color:var(--text); margin-bottom:8px; }
  .bk-paket-price { font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:.04em; background:linear-gradient(135deg,var(--yellow),var(--pink)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1; }
  .bk-paket-dur { font-size:.72rem; color:var(--muted); margin-bottom:14px; margin-top:3px; }
  .bk-paket-tag { display:inline-block; background:linear-gradient(135deg,var(--yellow),#f0a500); color:#111; font-size:.6rem; font-weight:700; letter-spacing:.1em; padding:2px 10px; border-radius:20px; text-transform:uppercase; margin-bottom:8px; }
  .bk-paket-feat { font-size:.72rem; color:var(--muted); line-height:1.8; }
  .bk-select-btn { margin-top:12px; width:100%; background:rgba(255,62,128,.15); border:1px solid rgba(255,62,128,.3); color:var(--pink); border-radius:8px; padding:8px; font-family:'Syne',sans-serif; font-weight:600; font-size:.78rem; cursor:none; transition:.25s; }
  .bk-select-btn:hover { background:linear-gradient(135deg,var(--pink),#c41a5c); color:#fff; border-color:transparent; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ─── LOADER ───
  window.addEventListener('load', () => {
    setTimeout(() => document.getElementById('loader').classList.add('done'), 1200);
  });

  // ─── CURSOR ───
  const cdot = document.getElementById('cdot');
  const cring = document.getElementById('cring');
  document.addEventListener('mousemove', e => {
    cdot.style.left = e.clientX + 'px';
    cdot.style.top = e.clientY + 'px';
    cring.style.left = e.clientX + 'px';
    cring.style.top = e.clientY + 'px';
  });
  document.querySelectorAll('a,button,.feat-card,.price-card,.testi-card').forEach(el => {
    el.addEventListener('mouseenter', () => { cring.style.transform = 'translate(-50%,-50%) scale(1.7)'; cring.style.opacity = '1'; });
    el.addEventListener('mouseleave', () => { cring.style.transform = 'translate(-50%,-50%) scale(1)'; cring.style.opacity = '.7'; });
  });

  // ─── NAVBAR ───
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
  });

  // ─── REVEAL ───
  const revealEls = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('up'); });
  }, { threshold: 0.12 });
  revealEls.forEach(el => io.observe(el));

  // ─── TAB SWITCHER ───
  function switchTab(tab, btn) {
    document.getElementById('pane-ps4').style.display = tab === 'ps4' ? 'block' : 'none';
    document.getElementById('pane-ps5').style.display = tab === 'ps5' ? 'block' : 'none';
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  }

  // ══════════════════════════════════
  //  BOOKING WIZARD
  // ══════════════════════════════════
  const PAKET_DATA = {
    PS4: [
      { id:'basic-ps4', nama:'Basic PS4', dur:'1 Jam', harga:15000, icon:'🎮', feat:['1 Jam Penuh','Semua Game PS4','1 Controller','WiFi Gratis'], popular:false },
      { id:'pro-ps4',   nama:'Pro PS4',   dur:'3 Jam', harga:40000, icon:'🎮', feat:['3 Jam Nonstop','Semua Game PS4','2 Controller','WiFi + Snack'], popular:true  },
      { id:'vip-ps4',   nama:'VIP PS4',   dur:'6 Jam', harga:70000, icon:'🎮', feat:['6 Jam Full','Semua Game PS4','2 Controller','Minuman + Snack','Kursi Prioritas'], popular:false }
    ],
    PS5: [
      { id:'basic-ps5', nama:'Basic PS5', dur:'1 Jam', harga:25000,  icon:'🚀', feat:['1 Jam Penuh','Semua Game PS5','DualSense','WiFi Gratis'], popular:false },
      { id:'pro-ps5',   nama:'Pro PS5',   dur:'3 Jam', harga:65000,  icon:'🚀', feat:['3 Jam Nonstop','Semua Game PS5','2 DualSense','WiFi + Snack'], popular:true  },
      { id:'vip-ps5',   nama:'VIP PS5',   dur:'6 Jam', harga:120000, icon:'🚀', feat:['6 Jam Full','Semua Game PS5','2 DualSense','Minuman + Snack','Kursi VIP'], popular:false }
    ]
  };

  let bkState = { konsol: '', paket: null, step: 1 };

  function openBookingModal(jumpToStep, preKonsol, prePaketId) {
    // Reset
    bkState = { konsol: preKonsol || '', paket: null, step: 1 };
    document.getElementById('namaBooking').value = '';
    document.getElementById('waBooking').value = '';
    document.getElementById('tanggalBooking').value = '';
    document.getElementById('jamBooking').value = '';
    document.getElementById('catatanBooking').value = '';

    if (jumpToStep === 3 && preKonsol && prePaketId) {
      const p = PAKET_DATA[preKonsol].find(x => x.id === prePaketId);
      if (p) { bkState.paket = p; goStep(3); }
      else goStep(1);
    } else if (jumpToStep === 2 && preKonsol) {
      renderPaketGrid(preKonsol);
      goStep(2);
    } else {
      goStep(1);
    }
    new bootstrap.Modal(document.getElementById('bookingModal')).show();
  }

  function goStep(n) {
    bkState.step = n;
    [1,2,3].forEach(i => {
      document.getElementById('step'+i).style.display = i===n ? 'block' : 'none';
    });
    const labels = ['PILIH KONSOL','PILIH PAKET','ISI DETAIL & KONFIRMASI'];
    document.getElementById('stepLabel').textContent = `LANGKAH ${n} DARI 3 — ${labels[n-1]}`;

    // stepper UI
    [1,2,3].forEach(i => {
      const el = document.getElementById('bkstep'+i);
      el.classList.remove('active','done');
      if (i === n) el.classList.add('active');
      else if (i < n) el.classList.add('done');
    });
    [1,2].forEach(i => {
      const ln = document.getElementById('bkline'+i);
      ln.classList.toggle('done', i < n);
    });
  }

  function pilihKonsol(k) {
    bkState.konsol = k;
    document.querySelectorAll('.bk-console-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('card'+k).classList.add('selected');
    setTimeout(() => { renderPaketGrid(k); goStep(2); }, 200);
  }

  function renderPaketGrid(k) {
    const pakets = PAKET_DATA[k];
    document.getElementById('konsolLabel').innerHTML = `Paket untuk <span style="color:var(--pink);font-weight:600;">PlayStation ${k.replace('PS','')}</span>`;
    const grid = document.getElementById('paketGrid');
    grid.innerHTML = pakets.map(p => `
      <div class="bk-paket-card ${p.popular?'featured':''}" onclick="pilihPaketWizard('${k}','${p.id}')">
        ${p.popular ? '<div class="bk-paket-tag">⚡ Populer</div>' : '<div style="height:20px;"></div>'}
        <div class="bk-paket-name">${p.nama}</div>
        <div class="bk-paket-price">${(p.harga/1000).toFixed(0)}K</div>
        <div class="bk-paket-dur">${p.dur}</div>
        <div class="bk-paket-feat">${p.feat.map(f=>'✦ '+f).join('<br>')}</div>
        <button class="bk-select-btn">Pilih Paket →</button>
      </div>
    `).join('');
  }

  function pilihPaketWizard(k, paketId) {
    const p = PAKET_DATA[k].find(x => x.id === paketId);
    if (!p) return;
    bkState.paket = p;
    // isi summary
    document.getElementById('bkSumIcon').textContent = p.icon;
    document.getElementById('bkSumNama').textContent = p.nama;
    document.getElementById('bkSumDur').textContent = p.dur + ' · ' + p.feat.join(', ');
    document.getElementById('bkSumHarga').textContent = 'Rp'+p.harga.toLocaleString('id-ID');
    goStep(3);
  }

  // Dipanggil dari tombol "Pilih" di halaman paket
  function pilihPaket(namaStr, hargaStr) {
    // parse konsol & paket dari namaStr, e.g. "Pro PS4 - 3 Jam"
    const isPS5 = namaStr.includes('PS5');
    const k = isPS5 ? 'PS5' : 'PS4';
    const level = namaStr.toLowerCase().includes('basic') ? 'basic' : namaStr.toLowerCase().includes('pro') ? 'pro' : 'vip';
    const paketId = level + '-' + k.toLowerCase();
    openBookingModal(3, k, paketId);
  }

  function kirimWA() {
    const nama    = document.getElementById('namaBooking').value.trim();
    const wa      = document.getElementById('waBooking').value.trim();
    const tgl     = document.getElementById('tanggalBooking').value;
    const jam     = document.getElementById('jamBooking').value;
    const catatan = document.getElementById('catatanBooking').value.trim();
    const p       = bkState.paket;

    if (!nama)   { shakeInput('namaBooking');    return; }
    if (!wa)     { shakeInput('waBooking');      return; }
    if (!tgl)    { shakeInput('tanggalBooking'); return; }
    if (!jam)    { shakeInput('jamBooking');     return; }

    // Format tanggal cantik
    const tglObj = new Date(tgl);
    const hariNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const bulanNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    const tglStr = `${hariNames[tglObj.getDay()]}, ${tglObj.getDate()} ${bulanNames[tglObj.getMonth()]} ${tglObj.getFullYear()}`;

    const hargaFmt = 'Rp' + p.harga.toLocaleString('id-ID');

    const msg = [
      `Halo PixelStation! 👋`,
      ``,
      `Saya ingin melakukan *booking* dengan detail berikut:`,
      ``,
      `👤 *Nama*      : ${nama}`,
      `📞 *WhatsApp*  : ${wa}`,
      ``,
      `🎮 *Console*   : ${p.nama.includes('PS5') ? 'PlayStation 5 🚀' : 'PlayStation 4 🎮'}`,
      `📦 *Paket*     : ${p.nama}`,
      `⏱️ *Durasi*    : ${p.dur}`,
      `💰 *Total*     : ${hargaFmt}`,
      ``,
      `📅 *Tanggal*   : ${tglStr}`,
      `🕒 *Jam Mulai* : ${jam} WIB`,
      catatan ? `📝 *Catatan*   : ${catatan}` : '',
      ``,
      `Mohon konfirmasi ketersediaan slot-nya. Terima kasih! 🙏`,
    ].filter(l => l !== null).join('%0A');

    window.open(`https://wa.me/6285871435748?text=${encodeURIComponent(decodeURIComponent(msg)).replace(/%20/g,'+')}`, '_blank');
  }

  function shakeInput(id) {
    const el = document.getElementById(id);
    el.style.borderColor = 'var(--pink)';
    el.style.animation = 'shake .35s ease';
    el.focus();
    setTimeout(() => { el.style.animation = ''; el.style.borderColor = ''; }, 400);
  }

  // Inject shake keyframe
  const shakeStyle = document.createElement('style');
  shakeStyle.textContent = `@keyframes shake{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-6px)}40%,80%{transform:translateX(6px)}}`;
  document.head.appendChild(shakeStyle);

  // ─── MEMBER ───
  function daftarMember() {
    const nama = document.getElementById('namaMember').value;
    const wa = document.getElementById('waMember').value;
    const email = document.getElementById('emailMember').value;
    if (!nama || !wa) { alert('Isi nama dan nomor WhatsApp terlebih dahulu.'); return; }
    const msg = `Halo PixelStation!%0ASaya ingin daftar member:%0A%0A👤 Nama: *${nama}*%0A📞 WhatsApp: *${wa}*%0A📧 Email: *${email}*%0A%0AMohon konfirmasinya. Terima kasih!`;
    window.open(`https://wa.me/6285871435748?text=${msg}`, '_blank');
  }
</script>
</body>
</html>