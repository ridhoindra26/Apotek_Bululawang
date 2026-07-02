@extends('layout.error')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    @include('errors.partials.error-card', [
        'status' => 404,
        'message' => $message ?? 'Halaman atau data yang Anda cari tidak ditemukan.',
    ])
@endsection