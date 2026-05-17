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
        $period_label   = "Hari Ini, " . date('d F Y');
        $doc_title      = "Laporan Harian — " . date('d F Y');
        break;
    case 'minggu':
        $date_condition = "YEARWEEK(tanggal) = YEARWEEK(CURDATE())";
        $period_label   = "Minggu Ke-" . date('W') . " Tahun " . date('Y');
        $doc_title      = "Laporan Mingguan — Minggu " . date('W Y');
        break;
    case 'bulan':
        $date_condition = "MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
        $period_label   = date('F Y');
        $doc_title      = "Laporan Bulanan — " . date('F Y');
        break;
    case 'tahun':
        $date_condition = "YEAR(tanggal) = YEAR(CURDATE())";
        $period_label   = "Tahun " . date('Y');
        $doc_title      = "Laporan Tahunan — " . date('Y');
        break;
    case 'custom':
        $sd = mysqli_real_escape_string($conn, $start_date);
        $ed = mysqli_real_escape_string($conn, $end_date);
        $date_condition = "tanggal BETWEEN '$sd' AND '$ed'";
        $period_label   = date('d F Y', strtotime($start_date)) . " s/d " . date('d F Y', strtotime($end_date));
        $doc_title      = "Laporan Periode " . $start_date . " sd " . $end_date;
        break;
    default:
        $date_condition = "DATE(tanggal) = CURDATE()";
        $period_label   = "Hari Ini, " . date('d F Y');
        $doc_title      = "Laporan Harian";
}

$result = mysqli_query($conn, "
    SELECT bookings.*, users.nama AS user_nama, playstations.nama_ps
    FROM bookings
    JOIN users        ON bookings.user_id = users.id
    JOIN playstations ON bookings.ps_id   = playstations.id
    WHERE $date_condition
    ORDER BY bookings.tanggal DESC, bookings.jam_mulai ASC
");

$rows             = [];
$total_pendapatan = 0;
$total_durasi     = 0;
$status_count     = ['confirmed'=>0,'selesai'=>0,'pending'=>0,'batal'=>0];

while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    if (in_array($row['status'], ['confirmed','selesai'])) {
        $total_pendapatan += $row['total_harga'];
    }
    $total_durasi += $row['durasi'] ?? 0;
    if (isset($status_count[$row['status']])) $status_count[$row['status']]++;
}

