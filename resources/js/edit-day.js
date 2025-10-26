let DAY_DATE = null;
let BRANCH_CACHE = [];
let EMP_VAC_IDX = {}; // empId -> { is_vacation: boolean }
let SCHEDULE = {};    // { [branchId]: { Pagi: [...], Siang: [...] } }

// NEW: shift-times catalog
let SHIFT_TIMES_BY_GROUP = { Pagi: [], Siang: [] }; // [{id, code, start_time, end_time}]
let SHIFT_TIME_BY_ID = {};                           // id -> {id, code, group, ...}

// Company defaults
const DEFAULT_SHIFT_TIME_ID = { Pagi: 1, Siang: 3 };

window.openEditDay = function (dateStr) {
  DAY_DATE = dateStr;
  document.getElementById("editDayModal").classList.remove("hidden");
  document.getElementById("editDayTitle").textContent = `(${dateStr})`;

  fetch(`/jadwal/day?date=${encodeURIComponent(dateStr)}`)
    .then((r) => r.json())
    .then(({ date, items, branches, shift_times }) => {
      BRANCH_CACHE = branches || [];

      // shift_times may be grouped: { Pagi: [...], Siang: [...] }
      buildShiftTimes(shift_times || {});

      buildIndexes(BRANCH_CACHE, items || []); // index libur
      initBoard(items || []);                  // init + render
    })
    .catch((err) => {
      console.error("Error fetching data", err);
      document.getElementById("editDayBody").innerHTML =
        `<div class="text-red-600 text-sm">Gagal memuat data.</div>`;
    });
  };

window.closeEditDay = function () {
  document.getElementById("editDayModal").classList.add("hidden");
  document.getElementById("editDayBody").innerHTML = "";
  DAY_DATE = null;
};

/* =========================
   Catalog builders / helpers
   ========================= */

function buildShiftTimes(shift_times) {
  // Reset
  SHIFT_TIMES_BY_GROUP = { Pagi: [], Siang: [] };
  SHIFT_TIME_BY_ID = {};

  // shift_times may come grouped: { Pagi: [{id, code, ...}], Siang: [...] }
  ['Pagi', 'Siang'].forEach((g) => {
    const list = (shift_times[g] || []).map(st => ({
      id: st.id,
      code: st.code ?? codeFromTime(st), // fallback label if code missing
      start_time: st.start_time,
      end_time: st.end_time,
      group: g,
    }));
    SHIFT_TIMES_BY_GROUP[g] = list;
    list.forEach(st => { SHIFT_TIME_BY_ID[st.id] = st; });
  });
}

function codeFromTime(st) {
  // e.g. fallback label: "06:50-14:50"
  return `${(st.start_time || '').slice(0,5)}-${(st.end_time || '').slice(0,5)}`;
}

function defaultShiftTimeId(shift /* 'Pagi'|'Siang' */) {
  return DEFAULT_SHIFT_TIME_ID[shift] ?? DEFAULT_SHIFT_TIME_ID['Pagi'];
}

function nextShiftTimeId(shift, currentId) {
  const list = SHIFT_TIMES_BY_GROUP[shift] || [];
  if (list.length === 0) return currentId;

  const idx = list.findIndex(st => String(st.id) === String(currentId));
  const nextIdx = idx >= 0 ? (idx + 1) % list.length : 0;
  return list[nextIdx].id;
}

// NEW: get role index for an employee from BRANCH_CACHE
function getRoleIndex(empId) {
  for (const b of BRANCH_CACHE) {
    const e = (b.employees || []).find(x => String(x.id) === String(empId));
    if (e) {
      // try common shapes coming from backend
      const v =
        e.role_index ??
        e.roleIndex ??
        e.role?.index ??
        e.roles?.index ??
        e.index_role ??
        e.index; // last resort if your employee object already carries index
      if (v !== undefined && v !== null) return Number(v);
    }
  }
  // fallback: very large so they sink to bottom
  return 999999;
}

