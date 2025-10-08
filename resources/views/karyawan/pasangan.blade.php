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
                @method('POST')
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Pasangan</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama pasangan" required>
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
            <table class="table table-striped">
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
                                data-modal-target="editModal" 
                                data-modal-toggle="editModal" 
                                class="btn btn-warning btn-sm"
                                data-id="{{ $pasangan->id }}" 
                                data-name="{{ $pasangan->nama }}">
                                Edit
                            </button>
                            <form action="{{ route('pasangan.destroy', $pasangan->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pasangan ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Edit Pasangan -->
    <div id="editModal" tabindex="-1" aria-hidden="true" 
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Header -->
                <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Edit Pasangan
                    </h3>
                    <button type="button" 
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" 
                        data-modal-hide="editModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <!-- Body -->
                <div class="p-6 space-y-6">
                    <form id="editForm" method="POST">
                        @csrf
                        @method('POST')
                        <div class="mb-3">
                            <label for="editName" class="block text-sm font-medium text-gray-700">Nama Pasangan</label>
                            <input type="text" id="editName" name="name" 
                                class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                required>
                        </div>
                        <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('[data-modal-target="editModal"]');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                const editForm = document.querySelector('#editForm');
                editForm.setAttribute('action', `/pasangan/${id}`);
                document.querySelector('#editName').value = name;
            });
        });
    });
</script>
@endsection
