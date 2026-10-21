<x-guest-layout>
<div class="max-w-md mx-auto bg-white rounded-2xl shadow-lg p-6">
    <h2 class="text-2xl font-semibold mb-4 text-center">🔑 Reset Password</h2>

    @if (session('status'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
            <input id="password" type="password" name="password" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md transition">
            Simpan Password Baru
        </button>
    </form>
</div>

</x-guest-layout>