$doc_no = 'PSN-' . date('Ymd') . '-' . strtoupper(substr($filter, 0, 3));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($doc_title); ?> — PixelStation</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ─── RESET ─── */
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        :root {
            --brand:     #1A1040;
            --brand-mid: #2E1F6B;
            --accent:    #FF3E80;
            --gold:      #B8750A;
            --green:     #006B40;
            --red:       #CC1A50;
            --amber:     #7A5300;
            --text:      #1A1040;
            --muted:     #6B6A7A;
            --border:    #D8D4EE;
            --row-alt:   #F7F5FF;
            --bg:        #EEEAF8;
        }

        body {
            font-family:'Inter', Arial, sans-serif;
            font-size:10pt;
            color:var(--text);
            background:var(--bg);
            line-height:1.5;
        }

        /* ─── SCREEN TOOLBAR ─── */
        .print-toolbar {
            max-width:820px; margin:20px auto 14px;
            display:flex; justify-content:flex-end; gap:10px;
            padding:0 6px;
        }
        .tb-btn {
            font-family:'Inter',sans-serif; font-weight:600; font-size:.78rem;
            padding:9px 20px; border-radius:8px; cursor:pointer;
            display:inline-flex; align-items:center; gap:7px;
            text-decoration:none; border:none; transition:.2s; letter-spacing:.02em;
        }
        .tb-print {
            background:var(--brand); color:#fff;
            box-shadow:0 4px 18px rgba(26,16,64,.28);
        }
        .tb-print:hover { background:var(--brand-mid); transform:translateY(-1px); color:#fff; }
        .tb-back {
            background:#fff; color:var(--muted); border:1px solid var(--border);
            text-decoration:none;
        }
        .tb-back:hover { border-color:var(--brand); color:var(--brand); text-decoration:none; }

        /* ─── PAGE WRAPPER ─── */
        .page-wrapper {
            max-width:820px;
            margin:0 auto 32px;
            background:#fff;
            box-shadow:0 6px 48px rgba(26,16,64,.16);
            border-radius:10px;
            overflow:hidden;
        }

        /* ─── LETTERHEAD ─── */
        .letterhead {
            background:var(--brand);
            padding:26px 36px 20px;
            display:flex; justify-content:space-between; align-items:center; gap:20px;
        }
        .lh-left { display:flex; align-items:center; gap:14px; }
        .lh-icon {
            width:48px; height:48px; border-radius:12px;
            background:linear-gradient(135deg, #FF3E80, #FFD166);
            display:flex; align-items:center; justify-content:center;
            font-size:1.5rem; flex-shrink:0;
        }
        .lh-name {
            font-size:1.35rem; font-weight:700; color:#fff;
            letter-spacing:.07em; text-transform:uppercase; line-height:1.1;
        }
        .lh-tagline {
            font-size:.65rem; color:rgba(255,255,255,.45);
            letter-spacing:.14em; text-transform:uppercase;
        }
        .lh-right {
            text-align:right; font-size:.70rem;
            color:rgba(255,255,255,.50); line-height:1.9;
        }
        .lh-right strong { color:rgba(255,255,255,.80); font-weight:500; }

        /* ─── ACCENT BAR ─── */
        .accent-bar {
            height:4px;
            background:linear-gradient(90deg, #FF3E80 0%, #FFD166 50%, #FF3E80 100%);
        }

        /* ─── DOC META ─── */
        .doc-meta-bar {
            display:flex; justify-content:space-between; align-items:flex-start;
            padding:20px 36px 16px;
            border-bottom:1px solid var(--border);
            gap:24px;
        }
        .dmt-left .dmt-type {
            font-size:.62rem; font-weight:700; letter-spacing:.20em;
            text-transform:uppercase; color:var(--muted); margin-bottom:5px;
        }
        .dmt-left h1 {
            font-size:1.22rem; font-weight:700; color:var(--brand);
            line-height:1.2; margin-bottom:4px;
        }
        .dmt-left .dmt-period {
            font-size:.80rem; font-weight:600; color:var(--accent);
        }
        .dmt-right {
            text-align:right; font-size:.72rem; color:var(--muted);
            line-height:1.85; flex-shrink:0;
        }
        .doc-no-badge {
            font-family:'Courier New',monospace; font-size:.68rem; font-weight:700;
            background:#EDE8FF; color:var(--brand);
            padding:3px 10px; border-radius:5px; border:1px solid #C8BBEE;
            display:inline-block; margin-bottom:7px;
        }

        /* ─── SUMMARY STRIP ─── */
        .summary-strip {
            display:grid; grid-template-columns:repeat(4,1fr);
            border-bottom:2px solid var(--border);
        }
        .ss-cell {
            padding:13px 16px; text-align:center;
            border-right:1px solid var(--border); background:#FAFAFE;
        }
        .ss-cell:last-child { border-right:none; }
        .ss-lbl {
            font-size:.60rem; font-weight:700; letter-spacing:.14em;
            text-transform:uppercase; color:var(--muted); margin-bottom:4px;
        }
        .ss-val {
            font-size:1.05rem; font-weight:700; color:var(--brand); line-height:1;
        }
        .ss-val.is-green { color:var(--green); }
        .ss-val.is-gold  { color:var(--gold);  font-size:.92rem; }
        .ss-val.is-amber { color:var(--amber); }

        /* ─── TABLE SECTION ─── */
        .tbl-section-label {
            padding:9px 36px 7px;
            font-size:.62rem; font-weight:700; letter-spacing:.16em;
            text-transform:uppercase; color:var(--muted);
            border-bottom:1px solid var(--border);
            background:#FAFAFE;
        }

        table.rtable {
            width:100%; border-collapse:collapse; font-size:.82rem;
        }
        table.rtable thead th {
            background:var(--brand); color:#fff;
            font-size:.65rem; font-weight:600; letter-spacing:.09em; text-transform:uppercase;
            padding:10px 11px; text-align:left; white-space:nowrap;
        }
        table.rtable thead th:first-child { padding-left:36px; }
        table.rtable thead th:last-child  { padding-right:36px; }

        table.rtable tbody tr:nth-child(odd)  { background:#fff; }
        table.rtable tbody tr:nth-child(even) { background:var(--row-alt); }
        table.rtable tbody tr:hover           { background:#F0EAFF; }

        table.rtable tbody td {
            padding:9px 11px; border-bottom:1px solid #ECE8F8;
            vertical-align:middle;
        }
        table.rtable tbody td:first-child { padding-left:36px; }
        table.rtable tbody td:last-child  { padding-right:36px; }

        /* col helpers */
        .tc-no     { text-align:center; color:var(--muted); font-size:.76rem; width:38px; }
        .tc-center { text-align:center; }
        .tc-right  { text-align:right; }
        .tc-fw     { font-weight:600; }
        .tc-muted  { color:var(--muted); }

        /* status badge */
        .sbadge {
            display:inline-block; padding:2px 9px; border-radius:20px;
            font-size:.62rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
            border:1px solid transparent;
        }
        .sb-confirmed, .sb-selesai { background:#D4F5E3; color:var(--green); border-color:#90DDBE; }
        .sb-pending   { background:#FFF3CC; color:var(--amber); border-color:#FFDD77; }
        .sb-batal     { background:#FFE0EA; color:var(--red);   border-color:#FFB3C6; }

        /* no data */
        .no-data td { text-align:center; padding:44px 20px; color:var(--muted); font-style:italic; }

        /* ─── TFOOT TOTAL ─── */
        table.rtable tfoot tr td {
            padding:11px 11px; font-weight:700; font-size:.86rem;
            background:#EDE8FF; border-top:2px solid #B8A8E8;
            color:var(--brand);
        }
        table.rtable tfoot tr td:first-child { padding-left:36px; }
        table.rtable tfoot tr td:last-child  { padding-right:36px; }
        .tf-label { text-align:right; }
        .tf-dur   { text-align:center; }
        .tf-money { text-align:right; color:var(--gold); font-size:.92rem; }
        .tf-trx   { text-align:center; font-size:.74rem; color:var(--muted); font-weight:500; }

        /* ─── DOCUMENT FOOTER ─── */
        .doc-footer {
            padding:16px 36px 20px;
            border-top:1px solid var(--border);
            display:flex; justify-content:space-between; align-items:flex-end; gap:24px;
            background:#FAFAFE;
        }
        .df-notes {
            font-size:.68rem; color:var(--muted); line-height:1.8; max-width:56%;
        }
        .df-notes strong { color:var(--brand); font-weight:600; }
        .df-sign { text-align:center; font-size:.72rem; color:var(--muted); }
        .sign-line {
            width:130px; height:46px;
            border-bottom:1.5px solid var(--brand);
            margin:0 auto 5px;
        }
        .df-sign strong { display:block; color:var(--brand); font-weight:600; font-size:.74rem; }

        /* ─── PRINT STYLES ─── */
        @media print {
            body { background:#fff !important; font-size:9pt; }

            .print-toolbar { display:none !important; }

            .page-wrapper {
                max-width:100%; margin:0;
                box-shadow:none; border-radius:0;
            }

            /* tighten padding */
            .letterhead  { padding:16px 24px 14px; }
            .doc-meta-bar{ padding:14px 24px 12px; }
            .tbl-section-label { padding:7px 24px 5px; }
            table.rtable thead th:first-child,
            table.rtable tbody td:first-child,
            table.rtable tfoot tr td:first-child { padding-left:24px; }
            table.rtable thead th:last-child,
            table.rtable tbody td:last-child,
            table.rtable tfoot tr td:last-child  { padding-right:24px; }
            .doc-footer { padding:12px 24px 16px; }
            .ss-cell { padding:10px 12px; }

            /* page setup */
            @page {
                size:A4 portrait;
                margin:10mm 12mm 14mm;
            }

            /* avoid breaks */
            .summary-strip,
            .doc-footer { page-break-inside:avoid; }
            table.rtable tbody tr { page-break-inside:avoid; }

            /* repeat thead on new page */
            table.rtable thead { display:table-header-group; }
            table.rtable tfoot { display:table-footer-group; }
        }
    </style>
</head>
<body>

<!-- TOOLBAR (screen only) -->
<div class="print-toolbar">
    <a href="laporan.php?filter=<?= urlencode($filter); ?>&start_date=<?= urlencode($start_date); ?>&end_date=<?= urlencode($end_date); ?>"
       class="tb-btn tb-back">
        ← Kembali
    </a>
    <button onclick="window.print()" class="tb-btn tb-print">
        🖨️&nbsp; Cetak / Simpan PDF
    </button>
</div>

<!-- DOCUMENT -->
<div class="page-wrapper">

    <!-- LETTERHEAD -->
    <div class="letterhead">
        <div class="lh-left">
            <div class="lh-icon">🎮</div>
            <div>
                <div class="lh-name">PixelStation</div>
                <div class="lh-tagline">Rental PlayStation Premium</div>
            </div>
        </div>
        <div class="lh-right">
            <strong>PixelStation</strong><br>
            Cirebon, Jawa Barat<br>
            0858-7143-5748<br>
            pixelstation@email.com
        </div>
    </div>

    <div class="accent-bar"></div>

    <!-- DOC META -->
    <div class="doc-meta-bar">
        <div class="dmt-left">
            <div class="dmt-type">Dokumen Resmi — Laporan Internal</div>
            <h1>Laporan Data Booking</h1>
            <div class="dmt-period">Periode: <?= htmlspecialchars($period_label); ?></div>
        </div>
        <div class="dmt-right">
            <div class="doc-no-badge"><?= $doc_no; ?></div>
            <div>Dicetak: <?= date('d/m/Y H:i'); ?> WIB</div>
            <div>Admin: <strong style="color:var(--brand);"><?= htmlspecialchars($_SESSION['nama']); ?></strong></div>
        </div>
    </div>

    <!-- SUMMARY STRIP -->
    <div class="summary-strip">
        <div class="ss-cell">
            <div class="ss-lbl">Total Booking</div>
            <div class="ss-val"><?= count($rows); ?></div>
        </div>
        <div class="ss-cell">
            <div class="ss-lbl">Terkonfirmasi</div>
            <div class="ss-val is-green"><?= $status_count['confirmed'] + $status_count['selesai']; ?></div>
        </div>
        <div class="ss-cell">
            <div class="ss-lbl">Total Durasi</div>
            <div class="ss-val"><?= $total_durasi; ?> <span style="font-size:.70rem;font-weight:500;color:var(--muted);">jam</span></div>
        </div>
        <div class="ss-cell">
            <div class="ss-lbl">Pendapatan</div>
            <div class="ss-val is-gold">Rp <?= number_format($total_pendapatan); ?></div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="tbl-section-label">Rincian Transaksi</div>
    <table class="rtable">
        <thead>
            <tr>
                <th class="tc-no">No</th>
                <th>Tanggal</th>
                <th class="tc-center">Jam</th>
                <th>Pelanggan</th>
                <th>Unit PlayStation</th>
                <th class="tc-center">Durasi</th>
                <th class="tc-right">Total Harga</th>
                <th class="tc-center">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr class="no-data">
                <td colspan="8">Tidak ada data booking pada periode yang dipilih.</td>
            </tr>
        <?php else: ?>
            <?php $no = 1; foreach ($rows as $row):
                $sc = match($row['status']) {
                    'confirmed' => 'sb-confirmed',
                    'selesai'   => 'sb-selesai',
                    'pending'   => 'sb-pending',
                    'batal'     => 'sb-batal',
                    default     => ''
                };
            ?>
            <tr>
                <td class="tc-no"><?= $no++; ?></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                <td class="tc-center tc-muted"><?= htmlspecialchars($row['jam_mulai']); ?></td>
                <td class="tc-fw"><?= htmlspecialchars($row['user_nama']); ?></td>
                <td><?= htmlspecialchars($row['nama_ps']); ?></td>
                <td class="tc-center"><?= $row['durasi']; ?> jam</td>
                <td class="tc-right tc-fw">Rp <?= number_format($row['total_harga']); ?></td>
                <td class="tc-center">
                    <span class="sbadge <?= $sc; ?>"><?= ucfirst($row['status']); ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        <?php if (!empty($rows)): ?>
        <tfoot>
            <tr>
                <td colspan="5" class="tf-label">Total Keseluruhan</td>
                <td class="tf-dur"><?= $total_durasi; ?> jam</td>
                <td class="tf-money">Rp <?= number_format($total_pendapatan); ?></td>
                <td class="tf-trx"><?= count($rows); ?> trx</td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <!-- DOC FOOTER -->
    <div class="doc-footer">
        <div class="df-notes">
            <strong>Catatan:</strong><br>
            * Pendapatan dihitung dari transaksi berstatus <em>Confirmed</em> dan <em>Selesai</em>.<br>
            * Dokumen ini digenerate otomatis oleh sistem PixelStation dan sah tanpa tanda tangan basah.<br>
            * Nomor dokumen: <strong><?= $doc_no; ?></strong>
        </div>
        <div class="df-sign">
            <div class="sign-line"></div>
            <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong>
            Administrator
        </div>
    </div>

</div><!-- /page-wrapper -->

<script>
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.addEventListener('load', () => window.print());
    }
</script>

</body>
</html>