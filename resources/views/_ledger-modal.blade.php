<div x-data="ledgerModal(@js($endpoint))"
     @open-ledger.window="open()"
     x-cloak
     class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     x-show="openModal"
     x-transition
     style="display: none;"> {{-- keep out of flow until Alpine starts --}}
  <!-- Overlay -->
  <div class="absolute inset-0 bg-black/30" @click="close()"></div>

  <!-- Panel -->
  <div class="relative w-full sm:max-w-2xl sm:rounded-2xl bg-white shadow-lg
              max-h-[85vh] overflow-hidden">
    <div class="flex items-center justify-between border-b px-4 sm:px-6 py-3">
      <h3 class="font-semibold text-slate-800">Time Ledger</h3>
      <button @click="close()" class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100">✕</button>
    </div>

    <div class="p-4 sm:p-6 overflow-y-auto">
      <template x-if="loading">
        <div class="text-sm text-slate-500">Loading…</div>
      </template>

      <template x-if="!loading && items.length === 0">
        <div class="text-sm text-slate-400">Ledger kosong.</div>
      </template>

      <template x-if="!loading && items.length">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm border-collapse">
            <thead>
              <tr class="text-left text-slate-500 border-b">
                <th class="py-2 pr-4">Tanggal</th>
                <th class="py-2 pr-4">Waktu</th>
                <th class="py-2 pr-4">Tipe</th>
                <th class="py-2 pr-4 text-right">Menit</th>
                <th class="py-2 pr-4">Sumber</th>
                <th class="py-2 pr-4">Catatan</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="(row, idx) in items" :key="idx">
                <tr class="border-b hover:bg-slate-50">
                  <td class="py-2 pr-4" x-text="row.date || '—'"></td>
                  <td class="py-2 pr-4" x-text="row.time || '—'"></td>
                  <td class="py-2 pr-4">
                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium"
                          :class="badgeClass(row.type)"
                          x-text="labelType(row.type)"></span>
                  </td>
                  <td class="py-2 pr-4 text-right" x-text="fmtMinutes(row.type, row.minutes)"></td>
                  <td class="py-2 pr-4 capitalize" x-text="row.source || '—'"></td>
                  <td class="py-2 pr-4" x-text="row.note || '—'"></td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </template>
    </div>

    <div class="border-t px-4 sm:px-6 py-3 flex justify-end">
      <button @click="close()" class="rounded-md border px-4 py-2 text-sm hover:bg-slate-50">Tutup</button>
    </div>
  </div>
</div>