// NEW: comparator & sorters
function compareByRoleIndex(a, b) {
  const ai = a.role_index ?? 999999;
  const bi = b.role_index ?? 999999;
  if (ai !== bi) return ai - bi;
  // tie-break by name then id
  const an = (a.name || "").toString();
  const bn = (b.name || "").toString();
  const byName = an.localeCompare(bn, undefined, { sensitivity: "base" });
  if (byName !== 0) return byName;
  return String(a.id_employee).localeCompare(String(b.id_employee));
}

function sortBucket(branchId, shift) {
  const list = SCHEDULE[branchId]?.[shift];
  if (Array.isArray(list)) list.sort(compareByRoleIndex);
}

function sortAllBuckets() {
  Object.keys(SCHEDULE).forEach(bid => {
    ["Pagi","Siang"].forEach(s => sortBucket(bid, s));
  });
}


/* =========================
   Board init & render
   ========================= */

function initBoard(items) {
  buildSchedule(BRANCH_CACHE, items || []);
  renderBoard();
}

function renderBoard() {
  sortAllBuckets();
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
    head.innerHTML = `<div class="font-semibold">${b.name}</div>`;
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
        const payload = { fromBranchId: bid, fromShift: shift, id_employee: emp.id_employee };
        e.dataTransfer.setData("application/json", JSON.stringify(payload));
        e.dataTransfer.effectAllowed = "move";
      });

      // Left chunk: dot + name
      const left = document.createElement("div");
      left.className = "flex flex-col text-left";
      left.innerHTML = `
        <div class="flex items-center gap-2">
          <span class="inline-block w-2 h-2 rounded-full ${colorClassFor(emp, shift)}"></span>
          <span class="text-sm ${emp.is_vacation ? "text-red-600" : ""}">${emp.name}</span>
        </div>
      `;

      // Right chunk: shift-time badge (click to cycle)
      const right = document.createElement("button");
      right.type = "button";
      right.className =
        "text-[10px] leading-none px-1.5 py-0.5 rounded border bg-slate-50 hover:bg-slate-100 text-slate-700";
      right.title = "Klik untuk ganti varian jam";

      // render initial label
      right.textContent = labelForShiftTimeWithTime(emp.id_shift_time, shift);

      right.addEventListener("click", () => {
        const nextId = nextShiftTimeId(shift, emp.id_shift_time);
        emp.id_shift_time = nextId;
        right.textContent = labelForShiftTimeWithTime(nextId, shift);
      });

      item.appendChild(left);
      item.appendChild(right);
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

/* =========================
   Schedule mutations
   ========================= */

function moveEmployee(empId, fromBranchId, fromShift, toBranchId, toShift) {
  // Ensure bucket exists
  if (!SCHEDULE[toBranchId]) SCHEDULE[toBranchId] = { Pagi: [], Siang: [] };
  if (!SCHEDULE[toBranchId][toShift]) SCHEDULE[toBranchId][toShift] = [];

  // Remove from all buckets (dedupe)
  Object.keys(SCHEDULE).forEach(bid => {
    ["Pagi", "Siang"].forEach(s => {
      const list = SCHEDULE[bid][s] || [];
      const idx = list.findIndex(x => String(x.id_employee) === String(empId));
      if (idx !== -1) list.splice(idx, 1);
    });
  });

  // Add to target with default/kept shift-time
  const prev = findEmpInSchedule(empId); // after removal this is null
  const fallbackId = defaultShiftTimeId(toShift);

  SCHEDULE[toBranchId][toShift].push({
    id: null, // new placement
    id_employee: Number(empId),
    name: findEmpName(empId) || `Emp ${empId}`,
    is_vacation: getEmployeeVacation(empId),
    id_shift_time: fallbackId, // assign default variant for the target shift
    role_index: getRoleIndex(empId),
  });
  sortBucket(toBranchId, toShift);
  renderBoard();
}

