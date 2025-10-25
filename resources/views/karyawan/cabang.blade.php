@extends('layout.layout')

@section('content')
<div class="container-fluid py-4">

    <!-- Form Tambah Cabang -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Tambah Cabang</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('cabang.store') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Cabang</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama cabang" required>
                </div>
                <button type="submit" class="btn btn-primary">Tambah</button>
            </form>
        </div>
    </div>

    <!-- Daftar Cabang -->
    <div class="card">
        <div class="card-header">
            <h5>Daftar Cabang</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Cabang</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cabangs as $cabang)
                    <tr>
                        <td>{{ $cabang->id }}</td>
                        <td>{{ $cabang->name }}</td>
                        <td>{{ $cabang->created_at }}</td>
                        <td>{{ $cabang->updated_at }}</td>
                        <td>
                            <button 
                                type="button"
                                class="btn btn-warning btn-sm js-edit-cabang"
                                data-id="{{ $cabang->id }}" 
                                data-name="{{ $cabang->name }}">
                                Edit
                            </button>

                            <form action="{{ route('cabang.destroy', $cabang->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus cabang ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $cabangs->links() }}
            </div>
        </div>
    </div>

</div>

{{-- Modal is split out into a partial --}}
@include('karyawan.partials._edit-cabang-modal')

@endsection
