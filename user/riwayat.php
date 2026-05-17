<?php
include "../config/session.php";
include "../config/koneksi.php";

$user_id = $_SESSION['id'];

$result = mysqli_query($conn, "
    SELECT bookings.*, playstations.nama_ps
    FROM bookings
    JOIN playstations ON bookings.ps_id = playstations.id
    WHERE bookings.user_id='$user_id'
    ORDER BY bookings.id DESC
");

$total_rows  = mysqli_num_rows($result);
$rows        = [];
$total_spent = 0;
$total_hours = 0;

while ($r = mysqli_fetch_assoc($result)) {
    $rows[]       = $r;
    $total_spent += $r['total_harga'];
    $total_hours += $r['durasi'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Booking | PixelStation</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ── TOKENS ── */
:root {
    --bg:      #07070F;
    --l1:      rgba(255,255,255,.038);
    --l2:      rgba(255,255,255,.065);
    --pink:    #FF3E80;
    --pink-d:  #C02060;
    --yellow:  #FFD166;
    --purple:  #A78BFA;
    --green:   #34D399;
    --text:    #F5F2FF;
    --muted:   rgba(245,242,255,.48);
    --bd:      rgba(255,255,255,.07);
    --bd2:     rgba(255,255,255,.13);
    --rad:     20px;
    --pill:    100px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

/* grain overlay */
body::after {
    content: '';
    position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    opacity: .025;
}

/* ambient glows */
.gl { position: fixed; border-radius: 50%; filter: blur(110px); pointer-events: none; z-index: 0; animation: gdrift 14s ease-in-out infinite alternate; }
.gl-1 { width: 700px; height: 700px; background: rgba(255,62,128,.09);  top: -200px; left: -200px; }
.gl-2 { width: 500px; height: 500px; background: rgba(167,139,250,.07); bottom: -150px; right: -150px; animation-delay: -7s; }
@keyframes gdrift { from { transform:translate(0,0); } to { transform:translate(40px,28px); } }

/* ── TOPBAR ── */
.topbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 200;
    background: rgba(7,7,15,.82);
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--bd);
}
.topbar-inner {
    max-width: 1160px; margin: 0 auto; padding: 0 28px;
    height: 70px; display: flex; align-items: center; justify-content: space-between;
}
.brand { font-family: 'Bebas Neue'; font-size: 2rem; letter-spacing: 3px; color: var(--text); text-decoration: none; }
.brand em { color: var(--pink); font-style: normal; }
.btn-back {
    display: inline-flex; align-items: center; gap: 9px;
    background: var(--l1); border: 1px solid var(--bd2);
    color: var(--muted); padding: 11px 22px;
    border-radius: 13px; font-size: .88rem; font-weight: 700;
    text-decoration: none; transition: .22s; white-space: nowrap;
}
.btn-back:hover { background: var(--l2); color: var(--text); }
.btn-back .lbl { display: inline; }

/* ── PAGE ── */
.page {
    position: relative; z-index: 1;
    max-width: 1160px; margin: 0 auto;
    padding: 106px 28px 80px;
}

/* ── HEADING ── */
.ph {
    margin-bottom: 44px;
    opacity: 0; animation: up .5s .04s ease forwards;
}
.ph-label {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--pink); font-family: 'Syne';
    font-size: .78rem; letter-spacing: 3px; text-transform: uppercase;
    margin-bottom: 12px;
}
.ph-title {
    font-family: 'Bebas Neue';
    font-size: clamp(3rem, 6vw, 4.8rem);
    letter-spacing: 4px; line-height: .95; margin-bottom: 12px;
}
.ph-sub { color: var(--muted); font-size: .98rem; }
.ph-sub strong { color: var(--yellow); }

/* ── STATS ── */
.stats {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 14px;
    margin-bottom: 40px;
    opacity: 0; animation: up .5s .10s ease forwards;
}
.sbox {
    background: var(--l1); border: 1px solid var(--bd);
    border-radius: var(--rad); padding: 22px 26px;
    position: relative; overflow: hidden; transition: .28s;
}
.sbox::after {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--pink), var(--yellow));
    transform: scaleX(0); transform-origin: left; transition: .28s;
}
.sbox:hover { background: var(--l2); }
.sbox:hover::after { transform: scaleX(1); }
.sbox-icon {
    width: 40px; height: 40px; border-radius: 11px;
    background: rgba(255,62,128,.10); color: var(--pink);
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; margin-bottom: 14px;
}
.sbox-num {
    font-family: 'Bebas Neue'; font-size: 2.4rem;
    color: var(--yellow); line-height: 1; letter-spacing: 1px; margin-bottom: 3px;
}
.sbox-lbl { font-family: 'Syne'; font-size: .72rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--muted); }