/* =========================
   Builders: indexes & board state
   ========================= */

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
  // empty buckets per branch
  (branches || []).forEach(b => {
    SCHEDULE[String(b.id)] = { Pagi: [], Siang: [] };
  });

  const placed = new Set();

  // 1) Truth: existing items from backend
  (items || []).forEach(it => {
    const bid = String(it.id_branch);
    const shift = it.shift === "Siang" ? "Siang" : "Pagi";
    if (!SCHEDULE[bid]) SCHEDULE[bid] = { Pagi: [], Siang: [] };

    if (!placed.has(String(it.id_employee))) {
      const stId = it.id_shift_time || defaultShiftTimeId(shift);
      SCHEDULE[bid][shift].push({
        id: it.id || null,
        id_employee: it.id_employee,
        name: it.employee || `Emp ${it.id_employee}`,
        is_vacation: !!it.is_vacation,
        id_shift_time: stId,
        role_index: getRoleIndex(it.id_employee),
      });
      sortBucket(bid, shift);
      placed.add(String(it.id_employee));
    }
  });

  // 2) Employees with no item → default in original branch at Pagi
  (branches || []).forEach(b => {
    const bid = String(b.id);
    (b.employees || []).forEach(e => {
      const empId = String(e.id);
      if (placed.has(empId)) return;

      SCHEDULE[bid].Pagi.push({
        id: null,
        id_employee: e.id,
        name: e.name,
        is_vacation: getEmployeeVacation(e.id),
        id_shift_time: defaultShiftTimeId('Pagi'),
        role_index: getRoleIndex(e.id)
      });
      sortBucket(bid, 'Pagi');
      placed.add(empId);
    });
  });
}

/* =========================
   UI helpers
   ========================= */

function colorClassFor(empObj, shift) {
  if (empObj.is_vacation) return "bg-red-600";
  return shift === "Pagi" ? "bg-yellow-400" : "bg-blue-500";
}

function labelForShiftTime(id, shift) {
  const st = SHIFT_TIME_BY_ID[id];
  if (st && st.code) return st.code;
  // fallback: first available for that group
  const list = SHIFT_TIMES_BY_GROUP[shift] || [];
  return (list[0]?.code) || (st ? codeFromTime(st) : (shift === 'Pagi' ? 'P1' : 'S1'));
}

function getEmployeeVacation(empId) {
  return !!EMP_VAC_IDX[empId]?.is_vacation;
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

function findEmpName(empId) {
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

/* =========================
   Add row (manual)
   ========================= */

function refreshEmployeeSelect(wrapper) {
  const branchId = wrapper.querySelector("select").value;
  const empSel = wrapper.querySelectorAll("select")[1];
  const target = BRANCH_CACHE.find((b) => String(b.id) === String(branchId));
  const prev = empSel.dataset.value;

  empSel.innerHTML = "";
  (target?.employees || []).forEach((e) => {
    empSel.add(new Option(e.name, e.id, false, String(e.id) === String(prev)));
  });

  // selected + sync libur
  const currentEmpId = empSel.value || prev || (target?.employees?.[0]?.id ?? "");
  if (currentEmpId && empSel.value !== currentEmpId) empSel.value = currentEmpId;

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
    is_vacation: getEmployeeVacation(defEmpId),
  };
  body.appendChild(buildRow(row, body.children.length));
};

function updateLeaveLabel(wrapper, isVacation) {
  const leaveWrap = wrapper.querySelector('[data-leavewrap]');
  const span = leaveWrap?.querySelector('span');
  if (!span) return;
  span.textContent = isVacation ? "IYA" : "TIDAK";
  span.style.color = isVacation ? "#dc2626" : "#374151";
  span.style.fontWeight = isVacation ? "600" : "400";
}

function labelForShiftTimeWithTime(id, shift) {
  const st = SHIFT_TIME_BY_ID[id];
  if (!st) {
    return labelForShiftTime(id, shift);
  }
  const code = st.code || labelForShiftTime(id, shift);
  const start = (st.start_time || '').slice(0, 5);
  const end = (st.end_time || '').slice(0, 5);
  return `${code} (${start}–${end})`;
}


/* =========================
   Save
   ========================= */

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
          id_shift_time: emp.id_shift_time || defaultShiftTimeId(shift), // NEW
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
        console.log(data);
        throw new Error(data.message || "Gagal menyimpan.");
      }
      return r.json();
    })
    .then(() => window.location.reload())
    .catch((err) => alert(err.message));
};
