<x-guest-layout>
    <div class="max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8 mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
            Lupa Akun
        </h2>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('forgot.account.submit') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="identifier" class="block text-sm font-medium text-gray-700">
                    Masukkan Email atau Nomor Telepon
                </label>
                <input type="text" name="identifier" id="identifier" value="{{ old('identifier') }}"
                    class="mt-1 w-full border-gray-300 bg-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5 border"
                    placeholder="contoh: email@domain.com atau 08123456789" required>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 transition duration-150">
                Cari Akun
            </button>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">
                    ← Kembali ke Login
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>