@extends('layout.layout')

@section('title', 'Edit Supplier')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('suppliers.index') }}"
           class="text-sm font-medium text-blue-600 hover:text-blue-700">
            ← Kembali ke Supplier
        </a>

        <h1 class="mt-3 text-2xl font-bold text-gray-900">
            Edit Supplier
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Perbarui informasi supplier / PBF.
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @method('PUT')

            @include('suppliers.partials.form', [
                'supplier' => $supplier,
                'submitLabel' => 'Update Supplier',
            ])
        </form>
    </div>
</div>

<script>
    window.supplierFlash = {
        success: @json(session('success')),
        error: @json(session('error')),
    }
</script>
@endsection