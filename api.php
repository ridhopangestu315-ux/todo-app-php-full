<?php
// Suppress PHP errors/warnings from leaking into JSON output
error_reporting(0);
@ini_set('display_errors', '0');

/**
 * ============================================
 * API - STUDYFLOW
 * ============================================
 * File ini menangani semua request AJAX
 * Menggunakan prepared statement untuk keamanan
 * Mengembalikan JSON response
 */

session_start();
date_default_timezone_set('Asia/Jakarta');
require 'koneksi.php';

header('Content-Type: application/json; charset=utf-8');

// Fungsi validasi session
function validasiSession() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    return (int)$_SESSION['user_id'];
}

// Fungsi respons JSON
function respons($status, $message, $data = null) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function inputTeks($value, $max = 255) {
    $value = trim((string)($value ?? ''));
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }
    return substr($value, 0, $max);
}

function validTanggal($value) {
    $date = DateTime::createFromFormat('Y-m-d', (string)$value);
    return $date && $date->format('Y-m-d') === $value;
}

function validJam($value) {
    return (bool)preg_match('/^\d{2}:\d{2}$/', (string)$value);
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

// ============================================
// GET REQUEST HANDLER
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = validasiSession();
    $aksi = $_GET['aksi'] ?? '';

    // GET ALL TASKS
    if ($aksi === 'ambil_tugas') {
        $kolom_waktu = punyaKolom($conn, 'tasks', 'dibuat_pada') ? 'dibuat_pada' : 'created_at';
        $stmt = mysqli_prepare($conn, "
            SELECT id, nama_tugas, mata_kuliah, deadline, sudah_selesai, `$kolom_waktu` AS dibuat_pada
            FROM tasks 
            WHERE user_id = ? 
            ORDER BY deadline ASC, `$kolom_waktu` DESC
        ");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
        respons('success', 'Data tugas berhasil diambil', $tasks);
    }

    // GET ALL SCHEDULES
    elseif ($aksi === 'ambil_jadwal') {
        $kolom_waktu = punyaKolom($conn, 'schedules', 'dibuat_pada') ? 'dibuat_pada' : 'created_at';
        $stmt = mysqli_prepare($conn, "
            SELECT id, nama_jadwal, tanggal, jam, kategori, `$kolom_waktu` AS dibuat_pada
            FROM schedules 
            WHERE user_id = ? 
            ORDER BY tanggal ASC, jam ASC
        ");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $schedules = mysqli_fetch_all($result, MYSQLI_ASSOC);
        respons('success', 'Data jadwal berhasil diambil', $schedules);
    }

    // GET DASHBOARD STATS
    elseif ($aksi === 'dashboard_stats') {
        $queries = [
            'total_tugas' => "SELECT COUNT(*) as cnt FROM tasks WHERE user_id = ?",
            'tugas_selesai' => "SELECT COUNT(*) as cnt FROM tasks WHERE user_id = ? AND sudah_selesai = 1",
            'tugas_hariini' => "SELECT COUNT(*) as cnt FROM tasks WHERE user_id = ? AND deadline = CURDATE() AND sudah_selesai = 0",
            'deadline_dekat' => "SELECT COUNT(*) as cnt FROM tasks WHERE user_id = ? AND deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND sudah_selesai = 0"
        ];

        $stats = [];
        foreach ($queries as $key => $sql) {
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $stats[$key] = $row['cnt'] ?? 0;
        }
        respons('success', 'Statistik berhasil diambil', $stats);
    }

    // GET USER PROFILE
    elseif ($aksi === 'ambil_profile') {
        $stmt = mysqli_prepare($conn, "SELECT id, nama, email, foto_profil FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $profile = mysqli_fetch_assoc($result);
        respons('success', 'Profile berhasil diambil', $profile);
    }

    // GET USER SETTINGS
    elseif ($aksi === 'ambil_settings') {
        $stmt = mysqli_prepare($conn, "SELECT dark_mode, notifikasi FROM settings WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $settings = mysqli_fetch_assoc($result);
        
        if (!$settings) {
            // Buat settings default jika belum ada
            $stmt = mysqli_prepare($conn, "INSERT INTO settings (user_id, dark_mode, notifikasi) VALUES (?, 0, 1)");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $settings = ['dark_mode' => 0, 'notifikasi' => 1];
        }
        respons('success', 'Settings berhasil diambil', $settings);
    }

    elseif ($aksi === 'ambil_mata_kuliah') {
        $stmt = mysqli_prepare($conn, "
            SELECT id, nama_mata_kuliah
            FROM courses
            WHERE user_id = ?
            ORDER BY nama_mata_kuliah ASC
        ");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
        respons('success', 'Mata kuliah berhasil diambil', $courses);
    }

    // GET TASK BY ID
    elseif ($aksi === 'ambil_tugas_by_id') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = mysqli_prepare($conn, "
            SELECT * FROM tasks 
            WHERE id = ? AND user_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $task = mysqli_fetch_assoc($result);
        
        if (!$task) {
            respons('error', 'Tugas tidak ditemukan');
        }
        respons('success', 'Tugas berhasil diambil', $task);
    }
}

// ============================================
// POST REQUEST HANDLER
// ============================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = validasiSession();
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $aksi = $_POST['aksi'] ?? ($input['aksi'] ?? ($_GET['aksi'] ?? ''));

    // ADD TASK
    if ($aksi === 'tambah_tugas') {
        $nama_tugas = inputTeks($input['nama_tugas'] ?? '', 255);
        $mata_kuliah = inputTeks($input['mata_kuliah'] ?? '', 100);
        $deadline = inputTeks($input['deadline'] ?? '', 10);

        if (!$nama_tugas || !$mata_kuliah || !$deadline) {
            respons('error', 'Nama tugas, mata kuliah, dan deadline wajib diisi');
        }

        if (!validTanggal($deadline)) {
            respons('error', 'Format deadline tidak valid');
        }

        $stmt = mysqli_prepare($conn, "SELECT id FROM courses WHERE user_id = ? AND nama_mata_kuliah = ?");
        mysqli_stmt_bind_param($stmt, "is", $user_id, $mata_kuliah);
        mysqli_stmt_execute($stmt);
        $course = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (!$course) {
            respons('error', 'Tambahkan mata kuliah terlebih dahulu');
        }

        $stmt = mysqli_prepare($conn, "
            INSERT INTO tasks (user_id, nama_tugas, mata_kuliah, deadline) 
            VALUES (?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $nama_tugas, $mata_kuliah, $deadline);

        if (mysqli_stmt_execute($stmt)) {
            $task_id = mysqli_insert_id($conn);
            respons('success', 'Tugas berhasil ditambahkan', ['id' => $task_id]);
        } else {
            respons('error', 'Gagal menambahkan tugas');
        }
    }

    // EDIT TASK
    elseif ($aksi === 'edit_tugas') {
        $id = (int)($input['id'] ?? 0);
        $nama_tugas = inputTeks($input['nama_tugas'] ?? '', 255);
        $mata_kuliah = inputTeks($input['mata_kuliah'] ?? '', 100);
        $deadline = inputTeks($input['deadline'] ?? '', 10);

        if (!$id || !$nama_tugas || !$deadline) {
            respons('error', 'Data tidak lengkap');
        }

        if (!validTanggal($deadline)) {
            respons('error', 'Format deadline tidak valid');
        }

        $stmt = mysqli_prepare($conn, "
            UPDATE tasks 
            SET nama_tugas = ?, mata_kuliah = ?, deadline = ? 
            WHERE id = ? AND user_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "sssii", $nama_tugas, $mata_kuliah, $deadline, $id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            respons('success', 'Tugas berhasil diperbarui');
        } else {
            respons('error', 'Gagal memperbarui tugas');
        }
    }

    // DELETE TASK
    elseif ($aksi === 'hapus_tugas') {
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            respons('error', 'ID tugas tidak valid');
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            respons('success', 'Tugas berhasil dihapus');
        } else {
            respons('error', 'Gagal menghapus tugas');
        }
    }

    // TOGGLE TASK COMPLETION
    elseif ($aksi === 'toggle_selesai') {
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            respons('error', 'ID tugas tidak valid');
        }

        // Get current status
        $stmt = mysqli_prepare($conn, "SELECT sudah_selesai FROM tasks WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $task = mysqli_fetch_assoc($result);

        if (!$task) {
            respons('error', 'Tugas tidak ditemukan');
        }

        $new_status = $task['sudah_selesai'] ? 0 : 1;
        $stmt = mysqli_prepare($conn, "UPDATE tasks SET sudah_selesai = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "iii", $new_status, $id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            respons('success', 'Status tugas berhasil diperbarui', ['sudah_selesai' => $new_status]);
        } else {
            respons('error', 'Gagal memperbarui status');
        }
    }

    // ADD SCHEDULE
    elseif ($aksi === 'tambah_jadwal') {
        $nama_jadwal = inputTeks($input['nama_jadwal'] ?? '', 255);
        $tanggal = inputTeks($input['tanggal'] ?? '', 10);
        $jam = inputTeks($input['jam'] ?? '', 5);
        $kategori = inputTeks($input['kategori'] ?? 'pribadi', 50);
        $kategori_valid = ['kuliah', 'organisasi', 'ujian', 'pribadi'];

        if (!$nama_jadwal || !$tanggal || !$jam) {
            respons('error', 'Data jadwal tidak lengkap');
        }

        if (!validTanggal($tanggal) || !validJam($jam) || !in_array($kategori, $kategori_valid, true)) {
            respons('error', 'Data jadwal tidak valid');
        }

        $stmt = mysqli_prepare($conn, "
            INSERT INTO schedules (user_id, nama_jadwal, tanggal, jam, kategori) 
            VALUES (?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $nama_jadwal, $tanggal, $jam, $kategori);

        if (mysqli_stmt_execute($stmt)) {
            $schedule_id = mysqli_insert_id($conn);
            respons('success', 'Jadwal berhasil ditambahkan', ['id' => $schedule_id]);
        } else {
            respons('error', 'Gagal menambahkan jadwal');
        }
    }

    // DELETE SCHEDULE
    elseif ($aksi === 'hapus_jadwal') {
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            respons('error', 'ID jadwal tidak valid');
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM schedules WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            respons('success', 'Jadwal berhasil dihapus');
        } else {
            respons('error', 'Gagal menghapus jadwal');
        }
    }

    // UPDATE PROFILE
    elseif ($aksi === 'update_profile') {
        $nama = inputTeks($input['nama'] ?? '', 100);

        if (!$nama) {
            respons('error', 'Nama tidak boleh kosong');
        }

        $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $nama, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['nama'] = $nama;
            respons('success', 'Profil berhasil diperbarui');
        } else {
            respons('error', 'Gagal memperbarui profil');
        }
    }

    // UPDATE DARK MODE
    elseif ($aksi === 'update_dark_mode') {
        $dark_mode = (int)($input['dark_mode'] ?? 0);

        $stmt = mysqli_prepare($conn, "
            INSERT INTO settings (user_id, dark_mode) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE dark_mode = ?
        ");
        mysqli_stmt_bind_param($stmt, "iii", $user_id, $dark_mode, $dark_mode);

        if (mysqli_stmt_execute($stmt)) {
            respons('success', 'Dark mode berhasil diperbarui');
        } else {
            respons('error', 'Gagal memperbarui dark mode');
        }
    }

    // UPDATE SETTINGS
    elseif ($aksi === 'update_settings') {
        $notifikasi = (int)($input['notifikasi'] ?? 1);

        $stmt = mysqli_prepare($conn, "
            INSERT INTO settings (user_id, notifikasi) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE notifikasi = ?
        ");
        mysqli_stmt_bind_param($stmt, "iii", $user_id, $notifikasi, $notifikasi);

        if (mysqli_stmt_execute($stmt)) {
            respons('success', 'Settings berhasil diperbarui');
        } else {
            respons('error', 'Gagal memperbarui settings');
        }
    }

    // UPLOAD PROFILE PHOTO
    elseif ($aksi === 'upload_foto') {
        if (!isset($_FILES['foto'])) {
            respons('error', 'Tidak ada file yang dipilih');
        }

        $file = $_FILES['foto'];
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $allowed_mime = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024;

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            respons('error', 'Upload gagal. Kode error: ' . (int)$file['error']);
        }

        if ($file['size'] > $max_size) {
            respons('error', 'Ukuran file terlalu besar (max 2MB)');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mime = mime_content_type($file['tmp_name']);
        } else {
            $info_gambar = getimagesize($file['tmp_name']);
            $mime = $info_gambar['mime'] ?? '';
        }

        if (!in_array($ext, $allowed_ext, true) || !in_array($mime, $allowed_mime, true)) {
            respons('error', 'Format foto harus JPG, JPEG, atau PNG');
        }

        // Buat folder uploads jika belum ada
        if (!is_dir('uploads')) {
            if (!mkdir('uploads', 0755, true)) {
                respons('error', 'Folder uploads tidak bisa dibuat');
            }
        }

        if (!is_writable('uploads')) {
            respons('error', 'Folder uploads tidak memiliki permission tulis');
        }

        $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
        $target_path = 'uploads/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $stmt = mysqli_prepare($conn, "SELECT foto_profil FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $profile = mysqli_fetch_assoc($result);
            $foto_lama = $profile['foto_profil'] ?? '';
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn, "UPDATE users SET foto_profil = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $target_path, $user_id);

            if (mysqli_stmt_execute($stmt)) {
                if ($foto_lama && strpos($foto_lama, 'uploads/') === 0 && is_file($foto_lama)) {
                    unlink($foto_lama);
                }
                respons('success', 'Foto profil berhasil diupload', ['foto_profil' => $target_path]);
            } else {
                unlink($target_path);
                respons('error', 'Gagal menyimpan foto profil');
            }
        } else {
            respons('error', 'Gagal mengupload file');
        }
    }

    elseif ($aksi === 'tambah_mata_kuliah') {
        $nama_mata_kuliah = inputTeks($input['nama_mata_kuliah'] ?? '', 100);

        if (!$nama_mata_kuliah) {
            respons('error', 'Nama mata kuliah wajib diisi');
        }

        $stmt = mysqli_prepare($conn, "SELECT id FROM courses WHERE user_id = ? AND nama_mata_kuliah = ?");
        mysqli_stmt_bind_param($stmt, "is", $user_id, $nama_mata_kuliah);
        mysqli_stmt_execute($stmt);
        $course = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if ($course) {
            respons('error', 'Mata kuliah sudah ada');
        }

        if (punyaKolom($conn, 'courses', 'nama_matkul')) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO courses (user_id, nama_mata_kuliah, nama_matkul)
                VALUES (?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $nama_mata_kuliah, $nama_mata_kuliah);
        } else {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO courses (user_id, nama_mata_kuliah)
                VALUES (?, ?)
            ");
            mysqli_stmt_bind_param($stmt, "is", $user_id, $nama_mata_kuliah);
        }

        if (mysqli_stmt_execute($stmt)) {
            respons('success', 'Mata kuliah berhasil ditambahkan', [
                'id' => mysqli_insert_id($conn),
                'nama_mata_kuliah' => $nama_mata_kuliah
            ]);
        }

        if (mysqli_errno($conn) == 1062) {
            respons('error', 'Mata kuliah sudah ada');
        }

        respons('error', 'Gagal menambahkan mata kuliah');
    }

    elseif ($aksi === 'hapus_mata_kuliah') {
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            respons('error', 'ID mata kuliah tidak valid');
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            respons('success', 'Mata kuliah berhasil dihapus');
        }

        respons('error', 'Gagal menghapus mata kuliah');
    }

    elseif ($aksi === 'hapus_foto') {
        $stmt = mysqli_prepare($conn, "SELECT foto_profil FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $profile = mysqli_fetch_assoc($result);
        $foto_lama = $profile['foto_profil'] ?? '';

        $stmt = mysqli_prepare($conn, "UPDATE users SET foto_profil = NULL WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);

        if (mysqli_stmt_execute($stmt)) {
            if ($foto_lama && strpos($foto_lama, 'uploads/') === 0 && is_file($foto_lama)) {
                unlink($foto_lama);
            }
            respons('success', 'Foto profil berhasil dihapus');
        }

        respons('error', 'Gagal menghapus foto profil');
    }

    // RESET ACCOUNT
    elseif ($aksi === 'reset_akun') {
        // Delete all tasks
        $stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        // Delete all schedules
        $stmt = mysqli_prepare($conn, "DELETE FROM schedules WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        respons('success', 'Semua data akun berhasil direset');
    }

    else {
        respons('error', 'Aksi tidak diketahui');
    }
}

else {
    respons('error', 'Method tidak didukung');
}
?>
