<?php
/**
 * export-laporan.php — PixelStation
 * Export laporan booking ke .xlsx menggunakan PhpSpreadsheet.
 *
 * Install (jalankan sekali di root project):
 *   composer require phpoffice/phpspreadsheet
 */

include "../config/session.php";
include "../config/koneksi.php";

if (!isAdmin()) {
    header("Location: ../user/dashboard-user.php");
    exit;
}

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill, Font, Color};
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// ── PARAMETER FILTER ──────────────────────────────────────────────────────────
$filter     = $_GET['filter']     ?? 'hari';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date   = $_GET['end_date']   ?? date('Y-m-d');

switch ($filter) {
    case 'hari':
        $date_condition = "DATE(tanggal) = CURDATE()";
        $period_label   = "Hari Ini — " . date('d F Y');
        $filename       = "Laporan_Harian_" . date('Y-m-d');
        break;
    case 'minggu':
        $date_condition = "YEARWEEK(tanggal) = YEARWEEK(CURDATE())";
        $period_label   = "Minggu Ini";
        $filename       = "Laporan_Mingguan_" . date('Y-\WW');
        break;
    case 'bulan':
        $date_condition = "MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
        $period_label   = date('F Y');
        $filename       = "Laporan_Bulanan_" . date('Y-m');
        break;
    case 'tahun':
        $date_condition = "YEAR(tanggal) = YEAR(CURDATE())";
        $period_label   = "Tahun " . date('Y');
        $filename       = "Laporan_Tahunan_" . date('Y');
        break;
    case 'custom':
        $sd = mysqli_real_escape_string($conn, $start_date);
        $ed = mysqli_real_escape_string($conn, $end_date);
        $date_condition = "tanggal BETWEEN '$sd' AND '$ed'";
        $period_label   = date('d M Y', strtotime($start_date)) . " s/d " . date('d M Y', strtotime($end_date));
        $filename       = "Laporan_" . $start_date . "_sd_" . $end_date;
        break;
    default:
        $date_condition = "DATE(tanggal) = CURDATE()";
        $period_label   = "Hari Ini";
        $filename       = "Laporan_" . date('Y-m-d');
}

// ── AMBIL DATA ────────────────────────────────────────────────────────────────
$result = mysqli_query($conn, "
    SELECT bookings.*, users.nama AS user_nama, playstations.nama_ps
    FROM bookings
    JOIN users        ON bookings.user_id = users.id
    JOIN playstations ON bookings.ps_id   = playstations.id
    WHERE $date_condition
    ORDER BY bookings.tanggal DESC, bookings.jam_mulai ASC
");

$rows              = [];
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

// ── PALETTE ───────────────────────────────────────────────────────────────────
// Warna utama PixelStation yang bekerja baik di background terang Excel
$C_BRAND_DARK  = '1A1040';   // navy-indigo gelap → header utama
$C_BRAND_MID   = '2E1F6B';   // indigo medium → sub-header
$C_ACCENT_PINK = 'FF3E80';   // pink brand
$C_ACCENT_GOLD = 'D4A017';   // gold → angka pendapatan (lebih legible dari FFD166)
$C_ROW_ALT     = 'F5F0FF';   // ungu sangat pucat → baris genap
$C_ROW_EVEN    = 'FFFFFF';   // putih → baris ganjil
$C_FOOTER_BG   = 'EDE8FF';   // lavender muda → baris total
$C_BORDER      = 'C8BBEE';   // border halus ungu
$C_BORDER_HDR  = '4A3080';   // border header lebih tegas
$C_WHITE       = 'FFFFFF';
$C_DARK_TEXT   = '1A1040';
$C_MUTED       = '6B5EA8';

// ── SPREADSHEET ───────────────────────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Booking');

// ── COLUMN WIDTH ──────────────────────────────────────────────────────────────
$colWidths = ['A'=>6,'B'=>14,'C'=>12,'D'=>26,'E'=>24,'F'=>12,'G'=>20,'H'=>14];
foreach ($colWidths as $col => $w) {
    $sheet->getColumnDimension($col)->setWidth($w);
}

// ── LOGO / JUDUL PERUSAHAAN (baris 1) ────────────────────────────────────────
$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', 'PIXELSTATION — Rental PlayStation');
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold'=>true, 'size'=>16, 'color'=>['rgb'=>$C_WHITE], 'name'=>'Arial'],
    'fill'      => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>$C_BRAND_DARK]],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER, 'vertical'=>Alignment::VERTICAL_CENTER],
]);
$sheet->getRowDimension(1)->setRowHeight(36);

