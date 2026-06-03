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
    taskFilterLinks: $$("[data-filter-tugas]"),
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
    editTaskModal: $("#modalEditTugas"),
    editTaskForm: $("#formEditTugas"),
    editTaskIdInput: $("#inputIdEditTugas"),
    editTaskNameInput: $("#inputNamaEditTugas"),
    editTaskCourseInput: $("#pilihanMataKuliahEditTugas"),
    editTaskDeadlineInput: $("#inputDeadlineEditTugas"),
    closeEditTaskButton: $("#tombolBatalEditTugas"),
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
    var cls = "toast";
    if (type === "error") cls += " toast-error";
    if (type === "success") cls += " toast-success";
    toast.className = cls;
    toast.textContent = message;
    els.toastRoot.appendChild(toast);

    var duration = type === "success" ? 3200 : 2600;
    window.setTimeout(function () {
      toast.classList.add("toast-keluar");
      window.setTimeout(function () {
        toast.remove();
      }, 200);
    }, duration);
  }

  function setButtonLoading(button, isLoading, text) {
    if (!button) return;
    if (isLoading) {
      button.dataset.originalText = button.textContent;
      button.disabled = true;
      button.setAttribute("aria-busy", "true");
      if (text) button.textContent = text;
    } else {
      button.disabled = false;
      button.removeAttribute("aria-busy");
      if (button.dataset.originalText) button.textContent = button.dataset.originalText;
    }
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
      if (pageName === "tugas") {
        url.searchParams.delete("filter_tugas");
        url.searchParams.delete("deadline_tugas");
        if (els.taskFilter) {
          els.taskFilter.value = "semua";
        }
        if (els.taskList) {
          els.taskList.dataset.deadlineFilter = "semua";
        }
      }
      window.history.replaceState({}, "", url);
    }

    if (pageName === "tugas") {
      filterTasks();
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
    var today = new Date();
    var todayStr = today.getFullYear() + "-" +
      String(today.getMonth() + 1).padStart(2, "0") + "-" +
      String(today.getDate()).padStart(2, "0");
    var date = dateValue || todayStr;
    state.selectedScheduleDate = date;

    if (els.scheduleDateInput) els.scheduleDateInput.value = date;
    if (els.scheduleDateText) {
      try {
        var parts = date.split("-");
        var d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        els.scheduleDateText.textContent = new Intl.DateTimeFormat("id-ID", {
          weekday: "long",
          day: "numeric",
          month: "long",
          year: "numeric"
        }).format(d);
      } catch (e) {
        els.scheduleDateText.textContent = date;
      }
    }

    els.scheduleModal.classList.add("tampil");
    els.scheduleModal.setAttribute("aria-hidden", "false");
    window.setTimeout(function () {
      if (els.scheduleNameInput) els.scheduleNameInput.focus();
    }, 80);
  }

  function closeScheduleModal() {
    if (!els.scheduleModal) return;
    els.scheduleModal.classList.remove("tampil");
    els.scheduleModal.setAttribute("aria-hidden", "true");
    els.scheduleForm?.reset();
  }

  function setEditCourseValue(value) {
    if (!els.editTaskCourseInput) return;
    var course = value || "";
    var option = Array.from(els.editTaskCourseInput.options).find(function (item) {
      return item.value === course;
    });
    if (!option && course) {
      option = new Option(course, course);
      els.editTaskCourseInput.add(option);
    }
    els.editTaskCourseInput.value = course;
  }

  function openEditTaskModal(card) {
    if (!els.editTaskModal || !card) return;
    if (els.editTaskIdInput) els.editTaskIdInput.value = card.dataset.idTugas || "";
    if (els.editTaskNameInput) els.editTaskNameInput.value = card.dataset.namaTugas || "";
    setEditCourseValue(card.dataset.mataKuliah || "");
    if (els.editTaskDeadlineInput) els.editTaskDeadlineInput.value = card.dataset.deadline || "";

    els.editTaskModal.classList.add("tampil");
    els.editTaskModal.setAttribute("aria-hidden", "false");
    window.setTimeout(function () {
      if (els.editTaskNameInput) els.editTaskNameInput.focus();
    }, 80);
  }

  function closeEditTaskModal() {
    if (!els.editTaskModal) return;
    els.editTaskModal.classList.remove("tampil");
    els.editTaskModal.setAttribute("aria-hidden", "true");
    els.editTaskForm?.reset();
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
    const deadlineFilter = els.taskList.dataset.deadlineFilter || "semua";
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const cards = $$(".item-tugas", els.taskList);
    let visible = 0;

    cards.forEach(function (card) {
      const text = (card.dataset.search || card.textContent || "").toLowerCase();
      const done = card.dataset.status === "selesai";
      const deadline = card.dataset.deadline || "";
      const matchKeyword = !keyword || text.includes(keyword);
      const matchStatus = status === "semua" || (status === "selesai" ? done : !done);
      let matchDeadline = true;

      if (deadlineFilter === "hari_ini") {
        matchDeadline = deadline === formatDateValue(today);
      } else if (deadlineFilter === "besok") {
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        matchDeadline = deadline === formatDateValue(tomorrow);
      } else if (deadlineFilter === "dekat") {
        const due = parseDateValue(deadline);
        if (!due) {
          matchDeadline = false;
        } else {
          const diff = Math.round((due - today) / 86400000);
          matchDeadline = diff >= 0 && diff <= 7;
        }
      }

      const show = matchKeyword && matchStatus && matchDeadline;
      card.hidden = !show;
      if (show) visible += 1;
    });

    const empty = $("#pesanFilterTugasKosong");
    if (empty) {
      if (deadlineFilter === "hari_ini") {
        empty.textContent = "Tidak ada tugas dengan deadline hari ini";
      } else if (deadlineFilter === "besok") {
        empty.textContent = "Tidak ada tugas dengan deadline besok.";
      } else if (deadlineFilter === "dekat") {
        empty.textContent = "Tidak ada tugas dengan deadline dekat.";
      } else {
        empty.textContent = "Tidak ada tugas yang sesuai filter.";
      }
      empty.hidden = visible !== 0;
    }
  }

  function formatDateValue(date) {
    return date.getFullYear() + "-" +
      String(date.getMonth() + 1).padStart(2, "0") + "-" +
      String(date.getDate()).padStart(2, "0");
  }

  function parseDateValue(value) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value || "")) return null;
    const parts = value.split("-");
    return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
  }

  function clearDeadlineTaskFilter(updateUrl) {
    if (els.taskList) {
      els.taskList.dataset.deadlineFilter = "semua";
    }

    if (updateUrl) {
      const url = new URL(window.location.href);
      url.searchParams.delete("deadline_tugas");
      url.searchParams.set("filter_tugas", els.taskFilter?.value || "semua");
      window.history.replaceState({}, "", url);
    }
  }

  async function addCourse() {
    const value = (els.courseInput?.value || "").trim();
    if (!value) {
      if (els.courseError) els.courseError.textContent = "Nama mata kuliah wajib diisi.";
      return;
    }
    if (els.courseError) els.courseError.textContent = "";

    try {
      await apiPost("tambah_mata_kuliah", { nama_mata_kuliah: value });
      reloadWithPage("pengaturan");
    } catch (error) {
      if (els.courseError) els.courseError.textContent = error.message;
    }
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

  function openTaskPageWithFilter(filterValue, deadlineFilter) {
    const url = new URL(window.location.href);
    url.searchParams.set("halaman", "tugas");
    url.searchParams.set("filter_tugas", filterValue || "semua");
    if (deadlineFilter && deadlineFilter !== "semua") {
      url.searchParams.set("deadline_tugas", deadlineFilter);
    } else {
      url.searchParams.delete("deadline_tugas");
    }
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
          openTaskPageWithFilter("semua", "hari_ini");
        }
      });
    });

    els.taskFilterLinks.forEach(function (button) {
      button.addEventListener("click", function () {
        openTaskPageWithFilter(button.dataset.filterTugas || "semua", button.dataset.deadlineFilter || "semua");
      });
      if (button.tagName !== "BUTTON") {
        button.addEventListener("keydown", function (event) {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            openTaskPageWithFilter(button.dataset.filterTugas || "semua", button.dataset.deadlineFilter || "semua");
          }
        });
      }
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
      const submitButton = event.submitter || $(".tombol-utama", els.taskForm);
      var namaTugasEl = $("#inputNamaTugas");
      var mataKuliahEl = $("#pilihanMataKuliah");
      var deadlineEl = $("#inputDeadlineTugas");
      var nama = namaTugasEl ? namaTugasEl.value.trim() : "";
      var mataKuliah = mataKuliahEl ? mataKuliahEl.value.trim() : "";
      var deadline = deadlineEl ? deadlineEl.value : "";
      if (!nama || !deadline) {
        showToast("Nama tugas dan deadline wajib diisi.", "error");
        return;
      }

      try {
        setButtonLoading(submitButton, true, "Menyimpan...");
        await apiPost("tambah_tugas", {
          nama_tugas: nama,
          mata_kuliah: mataKuliah,
          deadline: deadline
        });
        reloadWithPage("tugas");
      } catch (error) {
        setButtonLoading(submitButton, false);
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
      const button = event.target.closest("[data-aksi]");
      if (!button) return;
      const card = button.closest(".item-tugas");
      if (!card) return;
      if (button.dataset.aksi === "edit-tugas") {
        openEditTaskModal(card);
        return;
      }
      if (button.dataset.aksi !== "hapus-tugas") return;
      openConfirm("Hapus tugas?", "Tugas ini akan dihapus dari StudyFlow.", async function () {
        await apiPost("hapus_tugas", { id: card.dataset.idTugas });
        reloadWithPage("tugas");
      });
    });

    els.editTaskForm?.addEventListener("submit", async function (event) {
      event.preventDefault();
      const submitButton = event.submitter || $(".tombol-modal-utama", els.editTaskForm);
      var id = els.editTaskIdInput ? els.editTaskIdInput.value : "";
      var nama = els.editTaskNameInput ? els.editTaskNameInput.value.trim() : "";
      var mataKuliah = els.editTaskCourseInput ? els.editTaskCourseInput.value.trim() : "";
      var deadline = els.editTaskDeadlineInput ? els.editTaskDeadlineInput.value : "";

      if (!id || !nama || !mataKuliah || !deadline) {
        showToast("Nama tugas, mata kuliah, dan deadline wajib diisi.", "error");
        return;
      }

      try {
        setButtonLoading(submitButton, true, "Menyimpan...");
        await apiPost("edit_tugas", {
          id: id,
          nama_tugas: nama,
          mata_kuliah: mataKuliah,
          deadline: deadline
        });
        reloadWithPage("tugas");
      } catch (error) {
        setButtonLoading(submitButton, false);
        showToast(error.message, "error");
      }
    });

    els.taskSearch?.addEventListener("input", filterTasks);
    els.taskFilter?.addEventListener("change", function () {
      clearDeadlineTaskFilter(true);
      filterTasks();
    });

    els.openScheduleButton?.addEventListener("click", function () {
      openScheduleModal();
    });
    els.closeEditTaskButton?.addEventListener("click", closeEditTaskModal);
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
      const submitButton = event.submitter || $(".tombol-modal-utama", els.scheduleForm);
      var nama = (els.scheduleNameInput ? els.scheduleNameInput.value : "").trim();
      var tanggal = els.scheduleDateInput ? els.scheduleDateInput.value : "";
      var jam = els.scheduleTimeInput ? els.scheduleTimeInput.value : "";
      var kategori = els.scheduleCategoryInput ? els.scheduleCategoryInput.value : "pribadi";

      if (!nama || !tanggal || !jam) {
        showToast("Nama, tanggal, dan jam jadwal wajib diisi.", "error");
        return;
      }

      // Validasi format jam HH:MM
      if (!/^\d{2}:\d{2}$/.test(jam)) {
        showToast("Format jam tidak valid. Gunakan format HH:MM.", "error");
        return;
      }

      try {
        setButtonLoading(submitButton, true, "Menyimpan...");
        await apiPost("tambah_jadwal", {
          nama_jadwal: nama,
          tanggal: tanggal,
          jam: jam,
          kategori: kategori
        });
        showToast("Jadwal berhasil ditambahkan!", "success");
        window.setTimeout(function () {
          reloadWithPage("kalender");
        }, 600);
      } catch (error) {
        setButtonLoading(submitButton, false);
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
        setButtonLoading(button, true);
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
      openConfirm("Hapus mata kuliah?", "Mata kuliah ini akan dihapus dari daftar akunmu.", async function () {
        await apiPost("hapus_mata_kuliah", { id: button.dataset.courseRemove });
        reloadWithPage("pengaturan");
      });
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
        if (modal === els.editTaskModal) closeEditTaskModal();
        if (modal === els.scheduleModal) closeScheduleModal();
        if (modal === els.detailModal) closeDetailModal();
        if (modal === els.confirmModal) closeConfirm();
      });
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        closeEditTaskModal();
        closeScheduleModal();
        closeDetailModal();
        closeConfirm();
      }
    });
  }

  function init() {
    if (els.nameInput) els.nameInput.value = profile.nama || "";
    if (els.notificationToggle) els.notificationToggle.checked = Boolean(Number(profile.notifikasi ?? 1));
    const params = new URLSearchParams(window.location.search);
    const requestedFilter = params.get("filter_tugas");
    const requestedDeadline = params.get("deadline_tugas");
    if (els.taskFilter && ["semua", "belum", "selesai"].includes(requestedFilter)) {
      els.taskFilter.value = requestedFilter;
    }
    if (els.taskList && ["hari_ini", "besok", "dekat"].includes(requestedDeadline)) {
      els.taskList.dataset.deadlineFilter = requestedDeadline;
    }
    syncDarkButtons(document.body.classList.contains("mode-gelap"));
    filterTasks();
    updateClock();
    window.setInterval(updateClock, 60000);
    setActivePage(state.activePage, false);
    wireEvents();

    // Toast login berhasil
    if (profile.flashLogin === 'berhasil') {
      window.setTimeout(function () {
        showToast("Login berhasil 👋 Selamat datang, " + (profile.nama || "kamu") + "!", "success");
      }, 400);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

// ============================================
// FITUR PENGATURAN TAMBAHAN
// Ganti Password | Ganti Email | Hapus Akun
// ============================================
(function () {
  function pesan(elId, teks, isError) {
    var el = document.getElementById(elId);
    if (!el) return;
    el.textContent = teks;
    el.style.color = isError ? 'var(--warna-bahaya, #e74c3c)' : 'var(--warna-sukses, #27ae60)';
  }

  function kirimJSON(aksi, data, cb) {
    fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(Object.assign({ aksi: aksi }, data))
    })
      .then(function (r) { return r.json(); })
      .then(cb)
      .catch(function () { cb({ status: 'error', message: 'Koneksi gagal' }); });
  }

  // --- GANTI PASSWORD ---
  var tombolGantiPassword = document.getElementById('tombolGantiPassword');
  if (tombolGantiPassword) {
    tombolGantiPassword.addEventListener('click', function () {
      var lama   = (document.getElementById('inputPasswordLama') || {}).value || '';
      var baru   = (document.getElementById('inputPasswordBaru') || {}).value || '';
      var konfirm = (document.getElementById('inputKonfirmasiPassword') || {}).value || '';

      pesan('pesanGantiPassword', '', true);

      if (!lama || !baru || !konfirm) {
        return pesan('pesanGantiPassword', 'Semua kolom wajib diisi.', true);
      }
      if (baru.length < 6) {
        return pesan('pesanGantiPassword', 'Password baru minimal 6 karakter.', true);
      }
      if (baru !== konfirm) {
        return pesan('pesanGantiPassword', 'Konfirmasi password tidak cocok.', true);
      }

      tombolGantiPassword.disabled = true;
      tombolGantiPassword.textContent = 'Menyimpan...';

      kirimJSON('ganti_password', {
        password_lama: lama,
        password_baru: baru,
        konfirmasi_password: konfirm
      }, function (res) {
        tombolGantiPassword.disabled = false;
        tombolGantiPassword.textContent = 'Simpan Password';
        if (res.status === 'success') {
          pesan('pesanGantiPassword', '✓ ' + res.message, false);
          document.getElementById('inputPasswordLama').value = '';
          document.getElementById('inputPasswordBaru').value = '';
          document.getElementById('inputKonfirmasiPassword').value = '';
        } else {
          pesan('pesanGantiPassword', res.message, true);
        }
      });
    });
  }

  // --- GANTI EMAIL ---
  var tombolGantiEmail = document.getElementById('tombolGantiEmail');
  if (tombolGantiEmail) {
    tombolGantiEmail.addEventListener('click', function () {
      var emailBaru = (document.getElementById('inputEmailBaru') || {}).value || '';
      var password  = (document.getElementById('inputPasswordKonfirmasiEmail') || {}).value || '';

      pesan('pesanGantiEmail', '', true);

      if (!emailBaru || !password) {
        return pesan('pesanGantiEmail', 'Email baru dan password wajib diisi.', true);
      }

      tombolGantiEmail.disabled = true;
      tombolGantiEmail.textContent = 'Menyimpan...';

      kirimJSON('ganti_email', {
        email_baru: emailBaru,
        password_konfirmasi: password
      }, function (res) {
        tombolGantiEmail.disabled = false;
        tombolGantiEmail.textContent = 'Simpan Email';
        if (res.status === 'success') {
          pesan('pesanGantiEmail', '✓ ' + res.message, false);
          var el = document.getElementById('emailSaatIni');
          if (el) el.textContent = emailBaru;
          document.getElementById('inputEmailBaru').value = '';
          document.getElementById('inputPasswordKonfirmasiEmail').value = '';
        } else {
          pesan('pesanGantiEmail', res.message, true);
        }
      });
    });
  }

  // --- HAPUS AKUN PERMANEN (Modal 2 Langkah) ---
  var SVG_EYE_OPEN   = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>';
  var SVG_EYE_CLOSED = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-6.5 0-10-8-10-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

  var modalHapusAkun       = document.getElementById('modalHapusAkun');
  var langkah1             = document.getElementById('hapusAkunLangkah1');
  var langkah2             = document.getElementById('hapusAkunLangkah2');
  var tombolHapusAkun      = document.getElementById('tombolHapusAkun');
  var tombolBatal1         = document.getElementById('tombolBatalHapusAkun1');
  var tombolLanjut         = document.getElementById('tombolLanjutHapusAkun');
  var tombolBatal2         = document.getElementById('tombolBatalHapusAkun2');
  var tombolKonfirmasi     = document.getElementById('tombolKonfirmasiHapusAkun');
  var inputPassHapus       = document.getElementById('inputPasswordHapusAkun');
  var toggleMataHapus      = document.querySelector('[data-toggle-password-hapus]');

  function bukaModalHapus() {
    if (!modalHapusAkun) return;
    // Reset ke langkah 1
    if (langkah1) langkah1.style.display = '';
    if (langkah2) langkah2.style.display = 'none';
    if (inputPassHapus) inputPassHapus.value = '';
    pesan('pesanHapusAkun', '', true);
    modalHapusAkun.classList.add('tampil');
    modalHapusAkun.setAttribute('aria-hidden', 'false');
  }

  function tutupModalHapus() {
    if (!modalHapusAkun) return;
    modalHapusAkun.classList.remove('tampil');
    modalHapusAkun.setAttribute('aria-hidden', 'true');
    if (inputPassHapus) inputPassHapus.value = '';
    pesan('pesanHapusAkun', '', true);
    // Reset tombol jika sempat disabled
    if (tombolKonfirmasi) {
      tombolKonfirmasi.disabled = false;
      tombolKonfirmasi.textContent = 'Hapus Akun Saya';
    }
  }

  // Buka modal dari tombol di panel
  if (tombolHapusAkun) {
    tombolHapusAkun.addEventListener('click', bukaModalHapus);
  }

  // Langkah 1 → Batal
  if (tombolBatal1) {
    tombolBatal1.addEventListener('click', tutupModalHapus);
  }

  // Langkah 1 → Lanjut ke langkah 2
  if (tombolLanjut) {
    tombolLanjut.addEventListener('click', function () {
      if (langkah1) langkah1.style.display = 'none';
      if (langkah2) langkah2.style.display = '';
      if (inputPassHapus) inputPassHapus.focus();
    });
  }

  // Langkah 2 → Kembali ke langkah 1
  if (tombolBatal2) {
    tombolBatal2.addEventListener('click', function () {
      if (langkah1) langkah1.style.display = '';
      if (langkah2) langkah2.style.display = 'none';
      pesan('pesanHapusAkun', '', true);
    });
  }

  // Toggle mata di modal hapus akun
  if (toggleMataHapus) {
    toggleMataHapus.addEventListener('click', function () {
      if (!inputPassHapus) return;
      var show = inputPassHapus.type === 'password';
      inputPassHapus.type = show ? 'text' : 'password';
      toggleMataHapus.setAttribute('aria-pressed', show ? 'true' : 'false');
      toggleMataHapus.querySelector('span').innerHTML = show ? SVG_EYE_CLOSED : SVG_EYE_OPEN;
    });
  }

  // Langkah 2 → Konfirmasi & hapus akun
  if (tombolKonfirmasi) {
    tombolKonfirmasi.addEventListener('click', function () {
      var password = inputPassHapus ? inputPassHapus.value : '';
      pesan('pesanHapusAkun', '', true);

      if (!password) {
        pesan('pesanHapusAkun', 'Masukkan password untuk konfirmasi.', true);
        if (inputPassHapus) inputPassHapus.focus();
        return;
      }

      tombolKonfirmasi.disabled = true;
      tombolKonfirmasi.textContent = 'Menghapus...';
      if (tombolBatal2) tombolBatal2.disabled = true;

      kirimJSON('hapus_akun', { password_konfirmasi: password }, function (res) {
        if (res.status === 'success') {
          window.location.href = 'login.php';
        } else {
          tombolKonfirmasi.disabled = false;
          tombolKonfirmasi.textContent = 'Hapus Akun Saya';
          if (tombolBatal2) tombolBatal2.disabled = false;
          pesan('pesanHapusAkun', res.message, true);
          if (inputPassHapus) {
            inputPassHapus.value = '';
            inputPassHapus.focus();
          }
        }
      });
    });
  }

  // Tutup modal saat klik latar belakang
  if (modalHapusAkun) {
    modalHapusAkun.addEventListener('click', function (e) {
      if (e.target === modalHapusAkun) tutupModalHapus();
    });
  }

  // Tutup modal dengan Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modalHapusAkun && modalHapusAkun.classList.contains('tampil')) {
      tutupModalHapus();
    }
  });

})();
