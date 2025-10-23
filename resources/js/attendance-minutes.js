import Swal from "sweetalert2";

(function () {
  const panel = document.getElementById('minutes-panel');
  if (!panel) return;

  const sheet = document.getElementById('minutes-panel-sheet');
  const els = {
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
    btnUse: document.getElementById('mp-use-suggest'),
    btnSave: document.getElementById('mp-save'),
  };

  let state = { id:null, cap:0, suggest:{ penalty:0, overtime:0 } };

  function showPanel(){ panel.classList.remove('hidden'); requestAnimationFrame(()=> sheet.classList.remove('translate-x-full')); }
  function hidePanel(){ sheet.classList.add('translate-x-full'); setTimeout(()=> panel.classList.add('hidden'), 300); }

  panel.addEventListener('click', e => { if (e.target.dataset.close) hidePanel(); });

  window.openMinutesPanel = async function (attendanceId) {
    const base = `${window.location.origin}/admin/attendances/${attendanceId}/minutes`;
    if (window.Swal) Swal.fire({ title:'Loading...', didOpen:()=>Swal.showLoading(), showConfirmButton:false, allowOutsideClick:false });

    try {
      const res = await fetch(base, { headers:{'Accept':'application/json'} });
      const json = await res.json();
      if (!res.ok || !json?.ok) throw new Error(json?.message || 'Failed to load');

      const a = json.attendance;
      state = { id:a.id, cap:a.overtime_minutes ?? 0, suggest:json.suggestions || {penalty:0,overtime:0}, saveUrl:base };

      els.meta.textContent = `${a.employee ?? 'Employee'} • ${a.branch ?? '-'} • ${a.date ?? '-'}`;
      els.id.value = a.id;
      els.late.textContent = a.late_minutes;
      els.earlyLeave.textContent = a.early_leave_minutes;
      els.earlyIn.textContent = a.early_checkin_minutes;
      els.ot.textContent = a.overtime_minutes;

      els.penalty.value = a.penalty_minutes || state.suggest.penalty || 0;
      els.penaltyHint.textContent = `Suggestion: ${state.suggest.penalty} min (Late + Early Leave − Early Check-in).`;
      els.otApplied.value = a.overtime_applied_minutes || state.suggest.overtime || 0;
      els.otApplied.max = state.cap;
      els.otHint.textContent = `Total overtime: ${state.cap} min.`;
      els.note.value = '';

      if (window.Swal) Swal.close();
      showPanel();
    } catch (err) {
      if (window.Swal) Swal.fire({ icon:'error', title:'Error', text: err.message || 'Cannot load', confirmButtonColor:'#318f8c' });
    }
  };

  els.btnUse.addEventListener('click', () => {
    els.penalty.value = state.suggest.penalty || 0;
    els.otApplied.value = Math.min(state.suggest.overtime || 0, state.cap || 0);
  });

  els.btnSave.addEventListener('click', async () => {
    const payload = {
      penalty_minutes: parseInt(els.penalty.value || '0',10),
      overtime_applied_minutes: parseInt(els.otApplied.value || '0',10),
      note: els.note.value || null,
      _token: document.querySelector('meta[name="csrf-token"]')?.content,
    };
    console.log('Payload : ', payload);
    
    if (payload.overtime_applied_minutes > state.cap)
      return Swal.fire({ icon:'error', title:'Validation', text:`Overtime cannot exceed ${state.cap} min.`, confirmButtonColor:'#318f8c' });

    Swal.fire({ title:'Saving...', didOpen:()=>Swal.showLoading(), showConfirmButton:false, allowOutsideClick:false });
    try {
      const res = await fetch(state.saveUrl, {
        method:'POST',
        headers:{'Accept':'application/json','Content-Type':'application/json'},
        body: JSON.stringify(payload),
      });
      const json = await res.json();
      console.log(json);
      
      if (!res.ok || !json?.ok) throw new Error(json?.message || 'Failed to save');
      Swal.fire({ icon:'success', title:'Saved', timer:1200, showConfirmButton:false });
      hidePanel();
    } catch (err) {
      Swal.fire({ icon:'error', title:'Error', text: err.message || 'Cannot save', confirmButtonColor:'#318f8c' });
    }
  });
})();
