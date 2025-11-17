<div id="minutes-panel" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
  <div class="absolute inset-0 bg-black/40" data-close="minutes-backdrop"></div>
  <div id="minutes-panel-sheet"
       class="absolute right-0 top-0 h-full w-full sm:w-[420px] translate-x-full transition-transform duration-300">
    <div class="flex h-full flex-col bg-white shadow-xl">
      <div class="flex items-center justify-between border-b px-4 py-3">
        <div>
          <h3 class="text-base font-semibold text-slate-800">Confirm Minutes</h3>
          <p class="text-xs text-slate-500" id="mp-meta">—</p>
        </div>
        <button class="rounded-md p-2 text-slate-500 hover:bg-slate-100" data-close="minutes-close">✕</button>
      </div>

      <div class="flex-1 overflow-y-auto p-4">
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div class="rounded-lg bg-slate-50 p-3"><p class="text-rose-700 font-bold">Late</p><p id="mp-late" class="font-semibold">0</p></div>
          <div class="rounded-lg bg-slate-50 p-3"><p class="text-rose-700 font-bold">Early Leave</p><p id="mp-early-leave" class="font-semibold">0</p></div>
          <div class="rounded-lg bg-slate-50 p-3"><p class="text-emerald-700 font-bold">Early Check-in</p><p id="mp-early-in" class="font-semibold">0</p></div>
          <div class="rounded-lg bg-slate-50 p-3"><p class="text-emerald-700 font-bold">Overtime</p><p id="mp-ot" class="font-semibold">0</p></div>
        </div>

        <form id="minutes-form" class="mt-5 space-y-4">
          <input type="hidden" id="mp-id" value="">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Penalty Minutes</label>
            <input type="number" min="0" id="mp-penalty"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
            <p id="mp-penalty-hint" class="text-xs text-rose-700 mt-1"></p>
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">Overtime Applied</label>
            <input type="number" min="0" id="mp-ot-applied"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
            <p id="mp-ot-hint" class="text-xs text-emerald-700 mt-1"></p>
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">Note (optional)</label>
            <textarea id="mp-note" rows="3"
                      class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0"
                      placeholder="Reason/consideration..."></textarea>
          </div>
        </form>
      </div>

      <div class="flex items-center justify-between gap-2 border-t px-4 py-3">
        <button class="rounded-full border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50" id="mp-use-suggest">
          Use Suggestions
        </button>
        <div class="flex gap-2">
          <button class="rounded-full px-4 py-2 text-slate-700 hover:bg-slate-50" data-close="minutes-cancel">Cancel</button>
          <button class="rounded-full bg-[#318f8c] px-4 py-2 text-white font-semibold hover:opacity-90" id="mp-save">
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