// ── JUDUL LAPORAN (baris 2) ───────────────────────────────────────────────────
$sheet->mergeCells('A2:H2');
$sheet->setCellValue('A2', 'LAPORAN DATA BOOKING');
$sheet->getStyle('A2')->applyFromArray([
    'font'      => ['bold'=>true, 'size'=>13, 'color'=>['rgb'=>$C_WHITE], 'name'=>'Arial'],
    'fill'      => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>$C_BRAND_MID]],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER, 'vertical'=>Alignment::VERTICAL_CENTER],
]);
$sheet->getRowDimension(2)->setRowHeight(28);

// ── SUB-INFO (baris 3 & 4) ────────────────────────────────────────────────────
$sheet->mergeCells('A3:H3');
$sheet->setCellValue('A3', 'Periode: ' . $period_label);
$sheet->getStyle('A3')->applyFromArray([
    'font'      => ['italic'=>true, 'size'=>10, 'color'=>['rgb'=>$C_WHITE], 'name'=>'Arial'],
    'fill'      => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>$C_BRAND_MID]],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER],
]);
$sheet->getRowDimension(3)->setRowHeight(20);

$sheet->mergeCells('A4:H4');
$sheet->setCellValue('A4', 'Dicetak: ' . date('d F Y, H:i') . ' WIB  |  Admin: ' . $_SESSION['nama']);
$sheet->getStyle('A4')->applyFromArray([
    'font'      => ['italic'=>true, 'size'=>9, 'color'=>['rgb'=>$C_WHITE], 'name'=>'Arial'],
    'fill'      => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>$C_BRAND_MID]],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER],
]);
$sheet->getRowDimension(4)->setRowHeight(18);

// ── RINGKASAN STATISTIK (baris 5-7) ──────────────────────────────────────────
$sheet->getRowDimension(5)->setRowHeight(6); // spasi

// Row 6 — label ringkasan
$summaryLabels = [
    'A6' => 'Total Booking',
    'B6' => 'Terkonfirmasi',
    'C6' => 'Selesai',
    'D6' => 'Pending',
    'E6' => 'Dibatalkan',
    'F6' => 'Total Durasi',
    'G6' => 'Total Pendapatan',
    'H6' => '',
];
foreach ($summaryLabels as $cell => $label) {
    $sheet->setCellValue($cell, $label);
}
$sheet->getStyle('A6:H6')->applyFromArray([
    'font'      => ['bold'=>true, 'size'=>8, 'color'=>['rgb'=>$C_MUTED], 'name'=>'Arial'],
    'fill'      => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>'F0EBFF']],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER],
]);
$sheet->getRowDimension(6)->setRowHeight(18);

