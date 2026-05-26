/*
================================
PENGATURAN NAMA DATA LOCAL STORAGE
================================
Bagian ini berisi nama tempat penyimpanan data di browser.
LocalStorage dipakai agar data tetap ada setelah halaman direfresh.
*/
const namaPenyimpananLocalStorage = {
  tugas: "studentTodoTasks",
  jadwal: "studentCalendarEvents",
  fotoProfil: "studentProfilePhoto",
  namaPengguna: "studentTodoName",
  modeGelap: "studentTodoDarkMode",
  notifikasiDeadline: "studentTodoNotification",
  // Kunci baru untuk menyimpan daftar mata kuliah buatan user
  daftarMataKuliah: "studentTodoCourseList"
};

const aturanFotoProfil = {
  ukuranMaksimal: 2 * 1024 * 1024,
  lebarMaksimal: 480,
  tinggiMaksimal: 480,
  tipeFileYangDiizinkan: ["image/jpeg", "image/jpg", "image/png"]
};

const daftarKategoriJadwal = {
  kuliah: { nama: "Kuliah", warna: "#4285f4" },
  organisasi: { nama: "Organisasi", warna: "#34a853" },
  ujian: { nama: "Ujian", warna: "#fbbc04" },
  pribadi: { nama: "Pribadi", warna: "#a142f4" },
  deadline: { nama: "Deadline", warna: "#ea4335" }
};

/*
================================
DAFTAR MATA KULIAH DEFAULT
================================
Mata kuliah ini dipakai sebagai nilai awal saat user
belum pernah mengatur mata kuliah sendiri.
Jika user sudah punya data sendiri di localStorage,
daftar ini tidak akan digunakan.
*/
const daftarMataKuliahDefault = [
  "Analisis dan Perancangan Sistem",
  "Grafika Komputer",
  "Interaksi Manusia dan Komputer",
  "Jaringan Komputer",
  "Pendidikan Agama Islam",
  "Pemrograman Web",
  "Praktikum Jaringan Komputer",
  "Praktikum PBO",
  "Sistem Rekayasa Berkelanjutan",
  "Statistika Untuk Komputasi"
];

/*
================================
DATA UTAMA APLIKASI
================================
Semua data yang sering dipakai dikumpulkan di satu tempat.
Dengan cara ini, kita mudah tahu isi aplikasi saat sedang debug.
*/
const dataAplikasi = {
  daftarSemuaTugas: ambilDataDariLocalStorage(namaPenyimpananLocalStorage.tugas, []),
  daftarSemuaJadwal: ambilDataDariLocalStorage(namaPenyimpananLocalStorage.jadwal, []),

  /*
    Daftar mata kuliah diambil dari localStorage.
    Kalau belum ada (pertama kali buka), pakai daftar default di atas.
    Setelah diambil, data ini disimpan ulang ke localStorage
    supaya tersedia di sesi berikutnya.
  */
  daftarMataKuliah: ambilDataDariLocalStorage(namaPenyimpananLocalStorage.daftarMataKuliah, null),

  kataKunciPencarianTugas: "",
  filterStatusTugas: "semua",
  filterKategoriJadwal: "semua",
  halamanYangSedangDibuka: "dashboard",
  bulanKalenderYangDibuka: new Date(),
  tanggalJadwalYangDipilih: ubahTanggalMenjadiKode(new Date()),
  notifikasiDeadlineAktif: ambilDataDariLocalStorage(namaPenyimpananLocalStorage.notifikasiDeadline, true),
  modeGelapAktif: ambilDataDariLocalStorage(namaPenyimpananLocalStorage.modeGelap, false)
};

/*
================================
AMBIL ELEMENT HTML
================================
Semua element HTML diambil sekali di awal.
Tujuannya agar tidak berulang-ulang memakai document.getElementById di banyak tempat.
*/
const elemenHalaman = {
  body: document.body,
  semuaTombolMenu: document.querySelectorAll(".tombol-menu"),
  semuaHalaman: document.querySelectorAll(".halaman"),
  semuaTombolNavMobile: document.querySelectorAll(".tombol-nav-mobile"),

  teksSapaan: document.getElementById("teksSapaan"),
  teksSapaanHero: document.getElementById("teksSapaanHero"),
  fotoProfilHeader: document.getElementById("fotoProfilHeader"),
  inisialProfilHeader: document.getElementById("inisialProfilHeader"),
  previewFotoProfil: document.getElementById("previewFotoProfil"),
  inisialPreviewProfil: document.getElementById("inisialPreviewProfil"),
  inputFotoProfil: document.getElementById("inputFotoProfil"),
  tombolUploadFoto: document.getElementById("tombolUploadFoto"),
  tombolHapusFoto: document.getElementById("tombolHapusFoto"),
  pesanErrorFotoProfil: document.getElementById("pesanErrorFotoProfil"),

  angkaTotalTugas: document.getElementById("angkaTotalTugas"),
  angkaTugasBelumSelesai: document.getElementById("angkaTugasBelumSelesai"),
  angkaTugasSelesai: document.getElementById("angkaTugasSelesai"),
  angkaDeadlineDekat: document.getElementById("angkaDeadlineDekat"),
  daftarNotifikasiDeadline: document.getElementById("daftarNotifikasiDeadline"),
  daftarTugasTerbaru: document.getElementById("daftarTugasTerbaru"),
  teksTanggalRealtime: document.getElementById("teksTanggalRealtime"),
  teksJamRealtime: document.getElementById("teksJamRealtime"),
  teksTanggalHero: document.getElementById("teksTanggalHero"),
  persenProgressHariIni: document.getElementById("persenProgressHariIni"),
  barProgressHariIni: document.getElementById("barProgressHariIni"),
  teksProgressHariIni: document.getElementById("teksProgressHariIni"),
  angkaFokusHariIni: document.getElementById("angkaFokusHariIni"),
  ringkasanSidebar: document.getElementById("ringkasanSidebar"),
  jumlahTugasHariIni: document.getElementById("jumlahTugasHariIni"),
  jumlahTugasBesok: document.getElementById("jumlahTugasBesok"),
  jumlahTugasSelesaiDashboard: document.getElementById("jumlahTugasSelesaiDashboard"),
  daftarTugasHariIni: document.getElementById("daftarTugasHariIni"),
  daftarTugasBesok: document.getElementById("daftarTugasBesok"),
  daftarTugasSelesaiDashboard: document.getElementById("daftarTugasSelesaiDashboard"),
  judulMiniKalender: document.getElementById("judulMiniKalender"),
  isiMiniKalender: document.getElementById("isiMiniKalender"),
  semuaTombolAksiCepat: document.querySelectorAll("[data-quick-action]"),

  formTambahTugas: document.getElementById("formTambahTugas"),
  inputNamaTugas: document.getElementById("inputNamaTugas"),
  pilihanMataKuliah: document.getElementById("pilihanMataKuliah"),
  inputDeadlineTugas: document.getElementById("inputDeadlineTugas"),
  pesanErrorNamaTugas: document.getElementById("pesanErrorNamaTugas"),
  pesanErrorMataKuliah: document.getElementById("pesanErrorMataKuliah"),
  pesanErrorDeadlineTugas: document.getElementById("pesanErrorDeadlineTugas"),
  inputPencarianTugas: document.getElementById("inputPencarianTugas"),
  filterStatusTugas: document.getElementById("filterStatusTugas"),
  daftarTugas: document.getElementById("daftarTugas"),

  teksBulanKalender: document.getElementById("teksBulanKalender"),
  tombolBulanSebelumnya: document.getElementById("tombolBulanSebelumnya"),
  tombolBulanBerikutnya: document.getElementById("tombolBulanBerikutnya"),
  tombolHariIni: document.getElementById("tombolHariIni"),
  tombolTambahJadwalCepat: document.getElementById("tombolTambahJadwalCepat"),
  filterKategoriJadwal: document.getElementById("filterKategoriJadwal"),
  isiKalender: document.getElementById("isiKalender"),
  daftarAgendaHariIni: document.getElementById("daftarAgendaHariIni"),
  daftarReminderDeadline: document.getElementById("daftarReminderDeadline"),

  inputNamaPengguna: document.getElementById("inputNamaPengguna"),
  tombolSimpanNama: document.getElementById("tombolSimpanNama"),
  toggleModeGelap: document.getElementById("toggleModeGelap"),
  toggleNotifikasiDeadline: document.getElementById("toggleNotifikasiDeadline"),
  tombolResetData: document.getElementById("tombolResetData"),

  // Elemen baru untuk kelola mata kuliah di halaman Pengaturan
  inputNamaMataKuliah: document.getElementById("inputNamaMataKuliah"),
  tombolTambahMataKuliah: document.getElementById("tombolTambahMataKuliah"),
  pesanErrorMataKuliahBaru: document.getElementById("pesanErrorMataKuliahBaru"),
  daftarMataKuliahPengaturan: document.getElementById("daftarMataKuliahPengaturan"),

  modalKonfirmasi: document.getElementById("modalKonfirmasi"),
  judulModalKonfirmasi: document.getElementById("judulModalKonfirmasi"),
  pesanModalKonfirmasi: document.getElementById("pesanModalKonfirmasi"),
  tombolBatalKonfirmasi: document.getElementById("tombolBatalKonfirmasi"),
  tombolSetujuKonfirmasi: document.getElementById("tombolSetujuKonfirmasi"),

  modalJadwal: document.getElementById("modalJadwal"),
  formTambahJadwal: document.getElementById("formTambahJadwal"),
  teksTanggalJadwalDipilih: document.getElementById("teksTanggalJadwalDipilih"),
  inputNamaJadwal: document.getElementById("inputNamaJadwal"),
  inputTanggalJadwal: document.getElementById("inputTanggalJadwal"),
  inputJamJadwal: document.getElementById("inputJamJadwal"),
  pilihanKategoriJadwal: document.getElementById("pilihanKategoriJadwal"),
  pesanErrorNamaJadwal: document.getElementById("pesanErrorNamaJadwal"),
  pesanErrorTanggalJadwal: document.getElementById("pesanErrorTanggalJadwal"),
  pesanErrorJamJadwal: document.getElementById("pesanErrorJamJadwal"),
  tombolBatalJadwal: document.getElementById("tombolBatalJadwal"),

  tombolTambahCepatMobile: document.getElementById("tombolTambahCepatMobile"),
  wadahToast: document.getElementById("wadahToast")
};

let fungsiJawabanModalKonfirmasi = null;
const BATAS_RENDER_TUGAS = 80;
let idDebouncePencarianTugas = null;
let cacheRenderKalender = "";

/*
================================
FUNGSI BANTUAN LOCAL STORAGE
================================
Bagian ini hanya bertugas mengambil dan menyimpan data ke browser.
*/
function ambilDataDariLocalStorage(namaData, nilaiDefault) {
  const dataTersimpan = localStorage.getItem(namaData);

  if (dataTersimpan === null) {
    return nilaiDefault;
  }

  try {
    return JSON.parse(dataTersimpan);
  } catch (error) {
    return dataTersimpan;
  }
}

function simpanDataKeLocalStorage(namaData, isiData) {
  localStorage.setItem(namaData, JSON.stringify(isiData));
}

function simpanDaftarTugas() {
  simpanDataKeLocalStorage(namaPenyimpananLocalStorage.tugas, dataAplikasi.daftarSemuaTugas);
}

function simpanDaftarJadwal() {
  simpanDataKeLocalStorage(namaPenyimpananLocalStorage.jadwal, dataAplikasi.daftarSemuaJadwal);
}

/*
================================
FUNGSI BANTUAN TANGGAL
================================
Tanggal dibuat dalam format yyyy-mm-dd agar mudah dibandingkan.
*/
function ubahTanggalMenjadiKode(tanggal) {
  const tahun = tanggal.getFullYear();
  const bulan = String(tanggal.getMonth() + 1).padStart(2, "0");
  const hari = String(tanggal.getDate()).padStart(2, "0");

  return `${tahun}-${bulan}-${hari}`;
}

function ubahKodeMenjadiTanggal(kodeTanggal) {
  const bagianTanggal = kodeTanggal.split("-");
  const tahun = Number(bagianTanggal[0]);
  const bulan = Number(bagianTanggal[1]);
  const hari = Number(bagianTanggal[2]);

  return new Date(tahun, bulan - 1, hari);
}

function cekApakahTanggalSama(tanggalPertama, tanggalKedua) {
  return ubahTanggalMenjadiKode(tanggalPertama) === ubahTanggalMenjadiKode(tanggalKedua);
}

function formatTanggalIndonesia(kodeTanggal) {
  return new Intl.DateTimeFormat("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric"
  }).format(ubahKodeMenjadiTanggal(kodeTanggal));
}

