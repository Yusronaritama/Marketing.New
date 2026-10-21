@extends('layouts.admin.app') {{-- Sesuaikan dengan layout admin kamu --}}

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Sinkronisasi Nilai Moodle</div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <p>Klik tombol di bawah untuk mengambil data nilai terbaru dari Course ID: <strong>{{ config('services.moodle.default_course_id') }}</strong></p>
            
            <form action="{{ route('moodle.sync') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Sinkronisasi Sekarang
                </button>
            </form>
        </div>
    </div>
</div>
@endsection