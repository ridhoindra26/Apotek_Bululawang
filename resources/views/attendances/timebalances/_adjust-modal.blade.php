<div id="adjustModal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl border border-slate-200 relative">

    <button type="button" id="adjustCloseBtn"
            class="absolute top-3 right-3 text-slate-400 hover:text-slate-600">
      ✕
    </button>

    <h3 class="text-lg font-semibold text-slate-800 mb-4">Adjust Time Balance</h3>
    <form id="adjustForm" action="{{ route('attendances.balance.adjust', $employeeId) }}" method="POST" class="space-y-3">
      @csrf
      {{-- <div>
        <label class="block text-sm text-slate-600 mb-1">Type</label>
        <select name="type" required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
          <option value="">Select Type</option>
          <option value="overtime_add">Overtime Add (Credit)</option>
          <option value="overtime_spend">Overtime Spend (Use)</option>
          <option value="penalty_add">Penalty Add (Debt)</option>
          <option value="penalty_reduce">Penalty Reduce (Cancel Debt)</option>
        </select>
      </div> --}}

      <div>
        <label class="block text-sm !text-emerald-700 mb-1">Credit Minutes</label>
        <input type="number" name="penalty_minutes" min="0" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
      </div>

      <div>
        <label class="block text-sm !text-rose-700 mb-1">Debt Minutes</label>
        <input type="number" name="overtime_applied_minutes" min="0" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
      </div>

      <div>
        <label class="block text-sm text-slate-600 mb-1">Note</label>
        <textarea name="note" rows="2"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[#318f8c] focus:ring-0"
                  placeholder="Describe adjustment reason..."
                  required></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-3">
        <button type="button" id="adjustCancelBtn"
                class="rounded-md border px-4 py-2 text-slate-700 hover:bg-slate-50">Cancel</button>
        <button type="submit"
                class="rounded-md bg-[#318f8c] px-4 py-2 text-white font-semibold hover:bg-[#2b7b79]">Save</button>
      </div>
    </form>
  </div>
</div>
