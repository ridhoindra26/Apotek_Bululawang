@extends('layout.layout')
@section('title','Dashboard')
@section('content')
    <div class="flex justify-center items-center h-screen">
        <h1 class="text-3xl font-bold text-gray-800">Selamat Datang di Dashboard!</h1>
        <p class="text-xl text-gray-600">Role: {{ auth()->user()->role }}</p>
    </div>
@endsection