function hitungSisaHari(kodeTanggalDeadline) {
  const hariIni = new Date();
  hariIni.setHours(0, 0, 0, 0);

  const tanggalDeadline = ubahKodeMenjadiTanggal(kodeTanggalDeadline);
  tanggalDeadline.setHours(0, 0, 0, 0);

  const selisihWaktu = tanggalDeadline - hariIni;
  return Math.ceil(selisihWaktu / (1000 * 60 * 60 * 24));
}

function buatTeksSisaDeadline(kodeTanggalDeadline) {
  const sisaHari = hitungSisaHari(kodeTanggalDeadline);

  if (sisaHari < 0) {
    return "Terlewat";
  }

  if (sisaHari === 0) {
    return "Hari ini";
  }

  if (sisaHari === 1) {
    return "Besok";
  }

  return `${sisaHari} hari lagi`;
}

function formatTanggalPanjangIndonesia(tanggal) {
  return new Intl.DateTimeFormat("id-ID", {
    weekday: "long",
    day: "2-digit",
    month: "long",
    year: "numeric"
  }).format(tanggal);
}

function tampilkanTanggalRealtime() {
  const sekarang = new Date();
  const teksTanggal = formatTanggalPanjangIndonesia(sekarang);
  const teksJam = new Intl.DateTimeFormat("id-ID", {
    hour: "2-digit",
    minute: "2-digit"
  }).format(sekarang);

  if (elemenHalaman.teksTanggalRealtime) {
    elemenHalaman.teksTanggalRealtime.textContent = teksTanggal;
  }

  if (elemenHalaman.teksJamRealtime) {
    elemenHalaman.teksJamRealtime.textContent = teksJam;
  }

  if (elemenHalaman.teksTanggalHero) {
    elemenHalaman.teksTanggalHero.textContent = teksTanggal;
  }
}

