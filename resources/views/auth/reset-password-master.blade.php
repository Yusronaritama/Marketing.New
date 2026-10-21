@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2>Reset Password</h2>
        
        <form method="POST" action="{{ route('password.update.master') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" name="password" id="password" required class="form-control">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    </div>
</div>
@endsection