@extends('layout.layout')

@section('title', 'Tambah Pengumuman')
@section('page_title', 'Tambah Pengumuman')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-slate-200 rounded-2xl p-6">
        <form action="{{ route('announcements.store') }}" method="POST">
            @include('announcements._form', ['announcement' => new \App\Models\Announcement()])
        </form>
    </div>
</div>
@endsection