/*
================================
FUNGSI BANTUAN KEAMANAN TEKS
================================
Teks dari input user diamankan sebelum dimasukkan ke innerHTML.
Ini mencegah HTML dari input user ikut terbaca sebagai kode.
*/
function amankanTeksUntukHtml(teks) {
  return String(teks)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function buatTampilanKosong(pesan) {
  return `<div class="kotak-kosong">${pesan}</div>`;
}

function tampilkanToast(pesan) {
  const toastBaru = document.createElement("div");

  toastBaru.className = "toast";
  toastBaru.textContent = pesan;
  elemenHalaman.wadahToast.appendChild(toastBaru);

  setTimeout(function () {
    toastBaru.classList.add("toast-keluar");
  }, 2400);

  setTimeout(function () {
    toastBaru.remove();
  }, 2800);
}

/*
================================
FUNGSI DATA TUGAS
================================
Bagian ini hanya mengurus data tugas: membuat, menambah, menghapus, dan mengubah status.
*/
function buatDataTugas(namaTugas, mataKuliah, deadline) {
  return {
    id: Date.now().toString(),
    namaTugas: namaTugas,
    mataKuliah: mataKuliah,
    deadline: deadline,
    sudahSelesai: false,
    dibuatPada: new Date().toISOString()
  };
}

function tambahTugasKeData(dataTugasBaru) {
  dataAplikasi.daftarSemuaTugas.unshift(dataTugasBaru);
  simpanDaftarTugas();
}

function hapusTugasDariData(idTugas) {
  dataAplikasi.daftarSemuaTugas = dataAplikasi.daftarSemuaTugas.filter(function (tugas) {
    return tugas.id !== idTugas;
  });

  simpanDaftarTugas();
}

function ubahStatusSelesaiTugas(idTugas) {
  dataAplikasi.daftarSemuaTugas = dataAplikasi.daftarSemuaTugas.map(function (tugas) {
    if (tugas.id !== idTugas) {
      return tugas;
    }

    return {
      id: tugas.id,
      namaTugas: tugas.namaTugas,
      mataKuliah: tugas.mataKuliah,
      deadline: tugas.deadline,
      sudahSelesai: !tugas.sudahSelesai,
      dibuatPada: tugas.dibuatPada
    };
  });

  simpanDaftarTugas();
}

function ambilTugasYangDeadlineDekat() {
  return dataAplikasi.daftarSemuaTugas.filter(function (tugas) {
    const sisaHari = hitungSisaHari(tugas.deadline);
    return !tugas.sudahSelesai && sisaHari >= 0 && sisaHari <= 2;
  });
}

function ambilTugasSesuaiFilter() {
  const kataKunci = dataAplikasi.kataKunciPencarianTugas.toLowerCase();

  return dataAplikasi.daftarSemuaTugas.filter(function (tugas) {
    const cocokDenganKataKunci =
      tugas.namaTugas.toLowerCase().includes(kataKunci) ||
      tugas.mataKuliah.toLowerCase().includes(kataKunci);

    const cocokDenganStatus =
      dataAplikasi.filterStatusTugas === "semua" ||
      (dataAplikasi.filterStatusTugas === "selesai" && tugas.sudahSelesai) ||
      (dataAplikasi.filterStatusTugas === "belum" && !tugas.sudahSelesai);

    return cocokDenganKataKunci && cocokDenganStatus;
  });
}

/*
================================
VALIDASI FORM TUGAS
================================
Validasi berjalan saat user menekan tombol Tambah Tugas.
*/
function tampilkanErrorInput(inputElement, pesanElement, pesan) {
  inputElement.classList.add("input-error");
  pesanElement.textContent = pesan;
}

function bersihkanErrorFormTugas() {
  elemenHalaman.inputNamaTugas.classList.remove("input-error");
  elemenHalaman.pilihanMataKuliah.classList.remove("input-error");
  elemenHalaman.inputDeadlineTugas.classList.remove("input-error");

  elemenHalaman.pesanErrorNamaTugas.textContent = "";
  elemenHalaman.pesanErrorMataKuliah.textContent = "";
  elemenHalaman.pesanErrorDeadlineTugas.textContent = "";
}

function validasiFormTugas() {
  bersihkanErrorFormTugas();

  const namaTugas = elemenHalaman.inputNamaTugas.value.trim();
  const mataKuliah = elemenHalaman.pilihanMataKuliah.value;
  const deadline = elemenHalaman.inputDeadlineTugas.value;
  let formValid = true;

  if (namaTugas.length < 3) {
    tampilkanErrorInput(elemenHalaman.inputNamaTugas, elemenHalaman.pesanErrorNamaTugas, "Nama tugas minimal 3 karakter.");
    formValid = false;
  }

  if (mataKuliah === "") {
    tampilkanErrorInput(elemenHalaman.pilihanMataKuliah, elemenHalaman.pesanErrorMataKuliah, "Pilih mata kuliah terlebih dahulu.");
    formValid = false;
  }

  if (deadline === "") {
    tampilkanErrorInput(elemenHalaman.inputDeadlineTugas, elemenHalaman.pesanErrorDeadlineTugas, "Pilih tanggal deadline.");
    formValid = false;
  } else if (hitungSisaHari(deadline) < 0) {
    tampilkanErrorInput(elemenHalaman.inputDeadlineTugas, elemenHalaman.pesanErrorDeadlineTugas, "Deadline tidak boleh tanggal yang sudah lewat.");
    formValid = false;
  }

  return {
    formValid: formValid,
    namaTugas: namaTugas,
    mataKuliah: mataKuliah,
    deadline: deadline
  };
}

/*
================================
FUNGSI DATA JADWAL KALENDER
================================
Jadwal manual dan deadline tugas digabung agar tampil dalam kalender yang sama.
*/
function buatDataJadwal(namaJadwal, tanggalJadwal, jamJadwal, kategoriJadwal) {
  return {
    id: Date.now().toString(),
    namaJadwal: namaJadwal,
    tanggalJadwal: tanggalJadwal,
    jamJadwal: jamJadwal,
    kategoriJadwal: kategoriJadwal,
    dibuatPada: new Date().toISOString()
  };
}

function tambahJadwalKeData(dataJadwalBaru) {
  dataAplikasi.daftarSemuaJadwal.unshift(dataJadwalBaru);
  simpanDaftarJadwal();
}

/*
  hapusJadwalDariData(idJadwal)
  --------------------------------
  FITUR BARU: Menghapus satu jadwal manual dari daftar berdasarkan ID-nya.
  Hanya jadwal manual yang bisa dihapus (tipe "jadwal").
  Deadline tugas tidak bisa dihapus dari sini - harus dari halaman Tugas.
*/
function hapusJadwalDariData(idJadwal) {
  // Saring: simpan semua jadwal KECUALI yang ID-nya cocok
  dataAplikasi.daftarSemuaJadwal = dataAplikasi.daftarSemuaJadwal.filter(function (jadwal) {
    return jadwal.id !== idJadwal;
  });

  // Simpan perubahan ke localStorage
  simpanDaftarJadwal();
}

function ambilSemuaItemKalender() {
  const daftarJadwalManual = dataAplikasi.daftarSemuaJadwal.map(function (jadwal) {
    return {
      id: jadwal.id,
      judul: jadwal.namaJadwal,
      tanggal: jadwal.tanggalJadwal,
      jam: jadwal.jamJadwal,
      kategori: jadwal.kategoriJadwal,
      tipe: "jadwal",
      warna: daftarKategoriJadwal[jadwal.kategoriJadwal].warna
    };
  });

  const daftarDeadlineTugas = dataAplikasi.daftarSemuaTugas.map(function (tugas) {
    return {
      id: tugas.id,
      judul: tugas.namaTugas,
      tanggal: tugas.deadline,
      jam: "23:59",
      kategori: "deadline",
      tipe: "deadline",
      mataKuliah: tugas.mataKuliah,
      sudahSelesai: tugas.sudahSelesai,
      warna: daftarKategoriJadwal.deadline.warna
    };
  });

  const daftarGabungan = daftarJadwalManual.concat(daftarDeadlineTugas);

  return daftarGabungan.filter(function (itemKalender) {
    return dataAplikasi.filterKategoriJadwal === "semua" || itemKalender.kategori === dataAplikasi.filterKategoriJadwal;
  });
}

function ambilItemKalenderBerdasarkanTanggal(kodeTanggal) {
  const daftarItemKalender = ambilSemuaItemKalender();

  return daftarItemKalender
    .filter(function (itemKalender) {
      return itemKalender.tanggal === kodeTanggal;
    })
    .sort(function (itemPertama, itemKedua) {
      return itemPertama.jam.localeCompare(itemKedua.jam);
    });
}

function buatPetaItemKalenderBerdasarkanTanggal() {
  return ambilSemuaItemKalender().reduce(function (petaItem, itemKalender) {
    if (!petaItem[itemKalender.tanggal]) {
      petaItem[itemKalender.tanggal] = [];
    }

    petaItem[itemKalender.tanggal].push(itemKalender);
    return petaItem;
  }, {});
}

function urutkanPetaItemKalender(petaItem) {
  Object.keys(petaItem).forEach(function (kodeTanggal) {
    petaItem[kodeTanggal].sort(function (itemPertama, itemKedua) {
      return itemPertama.jam.localeCompare(itemKedua.jam);
    });
  });
}

/*
================================
VALIDASI FORM JADWAL
================================
Validasi berjalan saat modal jadwal disubmit.
*/
function bersihkanErrorFormJadwal() {
  elemenHalaman.inputNamaJadwal.classList.remove("input-error");
  elemenHalaman.inputTanggalJadwal.classList.remove("input-error");
  elemenHalaman.inputJamJadwal.classList.remove("input-error");

  elemenHalaman.pesanErrorNamaJadwal.textContent = "";
  elemenHalaman.pesanErrorTanggalJadwal.textContent = "";
  elemenHalaman.pesanErrorJamJadwal.textContent = "";
}

function validasiFormJadwal() {
  bersihkanErrorFormJadwal();

  const namaJadwal = elemenHalaman.inputNamaJadwal.value.trim();
  const tanggalJadwal = elemenHalaman.inputTanggalJadwal.value;
  const jamJadwal = elemenHalaman.inputJamJadwal.value;
  const kategoriJadwal = elemenHalaman.pilihanKategoriJadwal.value;
  let formValid = true;

  if (namaJadwal.length < 3) {
    tampilkanErrorInput(elemenHalaman.inputNamaJadwal, elemenHalaman.pesanErrorNamaJadwal, "Nama kegiatan minimal 3 karakter.");
    formValid = false;
  }

  if (tanggalJadwal === "") {
    tampilkanErrorInput(elemenHalaman.inputTanggalJadwal, elemenHalaman.pesanErrorTanggalJadwal, "Tanggal wajib diisi.");
    formValid = false;
  }

  if (jamJadwal === "") {
    tampilkanErrorInput(elemenHalaman.inputJamJadwal, elemenHalaman.pesanErrorJamJadwal, "Jam wajib diisi.");
    formValid = false;
  }

  return {
    formValid: formValid,
    namaJadwal: namaJadwal,
    tanggalJadwal: tanggalJadwal,
    jamJadwal: jamJadwal,
    kategoriJadwal: kategoriJadwal
  };
}

/*
================================
FUNGSI TAMPILKAN DASHBOARD
================================
Dashboard membaca data tugas, lalu menampilkan angka statistik.
*/
function tampilkanDashboard() {
  const totalTugas = dataAplikasi.daftarSemuaTugas.length;
  const totalTugasSelesai = dataAplikasi.daftarSemuaTugas.filter(function (tugas) {
    return tugas.sudahSelesai;
  }).length;
  const daftarDeadlineDekat = ambilTugasYangDeadlineDekat();
  const progressMingguan = totalTugas === 0 ? 0 : Math.round((totalTugasSelesai / totalTugas) * 100);

  elemenHalaman.angkaTotalTugas.textContent = totalTugas;
  elemenHalaman.angkaTugasSelesai.textContent = totalTugasSelesai;
  elemenHalaman.angkaTugasBelumSelesai.textContent = `${progressMingguan}%`;
  elemenHalaman.angkaDeadlineDekat.textContent = daftarDeadlineDekat.length;

  tampilkanNotifikasiDeadline(daftarDeadlineDekat);
  tampilkanTugasTerbaru();
  tampilkanRingkasanProduktivitas();
  tampilkanKelompokTugasDashboard();
  tampilkanMiniKalenderDashboard();
}

function tampilkanRingkasanProduktivitas() {
  const kodeHariIni = ubahTanggalMenjadiKode(new Date());
  const tugasHariIni = dataAplikasi.daftarSemuaTugas.filter(function (tugas) {
    return tugas.deadline === kodeHariIni;
  });
  const tugasHariIniSelesai = tugasHariIni.filter(function (tugas) {
    return tugas.sudahSelesai;
  }).length;
  const tugasAktif = dataAplikasi.daftarSemuaTugas.filter(function (tugas) {
    return !tugas.sudahSelesai;
  }).length;
  const persenProgress = tugasHariIni.length === 0
    ? 0
    : Math.round((tugasHariIniSelesai / tugasHariIni.length) * 100);

  if (elemenHalaman.persenProgressHariIni) {
    elemenHalaman.persenProgressHariIni.textContent = `${persenProgress}%`;
  }

  if (elemenHalaman.barProgressHariIni) {
    elemenHalaman.barProgressHariIni.style.transform = `scaleX(${persenProgress / 100})`;
  }

  if (elemenHalaman.teksProgressHariIni) {
    elemenHalaman.teksProgressHariIni.textContent = tugasHariIni.length === 0
      ? "Belum ada deadline hari ini. Ruang fokus masih lega."
      : `${tugasHariIniSelesai} dari ${tugasHariIni.length} tugas hari ini selesai.`;
  }

  if (elemenHalaman.angkaFokusHariIni) {
    elemenHalaman.angkaFokusHariIni.textContent = tugasHariIni.filter(function (tugas) {
      return !tugas.sudahSelesai;
    }).length;
  }

  if (elemenHalaman.ringkasanSidebar) {
    elemenHalaman.ringkasanSidebar.textContent = `${tugasAktif} tugas aktif`;
  }
}

function buatHtmlItemRingkasTugas(tugas) {
  const kelasStatus = tugas.sudahSelesai ? "label-sukses" : "label-peringatan";
  const teksStatus = tugas.sudahSelesai ? "Selesai" : buatTeksSisaDeadline(tugas.deadline);

  return `
    <div class="item-kalender ${tugas.sudahSelesai ? "tugas-selesai" : ""}">
      <span class="label-status ${kelasStatus}">${teksStatus}</span>
      <div>
        <strong>${amankanTeksUntukHtml(tugas.namaTugas)}</strong>
        <p>${amankanTeksUntukHtml(tugas.mataKuliah)} - ${formatTanggalIndonesia(tugas.deadline)}</p>
      </div>
    </div>
  `;
}

function tampilkanKelompokTugasDashboard() {
  const kodeHariIni = ubahTanggalMenjadiKode(new Date());
  const tanggalBesok = new Date();
  tanggalBesok.setDate(tanggalBesok.getDate() + 1);
  const kodeBesok = ubahTanggalMenjadiKode(tanggalBesok);

  const tugasHariIni = dataAplikasi.daftarSemuaTugas.filter(function (tugas) {
    return tugas.deadline === kodeHariIni && !tugas.sudahSelesai;
  });
  const tugasBesok = dataAplikasi.daftarSemuaTugas.filter(function (tugas) {
    return tugas.deadline === kodeBesok && !tugas.sudahSelesai;
  });
  const tugasSelesai = dataAplikasi.daftarSemuaTugas.filter(function (tugas) {
    return tugas.sudahSelesai;
  }).slice(0, 4);

  if (elemenHalaman.jumlahTugasHariIni) {
    elemenHalaman.jumlahTugasHariIni.textContent = tugasHariIni.length;
  }

  if (elemenHalaman.jumlahTugasBesok) {
    elemenHalaman.jumlahTugasBesok.textContent = tugasBesok.length;
  }

  if (elemenHalaman.jumlahTugasSelesaiDashboard) {
    elemenHalaman.jumlahTugasSelesaiDashboard.textContent = tugasSelesai.length;
  }

  if (elemenHalaman.daftarTugasHariIni) {
    elemenHalaman.daftarTugasHariIni.innerHTML = tugasHariIni.length === 0
      ? buatTampilanKosong("Tidak ada deadline hari ini.")
      : tugasHariIni.slice(0, 4).map(buatHtmlItemRingkasTugas).join("");
  }

  if (elemenHalaman.daftarTugasBesok) {
    elemenHalaman.daftarTugasBesok.innerHTML = tugasBesok.length === 0
      ? buatTampilanKosong("Belum ada deadline besok.")
      : tugasBesok.slice(0, 4).map(buatHtmlItemRingkasTugas).join("");
  }

  if (elemenHalaman.daftarTugasSelesaiDashboard) {
    elemenHalaman.daftarTugasSelesaiDashboard.innerHTML = tugasSelesai.length === 0
      ? buatTampilanKosong("Belum ada tugas selesai.")
      : tugasSelesai.map(buatHtmlItemRingkasTugas).join("");
  }
}

function tampilkanMiniKalenderDashboard() {
  if (!elemenHalaman.isiMiniKalender || !elemenHalaman.judulMiniKalender) {
    return;
  }

  const petaItemKalender = buatPetaItemKalenderBerdasarkanTanggal();
  const hariIni = new Date();
  const tahun = hariIni.getFullYear();
  const bulan = hariIni.getMonth();
  const tanggalPertama = new Date(tahun, bulan, 1);
  const jumlahHari = new Date(tahun, bulan + 1, 0).getDate();
  const offsetAwal = tanggalPertama.getDay();
  let isiHtml = "";

  elemenHalaman.judulMiniKalender.textContent = new Intl.DateTimeFormat("id-ID", {
    month: "long",
    year: "numeric"
  }).format(hariIni);

  for (let kosong = 0; kosong < offsetAwal; kosong++) {
    isiHtml += "<span></span>";
  }

  for (let tanggal = 1; tanggal <= jumlahHari; tanggal++) {
    const tanggalAktif = new Date(tahun, bulan, tanggal);
    const kodeTanggal = ubahTanggalMenjadiKode(tanggalAktif);
    const adaAgenda = Boolean(petaItemKalender[kodeTanggal]);
    const kelasHariIni = cekApakahTanggalSama(tanggalAktif, hariIni) ? "mini-hari-ini" : "mini-tanggal-aktif";
    const kelasAgenda = adaAgenda ? " mini-ada-agenda" : "";

    isiHtml += `<span class="${kelasHariIni}${kelasAgenda}">${tanggal}</span>`;
  }

  elemenHalaman.isiMiniKalender.innerHTML = isiHtml;
}

function tampilkanNotifikasiDeadline(daftarDeadlineDekat) {
  if (!dataAplikasi.notifikasiDeadlineAktif) {
    elemenHalaman.daftarNotifikasiDeadline.innerHTML = buatTampilanKosong("Notifikasi deadline sedang dinonaktifkan.");
    return;
  }

  if (daftarDeadlineDekat.length === 0) {
    elemenHalaman.daftarNotifikasiDeadline.innerHTML = buatTampilanKosong("Belum ada deadline dekat.");
    return;
  }

  elemenHalaman.daftarNotifikasiDeadline.innerHTML = daftarDeadlineDekat.map(function (tugas) {
    return `
      <div class="item-kalender">
        <div class="tanggal-kalender">${buatTeksSisaDeadline(tugas.deadline)}</div>
        <div>
          <strong>${amankanTeksUntukHtml(tugas.namaTugas)}</strong>
          <p>${amankanTeksUntukHtml(tugas.mataKuliah)} - ${formatTanggalIndonesia(tugas.deadline)}</p>
        </div>
      </div>
    `;
  }).join("");
}

function tampilkanTugasTerbaru() {
  const daftarTugasTerbaru = dataAplikasi.daftarSemuaTugas.slice(0, 4);

  if (daftarTugasTerbaru.length === 0) {
    elemenHalaman.daftarTugasTerbaru.innerHTML = buatTampilanKosong("Belum ada tugas. Tambahkan tugas pertamamu.");
    return;
  }

  elemenHalaman.daftarTugasTerbaru.innerHTML = daftarTugasTerbaru.map(function (tugas) {
    const kelasStatus = tugas.sudahSelesai ? "label-sukses" : "label-peringatan";
    const teksStatus = tugas.sudahSelesai ? "Selesai" : buatTeksSisaDeadline(tugas.deadline);

    return `
      <div class="item-kalender">
        <span class="label-status ${kelasStatus}">${teksStatus}</span>
        <div>
          <strong>${amankanTeksUntukHtml(tugas.namaTugas)}</strong>
          <p>${amankanTeksUntukHtml(tugas.mataKuliah)}</p>
        </div>
      </div>
    `;
  }).join("");
}

/*
================================
FUNGSI TAMPILKAN DATA TUGAS
================================
Bagian ini mengubah array tugas menjadi tampilan HTML.
*/
function tampilkanDaftarTugas() {
  const daftarTugasYangDitampilkan = ambilTugasSesuaiFilter();
  const daftarTugasTerbatas = daftarTugasYangDitampilkan.slice(0, BATAS_RENDER_TUGAS);

  if (daftarTugasYangDitampilkan.length === 0) {
    elemenHalaman.daftarTugas.innerHTML = buatTampilanKosong("Tidak ada tugas yang sesuai.");
    return;
  }

  const htmlDaftarTugas = daftarTugasTerbatas.map(function (tugas) {
    const kelasTugasSelesai = tugas.sudahSelesai ? "tugas-selesai" : "";
    const kelasStatus = tugas.sudahSelesai ? "label-sukses" : "label-peringatan";
    const teksStatus = tugas.sudahSelesai ? "Selesai" : buatTeksSisaDeadline(tugas.deadline);
    const checkboxTercentang = tugas.sudahSelesai ? "checked" : "";

    return `
      <article class="item-tugas ${kelasTugasSelesai}" data-id-tugas="${tugas.id}">
        <div class="bagian-utama-tugas">
          <input class="checkbox-tugas" type="checkbox" ${checkboxTercentang} aria-label="Tandai selesai">
          <div class="konten-tugas">
            <p class="judul-tugas">${amankanTeksUntukHtml(tugas.namaTugas)}</p>
            <div class="info-tugas">
              <span class="meta-tugas">${amankanTeksUntukHtml(tugas.mataKuliah)}</span>
              <span class="pemisah-meta" aria-hidden="true"></span>
              <span class="meta-tugas">${formatTanggalIndonesia(tugas.deadline)}</span>
            </div>
          </div>
        </div>
        <div class="aksi-tugas">
          <span class="label-status ${kelasStatus}">${teksStatus}</span>
          <button class="tombol-kecil tombol-hapus" type="button" data-aksi="hapus-tugas">Hapus</button>
        </div>
      </article>
    `;
  }).join("");

  const htmlBatasRender = daftarTugasYangDitampilkan.length > BATAS_RENDER_TUGAS
    ? `<div class="kotak-kosong">Menampilkan ${BATAS_RENDER_TUGAS} dari ${daftarTugasYangDitampilkan.length} tugas. Gunakan pencarian atau filter agar daftar lebih spesifik.</div>`
    : "";

  elemenHalaman.daftarTugas.innerHTML = htmlDaftarTugas + htmlBatasRender;
}

/*
================================
FUNGSI TAMPILKAN KALENDER
================================
Kalender dibuat dari 42 kotak tanggal agar bentuknya selalu rapi.
*/
function tampilkanKalender() {
  const kunciRenderKalender = [
    dataAplikasi.bulanKalenderYangDibuka.getFullYear(),
    dataAplikasi.bulanKalenderYangDibuka.getMonth(),
    dataAplikasi.filterKategoriJadwal,
    dataAplikasi.daftarSemuaTugas.length,
    dataAplikasi.daftarSemuaJadwal.length,
    dataAplikasi.daftarSemuaTugas.filter(function (tugas) { return tugas.sudahSelesai; }).length
  ].join("|");

  if (cacheRenderKalender === kunciRenderKalender) {
    return;
  }

  cacheRenderKalender = kunciRenderKalender;
  tampilkanJudulBulanKalender();
  tampilkanKotakTanggalKalender();
  tampilkanAgendaHariIni();
  tampilkanReminderDeadline();
}

function tampilkanJudulBulanKalender() {
  elemenHalaman.teksBulanKalender.textContent = new Intl.DateTimeFormat("id-ID", {
    month: "long",
    year: "numeric"
  }).format(dataAplikasi.bulanKalenderYangDibuka);
}

function tampilkanKotakTanggalKalender() {
  const tahunAktif = dataAplikasi.bulanKalenderYangDibuka.getFullYear();
  const bulanAktif = dataAplikasi.bulanKalenderYangDibuka.getMonth();
  const tanggalPertamaBulan = new Date(tahunAktif, bulanAktif, 1);
  const tanggalAwalKalender = new Date(tanggalPertamaBulan);
  const petaItemKalender = buatPetaItemKalenderBerdasarkanTanggal();

  tanggalAwalKalender.setDate(tanggalAwalKalender.getDate() - tanggalPertamaBulan.getDay());
  urutkanPetaItemKalender(petaItemKalender);

  let isiHtmlKalender = "";

  for (let urutanTanggal = 0; urutanTanggal < 42; urutanTanggal++) {
    const tanggalUntukKotak = new Date(tanggalAwalKalender);
    tanggalUntukKotak.setDate(tanggalAwalKalender.getDate() + urutanTanggal);

    isiHtmlKalender += buatHtmlKotakTanggal(tanggalUntukKotak, bulanAktif, petaItemKalender);
  }

  elemenHalaman.isiKalender.innerHTML = isiHtmlKalender;
}

function buatHtmlKotakTanggal(tanggalUntukKotak, bulanAktif, petaItemKalender) {
  const kodeTanggal = ubahTanggalMenjadiKode(tanggalUntukKotak);
  const daftarItemTanggalIni = petaItemKalender[kodeTanggal] || [];
  const daftarItemYangDitampilkan = daftarItemTanggalIni.slice(0, 3);
  const jumlahItemTersembunyi = daftarItemTanggalIni.length - daftarItemYangDitampilkan.length;
  const kelasHariIni = cekApakahTanggalSama(tanggalUntukKotak, new Date()) ? "tanggal-hari-ini" : "";
  const kelasLuarBulan = tanggalUntukKotak.getMonth() !== bulanAktif ? "tanggal-luar-bulan" : "";

  return `
    <button class="kotak-tanggal ${kelasHariIni} ${kelasLuarBulan}" type="button" data-tanggal="${kodeTanggal}">
      <span class="kepala-tanggal">
        <span class="angka-tanggal">${tanggalUntukKotak.getDate()}</span>
        ${daftarItemTanggalIni.length > 0 ? '<span class="titik-jadwal"></span>' : ""}
      </span>
      <span class="daftar-jadwal-di-tanggal">
        ${daftarItemYangDitampilkan.map(buatHtmlLabelJadwal).join("")}
        ${jumlahItemTersembunyi > 0 ? `<span class="jumlah-jadwal-lain">+${jumlahItemTersembunyi} jadwal lain</span>` : ""}
      </span>
    </button>
  `;
}

function buatHtmlLabelJadwal(itemKalender) {
  const teksJam = itemKalender.tipe === "deadline" ? "DL" : itemKalender.jam;
  const kelasDeadline = itemKalender.tipe === "deadline" ? "label-deadline" : "";

  return `
    <span class="label-jadwal kategori-${itemKalender.kategori} ${kelasDeadline}">
      ${teksJam} ${amankanTeksUntukHtml(itemKalender.judul)}
    </span>
  `;
}

function tampilkanAgendaHariIni() {
  const kodeHariIni = ubahTanggalMenjadiKode(new Date());
  const daftarAgendaHariIni = ambilItemKalenderBerdasarkanTanggal(kodeHariIni);

  if (daftarAgendaHariIni.length === 0) {
    elemenHalaman.daftarAgendaHariIni.innerHTML = buatTampilanKosong("Tidak ada agenda hari ini.");
    return;
  }

  elemenHalaman.daftarAgendaHariIni.innerHTML = daftarAgendaHariIni.map(buatHtmlItemAgenda).join("");
}

function tampilkanReminderDeadline() {
  const daftarDeadlineDekat = ambilTugasYangDeadlineDekat();

  if (daftarDeadlineDekat.length === 0) {
    elemenHalaman.daftarReminderDeadline.innerHTML = buatTampilanKosong("Deadline dekat belum ada.");
    return;
  }

  elemenHalaman.daftarReminderDeadline.innerHTML = daftarDeadlineDekat.map(function (tugas) {
    const itemDeadline = {
      judul: tugas.namaTugas,
      tanggal: tugas.deadline,
      jam: "23:59",
      kategori: "deadline",
      tipe: "deadline",
      mataKuliah: tugas.mataKuliah
    };

    return buatHtmlItemAgenda(itemDeadline);
  }).join("");
}

function buatHtmlItemAgenda(itemKalender) {
  const dataKategori = daftarKategoriJadwal[itemKalender.kategori];
  let teksKeterangan = `${dataKategori.nama} - ${itemKalender.jam}`;

  if (itemKalender.tipe === "deadline") {
    teksKeterangan = `${itemKalender.mataKuliah || "Tugas"} - ${buatTeksSisaDeadline(itemKalender.tanggal)}`;
  }

  /*
    FITUR BARU: Tombol hapus hanya muncul untuk jadwal manual (tipe "jadwal").
    Deadline tugas tidak bisa dihapus dari kalender - harus dari halaman Tugas.
    data-id-jadwal dipakai oleh event delegation untuk tahu jadwal mana yang dihapus.
  */
  const htmlTombolHapus = itemKalender.tipe === "jadwal"
    ? `<button
         class="tombol-hapus-jadwal"
         type="button"
         data-id-jadwal="${amankanTeksUntukHtml(itemKalender.id)}"
         aria-label="Hapus jadwal ${amankanTeksUntukHtml(itemKalender.judul)}"
       >&#x1F5D1;</button>`
    : "";

  return `
    <article class="item-agenda kategori-${itemKalender.kategori}">
      <div class="isi-item-agenda">
        <strong>${amankanTeksUntukHtml(itemKalender.judul)}</strong>
        <p>${formatTanggalIndonesia(itemKalender.tanggal)} - ${amankanTeksUntukHtml(teksKeterangan)}</p>
      </div>
      ${htmlTombolHapus}
    </article>
  `;
}

/*
================================
MODAL DETAIL TANGGAL
================================
Modal ini dipakai untuk melihat
semua jadwal dalam 1 tanggal.
*/

function tampilkanDetailTanggal(kodeTanggal) {

  // Ambil elemen modal
  const modal = document.getElementById("modalDetailTanggal");

  const judulModal =
    document.getElementById("judulModalDetail");

  const daftarJadwal =
    document.getElementById("daftarJadwalDetailTanggal");

  // -------------------------------------------------------
  // SIMPAN TANGGAL YANG SEDANG DIBUKA
  // -------------------------------------------------------
  // Disimpan ke dataAplikasi agar tombol "+ Tambah jadwal di tanggal ini"
  // bisa tahu tanggal mana yang sedang aktif saat tombol ditekan.
  dataAplikasi.tanggalDetailYangSedangDibuka = kodeTanggal;

  // Ambil semua item kalender di tanggal tersebut
  const daftarItem =
    ambilItemKalenderBerdasarkanTanggal(kodeTanggal);

  // Ubah judul tanggal
  judulModal.textContent =
    formatTanggalIndonesia(kodeTanggal);

  // Kalau tidak ada jadwal
  if (daftarItem.length === 0) {

    daftarJadwal.innerHTML = `
      <div class="kotak-kosong">
        Tidak ada jadwal di tanggal ini.
      </div>
    `;

  } else {

    // Tampilkan semua jadwal
    daftarJadwal.innerHTML =
      daftarItem.map(function (itemKalender) {

        return buatHtmlItemAgenda(itemKalender);

      }).join("");
  }

  // Tampilkan modal
  modal.classList.add("tampil");

  modal.setAttribute(
    "aria-hidden",
    "false"
  );
}

/*
================================
TUTUP MODAL DETAIL
================================
*/

function tutupModalDetailTanggal() {

  const modal =
    document.getElementById("modalDetailTanggal");

  modal.classList.remove("tampil");

  modal.setAttribute(
    "aria-hidden",
    "true"
  );
}

/*
================================
FUNGSI TAMPILKAN SEMUA DATA
================================
Function ini dipanggil setelah data berubah.
Tujuannya agar semua tampilan ikut update.
*/
function tampilkanSemuaDataAplikasi() {
  tampilkanDashboard();

  if (dataAplikasi.halamanYangSedangDibuka === "tugas") {
    tampilkanDaftarTugas();
  }

  if (dataAplikasi.halamanYangSedangDibuka === "kalender") {
    tampilkanKalender();
  }
}

/*
================================
FUNGSI HALAMAN DAN PENGATURAN
================================
Bagian ini mengatur pindah halaman, nama pengguna, dan mode gelap.
*/
function tampilkanHalaman(namaHalaman) {
  dataAplikasi.halamanYangSedangDibuka = namaHalaman;

  elemenHalaman.semuaHalaman.forEach(function (halaman) {
    halaman.classList.toggle("halaman-aktif", halaman.id === namaHalaman);
  });

  elemenHalaman.semuaTombolMenu.forEach(function (tombolMenu) {
    tombolMenu.classList.toggle("aktif", tombolMenu.dataset.halaman === namaHalaman);
  });

  elemenHalaman.semuaTombolNavMobile.forEach(function (tombolMobile) {
    tombolMobile.classList.toggle("aktif", tombolMobile.dataset.halaman === namaHalaman);
  });

  if (namaHalaman === "dashboard") {
    tampilkanDashboard();
  }

  if (namaHalaman === "tugas") {
    tampilkanDaftarTugas();
  }

  if (namaHalaman === "kalender") {
    tampilkanKalender();
  }
}

function simpanNamaPengguna() {
  const namaPengguna = elemenHalaman.inputNamaPengguna.value.trim();

  if (namaPengguna === "") {
    elemenHalaman.inputNamaPengguna.focus();
    return;
  }

  localStorage.setItem(namaPenyimpananLocalStorage.namaPengguna, namaPengguna);
  tampilkanNamaPengguna(namaPengguna);
  tampilkanToast("Nama profil berhasil disimpan.");
}

function tampilkanNamaPengguna(namaPengguna) {
  const teksSapaanBaru = `Selamat datang kembali, ${namaPengguna} 👋`;
  elemenHalaman.teksSapaan.textContent = teksSapaanBaru;

  if (elemenHalaman.teksSapaanHero) {
    elemenHalaman.teksSapaanHero.textContent = teksSapaanBaru;
  }

  tampilkanInisialProfil(namaPengguna);
}

function tampilkanInisialProfil(namaPengguna) {
  const inisial = namaPengguna ? namaPengguna.trim().charAt(0).toUpperCase() : "M";

  elemenHalaman.inisialProfilHeader.textContent = inisial;
  elemenHalaman.inisialPreviewProfil.textContent = inisial;
}

function terapkanModeGelap() {
  elemenHalaman.body.classList.toggle("mode-gelap", dataAplikasi.modeGelapAktif);
  elemenHalaman.toggleModeGelap.checked = dataAplikasi.modeGelapAktif;
}

/*
================================
FUNGSI FOTO PROFIL
================================
File dibaca dengan FileReader, ditampilkan sebagai preview, lalu diresize dengan canvas.
Hasil resize disimpan ke localStorage sebagai teks base64.
*/
function tampilkanFotoProfil(dataFoto) {
  const fotoTersimpan = dataFoto || localStorage.getItem(namaPenyimpananLocalStorage.fotoProfil);
  const teksInisialHeader = elemenHalaman.inisialProfilHeader.textContent || "M";
  const teksInisialPreview = elemenHalaman.inisialPreviewProfil.textContent || "M";

  if (!fotoTersimpan) {
    elemenHalaman.fotoProfilHeader.innerHTML = `<span id="inisialProfilHeader">${teksInisialHeader}</span>`;
    elemenHalaman.previewFotoProfil.innerHTML = `<span id="inisialPreviewProfil">${teksInisialPreview}</span>`;
    elemenHalaman.inisialProfilHeader = document.getElementById("inisialProfilHeader");
    elemenHalaman.inisialPreviewProfil = document.getElementById("inisialPreviewProfil");
    return;
  }

  elemenHalaman.fotoProfilHeader.innerHTML = `<img src="${fotoTersimpan}" alt="Foto profil">`;
  elemenHalaman.previewFotoProfil.innerHTML = `<img src="${fotoTersimpan}" alt="Preview foto profil">`;
}

function tampilkanErrorFotoProfil(pesan) {
  elemenHalaman.pesanErrorFotoProfil.textContent = pesan;
  elemenHalaman.pesanErrorFotoProfil.classList.add("tampil");
}

function bersihkanErrorFotoProfil() {
  elemenHalaman.pesanErrorFotoProfil.textContent = "";
  elemenHalaman.pesanErrorFotoProfil.classList.remove("tampil");
}

function validasiFotoProfil(fileFoto) {
  if (!fileFoto) {
    return "Pilih file gambar terlebih dahulu.";
  }

  if (!aturanFotoProfil.tipeFileYangDiizinkan.includes(fileFoto.type)) {
    return "Format foto harus JPG, JPEG, atau PNG.";
  }

  if (fileFoto.size > aturanFotoProfil.ukuranMaksimal) {
    return "Ukuran foto maksimal 2MB.";
  }

  return "";
}

function bacaFileFotoSebagaiDataUrl(fileFoto) {
  return new Promise(function (berhasil, gagal) {
    const pembacaFile = new FileReader();

    pembacaFile.onload = function () {
      berhasil(pembacaFile.result);
    };

    pembacaFile.onerror = function () {
      gagal(new Error("Gagal membaca file gambar."));
    };

    pembacaFile.readAsDataURL(fileFoto);
  });
}

function perkecilUkuranFoto(dataUrlFoto) {
  return new Promise(function (berhasil, gagal) {
    const gambar = new Image();

    gambar.onload = function () {
      const kanvas = document.createElement("canvas");
      const alatGambar = kanvas.getContext("2d");
      const skalaGambar = Math.min(
        aturanFotoProfil.lebarMaksimal / gambar.width,
        aturanFotoProfil.tinggiMaksimal / gambar.height,
        1
      );

      kanvas.width = Math.round(gambar.width * skalaGambar);
      kanvas.height = Math.round(gambar.height * skalaGambar);
      alatGambar.drawImage(gambar, 0, 0, kanvas.width, kanvas.height);

      berhasil(kanvas.toDataURL("image/jpeg", 0.82));
    };

    gambar.onerror = function () {
      gagal(new Error("File gambar tidak bisa diproses."));
    };

    gambar.src = dataUrlFoto;
  });
}

async function prosesUploadFotoProfil(event) {
  const fileFoto = event.target.files[0];
  const pesanError = validasiFotoProfil(fileFoto);

  bersihkanErrorFotoProfil();

  if (pesanError !== "") {
    tampilkanErrorFotoProfil(pesanError);
    elemenHalaman.inputFotoProfil.value = "";
    return;
  }

  try {
    const fotoAsli = await bacaFileFotoSebagaiDataUrl(fileFoto);
    tampilkanFotoProfil(fotoAsli);

    const fotoYangSudahDiperkecil = await perkecilUkuranFoto(fotoAsli);
    localStorage.setItem(namaPenyimpananLocalStorage.fotoProfil, fotoYangSudahDiperkecil);
    tampilkanFotoProfil(fotoYangSudahDiperkecil);
    tampilkanToast("Foto profil berhasil diperbarui.");
  } catch (error) {
    tampilkanErrorFotoProfil(error.message);
  } finally {
    elemenHalaman.inputFotoProfil.value = "";
  }
}

function hapusFotoProfil() {
  localStorage.removeItem(namaPenyimpananLocalStorage.fotoProfil);
  bersihkanErrorFotoProfil();
  tampilkanFotoProfil("");
  tampilkanToast("Foto profil berhasil dihapus.");
}

/*
================================
FUNGSI MODAL KONFIRMASI
================================
Modal ini menggantikan popup konfirmasi bawaan browser.
*/
function bukaModalKonfirmasi(pengaturanModal) {
  elemenHalaman.judulModalKonfirmasi.textContent = pengaturanModal.judul;
  elemenHalaman.pesanModalKonfirmasi.textContent = pengaturanModal.pesan;
  elemenHalaman.tombolSetujuKonfirmasi.textContent = pengaturanModal.teksSetuju || "Hapus";
  elemenHalaman.tombolBatalKonfirmasi.textContent = pengaturanModal.teksBatal || "Batal";

  elemenHalaman.modalKonfirmasi.classList.add("tampil");
  elemenHalaman.modalKonfirmasi.setAttribute("aria-hidden", "false");
  elemenHalaman.tombolSetujuKonfirmasi.focus();

  return new Promise(function (jawabModal) {
    fungsiJawabanModalKonfirmasi = jawabModal;
  });
}

/*
  tutupModalKonfirmasi(jawabanUser)
  ----------------------------------
  PERBAIKAN BUG 1:
  
  MASALAH SEBELUMNYA:
  Fungsi ini punya guard "if modal tidak tampil, return lebih awal".
  Akibatnya, kalau fungsi dipanggil dua kali (misal: user klik Batal,
  lalu Escape juga terpicu), pemanggilan kedua langsung return —
  padahal fungsiJawabanModalKonfirmasi belum di-null-kan dengan benar.
  
  Tapi bug utama ada di tampilkanDaftarMataKuliahDiPengaturan():
  Di sana, event listener dipasang di DALAM fungsi render, lalu
  langsung dihapus setelah sekali klik. Jadi setelah user hapus
  satu mata kuliah, render ulang → listener baru dipasang → klik
  tombol hapus → modal muncul → klik Batal → listener dihapus oleh
  removeEventListener → render berikutnya tidak bisa klik lagi!
  
  SOLUSI:
  1. Pindahkan event listener daftarMataKuliahPengaturan ke pasangSemuaEventListener()
     menggunakan event delegation yang benar (tidak pakai removeEventListener).
  2. Pastikan tutupModalKonfirmasi SELALU memanggil fungsiJawabanModal
     meski modal tidak sedang tampil, agar Promise tidak menggantung.
*/
function tutupModalKonfirmasi(jawabanUser) {
  // Sembunyikan modal jika sedang tampil
  if (elemenHalaman.modalKonfirmasi.classList.contains("tampil")) {
    elemenHalaman.modalKonfirmasi.classList.remove("tampil");
    elemenHalaman.modalKonfirmasi.setAttribute("aria-hidden", "true");
  }

  /*
    KUNCI PERBAIKAN: Selalu resolve Promise, tidak peduli apakah
    modal tadi tampil atau tidak. Ini mencegah Promise menggantung
    (hanging promise) yang menyebabkan tombol hapus berikutnya mati.
  */
  if (fungsiJawabanModalKonfirmasi) {
    fungsiJawabanModalKonfirmasi(jawabanUser);
    fungsiJawabanModalKonfirmasi = null;
  }
}

async function resetSemuaDataTugas() {
  const userSetujuMenghapus = await bukaModalKonfirmasi({
    judul: "Hapus semua tugas?",
    pesan: "Semua tugas yang tersimpan di browser ini akan dihapus permanen.",
    teksSetuju: "Hapus",
    teksBatal: "Batal"
  });

  if (!userSetujuMenghapus) {
    return;
  }

  dataAplikasi.daftarSemuaTugas = [];
  simpanDaftarTugas();
  tampilkanSemuaDataAplikasi();
  tampilkanToast("Semua tugas berhasil dihapus.");
}

/*
================================
FUNGSI MODAL JADWAL
================================
Modal ini muncul ketika user klik tanggal kalender atau tombol Tambah Jadwal.
*/
function bukaModalTambahJadwal(kodeTanggal) {
  dataAplikasi.tanggalJadwalYangDipilih = kodeTanggal || ubahTanggalMenjadiKode(new Date());

  elemenHalaman.formTambahJadwal.reset();
  bersihkanErrorFormJadwal();
  elemenHalaman.inputTanggalJadwal.value = dataAplikasi.tanggalJadwalYangDipilih;
  elemenHalaman.pilihanKategoriJadwal.value = "kuliah";
  elemenHalaman.teksTanggalJadwalDipilih.textContent = `Tanggal dipilih: ${formatTanggalIndonesia(dataAplikasi.tanggalJadwalYangDipilih)}`;
  elemenHalaman.modalJadwal.classList.add("tampil");
  elemenHalaman.modalJadwal.setAttribute("aria-hidden", "false");
  elemenHalaman.inputNamaJadwal.focus();
}

function tutupModalTambahJadwal() {
  if (!elemenHalaman.modalJadwal.classList.contains("tampil")) {
    return;
  }

  elemenHalaman.modalJadwal.classList.remove("tampil");
  elemenHalaman.modalJadwal.setAttribute("aria-hidden", "true");
  bersihkanErrorFormJadwal();
}

/*
================================
FUNGSI MATA KULIAH
================================
Bagian ini mengurus semua hal terkait mata kuliah:
- Menyimpan dan membaca dari localStorage
- Menambah mata kuliah baru
- Menghapus mata kuliah
- Memperbarui dropdown di form tugas
- Menampilkan daftar di halaman Pengaturan

ALUR DATA:
1. Data mata kuliah disimpan sebagai array string di localStorage
   Contoh: ["Pemrograman Web", "Jaringan Komputer"]

2. Setiap kali data berubah (tambah/hapus), array diperbarui
   lalu disimpan ulang ke localStorage

3. Dropdown di form tugas langsung diperbarui agar sinkron
================================
*/

/*
  simpanDaftarMataKuliah()
  -------------------------
  Menyimpan array daftarMataKuliah ke localStorage.
  Dipanggil setiap kali ada perubahan (tambah atau hapus).
*/
function simpanDaftarMataKuliah() {
  simpanDataKeLocalStorage(
    namaPenyimpananLocalStorage.daftarMataKuliah,
    dataAplikasi.daftarMataKuliah
  );
}

/*
  inisialisasiDaftarMataKuliah()
  --------------------------------
  Dipanggil sekali saat aplikasi pertama kali dibuka.
  Tugasnya: cek apakah sudah ada data mata kuliah di localStorage.
  - Kalau belum ada (null): pakai daftar default dan langsung simpan
  - Kalau sudah ada: gunakan data yang tersimpan
*/
function inisialisasiDaftarMataKuliah() {
  /*
    FITUR 3 — HAPUS MATA KULIAH DEFAULT:
    Sebelumnya, kalau localStorage kosong (null), aplikasi
    otomatis mengisi daftar dengan daftarMataKuliahDefault.
    Sekarang perilaku ini dihapus.

    Kalau user baru pertama kali buka (null):
    → set ke array kosong agar dropdown tetap bersih.
    User harus tambah mata kuliah sendiri di Pengaturan.
  */
  if (dataAplikasi.daftarMataKuliah === null) {
    // Mulai dengan daftar kosong — tidak ada mata kuliah default
    dataAplikasi.daftarMataKuliah = [];

    // Simpan ke localStorage agar sesi berikutnya tidak null lagi
    simpanDaftarMataKuliah();
  }
}

/*
  cekApakahMataKuliahSudahAda(namaMataKuliah)
  ---------------------------------------------
  Mengecek apakah nama mata kuliah sudah ada di daftar.
  Perbandingan tidak peduli huruf besar/kecil agar tidak duplikat.
  Mengembalikan true jika sudah ada, false jika belum.
*/
function cekApakahMataKuliahSudahAda(namaMataKuliah) {
  var namaDalamHurufKecil = namaMataKuliah.toLowerCase().trim();

  return dataAplikasi.daftarMataKuliah.some(function (mataKuliahYangAda) {
    return mataKuliahYangAda.toLowerCase().trim() === namaDalamHurufKecil;
  });
}

/*
  validasiInputMataKuliah(namaInputMataKuliah)
  ---------------------------------------------
  Memeriksa apakah nama mata kuliah yang dimasukkan user valid.
  Aturan validasi:
  - Tidak boleh kosong
  - Minimal 3 karakter
  - Tidak boleh sudah ada di daftar (duplikat)
  Mengembalikan string pesan error, atau string kosong jika valid.
*/
function validasiInputMataKuliah(namaInputMataKuliah) {
  var namaBersih = namaInputMataKuliah.trim();

  if (namaBersih === "") {
    return "Nama mata kuliah tidak boleh kosong.";
  }

  if (namaBersih.length < 3) {
    return "Nama mata kuliah minimal 3 karakter.";
  }

  if (cekApakahMataKuliahSudahAda(namaBersih)) {
    return "Mata kuliah ini sudah ada di daftar.";
  }

  return ""; // Kosong berarti valid, tidak ada error
}

/*
  tambahMataKuliahBaru()
  -----------------------
  Menambahkan satu mata kuliah baru ke dalam daftar.
  Dipanggil saat user klik tombol Tambah.
  Urutan:
  1. Ambil nilai dari input
  2. Validasi
  3. Kalau valid: tambah ke array, simpan, perbarui tampilan
  4. Kalau tidak valid: tampilkan pesan error
*/
function tambahMataKuliahBaru() {
  var namaInputMataKuliah = elemenHalaman.inputNamaMataKuliah.value;
  var namaBersih = namaInputMataKuliah.trim();

  // Hapus pesan error lama sebelum validasi ulang
  elemenHalaman.pesanErrorMataKuliahBaru.textContent = "";
  elemenHalaman.inputNamaMataKuliah.classList.remove("input-error");

  // Jalankan validasi
  var pesanError = validasiInputMataKuliah(namaBersih);

  // Kalau ada error, tampilkan dan berhenti di sini
  if (pesanError !== "") {
    elemenHalaman.pesanErrorMataKuliahBaru.textContent = pesanError;
    elemenHalaman.inputNamaMataKuliah.classList.add("input-error");
    elemenHalaman.inputNamaMataKuliah.focus();
    return;
  }

  // Tambahkan ke array, lalu urutkan alfabetis
  dataAplikasi.daftarMataKuliah.push(namaBersih);
  dataAplikasi.daftarMataKuliah.sort();

  // Simpan perubahan ke localStorage
  simpanDaftarMataKuliah();

  // Bersihkan input agar siap untuk input berikutnya
  elemenHalaman.inputNamaMataKuliah.value = "";
  elemenHalaman.inputNamaMataKuliah.classList.remove("input-error");

  // Perbarui semua tampilan yang berkaitan
  tampilkanDaftarMataKuliahDiPengaturan();
  perbaruiDropdownMataKuliah();

  // Beri feedback ke user
  tampilkanToast("Mata kuliah berhasil ditambahkan.");
  elemenHalaman.inputNamaMataKuliah.focus();
}

/*
  hapusMataKuliah(namaMataKuliah)
  --------------------------------
  Menghapus satu mata kuliah dari daftar berdasarkan namanya.
  Sebelum menghapus, akan muncul konfirmasi modal.
  Kalau user setuju, data dihapus dan tampilan diperbarui.
*/
async function hapusMataKuliah(namaMataKuliah) {
  // Tanyakan konfirmasi ke user sebelum menghapus
  var userSetujuMenghapus = await bukaModalKonfirmasi({
    judul: "Hapus mata kuliah?",
    pesan: '"' + namaMataKuliah + '" akan dihapus dari daftar. Tugas yang sudah ada tidak terpengaruh.',
    teksSetuju: "Hapus",
    teksBatal: "Batal"
  });

  // Kalau user pilih Batal, langsung berhenti
  if (!userSetujuMenghapus) {
    return;
  }

  // Cari elemen item di DOM untuk animasi keluar
  var semuaItemMataKuliah = elemenHalaman.daftarMataKuliahPengaturan.querySelectorAll(".item-mata-kuliah");
  var elemenYangAkanDihapus = null;

  semuaItemMataKuliah.forEach(function (itemElemen) {
    var tombolHapus = itemElemen.querySelector(".tombol-hapus-mata-kuliah");
    if (tombolHapus && tombolHapus.dataset.namaMataKuliah === namaMataKuliah) {
      elemenYangAkanDihapus = itemElemen;
    }
  });

  // Mainkan animasi keluar dulu sebelum benar-benar dihapus dari DOM
  if (elemenYangAkanDihapus) {
    elemenYangAkanDihapus.classList.add("sedang-dihapus");

    // Tunggu animasi selesai (220ms sesuai CSS transition)
    await new Promise(function (selesai) {
      setTimeout(selesai, 220);
    });
  }

  // Hapus dari array: filter hanya yang namanya BUKAN nama yang dihapus
  dataAplikasi.daftarMataKuliah = dataAplikasi.daftarMataKuliah.filter(function (namaMataKuliahYangAda) {
    return namaMataKuliahYangAda !== namaMataKuliah;
  });

  // Simpan perubahan ke localStorage
  simpanDaftarMataKuliah();

  // Perbarui tampilan daftar dan dropdown
  tampilkanDaftarMataKuliahDiPengaturan();
  perbaruiDropdownMataKuliah();

  // Beri feedback ke user
  tampilkanToast("Mata kuliah berhasil dihapus.");
}

/*
  perbaruiDropdownMataKuliah()
  ------------------------------
  Memperbarui isi <select> dropdown mata kuliah di form tugas.
  Fungsi ini dipanggil setiap kali daftar mata kuliah berubah.

  CARA KERJA:
  1. Simpan dulu pilihan yang sedang aktif
  2. Kosongkan semua opsi
  3. Tambahkan opsi default ("Pilih mata kuliah")
  4. Loop seluruh daftar → buat <option> baru tiap mata kuliah
  5. Kembalikan pilihan aktif kalau masih ada di daftar
*/
function perbaruiDropdownMataKuliah() {
  var elemenSelect = elemenHalaman.pilihanMataKuliah;

  // Simpan pilihan yang sedang aktif agar tidak direset
  var nilaiYangSedangDipilih = elemenSelect.value;

  // Hapus semua opsi yang ada dulu
  elemenSelect.innerHTML = "";

  // Tambahkan opsi default (kosong) sebagai pilihan pertama
  var opsiDefault = document.createElement("option");
  opsiDefault.value = "";
  opsiDefault.textContent = "Pilih mata kuliah";
  elemenSelect.appendChild(opsiDefault);

  // Tambahkan tiap mata kuliah dari daftar sebagai opsi baru
  dataAplikasi.daftarMataKuliah.forEach(function (namaMataKuliah) {
    var opsiMataKuliah = document.createElement("option");
    opsiMataKuliah.value = namaMataKuliah;
    opsiMataKuliah.textContent = namaMataKuliah;
    elemenSelect.appendChild(opsiMataKuliah);
  });

  // Pulihkan pilihan yang tadi aktif kalau masih ada di daftar
  if (nilaiYangSedangDipilih && cekApakahMataKuliahSudahAda(nilaiYangSedangDipilih)) {
    elemenSelect.value = nilaiYangSedangDipilih;
  }
}

/*
  tampilkanDaftarMataKuliahDiPengaturan()
  -----------------------------------------
  Merender ulang daftar mata kuliah di halaman Pengaturan.
  Tiap item punya nama di kiri dan tombol Hapus di kanan.

  CARA KERJA:
  1. Cek apakah daftar kosong → tampilkan empty state jika iya
  2. Kalau tidak kosong: buat HTML untuk tiap mata kuliah
  3. Pasang event listener pada wadah dengan event delegation
*/
function tampilkanDaftarMataKuliahDiPengaturan() {
  var wadahDaftar = elemenHalaman.daftarMataKuliahPengaturan;

  // Kalau daftar kosong, tampilkan pesan empty state
  if (dataAplikasi.daftarMataKuliah.length === 0) {
    wadahDaftar.innerHTML = `
      <div class="kotak-kosong-mata-kuliah">
        <span class="ikon-kosong-mata-kuliah">📚</span>
        Belum ada mata kuliah.<br>
        Tambahkan mata kuliah pertamamu di atas.
      </div>
    `;
    return;
  }

  // Buat HTML untuk tiap mata kuliah dalam daftar
  var htmlDaftarMataKuliah = dataAplikasi.daftarMataKuliah.map(function (namaMataKuliah) {
    // Nama diamankan agar karakter khusus tidak merusak HTML
    var namaMataKuliahAman = amankanTeksUntukHtml(namaMataKuliah);

    return `
      <div class="item-mata-kuliah">
        <span class="nama-mata-kuliah-item">${namaMataKuliahAman}</span>
        <button
          class="tombol-hapus-mata-kuliah"
          type="button"
          data-nama-mata-kuliah="${namaMataKuliahAman}"
          aria-label="Hapus ${namaMataKuliahAman}"
        >
          Hapus
        </button>
      </div>
    `;
  }).join("");

  /*
    PERBAIKAN BUG 1 (bagian mata kuliah):
    Listener TIDAK lagi dipasang di sini.
    Listener sudah dipindah ke pasangSemuaEventListener() di bawah,
    menggunakan event delegation yang benar dan tidak pernah dihapus.
    Fungsi ini sekarang hanya mengurus tampilan HTML saja.
  */
  wadahDaftar.innerHTML = htmlDaftarMataKuliah;
}

/*
================================
FUNGSI HAPUS JADWAL (FITUR BARU)
================================
Menangani konfirmasi dan penghapusan jadwal manual.
*/

/*
  hapusJadwalDenganKonfirmasi(idJadwal, namaJadwal)
  --------------------------------------------------
  Fungsi ini dipanggil saat user klik tombol hapus jadwal (ikon tempat sampah).
  Urutan kerjanya:
  1. Tampilkan modal konfirmasi dengan nama jadwal yang akan dihapus
  2. Tunggu jawaban user (setuju atau batal)
  3. Kalau setuju: hapus dari data, simpan, refresh tampilan, tampilkan toast
  4. Kalau batal: tidak ada yang berubah
*/
async function hapusJadwalDenganKonfirmasi(idJadwal, namaJadwal) {

  // -------------------------------------------------------
  // LANGKAH 1: Tutup modal detail tanggal lebih dulu
  // -------------------------------------------------------
  // Ini mencegah modal detail dan modal konfirmasi tampil
  // bersamaan dan saling bertabrakan.
  // Kita tutup dulu, tunggu animasi selesai, baru buka konfirmasi.
  tutupModalDetailTanggal();

  // Tunggu animasi tutup modal detail selesai
  // 300ms adalah waktu aman agar animasi tidak terputus
  await new Promise(function (selesai) {
    setTimeout(selesai, 300);
  });

  // -------------------------------------------------------
  // LANGKAH 2: Tampilkan modal konfirmasi
  // -------------------------------------------------------
  // Modal detail sudah tertutup, aman untuk membuka konfirmasi
  const userSetuju = await bukaModalKonfirmasi({
    judul: "Hapus jadwal?",
    pesan: '"' + namaJadwal + '" akan dihapus permanen dari kalender.',
    teksSetuju: "Hapus",
    teksBatal: "Batal"
  });

  // Kalau user pilih Batal, berhenti di sini — tidak ada yang berubah
  if (!userSetuju) {
    return;
  }

  // -------------------------------------------------------
  // LANGKAH 3: Hapus data dan refresh semua tampilan
  // -------------------------------------------------------
  // Hapus jadwal dari array dan simpan ke localStorage
  hapusJadwalDariData(idJadwal);

  // Refresh kalender: kotak tanggal + agenda hari ini + reminder deadline
  tampilkanKalender();

  // Tampilkan notifikasi berhasil ke user
  tampilkanToast("Jadwal berhasil dihapus.");
}

/*
================================
EVENT LISTENER
================================
Bagian ini menghubungkan aksi user dengan function di atas.
*/
function pasangSemuaEventListener() {
  elemenHalaman.semuaTombolMenu.forEach(function (tombolMenu) {
    tombolMenu.addEventListener("click", function () {
      tampilkanHalaman(tombolMenu.dataset.halaman);
    });
  });

  elemenHalaman.semuaTombolAksiCepat.forEach(function (tombolAksi) {
    tombolAksi.addEventListener("click", function () {
      const aksi = tombolAksi.dataset.quickAction;

      if (aksi === "tambah-tugas") {
        tampilkanHalaman("tugas");
        elemenHalaman.inputNamaTugas.focus();
        return;
      }

      if (aksi === "tambah-jadwal") {
        bukaModalTambahJadwal(ubahTanggalMenjadiKode(new Date()));
        return;
      }

      if (aksi === "lihat-kalender") {
        tampilkanHalaman("kalender");
        return;
      }

      if (aksi === "fokus-hari-ini") {
        tampilkanHalaman("tugas");
        dataAplikasi.kataKunciPencarianTugas = "";
        dataAplikasi.filterStatusTugas = "belum";
        elemenHalaman.inputPencarianTugas.value = "";
        elemenHalaman.filterStatusTugas.value = "belum";
        tampilkanDaftarTugas();
      }
    });
  });

  document
    .getElementById("tombolTutupDetailTanggal")
    .addEventListener("click", function () {
      tutupModalDetailTanggal();
    });

  // ================================
  // LISTENER TOMBOL TAMBAH JADWAL DARI MODAL DETAIL
  // ================================
  // Saat user klik "+ Tambah jadwal di tanggal ini":
  // 1. Ambil tanggal yang sedang dibuka dari dataAplikasi
  // 2. Tutup modal detail lebih dulu
  // 3. Tunggu animasi tutup selesai (300ms)
  // 4. Buka modal tambah jadwal dengan tanggal sudah terisi otomatis
  document
    .getElementById("tombolTambahJadwalDariDetail")
    .addEventListener("click", function () {

      // Ambil tanggal yang sedang aktif di modal detail
      const tanggalYangDipilih = dataAplikasi.tanggalDetailYangSedangDibuka;

      // Tutup modal detail dulu agar tidak bertabrakan dengan modal jadwal
      tutupModalDetailTanggal();

      // Tunggu animasi tutup selesai sebelum buka modal tambah jadwal
      setTimeout(function () {

        // Buka modal tambah jadwal — tanggal otomatis terisi sesuai tanggal tadi
        bukaModalTambahJadwal(tanggalYangDipilih);

      }, 300);

    });

  // ================================
  // EVENT DELEGATION: HAPUS JADWAL DI MODAL DETAIL TANGGAL
  // ================================
  // Listener dipasang di wadah daftar jadwal di dalam modal detail.
  // Menggunakan event delegation agar tombol hapus dari semua item
  // tertangani oleh satu listener yang sama.
  document
    .getElementById("daftarJadwalDetailTanggal")
    .addEventListener("click", function (event) {

      // Cari tombol hapus jadwal yang diklik menggunakan closest()
      // Ini bekerja meski user klik teks atau ikon di dalam tombol
      const tombolHapus = event.target.closest(".tombol-hapus-jadwal");

      // Kalau yang diklik bukan tombol hapus, abaikan
      if (!tombolHapus) {
        return;
      }

      // PENTING: Hentikan event bubbling agar klik tombol hapus
      // tidak naik ke listener backdrop modal yang ada di atasnya.
      // Tanpa ini, klik tombol hapus juga dianggap klik backdrop
      // sehingga modal langsung tertutup sebelum proses hapus jalan.
      event.stopPropagation();

      // Ambil ID jadwal dari atribut data pada tombol
      const idJadwal = tombolHapus.dataset.idJadwal;

      // Cari nama jadwal dari elemen <strong> terdekat untuk ditampilkan di konfirmasi
      const itemAgenda = tombolHapus.closest(".item-agenda");
      const namaJadwal = itemAgenda
        ? itemAgenda.querySelector("strong").textContent
        : "Jadwal ini";

      // Jalankan proses hapus:
      // 1. Tutup modal detail
      // 2. Tunggu animasi
      // 3. Buka modal konfirmasi
      // 4. Kalau setuju: hapus data, refresh UI, tampilkan toast
      hapusJadwalDenganKonfirmasi(idJadwal, namaJadwal);

    });

  elemenHalaman.formTambahTugas.addEventListener("submit", function (event) {
    event.preventDefault();

    const hasilValidasi = validasiFormTugas();

    if (!hasilValidasi.formValid) {
      return;
    }

    const tugasBaru = buatDataTugas(hasilValidasi.namaTugas, hasilValidasi.mataKuliah, hasilValidasi.deadline);
    tambahTugasKeData(tugasBaru);
    elemenHalaman.formTambahTugas.reset();
    bersihkanErrorFormTugas();
    tampilkanSemuaDataAplikasi();
    tampilkanToast("Tugas baru berhasil ditambahkan.");
  });

  elemenHalaman.daftarTugas.addEventListener("click", async function (event) {
    const kartuTugas = event.target.closest(".item-tugas");

    if (!kartuTugas) {
      return;
    }

    const idTugas = kartuTugas.dataset.idTugas;

    if (event.target.matches(".checkbox-tugas")) {
      ubahStatusSelesaiTugas(idTugas);
      tampilkanSemuaDataAplikasi();
      tampilkanToast("Status tugas berhasil diperbarui.");
    }

    if (event.target.dataset.aksi === "hapus-tugas") {

  const userSetuju = await bukaModalKonfirmasi({
    judul: "Hapus tugas?",
    pesan: "Tugas ini akan dihapus permanen.",
    teksSetuju: "Hapus",
    teksBatal: "Batal"
  });

  if (!userSetuju) {
    return;
  }

  hapusTugasDariData(idTugas);
  tampilkanSemuaDataAplikasi();
  tampilkanToast("Tugas berhasil dihapus.");
}
  });

  elemenHalaman.inputPencarianTugas.addEventListener("input", function (event) {
    const nilaiPencarian = event.target.value.trim();

    clearTimeout(idDebouncePencarianTugas);
    idDebouncePencarianTugas = setTimeout(function () {
      dataAplikasi.kataKunciPencarianTugas = nilaiPencarian;
      requestAnimationFrame(tampilkanDaftarTugas);
    }, 140);
  });

  elemenHalaman.filterStatusTugas.addEventListener("change", function (event) {
    dataAplikasi.filterStatusTugas = event.target.value;
    tampilkanDaftarTugas();
  });

  elemenHalaman.tombolBulanSebelumnya.addEventListener("click", function () {
    pindahBulanKalender(-1);
  });

  elemenHalaman.tombolBulanBerikutnya.addEventListener("click", function () {
    pindahBulanKalender(1);
  });

  elemenHalaman.tombolHariIni.addEventListener("click", function () {
    dataAplikasi.bulanKalenderYangDibuka = new Date();
    tampilkanKalender();
  });

  elemenHalaman.tombolTambahJadwalCepat.addEventListener("click", function () {
    bukaModalTambahJadwal(ubahTanggalMenjadiKode(new Date()));
  });

  elemenHalaman.filterKategoriJadwal.addEventListener("change", function (event) {
    dataAplikasi.filterKategoriJadwal = event.target.value;
    tampilkanKalender();
  });

  elemenHalaman.isiKalender.addEventListener("click", function (event) {

  const kotakTanggal = event.target.closest(".kotak-tanggal");

  if (!kotakTanggal) {
    return;
  }

  // Ambil tanggal dari kotak kalender
  const kodeTanggal = kotakTanggal.dataset.tanggal;

  // Buka modal detail tanggal
  tampilkanDetailTanggal(kodeTanggal);

  });

  elemenHalaman.formTambahJadwal.addEventListener("submit", function (event) {
    event.preventDefault();

    const hasilValidasi = validasiFormJadwal();

    if (!hasilValidasi.formValid) {
      return;
    }

    const jadwalBaru = buatDataJadwal(
      hasilValidasi.namaJadwal,
      hasilValidasi.tanggalJadwal,
      hasilValidasi.jamJadwal,
      hasilValidasi.kategoriJadwal
    );

    tambahJadwalKeData(jadwalBaru);
    tutupModalTambahJadwal();
    cacheRenderKalender = "";

    if (dataAplikasi.halamanYangSedangDibuka === "kalender") {
      tampilkanKalender();
    } else {
      tampilkanDashboard();
    }

    tampilkanToast("Jadwal baru berhasil disimpan.");

    // -------------------------------------------------------
    // BUKA KEMBALI MODAL DETAIL SETELAH JADWAL DITAMBAH
    // -------------------------------------------------------
    // Kalau jadwal baru ditambahkan dari tombol di modal detail,
    // kita buka ulang modal detail tanggal itu agar user langsung
    // bisa melihat jadwal baru yang baru saja ditambahkan.
    // Cek: apakah tanggal jadwal baru sama dengan tanggal detail yang tadi dibuka?
    const tanggalDetailSebelumnya = dataAplikasi.tanggalDetailYangSedangDibuka;

    if (tanggalDetailSebelumnya && tanggalDetailSebelumnya === hasilValidasi.tanggalJadwal) {

      // Tunggu animasi modal jadwal tutup dulu, baru buka detail
      setTimeout(function () {
        tampilkanDetailTanggal(tanggalDetailSebelumnya);
      }, 300);

    }
  });

  elemenHalaman.tombolSimpanNama.addEventListener("click", simpanNamaPengguna);
  elemenHalaman.tombolTambahCepatMobile.addEventListener("click", function () {
    tampilkanHalaman("tugas");
    elemenHalaman.inputNamaTugas.focus();
  });
  elemenHalaman.tombolUploadFoto.addEventListener("click", function () {
    elemenHalaman.inputFotoProfil.click();
  });
  elemenHalaman.inputFotoProfil.addEventListener("change", prosesUploadFotoProfil);
  elemenHalaman.tombolHapusFoto.addEventListener("click", hapusFotoProfil);

  elemenHalaman.toggleModeGelap.addEventListener("change", function (event) {
    dataAplikasi.modeGelapAktif = event.target.checked;
    simpanDataKeLocalStorage(namaPenyimpananLocalStorage.modeGelap, dataAplikasi.modeGelapAktif);
    terapkanModeGelap();
  });

  elemenHalaman.toggleNotifikasiDeadline.addEventListener("change", function (event) {
    dataAplikasi.notifikasiDeadlineAktif = event.target.checked;
    simpanDataKeLocalStorage(namaPenyimpananLocalStorage.notifikasiDeadline, dataAplikasi.notifikasiDeadlineAktif);
    tampilkanDashboard();
  });

  elemenHalaman.tombolResetData.addEventListener("click", resetSemuaDataTugas);

  // ================================
  // EVENT LISTENER MATA KULIAH (BARU)
  // ================================

  // Klik tombol Tambah mata kuliah
  elemenHalaman.tombolTambahMataKuliah.addEventListener("click", tambahMataKuliahBaru);

  // Tekan Enter di input nama mata kuliah → sama seperti klik tombol Tambah
  elemenHalaman.inputNamaMataKuliah.addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
      event.preventDefault(); // Cegah form submit bawaan browser
      tambahMataKuliahBaru();
    }
  });

  // Bersihkan pesan error saat user mulai mengetik lagi
  elemenHalaman.inputNamaMataKuliah.addEventListener("input", function () {
    elemenHalaman.pesanErrorMataKuliahBaru.textContent = "";
    elemenHalaman.inputNamaMataKuliah.classList.remove("input-error");
  });

  // ================================
  // FITUR 1 — VALIDASI REALTIME FORM TUGAS
  // ================================
  // Listener input/change dipasang di sini agar tidak merusak
  // validasi submit yang sudah ada. Keduanya berjalan bersamaan:
  // - Realtime: error muncul/hilang saat user mengetik / memilih
  // - Submit: validasi final sebelum data disimpan

  // Nama tugas: cek minimal 3 karakter saat user mengetik
  elemenHalaman.inputNamaTugas.addEventListener("input", function () {
    const nilaiNamaTugas = elemenHalaman.inputNamaTugas.value.trim();

    if (nilaiNamaTugas.length >= 3) {
      // Nama sudah cukup panjang → hapus error
      elemenHalaman.inputNamaTugas.classList.remove("input-error");
      elemenHalaman.pesanErrorNamaTugas.textContent = "";
    } else if (nilaiNamaTugas.length > 0) {
      // User sudah mulai mengetik tapi masih kurang → tampilkan error
      tampilkanErrorInput(
        elemenHalaman.inputNamaTugas,
        elemenHalaman.pesanErrorNamaTugas,
        "Nama tugas minimal 3 karakter."
      );
    } else {
      // Field kosong (user hapus semua) → hapus error dulu, jangan tampilkan baru
      elemenHalaman.inputNamaTugas.classList.remove("input-error");
      elemenHalaman.pesanErrorNamaTugas.textContent = "";
    }
  });

  // Mata kuliah: hilangkan error saat user memilih opsi yang valid
  elemenHalaman.pilihanMataKuliah.addEventListener("change", function () {
    if (elemenHalaman.pilihanMataKuliah.value !== "") {
      // User sudah memilih mata kuliah → hapus error
      elemenHalaman.pilihanMataKuliah.classList.remove("input-error");
      elemenHalaman.pesanErrorMataKuliah.textContent = "";
    }
  });

  // Deadline: validasi tanggal tidak kosong dan tidak lewat, realtime
  elemenHalaman.inputDeadlineTugas.addEventListener("change", function () {
    const nilaiDeadline = elemenHalaman.inputDeadlineTugas.value;

    if (nilaiDeadline === "") {
      // Tanggal dihapus → hapus error (jangan tampilkan baru)
      elemenHalaman.inputDeadlineTugas.classList.remove("input-error");
      elemenHalaman.pesanErrorDeadlineTugas.textContent = "";
    } else if (hitungSisaHari(nilaiDeadline) < 0) {
      // Tanggal yang dipilih sudah lewat
      tampilkanErrorInput(
        elemenHalaman.inputDeadlineTugas,
        elemenHalaman.pesanErrorDeadlineTugas,
        "Deadline tidak boleh tanggal yang sudah lewat."
      );
    } else {
      // Tanggal valid → hapus error
      elemenHalaman.inputDeadlineTugas.classList.remove("input-error");
      elemenHalaman.pesanErrorDeadlineTugas.textContent = "";
    }
  });

  // ================================
  // FITUR 2 — VALIDASI REALTIME FORM JADWAL
  // ================================
  // Pola yang sama persis dengan form tugas di atas
  // agar konsisten dari sisi UX maupun gaya kode.

  // Nama kegiatan: minimal 3 karakter
  elemenHalaman.inputNamaJadwal.addEventListener("input", function () {
    const nilaiNamaJadwal = elemenHalaman.inputNamaJadwal.value.trim();

    if (nilaiNamaJadwal.length >= 3) {
      // Sudah cukup panjang → hapus error
      elemenHalaman.inputNamaJadwal.classList.remove("input-error");
      elemenHalaman.pesanErrorNamaJadwal.textContent = "";
    } else if (nilaiNamaJadwal.length > 0) {
      // Sedang mengetik tapi masih kurang → tampilkan error
      tampilkanErrorInput(
        elemenHalaman.inputNamaJadwal,
        elemenHalaman.pesanErrorNamaJadwal,
        "Nama kegiatan minimal 3 karakter."
      );
    } else {
      // Field kosong → hapus error
      elemenHalaman.inputNamaJadwal.classList.remove("input-error");
      elemenHalaman.pesanErrorNamaJadwal.textContent = "";
    }
  });

  // Tanggal jadwal: hilangkan error saat user memilih tanggal
  elemenHalaman.inputTanggalJadwal.addEventListener("change", function () {
    if (elemenHalaman.inputTanggalJadwal.value !== "") {
      elemenHalaman.inputTanggalJadwal.classList.remove("input-error");
      elemenHalaman.pesanErrorTanggalJadwal.textContent = "";
    }
  });

  // Jam jadwal: hilangkan error saat user memilih jam
  elemenHalaman.inputJamJadwal.addEventListener("change", function () {
    if (elemenHalaman.inputJamJadwal.value !== "") {
      elemenHalaman.inputJamJadwal.classList.remove("input-error");
      elemenHalaman.pesanErrorJamJadwal.textContent = "";
    }
  });

  // ================================
  // EVENT DELEGATION: HAPUS JADWAL DI AGENDA HARI INI
  // ================================
  // Satu listener di wadah agenda, menangani klik tombol hapus dari semua item jadwal.
  // Event delegation dipilih karena isi agenda dirender ulang setiap kali kalender di-refresh.
  // Dengan pola ini, listener tidak perlu dipasang ulang setelah render.
  elemenHalaman.daftarAgendaHariIni.addEventListener("click", function (event) {

    // Cari tombol hapus jadwal yang diklik
    const tombolHapus = event.target.closest(".tombol-hapus-jadwal");

    // Kalau yang diklik bukan tombol hapus jadwal, abaikan
    if (!tombolHapus) {
      return;
    }

    // PENTING: Hentikan event bubbling agar tidak bocor ke listener parent
    event.stopPropagation();

    // Ambil ID jadwal dari atribut data pada tombol
    const idJadwal = tombolHapus.dataset.idJadwal;

    // Cari nama jadwal dari item agenda terdekat untuk ditampilkan di modal konfirmasi
    const itemAgenda = tombolHapus.closest(".item-agenda");
    const namaJadwal = itemAgenda ? itemAgenda.querySelector("strong").textContent : "Jadwal ini";

    // Panggil fungsi hapus dengan konfirmasi
    hapusJadwalDenganKonfirmasi(idJadwal, namaJadwal);

  });

  // ================================
  // EVENT DELEGATION: HAPUS MATA KULIAH DI PENGATURAN (PERBAIKAN BUG 1)
  // ================================
  // Listener dipasang SEKALI di sini, bukan di dalam fungsi render.
  // Ini solusi utama Bug 1: listener tidak pernah hilang atau menumpuk.
  elemenHalaman.daftarMataKuliahPengaturan.addEventListener("click", function (event) {
    // Cari tombol hapus mata kuliah yang diklik
    const tombolHapus = event.target.closest(".tombol-hapus-mata-kuliah");

    // Kalau yang diklik bukan tombol hapus, abaikan
    if (!tombolHapus) {
      return;
    }

    // Ambil nama mata kuliah dari atribut data pada tombol
    const namaYangAkanDihapus = tombolHapus.dataset.namaMataKuliah;

    // Panggil fungsi hapus dengan konfirmasi
    hapusMataKuliah(namaYangAkanDihapus);
  });

  elemenHalaman.tombolSetujuKonfirmasi.addEventListener("click", function () {
    tutupModalKonfirmasi(true);
  });
  elemenHalaman.tombolBatalKonfirmasi.addEventListener("click", function () {
    tutupModalKonfirmasi(false);
  });
  elemenHalaman.tombolBatalJadwal.addEventListener("click", tutupModalTambahJadwal);

  elemenHalaman.modalKonfirmasi.addEventListener("click", function (event) {
    if (event.target === elemenHalaman.modalKonfirmasi) {
      tutupModalKonfirmasi(false);
    }
  });

  // ================================
  // LISTENER BACKDROP MODAL DETAIL TANGGAL
  // ================================
  // Klik area hitam di luar modal → tutup modal.
  // stopPropagation() dipasang agar klik backdrop tidak
  // bocor ke listener lain yang mungkin ada di atasnya.
  document
    .getElementById("modalDetailTanggal")
    .addEventListener("click", function (event) {

      // Kalau user klik area hitam di luar konten modal (backdrop)
      if (event.target.id === "modalDetailTanggal") {

        // Hentikan event agar tidak naik ke listener parent
        event.stopPropagation();

        tutupModalDetailTanggal();

      }

    });

  elemenHalaman.modalJadwal.addEventListener("click", function (event) {
    if (event.target === elemenHalaman.modalJadwal) {
      tutupModalTambahJadwal();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      tutupModalKonfirmasi(false);
      tutupModalTambahJadwal();
      tutupModalDetailTanggal();
    }
  });
}

