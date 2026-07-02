{{-- Adjust Modal (expects: $period, $item) --}}
<div x-cloak x-show="open" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-sm text-slate-500">Payroll Item</div>
                    <div class="font-semibold text-slate-900 truncate">
                        {{ $item->employee->name ?? ('Employee #'.$item->id_employee) }}
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">
                        Period: {{ $period->name ?? $period->code }}
                        <span class="ml-2 inline-flex rounded-full px-2 py-0.5 text-[10px]
                            @if($period->status==='draft') bg-slate-100 text-slate-700
                            @elseif($period->status==='locked') bg-amber-100 text-amber-800
                            @elseif($period->status==='paid') bg-emerald-100 text-emerald-800
                            @else bg-rose-100 text-rose-800 @endif
                        ">
                            {{ strtoupper($period->status) }}
                        </span>
                    </div>
                </div>

                <button type="button" class="text-slate-500 hover:text-slate-700" @click="open=false">✕</button>
            </div>

            <div class="p-4 space-y-4">

                {{-- Summary --}}
                <div class="grid gap-3 sm:grid-cols-4">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-xs text-slate-500">Base</div>
                        <div class="font-semibold text-slate-900">{{ number_format($item->base_salary_snapshot) }}</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-xs text-slate-500">Allowance</div>
                        <div class="font-semibold text-slate-900">{{ number_format($item->allowance_total) }}</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-xs text-slate-500">Deduction</div>
                        <div class="font-semibold text-slate-900">{{ number_format($item->deduction_total) }}</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-xs text-slate-500">Net Pay</div>
                        <div class="font-semibold text-slate-900">{{ number_format($item->net_pay) }}</div>
                    </div>
                </div>

                @if($period->status !== 'draft')
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-amber-800 text-sm">
                        Period is <span class="font-semibold">{{ strtoupper($period->status) }}</span>.
                        Lines cannot be modified.
                    </div>
                @endif

                {{-- Add line form (only in draft) --}}
                @if($period->status === 'draft')
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="text-sm font-semibold text-slate-900">Tambah Item</div>
                        <div class="text-xs text-slate-500">Tambah tunjangan atau potongan untuk karyawan ini.</div>

                        <form method="POST" action="{{ route('payroll.item-lines.store') }}" class="mt-3 grid gap-3 sm:grid-cols-12">
                            @csrf
                            <input type="hidden" name="payroll_item_id" value="{{ $item->id }}">

                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Tipe</label>
                                <select name="type" class="w-full rounded-lg border-slate-200 text-sm" required>
                                    <option value="allowance">Allowance</option>
                                    <option value="deduction">Deduction</option>
                                </select>
                            </div>

                            <div class="sm:col-span-4">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Nama</label>
                                <input type="text" name="name"
                                       class="w-full rounded-lg border-slate-200 text-sm"
                                       placeholder="Contoh: Bonus / Telat"
                                       required>
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Nominal</label>
                                <input type="number" name="amount" min="0" step="1"
                                       class="w-full rounded-lg border-slate-200 text-sm"
                                       placeholder="0"
                                       required>
                            </div>

                            <div class="sm:col-span-2 flex items-end">
                                <button class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                                    Add
                                </button>
                            </div>

                            <div class="sm:col-span-12">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Deskripsi (opsional)</label>
                                <textarea name="description" rows="2"
                                          class="w-full rounded-lg border-slate-200 text-sm"
                                          placeholder="Contoh: Bonus lembur minggu ini"></textarea>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Existing lines --}}
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Daftar Item</div>
                            <div class="text-xs text-slate-500">Tunjangan & potongan pada periode ini.</div>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @forelse($item->lines as $line)
                            <div class="p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px]
                                                @if($line->type==='allowance') bg-emerald-100 text-emerald-800
                                                @else bg-rose-100 text-rose-800 @endif
                                            ">
                                                {{ strtoupper($line->type) }}
                                            </span>
                                            <div class="text-sm font-semibold text-slate-900 break-words">
                                                {{ $line->name }}
                                            </div>
                                        </div>

                                        @if($line->description)
                                            <div class="mt-1 text-xs text-slate-500 whitespace-pre-line break-words">
                                                {{ $line->description }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ number_format($line->amount) }}
                                        </div>

                                        @if($period->status === 'draft')
                                            {{-- Edit toggle --}}
                                            <div x-data="{ edit:false }" class="inline-block">
                                                <button type="button"
                                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700"
                                                        @click="edit=!edit">
                                                    Edit
                                                </button>

                                                <div x-cloak x-show="edit" class="mt-2 rounded-lg border border-slate-200 bg-slate-50 p-3 w-full sm:w-[420px]">
                                                    <form method="POST" action="{{ route('payroll.item-lines.update', $line->id) }}" class="grid gap-2">
                                                        @csrf
                                                        @method('PUT')

                                                        <div class="grid gap-2 sm:grid-cols-3">
                                                            <div class="sm:col-span-1">
                                                                <label class="block text-xs font-medium text-slate-600 mb-1">Tipe</label>
                                                                <select name="type" class="w-full rounded-lg border-slate-200 text-sm" required>
                                                                    <option value="allowance" @selected($line->type==='allowance')>Allowance</option>
                                                                    <option value="deduction" @selected($line->type==='deduction')>Deduction</option>
                                                                </select>
                                                            </div>

                                                            <div class="sm:col-span-1">
                                                                <label class="block text-xs font-medium text-slate-600 mb-1">Nominal</label>
                                                                <input type="number" name="amount" min="0" step="1"
                                                                       class="w-full rounded-lg border-slate-200 text-sm"
                                                                       value="{{ $line->amount }}"
                                                                       required>
                                                            </div>

                                                            <div class="sm:col-span-1">
                                                                <label class="block text-xs font-medium text-slate-600 mb-1">Nama</label>
                                                                <input type="text" name="name"
                                                                       class="w-full rounded-lg border-slate-200 text-sm"
                                                                       value="{{ $line->name }}"
                                                                       required>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-600 mb-1">Deskripsi (opsional)</label>
                                                            <textarea name="description" rows="2"
                                                                      class="w-full rounded-lg border-slate-200 text-sm">{{ $line->description }}</textarea>
                                                        </div>

                                                        <div class="flex justify-end gap-2">
                                                            <button type="button"
                                                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700"
                                                                    @click="edit=false">
                                                                Cancel
                                                            </button>

                                                            <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">
                                                                Save
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('payroll.item-lines.destroy', $line->id) }}"
                                                  onsubmit="return confirm('Delete this line?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-700">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-500 text-sm">
                                Belum ada item.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <div class="p-4 border-t border-slate-200 flex justify-end">
                <button type="button"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                        @click="open=false">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>