/* ── FILTER BAR ── */
/*  chips stretch to fill the same width as the cards below  */
.filter-wrap {
    margin-bottom: 28px;
    opacity: 0; animation: up .5s .16s ease forwards;
}
.filter-bar {
    display: flex; gap: 10px;
    /* no overflow-x — let it wrap on very small screens; hide on mobile via scroll */
    overflow-x: auto; scrollbar-width: none;
}
.filter-bar::-webkit-scrollbar { display: none; }

.chip {
    flex: 1;                    /* ← equal width, fills full bar */
    min-width: 0;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: var(--l1); border: 1px solid var(--bd);
    color: var(--muted);
    padding: 13px 10px;
    border-radius: var(--pill);
    font-size: .88rem; font-weight: 700;
    cursor: pointer; transition: .2s;
    user-select: none; -webkit-tap-highlight-color: transparent;
    white-space: nowrap;
    text-align: center;
}
.chip:hover { background: var(--l2); color: var(--text); border-color: var(--bd2); }
.chip.active { background: rgba(255,62,128,.13); border-color: rgba(255,62,128,.35); color: var(--pink); }
.chip-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ── BOOKING LIST ── */
.blist { display: flex; flex-direction: column; gap: 14px; }

.brow {
    opacity: 0; animation: up .48s ease forwards;
}
.brow:nth-child(1)   { animation-delay:.20s; }
.brow:nth-child(2)   { animation-delay:.26s; }
.brow:nth-child(3)   { animation-delay:.32s; }
.brow:nth-child(4)   { animation-delay:.38s; }
.brow:nth-child(n+5) { animation-delay:.43s; }

/* ── CARD ── */
.bcard {
    position: relative;
    background: var(--l1); border: 1px solid var(--bd);
    border-radius: var(--rad); overflow: hidden;
    transition: transform .3s, border-color .3s, box-shadow .3s;
    -webkit-tap-highlight-color: transparent;
}
.bcard:hover {
    transform: translateY(-4px);
    border-color: rgba(255,62,128,.22);
    box-shadow: 0 18px 50px rgba(0,0,0,.42);
}

/* top gradient line */
.bcard-line {
    height: 3px;
    background: linear-gradient(90deg, var(--pink), var(--yellow));
}

/*
   3-COLUMN INNER LAYOUT
   ┌──────────────────┬──────────────────┬─────────────┐
   │  COL A (PS info) │  COL B (meta)    │  COL C (Rp) │
   │  ~38%            │  ~38%            │  ~24%       │
   └──────────────────┴──────────────────┴─────────────┘
*/
.bcard-body {
    display: grid;
    grid-template-columns: 38fr 38fr 24fr;
    align-items: center;
    padding: 26px 28px;
    gap: 0;
}

/* separators between cols */
.bcard-col {
    padding: 0 26px;
}
.bcard-col:first-child { padding-left: 0; }
.bcard-col:last-child  { padding-right: 0; }
.bcard-col + .bcard-col {
    border-left: 1px solid var(--bd);
}

