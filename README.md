# StudyFlow - Aplikasi Todo Mahasiswa Fullstack PHP + MySQL

## 📋 Deskripsi Project
StudyFlow adalah aplikasi web modern untuk manajemen tugas dan jadwal mahasiswa. Dibangun dengan:
- **Backend**: PHP Native (MySQLi Procedural)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Architecture**: MVC Pattern dengan Separation of Concerns

## 🎯 Fitur Utama

### 1. **Authentication**
- ✅ Register akun baru
- ✅ Login dengan email & password
- ✅ Session management
- ✅ Logout

### 2. **Manajemen Tugas**
- ✅ Tambah, edit, hapus tugas
- ✅ Checklist tugas selesai
- ✅ Search tugas
- ✅ Filter berdasarkan status (semua, belum selesai, selesai)
- ✅ Deadline realtime dari database
- ✅ Statistik tugas (total, selesai, deadline dekat)

### 3. **Dashboard Realtime**
- ✅ Total tugas realtime
- ✅ Tugas deadline hari ini
- ✅ Deadline dekat (2 hari ke depan)
- ✅ Tugas selesai count
- ✅ Progress bar harian
- ✅ Fokus hari ini

### 4. **Kalender & Jadwal**
- ✅ Kalender dinamis
- ✅ Tambah jadwal dengan tanggal, jam, kategori
- ✅ Filter kategori jadwal
- ✅ Deadline tugas muncul di kalender
- ✅ Agenda hari ini

### 5. **Pengaturan**
- ✅ Update nama profil
- ✅ Upload foto profil
- ✅ Dark mode (tersimpan di database)
- ✅ Toggle notifikasi deadline
- ✅ Reset semua data akun

### 6. **Keamanan**
- ✅ Prepared Statements (Anti SQL Injection)
- ✅ htmlspecialchars() (Anti XSS)
- ✅ Password hashing dengan PASSWORD_DEFAULT
- ✅ Session validation
- ✅ CSRF protection siap

## 🗂️ Struktur File

```
todo-app-php-full/
├── index.php                 # Entry point (redirect ke login/dashboard)
├── login.php                 # Halaman login
├── register.php              # Halaman register
├── logout.php                # Logout & destroy session
├── dashboard.php             # Halaman utama (dashboard/tugas/kalender/pengaturan)
├── api.php                   # API endpoints untuk AJAX
├── koneksi.php              # Database connection
├── style.css                # CSS styling
├── script.js                # JavaScript (refactored untuk API)
├── database.sql             # SQL schema lengkap
├── uploads/                 # Folder untuk foto profil
├── .gitkeep                # Placeholder git
└── README.md               # Dokumentasi ini
```

## 🗄️ Struktur Database

