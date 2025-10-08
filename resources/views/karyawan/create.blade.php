@extends('layout.layout')

@section('content')
<div class="container-fluid py-4">

    <!-- Form Tambah Karyawan -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Tambah Karyawan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('karyawan.store') }}" method="POST">
                @csrf
                @method('POST')
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Karyawan</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama karyawan" required>
                </div>
                <div class="mb-3">
                    <label for="cabang_id" class="form-label">Cabang</label>
                    <select class="form-control" id="cabang_id" name="id_branch" required>
                        <option value="">Pilih Cabang</option>
                        @foreach ($cabangs as $cabang)
                            <option value="{{ $cabang->id }}">{{ $cabang->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="pasangan_id" class="form-label">Pasangan (Opsional)</label>
                    <select class="form-control" id="pasangan_id" name="id_role">
                        <option value="">Tidak Ada</option>
                        @foreach ($pasangans as $pasangan)
                            <option value="{{ $pasangan->id }}">{{ $pasangan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Tambah</button>
            </form>
        </div>
    </div>

</div>
@endsection
