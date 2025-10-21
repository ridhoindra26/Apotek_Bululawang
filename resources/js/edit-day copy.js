let DAY_DATE = null;
let BRANCH_CACHE = [];
let EMP_VAC_IDX = {}; // <— empId -> { is_vacation: boolean }
let SCHEDULE = {}; // { [branchId]: { Pagi: [...], Siang: [...] } }

window.openEditDay = function (dateStr) {
  DAY_DATE = dateStr;
  document.getElementById("editDayModal").classList.remove("hidden");
  document.getElementById("editDayTitle").textContent = `(${dateStr})`;

  fetch(`/jadwal/day?date=${encodeURIComponent(dateStr)}`)
    .then((r) => r.json())
    .then(({ date, items, branches, shift_times }) => {
      BRANCH_CACHE = branches || [];
      buildIndexes(BRANCH_CACHE, items || []); // index libur
      initBoard(items || []);                  // ← inisialisasi + render
    })
    .catch(() => {
      document.getElementById("editDayBody").innerHTML =
        `<div class="text-red-600 text-sm">Gagal memuat data.</div>`;
    });
};

window.closeEditDay = function () {
    document.getElementById("editDayModal").classList.add("hidden");
    document.getElementById("editDayBody").innerHTML = "";
    DAY_DATE = null;
};

function initBoard(items) {
  buildSchedule(BRANCH_CACHE, items || []);
  renderBoard();
}

function renderBoard() {
  const body = document.getElementById("editDayBody");
  body.innerHTML = "";

  const wrap = document.createElement("div");
  wrap.className = "w-full overflow-x-auto";

  const grid = document.createElement("div");
  grid.className = "min-w-[300px] grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 mx-auto";

  BRANCH_CACHE.forEach(b => {
    const bid = String(b.id);
    const col = document.createElement("div");
    col.className = "border rounded-lg p-3 bg-white shadow-sm flex flex-col";

    const head = document.createElement("div");
    head.className = "mb-2 flex items-center justify-between";
    head.innerHTML = `
      <div class="font-semibold">${b.name}</div>
    `;
    col.appendChild(head);

    ["Pagi", "Siang"].forEach(shift => {
      const section = document.createElement("div");
      section.className = "mb-3";

      const h = document.createElement("div");
      h.className = "text-sm font-medium mb-1 flex items-center gap-2";
      h.innerHTML = `
        <span class="inline-block w-2.5 h-2.5 rounded-full ${shift === "Pagi" ? "bg-yellow-400" : "bg-blue-500"}"></span>
        <span>${shift}</span>
      `;
      section.appendChild(h);

      const zone = document.createElement("div");
      zone.className = "min-h-12 rounded-md border border-dashed border-gray-300 p-2 space-y-1 transition-colors";
      zone.dataset.dropzone = "true";
      zone.dataset.branchId = bid;
      zone.dataset.shift = shift;

      zone.addEventListener("dragover", (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = "move";
        zone.classList.add("bg-gray-50");
      });
      zone.addEventListener("dragleave", () => {
        zone.classList.remove("bg-gray-50");
      });
      zone.addEventListener("drop", (e) => {
        e.preventDefault();
        zone.classList.remove("bg-gray-50");
        const data = e.dataTransfer.getData("application/json");
        if (!data) return;
        const { fromBranchId, fromShift, id_employee } = JSON.parse(data);
        const dropped = findEmpInSchedule(String(id_employee));
        if (dropped?.is_vacation) {
          alert("Tidak dapat memindahkan karyawan yang sedang libur");
          return;
        }
        moveEmployee(String(id_employee), String(fromBranchId), String(fromShift), bid, shift);
      });

      (SCHEDULE[bid]?.[shift] || []).forEach(emp => {
        const item = document.createElement("div");
        item.className =
          "flex items-center justify-between gap-2 px-2 py-1 rounded border bg-white hover:bg-gray-50 cursor-move";
        item.draggable = true;
        item.dataset.empId = emp.id_employee;

        item.addEventListener("dragstart", (e) => {
          const payload = {
            fromBranchId: bid,
            fromShift: shift,
            id_employee: emp.id_employee,
          };
          e.dataTransfer.setData("application/json", JSON.stringify(payload));
          e.dataTransfer.effectAllowed = "move";
        });

        const left = document.createElement("div");
        left.className = "flex items-center gap-2";
        const dot = document.createElement("span");
        dot.className = `inline-block w-2 h-2 rounded-full ${colorClassFor(emp, shift)}`;
        const name = document.createElement("span");
        name.className = `text-sm ${emp.is_vacation ? "text-red-600" : ""}`;
        name.textContent = emp.name
        left.appendChild(dot);
        left.appendChild(name);

        item.appendChild(left);
        zone.appendChild(item);
      });

      section.appendChild(zone);
      col.appendChild(section);
    });

    grid.appendChild(col);
  });

  wrap.appendChild(grid);
  body.appendChild(wrap);
}