/* ── COL A: PS info ── */
.ps-row { display: flex; align-items: center; gap: 16px; margin-bottom: 12px; }
.ps-ava {
    width: 54px; height: 54px; min-width: 54px;
    border-radius: 16px;
    background: rgba(255,62,128,.10); border: 1px solid rgba(255,62,128,.15);
    display: flex; align-items: center; justify-content: center;
    color: var(--pink); font-size: 1.25rem;
}
.ps-name {
    font-family: 'Syne'; font-size: 1.15rem; font-weight: 800;
    line-height: 1.25; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ps-id { font-size: .75rem; color: var(--muted); font-family: monospace; margin-top: 3px; }

/* status sits below the name row */
.sbadge {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 7px 16px; border-radius: var(--pill);
    font-family: 'Syne'; font-size: .72rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .8px; white-space: nowrap;
}
.sbadge .sd { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.sbadge.pending   { color:var(--yellow); background:rgba(255,209,102,.10); border:1px solid rgba(255,209,102,.22); }
.sbadge.confirmed { color:var(--green);  background:rgba(52,211,153,.10);  border:1px solid rgba(52,211,153,.22); }
.sbadge.done      { color:var(--purple); background:rgba(167,139,250,.10); border:1px solid rgba(167,139,250,.22); }
.sbadge.cancelled { color:var(--pink);   background:rgba(255,62,128,.10);  border:1px solid rgba(255,62,128,.22); }
.sbadge.pending .sd, .sbadge.confirmed .sd { animation: blink 2s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.2;} }

/* ── COL B: meta ── */
.meta-title { font-family:'Syne'; font-size:.7rem; letter-spacing:2px; text-transform:uppercase; color:var(--pink); margin-bottom:14px; }
.mpills { display:flex; flex-direction:column; gap:10px; }
.mpill {
    display:inline-flex; align-items:center; gap:10px;
    padding:10px 16px;
    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07);
    border-radius:11px; font-size:.88rem; color:var(--muted);
}
.mpill i { color:var(--pink); font-size:.8rem; width:14px; text-align:center; }
.mpill-val { color:var(--text); font-weight:500; }

