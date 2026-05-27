<?php
// Suppress PHP warnings/notices dari tampilan
error_reporting(E_ERROR);
@ini_set('display_errors', '0');

date_default_timezone_set('Asia/Jakarta');
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Flash message: login berhasil
$flash_login = '';
if (isset($_SESSION['flash_login']) && $_SESSION['flash_login'] === 'berhasil') {
    $flash_login = 'berhasil';
    unset($_SESSION['flash_login']);
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function ambilSatu($conn, $sql, $types = '', $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return null;
    }
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function ambilSemua($conn, $sql, $types = '', $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows ?: [];
}

function hitungData($conn, $sql, $user_id) {
    $row = ambilSatu($conn, $sql, 'i', [$user_id]);
    return (int)($row['cnt'] ?? 0);
}

function punyaKolom($conn, $table, $column) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "ss", $table, $column);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $exists = (int)($row['cnt'] ?? 0) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function pastikanTabelCourses($conn) {
    mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS courses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            nama_mata_kuliah VARCHAR(100) NOT NULL,
            dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_course (user_id, nama_mata_kuliah),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    if (!punyaKolom($conn, 'courses', 'nama_mata_kuliah')) {
        mysqli_query($conn, "ALTER TABLE courses ADD COLUMN nama_mata_kuliah VARCHAR(100) NULL AFTER user_id");
        if (punyaKolom($conn, 'courses', 'nama_matkul')) {
            mysqli_query($conn, "UPDATE courses SET nama_mata_kuliah = nama_matkul WHERE nama_mata_kuliah IS NULL OR nama_mata_kuliah = ''");
        }
    }

    if (!punyaKolom($conn, 'courses', 'dibuat_pada')) {
        mysqli_query($conn, "ALTER TABLE courses ADD COLUMN dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
        if (punyaKolom($conn, 'courses', 'created_at')) {
            mysqli_query($conn, "UPDATE courses SET dibuat_pada = created_at WHERE dibuat_pada IS NULL");
        }
    }
}

function tanggalKode($date = null) {
    return ($date ?: new DateTime())->format('Y-m-d');
}

function formatTanggalIndo($kodeTanggal) {
    if (!$kodeTanggal) {
        return '-';
    }
    $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggal = new DateTime($kodeTanggal);
    return $namaHari[(int)$tanggal->format('w')] . ', ' . $tanggal->format('j') . ' ' . $namaBulan[(int)$tanggal->format('n')] . ' ' . $tanggal->format('Y');
}

function formatBulanIndo(DateTime $tanggal) {
    $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $namaBulan[(int)$tanggal->format('n')] . ' ' . $tanggal->format('Y');
}

function teksDeadline($kodeTanggal, $selesai = false) {
    if ($selesai) {
        return 'Selesai';
    }
    if (!$kodeTanggal) {
        return 'Tanpa deadline';
    }
    $hariIni = new DateTime('today');
    $deadline = new DateTime($kodeTanggal);
    $selisih = (int)$hariIni->diff($deadline)->format('%r%a');
    if ($selisih < 0) return 'Terlambat';
    if ($selisih === 0) return 'Hari ini';
    if ($selisih === 1) return 'Besok';
    return $selisih . ' hari lagi';
}

function kotakKosong($pesan) {
    return '<div class="kotak-kosong">' . e($pesan) . '</div>';
}

function renderItemRingkasTugas($task) {
    $selesai = (int)$task['sudah_selesai'] === 1;
    $kelasStatus = $selesai ? 'label-sukses' : 'label-peringatan';
    $kelasSelesai = $selesai ? ' tugas-selesai' : '';
    return '
      <div class="item-kalender' . $kelasSelesai . '">
        <span class="label-status ' . $kelasStatus . '">' . e(teksDeadline($task['deadline'], $selesai)) . '</span>
        <div>
          <strong>' . e($task['nama_tugas']) . '</strong>
          <p>' . e($task['mata_kuliah'] ?: 'Tanpa mata kuliah') . ' - ' . e(formatTanggalIndo($task['deadline'])) . '</p>
        </div>
      </div>';
}

function renderDaftarRingkas($tasks, $emptyText) {
    if (!$tasks) {
        return kotakKosong($emptyText);
    }
    $html = '';
    foreach ($tasks as $task) {
        $html .= renderItemRingkasTugas($task);
    }
    return $html;
}

function renderTaskCard($task) {
    $selesai = (int)$task['sudah_selesai'] === 1;
    $kelas = $selesai ? ' tugas-selesai' : '';
    $status = $selesai ? 'selesai' : 'belum';
    $label = $selesai ? 'label-sukses' : 'label-peringatan';
    $checked = $selesai ? ' checked' : '';
    $search = strtolower(($task['nama_tugas'] ?? '') . ' ' . ($task['mata_kuliah'] ?? ''));
    return '
      <article class="item-tugas' . $kelas . '" data-id-tugas="' . (int)$task['id'] . '" data-status="' . e($status) . '" data-search="' . e($search) . '">
        <div class="bagian-utama-tugas">
          <input class="checkbox-tugas" type="checkbox"' . $checked . ' aria-label="Tandai selesai">
          <div class="konten-tugas">
            <p class="judul-tugas">' . e($task['nama_tugas']) . '</p>
            <div class="info-tugas">
              <span class="meta-tugas">' . e($task['mata_kuliah'] ?: 'Tanpa mata kuliah') . '</span>
              <span class="pemisah-meta" aria-hidden="true"></span>
              <span class="meta-tugas">' . e(formatTanggalIndo($task['deadline'])) . '</span>
            </div>
          </div>
        </div>
        <div class="aksi-tugas">
          <span class="label-status ' . $label . '">' . e(teksDeadline($task['deadline'], $selesai)) . '</span>
          <button class="tombol-kecil tombol-hapus" type="button" data-aksi="hapus-tugas">Hapus</button>
        </div>
      </article>';
}

function renderAgendaItem($item) {
    $kategori = preg_replace('/[^a-z0-9_-]/i', '', $item['kategori']);
    $isJadwal = $item['tipe'] === 'jadwal';
    $button = $isJadwal
        ? '<button class="tombol-hapus-jadwal" type="button" data-hapus-jadwal="' . (int)$item['id'] . '" aria-label="Hapus jadwal">x</button>'
        : '';
    return '
      <article class="item-agenda kategori-' . e($kategori) . '" data-tanggal="' . e($item['tanggal']) . '">
        <div class="isi-item-agenda">
          <strong>' . e($item['judul']) . '</strong>
          <p>' . e(formatTanggalIndo($item['tanggal'])) . ' - ' . e($item['keterangan']) . '</p>
        </div>
        ' . $button . '
      </article>';
}

pastikanTabelCourses($conn);

// Auto-create schedules table jika belum ada
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS schedules (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        nama_jadwal VARCHAR(255) NOT NULL,
        tanggal DATE,
        jam TIME,
        kategori VARCHAR(50) DEFAULT 'pribadi',
        dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_tanggal (tanggal)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$user_data = ambilSatu($conn, "SELECT nama, email, foto_profil FROM users WHERE id = ?", 'i', [$user_id]) ?: [];
$settings = ambilSatu($conn, "SELECT dark_mode, notifikasi FROM settings WHERE user_id = ?", 'i', [$user_id]);
if (!$settings) {
    $stmt = mysqli_prepare($conn, "INSERT INTO settings (user_id, dark_mode, notifikasi) VALUES (?, 0, 1)");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $settings = ['dark_mode' => 0, 'notifikasi' => 1];
}

$nama_user = $user_data['nama'] ?? ($_SESSION['nama'] ?? 'Mahasiswa');
$foto_profil = $user_data['foto_profil'] ?? '';
$inisial_user = strtoupper(substr(trim($nama_user), 0, 1) ?: 'M');

$kolomWaktuTugas = punyaKolom($conn, 'tasks', 'dibuat_pada') ? 'dibuat_pada' : 'created_at';
$kolomWaktuJadwal = punyaKolom($conn, 'schedules', 'dibuat_pada') ? 'dibuat_pada' : 'created_at';
$tasks = ambilSemua($conn, "SELECT id, nama_tugas, mata_kuliah, deadline, sudah_selesai, `$kolomWaktuTugas` AS dibuat_pada FROM tasks WHERE user_id = ? ORDER BY deadline ASC, `$kolomWaktuTugas` DESC", 'i', [$user_id]);
$schedules = ambilSemua($conn, "SELECT id, nama_jadwal, tanggal, jam, kategori, `$kolomWaktuJadwal` AS dibuat_pada FROM schedules WHERE user_id = ? ORDER BY tanggal ASC, jam ASC", 'i', [$user_id]);

$today = tanggalKode();
$tomorrowDate = new DateTime('tomorrow');
$tomorrow = tanggalKode($tomorrowDate);
$total_tugas = count($tasks);
$tugas_selesai = 0;
$tugas_hariini = 0;
$deadline_dekat = 0;
$tugas_hariini_semua = 0;
$tugas_hariini_selesai = 0;
$tugas_besok = [];
$tugas_hariini_list = [];
$tugas_selesai_list = [];
$tugas_terbaru = $tasks;
usort($tugas_terbaru, function ($a, $b) {
    return strcmp($b['dibuat_pada'], $a['dibuat_pada']);
});

foreach ($tasks as $task) {
    $selesai = (int)$task['sudah_selesai'] === 1;
    if ($selesai) $tugas_selesai++;
    if ($task['deadline'] === $today) {
        $tugas_hariini_semua++;
        if ($selesai) $tugas_hariini_selesai++;
        if (!$selesai) {
            $tugas_hariini++;
            $tugas_hariini_list[] = $task;
        }
    }
    if ($task['deadline'] === $tomorrow && !$selesai) {
        $tugas_besok[] = $task;
    }
    if ($selesai && count($tugas_selesai_list) < 4) {
        $tugas_selesai_list[] = $task;
    }
    if (!$selesai && $task['deadline']) {
        $diff = (int)(new DateTime('today'))->diff(new DateTime($task['deadline']))->format('%r%a');
        if ($diff >= 0 && $diff <= 2) $deadline_dekat++;
    }
}

$progress_hari_ini = $tugas_hariini_semua > 0 ? (int)round(($tugas_hariini_selesai / $tugas_hariini_semua) * 100) : 0;
$progress_total = $total_tugas > 0 ? (int)round(($tugas_selesai / $total_tugas) * 100) : 0;

$active_page = $_GET['halaman'] ?? 'dashboard';
if (!in_array($active_page, ['dashboard', 'tugas', 'kalender', 'pengaturan'], true)) {
    $active_page = 'dashboard';
}

$bulanParam = $_GET['bulan'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $bulanParam)) {
    $bulanParam = date('Y-m');
}
$bulanAktif = DateTime::createFromFormat('Y-m-d', $bulanParam . '-01') ?: new DateTime('first day of this month');
$bulanSebelumnya = (clone $bulanAktif)->modify('-1 month')->format('Y-m');
$bulanBerikutnya = (clone $bulanAktif)->modify('+1 month')->format('Y-m');
$kategoriFilter = $_GET['kategori'] ?? 'semua';
$kategoriValid = ['semua', 'kuliah', 'organisasi', 'ujian', 'pribadi', 'deadline'];
if (!in_array($kategoriFilter, $kategoriValid, true)) {
    $kategoriFilter = 'semua';
}

$itemsKalender = [];
foreach ($schedules as $schedule) {
    $kategori = $schedule['kategori'] ?: 'pribadi';
    $itemsKalender[] = [
        'id' => (int)$schedule['id'],
        'tipe' => 'jadwal',
        'tanggal' => $schedule['tanggal'],
        'jam' => substr((string)$schedule['jam'], 0, 5),
        'kategori' => $kategori,
        'judul' => $schedule['nama_jadwal'],
        'keterangan' => ucfirst($kategori) . ' - ' . substr((string)$schedule['jam'], 0, 5)
    ];
}
foreach ($tasks as $task) {
    if ((int)$task['sudah_selesai'] === 1 || !$task['deadline']) continue;
    $itemsKalender[] = [
        'id' => (int)$task['id'],
        'tipe' => 'deadline',
        'tanggal' => $task['deadline'],
        'jam' => 'DL',
        'kategori' => 'deadline',
        'judul' => $task['nama_tugas'],
        'keterangan' => ($task['mata_kuliah'] ?: 'Tugas') . ' - ' . teksDeadline($task['deadline'])
    ];
}
if ($kategoriFilter !== 'semua') {
    $itemsKalender = array_values(array_filter($itemsKalender, function ($item) use ($kategoriFilter) {
        return $item['kategori'] === $kategoriFilter;
    }));
}
usort($itemsKalender, function ($a, $b) {
    return strcmp($a['tanggal'] . $a['jam'], $b['tanggal'] . $b['jam']);
});

$itemsByDate = [];
foreach ($itemsKalender as $item) {
    $itemsByDate[$item['tanggal']][] = $item;
}

$agendaHariIni = $itemsByDate[$today] ?? [];
$reminderDeadline = array_values(array_filter($itemsKalender, function ($item) {
    if ($item['kategori'] !== 'deadline') return false;
    $diff = (int)(new DateTime('today'))->diff(new DateTime($item['tanggal']))->format('%r%a');
    return $diff >= 0 && $diff <= 2;
}));

$courses = ambilSemua($conn, "SELECT id, nama_mata_kuliah FROM courses WHERE user_id = ? ORDER BY nama_mata_kuliah ASC", 'i', [$user_id]);

function kalenderUrl($bulan, $kategori) {
    return 'dashboard.php?halaman=kalender&bulan=' . urlencode($bulan) . '&kategori=' . urlencode($kategori);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>StudyFlow - Dashboard Mahasiswa</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20260526-ssr-mobile">
  <link rel="icon" type="image/png" href="icon1.PNG">
</head>
<body class="<?= (int)$settings['dark_mode'] ? 'mode-gelap' : '' ?>" data-halaman-aktif="<?= e($active_page) ?>">
  <div class="latar-ambient" aria-hidden="true"></div>
  <div class="wadah-aplikasi">
    <aside class="menu-samping">
      <div class="identitas-aplikasi">
        <span class="ikon-aplikasi">SF</span>
        <div>
          <h2>StudyFlow</h2>
          <p>Student workspace</p>
        </div>
      </div>

      <nav class="daftar-menu">
        <button class="tombol-menu <?= $active_page === 'dashboard' ? 'aktif' : '' ?>" type="button" data-halaman="dashboard"><span class="ikon-menu">⌂</span><span>Dashboard</span></button>
        <button class="tombol-menu <?= $active_page === 'tugas' ? 'aktif' : '' ?>" type="button" data-halaman="tugas"><span class="ikon-menu">□</span><span>Tugas</span></button>
        <button class="tombol-menu <?= $active_page === 'kalender' ? 'aktif' : '' ?>" type="button" data-halaman="kalender"><span class="ikon-menu">◇</span><span>Kalender</span></button>
        <button class="tombol-menu <?= $active_page === 'pengaturan' ? 'aktif' : '' ?>" type="button" data-halaman="pengaturan"><span class="ikon-menu">⚙</span><span>Pengaturan</span></button>
      </nav>

      <button class="profil-sidebar" type="button" data-halaman="pengaturan" aria-label="Buka pengaturan profil">
        <span class="avatar-sidebar">
          <?php if ($foto_profil): ?><img src="<?= e($foto_profil) ?>" alt="Foto profil"><?php else: ?><?= e($inisial_user) ?><?php endif; ?>
        </span>
        <span class="info-profil-sidebar"><strong><?= e($nama_user) ?></strong><small>Profil mahasiswa</small></span>
      </button>

      <div class="sidebar-insight">
        <span class="label-mini">Hari ini</span>
        <strong id="ringkasanSidebar"><?= (int)$tugas_hariini ?> tugas aktif</strong>
        <p>Atur ritme belajarmu tanpa bikin dashboard terasa penuh.</p>
      </div>
    </aside>

    <main class="konten-utama">
      <header class="kepala-halaman">
        <div class="kepala-copy">
          <p class="teks-kecil">Catatan Tugas Mahasiswa</p>
          <h1 id="teksSapaan">Selamat datang kembali, <?= e($nama_user) ?></h1>
        </div>
        <div class="header-actions">
          <div class="tanggal-header"><span id="teksTanggalRealtime"></span><small id="teksJamRealtime"></small></div>
          <button id="tombolModeGelapHeader" class="tombol-mode-gelap" type="button" data-toggle-mode-gelap aria-label="Toggle dark mode" aria-pressed="<?= (int)$settings['dark_mode'] ? 'true' : 'false' ?>">
            <span class="ikon-mode-gelap" aria-hidden="true"><?= (int)$settings['dark_mode'] ? '☀' : '🌙' ?></span>
          </button>
          <div id="fotoProfilHeader" class="foto-profil-header foto-profil-header--klikable" tabindex="0" role="button" aria-label="Buka pengaturan">
            <?php if ($foto_profil): ?><img src="<?= e($foto_profil) ?>" alt="Foto profil"><?php else: ?><span id="inisialProfilHeader"><?= e($inisial_user) ?></span><?php endif; ?>
          </div>
        </div>
      </header>

      <section id="dashboard" class="halaman <?= $active_page === 'dashboard' ? 'halaman-aktif' : '' ?>">
        <section class="hero-dashboard">
          <div class="hero-copy">
            <span class="pill-status">Workspace aktif</span>
            <h2 id="teksSapaanHero">Selamat datang kembali, <?= e($nama_user) ?></h2>
            <p id="teksTanggalHero"><?= e(formatTanggalIndo($today)) ?></p>
            <div class="hero-progress">
              <div class="progress-meta"><span>Progress tugas hari ini</span><strong id="persenProgressHariIni"><?= (int)$progress_hari_ini ?>%</strong></div>
              <div class="progress-track" aria-label="Progress produktivitas"><span id="barProgressHariIni" class="progress-fill" style="width: <?= (int)$progress_hari_ini ?>%"></span></div>
              <small id="teksProgressHariIni"><?= $tugas_hariini_semua ? e($tugas_hariini_selesai . ' dari ' . $tugas_hariini_semua . ' tugas hari ini selesai.') : 'Belum ada deadline hari ini. Ruang fokus masih lega.' ?></small>
            </div>
          </div>
          <div class="hero-panel"><span class="label-mini">Fokus hari ini</span><strong id="angkaFokusHariIni"><?= (int)$tugas_hariini ?></strong><p>Tugas dengan deadline hari ini.</p></div>
        </section>

        <section class="section-dashboard">
          <div class="section-heading"><div><p class="teks-kecil">Aksi cepat</p><h2>Mulai dari sini</h2></div></div>
          <div class="grid-aksi-cepat">
            <button class="kartu-aksi" type="button" data-quick-action="tambah-tugas"><span class="ikon-aksi">＋</span><strong>Tambah Tugas</strong><small>Catat deadline baru</small></button>
            <button class="kartu-aksi" type="button" data-quick-action="tambah-jadwal"><span class="ikon-aksi">◇</span><strong>Tambah Jadwal</strong><small>Buat agenda kuliah</small></button>
            <button class="kartu-aksi" type="button" data-quick-action="lihat-kalender"><span class="ikon-aksi">▦</span><strong>Lihat Kalender</strong><small>Cek ritme bulan ini</small></button>
            <button class="kartu-aksi" type="button" data-quick-action="fokus-hari-ini"><span class="ikon-aksi">◎</span><strong>Fokus Hari Ini</strong><small>Kerjakan prioritas utama</small></button>
          </div>
        </section>

        <section class="section-dashboard">
          <div class="section-heading"><div><p class="teks-kecil">Statistik</p><h2>Snapshot produktivitas</h2></div></div>
          <div class="grid-statistik">
            <article class="kartu-statistik warna-biru"><span class="ikon-statistik">□</span><p>Total tugas</p><h3 id="angkaTotalTugas"><?= (int)$total_tugas ?></h3></article>
            <article class="kartu-statistik warna-merah"><span class="ikon-statistik">!</span><p>Deadline dekat</p><h3 id="angkaDeadlineDekat"><?= (int)$deadline_dekat ?></h3></article>
            <article class="kartu-statistik warna-hijau"><span class="ikon-statistik">✓</span><p>Tugas selesai</p><h3 id="angkaTugasSelesai"><?= (int)$tugas_selesai ?></h3></article>
            <article class="kartu-statistik warna-ungu"><span class="ikon-statistik">↗</span><p>Progress total</p><h3 id="angkaTugasBelumSelesai"><?= (int)$progress_total ?>%</h3></article>
          </div>
        </section>

        <section class="dashboard-grid-utama">
          <div class="kolom-dashboard">
            <section class="panel panel-tugas-dashboard"><div class="kepala-panel"><div><p class="teks-kecil">Tugas</p><h3>Deadline Hari Ini</h3></div><span class="badge-panel" id="jumlahTugasHariIni"><?= count($tugas_hariini_list) ?></span></div><div id="daftarTugasHariIni" class="daftar-ringkas"><?= renderDaftarRingkas(array_slice($tugas_hariini_list, 0, 4), 'Tidak ada deadline hari ini.') ?></div></section>
            <section class="panel panel-tugas-dashboard"><div class="kepala-panel"><div><p class="teks-kecil">Berikutnya</p><h3>Deadline Besok</h3></div><span class="badge-panel" id="jumlahTugasBesok"><?= count($tugas_besok) ?></span></div><div id="daftarTugasBesok" class="daftar-ringkas"><?= renderDaftarRingkas(array_slice($tugas_besok, 0, 4), 'Belum ada deadline besok.') ?></div></section>
            <section class="panel panel-tugas-dashboard"><div class="kepala-panel"><div><p class="teks-kecil">Selesai</p><h3>Tugas Selesai</h3></div><span class="badge-panel" id="jumlahTugasSelesaiDashboard"><?= count($tugas_selesai_list) ?></span></div><div id="daftarTugasSelesaiDashboard" class="daftar-ringkas"><?= renderDaftarRingkas($tugas_selesai_list, 'Belum ada tugas selesai.') ?></div></section>
            <section class="panel panel-tugas-dashboard"><div class="kepala-panel"><div><p class="teks-kecil">Terbaru</p><h3>Tugas Terbaru</h3></div></div><div id="daftarTugasTerbaru" class="daftar-ringkas"><?= renderDaftarRingkas(array_slice($tugas_terbaru, 0, 4), 'Belum ada tugas. Tambahkan tugas pertamamu.') ?></div></section>
          </div>

          <aside class="kolom-dashboard kolom-kanan">
            <section class="panel panel-kalender-preview">
              <div class="kepala-panel"><div><p class="teks-kecil">Kalender</p><h3>Preview bulan ini</h3></div><button class="tombol-kedua tombol-mini" type="button" data-quick-action="lihat-kalender">Buka</button></div>
              <div class="mini-calendar">
                <div id="judulMiniKalender" class="judul-mini-kalender"><?= e(formatBulanIndo(new DateTime('first day of this month'))) ?></div>
                <div class="mini-calendar-days" aria-hidden="true"><span>M</span><span>S</span><span>S</span><span>R</span><span>K</span><span>J</span><span>S</span></div>
                <div id="isiMiniKalender" class="mini-calendar-grid">
                  <?php
                    $miniStart = new DateTime('first day of this month');
                    $miniOffset = (int)$miniStart->format('w');
                    for ($i = 0; $i < $miniOffset; $i++) echo '<span></span>';
                    $miniDays = (int)$miniStart->format('t');
                    for ($d = 1; $d <= $miniDays; $d++) {
                        $kode = date('Y-m-') . str_pad((string)$d, 2, '0', STR_PAD_LEFT);
                        $class = $kode === $today ? 'mini-hari-ini' : 'mini-tanggal-aktif';
                        if (!empty($itemsByDate[$kode])) $class .= ' mini-ada-agenda';
                        echo '<span class="' . e($class) . '">' . $d . '</span>';
                    }
                  ?>
                </div>
              </div>
            </section>
            <section class="panel"><div class="kepala-panel"><div><p class="teks-kecil">Reminder</p><h3>Deadline dekat</h3></div></div><div id="daftarNotifikasiDeadline" class="daftar-ringkas"><?= (int)$settings['notifikasi'] ? renderDaftarRingkas(array_slice(array_filter($tasks, function ($task) { if ((int)$task['sudah_selesai'] === 1) return false; $diff = (int)(new DateTime('today'))->diff(new DateTime($task['deadline']))->format('%r%a'); return $diff >= 0 && $diff <= 2; }), 0, 4), 'Belum ada deadline dekat.') : kotakKosong('Notifikasi deadline sedang dinonaktifkan.') ?></div></section>
          </aside>
        </section>
      </section>

      <section id="tugas" class="halaman <?= $active_page === 'tugas' ? 'halaman-aktif' : '' ?>">
        <div class="judul-bagian"><div><p class="teks-kecil">Kelola</p><h2>Daftar Tugas</h2></div></div>
        <form id="formTambahTugas" class="form-tugas" novalidate>
          <div class="grup-form"><label for="inputNamaTugas">Nama tugas</label><input type="text" id="inputNamaTugas" placeholder="Contoh: Laporan praktikum" autocomplete="off"><small id="pesanErrorNamaTugas" class="pesan-error"></small></div>
          <div class="grup-form"><label for="pilihanMataKuliah">Mata kuliah</label><select id="pilihanMataKuliah"><option value=""><?= $courses ? 'Pilih mata kuliah' : 'Tambahkan mata kuliah terlebih dahulu' ?></option><?php foreach ($courses as $course): ?><option value="<?= e($course['nama_mata_kuliah']) ?>"><?= e($course['nama_mata_kuliah']) ?></option><?php endforeach; ?></select><small id="pesanErrorMataKuliah" class="pesan-error"></small></div>
          <div class="grup-form"><label for="inputDeadlineTugas">Deadline</label><input type="date" id="inputDeadlineTugas" min="<?= e($today) ?>"><small id="pesanErrorDeadlineTugas" class="pesan-error"></small></div>
          <button class="tombol-utama" type="submit">Tambah Tugas</button>
        </form>
        <div class="alat-filter-tugas"><div class="search-wrapper"><span>⌕</span><input type="search" id="inputPencarianTugas" placeholder="Cari tugas atau mata kuliah"></div><select id="filterStatusTugas"><option value="semua">Semua tugas</option><option value="belum">Belum selesai</option><option value="selesai">Selesai</option></select></div>
        <div id="daftarTugas" class="daftar-tugas">
          <?php if ($tasks): foreach ($tasks as $task): echo renderTaskCard($task); endforeach; else: echo kotakKosong('Belum ada tugas. Tambahkan tugas pertamamu.'); endif; ?>
        </div>
        <div id="pesanFilterTugasKosong" class="kotak-kosong" hidden>Tidak ada tugas yang sesuai filter.</div>
      </section>

      <section id="kalender" class="halaman <?= $active_page === 'kalender' ? 'halaman-aktif' : '' ?>">
        <div class="judul-bagian"><div><p class="teks-kecil">Agenda</p><h2>Kalender Mahasiswa</h2></div><button id="tombolTambahJadwalCepat" class="tombol-utama" type="button">Tambah Jadwal</button></div>
        <div class="layout-kalender">
          <section class="kartu-kalender">
            <div class="toolbar-kalender">
              <div><p class="teks-kecil">Kalender Bulanan</p><h3 id="teksBulanKalender"><?= e(formatBulanIndo($bulanAktif)) ?></h3></div>
              <div class="aksi-kalender">
                <button id="tombolBulanSebelumnya" class="tombol-ikon" type="button" aria-label="Bulan sebelumnya" data-calendar-url="<?= e(kalenderUrl($bulanSebelumnya, $kategoriFilter)) ?>">‹</button>
                <button id="tombolHariIni" class="tombol-kedua" type="button" data-calendar-url="<?= e(kalenderUrl(date('Y-m'), $kategoriFilter)) ?>">Hari ini</button>
                <button id="tombolBulanBerikutnya" class="tombol-ikon" type="button" aria-label="Bulan berikutnya" data-calendar-url="<?= e(kalenderUrl($bulanBerikutnya, $kategoriFilter)) ?>">›</button>
              </div>
            </div>
            <div class="filter-kalender"><label for="filterKategoriJadwal">Filter kategori</label><select id="filterKategoriJadwal"><?php foreach ($kategoriValid as $kategori): ?><option value="<?= e($kategori) ?>" <?= $kategoriFilter === $kategori ? 'selected' : '' ?>><?= e($kategori === 'semua' ? 'Semua kategori' : ucfirst($kategori === 'deadline' ? 'Deadline tugas' : $kategori)) ?></option><?php endforeach; ?></select></div>
            <div class="kalender-scroll-wrapper">
              <div class="nama-hari-kalender" aria-hidden="true"><span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span></div>
              <div id="isiKalender" class="isi-kalender">
                <?php
                  $first = clone $bulanAktif;
                  $start = clone $first;
                  $start->modify('-' . (int)$first->format('w') . ' day');
                  for ($i = 0; $i < 42; $i++) {
                      $date = clone $start;
                      $date->modify('+' . $i . ' day');
                      $kode = $date->format('Y-m-d');
                      $items = $itemsByDate[$kode] ?? [];
                      $classes = [];
                      if ($kode === $today) $classes[] = 'tanggal-hari-ini';
                      if ($date->format('m') !== $bulanAktif->format('m')) $classes[] = 'tanggal-luar-bulan';
                      echo '<button class="kotak-tanggal ' . e(implode(' ', $classes)) . '" type="button" data-tanggal="' . e($kode) . '" data-tanggal-label="' . e(formatTanggalIndo($kode)) . '">';
                      echo '<span class="kepala-tanggal"><span class="angka-tanggal">' . (int)$date->format('j') . '</span>' . ($items ? '<span class="titik-jadwal"></span>' : '') . '</span>';
                      echo '<span class="daftar-jadwal-di-tanggal">';
                      foreach (array_slice($items, 0, 3) as $item) {
                          echo '<span class="label-jadwal kategori-' . e($item['kategori']) . '">' . e($item['jam'] . ' ' . $item['judul']) . '</span>';
                      }
                      if (count($items) > 3) echo '<span class="jumlah-jadwal-lain">+' . (count($items) - 3) . ' jadwal lain</span>';
                      echo '</span></button>';
                  }
                ?>
              </div>
            </div>
          </section>
          <aside class="sisi-agenda">
            <section class="panel"><h3>Agenda Hari Ini</h3><div id="daftarAgendaHariIni" class="daftar-agenda"><?= $agendaHariIni ? implode('', array_map('renderAgendaItem', $agendaHariIni)) : kotakKosong('Tidak ada agenda hari ini.') ?></div></section>
            <section class="panel"><h3>Reminder Deadline</h3><div id="daftarReminderDeadline" class="daftar-agenda"><?= $reminderDeadline ? implode('', array_map('renderAgendaItem', array_slice($reminderDeadline, 0, 5))) : kotakKosong('Deadline dekat belum ada.') ?></div></section>
          </aside>
        </div>
        <div id="dataAgendaTanggal" hidden><?php foreach ($itemsKalender as $item) echo renderAgendaItem($item); ?></div>
      </section>

      <section id="pengaturan" class="halaman <?= $active_page === 'pengaturan' ? 'halaman-aktif' : '' ?>">
        <div class="judul-bagian"><div><p class="teks-kecil">Preferensi</p><h2>Pengaturan</h2></div></div>
        <div class="grid-pengaturan">
          <section class="panel">
            <h3>Profil</h3>
            <form id="formFotoProfil" class="pengaturan-profil" enctype="multipart/form-data">
              <div id="previewFotoProfil" class="preview-foto-profil" aria-label="Preview foto profil"><?php if ($foto_profil): ?><img src="<?= e($foto_profil) ?>" alt="Preview foto profil"><?php else: ?><span id="inisialPreviewProfil"><?= e($inisial_user) ?></span><?php endif; ?></div>
              <div class="aksi-profil"><input type="file" id="inputFotoProfil" name="foto" accept="image/png, image/jpeg, image/jpg" hidden><button id="tombolUploadFoto" class="tombol-kedua" type="button">Upload Foto</button><button id="tombolHapusFoto" class="tombol-kecil tombol-hapus" type="button">Hapus Foto</button></div>
            </form>
            <small id="pesanErrorFotoProfil" class="pesan-error-foto"></small>
            <label for="inputNamaPengguna">Nama kamu</label>
            <input type="text" id="inputNamaPengguna" value="<?= e($nama_user) ?>" placeholder="Masukkan nama kamu" autocomplete="off">
            <button id="tombolSimpanNama" class="tombol-utama" type="button">Simpan Nama</button>
          </section>
          <section class="panel"><h3>Tampilan</h3><p class="teks-kecil-panel">Mode tampilan mengikuti tombol di header dan tersimpan otomatis.</p><button id="tombolModeGelapPengaturan" class="tombol-mode-pengaturan" type="button" data-toggle-mode-gelap aria-pressed="<?= (int)$settings['dark_mode'] ? 'true' : 'false' ?>"><span class="ikon-mode-gelap" aria-hidden="true"><?= (int)$settings['dark_mode'] ? '☀' : '🌙' ?></span><span class="label-mode-gelap"><?= (int)$settings['dark_mode'] ? 'Gunakan light mode' : 'Gunakan dark mode' ?></span></button></section>
          <section class="panel"><h3>Notifikasi</h3><label class="baris-switch" for="toggleNotifikasiDeadline"><span>Peringatan deadline dekat</span><input type="checkbox" id="toggleNotifikasiDeadline" <?= (int)$settings['notifikasi'] ? 'checked' : '' ?>></label></section>
          <section class="panel panel-mata-kuliah" id="panelMataKuliah"><h3>Mata Kuliah</h3><p class="teks-kecil-panel">Tambahkan mata kuliah kamu. Daftar ini otomatis muncul di form tambah tugas.</p><div class="form-tambah-mata-kuliah"><div class="grup-form"><label for="inputNamaMataKuliah">Nama mata kuliah</label><input type="text" id="inputNamaMataKuliah" placeholder="Contoh: Pemrograman Mobile" autocomplete="off" maxlength="80"><small id="pesanErrorMataKuliahBaru" class="pesan-error"></small></div><button id="tombolTambahMataKuliah" class="tombol-utama" type="button">Tambah</button></div><div id="daftarMataKuliahPengaturan" class="daftar-mata-kuliah-pengaturan"><?php if ($courses): foreach ($courses as $course): ?><div class="item-mata-kuliah" data-course-id="<?= (int)$course['id'] ?>"><span class="nama-mata-kuliah-item"><?= e($course['nama_mata_kuliah']) ?></span><button class="tombol-hapus-mata-kuliah" type="button" data-course-remove="<?= (int)$course['id'] ?>">Hapus</button></div><?php endforeach; else: ?><div class="kotak-kosong-mata-kuliah"><span class="ikon-kosong-mata-kuliah">+</span>Belum ada mata kuliah. Tambahkan mata kuliah terlebih dahulu.</div><?php endif; ?></div></section>
          <section class="panel panel-bahaya"><h3>Data</h3><p>Hapus semua tugas dan jadwal dari akun ini.</p><button id="tombolResetData" class="tombol-bahaya" type="button">Hapus Semua Data</button></section>
          <section class="panel panel-akun"><h3>Akun</h3><p>Keluar dari sesi StudyFlow di perangkat ini.</p><a class="tombol-logout" href="logout.php" aria-label="Logout dari StudyFlow"><span class="ikon-logout" aria-hidden="true">&#x21AA;</span><span>Logout</span></a></section>
        </div>
      </section>
    </main>
  </div>

  <div id="modalKonfirmasi" class="lapisan-modal" aria-hidden="true"><div class="modal-konfirmasi" role="dialog" aria-modal="true" aria-labelledby="judulModalKonfirmasi" aria-describedby="pesanModalKonfirmasi"><div class="ikon-modal" aria-hidden="true">!</div><div class="isi-modal"><p class="teks-kecil">Konfirmasi</p><h2 id="judulModalKonfirmasi">Konfirmasi aksi</h2><p id="pesanModalKonfirmasi">Lanjutkan aksi ini?</p></div><div class="aksi-modal"><button id="tombolBatalKonfirmasi" class="tombol-modal tombol-modal-kedua" type="button">Batal</button><button id="tombolSetujuKonfirmasi" class="tombol-modal tombol-modal-bahaya" type="button">Lanjutkan</button></div></div></div>
  <div id="modalJadwal" class="lapisan-modal" aria-hidden="true"><form id="formTambahJadwal" class="modal-jadwal" role="dialog" aria-modal="true" aria-labelledby="judulModalJadwal" novalidate><div class="isi-modal"><p class="teks-kecil">Jadwal Baru</p><h2 id="judulModalJadwal">Tambah Jadwal</h2><p id="teksTanggalJadwalDipilih">Pilih tanggal pada kalender.</p></div><div class="grup-form"><label for="inputNamaJadwal">Nama kegiatan</label><input type="text" id="inputNamaJadwal" placeholder="Contoh: Diskusi kelompok" autocomplete="off"><small id="pesanErrorNamaJadwal" class="pesan-error"></small></div><div class="baris-form"><div class="grup-form"><label for="inputTanggalJadwal">Tanggal</label><input type="date" id="inputTanggalJadwal"><small id="pesanErrorTanggalJadwal" class="pesan-error"></small></div><div class="grup-form"><label for="inputJamJadwal">Jam</label><input type="time" id="inputJamJadwal"><small id="pesanErrorJamJadwal" class="pesan-error"></small></div></div><div class="grup-form"><label for="pilihanKategoriJadwal">Kategori</label><select id="pilihanKategoriJadwal"><option value="kuliah">Kuliah</option><option value="organisasi">Organisasi</option><option value="ujian">Ujian</option><option value="pribadi">Pribadi</option></select></div><div class="aksi-modal"><button id="tombolBatalJadwal" class="tombol-modal tombol-modal-kedua" type="button">Batal</button><button class="tombol-modal tombol-modal-utama" type="submit">Simpan Jadwal</button></div></form></div>
  <div id="modalDetailTanggal" class="lapisan-modal" aria-hidden="true"><div class="modal-detail-jadwal" role="dialog" aria-modal="true" aria-labelledby="judulModalDetail"><div class="kepala-modal-detail"><div><p class="teks-kecil">Jadwal di Tanggal</p><h2 id="judulModalDetail">Detail tanggal</h2></div><button id="tombolTutupDetailTanggal" class="tombol-tutup-modal" type="button">×</button></div><div id="daftarJadwalDetailTanggal" class="daftar-jadwal-detail"></div><button id="tombolTambahJadwalDariDetail" class="tombol-tambah-di-modal" type="button">Tambah jadwal di tanggal ini</button></div></div>

  <button id="tombolTambahCepatMobile" class="tombol-tambah-cepat" type="button" aria-label="Tambah tugas cepat">+</button>
  <div id="wadahToast" class="wadah-toast" aria-live="polite" aria-atomic="true"></div>
  <nav class="bottom-navigation-mobile" aria-label="Navigasi mobile">
    <button class="tombol-nav-mobile <?= $active_page === 'dashboard' ? 'aktif' : '' ?>" type="button" data-halaman="dashboard"><span>⌂</span><small>Dashboard</small></button>
    <button class="tombol-nav-mobile <?= $active_page === 'tugas' ? 'aktif' : '' ?>" type="button" data-halaman="tugas"><span>□</span><small>Tugas</small></button>
    <button class="tombol-nav-mobile <?= $active_page === 'kalender' ? 'aktif' : '' ?>" type="button" data-halaman="kalender"><span>◇</span><small>Kalender</small></button>
    <button class="tombol-nav-mobile <?= $active_page === 'pengaturan' ? 'aktif' : '' ?>" type="button" data-halaman="pengaturan"><span>⚙</span><small>Pengaturan</small></button>
  </nav>
  <button id="tombolTambahMobile" class="tombol-tambah-mobile" type="button" aria-label="Tambah tugas">+</button>
  <script>
    window.studyflowUser = {
      nama: <?= json_encode($nama_user, JSON_UNESCAPED_UNICODE) ?>,
      fotoProfil: <?= json_encode($foto_profil, JSON_UNESCAPED_UNICODE) ?>,
      inisial: <?= json_encode($inisial_user, JSON_UNESCAPED_UNICODE) ?>,
      modeGelap: <?= (int)$settings['dark_mode'] ?>,
      notifikasi: <?= (int)$settings['notifikasi'] ?>,
      flashLogin: <?= json_encode($flash_login) ?>
    };
  </script>
  <script src="script.js?v=20260526-ssr-mobile"></script>
</body>
</html>
