@extends('layout.layout')

@section('content')
<div class="container py-4">

    <!-- Card Detail Karyawan -->
    <div class="card shadow-lg">
        <div class="card-header bg-blue-600">
            <h3 class="text-xl font-semibold text-white">Detail Karyawan</h3>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tbody>
                    <tr>
                        <th>Nama</th>
                        <td>{{ $karyawan->name }}</td>
                    </tr>
                    <tr>
                        <th>Cabang</th>
                        <td>{{ $karyawan->branches ? $karyawan->branches->name : 'Tidak Diketahui' }}</td>
                    </tr>
                    <tr>
                        <th>Pasangan</th>
                        <td>{{ $karyawan->roles ? $karyawan->roles->name : 'Tidak Ada' }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $karyawan->created_at }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $karyawan->updated_at }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer flex justify-between">
            <!-- Tombol Aksi -->
            <div>
                <a href="{{ route('karyawan.edit', $karyawan->id) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('karyawan.destroy', $karyawan->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus karyawan ini?')">Hapus</button>
                </form>
            </div>
            <!-- Tombol Kembali -->
            <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

</div>
@endsection