### Table: users
```sql
- id (INT, Primary Key, Auto Increment)
- nama (VARCHAR 100)
- email (VARCHAR 100, Unique)
- password (VARCHAR 255, Hashed)
- foto_profil (VARCHAR 255, nullable)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Table: tasks
```sql
- id (INT, Primary Key)
- user_id (INT, Foreign Key)
- nama_tugas (VARCHAR 255)
- mata_kuliah (VARCHAR 100)
- deadline (DATE)
- sudah_selesai (BOOLEAN)
- dibuat_pada (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Table: schedules
```sql
- id (INT, Primary Key)
- user_id (INT, Foreign Key)
- nama_jadwal (VARCHAR 255)
- tanggal (DATE)
- jam (TIME)
- kategori (VARCHAR 50)
- dibuat_pada (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Table: settings
```sql
- id (INT, Primary Key)
- user_id (INT, Foreign Key, Unique)
- dark_mode (BOOLEAN)
- notifikasi (BOOLEAN)
- updated_at (TIMESTAMP)
```

## 🚀 Setup & Instalasi

### 1. **Setup Database**
```bash
# Buka MySQL client
mysql -u root -p

# Copy-paste seluruh isi database.sql
```

Atau import langsung:
```bash
mysql -u root -p studyflow < database.sql
```

### 2. **Setup Project**
```bash
# Copy folder ke htdocs atau www directory
# Pastikan koneksi database sudah benar di koneksi.php

# Buat folder uploads (jika belum ada)
mkdir uploads
chmod 755 uploads
```

### 3. **Konfigurasi koneksi.php**
```php
$host = 'localhost';
$db   = 'studyflow';
$user = 'root';
$pass = '';  // Sesuaikan dengan konfigurasi Anda
```

### 4. **Akses Aplikasi**
```
http://localhost/todo-app-php-full/
atau
http://localhost/todo-app-php-full/index.php
```

## 🔌 API Endpoints

Semua request dikirim ke `api.php` via FETCH API. Format response: JSON

### GET Endpoints

#### 1. Ambil Semua Tugas
```
GET /api.php?aksi=ambil_tugas
Response: [{ id, nama_tugas, mata_kuliah, deadline, sudah_selesai, dibuat_pada }, ...]
```

#### 2. Ambil Semua Jadwal
```
GET /api.php?aksi=ambil_jadwal
Response: [{ id, nama_jadwal, tanggal, jam, kategori, dibuat_pada }, ...]
```

#### 3. Ambil Dashboard Stats
```
GET /api.php?aksi=dashboard_stats
Response: { total_tugas, tugas_selesai, tugas_hariini, deadline_dekat }
```

#### 4. Ambil Profile User
```
GET /api.php?aksi=ambil_profile
Response: { id, nama, email, foto_profil }
```

#### 5. Ambil Settings User
```
GET /api.php?aksi=ambil_settings
Response: { dark_mode, notifikasi }
```

### POST Endpoints

#### 1. Tambah Tugas
```
POST /api.php
Body: { aksi: 'tambah_tugas', nama_tugas, mata_kuliah, deadline }
```

#### 2. Edit Tugas
```
POST /api.php
Body: { aksi: 'edit_tugas', id, nama_tugas, mata_kuliah, deadline }
```

#### 3. Hapus Tugas
```
POST /api.php
Body: { aksi: 'hapus_tugas', id }
```

#### 4. Toggle Tugas Selesai
```
POST /api.php
Body: { aksi: 'toggle_selesai', id }
```

#### 5. Tambah Jadwal
```
POST /api.php
Body: { aksi: 'tambah_jadwal', nama_jadwal, tanggal, jam, kategori }
```

#### 6. Hapus Jadwal
```
POST /api.php
Body: { aksi: 'hapus_jadwal', id }
```

#### 7. Update Profile
```
POST /api.php
Body: { aksi: 'update_profile', nama }
```

#### 8. Update Dark Mode
```
POST /api.php
Body: { aksi: 'update_dark_mode', dark_mode }
```

#### 9. Update Settings
```
POST /api.php
Body: { aksi: 'update_settings', notifikasi }
```

#### 10. Upload Foto Profil
```
POST /api.php
Form Data: { aksi: 'upload_foto', foto: File }
```

#### 11. Reset Akun
```
POST /api.php
Body: { aksi: 'reset_akun' }
```

## 💅 Fitur UI/UX

- **Responsive Design**: Mobile-first approach
- **Dark Mode**: Toggle dark/light mode (tersimpan di database)
- **Smooth Animations**: CSS transitions & transforms
- **Toast Notifications**: Feedback untuk setiap aksi
- **Real-time Updates**: Data di-refresh via AJAX
- **Modern Colors**: Gradient backgrounds dan color scheme profesional
- **Accessibility**: Semantic HTML, ARIA labels

## 🔒 Keamanan

1. **SQL Injection Prevention**
   - Semua query menggunakan Prepared Statements
   - Parameter binding dengan `mysqli_stmt_bind_param`

2. **XSS Prevention**
   - Input di-sanitize dengan `htmlspecialchars()`
   - Output di-escape sebelum ditampilkan

3. **Password Security**
   - Hash dengan `password_hash()` (bcrypt)
   - Verify dengan `password_verify()`

4. **Session Management**
   - Session validation di setiap halaman
   - Redirect ke login jika belum authenticated

## 📱 Responsive Breakpoints

- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

## 🎨 Color Scheme

```css
--primary: #4f46e5      (Indigo)
--secondary: #7c3aed    (Purple)
--success: #16a34a      (Green)
--warning: #d97706      (Amber)
--danger: #e11d48       (Rose)
--dark-bg: #0b1020
--light-bg: #eef3ff
```

## 📦 Upload ke InfinityFree

1. **Persiapan:**
   - ZIP seluruh folder project
   - Exclude node_modules (jika ada)

2. **Upload:**
   - Login ke InfinityFree
   - Upload file ke public_html atau folder aplikasi
   - Extract di server

3. **Database:**
   - Import database.sql via phpMyAdmin
   - Update kredensial koneksi di koneksi.php

4. **File Permissions:**
   - Set permissions untuk folder uploads: 755
   - Set permissions untuk file: 644

5. **Testing:**
   - Test login/register
   - Test CRUD operations
   - Test upload foto
   - Test dark mode

## 🐛 Troubleshooting

### Error: Connection failed
- Pastikan MySQL server berjalan
- Cek kredensial di koneksi.php
- Pastikan database `studyflow` sudah dibuat

### Upload foto gagal
- Pastikan folder `uploads` ada dan writeable
- Check file size (max 2MB)
- Check file format (JPG, PNG)

### Session error
- Clear browser cookies & cache
- Restart browser
- Pastikan session.save_path writeable

### Dark mode tidak tersimpan
- Pastikan query SQL berjalan
- Check error log di console
- Refresh halaman untuk verify

## 📝 Changelog

### v1.0.0 - Final Version
- ✅ Full PHP + MySQL integration
- ✅ Removed localStorage
- ✅ AJAX API for all operations
- ✅ Database-driven features
- ✅ Session management
- ✅ File upload support
- ✅ Prepared statements everywhere
- ✅ XSS protection
- ✅ Responsive design
- ✅ Dark mode support

## 📞 Support

Jika ada masalah atau pertanyaan, silakan:
1. Check error di browser console (F12)
2. Check PHP error log
3. Check MySQL error log
4. Pastikan semua file sudah tercopy dengan benar

## 📄 License

Gratis untuk penggunaan pribadi dan komersial.

---

**Created with ❤️ for Indonesian Students**
StudyFlow v1.0.0 - Complete Rewrite with Database Integration
