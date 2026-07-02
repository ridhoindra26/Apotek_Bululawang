@extends('layout.error')

@section('title', 'Akses Ditolak')

@section('content')
    @include('errors.partials.error-card', [
        'status' => 403,
        'message' => $message ?? 'Anda tidak memiliki akses untuk membuka halaman ini.',
    ])
@endsection