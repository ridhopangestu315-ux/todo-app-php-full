<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt_user = mysqli_prepare($conn, "SELECT nama, email FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$user_data  = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));
$nama_user  = $user_data['nama']  ?? 'Mahasiswa';
$email_user = $user_data['email'] ?? '';

function punyaKolom2($conn, $table, $column) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, "ss", $table, $column);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return (int)($row['cnt'] ?? 0) > 0;
}

$kolom_t = punyaKolom2($conn, 'tasks',     'dibuat_pada') ? 'dibuat_pada' : 'created_at';
$kolom_s = punyaKolom2($conn, 'schedules', 'dibuat_pada') ? 'dibuat_pada' : 'created_at';

$stmt = mysqli_prepare($conn, "SELECT nama_tugas, mata_kuliah, deadline, IF(sudah_selesai=1,'Selesai','Belum Selesai') AS status FROM tasks WHERE user_id = ? ORDER BY deadline ASC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$tasks = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

$stmt2 = mysqli_prepare($conn, "SELECT nama_jadwal, tanggal, jam, kategori FROM schedules WHERE user_id = ? ORDER BY tanggal ASC, jam ASC");
mysqli_stmt_bind_param($stmt2, "i", $user_id);
mysqli_stmt_execute($stmt2);
$schedules = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);

$total_tugas   = count($tasks);
$tugas_selesai = 0;
foreach ($tasks as $t) { if ($t['status'] === 'Selesai') $tugas_selesai++; }
$tugas_pending = $total_tugas - $tugas_selesai;
$total_jadwal  = count($schedules);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function fmtTgl($tgl) {
    if (!$tgl) return '-';
    $ts = strtotime($tgl);
    if (!$ts) return h($tgl);
    $bln = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    return date('j', $ts) . ' ' . $bln[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function fmtJam($j) { return $j ? substr($j, 0, 5) : '-'; }

$tgl_cetak = fmtTgl(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan StudyFlow</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: system-ui, -apple-system, sans-serif;
  font-size: 14px;
  color: #1a1a1a;
  background: #f0f0f0;
  padding: 16px;
}

/* ── TOMBOL (layar saja) ── */
.aksi {
  display: flex;
  gap: 8px;
  justify-content: center;
  margin-bottom: 16px;
}
.aksi a, .aksi button {
  flex: 1;
  max-width: 180px;
  padding: 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
}
.btn-print  { background: #4f46e5; color: #fff; }
.btn-back   { background: #e5e7eb; color: #374151; }

/* ── HALAMAN ── */
.halaman {
  background: #fff;
  max-width: 480px;
  margin: 0 auto;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 6px rgba(0,0,0,.1);
}

/* ── HEADER ── */
.header {
  background: #4f46e5;
  color: #fff;
  padding: 18px 16px 14px;
}
.header-top {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
.logo { font-size: 22px; }
.app-nama { font-size: 18px; font-weight: 800; letter-spacing: -.3px; }
.app-sub  { font-size: 11px; opacity: .75; }
.header-info { font-size: 12px; opacity: .85; line-height: 1.6; }
.header-info strong { opacity: 1; font-size: 13px; }

/* ── RINGKASAN ── */
.ringkasan {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1px;
  background: #e5e7eb;
  border-bottom: 1px solid #e5e7eb;
}
.stat {
  background: #fff;
  padding: 12px 14px;
  text-align: center;
}
.stat .angka { font-size: 22px; font-weight: 800; }
.stat .ket    { font-size: 11px; color: #6b7280; margin-top: 2px; }
.s-total   .angka { color: #4f46e5; }
.s-selesai .angka { color: #16a34a; }
.s-pending .angka { color: #ca8a04; }
.s-jadwal  .angka { color: #0369a1; }

/* ── SECTION ── */
.seksi { padding: 14px 16px 4px; }
.seksi-judul {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: #6b7280;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.seksi-judul::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e5e7eb;
}

/* ── ITEM KARTU ── */
.item {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 10px 12px;
  margin-bottom: 8px;
}
.item-nama {
  font-size: 13px;
  font-weight: 600;
  color: #111;
  margin-bottom: 5px;
  word-break: break-word;
}
.item-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  font-size: 11px;
  color: #6b7280;
}
.tag {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  background: #f3f4f6;
  padding: 2px 7px;
  border-radius: 20px;
}
.tag-selesai { background: #dcfce7; color: #16a34a; font-weight: 600; }
.tag-pending { background: #fef9c3; color: #92400e; font-weight: 600; }
.tag-kategori { background: #ede9fe; color: #4338ca; }

.kosong {
  text-align: center;
  color: #9ca3af;
  font-style: italic;
  font-size: 12px;
  padding: 16px 0 8px;
}

/* ── FOOTER ── */
.footer {
  padding: 12px 16px;
  text-align: center;
  font-size: 11px;
  color: #9ca3af;
  border-top: 1px solid #f3f4f6;
  margin-top: 4px;
}

/* ── PRINT ── */
@media print {
  body        { background: #fff; padding: 0; font-size: 12px; }
  .aksi       { display: none !important; }
  .halaman    { max-width: 100%; box-shadow: none; border-radius: 0; }
  .item       { break-inside: avoid; }
  @page       { margin: 10mm 8mm; }
}
</style>
</head>
<body>

<div class="aksi">
  <a class="btn-back" href="dashboard.php">← Kembali</a>
  <button class="btn-print" onclick="window.print()">🖨 Simpan PDF</button>
</div>

<div class="halaman">

  <!-- HEADER -->
  <div class="header">
    <div class="header-top">
      <div class="logo">📚</div>
      <div>
        <div class="app-nama">StudyFlow</div>
        <div class="app-sub">Laporan Data Akademik</div>
      </div>
    </div>
    <div class="header-info">
      <strong><?= h($nama_user) ?></strong><br>
      <?= $email_user ? h($email_user) . '<br>' : '' ?>
      Dicetak <?= $tgl_cetak ?>
    </div>
  </div>

  <!-- RINGKASAN -->
  <div class="ringkasan">
    <div class="stat s-total">
      <div class="angka"><?= $total_tugas ?></div>
      <div class="ket">Total Tugas</div>
    </div>
    <div class="stat s-selesai">
      <div class="angka"><?= $tugas_selesai ?></div>
      <div class="ket">Selesai</div>
    </div>
    <div class="stat s-pending">
      <div class="angka"><?= $tugas_pending ?></div>
      <div class="ket">Belum Selesai</div>
    </div>
    <div class="stat s-jadwal">
      <div class="angka"><?= $total_jadwal ?></div>
      <div class="ket">Total Jadwal</div>
    </div>
  </div>

  <!-- TUGAS -->
  <div class="seksi">
    <div class="seksi-judul">📝 Tugas (<?= $total_tugas ?>)</div>
    <?php if (empty($tasks)): ?>
      <p class="kosong">Belum ada tugas</p>
    <?php else: foreach ($tasks as $t): ?>
      <div class="item">
        <div class="item-nama"><?= h($t['nama_tugas']) ?></div>
        <div class="item-meta">
          <?php if ($t['mata_kuliah']): ?>
            <span class="tag">📖 <?= h($t['mata_kuliah']) ?></span>
          <?php endif; ?>
          <?php if ($t['deadline']): ?>
            <span class="tag">📅 <?= fmtTgl($t['deadline']) ?></span>
          <?php endif; ?>
          <?php if ($t['status'] === 'Selesai'): ?>
            <span class="tag tag-selesai">✓ Selesai</span>
          <?php else: ?>
            <span class="tag tag-pending">⏳ Pending</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- JADWAL -->
  <div class="seksi">
    <div class="seksi-judul">📅 Jadwal (<?= $total_jadwal ?>)</div>
    <?php if (empty($schedules)): ?>
      <p class="kosong">Belum ada jadwal</p>
    <?php else: foreach ($schedules as $s): ?>
      <div class="item">
        <div class="item-nama"><?= h($s['nama_jadwal']) ?></div>
        <div class="item-meta">
          <span class="tag">📅 <?= fmtTgl($s['tanggal']) ?></span>
          <span class="tag">🕐 <?= h(fmtJam($s['jam'])) ?></span>
          <?php if ($s['kategori']): ?>
            <span class="tag tag-kategori"><?= h($s['kategori']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="footer">StudyFlow &mdash; <?= $tgl_cetak ?></div>

</div>
</body>
</html>