// Row 7 — nilai ringkasan
$summaryVals = [
    'A7' => count($rows),
    'B7' => $status_count['confirmed'],
    'C7' => $status_count['selesai'],
    'D7' => $status_count['pending'],
    'E7' => $status_count['batal'],
    'F7' => $total_durasi . ' jam',
    'G7' => $total_pendapatan,
    'H7' => '',
];
foreach ($summaryVals as $cell => $val) {
    $sheet->setCellValue($cell, $val);
}
$sheet->getStyle('A7:H7')->applyFromArray([
    'font'      => ['bold'=>true, 'size'=>11, 'color'=>['rgb'=>$C_BRAND_DARK], 'name'=>'Arial'],
    'fill'      => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>'F0EBFF']],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER, 'vertical'=>Alignment::VERTICAL_CENTER],
]);
// Warna aksen untuk angka pendapatan
$sheet->getStyle('G7')->getFont()->getColor()->setRGB($C_ACCENT_GOLD);
// Format rupiah pada G7
$sheet->getStyle('G7')->getNumberFormat()->setFormatCode('"Rp "#,##0');
$sheet->getRowDimension(7)->setRowHeight(26);

// Border ringkasan
$sheet->getStyle('A6:G7')->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle'=>Border::BORDER_THIN, 'color'=>['rgb'=>$C_BORDER]],
        'outline'    => ['borderStyle'=>Border::BORDER_MEDIUM, 'color'=>['rgb'=>$C_BORDER_HDR]],
    ],
]);

$sheet->getRowDimension(8)->setRowHeight(8); // spasi

// ── HEADER KOLOM (baris 9) ────────────────────────────────────────────────────
$headers = ['No', 'Tanggal', 'Jam Mulai', 'Nama Pelanggan', 'Unit PlayStation', 'Durasi', 'Total Harga', 'Status'];
$cols    = ['A','B','C','D','E','F','G','H'];

foreach ($headers as $i => $h) {
    $sheet->setCellValue($cols[$i] . '9', $h);
}
$sheet->getStyle('A9:H9')->applyFromArray([
    'font'      => ['bold'=>true, 'size'=>10, 'color'=>['rgb'=>$C_WHITE], 'name'=>'Arial'],
    'fill'      => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>$C_ACCENT_PINK]],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER, 'vertical'=>Alignment::VERTICAL_CENTER, 'wrapText'=>true],
    'borders'   => [
        'allBorders' => ['borderStyle'=>Border::BORDER_THIN, 'color'=>['rgb'=>'CC2060']],
    ],
]);
$sheet->getRowDimension(9)->setRowHeight(24);

// ── DATA ROWS (mulai baris 10) ────────────────────────────────────────────────
$startRow = 10;
$no       = 1;

foreach ($rows as $i => $row) {
    $r        = $startRow + $i;
    $isEven   = ($i % 2 === 1);
    $fillRgb  = $isEven ? $C_ROW_ALT : $C_ROW_EVEN;

    $sheet->setCellValue("A{$r}", $no++);
    $sheet->setCellValue("B{$r}", date('d/m/Y', strtotime($row['tanggal'])));
    $sheet->setCellValue("C{$r}", $row['jam_mulai']);
    $sheet->setCellValue("D{$r}", $row['user_nama']);
    $sheet->setCellValue("E{$r}", $row['nama_ps']);
    $sheet->setCellValue("F{$r}", ($row['durasi'] ?? 0) . ' jam');
    $sheet->setCellValue("G{$r}", (float)$row['total_harga']);
    $sheet->setCellValue("H{$r}", strtoupper($row['status']));

    // Style seluruh baris
    $sheet->getStyle("A{$r}:H{$r}")->applyFromArray([
        'font'      => ['size'=>9, 'name'=>'Arial', 'color'=>['rgb'=>$C_DARK_TEXT]],
        'fill'      => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>$fillRgb]],
        'alignment' => ['vertical'=>Alignment::VERTICAL_CENTER],
        'borders'   => [
            'allBorders' => ['borderStyle'=>Border::BORDER_THIN, 'color'=>['rgb'=>$C_BORDER]],
        ],
    ]);

    // Alignment spesifik
    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Format rupiah kolom G
    $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle("G{$r}")->getFont()->setBold(true);

    // Warna badge status pada kolom H
    $statusColors = [
        'CONFIRMED' => ['font'=>'007A4B', 'fill'=>'D4F5E3'],
        'SELESAI'   => ['font'=>'007A4B', 'fill'=>'D4F5E3'],
        'PENDING'   => ['font'=>'7A5300', 'fill'=>'FFF3CC'],
        'BATAL'     => ['font'=>'CC1A50', 'fill'=>'FFE0EA'],
    ];
    $statusKey = strtoupper($row['status']);
    if (isset($statusColors[$statusKey])) {
        $sheet->getStyle("H{$r}")->applyFromArray([
            'font' => ['bold'=>true, 'color'=>['rgb'=>$statusColors[$statusKey]['font']]],
            'fill' => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>$statusColors[$statusKey]['fill']]],
        ]);
    }

    $sheet->getRowDimension($r)->setRowHeight(20);
}