function moveEmployee(empId, fromBranchId, fromShift, toBranchId, toShift) {
  // Pastikan bucket ada
  if (!SCHEDULE[toBranchId]) SCHEDULE[toBranchId] = { Pagi: [], Siang: [] };
  if (!SCHEDULE[toBranchId][toShift]) SCHEDULE[toBranchId][toShift] = [];

  // HAPUS employee ini dari SEMUA cabang & shift (supaya tidak dobel)
  Object.keys(SCHEDULE).forEach(bid => {
    ["Pagi", "Siang"].forEach(s => {
      const list = SCHEDULE[bid][s] || [];
      const idx = list.findIndex(x => String(x.id_employee) === String(empId));
      if (idx !== -1) list.splice(idx, 1);
    });
  });

  // Tambahkan ke tujuan
  SCHEDULE[toBranchId][toShift].push({
    // kalau kamu ingin bawa 'id' lama saat pindah shift cabang sama, kamu bisa cari dulu dari fromList.
    id: null, // biasa di-reset; backend bisa generate baru atau logikamu sendiri
    id_employee: Number(empId),
    name: findEmpName(empId) || `Emp ${empId}`,
    is_vacation: getEmployeeVacation(empId),
  });

  renderBoard();
}

function findEmpName(empId) {
  // coba cari dari SCHEDULE (kalau masih ada sisa), atau dari BRANCH_CACHE
  for (const b of Object.values(SCHEDULE)) {
    for (const s of ["Pagi","Siang"]) {
      const f = (b[s] || []).find(x => String(x.id_employee) === String(empId));
      if (f) return f.name;
    }
  }
  for (const b of BRANCH_CACHE) {
    const f = (b.employees || []).find(e => String(e.id) === String(empId));
    if (f) return f.name;
  }
  return null;
}

