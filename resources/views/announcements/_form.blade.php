@csrf

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Judul Pengumuman</label>
        <input type="text" name="title" value="{{ old('title', $announcement->title ?? '') }}"
               class="w-full rounded-lg border-slate-200 text-sm"
               required>
        @error('title')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Mulai</label>
        <input type="date" name="date_from"
               value="{{ old('date_from', isset($announcement) ? $announcement->date_from : '') }}"
               class="w-full rounded-lg border-slate-200 text-sm"
               required>
        @error('date_from')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Selesai</label>
        <input type="date" name="date_to"
               value="{{ old('date_to', isset($announcement) && $announcement->date_to ? $announcement->date_to : '') }}"
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

    {{-- @dd($selectedIds) --}}
    <div class="sm:col-span-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Target Karyawan</label>
        <select name="employee_ids[]" multiple size="8"
                class="w-full rounded-lg border-slate-200 text-sm">
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}"
                    @selected(in_array($employee->id, old('employee_ids', $selectedIds ?? [])))>
                    {{ $employee->name ?? $employee->full_name ?? 'Employee #'.$employee->id }}
                </option>
            @endforeach
        </select>
        <p class="text-[11px] text-slate-400 mt-0.5">Tekan Ctrl / Cmd untuk memilih lebih dari satu.</p>
        @error('employee_ids')
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