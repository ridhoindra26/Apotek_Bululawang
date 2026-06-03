import Swal from "sweetalert2";

(function () {
  // make sure Swal is available globally (optional but nice)
  window.Swal = window.Swal || Swal;

    // ---------------- RESET (CHOOSE CHECK-IN / CHECK-OUT) ----------------
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const RESET_ENDPOINT = '/admin/attendances/reset';

  async function postReset(attendanceId, mode) {
    if (!RESET_ENDPOINT) throw new Error('Reset endpoint is not defined.');
    if (!csrf) throw new Error('CSRF token not found.');

    const res = await fetch(RESET_ENDPOINT, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify({
        attendance_id: attendanceId,
        mode: mode,
      }),
    });

    let json = null;
    try { json = await res.json(); } catch (_) {}

    if (!res.ok) {
      // try to show laravel validation errors if any
      const msg =
        json?.message ||
        (json?.errors ? Object.values(json.errors).flat().join(' ') : null) ||
        'Failed to reset.';
      throw new Error(msg);
    }

    return json;
  }

  // Accept either: handleResetChoice(this) OR handleResetChoice(123)
  window.handleResetChoice = async function (arg) {
    const attendanceId =
      typeof arg === 'number'
        ? arg
        : parseInt(arg?.dataset?.id || '0', 10);

    if (!attendanceId) {
      console.error('Invalid attendance id for reset.');
      return;
    }

    if (!window.Swal) {
      // fallback if Swal missing
      const mode = prompt('Type: check_in / check_out', 'check_in');
      if (!mode) return;
      const ok = confirm(`Reset ${mode}?`);
      if (!ok) return;

      try {
        await postReset(attendanceId, mode);
        window.location.reload();
      } catch (e) {
        alert(e.message || 'Failed to reset.');
      }
      return;
    }

    // Step 1: choose mode
    const pick = await window.Swal.fire({
      title: 'Reset which part?',
      input: 'radio',
      inputOptions: {
        check_in: 'Check-in',
        check_out: 'Check-out',
      },
      inputValidator: (value) => (!value ? 'Please select one option.' : undefined),
      showCancelButton: true,
      confirmButtonText: 'Continue',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#318f8c',
      cancelButtonColor: '#6b7280',
    });

    if (!pick.isConfirmed) return;
    const mode = pick.value;

    // Step 2: confirm
    const conf = await window.Swal.fire({
      title: 'Are you sure?',
      text:
        mode === 'check_in'
          ? 'This will reset check-in time and photo, and will affect calculations.'
          : mode === 'check_out'
          ? 'This will reset check-out time and photo, and will affect calculations.'
          : 'This will reset both check-in and check-out (time + photo) and affect calculations.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, reset',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#318f8c',
      cancelButtonColor: '#6b7280',
    });

    if (!conf.isConfirmed) return;

    // Step 3: execute
    try {
      window.Swal.fire({
        title: 'Resetting...',
        didOpen: () => window.Swal.showLoading(),
        showConfirmButton: false,
        allowOutsideClick: false,
      });

      await postReset(attendanceId, mode);

      window.Swal.fire({
        icon: 'success',
        title: 'Reset done',
        timer: 1000,
        showConfirmButton: false,
      });

      window.location.reload();
    } catch (err) {
      console.error(err);
      window.Swal.fire({
        icon: 'error',
        title: 'Error',
        text: err.message || 'Failed to reset.',
        confirmButtonColor: '#318f8c',
      });
    }
  };

  // Minutes Panel

  const panel = document.getElementById('minutes-panel');
  const sheet = document.getElementById('minutes-panel-sheet');

  const els = panel ? {
    meta: document.getElementById('mp-meta'),
    id: document.getElementById('mp-id'),
    late: document.getElementById('mp-late'),
    earlyLeave: document.getElementById('mp-early-leave'),
    earlyIn: document.getElementById('mp-early-in'),
    ot: document.getElementById('mp-ot'),
    penalty: document.getElementById('mp-penalty'),
    penaltyHint: document.getElementById('mp-penalty-hint'),
    otApplied: document.getElementById('mp-ot-applied'),
    otHint: document.getElementById('mp-ot-hint'),
    note: document.getElementById('mp-note'),
    isLate: document.getElementById('mp-is-late'),
    isLateHint: document.getElementById('mp-is-late-hint'),
    lateTypeWrapper: document.getElementById('mp-late-type-wrapper'),
    lateTypeHint: document.getElementById('mp-late-type-hint'),
    btnUse: document.getElementById('mp-use-suggest'),
    btnSave: document.getElementById('mp-save'),
  } : {};

  let state = { id: null, cap: 0, suggest: { penalty: 0, overtime: 0 }, saveUrl: null };

  function showPanel() {
    if (!panel || !sheet) return;
    panel.classList.remove('hidden');
    requestAnimationFrame(() => sheet.classList.remove('translate-x-full'));
  }

  function hidePanel() {
    if (!panel || !sheet) return;
    sheet.classList.add('translate-x-full');
    setTimeout(() => panel.classList.add('hidden'), 300);
  }

  if (panel) {
    panel.addEventListener('click', e => {
      if (e.target.dataset.close) hidePanel();
    });
  }

  function setLateType(value) {
    document.querySelectorAll('input[name="mp-late-type"]').forEach((radio) => {
      radio.checked = radio.value === value;
    });
  }

  function getLateType() {
    const selected = document.querySelector('input[name="mp-late-type"]:checked');
    return selected ? selected.value : null;
  }

  function syncLateTypeVisibility() {
    if (!els.isLate || !els.lateTypeWrapper) return;

    const active = els.isLate.checked;

    els.lateTypeWrapper.classList.toggle('hidden', !active);

    if (!active) {
      setLateType(null);
    }
  }

  els.isLate?.addEventListener('change', syncLateTypeVisibility);

  // -------------- GLOBAL WRAPPER (CONFIRM EDIT) --------------
  window.handleMinutesPanel = function (attendanceId, isConfirmed) {
    // console.log('handleMinutesPanel called:', { attendanceId, isConfirmed });

    // not confirmed => open directly
    if (!isConfirmed) {
      return window.openMinutesPanel(attendanceId);
    }

    // confirmed => ask first
    if (!window.Swal) {
      console.warn('Swal not found, opening directly');
      return window.openMinutesPanel(attendanceId);
    }

    window.Swal.fire({
      title: 'Edit this attendance?',
      text: 'This record has already been confirmed. Editing may change time balance. Continue?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, edit',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#318f8c',
      cancelButtonColor: '#6b7280',
    }).then((result) => {
      if (result.isConfirmed) {
        window.openMinutesPanel(attendanceId);
      }
    });
  };

  // -------------- GLOBAL MAIN FUNCTION (LOAD + OPEN PANEL) --------------
  window.openMinutesPanel = async function (attendanceId) {
    if (!panel) {
      console.error('minutes-panel element not found in DOM');
      return;
    }

    const base = `${window.location.origin}/admin/attendances/${attendanceId}/minutes`;

    if (window.Swal) {
      window.Swal.fire({
        title: 'Loading...',
        didOpen: () => window.Swal.showLoading(),
        showConfirmButton: false,
        allowOutsideClick: false
      });
    }

    try {
      const res = await fetch(base, {
        headers: {
          'Accept': 'application/json'
        }
      });

      const json = await res.json();

      if (!res.ok || !json?.ok) {
        throw new Error(json?.message || 'Failed to load');
      }

      const a = json.attendance;

      const rawLate = Number(a.late_minutes ?? 0);
      const earlyLeave = Number(a.early_leave_minutes ?? 0);
      const earlyIn = Number(a.early_checkin_minutes ?? 0);
      const overtime = Number(a.overtime_minutes ?? 0);

      const suggestedIsLate = Boolean(json.suggestions?.is_late ?? rawLate > 0);

      state = {
        id: a.id,
        cap: overtime + earlyIn,
        suggest: {
          penalty: Number(json.suggestions?.penalty ?? 0),
          overtime: Number(json.suggestions?.overtime ?? 0),
          is_late: suggestedIsLate,
        },
        saveUrl: base
      };

      // fill UI
      els.meta.textContent = `${a.employee ?? 'Employee'} • ${a.branch ?? '-'} • ${a.date ?? '-'}`;
      els.id.value = a.id;

      els.late.textContent = rawLate;
      els.earlyLeave.textContent = earlyLeave;
      els.earlyIn.textContent = earlyIn;
      els.ot.textContent = overtime;

      // is_late
      if (els.isLate) {
        els.isLate.checked = Boolean(a.is_late);
      }

      setLateType(a.late_type || null);
      syncLateTypeVisibility();

      if (els.isLateHint) {
        els.isLateHint.textContent = rawLate > 0
          ? `System detected ${rawLate} min raw late. Admin can still decide whether this counts as official late.`
          : `No raw late detected. Admin can still mark this as late if needed.`;
      }

      els.penalty.value = a.penalty_minutes || 0;
      els.penaltyHint.textContent = `Suggestion: ${state.suggest.penalty} min (Late + Early Leave).`;

      els.otApplied.value = a.overtime_applied_minutes || 0;
      els.otApplied.max = state.cap;
      els.otHint.textContent = `Total Overtime: ${state.cap} min.`;

      els.note.value = a.minutes_note || '';

      if (window.Swal) window.Swal.close();

      showPanel();
    } catch (err) {
      console.error(err);

      if (window.Swal) {
        window.Swal.fire({
          icon: 'error',
          title: 'Error',
          text: err.message || 'Cannot load',
          confirmButtonColor: '#318f8c'
        });
      }
    }
  };

  // -------------- BUTTON HANDLERS (IF PANEL EXISTS) --------------
  if (panel && els.btnUse && els.btnSave) {
    els.btnUse.addEventListener('click', () => {
      els.penalty.value = state.suggest.penalty || 0;
      els.otApplied.value = Math.min(state.suggest.overtime || 0, state.cap || 0);
    });

    els.btnSave.addEventListener('click', async () => {

      const isLate = els.isLate ? els.isLate.checked : false;
      const lateType = isLate ? getLateType() : null;

      if (isLate && !lateType) {
        Swal.fire({
          icon: 'warning',
          title: 'Late Type Required',
          text: 'Pilih jenis telat: Dengan Izin atau Tanpa Izin.',
          confirmButtonColor: '#318f8c'
        });
        return;
      }
      
      const payload = {
        penalty_minutes: parseInt(els.penalty.value || '0', 10),
        overtime_applied_minutes: parseInt(els.otApplied.value || '0', 10),
        note: els.note.value || null,
        is_late: els.isLate ? els.isLate.checked : null,
        late_type: lateType,
        _token: document.querySelector('meta[name="csrf-token"]')?.content,
      };
      // console.log('Payload : ', payload);

      if (payload.overtime_applied_minutes > state.cap) {
        return window.Swal && window.Swal.fire({
          icon: 'error',
          title: 'Validation',
          text: `Overtime cannot exceed ${state.cap} min.`,
          confirmButtonColor: '#318f8c'
        });
      }

      if (window.Swal) {
        window.Swal.fire({
          title: 'Saving...',
          didOpen: () => window.Swal.showLoading(),
          showConfirmButton: false,
          allowOutsideClick: false
        });
      }

      try {
        const res = await fetch(state.saveUrl, {
          method: 'POST',
          headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        const json = await res.json();
        // console.log('Save response:', json);

        if (!res.ok || !json?.ok) throw new Error(json?.message || 'Failed to save');

        if (window.Swal) {
          window.Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false });
        }
        window.location.reload();
      } catch (err) {
        console.error(err);
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message || 'Cannot save',
            confirmButtonColor: '#318f8c'
          });
        }
      }
    });
  }
})();
