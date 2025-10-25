@extends('layout.layout')

@section('content')
<div class="container-fluid py-4">

    <!-- Form Tambah Pasangan -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Tambah Pasangan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('pasangan.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Pasangan</label>
                    <input type="text" class="form-control" id="name" name="name"
                           placeholder="Masukkan nama pasangan" required>
                </div>
                <button type="submit" class="btn btn-primary">Tambah</button>
            </form>
        </div>
    </div>

    <!-- Daftar Pasangan -->
    <div class="card">
        <div class="card-header">
            <h5>Daftar Pasangan</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Pasangan</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pasangans as $pasangan)
                    <tr>
                        <td>{{ $pasangan->id }}</td>
                        <td>{{ $pasangan->name }}</td>
                        <td>{{ $pasangan->created_at }}</td>
                        <td>{{ $pasangan->updated_at }}</td>
                        <td>
                            <button 
                                type="button"
                                class="btn btn-warning btn-sm js-edit-pasangan"
                                data-id="{{ $pasangan->id }}"
                                data-name="{{ $pasangan->name }}">
                                Edit
                            </button>

                            <form action="{{ route('pasangan.destroy', $pasangan->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus pasangan ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Include modal --}}
@include('karyawan.partials._edit-pasangan-modal')
@endsection
