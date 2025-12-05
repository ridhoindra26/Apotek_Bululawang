@extends('layout.layout')

@section('title', 'Edit Pengumuman')
@section('page_title', 'Edit Pengumuman')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-slate-200 rounded-2xl p-6">
        <form action="/announcement/{{ $announcement->id }}" method="POST">
            @method('POST')
            @include('announcements._form')
        </form>
    </div>
</div>
@endsection