function pindahBulanKalender(jumlahPerpindahanBulan) {
  const tanggalKalenderSaatIni = dataAplikasi.bulanKalenderYangDibuka;
  const tahunBaru = tanggalKalenderSaatIni.getFullYear();
  const bulanBaru = tanggalKalenderSaatIni.getMonth() + jumlahPerpindahanBulan;

  dataAplikasi.bulanKalenderYangDibuka = new Date(tahunBaru, bulanBaru, 1);
  tampilkanKalender();
}

/*
================================
FUNGSI MIGRASI DATA LAMA
================================
Project lama pernah menyimpan tugas dengan nama property berbeda.
Fungsi ini membantu agar data lama tetap bisa dibaca.
*/
function pindahkanDataTugasLamaJikaAda() {
  if (dataAplikasi.daftarSemuaTugas.length > 0) {
    return;
  }

  const dataTugasLama = ambilDataDariLocalStorage("tugas", []);

  if (!Array.isArray(dataTugasLama) || dataTugasLama.length === 0) {
    return;
  }

  dataAplikasi.daftarSemuaTugas = dataTugasLama.map(function (tugasLama, urutanTugas) {
    return {
      id: `${Date.now()}-${urutanTugas}`,
      namaTugas: tugasLama.judul || "Tugas tanpa nama",
      mataKuliah: tugasLama.matkul || "Mata kuliah",
      deadline: tugasLama.deadline,
      sudahSelesai: Boolean(tugasLama.selesai),
      dibuatPada: new Date().toISOString()
    };
  });

  simpanDaftarTugas();
}

