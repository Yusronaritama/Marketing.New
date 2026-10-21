<x-guest-layout>
    <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md" data-aos="zoom-in" data-aos-delay="200">
        
        <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">
            Lupa Kata Sandi
        </h2>

        <p class="text-sm text-gray-600 mb-6 text-center leading-relaxed">
            Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan reset password ke email dan WhatsApp (jika nomor tersedia).
        </p>

        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg text-green-700 bg-green-100 border border-green-300 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg text-green-700 bg-green-100 border border-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg text-red-700 bg-red-100 border border-red-300 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            {{-- Email Field --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Alamat Email
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-2 rounded-lg border border-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none text-gray-200 placeholder-gray-400"
                    placeholder="contoh@email.com">

                @error('email')
                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit Button --}}
            <button type="submit"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 font-semibold rounded-lg shadow-lg text-white transition-all duration-200">
                Kirim Tautan Reset Password
            </button>
        </form>
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm hover:underline text-blue-400">
                ← Kembali ke Halaman Login
            </a>
        </div>
    </div>
</x-guest-layout>