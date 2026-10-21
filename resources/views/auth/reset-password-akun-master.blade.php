<x-guest-layout>
    <div class="max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8 mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
            Reset Password Akun Master
        </h2>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('password.update.master') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            @if(isset($user) && $user)
                <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
                    <p class="text-sm text-blue-800">
                        <strong>Reset Password untuk:</strong><br>
                        Nama: {{ $user->name }}<br>
                        Email: {{ $user->email }}<br>
                        @if(isset($user->phone))
                            Telepon: {{ $user->phone }}
                        @endif
                    </p>
                </div>
            @endif

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password Baru
                </label>
                <input type="password" name="password" id="password" required
                    class="mt-1 w-full border-gray-300 bg-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5 border"
                    placeholder="Masukkan password baru (minimal 8 karakter)" minlength="8">
                <p class="text-xs text-gray-500 mt-1">Password harus minimal 8 karakter</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Konfirmasi Password Baru
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="mt-1 w-full border-gray-300 bg-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5 border"
                    placeholder="Ulangi password baru">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 transition duration-150 font-medium">
                Reset Password
            </button>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline text-sm">
                    ← Kembali ke Halaman Login
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>