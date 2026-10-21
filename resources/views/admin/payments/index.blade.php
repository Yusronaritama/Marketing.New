@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between mb-3">
        <h2>BNI Callback Monitoring</h2>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Test Simulasi VA User
            </button>
            <ul class="dropdown-menu">
                @php $users = \App\Models\User::whereNotNull('va_number')->take(10)->get(); @endphp
                @foreach($users as $user)
                    <li><a class="dropdown-item" href="{{ route('bni.simulate', $user->va_number) }}">Kirim Callback: {{ $user->name }} ({{ $user->va_number }})</a></li>
                @endforeach
            </ul>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>VA Number</th>
                <th>Detail Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at }}</td>
                <td><strong>{{ $log->va_number }}</strong></td>
                <td>
                    <pre class="bg-light p-2" style="font-size: 11px;">{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center text-black text-muted">Belum ada data callback. Klik "Test Simulasi" di atas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection