@extends('layout.error')

@section('title', 'Terjadi Kesalahan')

@section('content')
    @include('errors.partials.error-card', [
        'status' => 500,
        'message' => $message ?? 'Terjadi kesalahan pada server. Silakan coba lagi.',
    ])
@endsection