function buildRow(row, idx) {
  const wrapper = document.createElement("div");
  wrapper.className = "grid grid-cols-12 gap-2 items-end border p-2 rounded mb-1";
  wrapper.dataset.rowIndex = idx;

  // ——— Select Cabang
  const branchSel = document.createElement("select");
  branchSel.className = "col-span-3 border rounded px-2 py-1";
  BRANCH_CACHE.forEach((b) => {
    branchSel.add(new Option(b.name, b.id, false, String(b.id) === String(row.id_branch)));
  });
  branchSel.onchange = () => refreshEmployeeSelect(wrapper);

  // ——— Select Karyawan
  const empSel = document.createElement("select");
  empSel.className = "col-span-4 border rounded px-2 py-1";
  empSel.dataset.value = row.id_employee || "";
  // setiap employee berubah → sync libur
  empSel.onchange = () => {
    const empId = empSel.value;
    const vac = getEmployeeVacation(empId);
    wrapper.dataset.isVacation = vac ? "true" : "false";
    updateLeaveLabel(wrapper, vac);
  };
  setTimeout(() => refreshEmployeeSelect(wrapper), 0);

  // ——— Select Shift
  const shiftSel = document.createElement("select");
  shiftSel.className = "col-span-2 border rounded px-2 py-1";
  ["Pagi", "Siang"].forEach((s) =>
    shiftSel.add(new Option(s, s, false, s === (row.shift || "Pagi")))
  );

  // ——— Libur (read-only label)
  const leaveWrap = document.createElement("div");
  leaveWrap.className = "col-span-2 text-sm font-medium flex items-center gap-1";
  leaveWrap.setAttribute("data-leavewrap", "true");
  const leaveSpan = document.createElement("span");
  // nilai awal mengikuti row.is_vacation (jika ada); kalau tidak, nanti diset saat empSel resolved
  const initVac = !!row.is_vacation;
  leaveSpan.textContent = initVac ? "IYA" : "TIDAK";
  leaveSpan.style.color = initVac ? "#dc2626" : "#374151";
  leaveSpan.style.fontWeight = initVac ? "600" : "400";
  leaveWrap.appendChild(leaveSpan);

  // ——— Hapus
  const delBtn = document.createElement("button");
  delBtn.className = "col-span-1 text-red-600 text-sm";
  delBtn.textContent = "Hapus";
  delBtn.onclick = () => wrapper.remove();

  // ——— Hidden id
  const idInput = document.createElement("input");
  idInput.type = "hidden";
  idInput.value = row.id || "";

  const col = (el, label, span) => {
    const c = document.createElement("div");
    c.className = `col-span-${span}`;
    if (label) {
      const lbl = document.createElement("div");
      lbl.className = "text-xs text-gray-500";
      lbl.textContent = label;
      c.appendChild(lbl);
    }
    c.appendChild(el);
    return c;
  };

  wrapper.appendChild(col(branchSel, "Cabang", 3));
  wrapper.appendChild(col(empSel, "Karyawan", 4));
  wrapper.appendChild(col(shiftSel, "Shift", 2));
  wrapper.appendChild(col(leaveWrap, "Libur", 2));
  wrapper.appendChild(col(delBtn, "", 1));
  wrapper.appendChild(idInput);

  // dataset.isVacation awal (supaya payload konsisten)
  wrapper.dataset.isVacation = initVac ? "true" : "false";

  return wrapper;
}

function buildIndexes(branches, items) {
  EMP_VAC_IDX = {};
  (branches || []).forEach((b) => {
    (b.employees || []).forEach((e) => {
      let vac = e.is_vacation;
      if (vac === undefined && Array.isArray(e.vacations)) {
        vac = !!e.vacations.find((v) => String(v.date) === String(DAY_DATE));
      }
      if (vac === undefined && Array.isArray(items)) {
        const it = items.find((i) => String(i.id_employee) === String(e.id));
        vac = !!it?.is_vacation;
      }
      EMP_VAC_IDX[e.id] = { is_vacation: !!vac };
    });
  });
}

function buildSchedule(branches, items) {
  SCHEDULE = {};
  // siapkan bucket kosong per cabang
  (branches || []).forEach(b => {
    SCHEDULE[String(b.id)] = { Pagi: [], Siang: [] };
  });

  const placed = new Set(); // id_employee yang sudah ditempatkan

  // 1) Tempatkan semua karyawan yang punya jadwal di items → jadi sumber kebenaran
  (items || []).forEach(it => {
    const bid = String(it.id_branch);
    const shift = it.shift === "Siang" ? "Siang" : "Pagi";
    if (!SCHEDULE[bid]) {
      // jika cabang belum ada di branches, buatkan slotnya
      SCHEDULE[bid] = { Pagi: [], Siang: [] };
    }
    if (!placed.has(String(it.id_employee))) {
      SCHEDULE[bid][shift].push({
        id: it.id || null,
        id_employee: it.id_employee,
        name: it.employee || `Emp ${it.id_employee}`,
        is_vacation: !!it.is_vacation,
      });
      placed.add(String(it.id_employee));
    }
  });

  // 2) Untuk karyawan yang tidak ada di items, taruh default di cabang asal (shift Pagi)
  (branches || []).forEach(b => {
    const bid = String(b.id);
    (b.employees || []).forEach(e => {
      const empId = String(e.id);
      if (placed.has(empId)) return; // sudah ditaruh lewat items
      SCHEDULE[bid].Pagi.push({
        id: null,
        id_employee: e.id,
        name: e.name,
        is_vacation: getEmployeeVacation(e.id),
      });
      placed.add(empId);
    });
  });
}