/* ── COL C: price ── */
.price-col { text-align:right; }
.price-lbl { font-family:'Syne'; font-size:.7rem; letter-spacing:2px; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.price-val {
    font-family:'Bebas Neue'; font-size:2.6rem;
    color:var(--yellow); letter-spacing:1px; line-height:1; margin-bottom:12px;
}
.dur-pill {
    display:inline-flex; align-items:center; justify-content:flex-end; gap:8px;
    width:100%; padding:10px 16px;
    background:rgba(255,62,128,.08); border:1px solid rgba(255,62,128,.16);
    border-radius:12px; color:var(--pink); font-size:.88rem; font-weight:700;
}

/* ── EMPTY ── */
.empty {
    text-align:center; padding:110px 20px;
    opacity:0; animation:up .5s .18s ease forwards;
}
.empty-box {
    width:130px; height:130px; border-radius:34px; margin:0 auto 30px;
    display:flex; align-items:center; justify-content:center; font-size:3.5rem;
    background:rgba(255,62,128,.07); border:1px solid rgba(255,62,128,.14);
}
.empty-title { font-family:'Bebas Neue'; font-size:2.8rem; letter-spacing:3px; margin-bottom:12px; }
.empty-body  { color:var(--muted); font-size:1rem; line-height:1.8; margin-bottom:34px; }
.btn-cta {
    display:inline-flex; align-items:center; gap:12px;
    padding:15px 36px; border-radius:16px;
    background:linear-gradient(135deg,var(--pink),var(--pink-d));
    color:#fff; text-decoration:none;
    font-family:'Syne'; font-weight:800; font-size:.9rem;
    letter-spacing:1px; text-transform:uppercase;
    box-shadow:0 10px 30px rgba(255,62,128,.3); transition:.3s;
}
.btn-cta:hover { transform:translateY(-3px); color:#fff; box-shadow:0 18px 40px rgba(255,62,128,.45); }

/* no-result msg */
.no-result {
    text-align:center; padding:50px 20px; color:var(--muted);
    font-size:.95rem; display:none;
}
.no-result i { display:block; font-size:2rem; color:var(--pink); opacity:.4; margin-bottom:12px; }

/* ── ANIMATION ── */
@keyframes up { from{opacity:0;transform:translateY(18px);} to{opacity:1;transform:translateY(0);} }

/* ── SCROLLBAR ── */
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:rgba(255,62,128,.3);border-radius:3px;}

/* ══ RESPONSIVE ══ */

/* tablet */
@media(max-width:900px){
    .bcard-body { grid-template-columns:1fr 1fr; gap:0; }
    /* price col goes full width below */
    .bcard-col:last-child {
        grid-column:1/-1;
        border-left:none;
        border-top:1px solid var(--bd);
        padding:16px 0 0;
        margin-top:16px;
        display:flex;
        align-items:center;
        justify-content:space-between;
    }
    .price-col { text-align:left; display:flex; align-items:center; gap:20px; }
    .price-val { font-size:2.2rem; margin-bottom:0; }
    .dur-pill  { width:auto; }
}

/* mobile */
@media(max-width:600px){
    .topbar-inner  { height:62px; padding:0 18px; }
    .brand         { font-size:1.6rem; }
    .btn-back .lbl { display:none; }
    .btn-back      { padding:10px 14px; }

    .page { padding:92px 16px 64px; }
    .ph-title { font-size:2.7rem; }

    /* stats: 2 cols, last spans full */
    .stats { grid-template-columns:1fr 1fr; gap:10px; }
    .stats .sbox:last-child { grid-column:1/-1; }
    .sbox { padding:18px 20px; }
    .sbox-num { font-size:2rem; }

    /* chips: scroll horizontally, fixed size */
    .chip { flex:0 0 auto; padding:11px 18px; font-size:.82rem; }

    /* card: single column */
    .bcard-body { grid-template-columns:1fr; gap:0; }
    .bcard-col+.bcard-col {
        border-left:none; border-top:1px solid var(--bd);
        padding:16px 0 0; margin-top:16px;
    }
    .bcard-col:last-child {
        display:flex; align-items:center; justify-content:space-between;
    }
    .price-col { text-align:left; display:flex; align-items:center; gap:16px; }
    .price-val { font-size:2rem; margin-bottom:0; }
    .dur-pill  { width:auto; }

    .bcard-body { padding:20px 18px; }
    .ps-ava { width:46px; height:46px; min-width:46px; font-size:1.1rem; }
    .ps-name { font-size:1rem; }
}
</style>
</head>
<body>

<div class="gl gl-1"></div>
<div class="gl gl-2"></div>

<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="dashboard-user.php">🎮 PIXEL<em>STATION</em></a>
        <a class="btn-back" href="dashboard-user.php">
            <i class="fa-solid fa-arrow-left"></i>
            <span class="lbl">Dashboard</span>
        </a>
    </div>
</header>

<div class="page">

    <!-- HEADING -->
    <div class="ph">
        <div class="ph-label"><i class="fa-solid fa-clock-rotate-left"></i> Aktivitas Kamu</div>
        <div class="ph-title">Riwayat<br>Booking</div>
        <?php if($total_rows > 0): ?>
        <div class="ph-sub">Total <strong><?= $total_rows ?> sesi</strong> tercatat</div>
        <?php endif; ?>
    </div>

    <?php if($total_rows === 0): ?>

    <!-- EMPTY -->
    <div class="empty">
        <div class="empty-box">🎮</div>
        <div class="empty-title">Belum Ada Booking</div>
        <div class="empty-body">Kamu belum punya riwayat booking.<br>Yuk mulai sesi pertamamu!</div>
        <a href="booking-user.php" class="btn-cta"><i class="fa-solid fa-gamepad"></i> Booking Sekarang</a>
    </div>

    <?php else: ?>

    <!-- STATS -->
    <div class="stats">
        <div class="sbox">
            <div class="sbox-icon"><i class="fa-solid fa-gamepad"></i></div>
            <div class="sbox-num"><?= $total_rows ?></div>
            <div class="sbox-lbl">Total Sesi</div>
        </div>
        <div class="sbox">
            <div class="sbox-icon"><i class="fa-solid fa-hourglass-half"></i></div>
            <div class="sbox-num"><?= $total_hours ?></div>
            <div class="sbox-lbl">Total Jam</div>
        </div>
        <div class="sbox">
            <div class="sbox-icon"><i class="fa-solid fa-wallet"></i></div>
            <div class="sbox-num">Rp<?= number_format($total_spent/1000,0) ?>K</div>
            <div class="sbox-lbl">Total Pengeluaran</div>
        </div>
    </div>

    <!-- FILTER — full width, equal chips -->
    <div class="filter-wrap">
        <div class="filter-bar">
            <div class="chip active" data-filter="all">
                Semua <span style="color:var(--muted);font-weight:400;font-size:.8rem;"><?= $total_rows ?></span>
            </div>
            <div class="chip" data-filter="pending">
                <span class="chip-dot" style="background:var(--yellow)"></span>Pending
            </div>
            <div class="chip" data-filter="confirmed">
                <span class="chip-dot" style="background:var(--green)"></span>Confirmed
            </div>
            <div class="chip" data-filter="done">
                <span class="chip-dot" style="background:var(--purple)"></span>Selesai
            </div>
            <div class="chip" data-filter="cancelled">
                <span class="chip-dot" style="background:var(--pink)"></span>Dibatalkan
            </div>
        </div>
    </div>

    <!-- LIST -->
    <div class="blist" id="blist">

    <?php foreach($rows as $d):
        $status = strtolower($d['status']);
        if($status=='confirmed')     $lbl='Confirmed';
        elseif($status=='cancelled') $lbl='Dibatalkan';
        elseif($status=='done')      $lbl='Selesai';
        else                         $lbl='Pending';
        $tgl = date('d M Y', strtotime($d['tanggal']));
    ?>

    <div class="brow" data-status="<?= $status ?>">
        <div class="bcard">

            <!-- top pink–yellow line -->
            <div class="bcard-line"></div>

            <div class="bcard-body">

                <!-- ── COL A: PS info + status ── -->
                <div class="bcard-col">
                    <div class="ps-row">
                        <div class="ps-ava"><i class="fa-solid fa-gamepad"></i></div>
                        <div>
                            <div class="ps-name"><?= htmlspecialchars($d['nama_ps']) ?></div>
                            <div class="ps-id">#<?= str_pad($d['id'],5,'0',STR_PAD_LEFT) ?></div>
                        </div>
                    </div>
                    <div class="sbadge <?= $status ?>">
                        <span class="sd"></span><?= $lbl ?>
                    </div>
                </div>

                <!-- ── COL B: date / time / duration ── -->
                <div class="bcard-col">
                    <div class="meta-title"><i class="fa-solid fa-circle-info me-1"></i>Detail Sesi</div>
                    <div class="mpills">
                        <div class="mpill">
                            <i class="fa-solid fa-calendar-days"></i>
                            <span class="mpill-val"><?= $tgl ?></span>
                        </div>
                        <div class="mpill">
                            <i class="fa-solid fa-clock"></i>
                            <span class="mpill-val">Mulai <?= $d['jam_mulai'] ?></span>
                        </div>
                        <div class="mpill">
                            <i class="fa-solid fa-hourglass-half"></i>
                            <span class="mpill-val">Durasi <?= $d['durasi'] ?> Jam</span>
                        </div>
                    </div>
                </div>

                <!-- ── COL C: price ── -->
                <div class="bcard-col">
                    <div class="price-col">
                        <div>
                            <div class="price-lbl">Total Bayar</div>
                            <div class="price-val">Rp<?= number_format($d['total_harga'],0,',','.') ?></div>
                        </div>
                        <div class="dur-pill">
                            <i class="fa-solid fa-receipt"></i>
                            <?= $d['durasi'] ?> × sesi
                        </div>
                    </div>
                </div>

            </div><!-- /bcard-body -->
        </div>
    </div>

    <?php endforeach; ?>

    </div><!-- /blist -->

    <div class="no-result" id="no-result">
        <i class="fa-solid fa-filter"></i>
        Tidak ada booking dengan status ini.
    </div>

    <?php endif; ?>

</div><!-- /page -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    const chips  = document.querySelectorAll('.chip[data-filter]');
    const rows   = document.querySelectorAll('.brow');
    const noRes  = document.getElementById('no-result');

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            const f = chip.dataset.filter;
            let n   = 0;
            rows.forEach(row => {
                const show = f === 'all' || row.dataset.status === f;
                row.style.display = show ? '' : 'none';
                if(show) n++;
            });
            if(noRes) noRes.style.display = n === 0 ? 'block' : 'none';
        });
    });
})();
</script>

</body>
</html>