// ── FOOTER — BARIS TOTAL ──────────────────────────────────────────────────────
$lastDataRow  = $startRow + count($rows) - 1;
$footerRow    = $lastDataRow + 1;

$sheet->mergeCells("A{$footerRow}:E{$footerRow}");
$sheet->setCellValue("A{$footerRow}", 'TOTAL KESELURUHAN');
$sheet->setCellValue("F{$footerRow}", $total_durasi . ' jam');
$sheet->setCellValue("G{$footerRow}", $total_pendapatan);
$sheet->mergeCells("H{$footerRow}:H{$footerRow}");
$sheet->setCellValue("H{$footerRow}", count($rows) . ' Transaksi');

$sheet->getStyle("A{$footerRow}:H{$footerRow}")->applyFromArray([
    'font'      => ['bold'=>true, 'size'=>10, 'name'=>'Arial', 'color'=>['rgb'=>$C_BRAND_DARK]],
    'fill'      => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>$C_FOOTER_BG]],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER, 'vertical'=>Alignment::VERTICAL_CENTER],
    'borders'   => [
        'allBorders' => ['borderStyle'=>Border::BORDER_THIN, 'color'=>['rgb'=>$C_BORDER]],
        'outline'    => ['borderStyle'=>Border::BORDER_MEDIUM, 'color'=>['rgb'=>$C_BRAND_MID]],
    ],
]);
$sheet->getStyle("G{$footerRow}")->applyFromArray([
    'font' => ['bold'=>true, 'size'=>11, 'color'=>['rgb'=>$C_ACCENT_GOLD]],
]);
$sheet->getStyle("G{$footerRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
$sheet->getRowDimension($footerRow)->setRowHeight(24);

// ── CATATAN KAKI (2 baris di bawah footer) ───────────────────────────────────
$noteRow = $footerRow + 2;
$sheet->mergeCells("A{$noteRow}:H{$noteRow}");
$sheet->setCellValue("A{$noteRow}",
    '* Pendapatan dihitung dari transaksi berstatus "Confirmed" dan "Selesai". Dokumen ini digenerate otomatis oleh sistem PixelStation.'
);
$sheet->getStyle("A{$noteRow}")->applyFromArray([
    'font'      => ['italic'=>true, 'size'=>8, 'color'=>['rgb'=>$C_MUTED], 'name'=>'Arial'],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_LEFT],
]);

// ── FREEZE PANE & PRINT SETUP ─────────────────────────────────────────────────
$sheet->freezePane('A10');

$sheet->getPageSetup()
    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
    ->setFitToPage(true)
    ->setFitToWidth(1)
    ->setFitToHeight(0);

$sheet->getHeaderFooter()
    ->setOddHeader('&C&B&14 PixelStation — Laporan Booking')
    ->setOddFooter('&L' . $period_label . '&R Halaman &P dari &N');

$sheet->getPageMargins()
    ->setTop(0.75)->setBottom(0.75)
    ->setLeft(0.7)->setRight(0.7);

// ── OUTPUT ────────────────────────────────────────────────────────────────────
$writer = new Xlsx($spreadsheet);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;