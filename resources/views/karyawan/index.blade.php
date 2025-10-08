@extends('layout.layout')
@section('content')
    <!-- Card Information -->
    <div class="container-fluid py-0">
        <div class="row g-4">
            <div class="col-xl-4 col-sm-6">
                <div class="card h-100">
                    <div class="card-body p-3 rounded-[8px] bg-yellow-300">
                        <div class="row">
                            <div class="col-9">
                                <div class="numbers">
                                    <p class="text-sm mb-1 text-uppercase font-weight-bold text-white">Total Karyawan</p>
                                    <h5 class="font-weight-bolder text-white">
                                        {{ $total }} Karyawan
                                    </h5>
                                </div>
                            </div>
                            <div class="col-3 text-end">
                                <div class="icon text-center">
                                    <i class="ri-file-list-3-line text-5xl text-white opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($branches as $branch)
                <div class="col-xl-4 col-sm-6">
                    <div class="card h-100">
                        <div class="card-body p-3 rounded-[8px] 
                            {{ $loop->iteration % 2 == 0 ? 'bg-red-500' : 'bg-blue-500' }}">
                            <div class="row">
                                <div class="col-9">
                                    <div class="numbers">
                                        <p class="text-sm mb-1 text-uppercase font-weight-bold text-white">
                                            Total {{ $branch->name }}
                                        </p>
                                        <h5 class="font-weight-bolder text-white">
                                            {{ $branch->employees_count }} Karyawan
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-3 text-end">
                                    <div class="icon text-center">
                                        <i class="ri-user-follow-line text-5xl text-white opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Action Buttons -->
        <div class="row my-4">
            <div class="col-12 d-flex justify-content-start gap-3">
                <a href="{{ route('cabang.index') }}" class="btn bg-blue-500 text-black font-semibold py-2 px-4 rounded-lg hover:bg-blue-600">
                    Kelola Cabang
                </a>
                <a href="{{ route('pasangan.index') }}" class="btn bg-yellow-500 text-black font-semibold py-2 px-4 rounded-lg hover:bg-yellow-600">
                    Kelola Pasangan
                </a>
                <a href="{{ route('karyawan.create') }}" class="btn bg-green-500 text-black font-semibold py-2 px-4 rounded-lg hover:bg-green-600">
                    Tambah Karyawan
                </a>
            </div>
        </div>

        <!-- Start table -->
        <div class="row">
            <div class="col-12">
                <div class="card mt-4">
                    <div class="table-responsive p-0 px-4">

                        <!-- Tabel -->
                        <table class="px-6 w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="px-3 text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="ps-4 pr-6 py-3">
                                        ID
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Nama Karyawan
                                    </th>

                                    <th scope="col" class="px-6 py-3">
                                        Cabang
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Pasangan
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Detail
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($karyawans as $karyawan)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-4 py-4 font-medium text-black whitespace-nowrap">
                                            {{ $karyawan->id }}
                                        </td>
                                        <th scope="row"
                                            class="px-6 py-4 font-medium text-black whitespace-nowrap">
                                            {{ $karyawan->name}}
                                        </th>

                                        <td class="px-6 py-4 font-medium text-black whitespace-nowrap">
                                            {{ $karyawan->branches->name }}
                                        </td>
                                        <td class="px-6 py-4 font-medium text-black whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center text-xs font-medium px-2.5 py-0.5 rounded-lg">
                                                {{ $karyawan->roles ? $karyawan->roles->name : 'Tidak Ada' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-black whitespace-nowrap">
                                            <a href="{{ route('karyawan.show', $karyawan->id) }}"
                                                class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Lihat</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            Tidak Ada Data Ditemukan
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>

                        <!-- Pagination -->

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection