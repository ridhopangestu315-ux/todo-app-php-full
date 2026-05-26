/*
  StudyFlow lightweight controller.
  PHP renders data and markup. JavaScript only handles navigation,
  small interactions, AJAX mutations, modals, toast, and previews.
*/
(function () {
  "use strict";

  const state = {
    activePage: document.body.dataset.halamanAktif || "dashboard",
    selectedScheduleDate: "",
    confirmAction: null
  };

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  const els = {
    pages: $$(".halaman"),
    desktopNav: $$(".tombol-menu"),
    mobileNav: $$(".tombol-nav-mobile"),
    quickActions: $$("[data-quick-action]"),
    dateText: $("#teksTanggalRealtime"),
    timeText: $("#teksJamRealtime"),
    darkButtons: $$("[data-toggle-mode-gelap]"),
    headerAvatar: $("#fotoProfilHeader"),
    profilePreview: $("#previewFotoProfil"),
    profileInput: $("#inputFotoProfil"),
    uploadButton: $("#tombolUploadFoto"),
    removePhotoButton: $("#tombolHapusFoto"),
    photoError: $("#pesanErrorFotoProfil"),
    nameInput: $("#inputNamaPengguna"),
    saveNameButton: $("#tombolSimpanNama"),
    notificationToggle: $("#toggleNotifikasiDeadline"),
    taskForm: $("#formTambahTugas"),
    taskList: $("#daftarTugas"),
    taskSearch: $("#inputPencarianTugas"),
    taskFilter: $("#filterStatusTugas"),
    scheduleModal: $("#modalJadwal"),
    scheduleForm: $("#formTambahJadwal"),
    scheduleDateText: $("#teksTanggalJadwalDipilih"),
    scheduleDateInput: $("#inputTanggalJadwal"),
    scheduleNameInput: $("#inputNamaJadwal"),
    scheduleTimeInput: $("#inputJamJadwal"),
    scheduleCategoryInput: $("#pilihanKategoriJadwal"),
    openScheduleButton: $("#tombolTambahJadwalCepat"),
    closeScheduleButton: $("#tombolBatalJadwal"),
    calendarGrid: $("#isiKalender"),
    agendaToday: $("#daftarAgendaHariIni"),
    detailModal: $("#modalDetailTanggal"),
    detailTitle: $("#judulModalDetail"),
    detailList: $("#daftarJadwalDetailTanggal"),
    closeDetailButton: $("#tombolTutupDetailTanggal"),
    addFromDetailButton: $("#tombolTambahJadwalDariDetail"),
    confirmModal: $("#modalKonfirmasi"),
    confirmTitle: $("#judulModalKonfirmasi"),
    confirmMessage: $("#pesanModalKonfirmasi"),
    confirmYes: $("#tombolSetujuKonfirmasi"),
    confirmNo: $("#tombolBatalKonfirmasi"),
    resetButton: $("#tombolResetData"),
    mobileAddA: $("#tombolTambahCepatMobile"),
    mobileAddB: $("#tombolTambahMobile"),
    toastRoot: $("#wadahToast"),
    courseSelect: $("#pilihanMataKuliah"),
    courseInput: $("#inputNamaMataKuliah"),
    addCourseButton: $("#tombolTambahMataKuliah"),
    courseList: $("#daftarMataKuliahPengaturan"),
    courseError: $("#pesanErrorMataKuliahBaru")
  };

  const profile = window.studyflowUser || {};
  const defaultCourses = window.studyflowCourses || [];
  const storageKeys = {
    courses: "studyflow_mata_kuliah"
  };

  function safeText(value) {
    return String(value || "").replace(/[&<>"']/g, function (char) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
      }[char];
    });
  }

  async function apiPost(action, data) {
    const formData = new FormData();
    formData.append("aksi", action);

    Object.keys(data || {}).forEach(function (key) {
      formData.append(key, data[key]);
    });

    const response = await fetch("api.php", {
      method: "POST",
      body: formData,
      credentials: "same-origin"
    });

    let result = null;
    try {
      result = await response.json();
    } catch (error) {
      throw new Error("Respons server tidak valid.");
    }

    if (!response.ok || result.status !== "success") {
      throw new Error(result.message || "Aksi gagal diproses.");
    }

    return result;
  }

  function showToast(message, type) {
    if (!els.toastRoot) return;

    const toast = document.createElement("div");
    toast.className = "toast" + (type === "error" ? " toast-error" : "");
    toast.textContent = message;
    els.toastRoot.appendChild(toast);

    window.setTimeout(function () {
      toast.classList.add("toast-keluar");
      window.setTimeout(function () {
        toast.remove();
      }, 180);
    }, 2600);
  }

  function setActivePage(pageName, updateUrl) {
    if (!pageName || !$("#" + pageName)) return;

    state.activePage = pageName;
    document.body.dataset.halamanAktif = pageName;

    els.pages.forEach(function (page) {
      page.classList.toggle("halaman-aktif", page.id === pageName);
    });

    els.desktopNav.concat(els.mobileNav).forEach(function (button) {
      button.classList.toggle("aktif", button.dataset.halaman === pageName);
    });

    if (updateUrl) {
      const url = new URL(window.location.href);
      url.searchParams.set("halaman", pageName);
      window.history.replaceState({}, "", url);
    }

    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function updateClock() {
    const now = new Date();
    if (els.dateText) {
      els.dateText.textContent = new Intl.DateTimeFormat("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric"
      }).format(now);
    }

    if (els.timeText) {
      els.timeText.textContent = new Intl.DateTimeFormat("id-ID", {
        hour: "2-digit",
        minute: "2-digit"
      }).format(now);
    }
  }

  function syncDarkButtons(isDark) {
    els.darkButtons.forEach(function (button) {
      button.setAttribute("aria-pressed", isDark ? "true" : "false");
      const icon = $(".ikon-mode-gelap", button);
      const label = $(".label-mode-gelap", button);
      if (icon) icon.textContent = isDark ? "☀" : "🌙";
      if (label) label.textContent = isDark ? "Gunakan light mode" : "Gunakan dark mode";
      button.setAttribute("aria-label", isDark ? "Gunakan light mode" : "Gunakan dark mode");
    });
  }

  async function toggleDarkMode() {
    const nextMode = !document.body.classList.contains("mode-gelap");
    document.body.classList.toggle("mode-gelap", nextMode);
    syncDarkButtons(nextMode);

    try {
      await apiPost("update_dark_mode", { dark_mode: nextMode ? 1 : 0 });
    } catch (error) {
      document.body.classList.toggle("mode-gelap", !nextMode);
      syncDarkButtons(!nextMode);
      showToast(error.message, "error");
    }
  }

  function openScheduleModal(dateValue) {
    if (!els.scheduleModal) return;
    const date = dateValue || new Date().toISOString().slice(0, 10);
    state.selectedScheduleDate = date;

    if (els.scheduleDateInput) els.scheduleDateInput.value = date;
    if (els.scheduleDateText) {
      els.scheduleDateText.textContent = new Intl.DateTimeFormat("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric"
      }).format(new Date(date + "T00:00:00"));
    }

    els.scheduleModal.classList.add("tampil");
    els.scheduleModal.setAttribute("aria-hidden", "false");
    window.setTimeout(function () {
      els.scheduleNameInput?.focus();
    }, 60);
  }

  function closeScheduleModal() {
    if (!els.scheduleModal) return;
    els.scheduleModal.classList.remove("tampil");
    els.scheduleModal.setAttribute("aria-hidden", "true");
    els.scheduleForm?.reset();
  }

  function openDetailModal(date, title, items) {
    if (!els.detailModal) return;
    state.selectedScheduleDate = date;
    if (els.detailTitle) els.detailTitle.textContent = title;
    if (els.detailList) {
      els.detailList.innerHTML = items.length
        ? items.map(function (item) { return item.outerHTML; }).join("")
        : '<div class="kotak-kosong">Tidak ada jadwal di tanggal ini.</div>';
    }
    els.detailModal.classList.add("tampil");
    els.detailModal.setAttribute("aria-hidden", "false");
  }

  function closeDetailModal() {
    if (!els.detailModal) return;
    els.detailModal.classList.remove("tampil");
    els.detailModal.setAttribute("aria-hidden", "true");
  }

  function openConfirm(title, message, action) {
    state.confirmAction = action;
    if (els.confirmTitle) els.confirmTitle.textContent = title;
    if (els.confirmMessage) els.confirmMessage.textContent = message;
    els.confirmModal?.classList.add("tampil");
    els.confirmModal?.setAttribute("aria-hidden", "false");
  }

  function closeConfirm() {
    state.confirmAction = null;
    els.confirmModal?.classList.remove("tampil");
    els.confirmModal?.setAttribute("aria-hidden", "true");
  }

  function filterTasks() {
    if (!els.taskList) return;

    const keyword = (els.taskSearch?.value || "").toLowerCase().trim();
    const status = els.taskFilter?.value || "semua";
    const cards = $$(".item-tugas", els.taskList);
    let visible = 0;

    cards.forEach(function (card) {
      const text = (card.dataset.search || card.textContent || "").toLowerCase();
      const done = card.dataset.status === "selesai";
      const matchKeyword = !keyword || text.includes(keyword);
      const matchStatus = status === "semua" || (status === "selesai" ? done : !done);
      const show = matchKeyword && matchStatus;
      card.hidden = !show;
      if (show) visible += 1;
    });

    const empty = $("#pesanFilterTugasKosong");
    if (empty) empty.hidden = visible !== 0;
  }

  function getStoredCourses() {
    try {
      const parsed = JSON.parse(localStorage.getItem(storageKeys.courses) || "null");
      if (Array.isArray(parsed)) return parsed.filter(Boolean);
    } catch (error) {
      localStorage.removeItem(storageKeys.courses);
    }
    return defaultCourses.slice();
  }

  function setStoredCourses(courses) {
    localStorage.setItem(storageKeys.courses, JSON.stringify(courses));
  }

  function renderCourses() {
    const courses = Array.from(new Set(getStoredCourses().map(function (course) {
      return String(course).trim();
    }).filter(Boolean))).sort(function (a, b) {
      return a.localeCompare(b, "id-ID");
    });

    if (els.courseSelect) {
      const current = els.courseSelect.value;
      els.courseSelect.innerHTML = '<option value="">Pilih mata kuliah</option>' + courses.map(function (course) {
        return '<option value="' + safeText(course) + '">' + safeText(course) + "</option>";
      }).join("");
      if (current) els.courseSelect.value = current;
    }

    if (els.courseList) {
      els.courseList.innerHTML = courses.length
        ? courses.map(function (course) {
            return '<div class="item-mata-kuliah" data-course="' + safeText(course) + '"><span class="nama-mata-kuliah-item">' + safeText(course) + '</span><button class="tombol-hapus-mata-kuliah" type="button" data-course-remove="' + safeText(course) + '">Hapus</button></div>';
          }).join("")
        : '<div class="kotak-kosong-mata-kuliah"><span class="ikon-kosong-mata-kuliah">+</span>Belum ada mata kuliah.</div>';
    }
  }

  function addCourse() {
    const value = (els.courseInput?.value || "").trim();
    if (!value) {
      if (els.courseError) els.courseError.textContent = "Nama mata kuliah wajib diisi.";
      return;
    }

    const courses = getStoredCourses();
    if (courses.some(function (course) { return course.toLowerCase() === value.toLowerCase(); })) {
      if (els.courseError) els.courseError.textContent = "Mata kuliah sudah ada.";
      return;
    }

    courses.push(value);
    setStoredCourses(courses);
    if (els.courseInput) els.courseInput.value = "";
    if (els.courseError) els.courseError.textContent = "";
    renderCourses();
  }

  function previewAvatar(file) {
    if (!file || !els.profilePreview) return;
    const reader = new FileReader();
    reader.onload = function () {
      const img = '<img src="' + reader.result + '" alt="Preview foto profil">';
      els.profilePreview.innerHTML = img;
      if (els.headerAvatar) els.headerAvatar.innerHTML = img.replace("Preview foto profil", "Foto profil");
    };
    reader.readAsDataURL(file);
  }

  function reloadWithPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set("halaman", page || state.activePage || "dashboard");
    window.location.href = url.toString();
  }

  function wireEvents() {
    els.desktopNav.concat(els.mobileNav).forEach(function (button) {
      button.addEventListener("click", function () {
        setActivePage(button.dataset.halaman, true);
      });
    });

    els.quickActions.forEach(function (button) {
      button.addEventListener("click", function () {
        const action = button.dataset.quickAction;
        if (action === "tambah-tugas") {
          setActivePage("tugas", true);
          $("#inputNamaTugas")?.focus();
        } else if (action === "tambah-jadwal") {
          setActivePage("kalender", true);
          openScheduleModal();
        } else if (action === "lihat-kalender") {
          setActivePage("kalender", true);
        } else if (action === "fokus-hari-ini") {
          setActivePage("dashboard", true);
          $("#daftarTugasHariIni")?.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      });
    });

    els.headerAvatar?.addEventListener("click", function () {
      setActivePage("pengaturan", true);
    });
    els.headerAvatar?.addEventListener("keydown", function (event) {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        setActivePage("pengaturan", true);
      }
    });
    $$(".profil-sidebar").forEach(function (button) {
      button.addEventListener("click", function () {
        setActivePage("pengaturan", true);
      });
    });

    els.darkButtons.forEach(function (button) {
      button.addEventListener("click", toggleDarkMode);
    });

    els.notificationToggle?.addEventListener("change", async function () {
      try {
        await apiPost("update_settings", { notifikasi: els.notificationToggle.checked ? 1 : 0 });
        showToast("Pengaturan notifikasi disimpan.", "success");
      } catch (error) {
        els.notificationToggle.checked = !els.notificationToggle.checked;
        showToast(error.message, "error");
      }
    });

    els.taskForm?.addEventListener("submit", async function (event) {
      event.preventDefault();
      const nama = $("#inputNamaTugas")?.value.trim() || "";
      const mataKuliah = $("#pilihanMataKuliah")?.value.trim() || "";
      const deadline = $("#inputDeadlineTugas")?.value || "";
      if (!nama || !deadline) {
        showToast("Nama tugas dan deadline wajib diisi.", "error");
        return;
      }

      try {
        await apiPost("tambah_tugas", {
          nama_tugas: nama,
          mata_kuliah: mataKuliah,
          deadline: deadline
        });
        reloadWithPage("tugas");
      } catch (error) {
        showToast(error.message, "error");
      }
    });

    els.taskList?.addEventListener("change", async function (event) {
      const checkbox = event.target.closest(".checkbox-tugas");
      if (!checkbox) return;
      const card = checkbox.closest(".item-tugas");
      checkbox.disabled = true;
      try {
        await apiPost("toggle_selesai", { id: card.dataset.idTugas });
        reloadWithPage("tugas");
      } catch (error) {
        checkbox.checked = !checkbox.checked;
        checkbox.disabled = false;
        showToast(error.message, "error");
      }
    });

    els.taskList?.addEventListener("click", function (event) {
      const button = event.target.closest("[data-aksi='hapus-tugas']");
      if (!button) return;
      const card = button.closest(".item-tugas");
      openConfirm("Hapus tugas?", "Tugas ini akan dihapus dari StudyFlow.", async function () {
        await apiPost("hapus_tugas", { id: card.dataset.idTugas });
        reloadWithPage("tugas");
      });
    });

    els.taskSearch?.addEventListener("input", filterTasks);
    els.taskFilter?.addEventListener("change", filterTasks);

    els.openScheduleButton?.addEventListener("click", function () {
      openScheduleModal();
    });
    els.closeScheduleButton?.addEventListener("click", closeScheduleModal);
    els.mobileAddA?.addEventListener("click", function () {
      setActivePage("tugas", true);
      $("#inputNamaTugas")?.focus();
    });
    els.mobileAddB?.addEventListener("click", function () {
      setActivePage("tugas", true);
      $("#inputNamaTugas")?.focus();
    });

    els.scheduleForm?.addEventListener("submit", async function (event) {
      event.preventDefault();
      const nama = els.scheduleNameInput?.value.trim() || "";
      const tanggal = els.scheduleDateInput?.value || "";
      const jam = els.scheduleTimeInput?.value || "";
      const kategori = els.scheduleCategoryInput?.value || "pribadi";
      if (!nama || !tanggal || !jam) {
        showToast("Nama, tanggal, dan jam jadwal wajib diisi.", "error");
        return;
      }

      try {
        await apiPost("tambah_jadwal", {
          nama_jadwal: nama,
          tanggal: tanggal,
          jam: jam,
          kategori: kategori
        });
        reloadWithPage("kalender");
      } catch (error) {
        showToast(error.message, "error");
      }
    });

    els.calendarGrid?.addEventListener("click", function (event) {
      const dateButton = event.target.closest(".kotak-tanggal");
      if (!dateButton) return;
      const source = $("#dataAgendaTanggal");
      const items = $$(".item-agenda[data-tanggal='" + dateButton.dataset.tanggal + "']", source || document);
      openDetailModal(dateButton.dataset.tanggal, dateButton.dataset.tanggalLabel, items);
    });

    els.agendaToday?.addEventListener("click", function (event) {
      const button = event.target.closest("[data-hapus-jadwal]");
      if (!button) return;
      openConfirm("Hapus jadwal?", "Agenda ini akan dihapus dari kalender.", async function () {
        await apiPost("hapus_jadwal", { id: button.dataset.hapusJadwal });
        reloadWithPage("kalender");
      });
    });

    els.detailList?.addEventListener("click", function (event) {
      const button = event.target.closest("[data-hapus-jadwal]");
      if (!button) return;
      openConfirm("Hapus jadwal?", "Agenda ini akan dihapus dari kalender.", async function () {
        await apiPost("hapus_jadwal", { id: button.dataset.hapusJadwal });
        reloadWithPage("kalender");
      });
    });

    els.closeDetailButton?.addEventListener("click", closeDetailModal);
    els.addFromDetailButton?.addEventListener("click", function () {
      closeDetailModal();
      openScheduleModal(state.selectedScheduleDate);
    });

    $$("[data-calendar-url]").forEach(function (button) {
      button.addEventListener("click", function () {
        window.location.href = button.dataset.calendarUrl;
      });
    });

    $("#filterKategoriJadwal")?.addEventListener("change", function (event) {
      const url = new URL(window.location.href);
      url.searchParams.set("halaman", "kalender");
      url.searchParams.set("kategori", event.target.value);
      window.location.href = url.toString();
    });

    els.uploadButton?.addEventListener("click", function () {
      els.profileInput?.click();
    });
    els.profileInput?.addEventListener("change", async function () {
      const file = els.profileInput.files?.[0];
      if (!file) return;
      if (!["image/jpeg", "image/png"].includes(file.type) || file.size > 2 * 1024 * 1024) {
        if (els.photoError) els.photoError.textContent = "Foto harus JPG/PNG maksimal 2MB.";
        return;
      }
      if (els.photoError) els.photoError.textContent = "";
      previewAvatar(file);
      const formData = new FormData();
      formData.append("aksi", "upload_foto");
      formData.append("foto", file);
      try {
        const response = await fetch("api.php", { method: "POST", body: formData, credentials: "same-origin" });
        const result = await response.json();
        if (result.status !== "success") throw new Error(result.message || "Gagal mengupload foto.");
        showToast("Foto profil diperbarui.", "success");
      } catch (error) {
        if (els.photoError) els.photoError.textContent = error.message;
      }
    });

    els.removePhotoButton?.addEventListener("click", async function () {
      try {
        await apiPost("hapus_foto");
        reloadWithPage("pengaturan");
      } catch (error) {
        if (els.photoError) els.photoError.textContent = error.message;
      }
    });

    els.saveNameButton?.addEventListener("click", async function () {
      const nama = els.nameInput?.value.trim() || "";
      if (!nama) {
        showToast("Nama tidak boleh kosong.", "error");
        return;
      }
      try {
        await apiPost("update_profile", { nama: nama });
        reloadWithPage("pengaturan");
      } catch (error) {
        showToast(error.message, "error");
      }
    });

    els.addCourseButton?.addEventListener("click", addCourse);
    els.courseInput?.addEventListener("keydown", function (event) {
      if (event.key === "Enter") {
        event.preventDefault();
        addCourse();
      }
    });
    els.courseList?.addEventListener("click", function (event) {
      const button = event.target.closest("[data-course-remove]");
      if (!button) return;
      const name = button.dataset.courseRemove;
      setStoredCourses(getStoredCourses().filter(function (course) {
        return course !== name;
      }));
      renderCourses();
    });

    els.resetButton?.addEventListener("click", function () {
      openConfirm("Hapus semua data?", "Semua tugas dan jadwal akun ini akan dihapus.", async function () {
        await apiPost("reset_akun");
        reloadWithPage("dashboard");
      });
    });

    els.confirmNo?.addEventListener("click", closeConfirm);
    els.confirmYes?.addEventListener("click", async function () {
      const action = state.confirmAction;
      closeConfirm();
      if (!action) return;
      try {
        await action();
      } catch (error) {
        showToast(error.message, "error");
      }
    });

    $$(".lapisan-modal").forEach(function (modal) {
      modal.addEventListener("click", function (event) {
        if (event.target !== modal) return;
        if (modal === els.scheduleModal) closeScheduleModal();
        if (modal === els.detailModal) closeDetailModal();
        if (modal === els.confirmModal) closeConfirm();
      });
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        closeScheduleModal();
        closeDetailModal();
        closeConfirm();
      }
    });
  }

  function init() {
    if (els.nameInput) els.nameInput.value = profile.nama || "";
    if (els.notificationToggle) els.notificationToggle.checked = Boolean(Number(profile.notifikasi ?? 1));
    syncDarkButtons(document.body.classList.contains("mode-gelap"));
    renderCourses();
    filterTasks();
    updateClock();
    window.setInterval(updateClock, 60000);
    setActivePage(state.activePage, false);
    wireEvents();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