function rapikanFormatDataTugasYangSudahTersimpan() {
  dataAplikasi.daftarSemuaTugas = dataAplikasi.daftarSemuaTugas.map(function (tugas, urutanTugas) {
    return {
      id: tugas.id || `${Date.now()}-${urutanTugas}`,
      namaTugas: tugas.namaTugas || tugas.title || tugas.judul || "Tugas tanpa nama",
      mataKuliah: tugas.mataKuliah || tugas.course || tugas.matkul || "Mata kuliah",
      deadline: tugas.deadline,
      sudahSelesai: Boolean(tugas.sudahSelesai || tugas.isCompleted || tugas.selesai),
      dibuatPada: tugas.dibuatPada || tugas.createdAt || new Date().toISOString()
    };
  });

  simpanDaftarTugas();
}

function rapikanFormatDataJadwalYangSudahTersimpan() {
  dataAplikasi.daftarSemuaJadwal = dataAplikasi.daftarSemuaJadwal.map(function (jadwal, urutanJadwal) {
    return {
      id: jadwal.id || `${Date.now()}-${urutanJadwal}`,
      namaJadwal: jadwal.namaJadwal || jadwal.title || "Jadwal tanpa nama",
      tanggalJadwal: jadwal.tanggalJadwal || jadwal.date,
      jamJadwal: jadwal.jamJadwal || jadwal.time || "00:00",
      kategoriJadwal: jadwal.kategoriJadwal || jadwal.category || "kuliah",
      dibuatPada: jadwal.dibuatPada || jadwal.createdAt || new Date().toISOString()
    };
  });

  simpanDaftarJadwal();
}