function colorClassFor(empObj, shift) {
  if (empObj.is_vacation) return "bg-red-600";
  return shift === "Pagi" ? "bg-yellow-400" : "bg-blue-500";
}

function getEmployeeVacation(empId) {
  return !!EMP_VAC_IDX[empId]?.is_vacation;
}

function updateLeaveLabel(wrapper, isVacation) {
  // label “Libur” kita tampilkan sebagai teks saja, bukan input
  const leaveWrap = wrapper.querySelector('[data-leavewrap]');
  const span = leaveWrap?.querySelector('span');
  if (!span) return;
  if (isVacation) {
    span.textContent = "IYA";
    span.style.color = "#dc2626";
    span.style.fontWeight = "600";
  } else {
    span.textContent = "TIDAK";
    span.style.color = "#374151";
    span.style.fontWeight = "400";
  }
}

function findEmpInSchedule(empId) {
  for (const [bid, byShift] of Object.entries(SCHEDULE)) {
    for (const s of ["Pagi","Siang"]) {
      const f = (byShift[s] || []).find(x => String(x.id_employee) === String(empId));
      if (f) return f;
    }
  }
  return null;
}

function refreshEmployeeSelect(wrapper) {
  const branchId = wrapper.querySelector("select").value;
  const empSel = wrapper.querySelectorAll("select")[1];
  const target = BRANCH_CACHE.find((b) => String(b.id) === String(branchId));
  const prev = empSel.dataset.value;

  empSel.innerHTML = "";
  (target?.employees || []).forEach((e) => {
    empSel.add(new Option(e.name, e.id, false, String(e.id) === String(prev)));
  });

  // setelah opsi terbentuk, tentukan selected dan sync libur
  const currentEmpId = empSel.value || prev || (target?.employees?.[0]?.id ?? "");
  if (currentEmpId && empSel.value !== currentEmpId) {
    empSel.value = currentEmpId;
  }
  const vac = getEmployeeVacation(empSel.value);
  wrapper.dataset.isVacation = vac ? "true" : "false";
  updateLeaveLabel(wrapper, vac);
}

window.addRow = function () {
  const body = document.getElementById("editDayBody");
  const defBranch = BRANCH_CACHE[0];
  const defEmpId = defBranch?.employees?.[0]?.id || "";
  const row = {
    id_branch: defBranch?.id || "",
    id_employee: defEmpId,
    shift: "Pagi",
    is_vacation: getEmployeeVacation(defEmpId), // ikut employee
  };
  body.appendChild(buildRow(row, body.children.length));
};

function collectPayload() {
  const items = [];
  Object.entries(SCHEDULE).forEach(([branchId, byShift]) => {
    ["Pagi", "Siang"].forEach(shift => {
      (byShift[shift] || []).forEach(emp => {
        items.push({
          id: emp.id || null,
          id_branch: Number(branchId),
          id_employee: emp.id_employee,
          shift: shift,
          is_vacation: !!emp.is_vacation,
        });
      });
    });
  });
  return { date: DAY_DATE, items };
}

window.saveEditDay = function () {
    const payload = collectPayload();
    console.log(payload);
    
    fetch(`/jadwal/day`, {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
    })
        .then(async (r) => {
            if (!r.ok) {
                const data = await r.json().catch(() => ({}));
                throw new Error(data.message || "Gagal menyimpan.");
            }
            return r.json();
        })
        .then(() => window.location.reload())
        .catch((err) => alert(err.message));
};
