@extends('layout.error')

@section('title', 'Terjadi Kesalahan')

@section('content')
    @include('errors.partials.error-card', [
        'status' => $status ?? 'Oops',
        'message' => $message ?? 'Sistem tidak dapat memproses permintaan Anda saat ini.',
    ])
@endsection