/*
================================
FUNGSI AWAL APLIKASI
================================
Function ini pertama kali dipanggil saat file script.js selesai dibaca browser.
*/
function jalankanAplikasi() {
  pindahkanDataTugasLamaJikaAda();
  rapikanFormatDataTugasYangSudahTersimpan();
  rapikanFormatDataJadwalYangSudahTersimpan();

  // ================================
  // INISIALISASI MATA KULIAH (BARU)
  // Harus dipanggil sebelum pasangSemuaEventListener
  // agar dropdown sudah terisi saat halaman dibuka
  // ================================
  inisialisasiDaftarMataKuliah();

  pasangSemuaEventListener();

  const tanggalHariIni = ubahTanggalMenjadiKode(new Date());
  elemenHalaman.inputDeadlineTugas.min = tanggalHariIni;

  const namaPenggunaTersimpan = localStorage.getItem(namaPenyimpananLocalStorage.namaPengguna);

  if (namaPenggunaTersimpan) {
    elemenHalaman.inputNamaPengguna.value = namaPenggunaTersimpan;
    tampilkanNamaPengguna(namaPenggunaTersimpan);
  } else {
    tampilkanInisialProfil("");
  }

  tampilkanFotoProfil();
  elemenHalaman.toggleNotifikasiDeadline.checked = dataAplikasi.notifikasiDeadlineAktif;
  terapkanModeGelap();
  tampilkanTanggalRealtime();
  setInterval(tampilkanTanggalRealtime, 60000);
  tampilkanHalaman(dataAplikasi.halamanYangSedangDibuka);

  // ================================
  // RENDER MATA KULIAH SAAT AWAL (BARU)
  // Isi dropdown dan daftar pengaturan setelah semua siap
  // ================================
  perbaruiDropdownMataKuliah();
  tampilkanDaftarMataKuliahDiPengaturan();
}

