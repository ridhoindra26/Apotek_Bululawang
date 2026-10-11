@csrf

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700">
            Nama Supplier / PBF <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $supplier->name ?? '') }}"
            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
            required
        >
        @error('name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="code" class="mb-1 block text-sm font-medium text-gray-700">
            Kode Supplier
        </label>
        <input
            type="text"
            id="code"
            name="code"
            value="{{ old('code', $supplier->code ?? '') }}"
            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('code') border-red-500 @enderror"
            placeholder="Opsional"
        >
        @error('code')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">
            Telepon Supplier
        </label>
        <input
            type="text"
            id="phone"
            name="phone"
            value="{{ old('phone', $supplier->phone ?? '') }}"
            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone') border-red-500 @enderror"
            placeholder="Contoh: 0341xxxxxx"
        >
        @error('phone')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="mb-1 block text-sm font-medium text-gray-700">
            Email
        </label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $supplier->email ?? '') }}"
            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror"
            placeholder="Opsional"
        >
        @error('email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="contact_person" class="mb-1 block text-sm font-medium text-gray-700">
            Nama Kontak / Sales
        </label>
        <input
            type="text"
            id="contact_person"
            name="contact_person"
            value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('contact_person') border-red-500 @enderror"
            placeholder="Nama sales / PIC"
        >
        @error('contact_person')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="contact_person_phone" class="mb-1 block text-sm font-medium text-gray-700">
            Telepon Kontak / Sales
        </label>
        <input
            type="text"
            id="contact_person_phone"
            name="contact_person_phone"
            value="{{ old('contact_person_phone', $supplier->contact_person_phone ?? '') }}"
            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('contact_person_phone') border-red-500 @enderror"
        >
        @error('contact_person_phone')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="npwp" class="mb-1 block text-sm font-medium text-gray-700">
            NPWP
        </label>
        <input
            type="text"
            id="npwp"
            name="npwp"
            value="{{ old('npwp', $supplier->npwp ?? '') }}"
            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('npwp') border-red-500 @enderror"
            placeholder="Opsional"
        >
        @error('npwp')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center pt-6">
        <input
            type="hidden"
            name="is_active"
            value="0"
        >

        <input
            type="checkbox"
            id="is_active"
            name="is_active"
            value="1"
            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            @checked(old('is_active', $supplier->is_active ?? true))
        >

        <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
            Supplier aktif
        </label>
    </div>

    <div class="md:col-span-2">
        <label for="address" class="mb-1 block text-sm font-medium text-gray-700">
            Alamat
        </label>
        <textarea
            id="address"
            name="address"
            rows="3"
            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('address') border-red-500 @enderror"
            placeholder="Alamat supplier / PBF"
        >{{ old('address', $supplier->address ?? '') }}</textarea>
        @error('address')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('suppliers.index') }}"
       class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
        Batal
    </a>

    <button
        type="submit"
        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
    >
        {{ $submitLabel ?? 'Simpan' }}
    </button>
</div>