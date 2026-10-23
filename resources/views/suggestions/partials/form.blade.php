@php($editing = $suggestion->exists)
<div class="space-y-5 sm:space-y-6 [&_input:not([type=checkbox])]:min-h-11 [&_input:not([type=checkbox])]:w-full [&_input:not([type=checkbox])]:rounded-xl [&_input:not([type=checkbox])]:border-gray-200 [&_input:not([type=checkbox])]:bg-gray-50/60 [&_input:not([type=checkbox])]:px-3 [&_input:not([type=checkbox])]:py-2.5 [&_input:not([type=checkbox])]:text-sm [&_input:not([type=checkbox]):focus]:border-[#318f8c] [&_input:not([type=checkbox]):focus]:ring-4 [&_input:not([type=checkbox]):focus]:ring-[#318f8c]/10 [&_select]:min-h-11 [&_select]:w-full [&_select]:rounded-xl [&_select]:border-gray-200 [&_select]:bg-gray-50/60 [&_select]:px-3 [&_select]:py-2.5 [&_select]:text-sm [&_select:focus]:border-[#318f8c] [&_select:focus]:ring-4 [&_select:focus]:ring-[#318f8c]/10 [&_textarea]:w-full [&_textarea]:rounded-xl [&_textarea]:border-gray-200 [&_textarea]:bg-gray-50/60 [&_textarea]:px-3 [&_textarea]:py-2.5 [&_textarea]:text-sm [&_textarea]:leading-6 [&_textarea:focus]:border-[#318f8c] [&_textarea:focus]:ring-4 [&_textarea:focus]:ring-[#318f8c]/10">
    <div class="border-b border-gray-100 pb-3">
        <h2 class="font-semibold text-gray-900">Informasi Pengajuan</h2>
        <p class="mt-1 text-xs leading-5 text-gray-500">Isi data utama dan jelaskan saran secara singkat dan jelas.</p>
    </div>
        <label class="block w-full"><span class="mb-2 block text-sm font-medium text-gray-700">Jenis <b class="text-red-500">*</b></span>
            <select name="type" x-model="type" required class="w-full rounded-xl border-slate-300 focus:border-[#318f8c] focus:ring-[#318f8c]">
                <option value="suggestion">Saran</option><option value="complaint">Keluhan</option><option value="improvement">Improvement</option>
            </select>@error('type')<small class="text-red-600">{{ $message }}</small>@enderror
        </label>
    <label class="block w-full"><span class="mb-2 block text-sm font-medium text-gray-700">Visibilitas <b class="text-red-500">*</b></span>
        <select name="visibility" required class="w-full rounded-xl border-slate-300 focus:border-[#318f8c] focus:ring-[#318f8c]">
            <option value="public" @selected(old('visibility', $suggestion->visibility) === 'public')>Publik</option>
            <option value="private" @selected(old('visibility', $suggestion->visibility) === 'private')>Privat — hanya saya dan superadmin</option>
        </select>@error('visibility')<small class="text-red-600">{{ $message }}</small>@enderror
    </label>
    <label class="block w-full"><span class="mb-2 block text-sm font-medium text-gray-700">Judul <b class="text-red-500">*</b></span>
        <input name="title" value="{{ old('title', $suggestion->title) }}" maxlength="255" required class="w-full rounded-xl border-slate-300 focus:border-[#318f8c] focus:ring-[#318f8c]" placeholder="Ringkas dan mudah dipahami">@error('title')<small class="text-red-600">{{ $message }}</small>@enderror
    </label>
    <label class="block w-full"><span class="mb-2 block text-sm font-medium text-gray-700">Target cabang</span>
        <select name="branch_id" class="w-full rounded-xl border-slate-300 focus:border-[#318f8c] focus:ring-[#318f8c]"><option value="">Seluruh Perusahaan</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string)old('branch_id', $suggestion->target_branch_id) === (string)$branch->id)>{{ $branch->name }}</option>@endforeach</select>
        @error('branch_id')<small class="text-red-600">{{ $message }}</small>@enderror
    </label>
    @foreach ([['previous_condition','Kondisi sebelumnya','always'],['proposed_solution','Solusi yang diusulkan','conditional'],['expected_benefit','Manfaat yang diharapkan','conditional'],['additional_notes','Catatan tambahan','never']] as [$name,$label,$requirement])
        <label class="block w-full"><span class="mb-2 block text-sm font-medium text-gray-700">{{ $label }} @if($requirement !== 'never')<b class="text-red-500" x-show="{{ $requirement === 'always' ? 'true' : "type !== 'complaint'" }}">*</b>@endif</span>
            <textarea placeholder="{{ $label }}" name="{{ $name }}" rows="4" {{ $requirement === 'always' ? 'required' : '' }} @if($requirement === 'conditional') :required="type !== 'complaint'" @endif class="w-full rounded-xl border-slate-300 focus:border-[#318f8c] focus:ring-[#318f8c]">{{ old($name, $suggestion->{$name}) }}</textarea>@error($name)<small class="text-red-600">{{ $message }}</small>@enderror
        </label>
    @endforeach
    @if($editing && $suggestion->attachments->isNotEmpty())
        <div><p class="mb-2 text-sm font-bold text-slate-700">Lampiran tersimpan</p><div class="space-y-2">@foreach($suggestion->attachments as $attachment)
            <label class="flex w-full min-w-0 items-center gap-3 overflow-hidden rounded-xl border border-slate-200 p-3"><span class="min-w-0 flex-1 truncate text-sm" title="{{ $attachment->original_filename }}"><i class="ri-attachment-line"></i> {{ $attachment->original_filename }}</span><span class="shrink-0 whitespace-nowrap text-xs text-red-600"><input type="checkbox" name="deleted_attachment_ids[]" value="{{ $attachment->id }}" @change="confirmRemoval($event)"> Hapus</span></label>
        @endforeach</div></div>
    @endif
    <div><p class="mb-2 text-sm font-medium text-gray-700">Lampiran</p>
        <label class="flex cursor-pointer flex-col items-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/60 p-5 text-center transition hover:border-[#318f8c] hover:bg-[#318f8c]/5 sm:p-6"><i class="ri-upload-cloud-2-line text-3xl text-[#318f8c]"></i><span class="mt-2 text-sm font-semibold text-gray-800">Pilih foto atau PDF</span><span class="mt-1 text-xs text-gray-500">Maksimal 5 file, masing-masing 5 MB</span><input x-ref="files" @change="selectFiles($event)" class="hidden" type="file" name="attachments[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf"></label>
        @error('attachments')<small class="text-red-600">{{ $message }}</small>@enderror @error('attachments.*')<small class="text-red-600">{{ $message }}</small>@enderror
        <div class="mt-3 grid grid-cols-1 gap-2" x-show="files.length"><template x-for="(file,index) in files" :key="file.key"><div class="flex w-full min-w-0 items-center gap-3 overflow-hidden rounded-xl border bg-white p-3"><img x-show="file.image" :src="file.url" class="h-12 w-12 shrink-0 rounded-lg object-cover"><i x-show="!file.image" class="ri-file-pdf-2-line shrink-0 text-3xl text-red-500"></i><span class="min-w-0 flex-1 overflow-hidden"><b class="block w-full truncate text-sm" :title="file.name" x-text="file.name"></b><small class="text-xs text-slate-500" x-text="file.size"></small></span><button type="button" @click="removeFile(index)" class="shrink-0 rounded-lg p-2 text-red-500 hover:bg-red-50" aria-label="Hapus file"><i class="ri-close-line"></i></button></div></template></div>
    </div>
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/95 p-3 shadow-[0_-8px_24px_rgba(15,23,42,0.08)] backdrop-blur sm:sticky sm:bottom-3 sm:rounded-xl sm:border">
        <div class="mx-auto flex max-w-4xl gap-2 sm:justify-end">
            <a href="{{ $editing ? route('suggestions.show', $suggestion) : route('suggestions.mine') }}" class="flex min-h-11 flex-1 items-center justify-center rounded-xl border border-gray-300 px-4 py-2.5 text-center font-semibold text-gray-600 sm:flex-none sm:min-w-32">Batal</a>
            <button type="submit" :disabled="submitting" class="min-h-11 flex-[1.4] rounded-xl bg-[#318f8c] px-4 py-2.5 font-bold text-white shadow-sm disabled:opacity-60 sm:flex-none sm:min-w-48"><span x-show="!submitting">{{ $editing ? 'Simpan Perubahan' : 'Ajukan Saran' }}</span><span x-show="submitting" x-cloak><i class="ri-loader-4-line animate-spin"></i> Menyimpan...</span></button>
        </div>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => Alpine.data('suggestionForm', ({maxFiles, existingCount}) => ({
 type: @js(old('type', $suggestion->type ?: 'suggestion')), files: [], submitting: false,
 selectFiles(e) { const picked=[...e.target.files], deleted=document.querySelectorAll('input[name="deleted_attachment_ids[]"]:checked').length; if(picked.length+existingCount-deleted>maxFiles){ Swal.fire('Terlalu banyak lampiran',`Total maksimal ${maxFiles} file.`,'warning'); e.target.value=''; return; } this.rebuild(picked); },
 rebuild(items){ this.files.forEach(f=>f.url&&URL.revokeObjectURL(f.url)); this.files=items.map((f,i)=>({file:f,key:`${f.name}-${f.lastModified}-${i}`,name:f.name,size:`${(f.size/1024/1024).toFixed(2)} MB`,image:f.type.startsWith('image/'),url:f.type.startsWith('image/')?URL.createObjectURL(f):null})); const dt=new DataTransfer(); this.files.forEach(f=>dt.items.add(f.file)); this.$refs.files.files=dt.files; },
 removeFile(i){ const items=this.files.filter((_,x)=>x!==i).map(x=>x.file); this.rebuild(items); },
 async confirmRemoval(e){ if(!e.target.checked)return; const result=await Swal.fire({title:'Hapus lampiran?',text:'Lampiran akan dihapus saat perubahan disimpan.',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, tandai hapus',cancelButtonText:'Batal'}); if(!result.isConfirmed)e.target.checked=false; }
})))
</script>