jalankanAplikasi();

/* ================================
BOTTOM NAVIGATION MOBILE
================================ */

elemenHalaman.semuaTombolNavMobile.forEach(function(tombolMobile) {

  tombolMobile.addEventListener("click", function() {

    const halamanTujuan = tombolMobile.dataset.halaman;

    tampilkanHalaman(halamanTujuan);

  });

});

/* ================================
FLOATING BUTTON MOBILE
================================ */

document.getElementById("tombolTambahMobile").addEventListener("click", function() {

  tampilkanHalaman("tugas");

  elemenHalaman.inputNamaTugas.focus();

});

/* ================================================
   AVATAR PROFIL HEADER — KLIK KE PENGATURAN
   Saat avatar diklik → langsung buka halaman Pengaturan
   ================================================ */

(function () {
  var avatar = document.getElementById("fotoProfilHeader");
  if (!avatar) return; // Jaga-jaga kalau elemen tidak ada

  // Klik mouse → buka halaman Pengaturan
  avatar.addEventListener("click", function () {
    tampilkanHalaman("pengaturan");
  });

  // Keyboard: Enter atau Spasi saat fokus → sama seperti klik
  // (karena elemen ini div, bukan button)
  avatar.addEventListener("keydown", function (e) {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault(); // Cegah scroll saat tekan Spasi
      tampilkanHalaman("pengaturan");
    }
  });
})();
