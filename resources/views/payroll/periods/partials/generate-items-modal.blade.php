{{-- Generate Items Modal (expects Alpine state from parent: genOpen, q, filtered, selected, selectedCount, selectAllFiltered, clearAll) --}}
<div x-cloak x-show="genOpen" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/40" @click="genOpen=false"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <div class="text-sm text-slate-500">Generate Payroll Items</div>
                    <div class="font-semibold text-slate-900">Pilih Karyawan</div>
                </div>
                <button type="button" class="text-slate-500 hover:text-slate-700" @click="genOpen=false">✕</button>
            </div>

            <form method="POST" action="{{ route('payroll.periods.generate-items', $period->id) }}" class="p-4 space-y-3">
                @csrf

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <input type="text"
                           x-model="q"
                           class="w-full sm:w-80 rounded-lg border-slate-200 text-sm"
                           placeholder="Cari nama karyawan...">

                    <div class="flex gap-2">
                        <button type="button"
                                @click="selectAllFiltered()"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">
                            Select All
                        </button>

                        <button type="button"
                                @click="clearAll()"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">
                            Clear
                        </button>
                    </div>
                </div>

                <div class="text-xs text-slate-500">
                    Dipilih: <span class="font-medium text-slate-900" x-text="selectedCount"></span>
                </div>

                <div class="max-h-80 overflow-auto rounded-lg border border-slate-200">
                    <template x-for="emp in filtered" :key="emp.id">
                        <label class="flex items-center gap-3 px-4 py-3 border-b border-slate-200 last:border-b-0 cursor-pointer hover:bg-slate-50">
                            <input type="checkbox"
                                   class="rounded border-slate-300"
                                   :value="String(emp.id)"
                                   x-model="selected">

                            <div class="min-w-0">
                                <div class="text-sm font-medium text-slate-900" x-text="emp.name"></div>
                                <div class="text-xs text-slate-500">
                                    ID: <span x-text="emp.id"></span>
                                </div>
                            </div>
                        </label>
                    </template>

                    <div x-show="filtered.length === 0" class="p-4 text-sm text-slate-500">
                        Tidak ada karyawan sesuai pencarian.
                    </div>
                </div>

                {{-- Hidden inputs for selected employee_ids[] --}}
                <template x-for="id in selected" :key="'hid-'+id">
                    <input type="hidden" name="employee_ids[]" :value="id">
                </template>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                            @click="genOpen=false">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="selected.length === 0"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                        Generate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>