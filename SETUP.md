# 🚀 SETUP INSTRUCTION - StudyFlow

## Panduan Lengkap Instalasi dan Konfigurasi

### TAHAP 1: PERSIAPAN DATABASE

#### 1.1 Buka MySQL Client
```bash
# Lewat terminal/command prompt
mysql -u root -p

# Atau buka phpMyAdmin di browser
http://localhost/phpmyadmin
```

#### 1.2 Buat Database Baru
```sql
CREATE DATABASE IF NOT EXISTS studyflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 1.3 Import SQL Schema
```sql
-- Buka file database.sql dan copy seluruh isinya
-- Kemudian paste ke MySQL client

USE studyflow;
-- Paste isi database.sql di sini
```

**Atau menggunakan command line:**
```bash
mysql -u root -p studyflow < database.sql
```

#### 1.4 Verifikasi Database
```sql
USE studyflow;
SHOW TABLES;
-- Harusnya muncul: users, tasks, schedules, settings
```

---

### TAHAP 2: SETUP FILE PROJECT

#### 2.1 Copy Project ke Directory
```bash
# Windows (Laragon)
Copy folder ke: C:\laragon\www\todo-app-php-full

# atau
C:\xampp\htdocs\todo-app-php-full

# atau
C:\wamp\www\todo-app-php-full
```

#### 2.2 Verifikasi Struktur File
```
todo-app-php-full/
├── api.php
├── koneksi.php
├── login.php
├── register.php
├── logout.php
├── index.php
├── dashboard.php
├── script.js
├── style.css
├── database.sql
├── README.md
├── SETUP.md (file ini)
├── uploads/
│   └── .gitkeep
└── .gitkeep (opsional)
```

#### 2.3 Buat/Verifikasi Folder uploads
```bash
# Jika belum ada, buat folder uploads
mkdir uploads

# Set permissions (Linux/Mac)
chmod 755 uploads

# Windows: Right-click folder > Properties > Security > Edit
# Pastikan "Modify" dan "Write" permissions aktif
```

---

### TAHAP 3: KONFIGURASI DATABASE

#### 3.1 Update koneksi.php
Buka file `koneksi.php` dan pastikan konfigurasi sesuai dengan setup MySQL Anda:

```php
<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';      // Server MySQL (biasanya localhost)
$db   = 'studyflow';      // Nama database
$user = 'root';           // Username MySQL
$pass = '';               // Password MySQL (kosong untuk default)

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>
```

**Contoh Konfigurasi di berbagai platform:**

**Windows (Laragon/XAMPP default):**
```php
$user = 'root';
$pass = '';
```

**InfinityFree:**
```php
$host = 'sql123.epizy.com';  // Sesuai informasi dari InfinityFree
$user = 'epiz_xxxxx';        // Username dari InfinityFree
$pass = 'password_anda';     // Password dari InfinityFree
$db   = 'epiz_xxxxx_db';     // Database name dari InfinityFree
```

**Custom Server:**
```php
$host = 'your-host.com';
$user = 'your-username';
$pass = 'your-password';
$db   = 'studyflow';
```

---

### TAHAP 4: TEST KONEKSI

#### 4.1 Buat File Test (test_db.php)
```php
<?php
require 'koneksi.php';

