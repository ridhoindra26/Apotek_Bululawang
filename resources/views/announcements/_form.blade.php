@csrf

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Judul Pengumuman</label>
        <input type="text" name="title" value="{{ old('title', $announcement->title ?? '') }}"
               class="w-full border-slate-200 text-sm"
               required>
        @error('title')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Mulai</label>
        <input type="date" name="date_from"
               value="{{ old('date_from', isset($announcement) ? optional($announcement->date_from)->toDateString() : '') }}"
               class="w-full rounded-lg border-slate-200 text-sm"
               required>
        @error('date_from')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Selesai</label>
        <input type="date" name="date_to"
               value="{{ old('date_to', isset($announcement) && $announcement->date_to ? optional($announcement->date_to)->toDateString() : '') }}"
               class="w-full rounded-lg border-slate-200 text-sm">
        <p class="text-[11px] text-slate-400 mt-0.5">Kosongkan jika pengumuman berlaku tanpa batas.</p>
        @error('date_to')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Isi Pengumuman</label>
        <textarea name="body" rows="4"
                  class="w-full rounded-lg border-slate-200 text-sm"
                  required>{{ old('body', $announcement->body ?? '') }}</textarea>
        @error('body')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Target Karyawan as checkboxes + Select All --}}
    @php
        $checkedIds = old('employee_ids', $selectedIds ?? []);
    @endphp

    <div class="sm:col-span-2"
        x-data="{
            allIds: @js($employees->pluck('id')),
            selected: @js($checkedIds),
            selectAll() { this.selected = [...this.allIds]; },
            clearAll() { this.selected = []; }
        }"
    >
        <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-medium text-slate-600">
                Target Karyawan
            </label>

            <div class="flex items-center gap-2 text-[11px]">
                <button type="button"
                        class="px-2 py-0.5 rounded border border-slate-200 text-slate-600 hover:bg-slate-50"
                        @click="selectAll()">
                    Pilih semua
                </button>
                <button type="button"
                        class="px-2 py-0.5 rounded border border-slate-200 text-slate-500 hover:bg-slate-50"
                        @click="clearAll()">
                    Hapus semua
                </button>
            </div>
        </div>

        <div class="max-h-64 overflow-y-auto rounded-lg border border-slate-200 p-3 space-y-2 bg-white">
            @foreach ($employees as $employee)
                <label class="flex items-start gap-2 text-xs text-slate-700">
                    <input type="checkbox"
                        name="employee_ids[]"
                        value="{{ $employee->id }}"
                        id="emp-{{ $employee->id }}"
                        class="mt-0.5 rounded border-slate-300"
                        x-model="selected">
                    <span>
                        {{ $employee->name ?? $employee->full_name ?? 'Employee #'.$employee->id }}
                        @isset($employee->branch)
                            <span class="text-[10px] text-slate-400">— {{ $employee->branch->name }}</span>
                        @endisset
                    </span>
                </label>
            @endforeach
        </div>

        <p class="text-[11px] text-slate-400 mt-0.5">
            Centang karyawan yang akan menerima pengumuman ini.
        </p>

        @error('employee_ids')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
        @error('employee_ids.*')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex justify-end gap-2">
    <a href="{{ route('announcements.index') }}"
       class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
        Batal
    </a>
    <button type="submit"
            class="px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
        Simpan
    </button>
</div>