if ($conn) {
    echo "✓ Koneksi database berhasil!<br>";
    
    // Test table
    $result = mysqli_query($conn, "SELECT * FROM users LIMIT 1");
    if ($result) {
        echo "✓ Tabel users berhasil diakses!<br>";
    } else {
        echo "✗ Error: " . mysqli_error($conn);
    }
} else {
    echo "✗ Koneksi gagal!";
}
?>
```

#### 4.2 Buka di Browser
```
http://localhost/todo-app-php-full/test_db.php
```

Jika berhasil, delete file `test_db.php`

---

### TAHAP 5: AKSES APLIKASI

#### 5.1 Buka di Browser
```
http://localhost/todo-app-php-full/
atau
http://localhost/todo-app-php-full/index.php
```

#### 5.2 Halaman yang Muncul
- Jika sudah login: Akan redirect ke dashboard
- Jika belum login: Akan tampil halaman login

#### 5.3 Register Akun Baru
- Klik "Daftar" di halaman login
- Isi form: Nama, Email, Password
- Klik "Daftar"
- Redirect ke login, silakan login dengan email & password

---

### TAHAP 6: VERIFIKASI FITUR

#### 6.1 Dashboard
- [✓] Statistik tugas muncul
- [✓] Tanggal & jam realtime
- [✓] Sidebar menampilkan "tugas aktif"

#### 6.2 Manajemen Tugas
- [✓] Buat tugas baru
- [✓] Edit tugas (jika ada)
- [✓] Hapus tugas
- [✓] Checklist tugas selesai
- [✓] Search & filter
- [✓] Deadline muncul

#### 6.3 Kalender
- [✓] Kalender menampilkan bulan
- [✓] Bisa navigasi bulan
- [✓] Bisa tambah jadwal
- [✓] Jadwal muncul di kalender

#### 6.4 Pengaturan
- [✓] Bisa update nama
- [✓] Bisa upload foto
- [✓] Dark mode bisa toggle
- [✓] Notifikasi bisa toggle
- [✓] Bisa logout

---

### TAHAP 7: UPLOAD KE INFINITYFREE (Opsional)

#### 7.1 Persiapan File
```bash
# Buat ZIP file
- Exclude: .git, node_modules, .DS_Store
- Include semua file PHP, CSS, JS, SQL
```

#### 7.2 Login ke InfinityFree
- Buka https://www.infinityfree.net
- Login ke akun Anda
- Buka control panel

#### 7.3 Upload File
1. Buka File Manager
2. Navigate ke public_html
3. Upload ZIP file
4. Extract di server

#### 7.4 Setup Database di InfinityFree
1. Buka MySQL Manager
2. Create new database
3. Get credentials (host, username, password)
4. Update koneksi.php dengan credentials tersebut
5. Import database.sql via phpMyAdmin

#### 7.5 Set Folder Permissions
1. Select folder `uploads`
2. Right-click > Change Permissions
3. Set to 755

#### 7.6 Test di Production
```
https://your-domain.000webhostapp.com/todo-app-php-full/
```

---

### TROUBLESHOOTING

#### ❌ Error: "Koneksi gagal"
**Solusi:**
- Pastikan MySQL server running
- Cek username & password di koneksi.php
- Cek database name
- Jika di hosting, gunakan host yang benar (bukan localhost)

#### ❌ Error: "Table 'studyflow.users' doesn't exist"
**Solusi:**
- Database belum di-import
- Buka database.sql dan import ulang
- Atau jalankan: `mysql -u root -p studyflow < database.sql`

#### ❌ Error: "Session error" atau blank page
**Solusi:**
- Clear browser cookies
- Clear browser cache
- Restart server
- Check PHP error log: `var/log/php_error.log`

#### ❌ Upload foto tidak bekerja
**Solusi:**
- Folder `uploads` belum ada
- Folder `uploads` tidak writable
- Create: `mkdir uploads && chmod 755 uploads`
- File size > 2MB
- Format bukan JPG/PNG

#### ❌ Dark mode tidak tersimpan
**Solusi:**
- Check database connection
- Check query di api.php
- Open browser console (F12) untuk lihat error

#### ❌ Form tidak submit / AJAX error
**Solusi:**
- Open browser console (F12)
- Check error message
- Pastikan api.php bisa diakses
- Check network tab di DevTools

---

### TIPS & BEST PRACTICES

#### 1. Security
```php
// Selalu gunakan prepared statements
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

// Selalu sanitize output
echo htmlspecialchars($user_input);
```

#### 2. Performance
- Gunakan indexes di database (sudah ada)
- Cache data di JavaScript jika perlu
- Lazy load images
- Minify CSS & JS untuk production

#### 3. Backup
```bash
# Backup database
mysqldump -u root -p studyflow > backup.sql

# Restore database
mysql -u root -p studyflow < backup.sql
```

#### 4. Logs
```bash
# Check PHP error log
tail -f /var/log/apache2/error.log

# Check MySQL error log
tail -f /var/log/mysql/error.log
```

---

### FILE YANG PENTING

| File | Fungsi | Edit? |
|------|--------|-------|
| koneksi.php | Database connection | ✅ Ya (config saja) |
| api.php | API endpoints | ❌ Jangan |
| dashboard.php | Main interface | ❌ Minimal |
| script.js | Frontend logic | ❌ Jangan |
| style.css | Styling | ✅ Ya (jika perlu) |
| login.php | Auth | ❌ Jangan |
| register.php | Registration | ❌ Jangan |

---

### ENVIRONMENT REQUIREMENTS

- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Modern browser (Chrome, Firefox, Safari, Edge)
- JavaScript enabled

---

### SUPPORT & HELP

Jika ada masalah:

1. **Check PHP Error**
   ```
   Browser > F12 > Console
   ```

2. **Check Network**
   ```
   Browser > F12 > Network > Filter XHR
   Click action > lihat response
   ```

3. **Check Server Error**
   ```bash
   tail -f /var/log/php_error.log
   tail -f /var/log/apache2/error.log
   tail -f /var/log/mysql/error.log
   ```

4. **Test Database**
   ```php
   <?php
   require 'koneksi.php';
   var_dump(mysqli_connect_error());
   ?>
   ```

---

### NEXT STEPS

1. ✅ Setup database
2. ✅ Copy files
3. ✅ Configure koneksi.php
4. ✅ Test koneksi
5. ✅ Register user baru
6. ✅ Test semua fitur
7. ✅ Customize style.css (opsional)
8. ✅ Deploy ke production (opsional)

---

**StudyFlow Setup v1.0**  
Terakhir diupdate: 2026-05-26  
Semoga sukses! 🎉
